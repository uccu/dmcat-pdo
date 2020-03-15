<?php

namespace Uccu\DmcatPdo\Container;

use Uccu\DmcatPdo\Model\Model;
use Uccu\DmcatPdo\Exception\PrimarySetException;

class Record
{

    private $_model;
    private $_fields;
    private $_values;
    private $_setData = [];
    private $_set = false;

    function __construct($objData, Model $model)
    {
        foreach ($objData as $k => $v) {
            $this->$k = $v;
            $this->_fields[] = $k;
            $this->_values[] = $v;
        }
        $this->_set = true;
        $this->_model = $model;
    }

    function __get($key)
    {
        return $this->_null ? null : '';
    }

    function __toString()
    {
        return json_encode($this);
    }

    function remove()
    {
        return $this->_model->remove($this->{$this->_model->primary});
    }

    function __set($key, $value)
    {
        if (!$this->_set) {
            $this->$key = $value;
            return;
        }
        if ($this->_model->primary == $key) {
            throw new PrimarySetException;
        }
        if (isset($this->$key)) {
            $this->_setData[$this->$key] = $value;
        }
    }

    function save()
    {
        return $this->_model->set($this->_setData)->save($this->{$this->_model->primary});
    }

    function first()
    {
        return reset($this->_values);
    }
}
