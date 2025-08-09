<?php
var_dump( __DIR__ . '/class-cota-constants.php' );
require_once __DIR__ . '/class-cota-constants.php';
$constants = new Constants();
var_dump( $constants::COTA_APP_INCLUDES );
var_dump( $constants::COTA_APP_ASSETS );
var_dump( $constants::COTA_APP_LIBRARIES );
var_dump( $constants::MAX_INFORMATIONAL_DOCS );
var_dump( $constants::UPLOAD_DIR );
var_dump( $constants->COTA_APP_FILE );

var_dump( __DIR__ . '/class-app-meta-data.php' );
require_once __DIR__ . '/class-app-meta-data.php';
$meta = new App_Meta_Data( $constants->COTA_APP_FILE );

// Class constants (defined with const) are accessed as
// ClassName::CONSTANT_NAME.

// Instance properties (defined with public $property;)
// are accessed as $instance->property.

require_once $constants->COTA_APP_INCLUDES . 'class-database-functions.php';
$cota_db = new COTA_Database();
$connect = $cota_db->get_connection();

require_once $constants->COTA_APP_INCLUDES . 'helper-functions.php';
