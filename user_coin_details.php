<?php
// user_coin_details.php
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
            <?php 
                // Front Image Logic
                $front = 'assets/images/no-coin.png';
                if ($coin['own_image_front']) $front = $coin['own_image_front'];
                elseif ($coin['catalog_image_front']) $front = $coin['catalog_image_front'];

                // Back Image Logic
                $back = 'assets/images/no-coin.png';
                if ($coin['own_image_back']) $back = $coin['own_image_back'];
                elseif ($coin['catalog_image_back']) $back = $coin['catalog_image_back'];
            ?>
            <img src="<?php echo htmlspecialchars($front); ?>" class="coin-large-img" alt="Front">
            <img src="<?php echo htmlspecialchars($back); ?>" class="coin-large-img" alt="Back">
        </div>

        <div class="coin-header-info">
            <h1 class="coin-title"><?php echo htmlspecialchars($coin['title']); ?></h1>
            <div class="coin-subtitle">
                <?php echo htmlspecialchars($coin['country_name']); ?> • <?php echo $coin['year']; ?> • <?php echo htmlspecialchars($coin['denomination']); ?>
            </div>
        </div>

        <div class="action-bar">
            <a href="edit_coin.php?id=<?php echo $coin['id']; ?>" class="btn btn-accent" style="min-width: 120px;">
                Edit Details
            </a>
            
            <button onclick="openDeleteModal()" class="btn" style="background: #dc3545; color: white; min-width: 120px;">
                Delete Coin
            </button>
        </div>

        <div class="details-container">
            <div class="details-card">
                <h3 style="margin-top: 0; border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; display: inline-block;">My Collection Data</h3>
                
                <div class="data-row">
                    <span class="data-label">Grade / Condition</span>
                    <span class="data-value" style="font-weight: bold; background: #eee; padding: 2px 8px; border-radius: 4px;">
                        <?php echo htmlspecialchars($coin['grade']); ?>
                    </span>
                </div>
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
                
                <div style="margin-top: 20px; text-align: center;">
                    <a href="catalog_coin.php?id=<?php echo $coin['catalog_coin_id']; ?>" style="color: var(--accent-color); font-size: 0.9rem;">
                        View Global Catalog Page &rarr;
                    </a>
                </div>
            </div>
        </div>
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
            if (event.target == modal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>