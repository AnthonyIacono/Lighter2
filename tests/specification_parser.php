<?php

include_once('expect.php');
include_once('specification.php');
include_once('quoted_string.php');

class SpecificationParser {
    public $token_patterns = false;

    function __construct() {
        $quoted_string = function($match) {
            $q = new Quoted_String($match);

            return (object)array(
                'token' => 'quoted_string',
                'string' => $match,
                'value' => $q->string
            );
        };

        $heredoc = function($match) {
            return (object)array(
                'token' => 'heredoc',
                'string' => $match
            );
        };

        $this->token_patterns = array(
            "[\\n\\s]+" => function($match) {
                return (object)array(
                    'token' => 'whitespace',
                    'string' => $match
                );
            },
            "[0-9]+\\.\\s" => function($match) {
                $number = substr($match, 0, strlen($match) - 2);

                return (object)array(
                    'token' => 'number',
                    'number' => (int)$number
                );
            },
            "-?(0|([1-9][0-9]*))(\\.[0-9]+)?([eE]-?(0|([1-9][0-9]*)))?" => function($match) {
                return (object)array(
                    'token' => 'numeric',
                    'string' => $match
                );
            },
            "[^'\",.\\s]+" => function($match) {
                return (object)array(
                    'token' => 'string',
                    'string' => $match
                );
            },
            "\"(?:[^\\\\\"]|\\\\.)*\\\"" => $quoted_string,
            "'(?:[^\\\\']|\\\\.)*\\'" => $quoted_string,
            '(,|\\.)' => function($match) {
                return (object)array(
                    'token' => 'punctuation',
                    'string' => $match
                );
            },
            "<<<HTML\\n(.|\\n)+?\\nHTML" => $heredoc,
            "<<<TEXT\\n(.|\\n)+?\\nTEXT" => $heredoc,
            "<<<SQL\\n(.|\\n)+?\\nSQL" => $heredoc,
            "<<<JS\\n(.|\\n)+?\\nJS" => $heredoc
        );
    }

    function parse_text($text) {
        $tokens = array();

        $pending_lexing = $text;

        $bytes_lexed = 0;

        $length = strlen($text);

        while($bytes_lexed != $length) {
            $found_token = false;

            foreach($this->token_patterns as $token_pattern => $callback) {
                $pattern = "/^$token_pattern/";

                $matches = array();

                $matched = preg_match_all($pattern, $pending_lexing, $matches);

                if(!$matched) {
                    continue;
                }

                $match = $matches[0][0];

                $t = $callback($match);

                if($t === false) {
                    continue;
                }

                $t = (object)$t;

                $tokens[] = $t;

                $matchLength = strlen($match);

                $bytes_lexed += $matchLength;
                $pending_lexing = substr($pending_lexing, $matchLength);
                $found_token = true;
                break;
            }

            if(!$found_token) {
                echo("Syntax error: $pending_lexing");
                break;
            }
        }

        return new Specification($tokens);
    }

    function parse_file($path) {
        return $this->parse_text(file_get_contents($path));
    }
}