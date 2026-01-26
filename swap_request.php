<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;
$preselected_wanted_id = isset($_GET['wanted_coin_id']) ? (int)$_GET['wanted_coin_id'] : 0;

if ($receiver_id === 0 || $receiver_id == $sender_id) {
    die("Invalid trade partner.");
}

$stmtUser = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmtUser->execute([$receiver_id]);
$receiver = $stmtUser->fetch();
if (!$receiver) die("User not found.");

$stmtMy = $pdo->prepare("
    SELECT uc.id, cc.title, cc.denomination, cc.year, c.flag_image, uc.own_image_front, cc.catalog_image_front
    FROM user_coins uc
    JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
    JOIN countries c ON cc.country_id = c.id
    WHERE uc.user_id = ? AND uc.status = 'swap' AND uc.is_locked = 0
");
$stmtMy->execute([$sender_id]);
$my_swap_coins = $stmtMy->fetchAll();

$stmtTheir = $pdo->prepare("
    SELECT uc.id, cc.title, cc.denomination, cc.year, c.flag_image, uc.own_image_front, cc.catalog_image_front
    FROM user_coins uc
    JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
    JOIN countries c ON cc.country_id = c.id
    WHERE uc.user_id = ? AND uc.status = 'swap' AND uc.is_locked = 0
");
$stmtTheir->execute([$receiver_id]);
$their_swap_coins = $stmtTheir->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Propose Trade</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .trade-container { display: flex; gap: 40px; margin-top: 20px; }
        .trade-col { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .coin-select-card {
            display: flex; align-items: center; gap: 10px; padding: 10px;
            border: 2px solid #eee; border-radius: 6px; margin-bottom: 10px;
            cursor: pointer; transition: all 0.2s; position: relative;
        }
        .coin-select-card:hover { border-color: #ccc; }
        input[type="checkbox"] { display: none; }
        input[type="checkbox"]:checked + .coin-select-card {
            border-color: var(--accent-color);
            background-color: #fffdf5;
        }
        input[type="checkbox"]:checked + .coin-select-card::after {
            content: '✓'; position: absolute; right: 15px; top: 15px;
            font-weight: bold; color: var(--accent-color); font-size: 1.2rem;
        }
        .coin-mini-img { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
        .coin-info h4 { margin: 0; font-size: 0.95rem; }
        .coin-info p { margin: 0; font-size: 0.8rem; color: #666; }
        @media(max-width: 768px) { .trade-container { flex-direction: column; } }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1 style="margin-bottom: 5px;">New Trade Proposal</h1>
        <p>Trading with <strong><?php echo htmlspecialchars($receiver['username']); ?></strong></p>

        <?php if(isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="actions/create_trade.php" method="POST" id="tradeForm">
            <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
            <input type="hidden" name="type" value="swap">

            <div class="trade-container">
                <div class="trade-col">
                    <h3 style="border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; margin-top:0;">You Offer</h3>
                    <div style="max-height: 500px; overflow-y: auto;">
                        <?php if(empty($my_swap_coins)): ?>
                            <p style="color: #999;">You have no unlocked coins marked for SWAP.</p>
                            <a href="index.php" class="btn" style="font-size: 0.8rem;">Manage Collection</a>
                        <?php else: ?>
                            <?php foreach($my_swap_coins as $coin): ?>
                                <label>
                                    <input type="checkbox" name="offered[]" value="<?php echo $coin['id']; ?>">
                                    <div class="coin-select-card">
                                        <?php $img = $coin['own_image_front'] ?: $coin['catalog_image_front'] ?: 'assets/images/no-coin.png'; ?>
                                        <img src="<?php echo htmlspecialchars($img); ?>" class="coin-mini-img">
                                        <div class="coin-info">
                                            <h4><?php echo htmlspecialchars($coin['title']); ?></h4>
                                            <p><?php echo $coin['year']; ?> • <?php echo htmlspecialchars($coin['denomination']); ?></p>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #666;">&#8644;</div>

                <div class="trade-col">
                    <h3 style="border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; margin-top:0;">You Get</h3>
                    <div style="max-height: 500px; overflow-y: auto;">
                        <?php if(empty($their_swap_coins)): ?>
                            <p style="color: #999;">They have no available swap coins.</p>
                        <?php else: ?>
                            <?php foreach($their_swap_coins as $coin): ?>
                                <label>
                                    <input type="checkbox" name="requested[]" value="<?php echo $coin['id']; ?>" 
                                           <?php echo ($coin['id'] == $preselected_wanted_id) ? 'checked' : ''; ?>>
                                    <div class="coin-select-card">
                                        <?php $img = $coin['own_image_front'] ?: $coin['catalog_image_front'] ?: 'assets/images/no-coin.png'; ?>
                                        <img src="<?php echo htmlspecialchars($img); ?>" class="coin-mini-img">
                                        <div class="coin-info">
                                            <h4><?php echo htmlspecialchars($coin['title']); ?></h4>
                                            <p><?php echo $coin['year']; ?> • <?php echo htmlspecialchars($coin['denomination']); ?></p>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <label style="font-weight: bold; display: block; margin-bottom: 10px;">Note to User (Optional):</label>
                <textarea name="message" placeholder="E.g. I can ship on Monday, condition is good..." 
                          style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; box-sizing: border-box; resize: vertical;"></textarea>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn btn-accent" style="padding: 15px 40px; font-size: 1.1rem;">
                    Propose Swap
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('tradeForm').addEventListener('submit', function(e) {
            const offered = document.querySelectorAll('input[name="offered[]"]:checked').length;
            const requested = document.querySelectorAll('input[name="requested[]"]:checked').length;
            if (offered === 0 && requested === 0) {
                e.preventDefault();
                alert("Please select at least one coin to offer or request.");
            }
        });
    </script>
    <?php include 'includes/footer.php'; ?>

</body>
</html>