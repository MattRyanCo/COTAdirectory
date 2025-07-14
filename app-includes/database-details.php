<?php

global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

// Echo header
echo cota_page_header();

$cotadb->show_connection_info();
$cotadb->show_structure();
// Close the file 
$cotadb->close_connection();
