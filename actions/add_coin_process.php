<?php
session_start();
require '../config/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

function uploadImage($file, $targetDir) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; 
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error code: " . $file['error']);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) throw new Exception("Invalid file type: $ext");
    if ($file['size'] > 5 * 1024 * 1024) throw new Exception("File too large (Max 5MB)");

    $filename = 'coin_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    
    if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
        return 'uploads/coins/' . $filename;
    }
    throw new Exception("Failed to move uploaded file.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $user_id = $_SESSION['user_id'];
        $country_id = $_POST['country_id'];
        $grade = $_POST['grade'];
        $status = $_POST['status'];
        $is_manual = $_POST['is_manual']; 

        //upload logic
        $uploadDir = '../uploads/coins/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        //start transaction
        $pdo->beginTransaction();

        $img_front = uploadImage($_FILES['img_front'], $uploadDir);
        $img_back  = uploadImage($_FILES['img_back'], $uploadDir);

        $catalog_coin_id = 0;

        if ($is_manual == '1') {
            //manual entry
            $new_title = trim($_POST['new_title']);
            $new_denom = trim($_POST['new_denomination']);
            $new_year = (int)$_POST['new_year'];

            $stmtNew = $pdo->prepare("
                INSERT INTO catalog_coins 
                (country_id, title, denomination, year, catalog_image_front, catalog_image_back, created_by_user_id, is_approved) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0)
            ");
            
            $stmtNew->execute([
                $country_id, 
                $new_title, 
                $new_denom, 
                $new_year, 
                $img_front, 
                $img_back, 
                $user_id
            ]);
            
            $catalog_coin_id = $pdo->lastInsertId();

        } else {
            if (empty($_POST['catalog_coin_id'])) {
                throw new Exception("Please select a coin from the list.");
            }
            $catalog_coin_id = $_POST['catalog_coin_id'];
        }

        //add to User Collection
        $stmtUser = $pdo->prepare("
            INSERT INTO user_coins 
            (user_id, catalog_coin_id, grade, status, own_image_front, own_image_back) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmtUser->execute([
            $user_id, 
            $catalog_coin_id, 
            $grade, 
            $status, 
            $img_front, 
            $img_back
        ]);

        $pdo->commit();
        $_SESSION['success'] = "Coin added successfully!";
        header("Location: ../index.php");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Log the error for debugging
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../add_coin.php");
        exit;
    }
}
?>