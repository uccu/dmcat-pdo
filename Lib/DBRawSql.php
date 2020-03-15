<?php

namespace Uccu\DmcatPdo;

class DBRawSql
{
    function __construct($sql)
    {
        $this->sql = $sql;
    }
}
