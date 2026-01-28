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
        .column-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center; 
        }

        .chip {
            position: relative;
            display: inline-block;
            background: #f0f2f5;
            color: #555;
            border: 1px solid #ddd;
            padding: 8px 16px;
            border-radius: 50px; 
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
            user-select: none;
        }

        .chip input[type="checkbox"] {
            display: none;
        }

        .chip:hover {
            background: #e4e6eb;
            transform: translateY(-1px);
        }

        .chip:has(input:checked) {
            background: var(--accent-color); 
            color: white;
            border-color: var(--accent-color);
            box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        }

        .chip.locked {
            background: #343a40 !important; 
            color: #fff !important;
            border-color: #343a40;
            cursor: not-allowed;
            opacity: 0.9;
        }
        
        .chip.locked:hover {
            transform: none;
        }

        .preview-wrapper {
            background: #fff; 
            padding: 20px; 
            border: 1px solid #e1e4e8; 
            border-radius: 8px; 
            overflow-x: auto;
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            min-width: 600px;
        }
        
        .preview-table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 12px;
            color: #495057;
            text-align: left;
            font-weight: 600;
        }
        
        .preview-table td {
            border-bottom: 1px solid #dee2e6;
            padding: 10px 12px;
            color: #212529;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <a href="index.php" class="back-link">
            &#8592; Back to Dashboard
        </a>
        <h1>Data Management</h1>
        <p>Export your collection or import new coins using CSV files.</p>

        <?php if (isset($_SESSION['import_report'])): ?>
            <div style="margin-bottom: 20px;">
                <?php echo $_SESSION['import_report'];
                unset($_SESSION['import_report']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: #721c24; margin-bottom: 20px; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="data-grid">
            
            <div class="data-card">
                <h2 style="margin-top: 0; color: var(--accent-color);">Export Collection</h2>
                <p style="font-size: 0.9rem; margin-bottom: 15px;">Tap the columns you want to include:</p>

                <form action="actions/export_csv.php" method="GET" id="exportForm">
                    <div class="column-selector">
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="country" checked> Country
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="year" checked> Year
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="denomination" checked> Denom
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="title" checked> Title
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="grade" checked> Grade
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="status" checked> Status
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="price" checked> Price
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="note" checked> Note
                        </label>
                    </div>

                    <button type="submit" class="btn btn-accent" style="width: 100%; padding: 12px;">
                        &#11015; Download Custom CSV
                    </button>
                </form>
            </div>

            <div class="data-card">
                <h2 style="margin-top: 0; color: #28a745;">Import from CSV</h2>
                <p style="font-size: 0.9rem; margin-bottom: 15px;">Match your CSV file columns:</p>

                <form action="actions/import_csv_process.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="column-selector" id="importSelector">
                        <label class="chip locked" title="Mandatory field">
                            <input type="checkbox" checked disabled> Country
                            <input type="hidden" name="cols[]" value="country">
                        </label>
                        <label class="chip locked" title="Mandatory field">
                            <input type="checkbox" checked disabled> Year
                            <input type="hidden" name="cols[]" value="year">
                        </label>
                        <label class="chip locked" title="Mandatory field">
                            <input type="checkbox" checked disabled> Denom
                            <input type="hidden" name="cols[]" value="denomination">
                        </label>
                        <label class="chip locked" title="Mandatory field">
                            <input type="checkbox" checked disabled> Title
                            <input type="hidden" name="cols[]" value="title">
                        </label>

                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="grade" checked onchange="updatePreview()"> Grade
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="status" checked onchange="updatePreview()"> Status
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="price" checked onchange="updatePreview()"> Price
                        </label>
                        <label class="chip">
                            <input type="checkbox" name="cols[]" value="note" checked onchange="updatePreview()"> Note
                        </label>
                    </div>

                    <div class="form-group">
                        <input type="file" name="csv_file" accept=".csv" required style="margin-top: 10px; width: 100%; padding: 10px; background: #f9f9f9; border: 1px dashed #ccc;">
                    </div>
                    
                    <button type="submit" class="btn" style="background: #28a745; color: white; width: 100%; padding: 12px;">
                        &#11014; Upload CSV
                    </button>
                </form>
            </div>
        </div>

        <div class="card" style="margin-top: 40px; border-top: 4px solid var(--accent-color);">
            <h3 style="margin-top: 0; color: #444;">
                Expected CSV Format Preview
            </h3>
            <p style="color: #666; margin-bottom: 15px; font-size: 0.95rem;">
                Based on the tags selected in the <strong>Import</strong> section above, your CSV columns must follow this exact order:
            </p>

            <div class="preview-wrapper">
                <table class="preview-table">
                    <thead id="previewHead">
                        </thead>
                    <tbody id="previewBody">
                        </tbody>
                </table>
            </div>
            
            <p style="text-align: center; color: #888; font-size: 0.85rem; margin-top: 15px;">
                <span style="display:inline-block; width:10px; height:10px; background:#343a40; border-radius:50%; margin-right:5px;"></span> Dark fields are mandatory.
                <span style="display:inline-block; width:10px; height:10px; background:var(--accent-color); border-radius:50%; margin-left:10px; margin-right:5px;"></span> Colored fields are optional.
            </p>
        </div>

    </div>

    <script>
        const exampleData = {
            'country': 'Bulgaria',
            'year': '1951',
            'denomination': '1 Stotinka',
            'title': '1 Stotinka (1951)',
            'grade': 'UNC',
            'status': 'collection',
            'price': '5.50',
            'note': 'Gift from grandpa'
        };

        function updatePreview() {
            const headerRow = document.getElementById('previewHead');
            const bodyRow = document.getElementById('previewBody');
            
            const container = document.getElementById('importSelector');
            const allChips = container.querySelectorAll('.chip');
            
            let htmlHead = '<tr>';
            let htmlBody = '<tr>';
            
            allChips.forEach(chip => {
                const checkbox = chip.querySelector('input[type="checkbox"]');
                const hidden = chip.querySelector('input[type="hidden"]');
                
                if (checkbox && checkbox.checked) {
                    const val = hidden ? hidden.value : checkbox.value;
                    
                    const label = val.charAt(0).toUpperCase() + val.slice(1);
                    
                    htmlHead += `<th>${label}</th>`;
                    htmlBody += `<td>${exampleData[val] || '-'}</td>`;
                }
            });
            
            htmlHead += '</tr>';
            htmlBody += '</tr>';
            
            headerRow.innerHTML = htmlHead;
            bodyRow.innerHTML = htmlBody;
        }

        document.addEventListener('DOMContentLoaded', updatePreview);
    </script>
</body>

</html>