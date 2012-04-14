<?php

class Resource extends Extendable {
    /**
     * @var Request
     */
    public $request;

    /**
     * @var Route
     */
    public $route;

    public function __construct($properties = array()) {
        parent::__construct($properties);
    }

    /**
     * @var mixed
     */
    public function pre_execute() {
        return true;
    }

    /**
     * @var Response
     */
    public function execute() {

    }
}