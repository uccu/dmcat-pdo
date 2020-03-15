<?php

namespace Uccu\DmcatPdo\Model;

use Uccu\DmcatPdo\Container\Field;
use Uccu\DmcatPdo\DBRawSql;
use Uccu\DmcatPdo\DB;

trait LogicModel
{

    public function where($sql, ...$container)
    {
        if (is_string($sql)) {
            $this->condition .= ($this->condition ? ' AND (' : '') . DB::format($sql, $container, $this) . ($this->condition ? ' )' : '');
        } elseif (is_array($sql) || is_object($sql)) {
            foreach ($sql as $k => $v) {
                if (is_array($v)) call_user_func_array(array($this, 'where'), $v);
                elseif (is_string($v) || is_float($v) || is_int($v)) call_user_func_array(array($this, 'where'), array('%F = %n', $k, $v));
                elseif (is_null($v)) call_user_func_array(array($this, 'where'), array('%F IS NULL', $k));
            }
        }
        return $this;
    }
    public function set($sql, ...$container)
    {
        if (is_string($sql)) {
            $this->set .= ($this->set ? ' ,' : '') . DB::format($sql, $container, $this);
        } elseif (is_array($sql) || is_object($sql)) {
            foreach ($sql as $k => $v) {
                if (is_array($v)) call_user_func_array(array($this, 'set'), $v);
                elseif (!is_object($v)) call_user_func_array(array($this, 'set'), array('%F = %n', $k, $v));
            }
        }
        return $this;
    }
    public function order(...$container)
    {
        $count = count($container);
        if (!$count) return $this;

        if ($container[0] instanceof DBRawSql) {
            $this->order = $container[0]->sql;
            return $this;
        }

        $orders = [];
        foreach ($container as $k => $field) {

            if (is_numeric($k)) {
                $arr = explode(' ', $field);
                $field = $arr[0];
                $desc = $arr[1] ?? null;
            } else {
                $desc = $field;
                $field = $k;
            }
            $field = new Field($field,  $this);
            $orders[] = $field->fullFieldName . ' ' . (!$desc || strtoupper($container[1]) === 'ASC' ? 'ASC' : 'DESC');
        }

        $this->order = implode(', ', $orders);
        return $this;
    }
    public function offset($i = null)
    {
        if (!$i) return $this;
        $i = floor($i);
        $i < 0 && $i = 0;
        $this->offset = $i;
        return $this;
    }
    public function limit($i = null)
    {
        if (!$i) return $this;
        $i = floor($i);
        $i < 1 && $i = 1;
        $this->limit = $i;
        return $this;
    }
    public function page($page = 1, $count = null)
    {
        $this->limit($count);
        $count = $this->limit;
        if ($count) {
            $page = floor($page);
            if ($page < 1) $page = 1;
            $offset = ($page - 1) * $count;
            $this->offset($offset);
        }
        return $this;
    }
    public function select(...$container)
    {
        if ($container[0] instanceof DBRawSql) {
            $this->select = $container[0]->sql;
            return $this;
        }

        $fields = array();
        if (is_array($container[0])) $container = $container[0];
        foreach ($container as $v) {
            if (!$v) continue;
            $arr = explode('>', $v);
            $v = $arr[0];
            $as = $arr[1] ?? null;
            $field = new Field($v, $this, $as);
            $fields[] = $field->fullFieldSql;
        }
        if ($fields) $this->select = implode(',', $fields);
        return $this;
    }
    public function group($name)
    {
        $field = new Field($name, $this);
        $this->group = $field->fullFieldName;
        return $this;
    }
}
