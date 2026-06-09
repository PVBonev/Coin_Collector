<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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

if (!$coin) {
    die("Coin not found in your collection.");
}

$stmtGallery = $pdo->prepare("SELECT * FROM user_coin_images WHERE user_coin_id = ?");
$stmtGallery->execute([$id]);
$gallery_images = $stmtGallery->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Coin - <?php echo htmlspecialchars($coin['title']); ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">Edit Details</h2>
                <a href="user_coin_details.php?id=<?php echo $coin['id']; ?>" style="color: #666; font-size: 0.9rem;">Cancel</a>
            </div>

            <p style="margin-top: -10px; margin-bottom: 20px; color: #666; font-weight: bold;">
                <?php echo htmlspecialchars($coin['title']); ?> (<?php echo $coin['year']; ?>)
            </p>

            <form action="actions/edit_coin_process.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="user_coin_id" value="<?php echo $coin['id']; ?>">
                <input type="hidden" name="action" value="update">

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Condition (Sheldon Scale)</label>
                        <select name="grade">
                            <?php
                            $grades = [
                                'UNC' => 'Uncirculated (UNC/MS)',
                                'AU'  => 'About Uncirculated (AU)',
                                'XF'  => 'Extremely Fine (XF)',
                                'VF'  => 'Very Fine (VF)',
                                'F'   => 'Fine (F)',
                                'VG'  => 'Very Good (VG)',
                                'G'   => 'Good/Fair (G)'
                            ];

                            foreach ($grades as $code => $label) {
                                $sel = ($coin['grade'] == $code) ? 'selected' : '';
                                echo "<option value='$code' $sel>$label</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label>Status</label>
                        <select name="status">
                            <option value="collection" <?php if ($coin['status'] == 'collection') echo 'selected'; ?>>In Collection</option>
                            <option value="swap" <?php if ($coin['status'] == 'swap') echo 'selected'; ?>>For Swap</option>
                            <option value="sell" <?php if ($coin['status'] == 'sell') echo 'selected'; ?>>For Sale</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Current Market Value (Est. Price)</label>
                    <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($coin['price'] ?? ''); ?>" placeholder="0.00">
                </div>

                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
                    <h4 style="margin-top: 0; margin-bottom: 15px; color: #555; font-size: 0.95rem; text-transform: uppercase;">Acquisition Details</h4>

                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label style="font-size: 0.85rem;">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" value="<?php echo htmlspecialchars($coin['purchase_price'] ?? ''); ?>" placeholder="Paid amount">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label style="font-size: 0.85rem;">Date Acquired</label>
                            <input type="date" name="purchase_date" value="<?php echo htmlspecialchars($coin['purchase_date'] ?? ''); ?>">                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.85rem;">Acquired From (Location/Source)</label>
                        <input type="text" name="purchase_location" value="<?php echo htmlspecialchars($coin['purchase_location'] ?? ''); ?>" placeholder="e.g. eBay, Local Shop, Gift...">                    </div>
                </div>
                <div class="form-group">
                    <label>Private Notes</label>
                    <textarea name="private_notes" rows="3" placeholder="Add personal notes here..."><?php echo htmlspecialchars($coin['private_notes'] ?? ''); ?></textarea>
                </div>

                <hr style="margin: 25px 0;">

                <h4 style="margin-bottom: 5px;">Replace Photos</h4>
                <p style="font-size: 0.8rem; color: #666; margin-bottom: 15px;">Upload only if you want to replace your current images.</p>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Front Side</label>
                        <input type="file" name="img_front" accept="image/*">
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label>Back Side</label>
                        <input type="file" name="img_back" accept="image/*">
                    </div>
                </div>
                <?php if (count($gallery_images) > 0): ?>
                    <h4 style="margin-bottom: 10px;">Manage Gallery</h4>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                        <?php foreach ($gallery_images as $img): ?>
                            <div style="position: relative; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                <img src="<?php echo htmlspecialchars($img['image_path']); ?>" style="height: 60px;">
                                <label style="display: block; font-size: 0.8rem; color: #dc3545; cursor: pointer; text-align: center; margin-top: 5px;">
                                    <input type="checkbox" name="delete_gallery[]" value="<?php echo $img['id']; ?>"> Delete
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Add More Photos</label>
                    <input type="file" name="new_gallery[]" multiple accept="image/*">
                </div>

                <button type="submit" class="btn btn-accent" style="width: 100%; margin-top: 10px; font-weight: bold;">Save Changes</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>

</body>

</html>