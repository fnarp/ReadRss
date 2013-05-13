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
       * This function creates and returns a token.
       *
       * @static
       *
       * @param   bool   $new    If true, forces the function to create a new token.
       *
       * @return   string   Returns the new token.
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
       * This function checks if the given token is valid.
       *
       * @static
       *
       * @param   string   $token   Contains the token to be checked.
       *
       * @return   true    If the token is valid.
       * @return   false   If the token is invalid.
       */
      public static function checkToken($token)
      {
         return $token === Session::getInstance()->get('token');
      }

      /**
       * Encrypts a password for the database.
       *
       * @static
       *
       * @param   string   $mail       Contains the user's mail.
       * @param   string   $password   Contains the user's password.
       * @param   string   $salt       Contains the user specife salt.
       * @param   string   $rounds     Contains the number of rounds for crypt.
       *
       * @return   string   Returns the encrypted password.
       *
       */
      public static function encryptPassword($mail, $password, $salt, $rounds='08')
      {
         $length = strlen($password) * 4;
         $data = str_pad($password, $length, sha1($mail), STR_PAD_BOTH);
         $string = hash_hmac('whirlpool', $data, SERVER_KEY, true);

         return crypt($string, '$2a$' . $rounds . '$' . $salt);
      }

      /**
       * Checks if the given password is valid.
       *
       * @static
       *
       * @param   string   $mail       Contains the user's mail.
       * @param   string   $password   Contains the user's password.
       * @param   string   $stored     Contains the stored password.
       *
       * @return   true   If the password was valid.
       * @return   false  If the password was invalid.
       *
       */
      public static function checkPassword($mail, $password, $stored)
      {
         $length = strlen($password) * 4;
         $data = str_pad($password, $length, sha1($mail), STR_PAD_BOTH);
         $string = hash_hmac ('whirlpool', $data, SERVER_KEY, true);

         return (crypt($string, substr($stored, 0, 30)) === $stored);
      }

      /**
       * Creats a new password with the given parameters.
       *
       * @static
       *
       * @param   integer   $length   Contains the password's lenght.
       *
       * @return   string   Returns the generated password.
       *
       */
      public static function generateRandomString($length)
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
       * @static
       *
       * @return   array   Returns an array with all available languages.
       *
       */
      public static function getAllowedLanguages()
      {
         return array('en', 'de');
      }

      /*
       * This function modifies a string by the regex (alphanumeric).
       *
       * @static
       *
       * @param   string   $input    Contains the string which will be checked.
       * @param   int      $lenght   Contains the maximum lenght of input.
       * @param   int      $count    Contains the number of replacements which were made during runtime.
       *
       * @return   string   Returns the checked string.
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

      public static function url($input)
      {
         // pattern by diegoperini
         $pattern = '_^(?:(?:https?)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS';

         return preg_match($pattern, $input);
      }

      /**
       * This function recreates the $_GET array. While doing this it removes all bad characters.
       *
       * @static
       *
       */
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

      /**
       * Checks if a $_GET parameter is set.
       *
       * @static
       *
       * @param   string   $name           Contains the parameter's name.
       * @param   bool     $emptyAllowed   Defines if the parameter is allowed to be empty.
       * @param   string   $definedValue   Defines if the parameter neeed a defined value.
       *
       * @return   true    If all requirements are met.
       * @return   false   If one requirement is not met.
       *
       */
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

      /**
       * Checks if a $_POST parameter is set.
       *
       * @static
       *
       * @param   string   $name           Contains the parameter's name.
       * @param   bool     $emptyAllowed   Defines if the parameter is allowed to be empty.
       * @param   string   $definedValue   Defines if the parameter neeed a defined value.
       *
       * @return   true    If all requirements are met.
       * @return   false   If one requirement is not met.
       *
       */
      public static function checkPostParameter($name, $emptyAllowed=true, $definedValue=NULL)
      {
         if(isset($_POST[$name]))
         {
            if(!$emptyAllowed)
            {
               if(empty($_POST[$name]))
               {
                  return false;
               }
            }

            if($definedValue !== NULL)
            {
               if($definedValue !== $_POST[$name])
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

      public static function getGetParameter($name)
      {
         // TODO: extend
         if(isset($_GET[$name]))
            return $_GET[$name];

         return '';
      }
   }
?>
