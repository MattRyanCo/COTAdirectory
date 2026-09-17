<?php
/**
 * Print Instructions for Booklet
 *
 */

require_once __DIR__ . '/bootstrap.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-print-booklet.php';


// Echo header
echo cota_page_header();
// Echo Instructions. 
print_instructions( FALSE );