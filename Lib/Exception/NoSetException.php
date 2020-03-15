<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class NoSetException extends Exception
{
    function __construct()
    {
        parent::__construct('not set any data');
    }
}
