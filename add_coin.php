<?php
session_start();
require 'config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
$countries = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Coin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Layout */
        .add-coin-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            align-items: start;
        }

        /* Sidebar */
        .sidebar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 20px;
        }

        /* coin grid*/
        .coin-selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        /* man coin card */
        .coin-option-card {
            background: white;
            border: 2px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            overflow: hidden;
            position: relative;
        }

        .coin-option-card:hover {
            transform: translateY(-3px);
            border-color: #ccc;
        }

        .coin-option-card.selected {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.2);
        }

        .coin-option-img {
            height: 150px;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .coin-option-img img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .coin-option-info {
            padding: 10px;
            text-align: center;
        }

        .coin-option-denom {
            font-weight: bold;
            font-size: 1.1rem;
            color: #333;
        }

        .coin-option-title {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        /* form for details */
        #details-form-container {
            display: none;
            margin-top: 30px;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid var(--accent-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            background: #fff;
            border-radius: 8px;
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .add-coin-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1 style="margin-bottom: 20px;">Add Coin to Collection</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="add-coin-layout">

            <aside class="sidebar">
                <h3 style="margin-top: 0;">1. Select Filters</h3>

                <div class="form-group">
                    <label>Country</label>
                    <select id="country-select" style="width: 100%;">
                        <option value="">-- Select --</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country['id']; ?>"><?php echo htmlspecialchars($country['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Year</label>
                    <select id="year-select" style="width: 100%;" disabled>
                        <option value="">-- Select Country First --</option>
                    </select>
                </div>

                <p style="font-size: 0.9rem; color: #666; line-height: 1.5;">
                    Select the country and year to see available coin types.
                </p>
            </aside>

            <main>
                <h3 id="result-title" style="margin-top: 0;">2. Choose Coin Type</h3>

                <div id="coin-list" class="coin-selection-grid">
                    <div style="grid-column: 1/-1; color: #777; font-style: italic;">
                        Please select filters...
                    </div>
                </div>

                <div id="details-form-container">
                    <h3 style="margin-top: 0;">3. Your Coin Details</h3>
                    <p id="selected-coin-name" style="color: var(--accent-color); font-weight: bold; margin-bottom: 15px;"></p>

                    <form action="actions/add_coin_process.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="catalog_coin_id" id="selected-catalog-id">
                        <input type="hidden" name="country_id" id="hidden-country-id">
                        <input type="hidden" name="is_manual" value="0">

                        <div style="display: flex; gap: 15px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Condition</label>
                                <select name="grade">
                                    <option value="UNC">Uncirculated (UNC)</option>
                                    <option value="XF">Extremely Fine (XF)</option>
                                    <option value="VF">Very Fine (VF)</option>
                                    <option value="F">Fine (F)</option>
                                    <option value="Good">Good</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Status</label>
                                <select name="status">
                                    <option value="collection">In Collection</option>
                                    <option value="swap">For Swap</option>
                                    <option value="sell">For Sale</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: flex; gap: 15px;">
                            <div class="form-group" style="flex: 1;">
                                <label>My Photo (Front)</label>
                                <input type="file" name="img_front" accept="image/*">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>My Photo (Back)</label>
                                <input type="file" name="img_back" accept="image/*">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-accent" style="width: 100%;">Add to Collection</button>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        const countrySelect = document.getElementById('country-select');
        const yearSelect = document.getElementById('year-select');
        const coinList = document.getElementById('coin-list');
        const detailsForm = document.getElementById('details-form-container');
        const hiddenCatalogId = document.getElementById('selected-catalog-id');
        const hiddenCountryId = document.getElementById('hidden-country-id');
        const selectedCoinName = document.getElementById('selected-coin-name');

        //inb case we want to preselect a country from URL param
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            hiddenCountryId.value = countryId;
            
            //reset UI
            detailsForm.style.display = 'none';
            yearSelect.innerHTML = '<option value="">Loading...</option>';
            yearSelect.disabled = true;
            
            if (countryId) {
                //load years for that country
                fetch(`api/get_catalog_data.php?type=years&country_id=${countryId}`)
                    .then(res => res.json())
                    .then(data => {
                        yearSelect.innerHTML = '<option value="">-- All Years --</option>'; // Променихме текста
                        if (data.length > 0) {
                            data.forEach(y => yearSelect.innerHTML += `<option value="${y}">${y}</option>`);
                            yearSelect.disabled = false;
                        }
                    });

                //immediately load coins for all years
                loadCoins(countryId, null); 

            } else {
                yearSelect.innerHTML = '<option value="">-- Select Country First --</option>';
                coinList.innerHTML = '<div style="grid-column: 1/-1; color: #777;">Please select a country...</div>';
            }
        });

        // --- 2. ПРИ ИЗБОР НА ГОДИНА ---
        yearSelect.addEventListener('change', function() {
            const year = this.value; // Може да е празно (All Years)
            const countryId = countrySelect.value;
            loadCoins(countryId, year);
        });

        // --- ПОМОЩНА ФУНКЦИЯ ЗА ЗАРЕЖДАНЕ ---
        function loadCoins(countryId, year) {
            coinList.innerHTML = 'Loading coins...';
            detailsForm.style.display = 'none';

            let url = `api/get_catalog_data.php?type=coins&country_id=${countryId}`;
            if (year) {
                url += `&year=${year}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    coinList.innerHTML = ''; // Изчистваме

                    // 1. Рендерираме монетите
                    if (data.length > 0) {
                        data.forEach(coin => {
                            const imgUrl = coin.catalog_image_front ? coin.catalog_image_front : 'assets/images/no-coin.png';
                            
                            const card = document.createElement('div');
                            card.className = 'coin-option-card';
                            card.innerHTML = `
                                <div class="coin-option-img">
                                    <img src="${imgUrl}" alt="${coin.title}">
                                </div>
                                <div class="coin-option-info">
                                    <div class="coin-option-denom">${coin.denomination}</div>
                                    <div class="coin-option-title">${coin.title}</div>
                                </div>
                            `;
                            card.addEventListener('click', () => selectCoin(card, coin));
                            coinList.appendChild(card);
                        });
                    }

                    // 2. ВИНАГИ добавяме картата "Request New Coin"
                    const addCard = document.createElement('div');
                    addCard.className = 'coin-option-card add-new-card';
                    addCard.innerHTML = `
                        <div class="add-icon">+</div>
                        <div style="font-weight:bold;">Not in list?</div>
                        <div style="font-size:0.85rem; color:#666;">Request New Coin</div>
                    `;
                    addCard.addEventListener('click', () => {
                        window.location.href = `create_catalog_coin.php?country_id=${countryId}`;
                    });
                    coinList.appendChild(addCard);
                });
        }

        function selectCoin(cardElement, coinData) {
            document.querySelectorAll('.coin-option-card').forEach(c => c.classList.remove('selected'));
            cardElement.classList.add('selected');
            
            hiddenCatalogId.value = coinData.id;
            selectedCoinName.innerText = `Selected: ${coinData.denomination} - ${coinData.title}`;
            
            detailsForm.style.display = 'block';
            detailsForm.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>

</html>