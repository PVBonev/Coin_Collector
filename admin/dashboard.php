<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->query("
    SELECT cc.*, c.name as country_name, u.username 
    FROM catalog_coins cc
    JOIN countries c ON cc.country_id = c.id
    JOIN users u ON cc.created_by_user_id = u.id
    WHERE cc.is_approved = 0
    ORDER BY cc.created_at ASC
");
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .admin-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .admin-table th, .admin-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background-color: #f8f9fa; font-weight: bold; color: #333; }
        .admin-table tr:hover { background-color: #f1f1f1; }
        .btn-sm { padding: 5px 10px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h1>Pending Requests</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (count($requests) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Proposed Title</th>
                        <th>Denomination</th>
                        <th>Year</th>
                        <th>User</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['country_name']); ?></td>
                            <td><?php echo htmlspecialchars($req['title']); ?></td>
                            <td><?php echo htmlspecialchars($req['denomination']); ?></td>
                            <td><?php echo $req['year']; ?></td>
                            <td><?php echo htmlspecialchars($req['username']); ?></td>
                            <td>
                                <a href="review_coin.php?id=<?php echo $req['id']; ?>" class="btn btn-accent btn-sm">
                                    Review & Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 40px;">
                <h3>No pending requests</h3>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>