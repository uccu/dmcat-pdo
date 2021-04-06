<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class NoConfigException extends Exception
{
    function __construct($name)
    {
        parent::__construct('config:[' . $name . '] is not exist');
    }
}
