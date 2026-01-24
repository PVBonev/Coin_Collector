<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location = trim($_POST['location']);
    $bio = trim($_POST['bio']);
    
    $targetDir = '../uploads/user_prof_pics/';
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $imagePath = null;
    
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            if ($file['size'] <= 5 * 1024 * 1024) { // max 5MB
                $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                $targetFile = $targetDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                    $imagePath = 'uploads/user_prof_pics/' . $filename;
                } else {
                    $_SESSION['error'] = "Failed to upload image.";
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
            $sql = "UPDATE users SET location = ?, bio = ?, profile_image = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$location, $bio, $imagePath, $user_id]);
        } else {
            $sql = "UPDATE users SET location = ?, bio = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$location, $bio, $user_id]);
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
?>