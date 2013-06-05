<?php

Lib::Import('extendable');

/**
 * A MySQL Field's Schema
 */
class MySQLFieldSchema extends Extendable {
    public $Field;

    public $Type;

    public $Null;

    public $Key;

    public $Default;

    public $HasDefault = true;

    public $Extra;

    public function __construct($properties = array()) {
        if(is_object($properties)) {
            $properties = (array)$properties;
        }

        parent::__construct($properties);

        if(!isset($properties['Default'])) {
            $this->HasDefault = false;
        }

        if(strtolower($this->Null) == 'no') {
            $this->Null = false;
        }
        else if(strtolower($this->Null) == 'yes') {
            $this->Null = true;
        }
    }
}