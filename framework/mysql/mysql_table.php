<?php

Lib::Import('mysql/mysql_field_schema');

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

        $result = $this->database->query("SHOW COLUMNS FROM `{$this->table}`");

        if(false === $result) {
            throw new Exception("Table `{$this->table}` does not exist");
        }

        while($row = $result->fetch_assoc()) {
            $field = new MySQLFieldSchema($row);

            $this->schema[] = $field;

            if($field->Key == 'PRI') {
                $this->primaryKey = $field;
            }
        }
    }

    /**
     * The simplest, safest, and slowest way to save a record.
     * @param $record
     * @return boolean
     */
    public function save($record) {
        $record = new MySQLRecord($record);

        // If there is a primary key, we should check if there is already an entry for this record.
        if($this->primaryKey !== false) {
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
}