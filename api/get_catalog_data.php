<?php
require '../config/db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$country_id = isset($_GET['country_id']) ? (int)$_GET['country_id'] : 0;

if ($type === 'years' && $country_id > 0) {
    $stmt = $pdo->prepare("SELECT DISTINCT year FROM catalog_coins WHERE country_id = ? AND is_approved = 1 ORDER BY year DESC");
    $stmt->execute([$country_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));

} elseif ($type === 'coins' && $country_id > 0) {
    $year = isset($_GET['year']) && $_GET['year'] !== '' ? (int)$_GET['year'] : 0;
    
    $sql = "SELECT id, denomination, title, catalog_image_front 
            FROM catalog_coins 
            WHERE country_id = ? AND is_approved = 1";
    
    $params = [$country_id];

    if ($year > 0) {
        //if we have a year selected, filter by it
        $sql .= " AND year = ?";
        $params[] = $year;
    } else {
        // if no year selected, limit results to 10 most popular (by title)
        $sql .= " ORDER BY title ASC LIMIT 10"; 
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} else {
    echo json_encode([]);
}
?>