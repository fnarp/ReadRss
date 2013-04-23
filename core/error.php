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
    * Error Class
    *
    * TODO: Description / Implement
    *
    * @package    ReadRss
    * @subpackage Core
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class Error
   {
      const NOTICE = 10001;
      const WARNING = 10002;
      const ERROR = 10003;
      const UNRECOVERABLE = 10004;

      public static function newError($type, $code, $message)
      {
         echo ("exception!");

         if($type === self::UNRECOVERABLE)
         {
            die();
         }
      }
   }
?>