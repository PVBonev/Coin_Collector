<?php
session_start();//everyone can see this page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Countries - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1>Coin Catalog by Country</h1>
        <p>Browse coins from around the world.</p>

        <div class="coin-grid" style="margin-top: 20px;"> <!-- test data -->
            <div class="card">
                <h3>Bulgaria</h3>
                <p>Info</p>
            </div>
            <div class="card">
                <h3>USA</h3>
                <p>Info</p>
            </div>
        </div>
    </div>
</body>
</html>