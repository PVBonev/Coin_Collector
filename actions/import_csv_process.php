<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) { die("Access Denied"); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    
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
        
        if (count($row) < 4) {
            continue; 
        }

        $country = trim($row[0]);
        $year    = (int)trim($row[1]);
        $denom   = trim($row[2]);
        $title   = trim($row[3]);
        $grade   = trim($row[4]);
        $status  = trim($row[5]);
        $notes   = isset($row[6]) ? trim($row[6]) : '';

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
                    INSERT INTO user_coins (user_id, catalog_coin_id, grade, status, private_notes)
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $validGrades = ['UNC', 'XF', 'VF', 'F', 'Good'];
                
                if (empty($grade) || !in_array($grade, $validGrades)) {
                    $grade = 'Good';
                }
                
                $validStatus = ['collection', 'swap', 'sell'];
                if (!in_array($status, $validStatus)) $status = 'collection';

                $insert->execute([$_SESSION['user_id'], $coin['id'], $grade, $status, $notes]);
                $imported++;
            } else {
                //saving which coin is ksipped
                $skipped_count++;
                $skipped_items[] = "Row $rowNum: <strong>$country</strong> - $denom ($year) - $title";
            }

        } catch (Exception $e) {
            $skipped_count++;
            $skipped_items[] = "Row $rowNum: System error for $denom ($year)";
        }
    }

    fclose($handle);

    //prepare messages for the user
    if ($imported > 0) {
        $_SESSION['success'] = "Successfully imported $imported coins!";
    }

    if ($skipped_count > 0) {
        //save the skipped items in session to show later
        $_SESSION['import_errors'] = $skipped_items;
        
        if ($imported == 0) {
            $_SESSION['error'] = "No coins were imported. Check the error list below.";
        }
    }

    //go back to index after importing
    header("Location: ../index.php");
    exit;
}
?>