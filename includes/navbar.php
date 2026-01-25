<nav class="navbar">
    <div class="brand">
        <a href="<?php echo isset($_SESSION['user_id']) ? 'index.php' : 'countries.php'; ?>" style="color: inherit; text-decoration: none;">
            Coin Collector
        </a>
    </div>
    
    <div class="nav-links" style="display: flex; align-items: center;">
        
        <?php if (isset($_SESSION['user_id'])): ?>
            
            <?php 
                $searchType = isset($_GET['type']) ? $_GET['type'] : 'coins'; 
                $searchQuery = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
            ?>
            <form action="search_results.php" method="GET" style="display: inline-flex; align-items: center; gap: 5px; margin-right: 20px;">
                <select name="type" style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem;">
                    <option value="coins" <?php echo ($searchType == 'coins') ? 'selected' : ''; ?>>Coins</option>
                    <option value="users" <?php echo ($searchType == 'users') ? 'selected' : ''; ?>>Users</option>
                    <option value="countries" <?php echo ($searchType == 'countries') ? 'selected' : ''; ?>>Countries</option>
                </select>

                <input type="text" name="q" value="<?php echo $searchQuery; ?>" placeholder="Search..." required 
                       style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem; width: 150px;">

                <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.9rem; background: var(--accent-color); color: white; border: none; cursor: pointer;">
                    &#128269;
                </button>
            </form>
            <a href="countries.php">Countries</a>
            <a href="index.php">Dashboard</a>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin/dashboard.php" style="color: #ff9800; font-weight: bold;">Requests</a>
            <?php endif; ?>
            
            <div class="user-menu">
                <button class="settings-btn">&#9881;</button> <div class="dropdown-content">
                    <div class="dropdown-header">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </div>
                    
                    <a href="settings.php">Settings</a> <a href="../auth/logout_process.php" style="color: #dc3545;">Logout</a>
                </div>
            </div>

        <?php else: ?>
             <?php 
                $searchType = isset($_GET['type']) ? $_GET['type'] : 'coins'; 
                $searchQuery = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
            ?>
            <form action="search_results.php" method="GET" style="display: inline-flex; align-items: center; gap: 5px; margin-right: 20px;">
                <select name="type" style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem;">
                    <option value="coins" <?php echo ($searchType == 'coins') ? 'selected' : ''; ?>>Coins</option>
                    <option value="users" <?php echo ($searchType == 'users') ? 'selected' : ''; ?>>Users</option>
                    <option value="countries" <?php echo ($searchType == 'countries') ? 'selected' : ''; ?>>Countries</option>
                </select>

                <input type="text" name="q" value="<?php echo $searchQuery; ?>" placeholder="Search..." required 
                       style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem; width: 150px;">

                <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.9rem; background: var(--accent-color); color: white; border: none; cursor: pointer;">
                    &#128269;
                </button>
            </form>
            <a href="countries.php">Countries</a>
            <a href="login.php" style="margin-left: 20px;">Login</a>
            <a href="register.php" class="btn btn-accent" style="margin-left: 10px; color: var(--primary-color);">Register</a>
        <?php endif; ?>
    </div>
</nav>