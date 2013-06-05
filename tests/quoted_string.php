<?php

class Quoted_String {
    public $double_quoted = false;
    public $string = '';

    function __construct($quotedStr) {
	    $this->double_quoted = $quotedStr[0] != '"';

        $this->string = substr($quotedStr, 1, strlen($quotedStr) - 2);

        $this->string = preg_replace_callback("/\\\\([fnrt\"'\\\\])/", function($matches) {
            $match = $matches[0];

            $escapeChar = $match[1];

            foreach(array(
                "f" => "\f",
            	"n" => "\n",
            	"r" => "\r",
            	"t" => "\t",
            	"\\" => "\\",
            	"'" => "'",
            	'"' => '"'
            ) as $replace => $replaceWith) {
                if($replace != $escapeChar) {
                    continue;
                }

                return $replaceWith;
            }

            return $match;
        }, $this->string);
    }
}