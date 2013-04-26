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
    * Security class
    *
    * This class provides functions to protect ReadRss.
    *
    * @package    ReadRss
    * @subpackage Core
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class Security
   {
      /**
       * This function creats and returns a token.
       *
       * @param   bool  $new   decides if a new token will be created
       *
       * @return  string
       */
      public static function token($new = false)
      {
         $token = Session::getInstance()->get('token');

         if($new | null === $token)
         {
            $token = sha1(uniqid(null, true));

            Session::set('token', $token);
         }

         return $token;
      }

      /*
       * This function checks the given token.
       *
       * @param   string   $token   contains the token to be tested
       *
       * @return  boolean
       */
      public static function checkToken($token)
      {
         return $token === Session::getInstance()->get('token');
      }

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

      /*
       * This function modifies a string by the RegExp (alphanumeric)
       *
       * @param   string   $input   contains the string
       * @param   int      $lenght  contains the maximum lenght of the string
       * @param   int      $count   contains the number of replacements during the runtime
       *
       * @return  string
       */
      public static function alphanumeric($input, $lenght=null, &$count=null)
      {
         $input = preg_replace('/[[:alnum:]]/', '', $input, -1, $count);

         if(null !== $lenght)
         {
            return substr($input, 0, $lenght);
         }

         return $input;
      }

      public static function cleanGetParameters()
      {
         $requestUrl = $_SERVER['QUERY_STRING'];
         $_GET = array();

         $parameters = explode('&', $requestUrl);

         if(count($parameters) && $parameters[0] !== '')
         {
            foreach ($parameters as $parameter)
            {
               list($key, $value) = explode('=', $parameter);

               if(isset($value))
               {
                  Security::alphanumeric($value);
                  $_GET[$key] = $value;
               }
            }
         }
      }

      public static function checkGetParameter($name, $emptyAllowed=true, $definedValue=NULL)
      {
         if(isset($_GET[$name]))
         {
            if(!$emptyAllowed)
            {
               if(empty($_GET[$name]))
               {
                  return false;
               }
            }

            if($definedValue !== NULL)
            {
               if($definedValue !== $_GET[$name])
               {
                  return false;
               }
            }

            return true;
         }

         return false;
      }

      public static function getPostParameter($name)
      {
         // TODO: extend
         if(isset($_POST[$name]))
            return $_POST[$name];

         return '';
      }
   }
?>
