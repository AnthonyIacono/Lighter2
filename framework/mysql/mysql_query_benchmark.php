<?php

class MySQLQueryBenchmark extends Extendable {
    public $id;
    public $database;
    public $queries = array();

    public function __construct(MySQLDatabase $database, $properties = array()) {
        $this->id = StrLib::Guid();

        parent::__construct($properties);

        $this->database = $database;
    }

    /**
     * @param $query
     * @returns mixed guid
     */
    public function beginBenchmarkingQuery($query) {
        $queryId = StrLib::Guid();

        $this->queries[] = array(
            'query' => $query,
            'start_time' => microtime(true),
            'id' => $queryId
        );

        return $queryId;
    }

    public function finishBenchmarkingQuery($queryId) {
        foreach($this->queries as &$queryInfo) {
            if($queryInfo['id'] != $queryId) {
                continue;
            }

            $queryInfo['end_time'] = microtime(true);
            return;
        }

        throw new Exception("Unable to find query with id #" . $queryId);
    }

    public function getTotalTime() {
        $total_time = 0;
        foreach($this->queries as $queryInfo) {
            if(!isset($queryInfo['end_time'])) {
                continue;
            }

            $total_time += max(0, $queryInfo['end_time'] - $queryInfo['start_time']);
        }

        return $total_time;
    }
}