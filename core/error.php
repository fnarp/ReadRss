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
    * Error Class
    *
    * Provides an error reporting class for ReadRss.
    *
    * @package    ReadRss
    * @subpackage Core
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class Error
   {
      /**
       * Defines a const value for notice messages.
       */
      const NOTICE = 10001;
      
      /**
       * Defines a const value for warning messages.
       */
      const WARNING = 10002;
      
      /**
       * Defines a const value for error messages.
       */
      const ERROR = 10003;
      
      /**
       * Defines a const value for unrecoverable error messages.
       */
      const UNRECOVERABLE = 10004;

      /**
       * Method to report an error or something.
       * 
       * @static
       * 
       * @param   const    $type      Defines the type of reporting.
       * @param   string   $message   Contains the message that will be reported.
       * 
       */
      public static function newError($type, $message)
      {
         // TODO: implement
         echo ($message);

         if($type === self::UNRECOVERABLE)
         {
            die();
         }
      }
   }
?>