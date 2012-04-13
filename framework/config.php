<?php

class Config {
    private function __construct() {

    }

    public static $Configs = array();
    public static function Import($what) {
        if(is_array($what)) {
            foreach($what as $single) {
                self::Import($single);
            }
            return;
        }

        if(!is_string($what)) {
            return;
        }

        if(strstr($what, '../') !== false) {
            return;
        }

        $path = dirname(__FILE__) . '/../application/configs/' . $what.'.php';

        if(is_file($path)) {
            include_once($path);

            if(isset($config)) {
                self::$Configs[$what] = $config;
            }

            return;
        }
    }
}