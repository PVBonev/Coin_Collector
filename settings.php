<?php
// settings.php
session_start();
require 'config/db.php';

// Проверка за логнат потребител
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Взимаме текущите данни
$stmt = $pdo->prepare("SELECT username, email, profile_image, bio, location, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) die("User not found.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .settings-container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        /* Профилна секция */
        .profile-preview {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent-color);
            background-color: #f0f0f0;
        }

        .user-meta h2 { margin: 0 0 5px 0; }
        .user-meta p { margin: 0; color: #777; font-size: 0.9rem; }

        /* Форма */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
        textarea.form-control { resize: vertical; min-height: 80px; }

        /* File Input Style */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="settings-container">
            <h1 style="margin-top: 0;">Account Settings</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="actions/update_settings.php" method="POST" enctype="multipart/form-data">
                
                <div class="profile-preview">
                    <?php 
                        $avatar = !empty($user['profile_image']) ? $user['profile_image'] : 'assets/images/user-placeholder.png';
                    ?>
                    <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="avatar-large">
                    
                    <div class="user-meta">
                        <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <p>Member since: <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                        
                        <div style="margin-top: 10px;">
                            <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 5px;">Change Photo:</label>
                            <input type="file" name="profile_pic" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" 
                           value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" 
                           placeholder="e.g. Sofia, Bulgaria">
                </div>

                <div class="form-group">
                    <label>Bio / About Me</label>
                    <textarea name="bio" class="form-control" placeholder="Tell other collectors about your interests..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-accent" style="width: 100%; padding: 12px; font-size: 1rem;">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>