<?php
session_start();
require 'config/db.php';

if (!isset($_GET['q']) || empty($_GET['q'])) {
    header("Location: index.php");
    exit;
}

$query = trim($_GET['q']);
$type = isset($_GET['type']) ? $_GET['type'] : 'coins';

$limit = 30; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$results = [];
$total_results = 0;

if ($type === 'users') {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username LIKE ? AND id != ?");
    $countStmt->execute(["%$query%", $_SESSION['user_id'] ?? 0]);
    $total_results = $countStmt->fetchColumn();

    $sql = "SELECT id, username, profile_image, created_at FROM users WHERE username LIKE ? AND id != ? LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$query%", $_SESSION['user_id'] ?? 0]);
    $results = $stmt->fetchAll();

} elseif ($type === 'countries') {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM countries WHERE name LIKE ?");
    $countStmt->execute(["%$query%"]);
    $total_results = $countStmt->fetchColumn();

    $sql = "SELECT id, name, flag_image, continent FROM countries WHERE name LIKE ? LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$query%"]);
    $results = $stmt->fetchAll();

} else {
    $countSql = "SELECT COUNT(*) 
                 FROM catalog_coins cc
                 WHERE cc.title LIKE ? OR cc.denomination LIKE ? OR cc.year LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $like = "%$query%";
    $countStmt->execute([$like, $like, $like]);
    $total_results = $countStmt->fetchColumn();

    $sql = "SELECT cc.*, c.name as country_name, c.flag_image 
            FROM catalog_coins cc
            JOIN countries c ON cc.country_id = c.id
            WHERE cc.title LIKE ? OR cc.denomination LIKE ? OR cc.year LIKE ?
            LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll();
}

$total_pages = ceil($total_results / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results: <?php echo htmlspecialchars($query); ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1>Search Results</h1>
        <p>Found <strong><?php echo $total_results; ?></strong> results for "<strong><?php echo htmlspecialchars($query); ?></strong>" in <strong><?php echo ucfirst($type); ?></strong></p>
        <hr>

        <?php if (count($results) > 0): ?>
            
            <div class="results-list">

                <?php if ($type === 'users'): ?>
                    <?php foreach ($results as $user): ?>
                        <div class="result-row">
                            <div class="row-left">
                                <?php 
                                    $hasImg = !empty($user['profile_image']) && file_exists($user['profile_image']);
                                    $initial = strtoupper(substr($user['username'], 0, 1));
                                ?>

                                <?php if ($hasImg): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" class="row-img circle" alt="User">
                                <?php else: ?>
                                    <div class="user-initial-circle"><?php echo $initial; ?></div>
                                <?php endif; ?>

                                <div class="row-info">
                                    <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                                    <p>Joined: <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                                </div>
                            </div>
                            <div class="row-action">
                                <a href="view_profile.php?id=<?php echo $user['id']; ?>" class="btn btn-accent btn-sm">View Profile</a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php elseif ($type === 'countries'): ?>
                    <?php foreach ($results as $country): ?>
                        <div class="result-row">
                            <div class="row-left">
                                <img src="<?php echo htmlspecialchars($country['flag_image']); ?>" class="row-img" style="border: 1px solid #ddd;">
                                <div class="row-info">
                                    <h3><?php echo htmlspecialchars($country['name']); ?></h3>
                                    <p>Continent: <?php echo htmlspecialchars($country['continent']); ?></p>
                                </div>
                            </div>
                            <div class="row-action">
                                <a href="country.php?id=<?php echo $country['id']; ?>" class="btn btn-sm" style="background: #d4af37; color: #333;"><strong>View Coins</strong></a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <?php foreach ($results as $coin): ?>
                        <div class="result-row">
                            <div class="row-left">
                                <img src="<?php echo htmlspecialchars($coin['catalog_image_front']); ?>" class="row-img" alt="Coin">
                                <div class="row-info">
                                    <h3><?php echo htmlspecialchars($coin['title']); ?></h3>
                                    <p>
                                        <?php if($coin['flag_image']): ?>
                                            <img src="<?php echo $coin['flag_image']; ?>" style="width: 16px; vertical-align: middle;"> 
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($coin['country_name']); ?> • <?php echo $coin['year']; ?> • <?php echo $coin['denomination']; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="row-action">
                                <a href="catalog_coin.php?id=<?php echo $coin['id']; ?>" class="btn btn-accent btn-sm">Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div> <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?q=<?php echo urlencode($query); ?>&type=<?php echo $type; ?>&page=<?php echo $page - 1; ?>" class="page-link">&laquo; Previous</a>
                    <?php else: ?>
                        <span class="page-link disabled">&laquo; Previous</span>
                    <?php endif; ?>

                    <span style="display: flex; align-items: center; color: #777;">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </span>

                    <?php if ($page < $total_pages): ?>
                        <a href="?q=<?php echo urlencode($query); ?>&type=<?php echo $type; ?>&page=<?php echo $page + 1; ?>" class="page-link">Next &raquo;</a>
                    <?php else: ?>
                        <span class="page-link disabled">Next &raquo;</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <h3 style="color: #777;">No results found.</h3>
                <p>Try searching for something else.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>