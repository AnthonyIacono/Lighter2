<?php

Lib::Import(array('extendable', 'mysql/mysql_table'));

/**
 * A MySQL database.
 */
class MySQLDatabase extends Extendable {
    private $db;

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
     * @param $query
     * @param int $result_mode
     * @return mysqli_result
     */
    public function query($query, $result_mode = MYSQLI_STORE_RESULT) {
        return $this->db->query($query, $result_mode);
    }

    public function multi_query($query) {
        return $this->db->multi_query($query);
    }

    public function real_escape_string($string) {
        return $this->db->real_escape_string($string);
    }

    public function encode($value) {
        return $value === null ? 'null' : "'" . $this->real_escape_string($value) . "'";
    }
}