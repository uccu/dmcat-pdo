<?php

namespace Uccu\DmcatPdo;

use Uccu\DmcatPdo\Exception\StreamOpenFailException;

class ModelConfig
{

    static public $_CONFIG_ROOT = __DIR__;
    static public $configs;
    static private $_DATA;
    static public $name;


    static function switchConfig($name)
    {
        return self::$configs = self::$_DATA->$name;
    }

    static function init($name = null, $fileName = null)
    {
        $fileName =  $fileName ?? 'mysql';
        $name = $name ?? 'default';

        $path = self::$_CONFIG_ROOT . $fileName . '.json';
        $file = @file_get_contents($path);
        if (!$file) {
            throw new StreamOpenFailException;
        }

        self::$_DATA = json_decode($file);
        self::$configs = self::$_DATA->$name;
        self::$name = $name;
    }

    static function config($name = null)
    {
        $name = $name ?? 'default';
        return self::$_DATA->$name;
    }
}
