<?php

Lib::Import(array('extendable'));

/**
 * An HTTP request.
 */
class Request extends Extendable {
    public $verb = false;

    public $uri = false;

    public $query_string = false;

    public $data = array();

    public $files = array();

    public $get = true;

    public $post = false;

    public $delete = false;

    /**
     * @var bool
     */
    public $async = null;

    public function __construct($properties = array()) {
        parent::__construct($properties);

        $this->verb = false === $this->verb ?
            $_SERVER['REQUEST_METHOD'] :
            $this->verb;

        $verb_lower = strtolower($this->verb);

        $this->get = $verb_lower == 'get';

        $this->post = $verb_lower == 'post';

        $this->delete = $verb_lower == 'delete';

        $this->uri = false === $this->uri ?
            $_SERVER['REQUEST_URI'] :
            $this->uri;

        $this->query_string = false === $this->query_string ?
            $_SERVER['QUERY_STRING'] :
            $this->query_string;

        $this->async = null === $this->async ?
            getenv('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest' :
            $this->async;
    }

    public function getUri($include_query_string = false) {
        if($include_query_string) {
            return $this->uri;
        }

        $pieces = explode('?', $this->uri);

        return $pieces[0];
    }

    public function getData($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function getFile($key) {
        return isset($this->files[$key]) ? $this->files[$key] : null;
    }
}