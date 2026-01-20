<?php
session_start();
require 'config/db.php';

$sql = "SELECT c.id, c.name, c.flag_image, COUNT(cc.id) as coin_count 
        FROM countries c 
        LEFT JOIN catalog_coins cc ON c.id = cc.country_id 
        GROUP BY c.id 
        ORDER BY c.name ASC";

$stmt = $pdo->query($sql);
$countries = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Countries - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1 style="border-bottom: 2px solid var(--accent-color); display: inline-block; padding-bottom: 5px;">
            Countries
        </h1>
        <p style="margin-bottom: 30px;">Select a country to view its coin catalog.</p>

        <div class="coin-grid">
            <?php foreach ($countries as $country): ?>

                <a href="country.php?id=<?php echo $country['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="coin-card">
                        <div class="flag-wrapper">
                            <?php if ($country['flag_image']): ?>
                                <img src="<?php echo htmlspecialchars($country['flag_image']); ?>" alt="<?php echo htmlspecialchars($country['name']); ?>">
                            <?php else: ?>
                                <div style="width:100%; height:100%; background:#ccc; display:flex; align-items:center; justify-content:center;">No Flag</div>
                            <?php endif; ?>
                        </div>

                        <div class="coin-info" style="text-align: center;">
                            <div class="coin-title" style="font-size: 1.2rem;">
                                <?php echo htmlspecialchars($country['name']); ?>
                            </div>
                            <div class="country-stats">
                                <span style="color: var(--accent-color);">
                                    <?php echo $country['coin_count']; ?>
                                </span> coin types
                            </div>
                        </div>
                    </div>
                </a>

            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>