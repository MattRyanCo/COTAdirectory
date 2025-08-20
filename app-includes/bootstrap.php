<?php

/** Initialize app settings. */
require_once __DIR__ . '/class-app-settings.php';
$cota_app_settings = new App_Settings();

// Get database functions instantiated. 
require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-database-functions.php';
$cota_db = new COTA_Database();
$connect = $cota_db->get_connection();

require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';
