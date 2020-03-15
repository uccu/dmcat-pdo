<?php

namespace Uccu\DmcatPdo\Container;

use Uccu\DmcatPdo\Exception\NoFieldException;
use Uccu\DmcatPdo\Exception\NoJoinModelException;

class Field
{
    public $fieldName;
    public $realFieldName;
    public $fullFieldName;
    public $fullFieldSql;

    function __construct($fieldName, $model, $asField = null)
    {
        if (!$fieldName) {
            throw new NoFieldException;
        }
        $fields = explode('.', $fieldName);
        $count = count($fields);
        if ($count != 1) {
            for ($i = 0; $i < $count - 1; $i++) {
                $field = $fields[$i];
                if (isset($model->joinModels[$field])) {
                    $model = $model->joinModels[$field];
                } elseif (method_exists($model, $field)) {
                    $model = $model->joinModels[$field] = $model->$field();
                } else {
                    throw new NoJoinModelException;
                }
            }
        }

        $fieldName = end($fields);
        $table = $model->table;
        $this->fieldName = $asField ?? $fieldName;
        $this->realFieldName = $fieldName;
        $this->fullFieldName = $table->tableName . '.' . $this->realFieldName;
        $this->fullFieldSql = $this->fullFieldName;
        if ($asField) {
            $this->fullFieldSql .= ' ' . $asField;
        }
    }

    function __toString()
    {
        return $this->fullFieldName;
    }
}
