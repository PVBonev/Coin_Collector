<?php
session_start();
require 'config/db.php';

$coin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if ($coin_id === 0) {
    header("Location: countries.php");
    exit;
}

//take coin details from catalog
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

//check if we have it
$stmtOwn = $pdo->prepare("SELECT * FROM user_coins WHERE user_id = ? AND catalog_coin_id = ?");
$stmtOwn->execute([$user_id, $coin_id]);
$my_copy = $stmtOwn->fetch();



//statistics section
$stmtPrices = $pdo->prepare("
    SELECT 
        COUNT(*) as listings_count,
        MIN(price) as min_price, 
        MAX(price) as max_price, 
        AVG(price) as avg_price
    FROM user_coins 
    WHERE catalog_coin_id = ? AND status IN ('sell', 'swap') AND price > 0
");
$stmtPrices->execute([$coin_id]);
$market = $stmtPrices->fetch();

$stmtCount = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM user_coins WHERE catalog_coin_id = ?");
$stmtCount->execute([$coin_id]);
$total_owners = $stmtCount->fetchColumn();

$stmtGrades = $pdo->prepare("
    SELECT grade, COUNT(*) as count 
    FROM user_coins 
    WHERE catalog_coin_id = ? 
    GROUP BY grade 
    ORDER BY count DESC 
    LIMIT 3
");
$stmtGrades->execute([$coin_id]);
$top_grades = $stmtGrades->fetchAll();

$stmtStatus = $pdo->prepare("
    SELECT status, COUNT(*) as count 
    FROM user_coins 
    WHERE catalog_coin_id = ? AND status IN ('sell', 'swap')
    GROUP BY status
");
$stmtStatus->execute([$coin_id]);
$availability = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR);
$for_sale = $availability['sell'] ?? 0;
$for_swap = $availability['swap'] ?? 0;

$stmtUsers = $pdo->prepare("
    SELECT uc.id as user_coin_id, uc.grade, uc.status, uc.added_at, u.username, u.profile_image, u.id as owner_id
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
                    <a href="user_coin_details.php?id=<?php echo $my_copy['id']; ?>" class="btnblk" style="background: #d4af37;"> View Your Coin</a>
                <?php else: ?>
                    <a href="add_coin.php?catalog_coin_id=<?php echo $coin['id']; ?>" class="btn btn-accent">+ I have this coin</a>
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
                    <div style="margin-top: 20px; background: white; padding: 20px; border-radius: 8px; line-height: 1.6; border: 1px solid #eee;">
                        <strong>Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($coin['description'])); ?>
                    </div>
                <?php endif; ?>

                <div class="card" style="margin-top: 20px;">
                    <h3>Specifications</h3>
                    <table class="specs-table">
                        <tr>
                            <td class="specs-label">Country</td>
                            <td><?php echo htmlspecialchars($coin['country_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="specs-label">Year</td>
                            <td><?php echo $coin['year']; ?></td>
                        </tr>
                        <tr>
                            <td class="specs-label">Denomination</td>
                            <td><?php echo htmlspecialchars($coin['denomination']); ?></td>
                        </tr>
                        <tr>
                            <td class="specs-label">Material</td>
                            <td><?php echo htmlspecialchars($coin['material'] ?? 'Unknown'); ?></td>
                        </tr>
                        <tr>
                            <td class="specs-label">Period</td>
                            <td><?php echo htmlspecialchars($coin['period'] ?? 'Unknown'); ?></td>
                        </tr>

                        <?php if (!empty($coin['weight'])): ?>
                            <tr>
                                <td class="specs-label">Weight</td>
                                <td><?php echo $coin['weight']; ?> g</td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($coin['diameter'])): ?>
                            <tr>
                                <td class="specs-label">Diameter</td>
                                <td><?php echo $coin['diameter']; ?> mm</td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($coin['thickness'])): ?>
                            <tr>
                                <td class="specs-label">Thickness</td>
                                <td><?php echo $coin['thickness']; ?> mm</td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($coin['mintage'])): ?>
                            <tr>
                                <td class="specs-label">Mintage</td>
                                <td><?php echo number_format($coin['mintage']); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <div>
                <div class="card" style="margin-bottom: 20px;">
                    <h3 style="margin-top: 0;">Analytics & Market Data</h3>

                    <div style="display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
                        <div class="stat-badge">
                            Owners: <span class="stat-value"><?php echo $total_owners; ?></span>
                        </div>
                        <div class="stat-badge">
                            For Sale: <span class="stat-value" style="color: #dc3545;"><?php echo $for_sale; ?></span>
                        </div>
                        <div class="stat-badge">
                            For Swap: <span class="stat-value" style="color: #ef6c00;"><?php echo $for_swap; ?></span>
                        </div>
                    </div>

                    <?php if ($market['listings_count'] > 0): ?>
                        <div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                            <strong style="color: #555;">Market Value (Est.)</strong><br>
                            <span style="font-size: 1.2rem; font-weight: bold; color: #28a745;">
                                <?php echo number_format($market['avg_price'], 2); ?> €.
                            </span>
                            <div style="font-size: 0.8rem; color: #888;">
                                Range: <?php echo number_format($market['min_price'], 2); ?> - <?php echo number_format($market['max_price'], 2); ?> €.
                            </div>
                        </div>
                    <?php else: ?>
                        <p style="color: #999; font-style: italic; font-size: 0.9rem;">No active market listings yet.</p>
                    <?php endif; ?>

                    <?php if (count($top_grades) > 0): ?>
                        <div style="margin-top: 10px;">
                            <strong style="font-size: 0.9rem; color: #555;">Most Common Grades:</strong>
                            <?php
                            $max_g_count = $top_grades[0]['count'];
                            foreach ($top_grades as $g):
                                $width = ($g['count'] / $max_g_count) * 100;
                            ?>
                                <div class="grade-row">
                                    <span style="width: 40px; font-weight: bold;"><?php echo $g['grade']; ?></span>
                                    <div class="grade-bar" style="width: <?php echo $width * 0.6; ?>%;"></div>
                                    <span>(<?php echo $g['count']; ?>)</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
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
                                            <a href="view_profile.php?id=<?php echo $owner['owner_id']; ?>" style="text-decoration: none; color: #333; display: flex; align-items: center; gap: 8px;">
                                                <?php if ($owner['profile_image']): ?>
                                                    <img src="<?php echo htmlspecialchars($owner['profile_image']); ?>" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                                                <?php endif; ?>
                                                <strong><?php echo htmlspecialchars($owner['username']); ?></strong>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="grading_guide.php" title="View Grading Scale" style="color: inherit; text-decoration: none; border-bottom: 1px dashed #ccc; cursor: help;">
                                                <?php echo htmlspecialchars($owner['grade']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            $sClass = 'status-collection';
                                            if ($owner['status'] == 'sell') $sClass = 'status-sell';
                                            if ($owner['status'] == 'swap') $sClass = 'status-swap';
                                            ?>

                                            <?php if ($owner['status'] == 'swap' && isset($_SESSION['user_id']) && $owner['owner_id'] != $_SESSION['user_id']): ?>
                                                <a href="swap_request.php?receiver_id=<?php echo $owner['owner_id']; ?>&wanted_coin_id=<?php echo $owner['user_coin_id']; ?>"
                                                    title="Start Trade" style="text-decoration: none;">
                                                    <span class="status-badge status-swap" style="cursor: pointer; border: 1px solid #e6a800;">
                                                        Swap Coin
                                                    </span>
                                                </a>

                                            <?php elseif ($owner['status'] == 'sell' && isset($_SESSION['user_id']) && $owner['owner_id'] != $_SESSION['user_id']): ?>
                                                <a href="buy_request.php?coin_id=<?php echo $owner['user_coin_id']; ?>"
                                                    title="Buy Coin" style="text-decoration: none;">
                                                    <span class="status-badge status-sell" style="cursor: pointer; border: 1px solid #dc3545;">
                                                        Buy Coin
                                                    </span>
                                                </a>

                                            <?php else: ?>
                                                <span class="status-badge <?php echo $sClass; ?>">
                                                    <?php echo ucfirst($owner['status']); ?>
                                                </span>
                                            <?php endif; ?>
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
    <?php include 'includes/footer.php'; ?>

</body>

</html>