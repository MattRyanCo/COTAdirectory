<?php
require_once __DIR__ . '/class-cota-constants.php';
$constants = new Constants();

// $COTA_APP_FILE      = dirname(__DIR__) . '/index.php';
// $COTA_APP_ASSETS    = dirname(__DIR__) . '/app-assets/';
// $COTA_APP_INCLUDES  = dirname(__DIR__) . '/app-includes/';
// $COTA_APP_LIBRARIES = dirname(__DIR__) . '/app-libraries/';
$COTA_APP           = dirname( __DIR__ );
$COTA_APP_FILE      = $COTA_APP . '/index.php';
$COTA_APP_ASSETS    = $COTA_APP . '/app-assets/';
$COTA_APP_INCLUDES  = $COTA_APP . '/app-includes/';
$COTA_APP_LIBRARIES = $COTA_APP . '/app-libraries/';
$UPLOAD_DIR         = '../uploads/'; // Directory for uploaded files

// var_dump($COTA_APP);
// var_dump($COTA_APP_FILE);
// var_dump($COTA_APP_INCLUDES);
// var_dump($UPLOAD_DIR);

require_once __DIR__ . '/class-app-meta-data.php';
$meta = new AppMetaData( $COTA_APP_FILE );

require_once $COTA_APP_INCLUDES . 'class-database-functions.php';
$cota_db = new COTA_Database();
$connect = $cota_db->get_connection();

require_once $constants->COTA_APP_INCLUDES . 'helper-functions.php';
