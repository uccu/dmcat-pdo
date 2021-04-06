<?php


return [

    "ddd" => [
        "HOST" => "127.0.0.1",
        "USER" => "root",
        "PASSWORD" => "root",
        "DATABASE" => "test",
        "PREFIX" => "t_",
        "ATTR_TIMEOUT" => 5,
        "ATTR_AUTOCOMMIT" => 1,

        # 0 ERRMODE_SILENT 静默模式
        # 1 ERRMODE_WARNING 警告
        # 2 ERRMODE_EXCEPTION 异常
        "ATTR_ERRMODE" => 2,

        # 1 CASE_UPPER 字段名大写
        # 2 CASE_LOWER 字段名小写
        # 0 CASE_NATURAL 字段名不转换
        "ATTR_CASE" => 0,

        # 长连接
        "ATTR_PERSISTENT" => false
    ]


];
