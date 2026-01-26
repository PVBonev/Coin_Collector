<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Data Management</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1>Data Management</h1>
        <p>Export your collection from your profile to CSV or import new coins.</p>

        <?php if (isset($_SESSION['import_report'])): ?>
            <div style="margin-bottom: 20px;">
                <?php echo $_SESSION['import_report'];
                unset($_SESSION['import_report']); ?>
            </div>
        <?php endif; ?>

        <div class="data-grid">
            <div class="data-card">
                <h2 style="margin-top: 0; color: var(--accent-color);">Export Collection</h2>
                <p>Download your entire collection as a CSV file. You can use this file as a backup or as a template for editing.</p>

                <br>

                <a href="actions/export_csv.php" class="btn btn-accent" style="width: 100%; text-align: center; display: inline-block;">
                    &#11015; Download CSV &#11015;
                </a>
            </div>

            <div class="data-card">
                <h2 style="margin-top: 0; color: #28a745;">Import from CSV</h2>
                <p>Add your coin collection by uploading a CSV file.</p>

                <div class="alert-warning">
                    <strong>Important:</strong>
                    <ul style="margin: 5px 0 0 20px;">
                        <li>Use the exact column format as the example file bellow.</li>
                        <li>Coins must already exist in the global catalog <br> (Country + Year + Denomination + Title must match).</li>
                        <li>New coin types will be skipped.</li>
                    </ul>
                </div>

                <form action="actions/import_csv_process.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select CSV File</label>
                        <input type="file" name="csv_file" accept=".csv" required>
                    </div>
                    <button type="submit" class="btn" style="background: #28a745; color: white; width: 100%;">
                        &#11014; Upload CSV &#11014;
                    </button>
                </form>
            </div>
        </div>
        <div class="data-grid">
        </div>

        <div class="card" style="margin-top: 40px;">
            <h3 style="margin-top: 0; color: #555; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                Required CSV Format
            </h3>
            <p style="color: #666; margin-bottom: 20px;">
                Your CSV file needs to follow this exact structure to be imported correctly. The only mandatory columns are <strong>Country</strong>, <strong>Year</strong>, <strong>Denomination</strong>, and <strong>Title</strong>. Other columns are optional.
                <br>
                <small>Tip: You can generate a template by using the "Export" feature above.</small>
            </p>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; text-align: center; border: 1px dashed #ccc;">
                <img src="assets/images/csv_example.png" alt="CSV Table Example" style="max-width: 100%; height: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">

                <p style="margin-top: 10px; color: #888; font-style: italic; font-size: 0.9rem;">
                    Example of a correctly formatted spreadsheet
                </p>
            </div>
        </div>

    </div>
</body>

</html>
</div>
</body>

</html>