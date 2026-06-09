<?php
session_start();
require '../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Initialize S3 client without credentials (assumes IAM role or environment variables)
$s3Client = new S3Client([
    'version'     => 'latest',
    'region'      => AWS_S3_REGION
]);

function uploadMyImage($file, $s3Client)
{
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($ext, $allowed)) return null;

    $filename = 'coins/mycoin_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    try {
        $result = $s3Client->putObject([
            'Bucket'      => AWS_S3_BUCKET,
            'Key'         => $filename,
            'SourceFile'  => $file['tmp_name'],
            'ContentType' => mime_content_type($file['tmp_name'])
        ]);
        return $result->get('ObjectURL');
    } catch (AwsException $e) {
        return null;
    }
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
        $price = !empty($_POST['price']) ? (float)$_POST['price'] : 0;
        $notes = trim($_POST['private_notes']);

        $purchase_price = !empty($_POST['purchase_price']) ? (float)$_POST['purchase_price'] : NULL;
        $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : NULL;
        $purchase_location = !empty($_POST['purchase_location']) ? trim($_POST['purchase_location']) : NULL;

        // Uplload new images if provided
        $img_front = uploadMyImage($_FILES['img_front'], $s3Client);
        $img_back = uploadMyImage($_FILES['img_back'], $s3Client);

        $sql = "UPDATE user_coins SET 
                grade=?, status=?, price=?, private_notes=?, purchase_price=?, purchase_date=?, purchase_location=?";

        $params = [
            $grade, $status, $price, $notes, $purchase_price, $purchase_date, $purchase_location
        ];

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

        // Delete selected gallery images
        if (isset($_POST['delete_gallery']) && is_array($_POST['delete_gallery'])) {
            foreach ($_POST['delete_gallery'] as $imgId) {
                $stmtGet = $pdo->prepare("SELECT image_path FROM user_coin_images WHERE id = ? AND user_coin_id = ?");
                $stmtGet->execute([$imgId, $user_coin_id]);
                $imgRow = $stmtGet->fetch();
                
                if ($imgRow) {
                    // Take the S3 key from the URL
                    $key = str_replace('https://' . AWS_S3_BUCKET . '.s3.' . AWS_S3_REGION . '.amazonaws.com/', '', $imgRow['image_path']);
                    
                    try {
                        // Delete from S3
                        $s3Client->deleteObject([
                            'Bucket' => AWS_S3_BUCKET,
                            'Key'    => $key
                        ]);
                    } catch (AwsException $e) {
                        // IGNORE errors during deletion, we will still remove the DB record
                    }

                    // Delete from database
                    $stmtDel = $pdo->prepare("DELETE FROM user_coin_images WHERE id = ?");
                    $stmtDel->execute([$imgId]);
                }
            }
        }

        // Upload new gallery images if provided
        if (!empty($_FILES['new_gallery']['name'][0])) {
            $total_files = count($_FILES['new_gallery']['name']);
            $stmtGallery = $pdo->prepare("INSERT INTO user_coin_images (user_coin_id, image_path) VALUES (?, ?)");

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['new_gallery']['error'][$i] === UPLOAD_ERR_OK) {
                    $tempFile = [
                        'name'     => $_FILES['new_gallery']['name'][$i],
                        'type'     => $_FILES['new_gallery']['type'][$i],
                        'tmp_name' => $_FILES['new_gallery']['tmp_name'][$i],
                        'error'    => $_FILES['new_gallery']['error'][$i],
                        'size'     => $_FILES['new_gallery']['size'][$i]
                    ];
                    
                    $gallery_path = uploadMyImage($tempFile, $s3Client); 
                    if ($gallery_path) {
                        $stmtGallery->execute([$user_coin_id, $gallery_path]);
                    }
                }
            }
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = "Coin details updated successfully.";
        header("Location: ../user_coin_details.php?id=" . $user_coin_id);
        exit;
    }
}