<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pre_country_id = isset($_GET['country_id']) ? (int)$_GET['country_id'] : '';

$stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
$countries = $stmt->fetchAll();

$stmtMat = $pdo->query("SELECT id, name, symbol FROM materials ORDER BY name ASC");
$materials = $stmtMat->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Request New Coin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .composition-row {
            display: flex; 
            gap: 10px; 
            margin-bottom: 10px;
            align-items: center;
        }
        .remove-material-btn {
            background: #dc3545; 
            color: white; 
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            font-weight: bold;
        }
        .remove-material-btn:hover {
            background: #c82333;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2 style="text-align: center;">Request New Coin Type</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                This coin will be added to your collection immediately and sent for approval to the global catalog.
            </p>

            <form action="actions/add_coin_process.php" method="POST" enctype="multipart/form-data" id="newCoinForm">

                <div class="form-group">
                    <label>Country *</label>
                    <select name="country_id" required>
                        <option value="">-- Select Country --</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country['id']; ?>" <?php if ($country['id'] == $pre_country_id) echo 'selected'; ?>>
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

                <div class="form-group">
                    <label>Period (Optional)</label>
                    <input type="text" name="period" placeholder="e.g. People's Republic">
                </div>

                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
                    <h4 style="margin-top: 0; margin-bottom: 15px; color: var(--accent-color);">Composition (Optional)</h4>
                    <p style="font-size: 0.85rem; color: #666; margin-top: -10px; margin-bottom: 15px;">
                        Specify the metals used. The total percentage must equal exactly 100%. Leave empty if unknown.
                    </p>
                    
                    <div id="composition-container">
                        <div class="composition-row">
                            <select name="material_id[]" style="flex: 2; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="">-- Select Metal --</option>
                                <?php foreach ($materials as $mat): ?>
                                    <option value="<?php echo $mat['id']; ?>">
                                        <?php echo htmlspecialchars($mat['name']); ?> 
                                        <?php echo $mat['symbol'] ? '('.htmlspecialchars($mat['symbol']).')' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" step="0.01" name="material_percentage[]" placeholder="%" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" max="100" min="0.01">
                            <button type="button" class="remove-material-btn" onclick="this.parentElement.remove()">X</button>
                        </div>
                    </div>
                    
                    <button type="button" id="add-material-btn" class="btn" style="background: #e2e8f0; color: #333; font-size: 0.85rem; padding: 5px 10px; margin-top: 5px;">
                        + Add another metal
                    </button>
                </div>

                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Weight (g)</label>
                        <input type="number" step="0.01" name="weight" placeholder="e.g. 24.5">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Diameter (mm)</label>
                        <input type="number" step="0.01" name="diameter" placeholder="e.g. 37">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Mintage (pcs)</label>
                        <input type="number" name="mintage" placeholder="e.g. 500000">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description / Interesting Facts (Optional)</label>
                    <textarea name="description" rows="3" placeholder="Write something interesting about this coin..."></textarea>
                </div>

                <hr>
                <h3>Your Collection Details</h3>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Condition</label>
                        <select name="grade">
                            <option value="UNC">Uncirculated (UNC/MS)</option>
                            <option value="AU">About Uncirculated (AU)</option>
                            <option value="XF">Extremely Fine (XF)</option>
                            <option value="VF">Very Fine (VF)</option>
                            <option value="F">Fine (F)</option>
                            <option value="VG">Very Good (VG)</option>
                            <option value="G">Good/Fair (G)</option>
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
                
                <div class="form-group">
                    <label>My Photo (Edge)</label>
                    <input type="file" name="img_edge" accept="image/*">
                    <small>Optional: Photo of the coin's edge.</small>
                </div>

                <input type="hidden" name="is_manual" value="1">

                <button type="submit" class="btn btn-accent" style="width: 100%;">Create & Add to Collection</button>
            </form>
        </div>
    </div>

    <script>
        const materialOptions = `
            <option value="">-- Select Metal --</option>
            <?php foreach ($materials as $mat): ?>
                <option value="<?php echo $mat['id']; ?>">
                    <?php echo addslashes(htmlspecialchars($mat['name'])); ?> 
                    <?php echo $mat['symbol'] ? '('.addslashes(htmlspecialchars($mat['symbol'])).')' : ''; ?>
                </option>
            <?php endforeach; ?>
        `;

        document.getElementById('add-material-btn').addEventListener('click', function() {
            const container = document.getElementById('composition-container');
            const row = document.createElement('div');
            row.className = 'composition-row';
            row.innerHTML = `
                <select name="material_id[]" style="flex: 2; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    ${materialOptions}
                </select>
                <input type="number" step="0.01" name="material_percentage[]" placeholder="%" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" max="100" min="0.01">
                <button type="button" class="remove-material-btn" onclick="this.parentElement.remove()">X</button>
            `;
            container.appendChild(row);
        });

        document.getElementById('newCoinForm').addEventListener('submit', function(e) {
            let totalPercentage = 0;
            let hasMaterials = false;
            
            const selects = document.querySelectorAll('select[name="material_id[]"]');
            const percentages = document.querySelectorAll('input[name="material_percentage[]"]');
            
            for (let i = 0; i < selects.length; i++) {
                if (selects[i].value !== "") {
                    hasMaterials = true;
                    let pValue = parseFloat(percentages[i].value);
                    if (!isNaN(pValue)) {
                        totalPercentage += pValue;
                    }
                }
            }

            if (hasMaterials && Math.abs(totalPercentage - 100) > 0.01) {
                e.preventDefault(); 
                alert("Error: The total composition must be exactly 100%. Current total is " + totalPercentage.toFixed(2) + "%.");
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>