<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {

    $cols_order = isset($_POST['cols']) ? $_POST['cols'] : [];

    $col_map = array_flip($cols_order);

    $required = ['country', 'year', 'denomination', 'title'];
    foreach ($required as $req) {
        if (!isset($col_map[$req])) {
            $_SESSION['error'] = "Error: Missing required column mapping ($req).";
            header("Location: ../data_management.php");
            exit;
        }
    }

    $file = $_FILES['csv_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($ext !== 'csv') {
        $_SESSION['error'] = "Error: Please upload a CSV file.";
        header("Location: ../data_management.php");
        exit;
    }

    $handle = fopen($file['tmp_name'], "r");
    
    $header = fgetcsv($handle); 

    $imported = 0;
    $skipped_count = 0;
    $skipped_items = [];
    $rowNum = 1;

    while (($row = fgetcsv($handle)) !== FALSE) {
        $rowNum++;

        if (count($row) < count($cols_order)) {
            continue; 
        }

        $country = trim($row[$col_map['country']]);
        $year    = (int)trim($row[$col_map['year']]);
        $denom   = trim($row[$col_map['denomination']]);
        $title   = trim($row[$col_map['title']]);

        
        $grade = 'G'; // Default
        if (isset($col_map['grade']) && isset($row[$col_map['grade']])) {
            $grade = trim($row[$col_map['grade']]);
        }

        $status = 'collection'; // Default
        if (isset($col_map['status']) && isset($row[$col_map['status']])) {
            $status = trim($row[$col_map['status']]);
        }

        $price = 0; // Default
        if (isset($col_map['price']) && isset($row[$col_map['price']])) {
            $priceRaw = trim($row[$col_map['price']]);
            $price = (float)preg_replace('/[^0-9.]/', '', $priceRaw);
        }

        $notes = ''; // Default
        if (isset($col_map['note']) && isset($row[$col_map['note']])) {
            $notes = trim($row[$col_map['note']]);
        }

        try {
            $stmt = $pdo->prepare("
                SELECT cc.id 
                FROM catalog_coins cc
                JOIN countries c ON cc.country_id = c.id
                WHERE c.name = ? 
                    AND cc.year = ? 
                    AND cc.denomination = ? 
                    AND cc.title = ?
                LIMIT 1
            ");
            $stmt->execute([$country, $year, $denom, $title]);
            $coin = $stmt->fetch();

            if ($coin) {
                $insert = $pdo->prepare("
                    INSERT INTO user_coins (user_id, catalog_coin_id, grade, status, price, private_notes)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $validGrades = ['UNC', 'AU', 'XF', 'VF', 'F', 'VG', 'G'];
                if (empty($grade) || !in_array($grade, $validGrades)) $grade = 'G';

                $validStatus = ['collection', 'swap', 'sell'];
                if (!in_array($status, $validStatus)) $status = 'collection';

                $insert->execute([$_SESSION['user_id'], $coin['id'], $grade, $status, $price, $notes]);
                $imported++;
            } else {
                $skipped_count++;
                $skipped_items[] = "Row $rowNum: <strong>$country</strong> - $denom ($year)";
            }
        } catch (Exception $e) {
            $skipped_count++;
            $skipped_items[] = "Row $rowNum: System error";
        }
    }

    fclose($handle);

    if ($imported > 0) {
        $_SESSION['success'] = "Successfully imported $imported coins!";
    }

    if ($skipped_count > 0) {
        $_SESSION['import_errors'] = $skipped_items;
        if ($imported == 0) {
            $_SESSION['error'] = "No coins were imported. Check errors below.";
        } else {
             $_SESSION['import_report'] = "<div class='alert-warning'>Imported: $imported. Skipped: $skipped_count. <br>Check details below.</div>";
        }
    }

    header("Location: ../index.php");
    exit;
}