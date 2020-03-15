<?php

namespace Uccu\DmcatPdo\Model;

use Uccu\DmcatPdo\Container\Table;
use Uccu\DmcatPdo\Container\Field;
use Uccu\DmcatPdo\DB;

class BaseModel implements Model
{

    use StaticModel;
    use LogicModel;

    public      $field;
    public      $table;
    public      $primary;

    protected   $fields;
    private     $offset;
    private     $limit;
    private     $order;
    private     $condition;
    private     $group;
    private     $select;
    private     $set;

    private     $on;
    public      $joinModels = [];
    public      $outSql = false;

    public function __construct(...$table)
    {
        if (!$table) {
            $table[] = $this->table;
        }
        if ($table[0] instanceof Table) {
            $this->table = $table[0];
        } else {
            $this->table = new Table(...$table);
        }
        $this->_COLUMNS();
        if (!$this->primary) $this->primary = $this->field[0];
    }

    public function sql($sel = null)
    {
        $this->outSql = $sel ?? true;
        return $this;
    }

    private function _COLUMNS()
    {
        if (!$this->field) {
            DB::rawQuery('SHOW FULL COLUMNS FROM ' . $this->table);
            $this->field = DB::fetchAll();
            foreach ($this->field as &$v) $v = $v->Field;
        }
        return $this;
    }

    public function hasField($field)
    {
        return $field == '*' || in_array($field, $this->field) ? true : false;
    }

    public function importJoin()
    {
        if ($this->joinModels) {
            $this->on = '';
            foreach ($this->joinModels as $m) {
                $foreign = new Field($m->_link['forign'], $m);
                $key = new Field($m->_link['key'], $this);
                $this->on .= ' ' . $m->_link['joinType'] . ' JOIN ' . $m->table->fullTableSql . ' ON ' . $foreign->fullFieldName . ' = ' . $key->fullFieldName;
                $this->on .= $m->importJoin();
            }
            return $this->on;
        }
        return '';
    }
    protected function join($class, $forign = null, $key = null, $join = 'INNER')
    {
        $c = $class::clone();
        if (!$forign) $forign = $c->primary;
        if (!$key) $key = $c->table->tableName . '_id';
        $c->_link = [
            'forign' => $forign,
            'key' => $key,
            'joinType' => $join
        ];
        return $c;
    }


    public static function clone(...$params)
    {
        $type = $params[0] ?? null;

        static $object;
        if (empty($object) || !isset($object[$type])) {
            $object[$type] = new static(...$params);
        }
        return clone $object[$type];
    }
}
