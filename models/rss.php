<?php
/**
 * ReadRss
 *
 * An open source RSS collector and reader for PHP 5.4 or newer
 *
 * @package  ReadRss
 * @author   Pascal Minder <pascal.minder@trustus.ch>
 * @license  http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 * @link     https://github.com/HotPizzaBox/readrss
 *
 */

   defined('SERVER_KEY') OR exit('No direct script access allowed');

   /**
    * RssModel class
    *
    * Contains the database abstraction layer for the RssController.
    *
    * @package    ReadRss
    * @subpackage Model
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class RssModel
   {
      /**
       * Contains the Database class instance.
       *
       * @var \MySQL
       *
       */
      private $m_database = null;

      /**
       * Contains all relevant xml namespaces
       *
       * @var array
       */
      private $namespaces = array
                            (
                               'content' => 'http://purl.org/rss/1.0/modules/content/',
                               'wfw' => 'http://wellformedweb.org/CommentAPI/',
                               'dc' => 'http://purl.org/dc/elements/1.1/'
                            );

      /**
       * Constructor
       *
       * @param \MySQL     $database   contains the database instance
       *
       */
      public function __construct($database)
      {
         $this->m_database = $database;
      }

      public function getFeedList($userId)
      {
         $result = array();
         $feeds = array();

         $this->m_database->cleanUp();

         $this->m_database->select(array('f.title', 'f.unique_id'))
                            ->from('user_feed', 'uf')
                            ->join('uf', 'feed', 'f', 'uf.fk_feed = f.idfeed')
                            ->where('uf.fk_user = ' . $userId)
                            ->orderBy('f.title', 'AC');

         $this->m_database->executeSelect($result);

         if(false !== $result)
         {
            foreach($result as $feed)
            {
               $feeds[] = array('title' => $feed['title'], 'uId' => $feed['unique_id']);
            }
         }

         return $feeds;
      }

      public function addFeed($url, $userId)
      {
         // TODO: sanitize external data

         $fk_feed = 0;
         $fk_userFeed = 0;
         $xmlRssFeed = $this->fetchContent($url);

         $xml = simplexml_load_string($xmlRssFeed);

         if(false === $xml)
         {
            Error::newError(Error::WARNING, Template::getText('FailedReadingRssFeed'));

            return false;
         }

         $channelTitle = $xml->channel[0]->title;
         $channelLink = $xml->channel[0]->link;
         $channelDescription = $xml->channel[0]->description;

         /*
          * This field could be empty.
          */
         $channelLanguage = $xml->channel[0]->language;

         $fk_feed = $this->checkExistingFeed($channelLink);

         if($fk_feed === null)
         {
            $this->m_database->insert('feed')
                               ->set('title', $channelTitle, 's')
                               ->set('website', $channelLink, 's')
                               ->set('description', $channelDescription, 's')
                               ->set('language', $channelLanguage, 's')
                               ->set('unique_id', uniqid(), 's')
                               ->set('feed_url', $url, 's');

            $this->m_database->executeInsert($fk_feed, true);
         }

         // TODO: check if user already has this feed

         $this->m_database->insert('user_feed')
                            ->set('fk_feed', $fk_feed)
                            ->set('fk_user', $userId);

         $this->m_database->executeInsert($fk_userFeed, true);

         if($fk_userFeed === null)
         {
            return false;
         }

         return true;
      }

      public function deleteFeed($feedId, $userId)
      {
         $result = array();

         $this->m_database->cleanUp();

         $this->m_database->select(array('idfeed'))
                            ->from('feed', 'f')
                            ->where('f.unique_id = "' . $feedId . '"');

         $this->m_database->executeSelect($result);

         if(false !== $result)
         {
            $this->m_database->cleanUp();

            $this->m_database->delete()
                   ->from('user_feed', 'uf')
                   ->where('fk_user = ' . $userId)
                   ->where('fk_feed = ' . $result[0]['idfeed']);

             return $this->m_database->executeDelete();
         }

         return false;
      }

      public function getArticles($userId, $unreadArticles=true)
      {
         $result = array();
         $articles = array();

         $this->m_database->cleanUp();

         $this->m_database->select()
                            ->from('user_feed', 'uf')
                            ->join('uf', 'article', 'a', 'uf.fk_feed = a.fk_feed')
                            ->join('uf', 'article_state', 'a_s', 'a_s.fk_article = a.idarticle')
                            ->where('uf.fk_user = ' . $userId)
                            ->where('a_s.unread = ' . ($unreadArticles ? 1 : 0));

         $this->m_database->executeSelect($result);

         foreach ($result as $article)
         {
            $a = array();

            $a['id'] = $article['idarticle_state'];
            $a['title'] = $article['title'];
            $a['preview'] = $article['description'];

            $articles[] = $a;
         }

         return $articles;
      }

      private function checkExistingFeed($feedUrl)
      {
         $result = null;

         $this->m_database->cleanUp();

         $this->m_database->select('idfeed')
                            ->from('feed', 'f')
                            ->where('feed_url = "' . $feedUrl . '"');

         $this->m_database->executeSelect($result);

         if($result !== false)
         {
            return $result[0]['idfeed'];
         }

         return null;
      }

      private function fetchContent($url)
      {
         $ch = curl_init();

         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

         $data = curl_exec($ch);

         curl_close($ch);

         return $data;
      }

      public function updateFeed($feedId)
      {
         $result = false;

         $this->m_database->cleanUp();

         $this->m_database->select('idfeed, feed_url')
                            ->from('feed', 'f')
                            ->where('unique_id = "' . $feedId . '"');

         $this->m_database->executeSelect($result);

         if($result === false)
         {
            return false;
         }

         $xmlRssFeed = $this->fetchContent($result[0]['feed_url']);

         $xml = simplexml_load_string($xmlRssFeed);

         if(false === $xml)
         {
            Error::newError(Error::WARNING, Template::getText('FailedReadingRssFeed'));

            return false;
         }

         foreach ($xml->channel->item as $item)
         {
            $this->parseRssArticle($item, $result[0]['idfeed']);
         }

         return true;
      }

      private function parseRssArticle($item, $feedId)
      {
         $rssArticle = array();

         $rssArticle['feed'] = $feedId;

         $rssArticle['title'] = $this->parseElement($item->title);
         $rssArticle['articleLink'] = Security::url($item->link) === 1 ? $this->parseElement($item->link) : '';
         $rssArticle['articleCommentsLink'] = Security::url($item->comments) === 1 ? $this->parseElement($item->comments) : '';
         $rssArticle['articlePublicationDate'] = date("Y-m-d H:i:s", strtotime($this->parseElement($item->pubDate)));

         $rssArticle['articlePermaLink'] = $this->parseElement($item->guid);
         $rssArticle['articleIsPermaLink'] = $this->parseElement($item->guid['isPermaLink']);
         $rssArticle['articleAuthorMail'] = $this->parseElement($item->author);
         $rssArticle['categories'] = array();

         foreach($item->category as $category)
         {
            $rssArticle['categories'][] = $this->parseElement($category);
         }

         // get the data held in namespaces
         $content = $item->children($this->namespaces['content']);
         $dc = $item->children($this->namespaces['dc']);
         $wfw = $item->children($this->namespaces['wfw']);

         $rssArticle['articleAuthor'] = $this->parseElement($dc->creator);

         if(strlen($this->parseElement($content->encoded)) === 0)
         {
            $rssArticle['articleContent'] = $this->parseHTMLArticle($this->parseElement($item->description));
            $rssArticle['articleDescription'] = $this->parseHTMLArticle(substr($this->parseElement($item->description), 0, 300));
         }
         else
         {
            $rssArticle['articleContent'] = $this->parseHTMLArticle($this->parseElement($content->encoded));
            $rssArticle['articleDescription'] = $this->parseHTMLArticle($this->parseElement($item->description));
         }


         $rssArticle['articleCommentRss'] = $this->parseElement($wfw->commentRss);

         $this->saveRssArticle($rssArticle, $feedId);
      }

      private function saveRssArticle($article, $feedId)
      {
         $itemId = null;

         // check if article already exists:
         if(!$this->isExistingRssArticle($article['articlePermaLink']))
         {
            $this->m_database->cleanUp();

            $this->m_database->insert('article')
                               ->set('fk_feed', $article['feed'])
                               ->set('title', $article['title'], 's')
                               ->set('link', $article['articleLink'], 's')
                               ->set('comment_link', $article['articleCommentsLink'], 's')
                               ->set('publication', $article['articlePublicationDate'], 's')
                               ->set('description', $article['articleDescription'], 's')
                               ->set('guid', $article['articlePermaLink'], 's')
                               ->set('perma_link', ($article['articleIsPermaLink'] === 'true' ? 1 : 0))
                               ->set('author_mail', $article['articleAuthorMail'], 's')
                               ->set('author_name', $article['articleAuthor'], 's')
                               ->set('content', $article['articleContent'], 's')
                               ->set('comment_rss', $article['articleCommentRss'], 's');

            $this->m_database->executeInsert($itemId);

            $this->createArticleStates($feedId, $itemId);
         }
      }

      private function createArticleStates($feedId, $articleId)
      {
         $result = array();

         $this->m_database->cleanUp();

         $this->m_database->select('fk_user')
                            ->from('user_feed', 'uf')
                            ->where('fk_feed = "' . $feedId . '"');

         $this->m_database->executeSelect($result);

         foreach($result as $user)
         {
            $this->m_database->cleanUp();

            $this->m_database->insert('article_state')
                               ->set('fk_user', $user['fk_user'])
                               ->set('fk_article', $articleId);

            $this->m_database->executeInsert($id);
         }
      }

      private function isExistingRssArticle($guid)
      {
         $this->m_database->cleanUp();

         $this->m_database->count('article')
                            ->where('guid = "' . $guid . '"');

         $result = $this->m_database->executeCount();

         if($result >= 1)
         {
            return true;
         }

         return false;
      }

      private function parseElement($string)
      {
         return (string)trim($string);
      }

      private function parseHTMLArticle($input)
      {
         if(false !== strpos($input, '[...]'))
         {
            $length = strlen($input) - (strlen($input) - strpos($input, '[...]'));

            $input = substr($input, 0, $length);
         }

         $input = '<p>' . $input . '</p>';

         /*
         $doc = new DOMDocument();

         @$doc->loadHTML($input);

         $tags = $doc->getElementsByTagName('img');

         foreach ($tags as $tag) {
            $tag->setAttribute('width', '100%');
            $tag->removeAttribute('height');
         }

         $input = $doc->saveHTML();
          * */

         preg_match('/(<img[^>]+>)/i', $input, $matches);

         foreach ($matches as $img)
         {
            $old = $img;

            $img = preg_replace('/class=["]?((?:.(?!["]?\s+(?:\S+)=|[>"]))+.)["]?/', 'class="article_image"', $img);
            $img = preg_replace('/height=["]?((?:.(?!["]?\s+(?:\S+)=|[>"]))+.)["]?/', '', $img);
            $img = preg_replace('/width=["]?((?:.(?!["]?\s+(?:\S+)=|[>"]))+.)["]?/', '', $img);

            $img = '<div class="article_image">' . $img . '</div>';

            $input = str_replace($old, $img, $input);
         }

         $input = str_replace('"', '\"', $input);

         return $input;
      }
   }
?>