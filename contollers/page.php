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

   include_once 'views/page.php';

   /**
    * PageController Class
    *
    * This class contains the basic functions to create and display the ReadRss website.
    *
    * @package    ReadRss
    * @subpackage Controller
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class PageController
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
       * Contains the UserController class instance.
       *
       * @var \UserController
       *
       */
      private $m_user = null;

      /**
       * Contains the Rss class instance.
       *
       * @var \Rss
       */
      private $m_rss = null;

      /**
       * Contains the UserView class instance.
       *
       * @var \UserView
       *
       */
      private $m_view = null;

      /**
       * Contains the whole page source.
       *
       * @var string
       *
       */
      private $m_page = null;

      /**
       * Class constructor
       *
       * @param \MySQL           $database   contains the database instance
       * @param \Session         $session    contains the session instance
       * @param \UserController  $user       contains the user instance
       *
       */
      public function __construct($database, $session, $user)
      {
         $this->m_database = $database;
         $this->m_session = $session;
         $this->m_user = $user;
         //$this->m_rss = new RSS($this->m_database);
         $this->m_view = new PageView($this->m_session);

         Template::registerController(__CLASS__, $this);
      }

      /**
       * Prepares the HTML code of the page.
       *
       * @return  void
       *
       */
      public function preparePage()
      {
         $signInError = '';
         $mail = Security::getPostParameter('user');

         Security::cleanGetParameters();

         if(Security::checkGetParameter('action', false, 'signin') && !$this->m_user->isSignedIn())
         {
            $this->m_user->doSignIn($signInError, $mail, Security::getPostParameter('password'), true);
         }

         if($this->m_user->isSignedIn())
         {
            $this->m_page = $this->m_view->showOverview();

            return;
         }

         $this->m_page = $this->m_view->showSignIn($signInError, $mail);
      }

      public function showContent()
      {
         if(Security::checkGetParameter('show', false))
         {
            return 'Show posts from starred, archive or specifed tag.';
         }
         elseif(Security::checkGetParameter('action', false, 'feeds'))
         {
            return 'Show all rss sources.';
         }
         elseif(Security::checkGetParameter('action', false, 'search'))
         {
            return 'search for posts in all feeds';
         }
         elseif(Security::checkGetParameter('action', false, 'logout'))
         {
            $this->m_user->doSignOut();
         }
         else
         {
            return 'Show new posts.';
         }
      }

      public function showTags()
      {
         /*
          * <li class="tag"><a href="#">George Orwell</a></li>
          *                 <li class="tag"><a href="#">Book</a></li>
          */

         return '<li class="tag"><a href="#">George Orwell</a></li>';
      }

      /**
       * Shows the page.
       *
       * @return  string   contains the page source
       *
       */
      public function showPage()
      {
         echo $this->m_page;
      }
   }
?>