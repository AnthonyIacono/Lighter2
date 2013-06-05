<?php
require '../framework/framework.php';
Lib::Import(array('strlib', 'html'));

class Specification {
    public $tokens = false;
    public $lastNumber = false;
    public $lastDemonstrating = false;
    public $lastExpect = false;
    public $failedExpectations = 0;
    public $successfulExpectations = 0;
    public $demonstrations = array();
    public $vars = array();

    function __construct($tokens) {
        $this->tokens = $tokens;
        $demonstrations = $this->files_in_directory(dirname(__FILE__) . '/demonstrations');

        foreach($demonstrations as $demo) {
            preg_match('/([a-z_]+).php$/', $demo, $matches);
            $this->demonstrations[$matches[1]] = $demo;
        }
    }

    function run() {
        $token_count = count($this->tokens);

        for($i = 0; $i < $token_count;) {
            $token = $this->tokens[$i];
            $method = strtolower($token->token);

            if($method != 'whitespace' && isset($token->string)) {
                $method = strtolower($token->string);
            }

            $i = $this->{$method}($i);
        }

        echo("{$this->successfulExpectations} expectations succeeded, {$this->failedExpectations} expectations failed.\n\n");
    }

    function number($i) {
        $this->lastNumber = $this->tokens[$i]->number;
        $this->vars = array();

        echo("________________________\n\n{$this->lastNumber}.\n\n");

        return $i + 1;
    }

    function given($i) {
        $token_count = count($this->tokens);
        $statement = '';
        $spec = $this;

        $evaluate = function() use (&$statement, &$spec) {
            $statement = trim($statement);
            $statement = preg_replace('/;$/', '', $statement);
            $spec->eval_and_capture_variables("(\n". $statement . "\n);");

            echo("Given $statement\n\n");

            $statement = '';
        };

        for($i++; $i < $token_count; $i++) {
            $token = $this->tokens[$i];

            if($token->token == 'number') {
                $evaluate();
                return $i;
            }

            if(in_array(strtolower($token->string), array('demonstrate', 'given', 'expect'))) {
                $evaluate();
                return $i;
            }

            $statement .= $token->string;
        }

        if($statement != '') {
            $evaluate();
        }

        return $token_count;
    }

    function whitespace($i) {
        return $i + 1;
    }

    function demonstrate($i) {
        $token_count = count($this->tokens);
        $demonstrating = '';
        $spec = $this;

        $demonstrate = function($demonstrating) use(&$spec) {
            $spec->lastDemonstrating = trim($demonstrating);

            echo("Demonstrate {$spec->lastDemonstrating}\n\n");

            $underscored = StrLib::Underscore($demonstrating, true);
            $demos = array_keys($spec->demonstrations);

            usort($demos, function($a, $b) {
                return strlen($a) < strlen($b) ? 1 : -1;
            });

            foreach($demos as $demo) {
                if(false === strstr($underscored, $demo)) {
                    continue;
                }

                $spec->include_and_capture_variables($spec->demonstrations[$demo]);
                break;
            }
        };

        for($i++; $i < $token_count; $i++) {
            $token = $this->tokens[$i];

            if($token->token == 'number') {
                $demonstrate($demonstrating);
                return $i;
            }

            if(in_array(strtolower($token->string), array('demonstrate', 'given', 'expect'))) {
                $demonstrate($demonstrating);
                return $i;
            }

            $demonstrating .= $token->string;
        }

        $demonstrate($demonstrating);
        return $token_count;
    }

    function expect($i) {
        $statement = '';
        $spec = $this;

        $evaluate = function() use (&$statement, &$spec) {
            $statement = trim($statement);
            $statement = preg_replace('/;$/', '', $statement);
            $spec->eval_and_capture_variables("\$this->lastExpect = (\n\n$statement\n\n) ? true : false;");

            if(!$spec->lastExpect) {
                $spec->failedExpectations++;
                echo("Expect failed:\n\t$statement\n\n");
            }

            else {
                $spec->successfulExpectations++;
                echo("Expect succeeded:\n\t$statement\n\n");
            }

            $statement = '';
        };

        $token_count = count($this->tokens);

        for($x = $i + 1; $x < $token_count; $x++) {
            $token = $this->tokens[$x];

            if($token->token == 'number') {
                $evaluate();
                return $x;
            }

            if(in_array(strtolower($token->string), array('demonstrate', 'given', 'expect'))) {
                $evaluate();
                return $x;
            }

            $statement .= $token->string;
        }

        if($statement != '') {
            $evaluate();
        }

        return $token_count;
    }

    function files_in_directory($directory) {
        $handle = opendir($directory);

        if(!$handle) {
            return false;
        }

        $files = array();

        while (false !== ($entry = readdir($handle))) {
            if(in_array($entry, array('.', '..'))) {
                continue;
            }

            $file = $directory . '/' . $entry;

            if(is_dir($file)) {
                $files = array_merge($files, $this->files_in_directory($file));
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }

    function filter_defined_vars($defined_vars) {
        foreach($defined_vars as $k => $v) {
            if(substr($k, 0, 3) == '___') {
                unset($defined_vars[$k]);
            }
        }

        return $defined_vars;
    }

    function eval_and_capture_variables($___evaluatedStatement) {
        if(isset($this->vars['___evaluatedStatement'])) {
            unset($this->vars['___evaluatedStatement']);
        }

        extract($this->vars, EXTR_OVERWRITE);

        eval($___evaluatedStatement);

        $this->vars = array_merge($this->vars, get_defined_vars());
    }

    function include_and_capture_variables($___includedFile) {
        if(isset($this->vars['___includedFile'])) {
            unset($this->vars['___includedFile']);
        }

        extract($this->vars, EXTR_OVERWRITE);

        include($___includedFile);

        $this->vars = array_merge($this->vars, get_defined_vars());
    }
}