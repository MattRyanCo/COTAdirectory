<?php
/**
 * App Name:    Family Directory Management
 * Description: A simple family directory management system.
 * Version:     3.1.4
 * 
 * @package     FamilyDirectory
 * @author      Matt Ryan
 * Author URI:  https://github.com/MattRyanCo
 * @license     GPL-2.0-or-later
 * Github URL:  https://github.com/MattRyanCo/COTAdirectory
 * 
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** Initialize app settings. */
require_once __DIR__ . '/app-includes/class-app-settings.php';
$cota_app_settings = new App_Settings();


// Initialize database connection.
require_once $cota_app_settings->COTA_APP_INCLUDES . 'bootstrap.php';

/** Sets up the misc utility functions */
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';
($cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php');

// Set up meta data for app 
require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-app-meta-data.php';
$meta = new App_Meta_Data($cota_app_settings->COTA_APP_FILE);

// Echo page header using $meta
echo cota_page_header();

