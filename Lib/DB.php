<?php

namespace Uccu\DmcatPdo;

use Pdo;
use Uccu\DmcatPdo\Container\Field;
use Uccu\DmcatPdo\Exception\ArgsCountException;

class DB
{

    public static $pdos = [];
    private static $initConfig = false;

    public static function getPdo()
    {

        self::initConfig();
        if (isset(self::$pdos[ModelConfig::$name])) {
            return self::$pdos[ModelConfig::$name];
        }

        $config = ModelConfig::config(ModelConfig::$name);
        self::$pdos[ModelConfig::$name] = new PdoMysql(
            [
                'host' => $config->HOST ?? null,
                'user' => $config->USER ?? null,
                'password' => $config->PASSWORD ?? null,
                'database' => $config->DATABASE ?? null,
                'port' => $config->PORT ?? null,
                'charset' => $config->CHARSET ?? null,
                'unix_socket' => $config->UNIX_SOCKET ?? null,
            ],
            [
                PDO::ATTR_PERSISTENT    => $config->ATTR_PERSISTENT ?? false,
                PDO::ATTR_TIMEOUT       => $config->ATTR_TIMEOUT ?? 5,
                PDO::ATTR_AUTOCOMMIT    => $config->ATTR_AUTOCOMMIT ?? 1,
                PDO::ATTR_ERRMODE       => $config->ATTR_ERRMODE ?? 2,
                PDO::ATTR_CASE          => $config->ATTR_CASE ?? 0,
            ]
        );

        return self::$pdos[ModelConfig::$name];
    }

    static function config($name = null)
    {
        self::initConfig();
        return ModelConfig::config($name);
    }

    public static function initConfig()
    {
        if (!self::$initConfig) {
            ModelConfig::init();
            self::$initConfig = true;
        }
    }

    public static function switchConfig($name = null)
    {
        self::initConfig();
        ModelConfig::switchConfig($name);
    }

    public static function setConfigRoot($root)
    {
        ModelConfig::$_CONFIG_ROOT = $root;
    }

    public static function setConfigFile($file)
    {
        ModelConfig::$_CONFIG_FILE = $file;
    }

    public static function rawQuery($sql, $arr = [])
    {
        self::initConfig();
        return self::getPdo()->query($sql, $arr);
    }
    public static function fetchAll()
    {
        return self::getPdo()->fetchAll();
    }

    public static function start()
    {
        self::initConfig();
        return self::getPdo()->start();
    }
    public static function commit()
    {
        return self::getPdo()->commit();
    }
    public static function rollback()
    {
        return self::getPdo()->rollBack();
    }
    public static function inTransaction()
    {
        self::initConfig();
        return self::getPdo()->inTransaction();
    }

    public static function lastInsertId($name = NULL)
    {
        return self::getPdo()->lastInsertId($name);
    }
    public static function affectedRowCount()
    {
        return self::getPdo()->affectedRowCount();
    }
    public static function freeResult()
    {
        return self::getPdo()->freeResult();
    }

    public static function quote($name, $type = PDO::PARAM_STR)
    {
        self::initConfig();
        return self::getPdo()->quote($name, $type);
    }

    public static function raw($sql)
    {
        return new DBRawSql($sql);
    }

    public static function format($hql = '', $arg = array(), $model, $checkField = true)
    {

        $sql = preg_replace_callback('#([ =\-,\+\(]|^)([a-z\*][a-zA-Z0-9_\.]*)#', function ($m) use ($model, $checkField) {
            if (substr_count($m[2], '.') == 0 && $checkField && !$model->hasField($m[2])) return $m[0];
            return $m[1] . new Field($m[2], $model);
        }, $hql);
        $count = substr_count($sql, '%');

        if (!$count) {
            return $sql;
        } elseif ($count > count($arg)) {
            throw new ArgsCountException;
        }

        $len = strlen($sql);
        $i = $find = 0;
        $ret = '';
        while ($i <= $len && $find < $count) {
            if (substr($sql, $i, 1) == '%') {
                $next = substr($sql, $i + 1, 1);
                switch ($next) {
                    case 'F':
                        $ret .= new Field($arg[$find], $model);
                        break;
                    case 'N':
                        $field = new Field($arg[$find], $model);
                        $ret .= $field->fieldName;
                        break;
                    case 's':
                        $ret .= DB::quote(serialize($arg[$find]));
                        break;
                    case 'j':
                        $ret .= DB::quote(json_encode($arg[$find]));
                        break;
                    case 'f':
                        $ret .= sprintf('%F', $arg[$find]);
                        break;
                    case 'd':
                        $ret .= floor($arg[$find]);
                        break;
                    case 'i':
                        $ret .= $arg[$find];
                        break;
                    case 'b':
                        $ret .= DB::quote(base64_encode($arg[$find]));
                        break;
                    case 'c':
                        foreach ($arg[$find] as $k => $v) {
                            $arg[$find][$k] = DB::quote($v);
                        }
                        $ret .= implode(',', $arg[$find]);
                        break;
                    case 'a':
                        foreach ($arg[$find] as $k => $v) {
                            $arg[$find][$k] = DB::quote($v);
                        }
                        $ret .= implode(' AND ', $arg[$find]);
                        break;
                    default:
                        $ret .= DB::quote($arg[$find]);
                        break;
                }

                $i++;
                $find++;
            } else {
                $ret .= substr($sql, $i, 1);
            }
            $i++;
        }
        if ($i < $len) {
            $ret .= substr($sql, $i);
        }
        return $ret;
    }
}
