<?php
require_once __DIR__ . '/bootstrap.php';

// Echo header
echo cota_page_header();

$cota_db->show_connection_info();
$cota_db->show_structure();
// Close the file
$cota_db->close_connection();
