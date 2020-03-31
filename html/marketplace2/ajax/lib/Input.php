<?php


class Input
{
    // Returns true if $_POST or $_GET contains the key
    public static function has($key) {
        return isset($_GET[$key]) || isset($_POST[$key]);
    }

    // Returns the $_GET or $_POST variable that corresponds to the key
    public static function get($key) {
        return isset($_GET[$key]) ? $_GET[$key] : $_POST[$key];
    }
}