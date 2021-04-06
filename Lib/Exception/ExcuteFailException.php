<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class ExcuteFailException extends Exception
{
    function __construct()
    {
        parent::__construct('execute failed');
    }
}
