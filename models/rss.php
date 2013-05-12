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
         
         $this->m_database->select('feed_url')
                            ->from('feed', 'f')
                            ->where('unique_id = "' . $feedId . '"');
         
         $this->m_database->executeSelect($result);

         if($result === false)
         {
            return false;
         }
         
         $xmlRssFeed = $this->fetchContent($result[0]['feed_url']);
         
         file_put_contents('data.txt', $xmlRssFeed);

         $xml = simplexml_load_string($xmlRssFeed);
         
         if(false === $xml)
         {
            Error::newError(Error::WARNING, Template::getText('FailedReadingRssFeed'));

            return false;
         }
         
         foreach ($xml->channel->item as $item)
         {
            $this->readRssItem($item);
         }
         
         return true;
      }
      
      private function readRssItem($item)
      {
         $rssArticle = array();
         $rssArticle['title'] = $this->parseRssElement($item->title);
         $rssArticle['articleLink'] = $this->parseRssElement($item->link);
         $rssArticle['articleCommentsLink'] = $this->parseRssElement($item->comments);
         $rssArticle['articlePublicationDate'] = date("Y-m-d H:i:s", strtotime($this->parseRssElement($item->pubDate)));
         
         $rssArticle['articleDescription'] = strip_tags($this->parseRssElement($item->description));
         
         $rssArticle['articlePermaLink'] = $this->parseRssElement($item->guid);
         $rssArticle['articleIsPermaLink'] = $this->parseRssElement($item->guid['isPermaLink']);
         $rssArticle['articleAuthorMail'] = $this->parseRssElement($item->author);
         $rssArticle['categories'] = array();
         
         foreach($item->category as $category)
         {
            $rssArticle['categories'][] = $this->parseRssElement($category);
         }
         
         // get the data held in namespaces
         $content = $item->children($this->namespaces['content']);
         $dc = $item->children($this->namespaces['dc']);
         $wfw = $item->children($this->namespaces['wfw']);
         
         $rssArticle['articleAuthor'] = $this->parseRssElement($dc->creator);
         $rssArticle['articleContent'] = strip_tags($this->parseRssElement($content->encoded));
         $rssArticle['articleCommentRss'] = $this->parseRssElement($wfw->commentRss);
         
         //file_put_contents($rssArticle['title'], $rssArticle['articleContent']);

         var_dump($rssArticle);
         
         $this->saveRssItem($rssArticle);
      }
      
      private function saveRssItem($article)
      {
         // TODO: check if article already was saved
         
         $itemId = null;
         
         $this->m_database->cleanUp();
         
         $this->m_database->insert('article')
                            ->set('title', $article['title'], 's')
                            ->set('link', $article['articleLink'], 's')
                            ->set('comment_link', $article['articleCommentsLink'], 's')
                            ->set('publication', $article['articlePublicationDate'], 's')
                            ->set('description', $article['articleDescription'], 's')
                            ->set('guid', $article['articlePermaLink'], 's')
                            ->set('perma_link', ($article['articleIsPermaLink'] === 'true' ? 1 : 0))
                            ->set('author_mail', $article['articleAuthorMail'], 's')
                            ->set('author_name', $article['articleAuthor'], 's')
                            ->set('categories', implode(';', $article['categories']), 's')
                            ->set('content', htmlspecialchars($article['articleContent']), 's')
                            ->set('comment_rss', $article['articleCommentRss'], 's');
         
         $this->m_database->executeInsert($itemId);
      }
      
      private function parseRssElement($string)
      {
         return (string)trim($string);  
      }
   }
?>