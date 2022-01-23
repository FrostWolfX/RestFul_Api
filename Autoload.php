<?php

class Autoload
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $file = $_SERVER['DOCUMENT_ROOT'] . '\\' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
            return false;
        });
    }
}