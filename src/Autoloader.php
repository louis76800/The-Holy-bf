<?php

namespace src;

/**
 * Class Autoloader
 * @package src
 * Loading application class
 */
abstract class Autoloader
{

    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    private static function autoload($class)
    {
        // Windows = \ Linux/Mac = /
        $ds = DIRECTORY_SEPARATOR;
        $dir = __DIR__;
        // On retire le namespace de l'application
        $class = str_replace(__NAMESPACE__ . '\\', '', $class);
        // On remplace les \ par des /
        $classPath = str_replace("\\", $ds, $class);
        $file = "{$dir}{$ds}{$classPath}.php";
        if (is_readable($file)) {
            require_once($file);
        }
    }
}
