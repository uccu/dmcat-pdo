<?php

namespace Uccu\DmcatPdo\Container;

use Uccu\DmcatPdo\ModelConfig;

class Table
{

    public $tableName;
    public $realTableName;
    public $fullTableName;
    public $fullTableSql;

    function __construct($tableName, $asTable = null, $database = null)
    {
        $prefix = ModelConfig::$configs->prefix;
        if (!$database) $database = ModelConfig::$configs->database;
        $this->tableName = $asTable ?? $tableName;
        $this->realTableName = $prefix . $tableName;
        $this->fullTableName = '`' . $database . '`.`' . $this->realTableName . '`';
        $this->fullTableSql = $this->fullTableName;
        if ($asTable) {
            $this->fullTableSql .= ' `' . $asTable . '`';
        } elseif ($this->tableName != $this->realTableName) {
            $this->fullTableSql .= ' `' . $this->tableName . '`';
        }
    }

    function __toString()
    {
        return $this->fullTableName;
    }
}
