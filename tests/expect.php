<?php

function __LS_print() {
    $args = func_get_args();

    foreach($args as $arg) {
        $is_object = is_object($arg);

        if(is_array($arg) || $is_object) {
            if($is_object && method_exists($arg, '__toString')) {
                echo($arg->__toString());
                continue;
            }

            echo(trim(json_encode($arg)));
            continue;
        }

        echo((string)$arg);
    }
}

function __LS_str($arg) {
    $is_object = is_object($arg);

    if(is_array($arg) || $is_object) {
        if($is_object && method_exists($arg, '__toString')) {
            return "'" . $arg->__toString() . "'";
        }

        return trim(json_encode($arg));
    }

    return "'" . (string)$arg . "'";
}

function gt($l, $r) {
    __LS_print("Expecting ", __LS_str($l), " to be greater than ", __LS_str($r), "\n\n");

    return $l > $r;
}

function lt($l, $r) {
    __LS_print("Expecting ", __LS_str($l), " to be less than ", __LS_str($r), "\n\n");

    return $l < $r;
}

function e($l, $r, $strict = false) {
    __LS_print("Expecting ", __LS_str($l), " to be ", $strict ? 'exactly ' : '', "equal to ", __LS_str($r), "\n\n");

    if($strict) {
        return $l === $r;
    }

    return $l == $r;
}

function tr($o) {
    __LS_print("Expecting ", __LS_str($o), " to be truthy\n\n");

    if(empty($o)) {
        return false;
    }

    return true;
}

function fl($o) {
    __LS_print("Expecting ", __LS_str($o), " to be falsy\n\n");

    if(empty($o)) {
        return true;
    }

    return false;
}

function in($needle, $haystack, $strict = false) {
    __LS_print("Expecting ", __LS_str($needle), " to be inside ", __LS_str($haystack), "\n\n");

    if(!is_array($haystack)) {
        return false !== strstr((string)$haystack, (string)$needle);
    }

    return in_array($needle, $haystack, $strict);
}