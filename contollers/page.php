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
   include_once 'models/page.php';

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
       * Contains the PageView class instance.
       *
       * @var \PageView
       *
       */
      private $m_view = null;

      /**
       * Contains the PageModel class instance.
       *
       * @var \PageModel
       *
       */
      private $m_model = null;

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
         $this->m_view = new PageView();
         $this->m_model = new PageModel($this->m_database);

         $this->m_rss = new RSS($this->m_database, $this->m_session);

         Template::registerController(__CLASS__, $this);
      }

      /**
       * Prepares the HTML code of the page.
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
         elseif(Security::checkGetParameter('action', false, 'signout'))
         {
            $this->m_user->doSignOut();
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
         $userId = $this->m_session->get(SESSION_NAME_USERID);

         if(Security::checkGetParameter('action', false, 'addTag'))
         {
            // TODO: add $_GET santisize
            $this->m_model->addTag(Security::getPostParameter('tag'), $userId);
         }

         if(Security::checkGetParameter('action', false, 'deleteTag'))
         {
            // TODO: add $_GET santisize
            $this->m_model->deleteTag(urldecode($_GET['tag']), $userId);
         }


         if(Security::checkGetParameter('show', false))
         {
            return 'Show posts from starred, archive or specifed tag.';
         }
         elseif(Security::checkGetParameter('action', false, 'feeds'))
         {
            /*
             * show all rss sources.
             *
             */

            $this->m_rss->parseFeedArguments();

            return $this->m_view->showFeedList($this->m_rss->getFeedList());
         }
         elseif(Security::checkGetParameter('action', false, 'search'))
         {
            return 'search for posts in all feeds';
         }
         elseif(Security::checkGetParameter('action', false, 'update'))
         {
            $this->m_rss->updateFeeds();
         }
         else
         {
            $articles = $this->m_rss->getUnreadArticles();
            $numberOfArticles = count($articles);

            for($i = 0; $i < $numberOfArticles; $i++)
            {
               $articles[$i]['tags'] = $this->m_model->getArticleTags($articles[$i]['id']);
            }

            return $this->m_view->showArticleList($articles);
         }
      }

      /**
       * This function shows a list with all tags on the page.
       *
       * @return   string   Contains the html code of the list.
       *
       */
      public function showTags()
      {
         $userId = $this->m_session->get(SESSION_NAME_USERID);

         return $this->m_view->showTagList($this->m_model->getTagList($userId));
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