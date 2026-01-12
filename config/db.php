<?php
// config/db.php

$host = 'localhost';
$db   = 'coin_collector_db';
$user = 'root'; //change if u use dif name in XAMPP
$pass = '';     //change if u have pas
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    //dont show sensitive info in production
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>