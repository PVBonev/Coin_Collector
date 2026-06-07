<?php
// AWS S3 Configuration (Temporary Lab Credentials)
define('AWS_S3_KEY', '');
define('AWS_S3_SECRET', '');
define('AWS_S3_TOKEN', '');
define('AWS_S3_REGION', 'us-east-1');
define('AWS_S3_BUCKET', 'coin-collector-petko-2026');

// Database Configuration
$host = 'coin-collector-db.cukwcl2czaeg.us-east-1.rds.amazonaws.com'; 
$db   = 'coin_collector';
$user = 'admin';
$pass = ''; 
$port = '3306';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>