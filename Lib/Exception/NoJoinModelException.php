<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class NoJoinModelException extends Exception
{
    function __construct()
    {
        parent::__construct('not set join model');
    }
}
