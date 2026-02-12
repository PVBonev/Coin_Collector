<?php
session_start();
require 'config/db.php';
require_once 'includes/metal_price_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sqlCoins = "SELECT 
            uc.id AS collection_id,
            uc.grade,
            uc.status,
            uc.is_locked,       
            uc.own_image_front,
            uc.added_at, 
            cc.title,
            cc.year,
            cc.denomination,
            cc.catalog_image_front, 
            c.name AS country_name,
            c.flag_image
        FROM user_coins uc
        JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
        JOIN countries c ON cc.country_id = c.id
        WHERE uc.user_id = ?
        ORDER BY uc.added_at DESC";
$stmt = $pdo->prepare($sqlCoins);
$stmt->execute([$user_id]);
$my_coins = $stmt->fetchAll();

$metalPrices = getMetalPrices();

$sqlMetals = "
    SELECT 
        m.symbol,
        SUM(cc.weight * (comp.percentage / 100)) as total_grams
    FROM user_coins uc
    JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
    JOIN coin_composition comp ON cc.id = comp.catalog_coin_id
    JOIN materials m ON comp.material_id = m.id
    WHERE uc.user_id = ? AND m.is_precious = 1
    GROUP BY m.symbol
";

$stmtM = $pdo->prepare($sqlMetals);
$stmtM->execute([$user_id]);
$userMetals = $stmtM->fetchAll(PDO::FETCH_KEY_PAIR); // returns array ['Au' => 12.5, 'Ag' => 50.2]

$meltValueTotal = 0;
$goldValue = 0;
$silverValue = 0;

// Gold (Au) -> API Symbol XAU
if (isset($userMetals['Au']) && isset($metalPrices['XAU'])) {
    $goldGrams = $userMetals['Au'];
    $goldPrice = $metalPrices['XAU']['price_gram'];
    $goldValue = $goldGrams * $goldPrice;
    $meltValueTotal += $goldValue;
}

// Silver (Ag) -> API Symbol XAG
if (isset($userMetals['Ag']) && isset($metalPrices['XAG'])) {
    $silverGrams = $userMetals['Ag'];
    $silverPrice = $metalPrices['XAG']['price_gram'];
    $silverValue = $silverGrams * $silverPrice;
    $meltValueTotal += $silverValue;
}

// Statistics 
$stmtKPI = $pdo->prepare("
    SELECT 
        COUNT(*) as total_coins,
        SUM(price) as total_value,
        SUM(purchase_price) as total_spent,
        COUNT(DISTINCT cc.country_id) as total_countries
    FROM user_coins uc
    JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
    WHERE uc.user_id = ?
");
$stmtKPI->execute([$user_id]);
$kpi = $stmtKPI->fetch();

$total_coins = $kpi['total_coins'] ?: 0;
$total_value = $kpi['total_value'] ?: 0;
$total_spent = $kpi['total_spent'] ?: 0;
$profit = $total_value - $total_spent;
$profit_class = $profit >= 0 ? 'text-green' : 'text-red';

// Pie chart status
$stmtStatus = $pdo->prepare("SELECT status, COUNT(*) as count FROM user_coins WHERE user_id = ? GROUP BY status");
$stmtStatus->execute([$user_id]);
$status_data = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR);

$status_colors = [
    'collection' => '#28a745',
    'swap'       => '#ffc107',
    'sell'       => '#dc3545',
    'wishlist'   => '#17a2b8'
];

$gradient_parts = [];
$current_percent = 0;
$chart_legend = [];

if ($total_coins > 0) {
    foreach ($status_data as $status => $count) {
        $percent = ($count / $total_coins) * 100;
        $end_percent = $current_percent + $percent;
        $color = $status_colors[$status] ?? '#ccc';
        $gradient_parts[] = "$color $current_percent% $end_percent%";
        $chart_legend[] = ['label' => ucfirst($status), 'count' => $count, 'percent' => round($percent, 1), 'color' => $color];
        $current_percent = $end_percent;
    }
    $pie_gradient = implode(', ', $gradient_parts);
} else {
    $pie_gradient = '#eee 0% 100%';
}

// Top Countries
$stmtCountries = $pdo->prepare("
    SELECT c.name, COUNT(*) as count, c.flag_image
    FROM user_coins uc
    JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
    JOIN countries c ON cc.country_id = c.id
    WHERE uc.user_id = ?
    GROUP BY c.id
    ORDER BY count DESC LIMIT 5
");
$stmtCountries->execute([$user_id]);
$top_countries = $stmtCountries->fetchAll();

// Top Periods
$stmtPeriods = $pdo->prepare("
    SELECT cc.period, COUNT(*) as count
    FROM user_coins uc
    JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
    WHERE uc.user_id = ?
    GROUP BY cc.period
    ORDER BY count DESC LIMIT 5
");
$stmtPeriods->execute([$user_id]);
$top_periods = $stmtPeriods->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* new dashboard layout... we dont bother changing the original styles in the styles.css */

        .dashboard-summary {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
        }

        .portfolio-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
        }

        .portfolio-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #eee;
        }

        .portfolio-label {
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .portfolio-value {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .melt-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border-top: 4px solid #d4af37;
            display: flex;
            flex-direction: column;
        }

        .melt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .melt-total {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .metal-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px dashed #eee;
        }

        .metal-row:last-child {
            border-bottom: none;
        }

        .metal-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-weight: bold;
            font-size: 0.8rem;
            margin-right: 10px;
        }

        .icon-gold {
            background: #fff3cd;
            color: #856404;
        }

        .icon-silver {
            background: #e2e3e5;
            color: #383d41;
        }

        .metal-info small {
            display: block;
            color: #888;
            font-size: 0.8rem;
        }

        @media (max-width: 900px) {
            .dashboard-summary {
                grid-template-columns: 1fr;
            }
        }

        .pie-chart {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: conic-gradient(<?php echo $pie_gradient; ?>);
            position: relative;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .hidden {
            display: none !important;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 10px;">
            <div>
                <h1 style="margin-bottom: 15px;">Dashboard</h1>
                <div class="tabs-header">
                    <button class="tab-btn active" onclick="switchTab('collection')">My Collection</button>
                    <button class="tab-btn" onclick="switchTab('stats')">Statistics</button>
                </div>
            </div>

            <div id="collection-actions" style="margin-bottom: 15px;">
                <a href="data_management.php" class="btn" style="background: #6c757d; color: white; font-size: 0.9rem; margin-right: 10px;">
                    &#128193; Import/Export
                </a>
                <a href="add_coin.php" class="btn btn-accent">
                    + Add New Coin
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div id="tab-collection" class="tab-content active">

            <div class="controls-bar" style="margin-bottom: 20px;">
                <div class="search-box">
                    <span class="search-icon">&#128269;</span>
                    <input type="text" id="collectionSearch" placeholder="Search by title, country or year..." onkeyup="filterCollection()">
                </div>

                <div class="filter-box">
                    <select id="collectionSort" onchange="sortCollection()">
                        <option value="added_desc">Date Added (Newest -> Oldest)</option>
                        <option value="added_asc">Date Added (Oldest -> Newest)</option>
                        <option value="year_desc">Year (Newest -> Oldest)</option>
                        <option value="year_asc">Year (Oldest -> Newest)</option>
                        <option value="title_asc">Name (A-Z)</option>
                        <option value="title_desc">Name (Z-A)</option>
                    </select>
                </div>
            </div>

            <p style="margin-bottom: 15px;">You have <strong id="visibleCount"><?php echo count($my_coins); ?></strong> coins in your collection.</p>

            <?php if (count($my_coins) > 0): ?>
                <div class="collection-grid" id="collectionGrid">
                    <?php foreach ($my_coins as $coin): ?>

                        <div class="coin-card <?php echo $coin['is_locked'] ? 'locked' : ''; ?>"
                            data-title="<?php echo strtolower(htmlspecialchars($coin['title'])); ?>"
                            data-country="<?php echo strtolower(htmlspecialchars($coin['country_name'])); ?>"
                            data-year="<?php echo $coin['year']; ?>"
                            data-added="<?php echo strtotime($coin['added_at']); ?>">

                            <?php if ($coin['is_locked']): ?>
                                <div class="lock-overlay">&#128274;</div>
                            <?php endif; ?>

                            <a href="user_coin_details.php?id=<?php echo $coin['collection_id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                                <div class="coin-img-box">
                                    <?php
                                    $displayImage = 'assets/images/no-coin.png';
                                    if (!empty($coin['own_image_front'])) {
                                        $displayImage = $coin['own_image_front'];
                                    } elseif (!empty($coin['catalog_image_front'])) {
                                        $displayImage = $coin['catalog_image_front'];
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($displayImage); ?>" alt="Coin Image">
                                </div>
                            </a>

                            <div class="coin-details">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <a href="user_coin_details.php?id=<?php echo $coin['collection_id']; ?>" style="text-decoration: none; color: inherit;">
                                        <h3 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($coin['title']); ?></h3>
                                    </a>

                                    <?php if ($coin['flag_image']): ?>
                                        <img src="<?php echo htmlspecialchars($coin['flag_image']); ?>" style="width: 25px; border: 1px solid #eee;">
                                    <?php endif; ?>
                                </div>

                                <p style="color: #666; font-size: 0.9rem; margin: 5px 0;">
                                    <?php echo htmlspecialchars($coin['country_name']); ?> • <?php echo $coin['year']; ?>
                                </p>

                                <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: bold; font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($coin['denomination']); ?>
                                    </span>

                                    <div>
                                        <?php if ($coin['status'] !== 'collection'): ?>
                                            <span class="badge status-badge"><?php echo ucfirst($coin['status']); ?></span>
                                        <?php endif; ?>

                                        <?php
                                        $g = trim($coin['grade']);
                                        $gradeClass = 'grade-default';
                                        if ($g == 'UNC') $gradeClass = 'grade-unc';
                                        if ($g == 'AU')  $gradeClass = 'grade-au';
                                        if ($g == 'XF')  $gradeClass = 'grade-xf';
                                        if ($g == 'VF')  $gradeClass = 'grade-vf';
                                        if ($g == 'F')   $gradeClass = 'grade-f';
                                        ?>

                                        <a href="grading_guide.php" title="See Grading Guide" style="text-decoration: none;">
                                            <span class="badge <?php echo $gradeClass; ?>" style="cursor: help;">
                                                <?php echo htmlspecialchars($coin['grade']); ?>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>

                <div id="noResults" style="text-align: center; display: none; padding: 40px; color: #777;">
                    <h3>No coins found.</h3>
                </div>

            <?php else: ?>
                <div class="card" style="text-align: center; padding: 40px;">
                    <h3>Your collection is empty!</h3>
                    <p>Start by adding your first coin.</p>
                </div>
            <?php endif; ?>
        </div>

        <div id="tab-stats" class="tab-content">

            <div class="dashboard-summary">

                <div class="portfolio-card">
                    <h3 style="margin: 0; color: #333;">Portfolio Overview</h3>
                    <div class="portfolio-grid">
                        <div class="portfolio-item">
                            <div class="portfolio-label">Total Coins</div>
                            <div class="portfolio-value"><?php echo number_format($total_coins); ?></div>
                        </div>

                        <div class="portfolio-item">
                            <div class="portfolio-label">Est. Value</div>
                            <div class="portfolio-value text-green">
                                <?php echo number_format($total_value, 2); ?> <small>€</small>
                            </div>
                        </div>

                        <div class="portfolio-item">
                            <div class="portfolio-label">Total Spent</div>
                            <div class="portfolio-value">
                                <?php echo number_format($total_spent, 2); ?> <small>€</small>
                            </div>
                        </div>

                        <div class="portfolio-item">
                            <div class="portfolio-label">Profit / Loss</div>
                            <div class="portfolio-value <?php echo $profit_class; ?>">
                                <?php echo ($profit > 0 ? '+' : '') . number_format($profit, 2) . ' €'; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="melt-card">
                    <div class="melt-header">
                        <div>
                            <h3 style="margin: 0; color: #d4af37;">Melt Value</h3>
                            <span style="font-size: 0.85rem; color: #777;">Scrap Material Price</span>
                        </div>
                        <div class="melt-total">
                            <?php echo number_format($meltValueTotal, 2); ?> <small style="font-size: 1rem; color: #777;">€</small>
                        </div>
                    </div>

                    <div style="flex: 1;">
                        <?php if ($goldValue > 0): ?>
                            <div class="metal-row">
                                <div style="display: flex; align-items: center;">
                                    <span class="metal-icon icon-gold">Au</span>
                                    <div class="metal-info">
                                        <strong>Gold</strong> (<?php echo number_format($userMetals['Au'], 2); ?>g)
                                        <small>@ <?php echo number_format($goldPrice, 2); ?> €/g</small>
                                    </div>
                                </div>
                                <div style="font-weight: bold; font-size: 1.1rem;">
                                    <?php echo number_format($goldValue, 2); ?> €
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($silverValue > 0): ?>
                            <div class="metal-row">
                                <div style="display: flex; align-items: center;">
                                    <span class="metal-icon icon-silver">Ag</span>
                                    <div class="metal-info">
                                        <strong>Silver</strong> (<?php echo number_format($userMetals['Ag'], 2); ?>g)
                                        <small>@ <?php echo number_format($silverPrice, 2); ?> €/g</small>
                                    </div>
                                </div>
                                <div style="font-weight: bold; font-size: 1.1rem;">
                                    <?php echo number_format($silverValue, 2); ?> €
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($goldValue == 0 && $silverValue == 0): ?>
                            <p style="text-align: center; color: #999; margin-top: 20px;">
                                No precious metals found in your collection.
                            </p>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 15px; text-align: right; font-size: 0.75rem; color: #aaa;">
                        <span style="display: inline-block; width: 8px; height: 8px; background: #28a745; border-radius: 50%; margin-right: 5px;"></span>
                        Live prices updated: <?php echo isset($metalPrices['XAU']['updated_at']) ? date('H:i', $metalPrices['XAU']['updated_at']) : 'N/A'; ?>
                    </div>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-container">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Collection Status</h3>
                    <?php if ($total_coins > 0): ?>
                        <div class="pie-wrapper">
                            <div class="pie-chart"></div>
                            <ul class="legend">
                                <?php foreach ($chart_legend as $item): ?>
                                    <li>
                                        <span class="legend-color" style="background: <?php echo $item['color']; ?>"></span>
                                        <strong><?php echo $item['label']; ?></strong>:
                                        <?php echo $item['count']; ?> (<?php echo $item['percent']; ?>%)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #999;">No data.</p>
                    <?php endif; ?>
                </div>

                <div class="chart-container">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Top Countries</h3>
                    <?php if (count($top_countries) > 0): ?>
                        <?php $max_count = $top_countries[0]['count']; ?>
                        <?php foreach ($top_countries as $country): ?>
                            <?php $width = ($country['count'] / $max_count) * 100; ?>
                            <div class="bar-row">
                                <div class="bar-label">
                                    <?php if ($country['flag_image']): ?>
                                        <img src="<?php echo htmlspecialchars($country['flag_image']); ?>" width="20" style="border-radius: 2px;">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($country['name']); ?>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: <?php echo $width; ?>%;"></div>
                                </div>
                                <div class="bar-value"><?php echo $country['count']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #999;">No data.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="chart-container">
                <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Most Common Periods</h3>
                <?php if (count($top_periods) > 0): ?>
                    <?php $max_p_count = $top_periods[0]['count']; ?>
                    <?php foreach ($top_periods as $p): ?>
                        <?php $width = ($p['count'] / $max_p_count) * 100; ?>
                        <div class="bar-row">
                            <div class="bar-label" style="width: 250px;">
                                <?php echo htmlspecialchars($p['period'] ? $p['period'] : 'Unknown'); ?>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: <?php echo $width; ?>%; background: #6c757d;"></div>
                            </div>
                            <div class="bar-value"><?php echo $p['count']; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #999;">No data.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

            if (tabName === 'collection') {
                document.getElementById('tab-collection').classList.add('active');
                document.getElementById('collection-actions').style.display = 'block';
            } else {
                document.getElementById('tab-stats').classList.add('active');
                document.getElementById('collection-actions').style.display = 'none';
            }

            const buttons = document.querySelectorAll('.tab-btn');
            if (tabName === 'collection') buttons[0].classList.add('active');
            else buttons[1].classList.add('active');
        }


        function filterCollection() {
            const input = document.getElementById('collectionSearch').value.toLowerCase();
            const cards = document.querySelectorAll('.coin-card');
            let visible = 0;

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const country = card.getAttribute('data-country');
                const year = card.getAttribute('data-year');

                if (title.includes(input) || country.includes(input) || year.includes(input)) {
                    card.classList.remove('hidden');
                    visible++;
                } else {
                    card.classList.add('hidden');
                }
            });

            document.getElementById('visibleCount').innerText = visible;
            document.getElementById('noResults').style.display = (visible === 0) ? 'block' : 'none';
        }

        function sortCollection() {
            const sortType = document.getElementById('collectionSort').value;
            const grid = document.getElementById('collectionGrid');
            const cards = Array.from(grid.getElementsByClassName('coin-card'));

            cards.sort((a, b) => {
                const titleA = a.getAttribute('data-title');
                const titleB = b.getAttribute('data-title');
                const yearA = parseInt(a.getAttribute('data-year'));
                const yearB = parseInt(b.getAttribute('data-year'));
                const addedA = parseInt(a.getAttribute('data-added'));
                const addedB = parseInt(b.getAttribute('data-added'));

                if (sortType === 'title_asc') return titleA.localeCompare(titleB);
                if (sortType === 'title_desc') return titleB.localeCompare(titleA);
                if (sortType === 'year_desc') return yearB - yearA;
                if (sortType === 'year_asc') return yearA - yearB;
                if (sortType === 'added_asc') return addedA - addedB;
                if (sortType === 'added_desc') return addedB - addedA; // default
            });

            grid.innerHTML = "";
            cards.forEach(card => grid.appendChild(card));
        }
    </script>
    <?php include 'includes/footer.php'; ?>
</body>

</html>