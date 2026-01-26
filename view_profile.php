<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$target_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_user_id = $_SESSION['user_id'];

if ($target_user_id === 0) {
    die("Invalid user ID.");
}

if ($target_user_id === $current_user_id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT username, email, created_at, profile_image FROM users WHERE id = ?");
$stmt->execute([$target_user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

$statsSql = "SELECT 
                COUNT(*) as total_coins, 
                SUM(price) as total_value,
                SUM(CASE WHEN status = 'swap' THEN 1 ELSE 0 END) as for_swap
             FROM user_coins 
             WHERE user_id = ?";
$stmt = $pdo->prepare($statsSql);
$stmt->execute([$target_user_id]);
$stats = $stmt->fetch();

$topCountrySql = "SELECT c.name, c.flag_image, COUNT(*) as cnt
                  FROM user_coins uc
                  JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
                  JOIN countries c ON cc.country_id = c.id
                  WHERE uc.user_id = ?
                  GROUP BY c.id
                  ORDER BY cnt DESC
                  LIMIT 1";
$stmt = $pdo->prepare($topCountrySql);
$stmt->execute([$target_user_id]);
$topCountry = $stmt->fetch();

$coinsSql = "SELECT 
                uc.id AS collection_id, uc.grade, uc.status, uc.price, uc.own_image_front,
                cc.title, cc.year, cc.denomination, cc.catalog_image_front,
                c.name AS country_name, c.flag_image
             FROM user_coins uc
             JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
             JOIN countries c ON cc.country_id = c.id
             WHERE uc.user_id = ?
             ORDER BY uc.added_at DESC";
$stmt = $pdo->prepare($coinsSql);
$stmt->execute([$target_user_id]);
$coins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .profile-header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        .profile-initial {
            width: 100px;
            height: 100px;
            background: #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #aaa;
            font-weight: bold;
            text-transform: uppercase;
        }

        .profile-info h1 {
            margin: 0;
            font-size: 2rem;
        }

        .profile-meta {
            color: #666;
            margin-top: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border-top: 4px solid var(--accent-color);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        .btn-contact {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
        }

        .btn-contact:hover {
            background: #0056b3;
        }

        .coin-card {
            position: relative;
        }

        .coin-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: 8px;
            z-index: 10;
        }

        .coin-card:hover .coin-overlay {
            opacity: 1;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">

        <div class="profile-header">
            <?php
            $hasImg = !empty($user['profile_image']) && file_exists($user['profile_image']);
            $initial = strtoupper(substr($user['username'], 0, 1));
            ?>

            <?php if ($hasImg): ?>
                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" class="profile-avatar">
            <?php else: ?>
                <div class="profile-initial"><?php echo $initial; ?></div>
            <?php endif; ?>

            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <div class="profile-meta">
                    Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                </div>
                <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>?subject=Coin Swap Request from CoinCollector" class="btn-contact">
                    &#9993; Contact User
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_coins']; ?></div>
                <div class="stat-label">Total Coins</div>
            </div>

            <div class="stat-card" style="border-color: #28a745;">
                <div class="stat-value">
                    <?php echo $stats['total_value'] > 0 ? number_format($stats['total_value'], 2) . ' €' : '-'; ?>
                </div>
                <div class="stat-label">Collection Value</div>
            </div>

            <div class="stat-card" style="border-color: #ffc107;">
                <div class="stat-value"><?php echo $stats['for_swap']; ?></div>
                <div class="stat-label">Available for Swap</div>
            </div>

            <div class="stat-card" style="border-color: #17a2b8;">
                <div class="stat-value" style="font-size: 1.2rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <?php if ($topCountry): ?>
                        <img src="<?php echo htmlspecialchars($topCountry['flag_image']); ?>" width="30">
                        <?php echo htmlspecialchars($topCountry['name']); ?>
                    <?php else: ?>
                        None
                    <?php endif; ?>
                </div>
                <div class="stat-label">Top Country</div>
            </div>
        </div>

        <h2 style="border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 20px;">Collection Preview</h2>

        <?php if (count($coins) > 0): ?>
            <div class="collection-grid">
                <?php foreach ($coins as $coin): ?>
                    <div class="coin-card">

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($coin['status'] === 'swap'): ?>
                                <div class="coin-overlay">
                                    <a href="swap_request.php?receiver_id=<?php echo $target_user_id; ?>&wanted_coin_id=<?php echo $coin['collection_id']; ?>"
                                        class="btn btn-accent" style="padding: 10px 20px;">
                                        Swap Coin
                                    </a>
                                </div>
                            <?php elseif ($coin['status'] === 'sell'): ?>
                                <div class="coin-overlay">
                                    <a href="buy_request.php?coin_id=<?php echo $coin['collection_id']; ?>"
                                        class="btn" style="background-color: #dc3545; color: white; padding: 10px 20px;">
                                        Buy Coin
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="coin-img-box">
                            <?php
                            $displayImage = 'assets/images/no-coin.png';
                            if (!empty($coin['own_image_front'])) {
                                $displayImage = $coin['own_image_front'];
                            } elseif (!empty($coin['catalog_image_front'])) {
                                $displayImage = $coin['catalog_image_front'];
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($displayImage); ?>" alt="Coin Image">
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
                                    <?php if ($coin['status'] === 'swap'): ?>
                                        <span class="badge status-badge" style="background: #ffc107;">Swap</span>
                                    <?php elseif ($coin['status'] === 'sell'): ?>
                                        <span class="badge status-badge" style="background: #dc3545; color: white;">Sell</span>
                                    <?php endif; ?>

                                    <span class="badge" style="background: #6c757d; color: white;">
                                        <a href="grading_guide.php" style="color: white; text-decoration: none;" title="What is this grade?">
                                            <?php echo htmlspecialchars($coin['grade']); ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #777;">This user hasn't started their collection yet.</p>
        <?php endif; ?>

    </div>
    <?php include 'includes/footer.php'; ?>

</body>

</html>