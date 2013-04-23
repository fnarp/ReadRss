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
    * Cookie class
    *
    * This class provides a secure cookie class.
    *
    * @package    ReadRss
    * @subpackage Core
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class Cookie
   {
      /**
       * Sets a new cookie on the visitors computer.
       *
       * @static
       *
       * @param string  $key        contains the cookie name
       * @param string  $user       contains the username
       * @param integer $timeout    contains the validity period in seconds
       * @param string  $data       contains the string which will be saved
       * @param integer $sessionId  contains the current session id
       *
       * @return true   if the cookie was set
       * @return false  if the cookie could not be set
       *
       */
      public static function set($key, $user, $timeout, $data, $sessionId)
      {
         $timeout += time();

         $cookieHead = $user . '~' . $timeout;
         $cookieBody = $user . '~' . $timeout . '~' . $data . '~' . $sessionId;

         // calculate user key and checksum for integrity check
         $userKey = hash_hmac('md5', $cookieHead, SERVER_KEY);
         $checksum = hash_hmac('md5', $cookieBody, $userKey);

         // encrypt data ($cookie_body) with the user key
         $cookieBody = mcrypt_encrypt(MCRYPT_BLOWFISH, $userKey, $cookieBody, MCRYPT_MODE_ECB);

         $cookieValue = $cookieHead . '~' . base64_encode($cookieBody) . '~' . $checksum;

         if(setcookie($key, $cookieValue, $timeout, '/', COOKIE_DOMAIN, true, true))
         {
            if(!isset($_COOKIE[$key]))
            {
               return false;
            }

            return true;
         }

         return false;
      }

      /**
       * Returns the cookie value if it exists.
       *
       * @static
       *
       * @param string  $key     contains the cookie name
       * @param int     $userId  contains the assigned user id
       *
       * @return  string   if the cookie could be read
       * @return  false    if the cookie was invalid
       *
       */
      public static function get($key, &$userId=null)
      {
         if(!isset($_COOKIE[$key]))
         {
            return false;
         }

         // get the cookie
         $cookie = $_COOKIE[$key];

         // seperate fields
         $rawCookie = explode('~', $cookie);

         if(time() > $rawCookie[1])
         {
            return false;
         }

         // calculate user key for decryption
         $userKey = hash_hmac('md5', $rawCookie[0] . '~' . $rawCookie[1], SERVER_KEY);

         // return user Id
         if($userId !== null)
         {
            $userId = $rawCookie[0];
         }

         // decrypt the data
         // Rijndael is a block cypher, which means that it operates on chunks of data of a particular length. So
         // this functions fills it with %00. We have to remove it manually.
         $decryptedData = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $userKey, base64_decode($rawCookie[2]), MCRYPT_MODE_ECB));

         // calculate checksum
         $checksum = hash_hmac('md5', $decryptedData, $userKey);

         if($checksum === $rawCookie[3])
         {
            $rawInput = explode('~', $decryptedData);

            return $rawInput[2];
         }

         self::delete($key);

         return false;
      }

      /**
       * Deletes a cookie.
       *
       * @static
       *
       * @param string  $key  contains the cookie name
       *
       * @return  void
       *
       */
      public static function delete($key)
      {
         unset($_COOKIE[$key]);

         Cookie::set($key, '', -86400, '', 0);
      }
   }
?>