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
    <style>
        .collection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .coin-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .coin-card:hover {
            transform: translateY(-5px);
        }

        .coin-img-box {
            width: 100%;
            height: 200px;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .coin-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .coin-details {
            padding: 15px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            letter-spacing: 0.5px;
        }

        .grade-good {
            background: linear-gradient(135deg, #b0bec5, #78909c);
        }

        .grade-f {
            background: linear-gradient(135deg, #4fc3f7, #29b6f6);
        }

        .grade-vf {
            background: linear-gradient(135deg, #1e88e5, #1565c0);
        }

        .grade-xf {
            background: linear-gradient(135deg, #1565c0, #0d47a1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .grade-unc {
            background: linear-gradient(135deg, #0d47a1, #002171);
            color: #ffd700;
            border: 1px solid #ffd700;
            box-shadow: 0 0 8px rgba(255, 215, 0, 0.3);
        }

        .status-badge {
            font-size: 0.7rem;
            color: #666;
            border: 1px solid #ddd;
            background: #fff;
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>My Collection</h1>
            <a href="add_coin.php" class="btn btn-accent">+ Add New Coin</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <p>You have <strong><?php echo count($my_coins); ?></strong> coins in your collection.</p>

        <?php if (count($my_coins) > 0): ?>
            <div class="collection-grid">
                <?php foreach ($my_coins as $coin): ?>
                    <div class="coin-card">
                        <div class="coin-img-box">
                            <?php
                            $displayImage = 'assets/images/no-coin.png'; // Default placeholder

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
                                    $gradeClass = 'grade-good';
                                    if ($g == 'F') $gradeClass = 'grade-f';
                                    if ($g == 'VF') $gradeClass = 'grade-vf';
                                    if ($g == 'XF') $gradeClass = 'grade-xf';
                                    if ($g == 'UNC') $gradeClass = 'grade-unc';
                                    ?>
                                    <span class="badge <?php echo $gradeClass; ?>"><?php echo htmlspecialchars($coin['grade']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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