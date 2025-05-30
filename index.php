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
 * Version:     1.1.1
 * 
 */

/** Absolute path to the app directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/**
 * Stores the location of the app directory of functions, classes, and core content.
 *
 * @since 1.0.0
 */

define( 'COTA_APPINC', 'app-includes' );
define( 'COTA_APPASSETS', 'app-assets' );
define( 'COTA_APPINCLUDES', 'app-includes' );

/** Sets up the app vars and included files. */
require_once COTA_APPINCLUDES . '/cota-settings.php';
require_once COTA_APPINCLUDES . '/cota-database-functions.php';

// phpinfo(); // For debugging purposes, remove in production
$app = new COTA_Family_Directory_App();
$app->cota_render();
