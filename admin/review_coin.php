<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT cc.*, c.name as country_name 
    FROM catalog_coins cc
    JOIN countries c ON cc.country_id = c.id
    WHERE cc.id = ? AND cc.is_approved = 0
");
$stmt->execute([$id]);
$coin = $stmt->fetch();

if (!$coin) {
    die("Request not found or already processed.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Review Coin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <a href="dashboard.php" style="color: #666; text-decoration: none;">&larr; Back to List</a>
        <h1 style="margin-top: 10px;">Review Coin Request</h1>

        <div class="review-layout">
            <div class="preview-images">
                <h3>User Submitted Images</h3>
                <p style="color: #666; font-size: 0.9rem;">Check these to verify the details.</p>

                <?php if ($coin['catalog_image_front']): ?>
                    <img src="../<?php echo htmlspecialchars($coin['catalog_image_front']); ?>" alt="Front">
                <?php endif; ?>

                <?php if ($coin['catalog_image_back']): ?>
                    <img src="../<?php echo htmlspecialchars($coin['catalog_image_back']); ?>" alt="Back">
                <?php endif; ?>
            </div>

            <div class="edit-form">
                <h3>Edit & Approve Details</h3>
                <p style="color: #666; font-size: 0.9rem;">Clean up the data before publishing to the catalog.</p>

                <form action="../actions/review_coin_process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="coin_id" value="<?php echo $coin['id']; ?>">

                    <div class="form-group">
                        <label>Country (Read Only)</label>
                        <input type="text" value="<?php echo htmlspecialchars($coin['country_name']); ?>" disabled style="background: #eee;">
                    </div>

                    <div class="form-group">
                        <label>Denomination</label>
                        <input type="text" name="denomination" value="<?php echo htmlspecialchars($coin['denomination']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($coin['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" name="year" value="<?php echo $coin['year']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Material</label>
                        <input type="text" name="material" value="<?php echo htmlspecialchars($coin['material'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Period</label>
                        <input type="text" name="period" value="<?php echo htmlspecialchars($coin['period'] ?? ''); ?>">
                    </div>

                    <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #eee;">
                        <h4 style="margin-top: 0; margin-bottom: 10px; color: #555; font-size: 0.9rem;">Technical Specifications</h4>

                        <div style="display: flex; gap: 10px;">
                            <div class="form-group" style="flex: 1;">
                                <label style="font-size: 0.85rem;">Weight (g)</label>
                                <input type="number" step="0.01" name="weight" value="<?php echo htmlspecialchars($coin['weight'] ?? ''); ?>">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label style="font-size: 0.85rem;">Diameter (mm)</label>
                                <input type="number" step="0.01" name="diameter" value="<?php echo htmlspecialchars($coin['diameter'] ?? ''); ?>">
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <div class="form-group" style="flex: 1;">
                                <label style="font-size: 0.85rem;">Thickness (mm)</label>
                                <input type="number" step="0.01" name="thickness" value="<?php echo htmlspecialchars($coin['thickness'] ?? ''); ?>">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label style="font-size: 0.85rem;">Mintage</label>
                                <input type="number" name="mintage" value="<?php echo htmlspecialchars($coin['mintage'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"><?php echo htmlspecialchars($coin['description'] ?? ''); ?></textarea>
                    </div>

                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

                    <h4 style="margin-bottom: 10px;">Official Catalog Images</h4>
                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">
                        If you upload images here, they will become the official catalog photos.
                        Users who have their own photos will still see their own.
                        Users without photos will see these.
                    </p>

                    <div class="form-group">
                        <label>Catalog Photo (Front)</label>
                        <input type="file" name="admin_img_front" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Catalog Photo (Back)</label>
                        <input type="file" name="admin_img_back" accept="image/*">
                    </div>

                    <div class="action-bar">
                        <button type="submit" name="action" value="approve" class="btn btn-accent" style="flex: 2; background: #28a745;">
                            Save & Publish
                        </button>

                        <button type="submit" name="action" value="reject" class="btn" style="flex: 1; background: #dc3545; color: white;">
                            Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>