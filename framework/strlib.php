<?php

Config::Import('security');

class StrLib {
    private function __construct() {

    }
    public static function IsEmpty($str) {
        return self::Trim($str) == '';
    }
    public static function RandomMD5() {
        return md5(uniqid());
    }
    public static function IsLettersAndNumbers($str, $include_spaces = false) {
        if (self::Length($str) == 0) {
            return false;
        }
        return $str == self::LettersAndNumbers($str, $include_spaces);
    }
    public static function LettersAndNumbers($str, $include_spaces = false) {
        $new_str = '';
        $len = strlen($str);
        for($x = 0; $x < $len; $x++) {
            if(($str[$x] >= 'a' and $str[$x] <= 'z') or ($str[$x] >= 'A' and $str[$x] <= 'Z') or ($str[$x] >= '0' and $str[$x] <= '9')) {
                $new_str .= $str[$x];
            }
            if($str[$x] == ' ' and $include_spaces) {
                $new_str .= $str[$x];
            }
        }
        return $new_str;
    }
    public static function DateTime($timestamp = false) {
        if($timestamp === false) {
            $timestamp = time();
        }
        return date('Y-m-d H:i:s', $timestamp);
    }
    public static function IsNumbers($str) {
        $check_str = self::Numbers($str);
        return $str == $check_str;
    }
    public static function Numbers($str) {
        $l = strlen($str);
        $new_str = '';
        for($i = 0; $i < $l; $i++) {
            if($str[$i] >= '0' and $str[$i] <= '9') {
                $new_str .= $str[$i];
            }
        }
        return $new_str;
    }
    public static function Lower($str) {
        return strtolower($str);
    }
    public static function Upper($str) {
        return strtoupper($str);
    }
    public static function Replace($search, $replace, $subject, &$count = null) {
        return str_replace($search, $replace, $subject, $count);
    }
    public static function Trim($str) {
        return trim($str);
    }
    public static function Explode($delimiter, $string, $limit = -1) {
        return explode($delimiter, $string, $limit);
    }
    public static function Length($str) {
        return strlen($str);
    }
    public static function Hash($str) {
            if (Config::$Configs['security']['type']=='MD5') {
                return md5($str . Config::$Configs['security']['salt']);
            }
            return hash('sha256', hash('sha256', $str) . hash('sha256', $str));
        }
    // thank you internet
    public static function IsGuid($str) {
        return preg_match("/^[A-Fa-f0-9]{8}-([A-Fa-f0-9]{4}-){3}[A-Fa-f0-9]{12}$/", $str) ? true : false;
    }

    public static function Guid() {
        $tl = str_pad(dechex(mt_rand(0, 65535)), 4, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 65535)), 4, '0', STR_PAD_LEFT);
        $tm = str_pad(dechex(mt_rand(0, 65535)), 4, '0', STR_PAD_LEFT);
        $th = mt_rand(0, 255);
        $th = $th & hexdec('0f');
        $th = $th ^ hexdec('40');
        $th = str_pad(dechex($th), 2, '0', STR_PAD_LEFT);
        $cs = mt_rand(0, 255);
        $cs = $cs & hexdec('3f');
        $cs = $cs ^ hexdec('80');
        $cs = str_pad(dechex($cs), 2, '0', STR_PAD_LEFT);
        $clock_seq_low = str_pad(dechex(mt_rand(0, 65535)), 4, '0', STR_PAD_LEFT);
        $node = str_pad(dechex(mt_rand(0, 65535)), 4, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 65535)), 4, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 65535)), 4, '0', STR_PAD_LEFT);
        return $tl . '-' . $tm . '-' . $th . $cs . '-' . $clock_seq_low . '-' . $node;
    }
}