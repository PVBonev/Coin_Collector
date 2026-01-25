<nav class="navbar" style="padding: 10px 0; background-color: var(--primary-color);">
    <div style="width: 100%; padding: 0 30px; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">
        
        <a href="index.php" class="navbar-brand" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; font-size: 1.4rem; font-weight: bold;">
            <?php 
                $logoPath = file_exists('Logo/logo.svg') ? 'Logo/logo.svg' : '../Logo/logo.svg'; 
                if (!file_exists($logoPath) && file_exists('../Logo/logo.svg')) {
                    $logoPath = '../Logo/logo.svg';
                }
            ?>
            <img src="<?php echo $logoPath; ?>" alt="Coin Collector Logo" style="height: 45px; width: auto;">
        </a>
        
        <div class="nav-links" style="display: flex; align-items: center; gap: 15px;">
            
            <?php if (isset($_SESSION['user_id'])): ?>

                <a href="countries.php" style="color: white; text-decoration: none;">Countries</a>
                <a href="index.php" style="color: white; text-decoration: none;">Dashboard</a>
                
                <?php 
                    $searchType = isset($_GET['type']) ? $_GET['type'] : 'coins'; 
                    $searchQuery = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
                ?>
                <form action="search_results.php" method="GET" style="display: inline-flex; align-items: center; gap: 5px; margin-right: 15px;">
                    <select name="type" style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem;">
                        <option value="coins" <?php echo ($searchType == 'coins') ? 'selected' : ''; ?>>Coins</option>
                        <option value="users" <?php echo ($searchType == 'users') ? 'selected' : ''; ?>>Users</option>
                        <option value="countries" <?php echo ($searchType == 'countries') ? 'selected' : ''; ?>>Countries</option>
                    </select>
                    <input type="text" name="q" value="<?php echo $searchQuery; ?>" placeholder="Search..." required 
                           style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem; width: 150px;">
                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.9rem; background: var(--accent-color); color: white; border: none; cursor: pointer;">&#128269;</button>
                </form>

                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" style="color: #ffc107; font-weight: bold; text-decoration: none;">Requests</a>
                <?php endif; ?>
                
                <?php
                    $stmtNav = $pdo->prepare("SELECT profile_image, username FROM users WHERE id = ?");
                    $stmtNav->execute([$_SESSION['user_id']]);
                    $navUser = $stmtNav->fetch();
                    
                    $avatarUrl = '';
                    $hasAvatar = false;
                    
                    if (!empty($navUser['profile_image'])) {
                        if (file_exists($navUser['profile_image'])) {
                            $avatarUrl = $navUser['profile_image'];
                            $hasAvatar = true;
                        } elseif (file_exists('../' . $navUser['profile_image'])) {
                            $avatarUrl = '../' . $navUser['profile_image'];
                            $hasAvatar = true;
                        }
                    }
                    
                    $initial = strtoupper(substr($navUser['username'], 0, 1));
                ?>

                <div class="user-menu" style="margin-left: 10px; position: relative; display: inline-block;">
                    <button class="settings-btn" style="background: none; border: none; padding: 0; cursor: pointer; display: flex; align-items: center;">
                        <?php if ($hasAvatar): ?>
                            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Profile" 
                                 style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.8);">
                        <?php else: ?>
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: #0056b3; color: white; 
                                        display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; 
                                        border: 2px solid rgba(255,255,255,0.8); text-transform: uppercase;">
                                <?php echo $initial; ?>
                            </div>
                        <?php endif; ?>
                    </button>
                    
                    <div class="dropdown-content">
                        <div class="dropdown-header" style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 5px; color: #666; font-size: 0.9rem;">
                            Hello, <strong><?php echo htmlspecialchars($navUser['username']); ?></strong>
                        </div>
                        <a href="settings.php">Settings</a> 
                        <a href="auth/logout_process.php" style="color: #dc3545;">Logout</a>
                    </div>
                </div>

            <?php else: ?>
                <?php 
                    $searchType = isset($_GET['type']) ? $_GET['type'] : 'coins'; 
                    $searchQuery = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
                ?>
                <form action="search_results.php" method="GET" style="display: inline-flex; align-items: center; gap: 5px; margin-right: 15px;">
                    <select name="type" style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem;">
                        <option value="coins" <?php echo ($searchType == 'coins') ? 'selected' : ''; ?>>Coins</option>
                        <option value="users" <?php echo ($searchType == 'users') ? 'selected' : ''; ?>>Users</option>
                        <option value="countries" <?php echo ($searchType == 'countries') ? 'selected' : ''; ?>>Countries</option>
                    </select>
                    <input type="text" name="q" value="<?php echo $searchQuery; ?>" placeholder="Search..." required 
                           style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem; width: 150px;">
                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.9rem; background: var(--accent-color); color: white; border: none; cursor: pointer;">&#128269;</button>
                </form>

                <a href="countries.php" style="color: white; text-decoration: none;">Countries</a>
                <a href="login.php" style="margin-left: 20px; color: white; text-decoration: none;">Login</a>
                <a href="register.php" class="btn btn-accent" style="margin-left: 10px; color: var(--primary-color); text-decoration: none; padding: 5px 15px; border-radius: 4px;">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>