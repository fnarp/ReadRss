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
    * Template class
    *
    * This class provides a very easy template system implementation.
    *
    * @package    ReadRss
    * @subpackage Core
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class Template
   {
      /**
       * loaded language
       *
       * @var array
       *
       */
      private static $m_language = array();

      /**
       * language status
       *
       * @var boolean
       *
       */
      private static $m_isLoaded = false;

      /**
       * registered controllers
       *
       * @var array
       *
       */
      private static $m_controllers = array();

      /**
       * template file
       *
       * @var string
       *
       */
      private $template = '';

      /**
       * placeholder allocations
       *
       * @var array
       *
       */
      private $allocations = array();

      /**
       * Constructor - loads a template file
       *
       * @param   string   $path   Contains the path to the template file.
       *
       */
      public function __construct($path)
      {
         if(file_exists($path))
         {
            $this->template = file_get_contents($path);
         }
         else
         {
            Error::newError(Error::ERROR, ERROR_TEMPLATEFILE_NOT_FOUND);
         }
      }

      /**
       * Adds a new placeholder allocation.
       *
       * @param   string   $name    Placeholder name.
       * @param   string   $value   Placeholder value.
       *
       */
      public function assign($name, $value)
      {
         $this->allocations[$name] = $value;
      }

      /**
       * Clears the allocation array.
       *
       */
      public function clear()
      {
         $this->allocations = array();
      }

      /**
       * Creates and returns the parsed template string.
       *
       * @return   string   Ready to use template.
       *
       */
      public function getTemplate()
      {
         $templateText = $this->template;
         $modifiers = $this->findTemplateModifiers();

         foreach($modifiers as $mod)
         {
            if($mod['type'] === 'ctr')
            {
               $templateText = str_replace('{'.$mod['text'].'}', $this->callController($mod['class'], $mod['function'], $mod['parameters']), $templateText);
            }
            elseif($mod['type'] === 'trl')
            {
               $templateText = str_replace('{'.$mod['text'].'}', self::getText($mod['name']), $templateText);
            }
            else
            {
               $templateText = str_replace('{'.$mod['text'].'}', $this->allocations[$mod['name']], $templateText);
            }
         }

         return $templateText;
      }

      /**
       * Finds all placeholder in the template.
       *
       * @return   array   The position of all the placeholder.
       *
       */
      private function findTemplateModifiers()
      {
         $modifiers = array();
         $numberOfModifier = substr_count($this->template, '{');
         $positionInString = -1;

         for($i = 0; $i < $numberOfModifier; ++$i)
         {
            $modifier = array();

            $positionInString = strpos($this->template, '{' , ++$positionInString);

            $start = $positionInString;

            $positionInString = strpos($this->template, '}' , ++$positionInString);

            $end = $positionInString;

            $modifier['text'] = substr($this->template, $start + 1, $end - ($start + 1));

            $fields = explode(';', $modifier['text']);

            $modifier['type'] = $fields[0];

            if($modifier['type'] === 'ctr')
            {
               $modifier['class'] = $fields[1];
               $modifier['function'] = $fields[2];
               $modifier['parameters'] = $fields[3];
            }
            elseif($modifier['type'] === 'trl')
            {
               $modifier['name'] = $fields[1];
            }
            else
            {
               $modifier['name'] = $fields[1];
            }

            $modifiers[] = $modifier;
         }

         return $modifiers;
      }

      /**
       * Calls a controller which was referenced in the template file.
       *
       * @param   string   $controller   Contains the class' name.
       * @param   string   $method       Contains the class method's name.
       * @param   string   $argument     Contains the arguments which will be passed to the function.
       *
       * @return   string   Returns method output.
       *
       */
      private function callController($controller, $method, $argument)
      {
         if(isset(self::$m_controllers[$controller]))
         {
            if('' !== $argument)
            {
               return self::$m_controllers[$controller]->$method($argument);
            }
            else
            {
               return self::$m_controllers[$controller]->$method();
            }
         }

         ErrorHandler::report(ErrorHandler::Unrecoverable, ERROR_CONTROLLER.$controller, __FILE__, __LINE__);

         return '';
      }

      /**
       * Registers a controller which can be referenced in a template file.
       *
       * @param   string   $name       Contains the class' name.
       * @param   object   $instance   Contains the class instance.
       *
       */
      public static function registerController($name, $instance)
      {
         if(!isset(self::$m_controllers[$name]))
         {
            self::$m_controllers[$name] = $instance;
         }
      }

      /**
       * Loads a language file.
       *
       * @param   string   $language   Contains the language abbreviation.
       *
       */
      public static function loadLanguage($language)
      {
         include_once '/../language/'. $language . '.php';

         self::$m_language = $lang;
         self::$m_isLoaded = true;
      }

      /**
       * Returns a text in the loaded language.
       *
       * @param   string   $name   Contains the name
       *
       * @return string
       *
       */
      public static function getText($name)
      {
         if(self::$m_isLoaded === false)
         {
            self::loadLanguage(DEFAULT_LANGUAGE);
         }

         return self::$m_language[$name];
      }
   }
?>
