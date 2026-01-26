<?php

//instead of 127.0.0.1 we use the service name from docker-compose
$host = 'db'; 

$db   = 'coin_collector_db';
$user = 'user';     // must match docker-compose environment
$pass = 'password'; // must match docker-compose environment
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
