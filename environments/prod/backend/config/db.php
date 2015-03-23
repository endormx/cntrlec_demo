<?php
$dbhost = $_SERVER['SDEEA_HOSTNAME'];
$dbname = $_SERVER['SDEEA_DB'];

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host={$dbhost};dbname={$dbname}',
    'username' => $_SERVER['SDEEA_USERNAME'],
    'password' => $_SERVER['SDEEA_PASSWORD'],
    'charset' => 'utf8',
];
