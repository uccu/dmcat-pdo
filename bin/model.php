<?php

use Uccu\DmcatPdo\DB;
use Uccu\DmcatPdo\ModelConfig;

require("vendor/autoload.php");

if (PHP_SAPI != 'cli') {
    exit;
}

array_shift($argv);


$config = [
    '--namespace' => 'namespace',
    '-n' => 'namespace',

    '--config' => 'config',
    '-c' => 'config',

    '--dir' => 'dir',
    '-d' => 'dir',
];


$base = [
    'namespace' => null,
    'config' => __DIR__ . '/',
    'dir' => __DIR__,
];


$method = null;
foreach ($argv as $v) {

    if ($method) {
        $base[$method] = $v;
        $method = null;
        continue;
    }

    if (isset($config[$v])) {
        $method = $config[$v];
    }
}

DB::init("master", $base['config'] . '/');

$db = ModelConfig::$configs->database;
DB::rawQuery('select table_name name from information_schema.tables where table_schema=?', [$db]);
$all = DB::fetchAll();

if (!is_dir($base['dir']))
    !mkdir($base['dir'], 0777, true) && die('文件夹权限不足，无法创建文件！');

foreach ($all as $v) {

    $fullName = $v->name;
    $name = preg_replace("#^cmf_#", "", $fullName);

    $nameArr = explode("_", $name);

    $className = "";

    foreach ($nameArr as $v) {
        $className .= ucfirst($v);
    }

    DB::rawQuery('SHOW FULL COLUMNS FROM ' . $fullName);
    $l = DB::fetchAll();
    foreach ($l as &$e) $e = $e->Field;
    $field = $l;

    $field = json_encode($field);
    $field = str_replace(",", ", ", $field);

    DB::rawQuery('SELECT k.column_name name FROM information_schema.table_constraints t JOIN information_schema.key_column_usage k USING (constraint_name,table_schema,table_name)WHERE t.constraint_type = ? AND t.table_schema = ? AND t.table_name = ?', ['PRIMARY KEY', $db, $fullName]);
    $p = DB::fetchAll();

    $primary = "null";
    if (isset($p[0])) {
        $primary = "'" . $p[0]->name . "'";
    }

    $str = "<?php\n\n";

    if ($base['namespace']) {
        $str .= "namespace " . $base['namespace'] . ";\n\n";
    }


    $str .= "use Uccu\DmcatPdo\Model\BaseModel;\n\nclass " . $className . " extends BaseModel\n{\n    public    \$field   = " . $field . ";\n    public    \$table   = '" . $name . "';\n    public    \$primary = " . $primary . ";\n}\n";

    $fileP = $base['dir'] . '/' . $className . '.php';
    @unlink($fileP);
    $f = fopen($fileP, "w") or die("Unable to open file!");
    fwrite($f, $str);
    fclose($f);
}

exit("success");
