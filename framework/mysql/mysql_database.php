<?php

Lib::Import(array('extendable', 'mysql/mysql_table'));

/**
 * A MySQL database.
 */
class MySQLDatabase extends Extendable {
    /**
     * @var mysqli
     */
    public $db;

    public $host = false;

    public $username = false;

    public $password = false;

    public $database = '';

    public $port = false;

    public $socket = false;

    public $cachedTables = array();

    public function __construct($properties = array()) {
        parent::__construct($properties);

        $this->host = false === $this->host ?
            ini_get("mysqli.default_host") :
            $this->host;

        $this->username = false === $this->username ?
            ini_get("mysqli.default_user") :
            $this->username;

        $this->password = false === $this->password ?
            ini_get("mysqli.default_pw") :
            $this->password;

        $this->port = false === $this->port ?
            ini_get("mysqli.default_port") :
            $this->port;

        $this->socket = false === $this->socket ?
            ini_get("mysqli.default_socket") :
            $this->socket;

        $this->connect();
    }

    public function connect() {
        $this->cachedTables = array();

        $this->db = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port,
            $this->socket);
    }

    /**
     * @param $table
     * @return MySQLTable
     */
    public function table($table) {
        if(isset($this->cachedTables[$table])) {
            return $this->cachedTables[$table];
        }

        $this->cachedTables[$table] = new MySQLTable(array(
            'table' => $table,
            'database' => $this
        ));

        return $this->cachedTables[$table];
    }

    /**
     * @var MySQLQueryBenchmark
     */
    public $benchmarker = null;

    public function installBenchmarker(MySQLQueryBenchmark $benchmarker) {
        $this->benchmarker = $benchmarker;
    }

    public function query($query, $result_mode = MYSQLI_STORE_RESULT) {
        if($this->benchmarker !== null) {
            $queryId = $this->benchmarker->beginBenchmarkingQuery($query);
        }

        $result = $this->db->query($query, $result_mode);

        if($this->benchmarker !== null) {
            $this->benchmarker->finishBenchmarkingQuery($queryId);
        }

        if(empty($result)) {
            return 0;
            //die($query . $this->db->errno . ' is last error ' . $this->db->error);
            //throw new Exception("Error with query: {$query}");
        }

        return $result;
    }

    public function tableExists($tableName) {
        $tableNameEncoded = $this->encode($tableName);
        $databaseNameEncoded = $this->encode($this->database);

        $results = $this->selectQuery(<<<SQL
SELECT COUNT(*) as `count` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = {$databaseNameEncoded} AND TABLE_NAME = {$tableNameEncoded}
SQL
        );

        $result = $results[0];

        return !empty($result->count);
    }

    public function selectQuery($query, $modelType = null) {
        $result = $this->query($query);

        if(empty($result)) {
            throw new Exception("Query failed: {$query}");
        }

        $results = array();

        while($row = $result->fetch_assoc()) {
            $results[] = $this->construct_model($row, $modelType);
        }

        return $results;
    }

    public function fluentSelectQuery(MySQLFluentSelectQuery $query, $modelType = null) {
        throw new Exception("Not implemented yet");
    }

    public function multi_query($query) {
        return $this->db->multi_query($query);
    }

    public function real_escape_string($string) {
        return $this->db->real_escape_string($string);
    }

    public function encode($value) {
        if($value === false) {
            return 'false';
        }

        if($value === true) {
            return 'true';
        }

        return $value === null ? 'null' : "'" . $this->real_escape_string($value) . "'";
    }

    public function construct_model($row, $modelType = null) {
        $row = (object)$row;

        $modelType = null === $modelType ? 'MySQLRecord' : $modelType;

        // if the model type is just a class (there is no :: indicating a function call
        if(strpos($modelType, '::') === false) {
            if(method_exists($modelType, 'modelBinder')) {
                $model = call_user_func_array($modelType . '::modelBinder', array($row, $this));
            }
            else {
                $model = new $modelType();

                foreach($row as $k => $v) {
                    $model->{$k} = $v;
                }
            }
        }
        else {
            $model = call_user_func_array($modelType, array($row, $this));
        }

        return $model;
    }

    /**
     * @return MySQLFieldSchema[]
     */
    public function show_columns($table_name, $try_cache = true, $write_cache = true) {
        $cache_path = Config::$Configs['application']['paths']['cache'] . 'show_columns_' . $this->database . '_' . $table_name;

        if($try_cache && file_exists($cache_path)) {
            $schema = json_decode(file_get_contents($cache_path));
            $schema = !is_array($schema) ? array() : $schema;

            foreach($schema as &$field) {
                $field = new MySQLFieldSchema($field);
            }

            return $schema;
        }

        $table_name_escaped = $this->real_escape_string($table_name);

        $result = $this->query("SHOW COLUMNS FROM `{$table_name_escaped}`");

        if(false === $result || !is_object($result)) {
            throw new Exception("Table `{$table_name_escaped}` does not exist");
        }

        $schema = array();

        while($row = $result->fetch_assoc()) {
            $field = new MySQLFieldSchema($row);

            $schema[] = $field;
        }

        if($write_cache) {
            if(!is_dir(Config::$Configs['application']['paths']['cache'])) {
                @mkdir(Config::$Configs['application']['paths']['cache'], 0777, true);
            }
            file_put_contents($cache_path, json_encode($schema));
        }

        return $schema;
    }

    public function ping() {
        if($this->db->ping()) {
            return;
        }

        $this->connect();
    }

    public function get_insert_id() {
        return $this->db->insert_id;
    }
}