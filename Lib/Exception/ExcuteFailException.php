<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class MysqlConnectException extends Exception
{
    function __construct()
    {
        parent::__construct('execute failed');
    }
}
