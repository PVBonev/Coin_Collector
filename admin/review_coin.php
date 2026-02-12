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

$stmtComp = $pdo->prepare("
    SELECT comp.percentage, m.name, m.symbol, m.id as material_id 
    FROM coin_composition comp
    JOIN materials m ON comp.material_id = m.id
    WHERE comp.catalog_coin_id = ?
");
$stmtComp->execute([$id]);
$compositions = $stmtComp->fetchAll();

$stmtMat = $pdo->query("SELECT id, name, symbol FROM materials ORDER BY name ASC");
$allMaterials = $stmtMat->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Review Coin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
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
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <a href="dashboard.php" style="color: #666; text-decoration: none;">&larr; Back to List</a>
        <h1 style="margin-top: 10px;">Review Coin Request</h1>

        <div class="review-layout">
            <div class="preview-images">
                <h3>User Submitted Images</h3>
                <?php if ($coin['catalog_image_front']): ?>
                    <div style="margin-bottom: 10px;">
                        <strong>Front:</strong><br>
                        <img src="../<?php echo htmlspecialchars($coin['catalog_image_front']); ?>" alt="Front" style="max-width: 100%; border-radius: 8px;">
                    </div>
                <?php endif; ?>
                <?php if ($coin['catalog_image_back']): ?>
                    <div style="margin-bottom: 10px;">
                        <strong>Back:</strong><br>
                        <img src="../<?php echo htmlspecialchars($coin['catalog_image_back']); ?>" alt="Back" style="max-width: 100%; border-radius: 8px;">
                    </div>
                <?php endif; ?>
                <?php if ($coin['catalog_image_edge']): ?>
                    <div style="margin-bottom: 10px;">
                        <strong>Edge:</strong><br>
                        <img src="../<?php echo htmlspecialchars($coin['catalog_image_edge']); ?>" alt="Edge" style="max-width: 100%; border-radius: 8px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="edit-form">
                <h3>Edit & Approve Details</h3>
                <p style="color: #666; font-size: 0.9rem;">Clean up the data before publishing to the catalog.</p>

                <form action="../actions/review_coin_process.php" method="POST" enctype="multipart/form-data" id="reviewForm">
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
                        <label>Period</label>
                        <input type="text" name="period" value="<?php echo htmlspecialchars($coin['period'] ?? ''); ?>">
                    </div>

                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
                        <h4 style="margin-top: 0; margin-bottom: 15px; color: var(--accent-color);">Composition</h4>
                        <p style="font-size: 0.85rem; color: #666; margin-top: -10px; margin-bottom: 15px;">
                            Modify composition if needed. Total must be 100%.
                        </p>
                        
                        <div id="composition-container">
                            <?php if (count($compositions) > 0): ?>
                                <?php foreach ($compositions as $comp): ?>
                                    <div class="composition-row">
                                        <select name="material_id[]" style="flex: 2; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                                            <option value="">-- Select Metal --</option>
                                            <?php foreach ($allMaterials as $mat): ?>
                                                <option value="<?php echo $mat['id']; ?>" <?php if($mat['id'] == $comp['material_id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($mat['name']); ?> 
                                                    <?php echo $mat['symbol'] ? '('.htmlspecialchars($mat['symbol']).')' : ''; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="number" step="0.01" name="material_percentage[]" value="<?php echo $comp['percentage']; ?>" placeholder="%" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" max="100" min="0.01">
                                        <button type="button" class="remove-material-btn" onclick="this.parentElement.remove()">X</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" id="add-material-btn" class="btn" style="background: #e2e8f0; color: #333; font-size: 0.85rem; padding: 5px 10px; margin-top: 5px;">
                            + Add another metal
                        </button>
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
                    
                    <div class="form-group">
                        <label>Catalog Photo (Front)</label>
                        <input type="file" name="admin_img_front" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Catalog Photo (Back)</label>
                        <input type="file" name="admin_img_back" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label>Catalog Photo (Edge)</label>
                        <input type="file" name="admin_img_edge" accept="image/*">
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

    <script>
        const materialOptions = `
            <option value="">-- Select Metal --</option>
            <?php foreach ($allMaterials as $mat): ?>
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

        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            const action = e.submitter ? e.submitter.value : 'approve';
            if(action === 'reject') return;

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
                alert("Error: Total composition percentage must be exactly 100%.");
            }
        });
    </script>
</body>
</html>