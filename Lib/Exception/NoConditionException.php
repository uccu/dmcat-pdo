<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class NoConditionException extends Exception
{
    function __construct()
    {
        parent::__construct('no condition');
    }
}
