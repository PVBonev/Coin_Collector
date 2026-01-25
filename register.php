<?php
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
    <title>Register - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box" style="text-align: center;">
            
            <div style="margin-bottom: 40px;">
                <img src="Logo/logo_login.svg" alt="Coin Collector Logo" style="display:block; margin:0 auto 15px; width:clamp(120px, 15vw, 220px); height:auto;">
            </div>


            <?php if (isset($_SESSION['error'])): ?>
                <div style="color: var(--danger); margin-bottom: 15px; font-weight: bold; background: #ffe6e6; padding: 10px; border-radius: 5px;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="auth/register_process.php" method="POST">
                <div class="form-group" style="text-align: left;">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Choose a unique username">
                </div>

                <div class="form-group" style="text-align: left;">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="name@example.com">
                </div>

                <div class="form-group" style="text-align: left;">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="Min 6 characters">
                </div>

                <div class="form-group" style="text-align: left;">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="Repeat password">
                </div>

                <button type="submit" class="btn btn-accent" style="width: 100%; margin-top: 10px;">Sign Up</button>
            </form>

            <p style="margin-top: 20px; font-size: 0.9rem;">
                Already have an account? <a href="login.php" style="color: var(--accent-color);">Login here</a>
            </p>
            <p style="margin-top: 10px;">
                <a href="countries.php" style="color: var(--text-light); text-decoration: underline;">Continue as Guest</a>
            </p>
        </div>
    </div>
</body>
</html>