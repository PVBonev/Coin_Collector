<?php
// login.php
session_start();

if (isset($_SESSION['user_id'])) { 
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-box" style="text-align: center;">
            
            <div style="margin-bottom: 15px;">
                <img src="Logo/logo.svg" alt="Coin Collector Logo" style="width: 100px; height: auto;">
            </div>

            <h2 class="brand" style="margin-bottom: 20px; margin-top: 0;">Coin Collector</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="color: var(--danger); margin-bottom: 15px; font-weight: bold;">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div style="color: green; margin-bottom: 15px; font-weight: bold;">
                    <?php
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="auth/login_process.php" method="POST">
                <div class="form-group" style="text-align: left;">
                    <label>Username or Email</label>
                    <input type="text" name="identifier" required autofocus>
                </div>

                <div class="form-group" style="text-align: left;">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div style="text-align: right; margin-bottom: 15px;">
                    <a href="forgot_password.php" style="font-size: 0.85rem; color: var(--text-light);">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-accent" style="width: 100%;">Login</button>
            </form>

            <p style="margin-top: 15px; font-size: 0.9rem;">
                Don't have an account? <a href="register.php" style="color: var(--accent-color);">Register here</a>
            </p>
            <p style="margin-top: 10px;">
                <a href="countries.php" style="color: var(--text-light); text-decoration: underline;">Continue as Guest</a>
            </p>
        </div>
    </div>
</body>
</html>