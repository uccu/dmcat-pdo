<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class PrimarySetException extends Exception
{
    function __construct()
    {
        parent::__construct('primary key can not be set');
    }
}
