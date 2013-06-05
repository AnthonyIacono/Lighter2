<?php
Lib::Import('mysql/mysql_database');

class MySQLPool extends Extendable {
    /**
     * @var MySQLPool
     */
    static public $singleton = null;

    public $databases = array();

    static public function changeSingleton($singleton) {
        self::$singleton = $singleton;
    }

    public function database($config, $ping_cached = true) {
        $config = (object)$config;

        $host = empty($config->host) ?
            ini_get("mysqli.default_host") :
            $config->host;

        $username = empty($config->username) ?
            ini_get("mysqli.default_user") :
            $config->username;

        $password = !isset($config->password) || $config->password === false ?
            ini_get("mysqli.default_pw") :
            $config->password;

        $port = empty($config->port) ?
            ini_get("mysqli.default_port") :
            $config->port;

        $socket = !isset($config->socket) || $config->socket === false ?
            ini_get("mysqli.default_socket") :
            $config->socket;

        $dbname = $config->database;

        foreach($this->databases as $db) {
            /**
             * @var MySQLDatabase $db
             */
            if($db->host != $host or $db->username != $username or
                $db->password != $password or $db->database != $dbname or
                    $db->port != $port or $db->socket != $socket) {
                continue;
            }

            if($ping_cached) {
                $db->ping();
            }

            return $db;
        }

        $database = new MySQLDatabase($config);
        $database->benchmarker = new MySQLQueryBenchmark($database);
        $this->databases[] = $database;
        return $database;
    }
}

if(MySQLPool::$singleton === null) {
    MySQLPool::$singleton = new MySQLPool();
}