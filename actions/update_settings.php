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
            $_SESSION['error'] = "Error deleting account.";
            header("Location: ../settings.php");
            exit;
        }
    }

    if ($action === 'update_profile') {
        $email = trim($_POST['email']);
        $location = trim($_POST['location']);
        $bio = trim($_POST['bio']);
        
        $current_pass_input = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmtUser->execute([$user_id]);
        $currentUser = $stmtUser->fetch();

        $sensitive_change = false;

        if ($email !== $currentUser['email']) {
            $sensitive_change = true;
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $user_id]);
            if ($check->rowCount() > 0) {
                $_SESSION['error'] = "Email already taken.";
                header("Location: ../settings.php");
                exit;
            }
        }

        if (!empty($new_pass)) {
            $sensitive_change = true;
            if ($new_pass !== $confirm_pass) {
                $_SESSION['error'] = "New passwords do not match.";
                header("Location: ../settings.php");
                exit;
            }
            if (strlen($new_pass) < 6) {
                $_SESSION['error'] = "Password too short (min 6 chars).";
                header("Location: ../settings.php");
                exit;
            }
        }

        if ($sensitive_change) {
            if (empty($current_pass_input)) {
                $_SESSION['error'] = "Current password is required for security changes.";
                header("Location: ../settings.php");
                exit;
            }
            if (!password_verify($current_pass_input, $currentUser['password'])) {
                $_SESSION['error'] = "Incorrect current password!";
                header("Location: ../settings.php");
                exit;
            }
        }

        $targetDir = '../uploads/user_prof_pics/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $imagePath = null;
        
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_pic'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
                    $imagePath = 'uploads/user_prof_pics/' . $filename;
                }
            }
        }

        //save to base
        try {
            $finalPasswordHash = $currentUser['password'];
            if (!empty($new_pass)) {
                $finalPasswordHash = password_hash($new_pass, PASSWORD_DEFAULT);
            }

            $sql = "UPDATE users SET email=?, location=?, bio=?, password=? ";
            $params = [$email, $location, $bio, $finalPasswordHash];

            if ($imagePath) {
                $sql .= ", profile_image=? ";
                $params[] = $imagePath;
            }

            $sql .= "WHERE id=?";
            $params[] = $user_id;

            $stmtUpdate = $pdo->prepare($sql);
            $stmtUpdate->execute($params);

            $_SESSION['success'] = "Profile updated successfully!";

        } catch (PDOException $e) {
            $_SESSION['error'] = "DB Error: " . $e->getMessage();
        }

        header("Location: ../settings.php");
        exit;
    }
}
?>