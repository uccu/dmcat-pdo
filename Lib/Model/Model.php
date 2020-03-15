<?php

namespace Uccu\DmcatPdo\Model;

interface Model
{
    public function get();
    public function save();
    public function remove();
    public function add();
    public function find();
    public function getCount($key = '*');

    public function select();
    public function where($sql, ...$container);
    public function set($sql, ...$container);
    public function offset();
    public function limit();
    public function order();
    public function group($name);
    public function page($page = 1, $count = null);
}
