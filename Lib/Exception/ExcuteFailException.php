<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class ExcuteFailException extends Exception
{
    function __construct($sql, $arr)
    {
        parent::__construct('execute failed');
        $this->sql = $sql;
        $this->sqlArgs = $arr;
    }
}
