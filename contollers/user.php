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

   include_once 'models/user.php';

   /**
    * UserController Class
    *
    * This class contains all functions to deal with user related actions.
    *
    * @package    ReadRss
    * @subpackage Controller
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class UserController
   {
      /**
       * Contains the Database class instance.
       *
       * @var \MySQL
       *
       */
      private $m_database = null;

      /**
       * Contains the Session class instance.
       *
       * @var \Session
       *
       */
      private $m_session = null;

      /**
       * Contains the UserModel class instance.
       *
       * @var \UserModel
       *
       */
      private $m_model = null;

      /**
       * Class constructor
       *
       * @param \MySQL     $database   contains the database instance
       * @param \Session   $session    contains the session instance
       *
       * @return  void
       *
       */
      public function __construct($database, $session)
      {
         $this->m_database = $database;
         $this->m_session = $session;
         $this->m_model = new UserModel($this->m_database);

         Template::registerController(__CLASS__, $this);
      }

      /**
       * Tries to sign in a user with his email and his password.
       *
       * @param string  $error      contains the last error description
       * @param string  $email      contains the users mail address
       * @param string  $password   contains the users password
       * @param boolean $setCookie  contains wheter the aut-cookie should be set or not
       *
       * @return true   if the user was sign in successfully
       * @return false  if the credentials were wrong
       *
       */
      public function doSignIn(&$error, $email, $password, $setCookie)
      {
         $user = $this->m_model->getUserLoginData($email);

         if(null !== $user)
         {
            if(!$this->m_model->checkLoginCount($user['iduser']))
            {
               if(Security::checkPassword($email, $password, $user['password']))
               {
                  $this->m_session->set(SESSION_NAME_USERID, $user['iduser']);
                  $this->m_session->setSessionOwner($user['iduser']);

                  $this->m_model->addLoginCount($user['iduser'], true);

                  if($setCookie)
                  {
                     $this->renewAuthCookie();
                  }

                  return true;
               }

               $error = Template::getText('WrongPassword');

               return false;
            }

            $error = Template::getText('TooManyLoginTries');

            return false;
         }

         $error = Template::getText('UserNotExisting');

         return false;
      }

      /**
       * Tries to sing in a user with the client auth-cookie.
       *
       * @return  void
       *
       */
      public function doCookieSignIn()
      {
         // if the user is already logged in
         if(null !== $this->m_session->get(SESSION_NAME_USERID))
         {
            return;
         }

         $userId = null;
         $cookie = Cookie::get(COOKIE_NAME_AUTHENTIFICATION, $userId);

         if($cookie !== false)
         {
            if($this->m_model->checkCookieLogin($userId, $cookie))
            {
               $this->m_session->set(SESSION_NAME_USERID, $userId);
               $this->m_session->setSessionOwner($userId);
               $this->m_model->addLoginCount($userId, true);

               $this->renewAuthCookie();
            }
            else
            {
               Cookie::delete(COOKIE_NAME_AUTHENTIFICATION);
            }
         }
      }

      /**
       * Returns if the current user is sign in or not.
       *
       * @return true   if the user is sign in
       * @return false  if the user is not sign in
       *
       */
      public function isSignedIn()
      {
         if(null !== $this->m_session->get(SESSION_NAME_USERID))
         {
            return true;
         }

         return false;
      }

      /**
       * Sign out the current user.
       *
       * @return  void
       *
       */
      public function doSignOut()
      {
         // delete cookie entry in database
         $this->m_model->deleteAuthCookieSecret($this->m_session->get(SESSION_NAME_USERID));

         // close the session
         $this->m_session->close($this->m_session->getSessionId());

         // delete auth cookie
         Cookie::delete(COOKIE_NAME_AUTHENTIFICATION);

         // TODO: redirect to front page
      }

      /**
       * Recreates the aut-cookie to ensure that it stays valid.
       *
       * @return  void
       *
       */
      public function renewAuthCookie()
      {
         $userId = $this->m_session->get(SESSION_NAME_USERID);
         $secret = Security::generateRandomString(20);

         $this->m_model->saveAuthCookieSecret($userId, $secret);

         // delete old auth cookie
         Cookie::delete(COOKIE_NAME_AUTHENTIFICATION);

         // create the new auth cookie
         Cookie::set(COOKIE_NAME_AUTHENTIFICATION, $userId, COOKIE_TIMEOUT, $secret, $this->m_session->getSessionId());
      }

      /**
       * If no default language is defined in the session, it detects the browser language and saves it to the session.
       *
       * @return  void
       *
       */
      public function detectLanguage()
      {
         // TODO: perhaps there is a better solution.

         if(null !== $this->m_session->get(SESSION_NAME_LANGUAGE))
         {
            return;
         }

         $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

         if(in_array($language, Security::getAllowedLanguages()))
         {
            $this->m_session->set(SESSION_NAME_LANGUAGE, $language);
         }
         else
         {
            $this->m_session->set(SESSION_NAME_LANGUAGE, DEFAULT_LANGUAGE);
         }
      }

      /**
       * Sets the default language for the session.
       *
       * @param string  $language   contains the language code
       *
       * @return  void
       *
       */
      public function setLanguage($language)
      {
         $this->m_session->set(SESSION_NAME_LANGUAGE, $language);
      }
   }
?>