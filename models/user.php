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
    * UserModel class
    *
    * Contains the database abstraction layer for the UserController.
    *
    * @package    ReadRss
    * @subpackage Model
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class UserModel
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

      /**
       * Checks if the auth-cookie is valid in the database.
       *
       * @param string $userId   contains the user id
       * @param string $secret   contains the auth-cookie secret
       *
       * @return true   if the auth-cookie is valid
       * @return false  if the auth-cookie was invalid
       *
       */
      public function checkCookieLogin($userId, $secret)
      {
         $this->m_database->cleanUp();

         $this->m_database->count('cookie_auth')
                            ->where('fk_user=' . $userId)
                            ->where('secret="' . $secret . '"')
                            ->where('last_activity BETWEEN DATE_SUB(NOW(), INTERVAL 1 WEEK) AND NOW()');

         $result = $this->m_database->executeCount();

         if(is_array($result) && count($result) === 1)
         {
            return true;
         }

         return false;
      }

      /**
       * Saves the auth-cookie secret to the database.
       *
       * @param string $userId   contains the user id
       * @param string $secret   contains the auth-cookie secret
       *
       * @return void
       *
       */
      public function saveAuthCookieSecret($userId, $secret)
      {
         $id = 0;

         $this->m_database->cleanUp();

         $this->m_database->delete()
                            ->from('cookie_auth', 'c')
                            ->where('fk_user=' . $userId);

         $this->m_database->executeDelete(true);

         $this->m_database->insert('cookie_auth')
                            ->set('fk_user', $userId, 's')
                            ->set('secret', $secret, 's')
                            ->set('last_activity', 'NOW()');

         $this->m_database->executeInsert($id, true);
      }

      /**
       * Delets all auth-cookie secrets for a user.
       *
       * @param string $userId   contains the user id
       *
       * @return void
       *
       */
      public function deleteAuthCookieSecret($userId)
      {
         $this->m_database->cleanUp();

         $this->m_database->delete()
                            ->from('cookie_auth', 'c')
                            ->where('fk_user=' . $userId);

         $this->m_database->executeDelete(true);
      }

      /**
       * Returns all relevant data about a user for the login process.
       *
       * @param string $email contains the users email
       *
       * @return null   if no user was found with the given email
       * @return array  if a user was found
       *
       */
      public function getUserLoginData($email)
      {
         $result = null;

         $this->m_database->cleanUp();

         $this->m_database->select('iduser', 'email', 'password', 'salt')
                            ->from('user', 'u')
                            ->where('email="' . $email . '"')
                            ->where('activated=1')
                            ->where('banned=0');

         $this->m_database->executeSelect($result);

         if($result !== false)
         {
            return $result[0];
         }

         return null;
      }

      /**
       * Checks if there were too many failed login attempts.
       *
       * @param string $userId   contains the user id
       *
       * @return true   if there were too many failed login attempts
       * @return false  if every thing is okay
       *
       */
      public function checkLoginCount($userId)
      {
         $this->m_database->cleanUp();

         $this->m_database->count('sign_in_count')
                            ->where('fk_user=' . $userId)
                            ->where('state=0')
                            ->where('time BETWEEN DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND NOW()');

         $result = $this->m_database->executeCount();
         
         if($result >= MAX_LOGIN_TRYES)
         {
            return true;
         }

         return false;
      }

      /**
       * Adds a login try.
       *
       * @param string $userId   contains the user id
       * @param bool $success    true if the login attempt was okay, false if not
       *
       * @return void
       *
       */
      public function addLoginCount($userId, $success)
      {
         $id = 0;

         $this->m_database->cleanUp();

         $this->m_database->insert('sign_in_count')
                            ->set('fk_user', $userId)
                            ->set('time', 'NOW()')
                            ->set('state', $success);

         $this->m_database->executeInsert($id, true);
      }
   }
?>