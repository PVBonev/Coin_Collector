<?php
session_start();
require 'config/db.php';

$country_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

echo "<h1>Details for Country ID: $country_id</h1>";
echo "<p>Workin' on it</p>";
?>