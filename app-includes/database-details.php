<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get database functions instantiated. 
require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-database-functions.php';
$cota_db = new COTA_Database();
$connect = $cota_db->get_connection();

require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

// Echo header
echo cota_page_header();

$cota_db->show_connection_info();
$cota_db->show_structure();
// Close the file 
$cota_db->close_connection();
