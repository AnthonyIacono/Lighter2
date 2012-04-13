<?php

Lib::Import('http');

class Response extends Extendable {
    public $status = 200;

    public $headers = array();

    public $strictHeaders = array();

    public $body;

    public function __construct($properties = array()) {
        parent::__construct($properties);
    }

    public function pre_render() {
        header('HTTP/1.1 ' . HTTP::status($this->status));

        if(is_array($this->headers)) {
            foreach($this->headers as $k => $v) {
                header("{$k}: {$v}");
            }
        }
        else if(is_string($this->headers)) {
            header($this->headers);
        }

        if(is_array($this->strictHeaders)) {
            foreach($this->strictHeaders as $k => $v) {
                header("{$k}: {$v}", true);
            }
        }
        else if(is_string($this->strictHeaders)) {
            header($this->strictHeaders, true);
        }
    }

    public function render() {
        echo "{$this->body}";
    }

    public function post_render() {

    }

    public function send() {
        if(false === $this->pre_render()) {
            return;
        }

        if(false === $this->render()) {
            return;
        }

        $this->post_render();
    }
}