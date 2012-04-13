<?php

class Route extends Extendable {
    public $resource;

    public $pattern;

    public $named = array();

    public $segments = array();

    public function __construct($properties = array()) {
        parent::__construct($properties);
    }
}