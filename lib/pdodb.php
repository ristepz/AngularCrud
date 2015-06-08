<?php

/*
 * PDO class
 */

class pdodb {

    //@var host
    public $host;
    //@var database
    public $database;
    //@var username
    public $username;
    //@var password
    public $password;
    //@var Instance of our class will be stored into this variable
    private static $instance;
    //@var Connection id
    public $connection;
    //@var prepared statement holder
    public $stmt;
    //@var Store last result
    public $result = NULL;
    //@var Store query string
    protected $query_string;

    private function __construct() {
        
    }

    /*
     * @method singleton pattern
     */

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /*
     * @method
     * Connect to a database
     */

    public function connect($host = '', $database = '', $username = '', $password = '') {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;

        $dsn = "mysql:host=" . $host . ";dbname=" . $database;
        try {
            $this->connection = new PDO($dsn, $username, $password);
            $this->set_charset();
        } catch (PDOException $ex) {
            $this->throw_exception($ex);
        }
        return $this->connection;
    }

    /*
     * @method Execute simple query
     */

    public function simple_query($query_string) {
        //Set the PDO error mode to exception for use in try catch
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->result = $this->connection->query($query_string);
        $this->query_string = $query_string;
        return $this->result->fetchAll(PDO::FETCH_NUM);
    }
    /*
     * @method create table
     */
    public function create_table($query_string){
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->result = $this->connection->query($query_string);
        $this->query_string = $query_string;
    }

    /*
     * @method Select query
     */

    public function select($fields = array()) {
        $param_fields = is_array($fields) ? implode(",", $fields) : $fields;
        $this->query_string = "SELECT " . $param_fields;
        return $this;
    }

    /*
     * @method delete
     */

    public function delete() {
        $this->query_string = "DELETE ";
        return $this;
    }

    /*
     * @method update
     */

    public function update($table_name) {
        $this->query_string = "UPDATE " . $table_name;
        return $this;
    }
    /*
     * @method set - used for update query
     */
    public function set($update_string){
        $this->query_string .= " SET " . $update_string;
        return $this;
    }

    /*
     * @method From method, chooses database table
     */

    public function from($table_name) {
        $this->query_string .= " FROM " . $table_name;
        return $this;
    }

    /*
     * @method Where method, used for WHERE clause
     */

    public function where($where_clause) {
        $this->query_string .= " WHERE " . $where_clause;
        return $this;
    }

    /*
     * @method order, used for ASC and DESC ordering
     */

    public function orderby($order) {
        $this->query_string .= " ORDER BY " . $order;
        return $this;
    }

    /*
     * @method Limit method
     */

    public function limit($limit) {
        $this->query_string .= " LIMIT " . $limit;
        return $this;
    }

    /*
     * @method Offset method
     */

    public function offset($offset) {
        $this->query_string .= " OFFSET " . $offset;
        return $this;
    }

    /*
     * @method prepare
     */

    public function prepare() {
        if (!empty($this->query_string)) {
            $this->stmt = $this->connection->prepare($this->query_string);
        }
        return $this;
    }

    /*
     * @method bind
     * @param name - the name of the pdo value
     * @param value - the actual value
     * @param type - pdo constant type could be PDO::PARAM_INT, PDO::PARAM_STR default is set to string
     */

    public function bind($params) {

        if (!is_array($params)) {
            return;
        }
        foreach ($params as $param) {
            $param_type = PDO::PARAM_STR;
            if (isset($param['type'])) {
                switch ($param['type']) {
                    case 'int':
                        $param_type = PDO::PARAM_INT;
                        break;
                    case 'string':
                        $param_type = PDO::PARAM_STR;
                        break;
                }
            }
            $this->stmt->bindValue($param['name'], $param['value'], $param_type);
        }
        return $this;
    }

    /*
     * @method Execute the query and get the results
     */

    public function execute() {
        if (!empty($this->query_string)) {
            //Set the PDO error mode to exception for use in try catch
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //Execute the query
            $this->stmt->execute();
            $this->result = $this->stmt;
            return $this->stmt;
        }
        return false;
    }

    /*
     * @method fetch - Get results from last query
     */

    public function fetch() {
        $data = $this->stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    /*
     * @method insertInto table in database
     */

    public function insert_into($table) {
        $this->query_string = " INSERT INTO " . $table;
        return $this;
    }

    /*
     * @method Define columns to insert
     */

    public function columns($columns) {
        if (!is_array($columns)) {
            return;
        }
        $this->query_string .= " (" . implode(",", $columns) . ") ";
        return $this;
    }

    /*
     * @method Insret values in table
     * @param array data 
     */

    public function values($data) {
        if (!is_array($data)) {
            return;
        }
        $builded_string = "VALUES";
        //Multidimensional array of data
        if (isset($data[0]) && is_array($data[0])) {
            $bind_param_count = 1;
            foreach ($data as $row) {
                $row_count = count($row);
                $row_placeholders = "";
                for ($i = 0; $i < $row_count; $i++) {
                    //First bind placeholders
                    $row_placeholders .= "?,";
                }
                $builded_string .= "(" . substr($row_placeholders, 0, -1) . "),";
            }
            $builded_string = substr($builded_string, 0, -1);
            $this->query_string .= $builded_string;
            //////////////////////////////////////////////////////////////
            //Now prepare the query
            $this->stmt = $this->connection->prepare($this->query_string);
            //////////////////////////////////////////////////////////////
            //Bind the values
            foreach ($data as $row) {
                $row_count = count($row);
                for ($i = 0; $i < $row_count; $i++) {
                    $this->stmt->bindValue($bind_param_count, $row[$i]);
                    $bind_param_count++;
                }
            }
            ///////////////////////////////////////////////////////////////
            //Here we have only single row for insert
        } else {
            //Single row of data
            $row_placeholders = "";
            $data_count = count($data);
            for ($i = 0; $i < $data_count; $i++) {
                //First bind placeholders
                $row_placeholders .= "?,";
            }
            $builded_string .= "(" . substr($row_placeholders, 0, -1) . "),";
            $builded_string = substr($builded_string, 0, -1);
            $this->query_string .= $builded_string;
            //////////////////////////////////////////////////////////////
            //Now prepare the query
            $this->stmt = $this->connection->prepare($this->query_string);
            //Bind the values
            for ($i = 0; $i < $data_count; $i++) {
                $this->stmt->bindValue(($i + 1), $data[$i]);
            }
        }

        return $this;
    }

    /*
     * @method Select row by id
     */

    public function select_row_by_id($id) {
        
    }

    /*
     * @method inspect query string
     */

    public function inspect_query_string() {
        return $this->query_string;
    }

    /*
     * @method Set connection charset to utf8
     */

    public function set_charset() {
        $this->connection->exec("set names utf8");
    }

    /*
     * @method Get result row count
     */

    public function get_row_count() {
        if ($this->result != NULL) {
            return $this->result->rowCount();
        }
    }

    /*
     * @method get pdo error info
     */

    public function get_error_info() {
        $error_info = $this->connection->errorInfo();
        if (isset($error_info[2])) {
            return $error_info[2];
        }
        return false;
    }

    /*
     * @method Throw exception from exception object
     */

    public function throw_exception($exception, $stop_execution = false) {
        echo "<div style='background:#f8d9d9; color:#ad0000;font-family:Arial; font-size: 14px; padding: 25px; border:1px #ad0000 solid; margin:25px;'>";
        echo "<h3 style='border-bottom:1px #ad0000 dashed; margin:0 0 5px 0; padding-bottom: 4px;'>MYSQL ERROR:</h3>";
        echo "<div>Message: <strong>" . $exception->getMessage() . "</strong></div>";
        echo "<div>Error Code: <strong>" . $exception->getCode() . "</strong></div>";
        echo "<div>File: <strong>" . $exception->getFile() . "</strong></div>";
        echo "<div>At line: <strong>" . $exception->getLine() . "</strong></div>";
        echo "</div>";
        if ($stop_execution) {
            exit();
        }
    }

}
