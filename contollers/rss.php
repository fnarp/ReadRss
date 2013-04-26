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

   include_once 'models/user.php';

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
       * Contains the UserModel class instance.
       *
       * @var \UserModel
       *
       */
      private $m_model = null;

      /**
       * Contains the UserView class instance.
       *
       * @var \UserView
       *
       */
      private $m_view = null;

      /**
       * Class constructor
       *
       * @param \MySQL     $database   contains the database instance
       * @param \Session   $session    contains the session instance
       *
       * @return  void
       *
       */
      public function __construct($database, $session)
      {
         $this->m_database = $database;
         $this->m_session = $session;
         $this->m_model = new RssModel($this->m_database);
         $this->m_view = new RssView();

         Template::registerController(__CLASS__, $this);
      }

      public function fetchContent($url)
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
?>
