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
   include_once 'views/rss.php';

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
         $this->m_model = new RssModel($this->m_database);
         $this->m_view = new RssView();

         Template::registerController(__CLASS__, $this);
      }

      public function createFeed($feedUrl)
      {
         
      }

      public function updateFeeds()
      {
      }
   }
?>
