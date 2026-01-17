<?php
// create_catalog_coin.php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

//if user comes from add_coin with a chosen country, preselect it
$pre_country_id = isset($_GET['country_id']) ? (int)$_GET['country_id'] : '';

$stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
$countries = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request New Coin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2 style="text-align: center;">Request New Coin Type</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                This coin will be added to your collection immediately and sent for approval to the global catalog.
            </p>

            <form action="actions/add_coin_process.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Country *</label>
                    <select name="country_id" required>
                        <option value="">-- Select Country --</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country['id']; ?>" <?php if($country['id'] == $pre_country_id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($country['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Title * (e.g. Treaty of Rome)</label>
                    <input type="text" name="new_title" required placeholder="Coin Name">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Denomination *</label>
                        <input type="text" name="new_denomination" required placeholder="e.g. 2 Euro">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Year *</label>
                        <input type="number" name="new_year" required min="1000" max="<?php echo date('Y'); ?>">
                    </div>
                </div>

                <hr>
                <h3>Your Collection Details</h3>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Condition</label>
                        <select name="grade">
                            <option value="UNC">Uncirculated (UNC)</option>
                            <option value="XF">Extremely Fine (XF)</option>
                            <option value="VF">Very Fine (VF)</option>
                            <option value="F">Fine (F)</option>
                            <option value="Good">Good</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Status</label>
                        <select name="status">
                            <option value="collection">In Collection</option>
                            <option value="swap">For Swap</option>
                            <option value="sell">For Sale</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>My Photo (Front) *</label>
                    <input type="file" name="img_front" accept="image/*" required>
                    <small>Required for new catalog entries.</small>
                </div>
                
                <div class="form-group">
                    <label>My Photo (Back)</label>
                    <input type="file" name="img_back" accept="image/*">
                </div>

                <input type="hidden" name="is_manual" value="1">

                <button type="submit" class="btn btn-accent" style="width: 100%;">Create & Add to Collection</button>
            </form>
        </div>
    </div>
</body>
</html>