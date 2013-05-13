<?php
/**
 * ReadRss
 *
 * An open source RSS collector and reader for PHP 5.4 or newer.
 *
 * @package  ReadRss
 * @author   Pascal Minder <pascal.minder@trustus.ch>
 * @license  http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 * @link     https://github.com/HotPizzaBox/readrss
 *
 */

   defined('SERVER_KEY') OR exit('No direct script access allowed');

   /**
    * PageModel class
    *
    * Contains the database abstraction layer for the PageController.
    *
    * @package    ReadRss
    * @subpackage Model
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class PageModel
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

      public function addTag($tagName, $userId)
      {
         $fk_tag = 0;

         if($this->checkExistingTag($tagName) === null)
         {
            $this->m_database->cleanUp();

            $this->m_database->insert('tag')
                               ->set('name', $tagName, 's');

            $this->m_database->executeInsert($fk_tag, true);
         }

         if($fk_tag !== 0 &&
            $this->checkExistingUserTag($tagName, $userId) === null)
         {
            $this->m_database->cleanUp();

            $this->m_database->insert('user_tag')
                               ->set('fk_user', $userId)
                               ->set('fk_tag', $fk_tag);

            $this->m_database->executeInsert($fk_tag, true);
         }
      }

      public function deleteTag($tagId, $userId)
      {
         $this->m_database->cleanUp();

         $this->m_database->delete()
                            ->from('user_tag', 'ut')
                            ->where('fk_user = ' . $userId)
                            ->where('fk_tag = ' . $tagId);

         $this->m_database->executeDelete();
      }

      public function getTagList($userId)
      {
         $result = array();
         $tags = array();

         $this->m_database->cleanUp();

         $this->m_database->select('t.idtag, t.name')
                            ->from('tag', 't')
                            ->join('t', 'user_tag', 'ut', 'ut.fk_tag = t.idtag')
                            ->where('ut.fk_user = ' . $userId)
                            ->orderBy('t.name', 'AC');

         $this->m_database->executeSelect($result);

         if(false !== $result)
         {
            foreach($result as $tag)
            {
               $tags[] = array('id' => $tag['idtag'], 'name' => $tag['name']);
            }
         }

         return $tags;
      }

      public function getArticleTags($articleId)
      {
         $result = array();
         $tags = array();

         $this->m_database->cleanUp();

         $this->m_database->select()
                            ->from('tag', 't')
                            ->join('t', 'user_tag', 'ut', 'ut.fk_tag = t.idtag')
                            ->join('t', 'article_state_tag', 'ast', 'ast.fk_user_tag = ut.iduser_tag')
                            ->join('t', 'article_state', 'a_s', 'a_s.idarticle_state = ast.fk_article_state')
                            ->where('a_s.idarticle_state = ' . $articleId)
                            ->orderBy('t.name', 'AC');

         $this->m_database->executeSelect($result);

         if(false !== $result)
         {
            foreach($result as $tag)
            {
               $tags[] = array('id' => $tag['idtag'], 'name' => $tag['name']);
            }
         }

         return $tags;
      }

      private function checkExistingTag($tagName)
      {
         $result = null;

         $this->m_database->cleanUp();

         $this->m_database->select('t.name')
                            ->from('tag', 't')
                            ->join('t', 'user_tag', 'ut', 'ut.fk_tag = t.idtag')
                            ->where('t.name = "' . $tagName . '"')
                            ->orderBy('t.name', 'AC');

         $this->m_database->executeSelect($result);

         if($result !== false)
         {
            return $result[0]['idtag'];
         }

         return null;
      }

      private function checkExistingUserTag($tagName, $userId)
      {
         $result = null;

         $this->m_database->cleanUp();

         $this->m_database->select('t.name')
                            ->from('tag', 't')
                            ->join('t', 'user_tag', 'ut', 'ut.fk_tag = t.idtag')
                            ->where('ut.fk_user = ' . $userId)
                            ->where('t.name = "' . $tagName . '"')
                            ->orderBy('t.name', 'AC');

         $this->m_database->executeSelect($result);

         if($result !== false)
         {
            return $result[0]['idtag'];
         }

         return null;
      }
   }
?>