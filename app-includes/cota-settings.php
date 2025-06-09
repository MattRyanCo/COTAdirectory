<?php
/**
 * Used to set up and fix common variables and include
 * the app procedural and class library.
 *
 */

if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) ) {
    define( 'WP_ENVIRONMENT_TYPE', 'laragon' );
}
if ( 'laragon' !== WP_ENVIRONMENT_TYPE ) { // Define Gridpane settings
    define( 'DB_NAME', 'vDg_cotad_mattryan_co' );
    define( 'DB_USER', 'vDg_cotad_mattryan_co' );
    define( 'DB_PASSWORD', 'aYcqIqOyiJHXZxReYszNsphKU' );
    define( 'DB_HOST', 'localhost:/var/run/mysqld/mysqld.sock' );
} else {
    // Define Laragon (local) settings
    define( 'DB_NAME', 'cotadirectory' );
    define( 'DB_USER', 'root' );
    define( 'DB_PASSWORD', '' );
    define( 'DB_HOST', 'Localhost' );
}
/** Sets up the main directory class */
require_once ABSPATH . COTA_APPINC . '/cota-class-family-directory-app.php';

