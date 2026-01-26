<?php
session_start();
require 'config/db.php';

$sql = "SELECT c.id, c.name, c.flag_image, c.continent, COUNT(cc.id) as coin_count 
        FROM countries c 
        LEFT JOIN catalog_coins cc ON c.id = cc.country_id 
        GROUP BY c.id 
        ORDER BY c.name ASC";

$stmt = $pdo->query($sql);
$countries = $stmt->fetchAll();

$continents = [];
foreach ($countries as $c) {
    if (!empty($c['continent']) && $c['continent'] !== 'Unknown') {
        $continents[$c['continent']] = true;
    }
}
ksort($continents);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Countries - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1 style="border-bottom: 2px solid var(--accent-color); display: inline-block; padding-bottom: 5px;">
            Countries
        </h1>
        <p style="margin-bottom: 20px;">Browse coins by country.</p>

        <div class="controls-bar">
            <div class="search-box">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="countrySearch" placeholder="Type a country name..." onkeyup="filterCountries()">
            </div>

            <div class="filter-box">
                <select id="continentFilter" onchange="filterCountries()">
                    <option value="all">All Continents</option>
                    <?php foreach (array_keys($continents) as $cont): ?>
                        <option value="<?php echo htmlspecialchars($cont); ?>"><?php echo htmlspecialchars($cont); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="coin-grid" id="countriesGrid">
            <?php foreach ($countries as $country): ?>

                <a href="country.php?id=<?php echo $country['id']; ?>"
                    class="country-item-link"
                    data-name="<?php echo strtolower($country['name']); ?>"
                    data-continent="<?php echo htmlspecialchars($country['continent']); ?>"
                    style="text-decoration: none; color: inherit;">

                    <div class="coin-card">
                        <div class="flag-wrapper">
                            <?php if ($country['flag_image']): ?>
                                <img src="<?php echo htmlspecialchars($country['flag_image']); ?>" alt="<?php echo htmlspecialchars($country['name']); ?>">
                            <?php else: ?>
                                <div style="width:100%; height:100%; background:#ccc; display:flex; align-items:center; justify-content:center; color:#666; font-size:0.8rem;">No Flag</div>
                            <?php endif; ?>
                        </div>

                        <div class="coin-info" style="text-align: center;">
                            <div class="coin-title">
                                <?php echo htmlspecialchars($country['name']); ?>
                            </div>

                            <div class="country-stats">
                                <span style="color: var(--accent-color); font-weight: bold;">
                                    <?php echo $country['coin_count']; ?>
                                </span> coin types
                            </div>
                            <div style="font-size: 0.8rem; color: #999; margin-top: 5px;">
                                <?php echo htmlspecialchars($country['continent']); ?>
                            </div>
                        </div>
                    </div>
                </a>

            <?php endforeach; ?>
        </div>

        <div id="noResults" style="text-align: center; display: none; margin-top: 50px; color: #777;">
            <h3>No countries found matching your criteria.</h3>
        </div>
    </div>

    <script>
        function filterCountries() {
            const searchInput = document.getElementById('countrySearch').value.toLowerCase();
            const continentFilter = document.getElementById('continentFilter').value;

            const items = document.querySelectorAll('.country-item-link');
            let visibleCount = 0;

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                const continent = item.getAttribute('data-continent');

                const matchesSearch = name.includes(searchInput);
                const matchesContinent = (continentFilter === 'all') || (continent === continentFilter);

                if (matchesSearch && matchesContinent) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            const noResMsg = document.getElementById('noResults');
            if (visibleCount === 0) {
                noResMsg.style.display = 'block';
            } else {
                noResMsg.style.display = 'none';
            }
        }
    </script>
    <?php include 'includes/footer.php'; ?>

</body>

</html>