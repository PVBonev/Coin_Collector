<?php
// index.php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

//tab collection data
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


//tab statistics data
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

//pie chart
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
        .pie-chart {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: conic-gradient(<?php echo $pie_gradient; ?>);
            position: relative;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .hidden { display: none !important; }
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
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Coins</div>
                    <div class="stat-number"><?php echo number_format($total_coins); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Est. Value</div>
                    <div class="stat-number text-green"><?php echo number_format($total_value, 2); ?> <small>€</small></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Spent</div>
                    <div class="stat-number"><?php echo number_format($total_spent, 2); ?> <small>€</small></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Profit / Loss</div>
                    <div class="stat-number <?php echo $profit_class; ?>">
                        <?php echo ($profit > 0 ? '+' : '') . number_format($profit, 2) . ' €'; ?>
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