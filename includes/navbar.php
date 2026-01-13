<nav class="navbar">
    <div class="brand">
        <a href="<?php echo isset($_SESSION['user_id']) ? 'index.php' : 'countries.php'; ?>" style="color: inherit; text-decoration: none;">
            Coin Collector
        </a>
    </div>
    
    <div class="nav-links">
        <a href="countries.php">Countries</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="index.php">My Collection</a>
            
            <span style="margin-left: 20px; color: var(--accent-color); font-weight: bold;">
                Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>
            </span>
            <a href="auth/logout.php" class="btn" style="padding: 5px 15px; margin-left: 10px; background-color: var(--danger); font-size: 0.9rem;">Logout</a>
        
        <?php else: ?>
            <a href="login.php" style="margin-left: 20px;">Login</a>
            <a href="register.php" class="btn btn-accent" style="margin-left: 10px; color: var(--primary-color);">Register</a>
        <?php endif; ?>
    </div>
</nav>