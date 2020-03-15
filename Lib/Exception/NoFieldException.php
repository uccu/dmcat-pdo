<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class NoFieldException extends Exception
{
    function __construct()
    {
        parent::__construct('no field');
    }
}
