<?php
// catalog_coin.php
session_start();
require 'config/db.php';

$coin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if ($coin_id === 0) {
    header("Location: countries.php");
    exit;
}

// 1. Взимаме детайлите за монетата
$stmt = $pdo->prepare("
    SELECT cc.*, c.name as country_name, c.flag_image, c.id as country_id
    FROM catalog_coins cc
    JOIN countries c ON cc.country_id = c.id
    WHERE cc.id = ? AND cc.is_approved = 1
");
$stmt->execute([$coin_id]);
$coin = $stmt->fetch();

if (!$coin) {
    die("Coin not found or not approved yet.");
}

// 2. Проверяваме дали ТИ я имаш
$stmtOwn = $pdo->prepare("SELECT * FROM user_coins WHERE user_id = ? AND catalog_coin_id = ?");
$stmtOwn->execute([$user_id, $coin_id]);
$my_copy = $stmtOwn->fetch();

// 3. Взимаме списък с други собственици (Community)
// Показваме само тези, които са 'swap' или 'sell', или всички (по твой избор)
// Тук ще покажем всички, но ще сортираме тези за продажба най-отгоре
$stmtUsers = $pdo->prepare("
    SELECT uc.grade, uc.status, uc.added_at, u.username
    FROM user_coins uc
    JOIN users u ON uc.user_id = u.id
    WHERE uc.catalog_coin_id = ? AND uc.user_id != ?
    ORDER BY FIELD(uc.status, 'sell', 'swap', 'collection') ASC, uc.added_at DESC
    LIMIT 10
");
$stmtUsers->execute([$coin_id, $user_id]);
$owners = $stmtUsers->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($coin['title']); ?> - Details</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .coin-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        
        /* Images */
        .images-container { display: flex; gap: 20px; justify-content: center; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .coin-large-img { max-width: 45%; height: auto; border-radius: 50%; box-shadow: 0 5px 15px rgba(0,0,0,0.15); transition: transform 0.3s; }
        .coin-large-img:hover { transform: scale(1.05); }

        /* Specs Table */
        .specs-table { width: 100%; border-collapse: collapse; }
        .specs-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .specs-label { font-weight: bold; color: #666; width: 40%; }
        
        /* Owners Table */
        .owners-list { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .owners-list th { text-align: left; background: #f1f1f1; padding: 10px; font-size: 0.9rem; }
        .owners-list td { padding: 10px; border-bottom: 1px solid #eee; }
        
        /* Badges */
        .status-badge { padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; text-transform: uppercase; font-weight: bold; }
        .status-sell { background: #ffebee; color: #c62828; }
        .status-swap { background: #fff3e0; color: #ef6c00; }
        .status-collection { background: #e3f2fd; color: #1565c0; }

        @media (max-width: 768px) {
            .details-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <p style="font-size: 0.9rem;">
            <a href="countries.php" style="color: #666;">Countries</a> &gt; 
            <a href="country.php?id=<?php echo $coin['country_id']; ?>" style="color: #666;"><?php echo htmlspecialchars($coin['country_name']); ?></a> &gt; 
            Details
        </p>

        <div class="coin-header">
            <div>
                <h1 style="margin: 0;"><?php echo htmlspecialchars($coin['title']); ?></h1>
                <h3 style="margin: 5px 0; color: var(--accent-color);"><?php echo htmlspecialchars($coin['denomination']); ?> • <?php echo $coin['year']; ?></h3>
            </div>
            
            <div>
                <?php if ($my_copy): ?>
                    <button class="btn" style="background: #28a745; cursor: default;">&#10003; You own this coin</button>
                    <?php else: ?>
                    <a href="add_coin.php" class="btn btn-accent">+ I have this coin</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="details-grid">
            <div>
                <div class="images-container">
                    <?php 
                        $front = $coin['catalog_image_front'] ? $coin['catalog_image_front'] : 'assets/images/no-coin.png';
                        $back = $coin['catalog_image_back'] ? $coin['catalog_image_back'] : 'assets/images/no-coin.png';
                    ?>
                    <img src="<?php echo htmlspecialchars($front); ?>" class="coin-large-img" alt="Front">
                    <img src="<?php echo htmlspecialchars($back); ?>" class="coin-large-img" alt="Back">
                </div>
                
                <?php if ($coin['description']): ?>
                    <div style="margin-top: 20px; background: white; padding: 20px; border-radius: 8px; line-height: 1.6;">
                        <strong>Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($coin['description'])); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <div class="card">
                    <h3>Specifications</h3>
                    <table class="specs-table">
                        <tr><td class="specs-label">Country</td><td><?php echo htmlspecialchars($coin['country_name']); ?></td></tr>
                        <tr><td class="specs-label">Year</td><td><?php echo $coin['year']; ?></td></tr>
                        <tr><td class="specs-label">Value</td><td><?php echo htmlspecialchars($coin['denomination']); ?></td></tr>
                        <tr><td class="specs-label">Material</td><td><?php echo htmlspecialchars($coin['material'] ?? 'Unknown'); ?></td></tr>
                        <tr><td class="specs-label">Period</td><td><?php echo htmlspecialchars($coin['period'] ?? 'Unknown'); ?></td></tr>
                    </table>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h3>Who else has it?</h3>
                    <?php if (count($owners) > 0): ?>
                        <table class="owners-list">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($owners as $owner): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($owner['username']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($owner['grade']); ?></td>
                                        <td>
                                            <?php 
                                                $sClass = 'status-collection';
                                                if ($owner['status'] == 'sell') $sClass = 'status-sell';
                                                if ($owner['status'] == 'swap') $sClass = 'status-swap';
                                            ?>
                                            <span class="status-badge <?php echo $sClass; ?>">
                                                <?php echo ucfirst($owner['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color: #666; font-style: italic;">No one else has marked this coin public yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>