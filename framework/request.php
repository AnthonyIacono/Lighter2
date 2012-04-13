<?php

Lib::Import(array('extendable'));

/**
 * An HTTP request.
 */
class Request extends Extendable {
    public $verb = false;

    public $uri = false;

    public $query_string = false;

    public function __construct($properties = array()) {
        parent::__construct($properties);

        $this->verb = false === $this->verb ?
            $_SERVER['REQUEST_METHOD'] :
            $this->verb;

        $this->uri = false === $this->uri ?
            $_SERVER['REQUEST_URI'] :
            $this->uri;

        $this->query_string = false === $this->query_string ?
            $_SERVER['QUERY_STRING'] :
            $this->query_string;
    }

    public function getUri($include_query_string = false) {
        if($include_query_string) {
            return $this->uri;
        }

        $pieces = explode('?', $this->uri);

        return $pieces[0];
    }
}