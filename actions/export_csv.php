<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$filename = "my_coin_collection_" . date('Y-m-d') . ".csv";

$selected_cols = isset($_GET['cols']) ? $_GET['cols'] : ['country', 'year', 'denomination', 'title', 'grade', 'status', 'price', 'note'];

$column_map = [
    'country'      => 'c.name',
    'year'         => 'cc.year',
    'denomination' => 'cc.denomination',
    'title'        => 'cc.title',
    'grade'        => 'uc.grade',
    'status'       => 'uc.status',
    'price'        => 'uc.price',
    'note'         => 'uc.private_notes'
];

$sql_fields = [];
$csv_headers = [];

foreach ($selected_cols as $col) {
    if (array_key_exists($col, $column_map)) {
        $sql_fields[] = $column_map[$col]; 
        $csv_headers[] = ucfirst($col);    
    }
}

if (empty($sql_fields)) {
    die("No columns selected.");
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); 

fputcsv($output, $csv_headers);

$sql_select_string = implode(', ', $sql_fields);

$sql = "SELECT $sql_select_string
        FROM user_coins uc
        JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
        JOIN countries c ON cc.country_id = c.id
        WHERE uc.user_id = ?
        ORDER BY c.name, cc.year ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;