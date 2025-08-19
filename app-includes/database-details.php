<?php
require_once __DIR__ . '/bootstrap.php';
global $cota_db, $connect,  $cota_app_settings;
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

// Echo header
echo cota_page_header();

$cota_db->show_connection_info();
$cota_db->show_structure();
// Close the file 
$cota_db->close_connection();
