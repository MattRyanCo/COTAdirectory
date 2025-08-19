<?php
/**
 * App Name:    Family Directory Management
 * Description: A simple family directory management system.
 * Version:     3.1.2
 * 
 * @package     FamilyDirectory
 * @author      Matt Ryan
 * Author URI:  https://github.com/MattRyanCo
 * @license     GPL-2.0-or-later
 * Github URL:  https://github.com/MattRyanCo/COTAdirectory
 * 
 */

global $cota_app_settings, $meta;

/** Absolute path to the app's root directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'app-includes/class-app-settings.php';

/** Initialize app settings. */
$cota_app_settings = new App_Settings();

/** Sets up the app vars and included files. */
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-app-meta-data.php';

$meta = new App_Meta_Data($cota_app_settings->COTA_APP_FILE);

// Echo page header using $meta
echo cota_page_header();

