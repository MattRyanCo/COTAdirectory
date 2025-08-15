<?php
/**
 * App Name:    Family Directory Management
 * Description: A simple family directory management system.
 * Version:     2.0.7 
 * 
 * @package     FamilyDirectory
 * @author      Matt Ryan
 * Author URI:  https://github.com/MattRyanCo
 * @license     GPL-2.0-or-later
 * Github URL:  https://github.com/MattRyanCo/COTAdirectory
 * 
 */

global $cota_constants, $meta;

/** Absolute path to the app directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

class Constants {
    const MAX_FAMILY_MEMBERS = 10; // Maximum number of family members
    const ENVIRONMENT_TYPE = 'laragon';
    const ABSPATH = __DIR__ . '/';
    const COTA_APP_FILE = ABSPATH . 'index.php';
    const COTA_APP_ASSETS = ABSPATH . 'app-assets/';
    const COTA_APP_INCLUDES = ABSPATH . 'app-includes/';
	const COTA_APP_LIBRARIES = ABSPATH . 'app-libraries/';
    const FAMILY_LISTING_FONT = 8;
    const FAMILY_LISTING_FONT_SMALL = 7;
    const FAMILY_LISTING_LINE_HEIGHT = .15;
    const FAMILY_LISTING_LINE_HEIGHT_TALL = .25;
    const FAMILY_HEADING_FONT = 10;
    const DIRECTORY_HEADING_FONT = 12;
    const DIRECTORY_HEADING_FONT_SMALL = 10;
}

$cota_constants = new Constants();



/**
 * Stores the location of the app directory of functions, classes, and core content.
 *
 */

/** Sets up the app vars and included files. */
require_once $cota_constants::COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants::COTA_APP_INCLUDES . 'class-app-meta-data.php';

$meta = new AppMetadata($cota_constants::COTA_APP_FILE);

// echo "App Version: " . $meta->getVersion() . "\n";
// echo "GitHub URL: " . $meta->getGitHubUrl() . "\n";

// Echo page header
echo cota_page_header();

