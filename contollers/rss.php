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

   //include_once 'models/rss.php';
   //include_once 'views/rss.php';

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
       * Contains the RssModel class instance.
       *
       * @var \RssModel
       *
       */
      private $m_model = null;

      /**
       * Contains the RssView class instance.
       *
       * @var \RssView
       *
       */
      private $m_view = null;

      /**
       * Class constructor
       *
       * @param \MySQL     $database   contains the database instance
       *
       * @return  void
       *
       */
      public function __construct($database)
      {
         $this->m_database = $database;
         //$this->m_model = new RssModel($this->m_database);
         //$this->m_view = new RssView();

         Template::registerController(__CLASS__, $this);
      }

      public function createFeed($feedUrl)
      {

      }

      public function parseFeedArguments()
      {
         if(Security::checkGetParameter('do', false, 'add'))
         {
            echo 'add feed';
         }

         if(Security::checkGetParameter('do', false, 'save'))
         {
            echo 'save feed';
         }

         if(Security::checkGetParameter('do', false, 'delete'))
         {
            echo 'delete feed';
         }
      }

      public function getFeedList()
      {
         return '
            <div class="feed_list" style="width: 50%; margin: 20px 25%;">
               <div class="feed" style="width: 98%; padding:5px 1%; background-color: rgb(229, 229, 229); line-height: 1.1em; font-size: 1em;  margin-bottom: 10px; border-radius:4px;">
                  Golem.de
                  <span class="feed_options" style="float:right;">
                     <a href="?action=feeds&amp;do=edit&amp;feed=Golem.de">edit</a>
                     <a href="?action=feeds&amp;do=delete&amp;feed=Golem.de">delete</a>
                  </span>
                  <div class="clear_right" style="clear: right;"></div>
                  <div class="feed_config">

                  </div>
               </div>
               <div class="feed" style="width: 98%; padding:5px 1%; background-color: rgb(229, 229, 229); line-height: 1.1em; font-size: 1em;  margin-bottom: 10px; border-radius:4px;">
                  Golem.de
                  <span class="feed_options" style="float:right;">
                     <a href="?action=feeds&amp;do=edit&amp;feed=Golem.de">edit</a>
                     <a href="?action=feeds&amp;do=delete&amp;feed=Golem.de">delete</a>
                  </span>
                  <div class="clear_right" style="clear: right;"></div>
                  <div class="feed_config">

                  </div>
               </div>
            </div>
            ';
      }

      public function updateFeeds()
      {
      }
   }
?>
