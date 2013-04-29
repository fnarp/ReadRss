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
    * @package    SocialPlus
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
       * Constructor
       *
       * @param \MySQL     $database   contains the database instance
       *
       */
      public function __construct($database)
      {
         $this->m_database = $database;
      }

      public function setUpFeed($url)
      {
         $fk_feed = 0;
         $xmlRssFeed = $this->fetchContent($url);

         $xml = SimpleXmlElement($xmlRssFeed);

         if(count($xml) === 0)
         {
            Error::newError(Error::WARNING, 10, 'Could not read Feed.');

            return false;
         }

         $channelTitle = $xml->channel[0]->title;
         $channelLink = $xml->channel[0]->link;
         $channelDescription = $xml->channel[0]->description;

         /*
          * could be an empty field
          */
         $channelLanguage = $xml->channel[0]->language;

         $fk_feed = $this->getFeedId($channelLink);

         if($fk_feed === NULL)
         {
            $this->m_database->insert('feed')
                               ->set('title', $channelTitle, 's')
                               ->set('link', $channelLink, 's')
                               ->set('description', $channelDescription, 's')
                               ->set('language', $channelLanguage, 's');

            $this->m_database->executeInsert($fk_feed, true);
         }

         return $this->fetchFeed($url, $fk_feed, $xml);
      }

      public function fetchFeed($url, $fk_feed=NULL, $xml=NULL)
      {
         if($fk_feed === 0)
         {
            // TODO: what should I do here?
         }

         if($xml !== NULL)
         {
            $xmlRssFeed = $this->fetchContent($url);

            $xml = simplexml_load_string($xmlRssFeed);

            if(count($xml) === 0)
            {
               Error::newError(Error::WARNING, 10, 'Could not read Feed.');
            }
         }

         foreach ($xml->channel->item as $item)
         {
            (new RssPost($item))->save($this->m_database, $fk_feed);
         }
      }

      private function getFeedId($channelUrl)
      {
         $result = null;

         $this->m_database->cleanUp();

         $this->m_database->select('idfeed')
                            ->from('feed', 'f')
                            ->where('link="' . $channelUrl . '"');

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
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

         $data = curl_exec($ch);

         curl_close($ch);

         return $data;
      }
   }


   class RssPost
   {
      public $m_title = '';
      public $m_link = '';
      public $m_publicationDate;
      public $m_categories = array();
      public $m_author = '';
      public $m_guid = '';
      public $m_description = '';
      public $m_content = '';

      public function __construct($xmlPost)
      {
         $this->setTitle($xmlPost->title);
         $this->setLink($xmlPost->link);
         $this->setPublicationDate($xmlPost->pubDate);
         $this->setAuthor($xmlPost);
         $this->setId($xmlPost->guid);
         $this->setDescription($xmlPost->description);
         $this->setContent($xmlPost);
      }

      public function setTitle($title)
      {
         $this->m_title = $title;
      }

      public function setLink($link)
      {
         $this->m_link = $link;
      }

      public function setPublicationDate($date)
      {
         $this->m_publicationDate = strtotime($date);
      }

      public function setAuthor($xmlPost)
      {
         if(!empty($xmlPost->author))
         {
            $this->m_author = $xmlPost->author;
         }
         else
         {
            $this->m_author = $xmlPost->{'dc:creator'};
         }
         echo $xmlPost->{'dc:creator'};
         var_dump($xmlPost);
      }

      public function setId($id)
      {
         $this->m_guid = $id;
      }

      public function setDescription($decription)
      {
         $this->m_description = $decription;
      }

      public function setContent($content)
      {
         $this->m_content = "nothing";
      }

      public function save($database, $feed_id)
      {
         $fk_post = 0;

         $database->cleanUp();

         $database->insert('post')
                     ->set('fk_feed', $feed_id)
                     ->set('title', $this->m_title, 's')
                     ->set('link', $this->m_link, 's')
                     ->set('publication', date("Y-m-d H:i:s", $this->m_publicationDate), 's')
                     ->set('author', $this->m_author, 's')
                     ->set('guid', $this->m_guid, 's')
                     ->set('description', $this->m_description, 's')
                     ->set('content', $this->m_content, 's');

         $database->executeInsert($fk_post, true);
      }
   }
?>