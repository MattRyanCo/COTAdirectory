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

define( 'APPINC', 'app-includes' );
define( 'APPASSETS', 'app-assets' );
define( 'APPINCLUDES', 'app-includes' );

/** Sets up the app vars and included files. */
require_once ABSPATH . 'settings.php';

require_once APPINCLUDES . '/database_functions.php';


$app = new FamilyDirectoryApp();
$app->render();
