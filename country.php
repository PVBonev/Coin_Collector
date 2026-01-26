<?php
session_start();
require 'config/db.php';

$country_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($country_id === 0) {
    header("Location: countries.php");
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM countries WHERE id = ?");
$stmt->execute([$country_id]);
$country = $stmt->fetch();

if (!$country) {
    die("Country not found.");
}

$sql = "SELECT 
            cc.*,
            (SELECT COUNT(*) FROM user_coins uc WHERE uc.catalog_coin_id = cc.id AND uc.user_id = :uid) as is_owned
        FROM catalog_coins cc
        WHERE cc.country_id = :cid AND cc.is_approved = 1
        ORDER BY cc.year ASC, cc.denomination ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['uid' => $user_id, 'cid' => $country_id]);
$coins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($country['name']); ?> - Coin Catalog</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="country-header">
            <?php if ($country['flag_image']): ?>
                <img src="<?php echo htmlspecialchars($country['flag_image']); ?>" class="country-flag-large">
            <?php endif; ?>
            <div>
                <h1 style="margin: 0;"><?php echo htmlspecialchars($country['name']); ?></h1>
                <p style="margin: 5px 0 0 0; color: #666;">
                    <?php echo count($coins); ?> coin types in catalog
                </p>
            </div>

            <div style="margin-left: auto;">
                <a href="add_coin.php?country_id=<?php echo $country['id']; ?>" class="btn btn-accent">
                    + Add Coin from <?php echo htmlspecialchars($country['name']); ?>
                </a>
            </div>
        </div>

        <?php if (count($coins) > 0): ?>
            <div class="catalog-grid">
                <?php foreach ($coins as $coin): ?>
                    <a href="catalog_coin.php?id=<?php echo $coin['id']; ?>" class="catalog-card">

                        <?php if ($coin['is_owned'] > 0): ?>
                            <div class="owned-badge" title="You own this coin">✓</div>
                        <?php endif; ?>

                        <div class="card-img">
                            <?php
                            $img = $coin['catalog_image_front'] ? $coin['catalog_image_front'] : 'assets/images/no-coin.png';
                            ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="Coin">
                        </div>

                        <div class="card-body">
                            <div class="card-year"><?php echo $coin['year']; ?></div>
                            <div class="card-denom"><?php echo htmlspecialchars($coin['denomination']); ?></div>
                            <div class="card-title"><?php echo htmlspecialchars($coin['title']); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; background: white; border-radius: 8px;">
                <h3>No coins found for this country yet.</h3>
                <p>Be the first to contribute!</p>
                <a href="add_coin.php" class="btn btn-accent">Add Coin</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>