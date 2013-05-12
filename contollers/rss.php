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

   include_once 'models/rss.php';

   /**
    * RSS Class
    *
    * This class contains all functions to deal with rss sources.
    *
    * @package    ReadRss
    * @subpackage Controller
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class RSS
   {
      /**
       * Contains the Database class instance.
       *
       * @var \MySQL
       *
       */
      private $m_database = null;

      /**
       * Contains the Session class instance.
       *
       * @var \Session
       *
       */
      private $m_session = null;

      /**
       * Contains the RssModel class instance.
       *
       * @var \RssModel
       *
       */
      private $m_model = null;

      /**
       * Class constructor
       *
       * @param \MySQL     $database   contains the database instance
       *
       * @return  void
       *
       */
      public function __construct($database, $session)
      {
         $this->m_database = $database;
         $this->m_session = $session;
         $this->m_model = new RssModel($this->m_database);

         Template::registerController(__CLASS__, $this);
      }

      public function parseFeedArguments()
      {
         if(Security::checkGetParameter('do', false, 'add'))
         {
            $this->createFeed(Security::getPostParameter('feedurl'));
         }

         if(Security::checkGetParameter('do', false, 'save'))
         {
            // TODO: save feed
            // do i even need to have an edit mode?
            echo 'save feed';
         }

         if(Security::checkGetParameter('do', false, 'delete'))
         {
            $this->deleteFeed($_GET['feed']);
         }
      }
      
      public function createFeed($feedUrl)
      {
         $userId = $this->m_session->get(SESSION_NAME_USERID);

         $this->m_model->addFeed($feedUrl, $userId);
      }

      public function deleteFeed($feedId)
      {
         $userId = $this->m_session->get(SESSION_NAME_USERID);

         $this->m_model->deleteFeed($feedId, $userId);
      }

      public function getFeedList()
      {
         $userId = $this->m_session->get(SESSION_NAME_USERID);

         return $this->m_model->getFeedList($userId);
      }
      
      public function getUnreadArticles()
      {
         return array(array('title'=>'Hello World','preview'=>'First article'), array('title'=>'Hello World','preview'=>'Second article'));
      }

      public function updateFeeds()
      {
         $feeds = $this->getFeedList();
         
         foreach($feeds as $feed)
         {
            $this->m_model->updateFeed($feed['uId']);
         }
         
         return true;
      }
   }
?>
