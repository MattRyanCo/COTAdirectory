<?php
require_once __DIR__ . '/class-cota-constants.php';
$constants = new Constants();

require_once __DIR__ . '/class-app-meta-data.php';
$meta = new App_Meta_Data( $constants->$COTA_APP_FILE );

// Class constants (defined with const) are accessed as
// ClassName::CONSTANT_NAME.

// Instance properties (defined with public $property;)
// are accessed as $instance->property.

require_once $constants->COTA_APP_INCLUDES . 'class-database-functions.php';
$cota_db = new COTA_Database();
$connect = $cota_db->get_connection();

require_once $constants->COTA_APP_INCLUDES . 'helper-functions.php';
