<?php
/**
 * Database Dump Page
 *
 * Initializes application settings, establishes a database connection,
 * and then dumps the complete database to an sql file for archive.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** Initialize app settings. */
require_once __DIR__ . '/class-app-settings.php';
$cota_app_settings = new App_Settings();

// Get database functions instantiated. 
require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-database-functions.php';
$cota_db = new COTA_Database();
$connect = $cota_db->get_connection();

require_once $cota_app_settings->COTA_APP_INCLUDES . 'headers.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

// Echo header
echo cota_page_header();

$cota_db->dump_database();
// Close the file 
$cota_db->close_connection();
