<?php

namespace Uccu\DmcatPdo;

use Exception;
use Uccu\DmcatPdo\Exception\NoConfigException;
use Uccu\DmcatPdo\Exception\StreamOpenFailException;

class ModelConfig
{

    static public $_CONFIG_ROOT;
    static public $_CONFIG_FILE = "mysql";
    static public $configs;
    static private $_DATA;
    static public $name;


    static function switchConfig($name = null)
    {
        $name = $name ?? 'default';
        self::$name = $name;
        return true;
    }

    static function init()
    {
        $path = (self::$_CONFIG_ROOT ?? getcwd()) .'/'. self::$_CONFIG_FILE;
        if (is_readable($path . '.json') == true) {
            $file = file_get_contents($path . '.json');
        }
        if (is_readable($path . '.php') == true) {
            try {
                $file = json_encode(require($path . '.php'));
            } catch (Exception $e) {
                throw new StreamOpenFailException('php file formatting error.');
            }
        } else {
            throw new StreamOpenFailException('no conf file or not has permissions.');
        }

        self::$name = 'default';
        self::$_DATA = json_decode($file);
    }

    static function config($name = null)
    {
        $name = $name ?? self::$name ?? 'default';
        if (!isset(self::$_DATA->$name)) {
            throw new NoConfigException($name);
        }
        return self::$_DATA->$name;
    }
}
