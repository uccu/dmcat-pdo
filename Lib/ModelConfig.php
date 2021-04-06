<?php

namespace Uccu\DmcatPdo;

use Exception;
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

        $file = @file_get_contents(self::$_CONFIG_ROOT . $fileName . '.json');
        if (!$file) {
            try {
                $file = json_encode(require_once(self::$_CONFIG_ROOT . $fileName . '.php'));
            } catch (Exception $e) {
                throw new StreamOpenFailException;
            }
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
