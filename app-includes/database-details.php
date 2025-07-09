<?php
require_once '../app-includes/database-functions.php';
require_once '../app-includes/settings.php';
// echo nl2br(__FILE__ . ' loaded' . PHP_EOL);

// Echo header
echo cota_page_header();

$db = new COTA_Database();
// $conn = $db->get_connection();

echo nl2br('<h2>CONSTANTS<br>DB_NAME ' . DB_NAME. PHP_EOL);
echo nl2br('DB_USER ' . DB_USER. PHP_EOL);
echo nl2br('DB_HOST ' . DB_HOST. PHP_EOL);
echo nl2br('DB_PASSWORD ' . DB_PASSWORD. PHP_EOL);

$db->show_connection_info();
$db->show_structure();
// Close the file 
$db->close_connection();
