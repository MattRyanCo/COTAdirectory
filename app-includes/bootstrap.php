<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Pull in library for .env handling
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

/** Initialize app settings. */
require_once __DIR__ . '/class-app-settings.php';
$cota_app_settings = new App_Settings();

// Get database functions instantiated. 
require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-database-functions.php';
$cota_db = new COTA_Database();
$connect = $cota_db->get_connection();

require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';
