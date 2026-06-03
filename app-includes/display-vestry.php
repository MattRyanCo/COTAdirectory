<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
/**
 * Display the vestry db listing.
 *
 * @package COTAdirectory
 */
require_once __DIR__ . '/bootstrap.php';

require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-vestry-listing.php';

// Echo page header
echo cota_page_header();

$vestry     = $cota_db->read_vestry_database();
$num_vestry = $vestry->num_rows;
$ictr         = 1;

if ( 0 === $num_vestry ) {
	empty_database_alert( 'Vestry Listing Display' );
} else {
	// Dump out remainder of page.
	echo '<div class="cota-display-container">';
	echo '<h3>Vestry Listing</h3>';
	// echo '<h3>' . $num_vestry . ' Vestry Members</h3>';
	echo '<table class="vestry-directory-table">';
	echo '<tr><th>Name</th><th><i>Class</i></th><th><i>Role</i></th><th><i>Area Liaison</i></th></tr>';

	$vestry_individuals = $cota_db->read_members_of_vestry();
	while ( $vestry_individual = $vestry_individuals->fetch_assoc() ) {
		echo cota_format_vestry_listing( $vestry_individual );
	}
	echo "\n</table></body></html>";
}
// Close the file
$cota_db->close_connection();
