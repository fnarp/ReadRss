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

      public function showTagList($tags)
      {
         $tpl = new Template('templates/backend/templates/tag_item.tpl');
         $html = '';

         foreach($tags as $tag)
         {
            $tpl->assign('id', $tag['id']);
            $tpl->assign('name', $tag['name']);

            $html .= $tpl->getTemplate();
         }

         return $html;
      }

      public function showFeedList($items)
      {
         $tplList = new Template('templates/backend/templates/feed_list.tpl');
         $tplItem = new Template('templates/backend/templates/feed_list_item.tpl');
         $itemsList = '';

         foreach($items as $item)
         {
            $tplItem->assign('name', $item['title']);
            $tplItem->assign('uniqueId', $item['uId']);

            $itemsList .= $tplItem->getTemplate();
         }

         $tplList->assign('items', $itemsList);

         return $tplList->getTemplate();
      }

      public function showArticleList($articles)
      {
         $tpl = new Template('templates/backend/templates/article_preview.tpl');
         $html = '';

         foreach($articles as $article)
         {
            $tagHtml = '';
            $phpdate = strtotime($article['date']);
            
            $hours = 1;
            $days = (1);
            $months = 1;
            $out = '';
            
            $diff = time() - $phpdate;
            
            $hours = $diff / 3600;
            
            if($hours < 24)
            {
               $out = ' (' . floor($hours) . ' hours ago)';
            }
            elseif(($days = ($hours / 24)) < 31)
            {
               $out = ' (' . floor($days) . ' days ago)';
            }
            elseif(($months = ($days / 31)) < 12)
            {
               $out = ' (' . floor($months) . ' days ago)';
            }
            else
            {
               $out = ' (' . floor($months) / 12 . ' days ago)';
            }
            
            $tpl->assign('title', $article['title']);
            $tpl->assign('date', date('D, d. M Y H:i', $phpdate) . $out);
            $tpl->assign('preview', $article['preview']);

            foreach($article['tags'] as $tag)
            {
               // TODO: to template
               $tagHtml .= '<a href="?show=tags&amp;tag=' . $tag['id'] . '">' . $tag['name'] . '</a>';
            }

            $tpl->assign('articleTags', $tagHtml);

            $html .= $tpl->getTemplate();
         }

         return $html;
      }
   }
?>