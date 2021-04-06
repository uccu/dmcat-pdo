<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class ExcuteFailException extends Exception
{
    public $sql;
    function __construct()
    {
        parent::__construct('mysql connect failed');
    }
}
