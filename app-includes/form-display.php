<?php
// Show PHP errors on screen only for local development (Windows/Laragon).
// On the live server, log them instead: displayed warnings can end up inside
// form fields and be saved to the database.
$cota_show_errors = ( PHP_OS_FAMILY === 'Windows' ) ? '1' : '0';
ini_set( 'display_errors', $cota_show_errors );
ini_set( 'display_startup_errors', $cota_show_errors );
ini_set( 'log_errors', '1' );
error_reporting( E_ALL );
/**
 * Display the family listing.
 *
 * @package COTAdirectory
 */
require_once __DIR__ . '/bootstrap.php';

require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-family-listing.php';

// Echo page header
echo cota_page_header();

// Output the iframe for the AirTable form to add a family

echo '<a href="https://airtable.com/appDcjdkTREcNBq0C/pagyG4f67uqXrjND7/form">FORM:AIRTABLE Add Family</a><br><br>';


echo '<a href="https://airtable.com/appDcjdkTREcNBq0C/pagunMNCp8xV7EMYe/form">FORM:AIRTABLE Add Family Members</a><br><br>';

// $iframe = '<iframe class="airtable-embed" src="https://airtable.com/embed/appDcjdkTREcNBq0C/pagyG4f67uqXrjND7/form" frameborder="0" onmousewheel="" width="100%" height="533" style="background: transparent; border: 1px solid #ccc;"></iframe>';

// echo $iframe;

