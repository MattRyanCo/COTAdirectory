<?php
/**
 * Logout Page
 * 
 * Logs out the current user and redirects to login page.
 */

ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );

// Load bootstrap (but skip authentication check)
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

require_once __DIR__ . '/class-app-settings.php';
$cota_app_settings = new App_Settings();

require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-database-functions.php';
$cota_db = new COTA_Database();
$connect = $cota_db->get_connection();

require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-member-auth.php';

$auth = new COTA_Member_Auth( $connect );
$auth->logout();

// Redirect to login page
header( 'Location: /app-includes/login.php?loggedout=1' );
exit;

