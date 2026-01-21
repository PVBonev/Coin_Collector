<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT uc.*, cc.title, cc.year 
    FROM user_coins uc 
    JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
    WHERE uc.id = ? AND uc.user_id = ?
");
$stmt->execute([$id, $user_id]);
$coin = $stmt->fetch();

if (!$coin) die("Coin not found.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Coin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2>Edit: <?php echo htmlspecialchars($coin['title']); ?></h2>
            
            <form action="actions/edit_coin_process.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="user_coin_id" value="<?php echo $coin['id']; ?>">
                <input type="hidden" name="action" value="update">

                <div class="form-group">
                    <label>Condition</label>
                    <select name="grade">
                        <?php 
                        $grades = ['UNC', 'XF', 'VF', 'F', 'Good'];
                        foreach($grades as $g) {
                            $sel = ($coin['grade'] == $g) ? 'selected' : '';
                            echo "<option value='$g' $sel>$g</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="collection" <?php if($coin['status']=='collection') echo 'selected'; ?>>In Collection</option>
                        <option value="swap" <?php if($coin['status']=='swap') echo 'selected'; ?>>For Swap</option>
                        <option value="sell" <?php if($coin['status']=='sell') echo 'selected'; ?>>For Sale</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Private Notes</label>
                    <textarea name="private_notes" rows="3"><?php echo htmlspecialchars($coin['private_notes']); ?></textarea>
                </div>

                <hr>
                <h4>Replace Photos</h4>
                <p style="font-size: 0.8rem; color: #666;">Leave empty to keep current photos.</p>

                <div class="form-group">
                    <label>Front</label>
                    <input type="file" name="img_front" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Back</label>
                    <input type="file" name="img_back" accept="image/*">
                </div>

                <button type="submit" class="btn btn-accent" style="width: 100%;">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>