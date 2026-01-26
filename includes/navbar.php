<?php
$pathPrefix = file_exists(__DIR__ . '/../config/db.php') && !file_exists('config/db.php') ? '../' : '';
?>

<nav class="navbar" style="padding: 10px 0; background-color: var(--primary-color);">
    <div style="width: 100%; padding: 0 30px; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">

        <a href="<?php echo $pathPrefix; ?>index.php" class="navbar-brand" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; font-size: 1.4rem; font-weight: bold;">
            <img src="<?php echo $pathPrefix; ?>Logo/logo.svg" alt="Coin Collector Logo" style="height: 45px; width: auto;">
        </a>

        <div class="nav-links" style="display: flex; align-items: center; gap: 15px;">

            <?php if (isset($_SESSION['user_id'])): ?>

                <a href="<?php echo $pathPrefix; ?>countries.php" style="color: white; text-decoration: none;">Countries</a>
                <a href="<?php echo $pathPrefix; ?>index.php" style="color: white; text-decoration: none;">Dashboard</a>

                <?php
                $searchType = isset($_GET['type']) ? $_GET['type'] : 'coins';
                $searchQuery = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
                ?>
                <form action="<?php echo $pathPrefix; ?>search_results.php" method="GET" style="display: inline-flex; align-items: center; gap: 5px; margin-right: 15px;">
                    <select name="type" style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem;">
                        <option value="coins" <?php echo ($searchType == 'coins') ? 'selected' : ''; ?>>Coins</option>
                        <option value="users" <?php echo ($searchType == 'users') ? 'selected' : ''; ?>>Users</option>
                        <option value="countries" <?php echo ($searchType == 'countries') ? 'selected' : ''; ?>>Countries</option>
                    </select>
                    <input type="text" name="q" value="<?php echo $searchQuery; ?>" placeholder="Search..." required
                        style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.9rem; width: 150px;">
                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.9rem; background: var(--accent-color); color: white; border: none; cursor: pointer;">&#128269;</button>
                </form>

                <?php
                if (isset($pdo)) {
                    $stmtNotif = $pdo->prepare("SELECT COUNT(*) FROM trades WHERE receiver_id = ? AND status = 'pending'");
                    $stmtNotif->execute([$_SESSION['user_id']]);
                    $pending_count = $stmtNotif->fetchColumn();
                } else {
                    $pending_count = 0;
                }
                ?>
                <a href="<?php echo $pathPrefix; ?>my_trades.php" style="position: relative; text-decoration: none; margin-right: 10px; display: flex; align-items: center;" title="Trade Notifications">
                    <span style="font-size: 1.4rem; color: white;">&#128276;</span> 
                    <?php if ($pending_count > 0): ?>
                        <span style="
                            position: absolute; top: -5px; right: -5px;
                            background-color: #dc3545; color: white;
                            border-radius: 50%; padding: 2px 5px;
                            font-size: 0.7rem; font-weight: bold;
                            border: 1px solid var(--primary-color);
                            line-height: 1; min-width: 15px; text-align: center;
                        ">
                            <?php echo $pending_count; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="<?php echo $pathPrefix; ?>admin/dashboard.php" style="color: #ffc107; font-weight: bold; text-decoration: none;">Admin Panel</a>
                <?php endif; ?>

                <?php
                if (isset($pdo)) {
                    $stmtNav = $pdo->prepare("SELECT profile_image, username FROM users WHERE id = ?");
                    $stmtNav->execute([$_SESSION['user_id']]);
                    $navUser = $stmtNav->fetch();
                } else {
                    $navUser = ['username' => $_SESSION['username'] ?? 'User', 'profile_image' => null];
                }

                $avatarUrl = '';
                $hasAvatar = false;

                if (!empty($navUser['profile_image'])) {
                    $checkPath = $navUser['profile_image'];
                    if (file_exists($checkPath)) {
                        $avatarUrl = $pathPrefix . $navUser['profile_image'];
                        $hasAvatar = true;
                    } elseif (file_exists('../' . $checkPath)) { //if we are in admin
                         $avatarUrl = $pathPrefix . $navUser['profile_image'];
                         $hasAvatar = true;
                    }
                    if(!$hasAvatar) {
                         $avatarUrl = $pathPrefix . $navUser['profile_image'];
                         $hasAvatar = true; 
                    }
                }

                $initial = strtoupper(substr($navUser['username'], 0, 1));
                ?>

                <div class="user-menu" style="margin-left: 5px; position: relative; display: inline-block;">
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
                        <a href="<?php echo $pathPrefix; ?>settings.php">Settings</a>
                        <a href="<?php echo $pathPrefix; ?>auth/logout_process.php" style="color: #dc3545;">Logout</a>
                    </div>
                </div>

            <?php else: ?>
                <a href="<?php echo $pathPrefix; ?>countries.php" style="color: white; text-decoration: none;">Countries</a>
                <a href="<?php echo $pathPrefix; ?>login.php" style="margin-left: 20px; color: white; text-decoration: none;">Login</a>
                <a href="<?php echo $pathPrefix; ?>register.php" class="btn btn-accent" style="margin-left: 10px; color: var(--primary-color); text-decoration: none; padding: 5px 15px; border-radius: 4px;">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>