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
    * MySQL class
    *
    * This class provides an abstract mysql interface.
    *
    * @package    ReadRss
    * @subpackage Core
    * @author     Pascal Minder <pascal.minder@trustus.ch>
    *
    */
   class MySQL
   {
      /* The query types. */
      const SELECT = 0;
      const DELETE = 1;
      const UPDATE = 2;
      const INSERT = 3;
      const COUNT = 4;

      /**
       * mysqli instance
       *
       * @var mysqli
       *
       */
      private $database = null;

      /**
       * The type of query this is. Can be select, update or delete.
       *
       * @var integer
       *
       */
      private $type = self::SELECT;

      /**
       * The state of the query object. Can be dirty or clean.
       *
       * @var integer
       *
       */
      private $isDirty = true;

      /**
       * The array of SQL parts collected.
       *
       * @var array
       *
       */
      private $sqlParts = array(
                           'select'  => array(),
                           'insert'  => array(),
                           'update'  => array(),
                           'count'   => array(),
                           'from'    => array(),
                           'join'    => array(),
                           'where'   => array(),
                           'groupBy' => array(),
                           'having'  => array(),
                           'orderBy' => array(),
                           'limit'   => array(),
                           'set'     => array()
                           );

      /**
       * Initializes a new MySQL connection.
       *
       * @param string $host     contains the database servers host name
       * @param string $user     contains the username
       * @param string $password contains the password
       * @param string $database contains the database name
       *
       * @return true   if connection could be made
       * @return false  if an error occurred
       *
       */
      public function __construct($host, $user, $password, $database)
      {
         $this->database = new mysqli($host, $user, $password, $database);

         if (mysqli_connect_errno())
         {
            Error::newError(Error::UNRECOVERABLE, ERROR_CONNECT_DATABASE . mysqli_connect_errno());
         }
      }

      /**
       * Disconnect from the database server.
       *
       */
      public function disconnect()
      {
         if(null === $this->database->connect_error)
         {
            $this->database->close();

            $this->database = null;
         }
      }

      /**
       * Starts a new select query and specifies the database columns to select.
       *
       * @param string|array $select contains the column names you want to select
       *
       * @return MySQL the class instance
       *
       */
      public function select($select = null)
      {
         $this->type = self::SELECT;
         $this->isDirty = false;

         $selects = is_array($select) ? $select : func_get_args();

         return $this->add('select', $selects);
      }

      /**
       * This method starts a count query.
       *
       * @param string $table contains the table name you want to operate on
       *
       * @return MySQL the class instance
       *
       */
      public function count($table)
      {
         $this->type = self::COUNT;
         $this->isDirty = false;

         return $this->add('count', $table);
      }

      /**
       * This method starts an insert query.
       *
       * @param string $table contains the table name
       *
       * @return MySQL the class instance
       *
       */
      public function insert($table)
      {
         $this->type = self::INSERT;
         $this->isDirty = false;

         return $this->add('insert', $table);
      }

      /**
       * This method starts an update query.
       *
       * @param string $table contains the table name
       *
       * @return MySQL the class instance
       *
       */
      public function update($table)
      {
         $this->type = self::UPDATE;
         $this->isDirty = false;

         return $this->add('update', $table);
      }

      /**
       * This method starts a delete query.
       *
       * @return MySQL the class instance
       *
       */
      public function delete()
      {
         $this->type = self::DELETE;
         $this->isDirty = false;

         return $this;
      }

      /**
       * Create and add a query root corresponding to the table identified by the given alias.
       *
       * @param string $key   contains the table name
       * @param string $alias contains the alias
       *
       * @return MySQL the class instance
       *
       */
      public function from($key, $alias)
      {
         return $this->add('from', array('table' => $key, 'alias' => $alias), true);
      }

      /**
       * Creates and adds a join to the query.
       *
       * @param string $parent_key  the alias that points to a from clause
       * @param string $key         the table name to join
       * @param string $alias       the alias of the join table
       * @param string $condition   the condition to join the table
       *
       * @return MySQL the class instance
       *
       */
      public function join($parent_key, $key, $alias, $condition)
      {
         return $this->add('join', array('key' => $parent_key, 'table' => $key, 'alias' => $alias, 'condition' => $condition), true);
      }

      /**
       * Specifies one or more restrictions to the query result.
       *
       * @param string $condition the condition
       * @param integer $group the group (enclosed by parentheses)
       * @param string $type the connection type (A = and / O = or)
       *
       * @return MySQL the class instance
       *
       */
      public function where($condition, $group = 0, $type='A')
      {
         return $this->add('where', array('type' => $type, 'condition' => $condition, 'key' => $group), true);
      }

      /**
       * Specifies a grouping over the results of the query.
       *
       * @param string $groupBy contains the column name for grouping
       *
       * @return MySQL the class instance
       *
       */
      public function groupBy($groupBy)
      {
         $groupBy = is_array($groupBy) ? $groupBy : func_get_args();

         return $this->add('groupBy', $groupBy);
      }

      /**
       * Specifies a restriction over the groups of the query.
       *
       * @param string $condition The restriction over the groups.
       * @param string $type the connection type (A = and / O = or)
       *
       * @return MySQL the class instance
       *
       */
      public function having($condition, $type='A')
      {
         return $this->add('having', array('type' => $type, 'condition' => $condition), true);
      }

      /**
       * Specifies an ordering for the query results.
       *
       * @param string $key the column name
       * @param string $order the order type (AC = ascending / DC = descending)
       *
       * @return MySQL the class instance
       *
       */
      public function orderBy($key, $order='AC')
      {
         return $this->add('orderBy', array('table' => $key, 'order' => $order), true);
      }

      /**
       * Adds a limit to the query.
       *
       * @param int $from contains the start number
       * @param int $max contains the number of tuples to select
       *
       */
      public function limit($from, $max)
      {
         $this->sqlParts['limit'] = array($from, $max);
      }

      /**
       * Sets a new value for a column in a insert or update query.
       *
       * @param string $key contains the column name
       * @param mixed $value contains the new value
       * @param mixed $type set to 's' if it is a string
       *
       * @return MySQL the class instance
       *
       */
      public function set($key, $value, $type=0)
      {
         return $this->add('set', array('field' => $key, 'value' => $value, 'type' => $type), true);
      }

      /**
       * Appends a single query part to the SQL part array.
       *
       * @param string $sqlPartKey contains the sql part array key
       * @param mixed $keys contains the query parts to add
       * @param boolean $append contains a switch for append or replace
       *
       * @return MySQL the class instance
       *
       */
      private function add($sqlPartKey, $keys, $append=false)
      {
         if($append)
         {
            if(isset($keys['key']))
            {
               $this->sqlParts[$sqlPartKey][$keys['key']][] = $keys;
            }
            else
            {
               $this->sqlParts[$sqlPartKey][] = $keys;
            }
         }
         else
         {
            if(is_array($keys))
            {
               foreach ($keys as $value)
               {
                  $this->sqlParts[$sqlPartKey][] = $value;
               }
            }
            else
            {
               $this->sqlParts[$sqlPartKey][] = $keys;
            }
         }

         return $this;
      }

      /**
       * This method executs a defined select query and returns the results.
       *
       * @param array $result contains the query result
       * @param boolean $autocleanup indicates whether the class should clean up after executing the query
       *
       * @return true   if the query was successfully executed
       * @return false  if an error occurred
       *
       */
      public function executeSelect(&$result, $autocleanup=false)
      {
         if(self::SELECT === $this->type)
         {
            $query = $this->getSelectSQL();

            if($this->isDirty == false)
            {
               return $this->executeQuery($query, $autocleanup, $result);
            }

            return false;
         }

         return false;
      }

      /**
       * This method executs a defined delete query.
       *
       * @param boolean $autocleanup indicates whether the class should clean up after executing the query
       *
       * @return true   if the query was successfully executed
       * @return false  if an error occurred
       *
       */
      public function executeDelete($autocleanup=false)
      {
         if(self::DELETE === $this->type)
         {
            $query = $this->getDeleteSQL();

            if($this->isDirty == false)
            {
               return $this->executeQuery($query, $autocleanup);
            }

            return false;
         }

         return false;
      }

      /**
       * This method executs a defined update query.
       *
       * @param boolean $autocleanup indicates whether the class should clean up after executing the query
       *
       * @return true   if the query was successfully executed
       * @return false  if an error occurred
       *
       */
      public function executeUpdate($autocleanup=false)
      {
         if(self::UPDATE === $this->type)
         {
            $query = $this->getUpdateSQL();

            if($this->isDirty == false)
            {
               return $this->executeQuery($query, $autocleanup);
            }

            return false;
         }

         return false;
      }

      /**
       * This method executs a defined insert query.
       *
       * @param integer $database_id contains the primary key of the inserted database row
       * @param boolean $autocleanup indicates whether the class should clean up after executing the query
       *
       * @return true   if the query was successfully executed
       * @return false  if an error occurred
       *
       */
      public function executeInsert(&$database_id, $autocleanup=false)
      {
         if(self::INSERT === $this->type)
         {
            $query = $this->getInsertSQL();

            if($this->isDirty == false)
            {
               return $this->executeQuery($query, $autocleanup, $database_id);
            }

            return false;
         }

         return false;
      }

      /**
       * This method executs a defined count query.
       *
       * @return true   if the query was successfully executed
       * @return false  if an error occurred
       *
       */
      public function executeCount()
      {
         if(self::COUNT === $this->type)
         {
            $return = array();

            $query = $this->getCountSQL();
            
            if($this->isDirty == false)
            {
               if($this->executeQuery($query, true, $return))
               {
                  return $return[0]['numberOfRows'];
               }
            }

            return false;
         }

         return false;
      }

      /**
       * Resets all fields for creating a new query.
       *
       */
      public function cleanUp()
      {
         $this->sqlParts['select'] = array();
         $this->sqlParts['insert'] = array();
         $this->sqlParts['update'] = array();
         $this->sqlParts['count'] = array();
         $this->sqlParts['from'] = array();
         $this->sqlParts['join'] = array();
         $this->sqlParts['where'] = array();
         $this->sqlParts['groupBy'] = array();
         $this->sqlParts['having'] = array();
         $this->sqlParts['orderBy'] = array();
         $this->sqlParts['limit'] = array();
         $this->sqlParts['set'] = array();
      }

      /**
       * This method executes the query and returns the results to the caller.
       *
       * @param string $query contains the full query
       * @param boolean $autocleanup clean up after execution
       * @param mixed $return contains the query result
       *
       * @return true   if the query was successfully executed
       * @return false  if an error occurred
       *
       */
      private function executeQuery($query, $autocleanup, &$return=null)
      {
         $autocleanup ? $this->cleanUp() : '';

         $result = $this->database->query($query);

         if(self::SELECT == $this->type || self::COUNT == $this->type)
         {
            if(false === $result)
            {
               // TODO: We should add a query parameter to define the importance of the query. So we could decide here how serious it is, if the query fails.
               Error::newError(Error::UNRECOVERABLE, $this->database->error);

               return false;
            }
            elseif(0 < $result->num_rows)
            {
               while($row = $result->fetch_assoc())
               {
                  $return[] = $row;
               }

               mysqli_free_result($result);

               return true;
            }

            mysqli_free_result($result);

            return false;
         }
         elseif(self::INSERT == $this->type)
         {
            if(false !== $result)
            {
               $return = mysqli_insert_id($this->database);

               return true;
            }

            Error::newError(Error::ERROR, $this->database->error);

            return false;
         }

         if(false === $result)
         {
            echo $query;
            Error::newError(Error::UNRECOVERABLE, $this->database->error);

            return false;
         }

         return true;
      }

      /**
       * Returns the selecty query part as string.
       *
       * @return string the "select" query part
       *
       */
      private function getSelectSQL()
      {
         $query = 'SELECT ';

         if(count($this->sqlParts['select']) > 0)
         {
            $query .= implode(', ', $this->sqlParts['select']);
         }
         else
         {
            $query .= '*';
         }

         $query .= $this->getFromSql(true);

         $query .= $this->getWhereSql();

         $query .= $this->getGroupBySql();

         $query .= $this->getHavingSql();

         $query .= $this->getOrderBySql();

         $query .= $this->getLimitSql();

         return $query;

      }

      /**
       * Returns the delete query part as string.
       *
       * @return string the "delete" query part
       *
       */
      private function getDeleteSQL()
      {
         $query = 'Delete ';

         $query .= $this->getFromSql();

         $query .= $this->getWhereSql();

         $query .= $this->getLimitSql();

         return $query;
      }

      /**
       * Returns the update query part as string.
       *
       * @return string the "update" query part
       *
       */
      private function getUpdateSQL()
      {
         if(count($this->sqlParts['update']) > 0)
         {
            $query = 'UPDATE ' . $this->sqlParts['update'][0];
         }
         else
         {
            $this->isDirty = true;
         }

         $query .= $this->getUpdateSetSql();

         $query .= $this->getWhereSql();

         $query .= $this->getLimitSql();

         return $query;
      }

      /**
       * Returns the insert query part as string.
       *
       * @return string the "insert" query part
       *
       */
      private function getInsertSQL()
      {
         if(count($this->sqlParts['insert']) > 0)
         {
            $query = 'INSERT INTO ' . $this->sqlParts['insert'][0];
         }
         else
         {
            $this->isDirty = true;
         }

         $query .= $this->getInsertSetSql();

         return $query;
      }

      /**
       * Returns the count query part as string.
       *
       * @return string the "count" query part
       *
       */
      private function getCountSQL()
      {
         if(count($this->sqlParts['count']) > 0)
         {
            $query = 'SELECT COUNT(*) AS numberOfRows FROM ' . $this->sqlParts['count'][0];
         }
         else
         {
            $this->isDirty = true;
         }

         $query .= $this->getWhereSQL();

         return $query;
      }

      /**
       * Returns the from query part as string.
       *
       * @param bool $addAlias   sets whether to add alias or not
       * @param bool $addJoins   sets the need of joins
       *
       * @return string the "from" query part
       *
       */
      private function getFromSql($addAlias = true, $addJoins = false)
      {
         $query = ' FROM ';
         $number = count($this->sqlParts['from']);

         if(!($number > 0))
         {
            $this->isDirty = true;
         }

         foreach ($this->sqlParts['from'] as $key => $from)
         {
            $query .= $from['table'] . ($addAlias ? '' : 'AS ' . $from['alias']);

            if($addJoins && isset($this->sqlParts['join'][$from['alias']]))
            {
               foreach ($this->sqlParts['join'][$from['alias']] as $join)
               {
                  $query .= ' JOIN ' . $join['table'] . ' AS ' . $join['alias'] . ' ON ' . $join['condition'];
               }
            }

            if($key !== ($number - 1))
            {
               $query .= ', ';
            }
         }

         return $query;
      }

      /**
       * Returns the where query part as string.
       *
       * @return string the "where" query part
       *
       */
      private function getWhereSql()
      {
         $query = ' WHERE ';
         $number = count($this->sqlParts['where']);

         if(!($number > 0))
         {
            return '';
         }

         foreach ($this->sqlParts['where'] as $groupKey => $whereGroup)
         {
            $bracketsClosed = false;
            $groupMembers = count($whereGroup);

            $query .= '(';

            foreach ($whereGroup as $key => $where)
            {
               $query .= $where['condition'];

               if(($groupMembers > 1 || $number > 1) && ($groupKey !== ($number - 1) || ($key !== ($groupMembers - 1))))
               {
                  if($key === ($groupMembers - 1))
                  {
                     $query .= ')';
                     $bracketsClosed = true;
                  }

                  $query .= $this->getType($where['type']);
               }
            }

            if(!$bracketsClosed)
            {
               $query .= ')';
            }
         }

         return $query;
      }

      /**
       * Returns the group by query part as string.
       *
       * @return string the "group by" query part
       *
       */
      private function getGroupBySql()
      {
         if(count($this->sqlParts['groupBy']) > 0)
         {
            return ' GROUP BY ' . implode(', ', $this->sqlParts['groupBy']);
         }

         return '';
      }

      /**
       * Returns the having query part as string.
       *
       * @return string the "having" query part
       *
       */
      private function getHavingSql()
      {
         if(count($this->sqlParts['having']) > 0)
         {
            $number = count($this->sqlParts['having']);

            $query = ' HAVING ';

            foreach ($this->sqlParts['having'] as $key => $having)
            {
               $query .= $having['condition'];

               if($key !== ($number - 1))
               {
                  $query .= $this->getType($having['type']);
               }
            }

            return $query;
         }

         return '';
      }

      /**
       * Returns the order by query part as string.
       *
       * @return string the "order by" query part
       *
       */
      private function getOrderBySql()
      {
         if(count($this->sqlParts['orderBy']) > 0)
         {
            $number = count($this->sqlParts['orderBy']);

            $query = ' ORDER BY ';

            foreach ($this->sqlParts['orderBy'] as $key => $orderBy)
            {
               $query .= $orderBy['table'] . $this->getType($orderBy['order']);

               if($key !== ($number - 1))
               {
                  $query .= ', ';
               }
            }

            return $query;
         }

         return '';
      }

      /**
       * Returns the limit query part as string.
       *
       * @return string the "limit" query part
       *
       */
      private function getLimitSql()
      {
         if(count($this->sqlParts['limit']) == 2)
         {
            return ' LIMIT ' . $this->sqlParts['limit'][0] . ', ' . $this->sqlParts['limit'][1];
         }

         return '';
      }

      /**
       * Returns the update query part as string.
       *
       * @return string the query "update" part
       *
       */
      private function getUpdateSetSql()
      {
         $number = count($this->sqlParts['set']);

         if($number > 0)
         {
            $query = ' SET ';

            foreach($this->sqlParts['set'] as $key => $set)
            {
               $query .= $set['field'] . ' = ' . ($set['type'] === 's' ? '"' . $set['value'] . '"' : $set['value']);

               if($key !== ($number - 1))
               {
                  $query .= ', ';
               }
            }

            return $query;
         }

         return '';
      }

      /**
       * Returns the insert set query part as string.
       *
       * @return string the query "insert" part
       *
       */
      private function getInsertSetSql()
      {
         $number = count($this->sqlParts['set']);

         if($number > 0)
         {
            $query = ' (';

            foreach($this->sqlParts['set'] as $key => $set)
            {
               $query .= $set['field'];

               if($key !== ($number - 1))
               {
                  $query .= ', ';
               }
               else
               {
                  $query .= ') VALUES ';
               }
            }

            $query .= ' (';

            foreach($this->sqlParts['set'] as $key => $set)
            {
               $query .= ($set['type'] === 's' ? '"' . $set['value'] . '"' : $set['value']);

               if($key !== ($number - 1))
               {
                  $query .= ', ';
               }
               else
               {
                  $query .= ')';
               }
            }

            return $query;
         }

         return '';
      }

      /**
       * Returns the long form to a abbreviation.
       *
       * @param string $type the abbreviation
       *
       * @return string
       *
       */
      private function getType($type)
      {
         switch($type)
         {
            case 'A':
            case 'a':
            {
               return ' AND ';
            }break;

            case 'O':
            case 'o':
            {
               return ' OR ';
            }break;

            case 'AC':
            case 'ac':
            {
               return ' ASC';
            }break;

            case 'DC':
            case 'dc':
            {
               return ' DESC';
            }break;
         }
      }
   }
?>