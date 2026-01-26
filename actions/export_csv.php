<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$filename = "my_coin_collection_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

//open output stream
$output = fopen('php://output', 'w');

//BOM so it can read cirillic characters in Excel
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

//writing column headers
fputcsv($output, ['Country', 'Year', 'Denomination', 'Title', 'Grade', 'Status', 'Price', 'Notes']);

$sql = "SELECT 
            c.name as country_name,
            cc.year,
            cc.denomination,
            cc.title,
            uc.grade,
            uc.status,
            uc.price,
            uc.private_notes
        FROM user_coins uc
        JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
        JOIN countries c ON cc.country_id = c.id
        WHERE uc.user_id = ?
        ORDER BY c.name, cc.year ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);

//write row by row
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
