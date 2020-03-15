<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class ArgsCountException extends Exception
{
    function __construct()
    {
        parent::__construct('args count wrong');
    }
}
