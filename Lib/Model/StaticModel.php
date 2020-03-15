<?php

namespace Uccu\DmcatPdo\Model;

use Uccu\DmcatPdo\DB;
use Uccu\DmcatPdo\Container\Container;
use Uccu\DmcatPdo\Exception\NoSetException;
use Uccu\DmcatPdo\Exception\NoConditionException;
use Uccu\DmcatPdo\Container\Field;

trait StaticModel
{

    public function get($key = null)
    {
        $this->importJoin();
        $sql = 'SELECT ';
        !$this->select && $this->select($this->field);

        $sql .= $this->select . ' FROM ';
        $sql .= $this->table->fullTableSql;

        if ($this->on) $sql .= $this->on;
        if ($this->condition) $sql .= ' WHERE ' . $this->condition;
        if ($this->group) $sql .= ' GROUP BY ' . $this->group;
        if ($this->order) $sql .= ' ORDER BY ' . $this->order;
        if ($this->limit) $sql .= ' LIMIT ' . $this->limit;
        if ($this->offset) $sql .= ' OFFSET ' . $this->offset;

        $this->sql = $sql;
        return new Container($this, $key);
    }

    public function add($replace = false)
    {
        $this->importJoin();
        $sql = 'INSERT INTO ';
        if ($replace) $sql = 'REPLACE INTO ';
        $sql .= $this->table->fullTableName;

        if (!$this->set) throw new NoSetException;
        $sql .= ' SET ' . preg_replace('#`\w+`\.#', '', $this->set);

        $this->sql = $sql;
        return new Container($this);
    }
    public function save($id = null)
    {
        $this->importJoin();
        $sql = 'UPDATE ';
        $sql .= $this->table->fullTableSql;
        if ($this->on) $sql .= $this->on;

        if (!$this->set) throw new NoSetException;
        $sql .= ' SET ' . $this->set;

        $container = func_get_args();
        if (count($container)) {
            $this->condition = '';
            $this->where([$this->primary => $id]);
            $sql .= ' WHERE ' . $this->condition;
        } else if ($this->condition) {
            $sql .= ' WHERE ' . $this->condition;
        } else {
            throw new NoConditionException;
        }
        $this->sql = $sql;
        return new Container($this);
    }
    public function remove($id = null)
    {
        $this->importJoin();
        $sql = 'DELETE FROM ';
        $sql .= $this->table->fullTableName;

        $container = func_get_args();
        if (count($container)) {
            $this->condition = '';
            $this->where([$this->primary => $id]);
        }
        if (!$this->condition) {
            throw new NoConditionException;
        }
        $sql .= ' WHERE ' . preg_replace('#`\w+`\.#', '', $this->condition);

        $this->sql = $sql;
        return new Container($this);
    }

    public function find($id = null)
    {
        $this->limit(1);
        $container = func_get_args();
        if (count($container)) {
            $this->condition = '';
            $this->where([$this->primary => $id]);
        }
        return $this->get()->first();
    }
    public function getCount($key = '1', $distinct = false)
    {
        if ($key != '1' && $key != '*') {
            $key = new Field($key, $this);
            $key = $key->fullFieldName;
            if ($distinct) $key = 'DISTINCT ' . $key;
        }
        $count = $this->select(DB::raw('count(' . $key . ')'))->find()->first();
        return $count + 0;
    }

    public function getField($field, $key = null)
    {
        $this->select = '';
        $container = $this->select($field, $key)->get($key);
        foreach ($container as $k => $v) {
            $container[$k] = $v->$field;
        }
        return $container;
    }
}
