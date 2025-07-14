<?php
/**
 * Used to set up and fix common variables and include
 * the app procedural and class library.
 *
 */
require_once '../app-includes/helper-functions.php';

if ( !defined( 'ENVIRONMENT_TYPE' ) ) {
    define( 'ENVIRONMENT_TYPE', 'laragon' );
}
// if ( 'laragon' !== WP_ENVIRONMENT_TYPE ) { // Define Gridpane settings
    // define( 'DB_NAME', 'cotadirectory' );
    // define( 'DB_USER', 'cotadirectory' );
    // define( 'DB_PASSWORD', 'xo=BqIGmfJxc!+V7LNe97K9^V4p?86Lq' );
    // // define( 'DB_HOST', 'localhost:/var/run/mysqld/mysqld.sock' );
    // define( 'DB_HOST', '64.176.198.28:3306' );
// } else {
    // Define Laragon (local) settings
    // define( 'DB_NAME', 'cotadirectory' );
    // define( 'DB_USER', 'root' );
    // define( 'DB_PASSWORD', '' );
    // define( 'DB_HOST', 'Localhost' );
// }
class Constants {
    const MAX_FAMILY_MEMBERS = 10; // Maximum number of family members
}
