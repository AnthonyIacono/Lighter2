<?php

Lib::Import(array('mysql/mysql_field_schema', 'mysql/mysql_record', 'mysql/mysql_fluent_select_query'));

class MySQLTable extends Extendable {
    /**
     * @var MySQLDatabase
     */
    public $database;

    /**
     * @var string
     */
    public $table;

    /**
     * @var MySQLFieldSchema
     */
    public $primaryKey = false;

    /**
     * @var array
     */
    public $schema;

    public $records;

    public function __construct($properties = array()) {
        parent::__construct($properties);

        $this->schema = $this->database->show_columns($this->table);

        $primaryKey = false;

        foreach($this->schema as $field) {
            if($field->Key == 'PRI') {
                if($primaryKey !== false) {
                    $primaryKey = null;
                    continue;
                }

                $primaryKey = $field;
            }
        }

        $this->primaryKey = empty($primaryKey) ? false : $primaryKey;
    }

    /**
     * The simplest, safest, and slowest way to save a record.
     * @param $record
     * @return boolean
     */
    public function save($record, $savableMethod = null) {
        $savableMethod = $savableMethod === null ? 'asSavableModel' : $savableMethod;
        $record = is_object($record) ? $record : new MySQLRecord($record);

        if(method_exists($record, $savableMethod)) {
            $record = (object)$record->$savableMethod();
        }

        // HACKHACK! I'm pretty sure it shouldn't matter, but there is some margin for error with this.
        if(empty($record->datetime)) {
            $record->datetime = StrLib::DateTime();
        }

        // If there is a primary key, we should check if there is already an entry for this record.
        if($this->primaryKey !== false && isset($record->{$this->primaryKey->Field})) {
            $primaryKey = $this->primaryKey->Field;

            $primaryValueEncoded = $this->database->encode($record->{$primaryKey});

            $result = $this->database->query("SELECT COUNT(*) FROM `{$this->table}` WHERE `{$this->table}`.`{$primaryKey}` = $primaryValueEncoded");

            $row = $result->fetch_assoc();

            // We can update the existing record.
            if($row['COUNT(*)']) {
                $query = "UPDATE `{$this->table}` SET ";

                $first = true;
                foreach($record as $field => $value) {
                    // This won't need updating.
                    if($field == $primaryKey) {
                        continue;
                    }

                    // This field doesn't exist in the schema.
                    if(!count(array_filter($this->schema, function($field_schema) use($field) {
                        return $field_schema->Field == $field;
                    }))) {
                        continue;
                    }

                    $query .= !$first ? ',' : '';

                    $query .= "`{$this->table}`.`{$field}` = " . $this->database->encode($value);

                    $first = false;
                }

                $query .= " WHERE `{$this->table}`.`{$primaryKey}` = {$primaryValueEncoded}";

                $this->database->query($query);

                return true;
            }
        }

        // At this point we know should perform an INSERT
        $query = "INSERT INTO `{$this->table}` (";

        $first = true;
        foreach($this->schema as $schema) {
            $query .= !$first ? ',' : '';

            $query .= "`{$this->table}`.`{$schema->Field}`";

            $first = false;
        }

        $query .= ") VALUES (";

        $first = true;
        foreach($this->schema as $schema) {
            $query .= !$first ? ',' : '';

            $query .= $this->database->encode(
                isset($record->{$schema->Field}) ? $record->{$schema->Field} :
                    ($schema->Null ? null : '')
            );

            $first = false;
        }

        $query .= ")";

        return $this->database->query($query) ? true : false;
    }

    public function beginFluentSelectQuery($shortname = null) {
        $fluentSelectQuery = new MySQLFluentSelectQuery();

        $fluentSelectQuery->from($this, $shortname);

        return $fluentSelectQuery;
    }

    public function findBy($column, $value, $modelType = null) {
        $query = "SELECT ";

        $first = true;
        foreach($this->schema as $schema) {
            $query .= !$first ? ',' : '';

            $query .= "`{$this->table}`.`{$schema->Field}`";

            $first = false;
        }

        $query .= " FROM `{$this->table}` WHERE `{$this->table}`.`{$column}` = " . $this->database->encode($value);

        $result = $this->database->query($query);

        $records = array();

        while($row = $result->fetch_assoc()) {
            $recordModel = $this->database->construct_model($row, $modelType);

            $records[] = $recordModel;
        }

        return $records;
    }

    public function findByEx($columns = array(), $order = array(), $modelType = null) {
        $query = "SELECT ";

        $first = true;
        foreach($this->schema as $schema) {
            $query .= !$first ? ',' : '';

            $query .= "`{$this->table}`.`{$schema->Field}`";

            $first = false;
        }

        $query .= " FROM `{$this->table}` WHERE";

        $first = true;
        foreach($columns as $column => $value) {
            $operation = is_array($value) && isset($value['operation']) ? $value['operation'] : '=';

            $encoded = $this->database->encode(is_array($value) ? $value['value'] : $value);

            $query .= !$first ? ' AND' : '';

            $query .= " `{$this->table}`.`$column` $operation $encoded";

            $first = false;
        }

        if(!empty($order)) {
            $query .= " ORDER BY";

            $first = true;
            foreach($order as $column => $direction) {
                $query .= !$first ? ',' : '';

                $query .= " `{$this->table}`.`$column` $direction";

                $first = false;
            }
        }

        $result = $this->database->query($query);

        $records = array();

        while($row = $result->fetch_assoc()) {
            $records[] = $this->database->construct_model($row, $modelType);
        }

        return $records;
    }

    public function firstBy($column, $value, $modelType = null) {
        $query = "SELECT ";

        $first = true;
        foreach($this->schema as $schema) {
            $query .= !$first ? ',' : '';

            $query .= "`{$this->table}`.`{$schema->Field}`";

            $first = false;
        }

        $query .= " FROM `{$this->table}` WHERE `{$this->table}`.`{$column}` = " . $this->database->encode($value) .
            " LIMIT 1";

        $result = $this->database->query($query);

        $object = $result->fetch_object();

        if(null === $object) {
            return null;
        }

        return $this->database->construct_model($object, $modelType);
    }

    public function firstByEx($columns = array(), $order = array(), $modelType = null) {
        $query = "SELECT ";

        $first = true;
        foreach($this->schema as $schema) {
            $query .= !$first ? ',' : '';

            $query .= "`{$this->table}`.`{$schema->Field}`";

            $first = false;
        }

        $query .= " FROM `{$this->table}` WHERE ";

        $first = true;
        foreach($columns as $column => $value) {
            $operation = is_array($value) && isset($value['operation']) ? $value['operation'] : '=';

            $encoded = $this->database->encode(is_array($value) ? $value['value'] : $value);

            $query .= !$first ? ' AND' : '';

            $query .= " `{$this->table}`.`$column` $operation $encoded";

            $first = false;
        }

        if(!empty($order)) {
            $query .= " ORDER BY";

            $first = true;
            foreach($order as $column => $direction) {
                $query .= !$first ? ',' : '';

                $query .= " `{$this->table}`.`$column` $direction";

                $first = false;
            }
        }

        $query .= " LIMIT 1";

        $result = $this->database->query($query);

        $object = $result->fetch_object();

        if(null === $object) {
            return null;
        }

        return $this->database->construct_model($object, $modelType);
    }

    public function all($modelType = null) {
        $query = "SELECT ";

        $first = true;
        foreach($this->schema as $schema) {
            $query .= !$first ? ',' : '';

            $query .= "`{$this->table}`.`{$schema->Field}`";

            $first = false;
        }

        $query .= " FROM `{$this->table}`";

        $result = $this->database->query($query);

        $records = array();

        while($row = $result->fetch_assoc()) {
            $records[] = $this->database->construct_model($row, $modelType);
        }

        return $records;
    }
}