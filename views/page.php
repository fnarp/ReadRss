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
    * PageView class
    *
    * Contains the view layer for the PageController.
    *
    * @package    ReadRss
    * @subpackage View
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class PageView
   {
      /**
       * Constructor
       *
       */
      public function __construct()
      {
         // do noting
      }

      public function showSignIn($errorMessage, $user)
      {
         $tpl = new Template('templates/frontend/index.tpl');

         $tpl->assign('sitetitle', READRSS_SITETITLE);
         $tpl->assign('basepath', READRSS_BASEPATH);
         $tpl->assign('description', 'Online rss reader.');
         $tpl->assign('keywords', 'rss reader');

         if(empty($errorMessage))
         {
            $tpl->assign('error', '');
         }
         else
         {
            $tpl->assign('error', $errorMessage);
         }

         if(empty($user))
         {
            $tpl->assign('user', '');
         }
         else
         {
            $tpl->assign('user', $user);
         }

         return $tpl->getTemplate();
      }

      public function showOverview()
      {
         $tpl = new Template('templates/backend/index.tpl');

         $tpl->assign('sitetitle', READRSS_SITETITLE);
         $tpl->assign('basepath', READRSS_BASEPATH);
         $tpl->assign('description', 'Online rss reader.');
         $tpl->assign('keywords', 'rss reader');

         return $tpl->getTemplate();
      }
   }
?>