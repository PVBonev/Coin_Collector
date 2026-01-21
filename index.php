<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
            uc.id AS collection_id,
            uc.grade,
            uc.status,
            uc.own_image_front,  
            cc.title,
            cc.year,
            cc.denomination,
            cc.catalog_image_front, 
            c.name AS country_name,
            c.flag_image
        FROM user_coins uc
        JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
        JOIN countries c ON cc.country_id = c.id
        WHERE uc.user_id = ?
        ORDER BY uc.added_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_coins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Collection - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>My Collection</h1>

            <div style="display: flex; gap: 10px;">
                <a href="data_management.php" class="btn" style="background: #6c757d; color: white; font-size: 0.9rem; display: flex; align-items: center;">
                    &#128193; Import / Export
                </a>

                <a href="add_coin.php" class="btn btn-accent">
                    + Add New Coin
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['import_errors'])): ?>
            <div style="background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Warning:</strong> The following coins were NOT imported (not found in catalog or bad data):
                <ul style="margin: 10px 0 0 20px; max-height: 200px; overflow-y: auto;">
                    <?php foreach ($_SESSION['import_errors'] as $error): ?>
                        <li style="margin-bottom: 5px;"><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <div style="margin-top: 10px; font-size: 0.9rem;">
                    <em>Tip: Ensure the Country, Year, Denomination, and Title match exactly with the catalog.</em>
                </div>
            </div>
            <?php unset($_SESSION['import_errors']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <p>You have <strong><?php echo count($my_coins); ?></strong> coins in your collection.</p>

        <?php if (count($my_coins) > 0): ?>
            <div class="collection-grid">
                <?php foreach ($my_coins as $coin): ?>
                    <a href="user_coin_details.php?id=<?php echo $coin['collection_id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="coin-card">
                            <div class="coin-img-box">
                                <?php
                                $displayImage = 'assets/images/no-coin.png'; //default image for when we don't have an image for a coin

                                if (!empty($coin['own_image_front'])) {
                                    $displayImage = $coin['own_image_front'];
                                } elseif (!empty($coin['catalog_image_front'])) {
                                    $displayImage = $coin['catalog_image_front'];
                                }
                                ?>

                                <?php if ($displayImage !== 'assets/images/no-coin.png'): ?>
                                    <img src="<?php echo htmlspecialchars($displayImage); ?>" alt="Coin Image">
                                <?php else: ?>
                                    <span style="color: #999;">No Image</span>
                                <?php endif; ?>
                            </div>

                            <div class="coin-details">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <h3 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($coin['title']); ?></h3>
                                    <?php if ($coin['flag_image']): ?>
                                        <img src="<?php echo htmlspecialchars($coin['flag_image']); ?>" style="width: 25px; border: 1px solid #eee;">
                                    <?php endif; ?>
                                </div>

                                <p style="color: #666; font-size: 0.9rem; margin: 5px 0;">
                                    <?php echo htmlspecialchars($coin['country_name']); ?> • <?php echo $coin['year']; ?>
                                </p>

                                <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: bold; font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($coin['denomination']); ?>
                                    </span>

                                    <div>
                                        <?php if ($coin['status'] !== 'collection'): ?>
                                            <span class="badge status-badge"><?php echo ucfirst($coin['status']); ?></span>
                                        <?php endif; ?>

                                        <?php
                                        $g = trim($coin['grade']);
                                        $gradeClass = 'grade-default'; //gray by default

                                        //colors for grades
                                        if ($g == 'UNC') $gradeClass = 'grade-unc'; 
                                        if ($g == 'AU')  $gradeClass = 'grade-au';  
                                        if ($g == 'XF')  $gradeClass = 'grade-xf';  
                                        if ($g == 'VF')  $gradeClass = 'grade-vf';  
                                        if ($g == 'F')   $gradeClass = 'grade-f';   
                                        if ($g == 'VG')  $gradeClass = 'grade-vg';  
                                        if ($g == 'G')   $gradeClass = 'grade-g';   
                                        ?>
                                        <span class="badge <?php echo $gradeClass; ?>"><?php echo htmlspecialchars($coin['grade']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a> <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 40px;">
                <h3>Your collection is empty!</h3>
                <p>Start by adding your first coin.</p>
                <a href="add_coin.php" class="btn btn-accent" style="margin-top: 10px;">Add Coin</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>