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
    * MySQL class
    *
    * This class provides functions to protect social+.
    *
    * @package    ReadRss
    * @subpackage Core
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class Security
   {
      /**
       * Encrypts a password for the database.
       *
       * @param   string   $password   contains the password
       * @param   string   $salt       contains the salt
       *
       * @return  string   contians the encrypted password
       *
       */
      public static function encryptPassword($password, $salt)
      {
         $md5_pass = $password.$salt;

         for($i = 0; $i < 1000; ++$i)
         {
            $md5_pass = md5($md5_pass);
         }

         return $md5_pass;
      }

      /**
       * Creats a new password with the given parameters.
       *
       * @param   string   $length   contains the lenght of the password
       *
       * @return  string   contains a new generated password
       *
       */
      public static function generatePassword($length)
      {
         $upperLetter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
         $lowerLetter = 'abcdefghijklmnopqrstuvwxyz';
         $digits = '0123456789';
         $characters = $upperLetter.$digits.$lowerLetter;

         $password = '';

         for($i = 0; $i < $length; ++$i)
         {
            $password .= substr($characters, (mt_rand() % (strlen($characters))), 1);
         }

         return $password;
      }

      /**
       * Returns all available languages.
       *
       * @return  array   contains all available languages.
       *
       */
      public static function getAllowedLanguages()
      {
         return array('en', 'de');
      }
   }
?>
