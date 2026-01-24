<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

function uploadAdminImage($file, $targetDir) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload error code: " . $file['error']);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) throw new Exception("Invalid file type.");
    
    $filename = 'catalog_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    
    if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
        return 'uploads/coins/' . $filename;
    }
    throw new Exception("Failed to move file.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $coin_id = (int)$_POST['coin_id'];
    $action  = $_POST['action'];

    try {
        if ($action === 'approve') {
            $title = trim($_POST['title']);
            $denom = trim($_POST['denomination']);
            $year  = (int)$_POST['year'];
            $material = trim($_POST['material']);
            $period = trim($_POST['period']);
            $desc = trim($_POST['description']);

            $weight = !empty($_POST['weight']) ? $_POST['weight'] : NULL;
            $diameter = !empty($_POST['diameter']) ? $_POST['diameter'] : NULL;
            $thickness = !empty($_POST['thickness']) ? $_POST['thickness'] : NULL;
            $mintage = !empty($_POST['mintage']) ? $_POST['mintage'] : NULL;

            $uploadDir = '../uploads/coins/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $new_front = uploadAdminImage($_FILES['admin_img_front'], $uploadDir);
            $new_back  = uploadAdminImage($_FILES['admin_img_back'], $uploadDir);

            $sql = "UPDATE catalog_coins SET 
                    title = ?, denomination = ?, year = ?, 
                    material = ?, period = ?, description = ?, 
                    weight = ?, diameter = ?, thickness = ?, mintage = ?,
                    is_approved = 1";
            
            $params = [
                $title, $denom, $year, 
                $material, $period, $desc,
                $weight, $diameter, $thickness, $mintage
            ];

            if ($new_front) {
                $sql .= ", catalog_image_front = ?";
                $params[] = $new_front;
            }
            if ($new_back) {
                $sql .= ", catalog_image_back = ?";
                $params[] = $new_back;
            }

            $sql .= " WHERE id = ?";
            $params[] = $coin_id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $_SESSION['success'] = "Coin approved. Catalog updated successfully!";

        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("DELETE FROM catalog_coins WHERE id = ?");
            $stmt->execute([$coin_id]);
            $_SESSION['success'] = "Request rejected.";
        }

    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: ../admin/dashboard.php");
    exit;
}
?>