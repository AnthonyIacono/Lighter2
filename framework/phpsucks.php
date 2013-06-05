<?php

class PhpSucks {
    private function __construct() {

    }

    /**
     * @param $input
     * @return array
     */
    public static function ToIndexBasedArray($input) {
        $output = array();

        if(!is_array($input) && !is_object($input)) {
            return $output;
        }

        foreach($input as $i) {
            $output[] = $i;
        }

        return $output;
    }

    public static function IsAssocArray($arr) {
        if(!is_array($arr)) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function FlipGet($map, $flipped_key, $default = null) {
        $map = array_flip((array)$map);
        if(empty($map[$flipped_key])) {
            return $default;
        }

        return $map[$flipped_key];
    }

    /**
     * @static
     * Flatten an array a single level.
     * @param $arr
     * @return array
     */
    public static function Flatten($arr) {
        $output = array();
        foreach($arr as $_ => $v) {
            if(!is_array($v)) {
                $output[] = $v;
                continue;
            }

            foreach($v as $_ => $v2) {
                $output[] = $v2;
            }
        }
        return $output;
    }

    public static function GetLastArrayItem($arr, $default = null) {
        if(!is_array($arr)) {
            return $default;
        }

        $new_arr = self::ToIndexBasedArray($arr);
        $count = count($new_arr);

        if($count == 0) {
            return $default;
        }

        return $new_arr[$count - 1];
    }

    public static function GetFirstArrayItem($arr, $default = null) {
        if(!is_array($arr)) {
            return $default;
        }

        $new_arr = self::ToIndexBasedArray($arr);

        if(empty($new_arr)) {
            return $default;
        }

        return $new_arr[0];
    }

    public static function IsIndexBasedArray($array) {
        if(!is_array($array)) {
            return false;
        }

        return \Plinq\Plinq::factory($array)->All(function($k, $_) {
            return is_numeric($k);
        });
    }

    public static function ArrayMergePreserved() {
        $arrays = func_get_args();

        $output_array = array();

        foreach($arrays as $array) {
            foreach($array as $key => $value) {
                $output_array[$key] = $value;
            }
        }

        return $output_array;
    }
}