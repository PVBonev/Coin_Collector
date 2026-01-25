<?php
// config/db.php

$host = '127.0.0.1';
$db   = 'coin_collector_db';
$user = 'user'; //change if u use dif name in XAMPP
$pass = 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4"; 
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