<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $country_id = (int)$_POST['country_id'];
    $year = (int)$_POST['year'];
    $denomination = trim($_POST['denomination']);
    $material = trim($_POST['material']);
    $period = trim($_POST['period']);
    $description = trim($_POST['description']);
    $mintage = !empty($_POST['mintage']) ? (int)$_POST['mintage'] : null;
    $weight = !empty($_POST['weight']) ? $_POST['weight'] : null;
    $diameter = !empty($_POST['diameter']) ? $_POST['diameter'] : null;
    $thickness = !empty($_POST['thickness']) ? $_POST['thickness'] : null;

    try {
        $sql = "UPDATE catalog_coins SET 
                title = ?, country_id = ?, year = ?, denomination = ?, 
                material = ?, period = ?, description = ?, mintage = ?, 
                weight = ?, diameter = ?, thickness = ?
                WHERE id = ?";
        
        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute([
            $title, $country_id, $year, $denomination, 
            $material, $period, $description, $mintage, 
            $weight, $diameter, $thickness, $id
        ]);

        $_SESSION['success'] = "Catalog coin updated successfully!";
        header("Location: ../catalog_coin.php?id=" . $id);
        exit;

    } catch (PDOException $e) {
        $error = "Error updating coin: " . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM catalog_coins WHERE id = ?");
$stmt->execute([$id]);
$coin = $stmt->fetch();

if (!$coin) {
    die("Coin not found.");
}

$countries = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Coin: <?php echo htmlspecialchars($coin['title']); ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media(max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container" style="max-width: 800px;">
        <a href="../catalog_coin.php?id=<?php echo $id; ?>" style="color: #666; text-decoration: none;">&larr; Back to Coin</a>
        
        <h1 style="border-bottom: 2px solid var(--accent-color); padding-bottom: 10px;">Edit Catalog Entry</h1>
        
        <?php if(isset($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="card">
            
            <div class="form-group">
                <label>Title <span style="color: red;">*</span></label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($coin['title']); ?>" required>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Country <span style="color: red;">*</span></label>
                    <select name="country_id" required>
                        <?php foreach($countries as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $coin['country_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Year <span style="color: red;">*</span></label>
                    <input type="number" name="year" value="<?php echo $coin['year']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Denomination</label>
                    <input type="text" name="denomination" value="<?php echo htmlspecialchars($coin['denomination']); ?>">
                </div>

                <div class="form-group">
                    <label>Material</label>
                    <input type="text" name="material" value="<?php echo htmlspecialchars($coin['material']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Period / Era</label>
                <input type="text" name="period" value="<?php echo htmlspecialchars($coin['period']); ?>" placeholder="e.g. Republic, Kingdom...">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5"><?php echo htmlspecialchars($coin['description']); ?></textarea>
            </div>

            <h3 style="margin-top: 30px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Technical Specs</h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Mintage (Quantity)</label>
                    <input type="number" name="mintage" value="<?php echo $coin['mintage']; ?>">
                </div>
                <div class="form-group">
                    <label>Weight (g)</label>
                    <input type="number" step="0.01" name="weight" value="<?php echo $coin['weight']; ?>">
                </div>
                <div class="form-group">
                    <label>Diameter (mm)</label>
                    <input type="number" step="0.01" name="diameter" value="<?php echo $coin['diameter']; ?>">
                </div>
                <div class="form-group">
                    <label>Thickness (mm)</label>
                    <input type="number" step="0.01" name="thickness" value="<?php echo $coin['thickness']; ?>">
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" class="btn btn-accent" style="padding: 12px 30px;">Save Changes</button>
                <a href="../catalog_coin.php?id=<?php echo $id; ?>" class="btn" style="background: #ccc; color: #333;">Cancel</a>
            </div>

        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>