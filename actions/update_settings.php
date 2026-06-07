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

$user_id = $_SESSION['user_id'];

// initialise s3 client
$s3Client = new S3Client([
    'version'     => 'latest',
    'region'      => AWS_S3_REGION,
    'credentials' => [
        'key'    => AWS_S3_KEY,
        'secret' => AWS_S3_SECRET,
        'token'  => AWS_S3_TOKEN
    ]
]);

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
            $file = $_FILES['profile_pic'];
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                if ($file['size'] <= 5 * 1024 * 1024) {
                    // generate name for s3
                    $filename = 'profile_pics/avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

                    try {
                        // upload to s3
                        $result = $s3Client->putObject([
                            'Bucket'      => AWS_S3_BUCKET,
                            'Key'         => $filename,
                            'SourceFile'  => $file['tmp_name'],
                            'ContentType' => mime_content_type($file['tmp_name'])
                        ]);

                        // take public url
                        $imagePath = $result->get('ObjectURL');

                    } catch (AwsException $e) {
                        $_SESSION['error'] = "Failed to upload to S3: " . $e->getMessage();
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

        if (!empty($_POST['new_password'])) {
            $current_password = $_POST['verify_password'];
            $new_password = $_POST['new_password'];

            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $updatePass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updatePass->execute([$hashed_password, $user_id]);
                $_SESSION['success'] = "Profile and password updated successfully!";
            } else {
                $_SESSION['error'] = "Incorrect current password.";
            }
        }

        header("Location: ../settings.php");
        exit;
    }
}
?>