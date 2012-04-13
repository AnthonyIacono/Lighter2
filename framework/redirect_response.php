<?php

Lib::Import(array('response', 'http'));

class RedirectResponse extends Response {
    public $status = 302;

    public $location = '';

    public function __construct($properties = array()) {
        parent::__construct($properties);
    }

    public function pre_render() {
        parent::pre_render();

        header("Location: {$this->location}", true, $this->status);
    }
}