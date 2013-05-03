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
    * Session class
    *
    * This class provides a secure and fast way for saving session variables. It primary uses cookies for
    * identifying the user. If the user does not accept any cookies it falls back to the normal session.
    *
    * @package    ReadRss
    * @subpackage Core
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class Session
   {
      /**
       * MySQL class instance.
       *
       * @var \MySQL
       *
       */
      private $m_database = null;

      /**
       * Contains the session id.
       *
       * @var integer
       *
       */
      private $sessionId = null;

      /**
       * Contains the session data.
       *
       * @var array
       *
       */
      private $session = array();

      /**
       * Defines if the session was closed.
       *
       * @var bolean
       *
       */
      private $m_closed = false;

      /**
       * Initializes a new Session.
       *
       * @param   \MySQL   $database   Contains the database instance.
       *
       * @return   true    If the session was successfully created.
       * @return   false   If the session could not be created.
       *
       */
      public function __construct($database)
      {
         register_shutdown_function(array($this, 'write'));

         $this->m_database = $database;

         $cookie = Cookie::get(SESSION_KEY);

         if($cookie !== false)
         {
            $this->sessionId = $cookie;
         }
         elseif(isset($_SESSION[SESSION_KEY]))
         {
            $this->sessionId = $_SESSION[SESSION_KEY];
         }

         if($this->sessionId)
         {
            if($this->load($cookie) === true)
            {
               return true;
            }
         }

         $this->close();

         if($this->create())
         {
            return true;
         }

         Error::newError(Error::UNRECOVERABLE, ERROR_CREATING_SESSION);

         return false;
      }

      /**
       * Creates a new session.
       *
       * @return   true    If the session was successfully created.
       * @return   false   If the session could not be created.
       *
       */
      private function create()
      {
         $session_id = 0;

         $this->m_database->insert('session')
                            ->set('session_data', base64_encode(serialize($this->session)), 's')
                            ->set('activity', 'NOW()');

         if($this->m_database->executeInsert($session_id, true) === true)
         {
            $this->sessionId = $session_id;

            if(Cookie::set(SESSION_KEY, SESSION_DEFAULT_USER, SESSION_TIMEOUT, $this->sessionId, $this->sessionId))
            {
               return true;
            }

            $_SESSION[SESSION_KEY] = $this->sessionId;

            return true;
         }

         Error::newError(Error::UNRECOVERABLE, ERROR_SAVE_SESSION);

         return false;
      }

      /**
       * Loads a session from the database
       *
       * @param   integer   $sessionId   Contains the session id.
       *
       * @return   true    If the session was successfully loaded.
       * @return   false   If the session could not be loaded.
       *
       */
      private function load($sessionId)
      {
         $result = array();

         $this->m_database->select('fk_user', 'session_data', 'activity')
                            ->from('session', 's')
                            ->where('idsession = ' . $sessionId);

         if($this->m_database->executeSelect($result, true))
         {
            if((strtotime($result[0]['activity']) + SESSION_TIMEOUT) > time())
            {
               $this->m_database->update('session')
                                  ->set('activity', 'Now()')
                                  ->where('idsession = ' . $sessionId);

               if(!$this->m_database->executeUpdate(true))
               {
                  Error::newError(Error::UNRECOVERABLE, ERROR_UPDATE_SESSION);
               }

               $this->sessionId = $sessionId;
               $this->session = unserialize(base64_decode($result[0]['session_data']));

               if(Cookie::set(SESSION_KEY, SESSION_DEFAULT_USER, SESSION_TIMEOUT, $this->sessionId, $this->sessionId))
               {
                  return true;
               }

               $_SESSION[SESSION_KEY] = $this->sessionId;

               return true;
            }
         }

         self::close($sessionId);

         return false;
      }

      /**
       * This method is called by PHP (register_shutdown_function) and saves the session into the database.
       *
       */
      public function write()
      {
         if($this->m_closed)
         {
            return;
         }

         $this->m_database->cleanUp();

         $this->m_database->update('session')
                            ->set('session_data', base64_encode(serialize($this->session)), 's')
                            ->set('activity', 'Now()')
                            ->where('idsession = ' . $this->sessionId);

         if(!$this->m_database->executeUpdate(true))
         {
            Error::newError(Error::UNRECOVERABLE, ERROR_SAVE_SESSION);
         }
      }

      /**
       * This method destroys a session and cleans up.
       *
       * @param   integer   $session_id   Contains the session id.
       *
       */
      public function close($session_id = 0)
      {
         $this->m_closed = true;

         if($session_id !== 0)
         {
            $this->m_database->delete()
                               ->from('session', 's')
                               ->where('idsession = ' . $session_id);

            $this->m_database->executeDelete(true);
         }

         Cookie::delete(SESSION_KEY);

         if(isset($_SESSION[SESSION_KEY]))
         {
            unset($_SESSION[SESSION_KEY]);
         }

         $this->sessionId = null;
         $this->session = array();
      }

      /**
       * This method sets a session variable.
       *
       * @param   string   $key     Contains the variable name.
       * @param   mixed    $value   Contains the variavle value.
       *
       */
      public function set($key, $value)
      {
         $this->session[$key] = $value;
      }

      /**
       * This method returns the specified variable value.
       *
       * @param   string   $key   Contains the variable name.
       *
       * @return   mixed   Returns the session value.
       * @return   null    If an error occurred.
       *
       */
      public function get($key)
      {
         if(isset($this->session[$key]))
         {
            return $this->session[$key];
         }

         return null;
      }

      /**
       * This method delets a session variable.
       *
       * @param   string   $key   Contains the variable name.
       *
       */
      public function delete($key)
      {
         if(isset($this->session[$key]))
         {
            unset($this->session[$key]);
         }
      }

      /**
       * This method returns the session id.
       *
       * @return   integer   The session id.
       *
       */
      public function getSessionId()
      {
         return $this->sessionId;
      }

      /**
       * This method connects a user to a session.
       *
       * @param   integer   $user_id   Contains the user id.
       *
       */
      public function setSessionOwner($user_id)
      {
         $this->m_database->cleanUp();

         $this->m_database->update('session')
                            ->set('fk_user', $user_id)
                            ->set('activity', 'Now()')
                            ->where('idsession = ' . $this->sessionId);

         if(!$this->m_database->executeUpdate(true))
         {
            Error::newError(Error::UNRECOVERABLE, ERROR_SET_SESSION_OWNER);
         }
      }
   }
?>