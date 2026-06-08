<?php

date_default_timezone_set('Asia/Irkutsk');

$db_host = 'mysql';
$db_name = 'calendar_app';
$db_user = 'root';
$db_pass = 'root';

try {
    $dbo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}
