<?php

namespace Uccu\DmcatPdo;

use Pdo;
use Uccu\DmcatPdo\Container\Field;
use Uccu\DmcatPdo\Exception\ArgsCountException;

class DB
{

    public static $pdo;

    public static function init($name = null, $root = null)
    {
        $root && ModelConfig::$_CONFIG_ROOT = $root;
        ModelConfig::init($name);

        $config = ModelConfig::$configs;
        self::$pdo = new PdoMysql(
            [
                'host' => $config->host,
                'user' => $config->user,
                'password' => $config->password,
                'database' => $config->database
            ],
            [
                PDO::ATTR_PERSISTENT    => $config->ATTR_PERSISTENT ?? false,
                PDO::ATTR_TIMEOUT       => $config->ATTR_TIMEOUT ?? 5,
                PDO::ATTR_AUTOCOMMIT    => $config->ATTR_AUTOCOMMIT ?? 1,
                PDO::ATTR_ERRMODE       => $config->ATTR_ERRMODE ?? 2,
                PDO::ATTR_CASE          => $config->ATTR_CASE ?? 0,
            ]
        );
    }

    public static function setConfigRoot($root)
    {
        ModelConfig::$_CONFIG_ROOT = $root;
    }

    public static function rawQuery($sql, $arr = [])
    {
        return self::$pdo->query($sql, $arr);
    }
    public static function fetchAll()
    {
        return self::$pdo->fetchAll();
    }

    public static function start()
    {
        return self::$pdo->start();
    }
    public static function commit()
    {
        return self::$pdo->commit();
    }
    public static function rollback()
    {
        return self::$pdo->rollBack();
    }
    public static function inTransaction()
    {
        return self::$pdo->inTransaction();
    }

    public static function lastInsertId($name = NULL)
    {
        return self::$pdo->lastInsertId($name);
    }
    public static function affectedRowCount()
    {
        return self::$pdo->affectedRowCount();
    }
    public static function freeResult()
    {
        return self::$pdo->freeResult();
    }

    public static function quote($name, $type = PDO::PARAM_STR)
    {
        return self::$pdo->quote($name, $type);
    }

    public static function raw($sql)
    {
        return new DBRawSql($sql);
    }

    public static function format($hql = '', $arg = array(), $model, $checkField = true)
    {

        $sql = preg_replace_callback('#([ =\-,\+\(]|^)([a-z\*][a-zA-Z0-9_\.]*)#', function ($m) use ($model, $checkField) {
            if (substr_count($m[2], '.') == 0 && $checkField && !$model->hasField($m[2])) return $m[0];
            return new Field($m[2], $model);
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
