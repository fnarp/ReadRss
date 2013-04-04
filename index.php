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

   // show all errors
   error_reporting(-1);
   ini_set('display_errors', 1);

   // include needed files
   require_once 'core/config.php';
   require_once 'core/benchmark.php';
   require_once 'core/mysql.php';
   require_once 'core/cookie.php';
   require_once 'core/session.php';

   /*
    * Start the timer...
    */
   $benchmark = new Benchmark();

   /*
    * Create first time-point
    */
   $benchmark->mark('total_execution_time_start');

   /*
    * Connect to the database...
    */
   $database = new MySQL(DATABASE_HOST, DATABASE_USER, DATABASE_PWD, DATABASE_NAME);

   /*
    * Start and inititialize the session.
    */
   $session = new Session($database);

   // show page

   /*
    * Show the Benchmark output.
    */
   echo $benchmark->elapsed_time('total_execution_time_start');
?>