<?php

namespace Uccu\DmcatPdo\Container;

use Iterator;
use ArrayAccess;
use Uccu\DmcatPdo\DB;

class Container implements Iterator, ArrayAccess
{
    private $__data;
    private $_keys = [];
    private $_values = [];
    private $_position = 0;
    public $lastInsertId;
    public $affectedRowCount;

    function __construct($model, $key = null)
    {

        $model->clean();
        if ($model->outSql) {
            $this->sql = $model->sql;
            return;
        }
        $cmd = trim(strtoupper(substr($model->sql, 0, strpos($model->sql, ' '))));
        DB::rawQuery($model->sql);

        if ($cmd === 'UPDATE' || $cmd === 'DELETE' || $cmd === 'REPLACE') {
            $this->affectedRowCount = DB::affectedRowCount();
        } elseif ($cmd === 'INSERT') {
            $this->lastInsertId = DB::lastInsertId();
        } else {
            $data = DB::fetchAll();
            $this->__data = $data;

            $parseData = [];
            foreach ($data as $k => $v) {
                $record  = new Record($v, $model);
                $this->_values[] = $record;
                if ($key) {
                    $parseData[$v->$key] = $record;
                    $this->_keys[] = $v->$key;
                } else {
                    $parseData[$k] = $record;
                    $this->_keys[] = $k;
                }
            }
            $this->__data = $parseData;
        }
        DB::freeResult();
    }

    public function current()
    {
        return $this->_values[$this->_position];
    }
    public function key()
    {
        return $this->_keys[$this->_position];
    }
    public function next()
    {
        ++$this->_position;
    }
    public function rewind()
    {
        $this->_position = 0;
    }
    public function valid()
    {
        return isset($this->_values[$this->_position]);
    }
    function offsetExists($offset)
    {
        return isset($this->__data[$offset]);
    }
    function offsetGet($offset)
    {
        return $this->__data[$offset];
    }

    function offsetSet($offset, $value)
    {
        $position = array_search($offset, $this->_keys);
        if ($position === false) return;
        $this->__data[$offset] = $value;
        $this->_values[$position] = $value;
    }

    function offsetUnset($offset)
    {
        $position = array_search($offset, $this->_keys);
        if ($position === false) return;
        unset($this->__data[$offset]);
        unset($this->_keys[$position]);
        unset($this->_values[$position]);
        $this->_keys = array_values($this->_keys);
        $this->_values = array_values($this->_values);
    }

    function __toString()
    {
        return json_encode($this->__data);
    }

    function keys()
    {
        return $this->_keys;
    }
    function values()
    {
        return $this->_values;
    }

    function toArray()
    {
        return $this->__data;
    }

    function first()
    {
        return reset($this->_values);
    }

    function __get($name)
    {
        return $this->__data[$name] ?? null;
    }
}
