<?php
/**
 * Family Directory Management
 *
 * @package   FamilyDirectory
 * @author    Matt Ryan
 * @license   GPL-2.0-or-later
 * @link      https://github.com/mattryanco/cotafamilydirecotry
 * 
 * Description: A simple family directory management system.
 * Version:     2.0.0
 * 
 */

/** Absolute path to the app directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
echo nl2br(ABSPATH . ' = ABSPATH' . PHP_EOL);

/**
 * Stores the location of the app directory of functions, classes, and core content.
 *
 * @since 1.0.0
 */

// define( 'COTA_APPINC', ABSPATH . 'app-includes/' );
define( 'COTA_APPASSETS', ABSPATH . 'app-assets/' );
define( 'COTA_APPINCLUDES', ABSPATH . 'app-includes/' );
echo nl2br(COTA_APPASSETS . ' = COTA_APPASSETS' . PHP_EOL);
echo nl2br(COTA_APPINCLUDES . ' = COTA_APPINCLUDES' . PHP_EOL);

/** Sets up the app vars and included files. */
require_once COTA_APPINCLUDES . 'database-functions.php';
require_once COTA_APPINCLUDES . 'settings.php';


// Echo page header
echo cota_page_header();