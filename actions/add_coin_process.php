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

function uploadImage($file, $targetDir)
{
    // Ако файлът липсва в масива изобщо
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
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

        $uploadDir = '../uploads/coins/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $pdo->beginTransaction();

        // Снимки (вкл. новата за ръба)
        $img_front = uploadImage($_FILES['img_front'] ?? null, $uploadDir);
        $img_back  = uploadImage($_FILES['img_back'] ?? null, $uploadDir);
        $img_edge  = uploadImage($_FILES['img_edge'] ?? null, $uploadDir);

        $catalog_coin_id = 0;

        if ($is_manual == '1') {
            // Създаваме НОВА каталожна монета
            $new_title = trim($_POST['new_title']);
            $new_denom = trim($_POST['new_denomination']);
            $new_year = (int)$_POST['new_year'];

            $period = trim($_POST['period'] ?? '');
            $description = trim($_POST['description'] ?? '');

            $weight = !empty($_POST['weight']) ? $_POST['weight'] : NULL;
            $diameter = !empty($_POST['diameter']) ? $_POST['diameter'] : NULL;
            $mintage = !empty($_POST['mintage']) ? $_POST['mintage'] : NULL;

            // ВАЖНО: Премахнахме колоната material от заявката
            $stmtNew = $pdo->prepare("
                INSERT INTO catalog_coins 
                (country_id, title, denomination, year, period, description, 
                 weight, diameter, mintage,
                 catalog_image_front, catalog_image_back, catalog_image_edge, created_by_user_id, is_approved) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
            ");

            $stmtNew->execute([
                $country_id,
                $new_title,
                $new_denom,
                $new_year,
                $period,
                $description,
                $weight,
                $diameter,
                $mintage,
                $img_front,
                $img_back,
                $img_edge, // НОВО
                $user_id
            ]);

            $catalog_coin_id = $pdo->lastInsertId();

            // --- НОВА ЛОГИКА: ЗАПИС НА МАТЕРИАЛИТЕ В coin_composition ---
            if (isset($_POST['material_id']) && is_array($_POST['material_id'])) {
                $stmtComp = $pdo->prepare("INSERT INTO coin_composition (catalog_coin_id, material_id, percentage) VALUES (?, ?, ?)");
                
                for ($i = 0; $i < count($_POST['material_id']); $i++) {
                    $m_id = $_POST['material_id'][$i];
                    $m_perc = $_POST['material_percentage'][$i];
                    
                    // Записваме само ако и двете полета не са празни
                    if (!empty($m_id) && !empty($m_perc)) {
                        $stmtComp->execute([$catalog_coin_id, $m_id, $m_perc]);
                    }
                }
            }
            // ---------------------------------------------------------------

        } else {
            if (empty($_POST['catalog_coin_id'])) {
                throw new Exception("Please select a coin from the list.");
            }
            $catalog_coin_id = $_POST['catalog_coin_id'];
        }

        // Запис в user_coins (Добавяме и own_image_edge)
        $stmtUser = $pdo->prepare("
            INSERT INTO user_coins 
            (user_id, catalog_coin_id, grade, status, own_image_front, own_image_back, own_image_edge) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtUser->execute([
            $user_id,
            $catalog_coin_id,
            $grade,
            $status,
            $img_front,
            $img_back,
            $img_edge // НОВО
        ]);
                
        $new_user_coin_id = $pdo->lastInsertId();

        // Логика за допълнителни снимки в галерията
        if (!empty($_FILES['gallery']['name'][0])) {
            $total_files = count($_FILES['gallery']['name']);
            $stmtGallery = $pdo->prepare("INSERT INTO user_coin_images (user_coin_id, image_path) VALUES (?, ?)");

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                    $tempFile = [
                        'name'     => $_FILES['gallery']['name'][$i],
                        'type'     => $_FILES['gallery']['type'][$i],
                        'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                        'error'    => $_FILES['gallery']['error'][$i],
                        'size'     => $_FILES['gallery']['size'][$i]
                    ];
                    
                    try {
                        $gallery_path = uploadImage($tempFile, $uploadDir);
                        if ($gallery_path) {
                            $stmtGallery->execute([$new_user_coin_id, $gallery_path]);
                        }
                    } catch (Exception $e) {
                        continue; 
                    }
                }
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Coin added successfully!";
        header("Location: ../index.php");
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "Error: " . $e->getMessage();

        if (isset($_POST['is_manual']) && $_POST['is_manual'] == '1') {
            header("Location: ../create_catalog_coin.php");
        } else {
            header("Location: ../add_coin.php");
        }
        exit;
    }
}