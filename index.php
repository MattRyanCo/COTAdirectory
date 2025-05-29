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

define( 'APPINC', 'app-includes' );
define( 'APPASSETS', 'app-assets' );
define( 'APPINCLUDES', 'app-includes' );

/** Sets up the app vars and included files. */
require_once APPINCLUDES . '/settings.php';
require_once APPINCLUDES . '/database_functions.php';

// phpinfo(); // For debugging purposes, remove in production
$app = new FamilyDirectoryApp();
$app->render();
