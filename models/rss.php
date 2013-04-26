<?php
/**
 * social+
 *
 * An open source social network for PHP 5.4 or newer
 *
 * @package  SocialPlus
 * @author   Pascal Minder <pascal.minder@trustus.ch>
 * @author   Sandro Roth <sandro.roth@leuchterag.ch>
 * @license  http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 * @link     https://github.com/HotPizzaBox/socialplus
 *
 */

   defined('SERVER_KEY') OR exit('No direct script access allowed');

   /**
    * RssModel class
    *
    * Contains the database abstraction layer for the RssController.
    *
    * @package    SocialPlus
    * @subpackage Model
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class RssModel
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
   }
?>