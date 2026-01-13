<?php
session_start();

if (!isset($_SESSION['user_id'])) {//go to login page if not logged in
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Collection - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1>My Dashboard</h1>
        <div class="card">
            <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
            <p>This page is visible ONLY to logged-in users.</p>
        </div>
    </div>
</body>
</html>