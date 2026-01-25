<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'delete_account') {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            session_destroy();
            header("Location: ../countries.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Could not delete account: " . $e->getMessage();
            header("Location: ../settings.php");
            exit;
        }
    }

    if ($action === 'update_profile') {
        $email = trim($_POST['email']);
        $location = trim($_POST['location']);
        $bio = trim($_POST['bio']);
        
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->execute([$email, $user_id]);
        if ($checkEmail->rowCount() > 0) {
            $_SESSION['error'] = "This email is already taken.";
            header("Location: ../settings.php");
            exit;
        }

        $imagePath = null;
        
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            
            $targetDir = '../uploads/user_prof_pics/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $file = $_FILES['profile_pic'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {
                if ($file['size'] <= 5 * 1024 * 1024) { // Max 5MB
                    $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                    $targetFile = $targetDir . $filename;

                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                        $imagePath = 'uploads/user_prof_pics/' . $filename;
                    } else {
                        $_SESSION['error'] = "Failed to move uploaded file. Check folder permissions.";
                    }
                } else {
                    $_SESSION['error'] = "Image size too large (Max 5MB).";
                }
            } else {
                $_SESSION['error'] = "Invalid file format. Only JPG, PNG, WEBP allowed.";
            }
        }

        try {
            if ($imagePath) {
                $sql = "UPDATE users SET email = ?, location = ?, bio = ?, profile_image = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email, $location, $bio, $imagePath, $user_id]);
            } else {
                $sql = "UPDATE users SET email = ?, location = ?, bio = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email, $location, $bio, $user_id]);
            }
            
            if (!isset($_SESSION['error'])) {
                $_SESSION['success'] = "Profile updated successfully!";
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header("Location: ../settings.php");
        exit;
    }
}
?>