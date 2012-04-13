<?php

\Lib::Import(array('objects'));

class Extendable {
    public function __construct($properties = array()) {
        foreach($properties as $k => $v) {
            $this->{$k} = $v;
        }
    }
}