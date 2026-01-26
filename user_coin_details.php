<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_coin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$sql = "SELECT 
            uc.*,
            cc.title, cc.denomination, cc.year, cc.material, cc.period,
            cc.weight, cc.diameter, cc.thickness, cc.mintage,
            cc.catalog_image_front, cc.catalog_image_back,
            c.name as country_name, c.flag_image
        FROM user_coins uc
        JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
        JOIN countries c ON cc.country_id = c.id
        WHERE uc.id = ? AND uc.user_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_coin_id, $user_id]);
$coin = $stmt->fetch();

if (!$coin) {
    die("Coin not found in your collection.");
}

$catalog_id = $coin['catalog_coin_id'];

//how many ppl have it
$stmtCount = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM user_coins WHERE catalog_coin_id = ?");
$stmtCount->execute([$catalog_id]);
$total_owners = $stmtCount->fetchColumn();

//price statistics
$stmtPrices = $pdo->prepare("
    SELECT 
        MIN(price) as min_price, 
        MAX(price) as max_price, 
        AVG(price) as avg_price,
        COUNT(*) as listings_count
    FROM user_coins 
    WHERE catalog_coin_id = ? AND status IN ('sell', 'swap') AND price > 0
");
$stmtPrices->execute([$catalog_id]);
$market_data = $stmtPrices->fetch();

//grade separation
$stmtGrades = $pdo->prepare("
    SELECT grade, COUNT(*) as count 
    FROM user_coins 
    WHERE catalog_coin_id = ? 
    GROUP BY grade 
    ORDER BY count DESC 
    LIMIT 1
");
$stmtGrades->execute([$catalog_id]);
$most_common_grade = $stmtGrades->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My <?php echo htmlspecialchars($coin['title']); ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <a href="index.php" style="color: #666; text-decoration: none; display: inline-block; margin-bottom: 15px;">&larr; Back to Collection</a>

        <div class="image-hero">

            <div class="coin-header-info" style="flex: 1; text-align: left; margin: 0;">
                <h1 class="coin-title" style="margin-bottom: 10px; line-height: 1.2;"><?php echo htmlspecialchars($coin['title']); ?></h1>

                <div class="coin-subtitle" style="font-size: 1.2rem; color: #555;">
                    <div style="margin-bottom: 5px; font-weight: bold; color: var(--accent-color);">
                        <?php echo htmlspecialchars($coin['denomination']); ?>
                    </div>
                    <div>
                        <?php echo htmlspecialchars($coin['country_name']); ?> • <?php echo $coin['year']; ?>
                    </div>
                </div>

                <?php if ($coin['flag_image']): ?>
                    <img src="<?php echo htmlspecialchars($coin['flag_image']); ?>" style="width: 40px; margin-top: 15px; border: 1px solid #eee; border-radius: 4px;">
                <?php endif; ?>
            </div>

            <div style="flex: 1; display: flex; gap: 20px; justify-content: flex-start; align-items: center;">
                <?php
                $front = 'assets/images/no-coin.png';
                if ($coin['own_image_front']) $front = $coin['own_image_front'];
                elseif ($coin['catalog_image_front']) $front = $coin['catalog_image_front'];

                $back = 'assets/images/no-coin.png';
                if ($coin['own_image_back']) $back = $coin['own_image_back'];
                elseif ($coin['catalog_image_back']) $back = $coin['catalog_image_back'];
                ?>
                <img src="<?php echo htmlspecialchars($front); ?>" class="coin-large-img" alt="Front">
                <img src="<?php echo htmlspecialchars($back); ?>" class="coin-large-img" alt="Back">
            </div>

        </div>

        <div class="details-container">
            <div class="details-card">
                <h3 style="margin-top: 0; border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; display: inline-block;">My Collection Data</h3>

                <div class="data-row">
                    <span class="data-label">Grade / Condition</span>
                    <span class="data-value" style="font-weight: bold; background: #eee; padding: 2px 8px; border-radius: 4px;">

                        <a href="grading_guide.php" style="color: inherit; text-decoration: none; border-bottom: 1px dashed #999;" title="View Grading Scale">
                            <?php echo htmlspecialchars($coin['grade']); ?>
                        </a>

                    </span>
                </div>

                <div class="data-row">
                    <span class="data-label">Current Value</span>
                    <span class="data-value" style="color: var(--accent-color); font-weight: bold;">
                        <?php echo $coin['price'] > 0 ? number_format($coin['price'], 2) . ' €' : '-'; ?>
                    </span>
                </div>

                <?php if ($coin['purchase_price'] > 0): ?>
                    <div class="data-row">
                        <span class="data-label">Paid Price</span>
                        <span class="data-value"><?php echo number_format($coin['purchase_price'], 2); ?> €</span>
                    </div>
                <?php endif; ?>

                <?php if ($coin['purchase_location']): ?>
                    <div class="data-row">
                        <span class="data-label">Acquired From</span>
                        <span class="data-value"><?php echo htmlspecialchars($coin['purchase_location']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($coin['purchase_date']): ?>
                    <div class="data-row">
                        <span class="data-label">Date Acquired</span>
                        <span class="data-value"><?php echo date('d M Y', strtotime($coin['purchase_date'])); ?></span>
                    </div>
                <?php endif; ?>
                <div class="data-row">
                    <span class="data-label">Status</span>
                    <span class="data-value"><?php echo ucfirst($coin['status']); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Added On</span>
                    <span class="data-value"><?php echo date('d M Y', strtotime($coin['added_at'])); ?></span>
                </div>

                <div style="margin-top: 20px;">
                    <span class="data-label">My Notes:</span>
                    <p style="background: #f9f9f9; padding: 10px; border-radius: 5px; color: #555; margin-top: 5px; font-style: italic;">
                        <?php echo $coin['private_notes'] ? nl2br(htmlspecialchars($coin['private_notes'])) : 'No notes added.'; ?>
                    </p>
                </div>
            </div>

            <div class="details-card" style="background: #fdfdfd;">
                <h3 style="margin-top: 0; color: #666; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Catalog Specs</h3>

                <div class="data-row">
                    <span class="data-label">Denomination</span>
                    <span class="data-value"><?php echo htmlspecialchars($coin['denomination']); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Material</span>
                    <span class="data-value"><?php echo htmlspecialchars($coin['material']); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Period</span>
                    <span class="data-value"><?php echo htmlspecialchars($coin['period']); ?></span>
                </div>

                <?php if ($coin['weight']): ?>
                    <div class="data-row">
                        <span class="data-label">Weight</span>
                        <span class="data-value"><?php echo $coin['weight']; ?> g</span>
                    </div>
                <?php endif; ?>

                <?php if ($coin['diameter']): ?>
                    <div class="data-row">
                        <span class="data-label">Diameter</span>
                        <span class="data-value"><?php echo $coin['diameter']; ?> mm</span>
                    </div>
                <?php endif; ?>

                <?php if ($coin['thickness']): ?>
                    <div class="data-row">
                        <span class="data-label">Thickness</span>
                        <span class="data-value"><?php echo $coin['thickness']; ?> mm</span>
                    </div>
                <?php endif; ?>

                <?php if ($coin['mintage']): ?>
                    <div class="data-row">
                        <span class="data-label">Mintage</span>
                        <span class="data-value"><?php echo number_format($coin['mintage']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="details-card" style="border-top: 4px solid; color: var(--accent-color); margin-top: 20px; padding-top: 15px; background: #fff;">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        Community Stats
                    </h3>

                    <div class="data-row">
                        <span class="data-label">Total Owners</span>
                        <span class="data-value" style="font-weight: bold;"><?php echo $total_owners; ?> users</span>
                    </div>

                    <div class="data-row">
                        <span class="data-label">Most Common Grade</span>
                        <span class="data-value">
                            <?php echo $most_common_grade ? $most_common_grade['grade'] : 'N/A'; ?>
                        </span>
                    </div>

                    <hr style="margin: 15px 0; border: 0; border-top: 1px dashed #ddd;">
                    <h4 style="margin: 5px 0 10px 0; color: #555;">Market Data</h4>

                    <?php if ($market_data['listings_count'] > 0): ?>
                        <div class="data-row">
                            <span class="data-label">Market Price (Avg)</span>
                            <span class="data-value" style="font-weight: bold;">
                                <?php echo number_format($market_data['avg_price'], 2); ?> €
                            </span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Price Range</span>
                            <span class="data-value" style="font-size: 0.9rem;">
                                <?php echo number_format($market_data['min_price'], 2); ?> -
                                <?php echo number_format($market_data['max_price'], 2); ?> €
                            </span>
                        </div>
                        <div style="margin-top: 10px; font-size: 0.8rem; color: #888; text-align: center;">
                            Based on <?php echo $market_data['listings_count']; ?> listings currently active.
                        </div>
                    <?php else: ?>
                        <p style="color: #999; font-style: italic; text-align: center;">
                            No market data available yet.
                        </p>
                    <?php endif; ?>

                    <div style="margin-top: 20px; text-align: center;">
                        <a href="catalog_coin.php?id=<?php echo $coin['catalog_coin_id']; ?>" class="btn" style="background: #d4af37; color: white; padding: 5px 15px; font-size: 0.85rem;">
                            View All Owners
                        </a>
                    </div>
                </div>

                <div style="margin-top: 20px; text-align: center;">
                    <a href="catalog_coin.php?id=<?php echo $coin['catalog_coin_id']; ?>" style="color: var(--accent-color); font-size: 0.9rem;">
                        View Global Catalog Page &rarr;
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($coin['is_locked']) && $coin['is_locked'] == 1): ?>

            <div class="locked-alert" style="margin-top: 40px;">
                <strong>&#128274; Coin Locked</strong><br>
                This coin is currently part of an active trade request.<br>
                You cannot edit or delete it until the trade is completed or cancelled.
            </div>

        <?php else: ?>

            <div class="action-bar" style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
                <a href="edit_coin.php?id=<?php echo $coin['id']; ?>" class="btn btn-accent" style="min-width: 150px;">
                    Edit Details
                </a>

                <button onclick="openDeleteModal()" class="btn" style="background: #dc3545; color: white; min-width: 150px;">
                    Delete Coin
                </button>
            </div>

        <?php endif; ?>

    </div>

    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <h2 style="margin-top: 0; color: #dc3545;">Delete Coin?</h2>
            <p>Are you sure you want to remove <strong><?php echo htmlspecialchars($coin['title']); ?></strong> from your collection?</p>
            <p style="font-size: 0.9rem; color: #666;">This action cannot be undone.</p>

            <div class="modal-buttons">
                <button onclick="closeDeleteModal()" class="btn" style="background: #ccc; color: #333;">Cancel</button>

                <form action="actions/edit_coin_process.php" method="POST">
                    <input type="hidden" name="user_coin_id" value="<?php echo $coin['id']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn" style="background: #dc3545; color: white;">Yes, Delete It</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('deleteModal');

        function openDeleteModal() {
            modal.style.display = 'flex';
        }

        function closeDeleteModal() {
            modal.style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target == modal) closeDeleteModal();
        }
    </script>
    <?php include 'includes/footer.php'; ?>
</body>

</html>