<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

function uploadMyImage($file, $targetDir) {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'mycoin_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
        return 'uploads/coins/' . $filename;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_coin_id = (int)$_POST['user_coin_id'];
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];

    $check = $pdo->prepare("SELECT id FROM user_coins WHERE id = ? AND user_id = ?");
    $check->execute([$user_coin_id, $user_id]);
    if (!$check->fetch()) die("Unauthorized access.");

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM user_coins WHERE id = ?");
        $stmt->execute([$user_coin_id]);
        $_SESSION['success'] = "Coin removed from collection.";
        header("Location: ../index.php");
        exit;

    } elseif ($action === 'update') {
        $grade = $_POST['grade'];
        $status = $_POST['status'];
        $price = (float)$_POST['price']; 
        $notes = trim($_POST['private_notes']);
        
        $uploadDir = '../uploads/coins/';
        $img_front = uploadMyImage($_FILES['img_front'], $uploadDir);
        $img_back = uploadMyImage($_FILES['img_back'], $uploadDir);

        $sql = "UPDATE user_coins SET grade=?, status=?, price=?, private_notes=?";
        $params = [$grade, $status, $price, $notes];

        if ($img_front) {
            $sql .= ", own_image_front=?";
            $params[] = $img_front;
        }
        if ($img_back) {
            $sql .= ", own_image_back=?";
            $params[] = $img_back;
        }
        
        $sql .= " WHERE id=?";
        $params[] = $user_coin_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = "Coin details updated.";
        header("Location: ../user_coin_details.php?id=" . $user_coin_id);
        exit;
    }
}
?>