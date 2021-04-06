<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class StreamOpenFailException extends Exception
{
    function __construct()
    {
        parent::__construct('stream open failed');
    }
}
