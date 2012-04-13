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

    public $Extra;

    public function __construct($properties = array()) {
        parent::__construct($properties);

        if($this->Null == 'NO') {
            $this->Null = false;
        }
        else if($this->Null == 'YES') {
            $this->Null = true;
        }
    }
}