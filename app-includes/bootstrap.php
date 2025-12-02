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

// Require Member Authentication (if enabled)
require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-member-auth.php';
$cota_member_auth = new COTA_Member_Auth( $connect );
// Make auth object globally available
$GLOBALS['cota_member_auth'] = $cota_member_auth;

// Skip authentication check for login, logout, and password setup pages
$current_page = basename( $_SERVER['PHP_SELF'] );
$auth_exempt_pages = array( 'login.php', 'logout.php', 'setup-password.php', 'setup-member-auth-table.php' );

if ( ! in_array( $current_page, $auth_exempt_pages ) && COTA_Member_Auth::is_auth_enabled() ) {
	$cota_member_auth->require_authentication();
}

require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';
