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

      public function getTagList($userId)
      {
         $result = array();
         $tags = array();

         $this->m_database->cleanUp();

         $this->m_database->select('name')
                            ->from('tag', 't')
                            ->where('fk_user = ' . $userId)
                            ->orderBy('name', 'AC');

         $this->m_database->executeSelect($result);

         if(false !== $result)
         {
            foreach($result as $tag)
            {
               $tags[] = $tag['name'];
            }
         }

         return $tags;
      }
   }
?>