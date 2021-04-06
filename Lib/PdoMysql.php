<?php

namespace Uccu\DmcatPdo;

use Exception;
use PDO;
use Uccu\DmcatPdo\Exception\ExcuteFailException;
use Uccu\DmcatPdo\Exception\MysqlConnectException;

class PdoMysql
{

    private $results;
    private $dsn;
    private $database;
    private $host;
    private $port;
    private $charset;
    private $user;
    private $password;
    private $attr = [];

    public function __construct($params, $attr = [])
    {

        $this->database = $params['database'] ?? '';
        $this->host = $params['host'] ?? '127.0.0.1';
        $this->port = $params['port'] ?? '3306';
        $this->unix_socket = $params['unix_socket'] ?? '';

        $this->charset = $params['charset'] ?? 'utf8';
        $this->user = $params['user'] ?? '';
        $this->password = $params['password'] ?? '';

        $dsn = 'mysql:dbname=' . $this->database;

        if ($this->unix_socket) {
            $dsn .= ';unix_socket=' . $this->unix_socket;
        } else {
            $dsn .= ';host=' . $this->host;
            $dsn .= ';port=' . $this->port;
        }

        $this->dsn = $dsn .= ';charset=' . $this->charset;
        $this->attr = $attr;
    }

    function __get($name)
    {
        if ($name === 'connection') {
            try {
                $this->connection = new PDO(
                    $this->dsn,
                    $this->user,
                    $this->password,
                    $this->attr
                );
            } catch (Exception $e) {
                throw new MysqlConnectException;
            }

            return $this->connection;
        }
    }

    public function quote($name, $type = PDO::PARAM_STR)
    {
        return $this->connection->quote($name, $type);
    }

    public function query($sql, $arr = [])
    {
        $this->results = $this->connection->prepare($sql);
        try {
            $result = $this->results->execute($arr);
        } catch (Exception $e) {
            $ex  = new ExcuteFailException;
            $ex->sql = $sql;
            throw $ex;
        }

        return $result;
    }

    public function fetchAll()
    {
        return $this->results->fetchAll(PDO::FETCH_CLASS);
    }

    function start()
    {
        return $this->connection->beginTransaction();
    }
    function commit()
    {
        return $this->connection->commit();
    }
    function rollback()
    {
        return $this->connection->rollBack();
    }
    function inTransaction()
    {
        return $this->connection->inTransaction();
    }

    function lastInsertId($name = NULL)
    {
        return $this->connection->lastInsertId($name);
    }
    function affectedRowCount()
    {
        return $this->results->rowCount();
    }
    function freeResult()
    {
        if (!$this->results) return false;
        return $this->results->closeCursor();
    }
}
