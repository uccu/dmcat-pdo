<?php

namespace Uccu\DmcatPdo\Exception;

use Exception;

class StreamOpenFailException extends Exception
{
    function __construct($detail)
    {
        parent::__construct('stream open failed:' . $detail);
    }
}
