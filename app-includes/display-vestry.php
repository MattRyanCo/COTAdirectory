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
	echo '<h3>' . $num_vestry . ' Vestry Members</h3>';
	echo '<table class="vestry-directory-table">';
		echo '<tr><th>Name</th><th><i>Class</i></th><th><i>Role</i></th><th><i>Area Liasion</i></th></tr>';

	while ( $ictr <= $num_vestry ) {
		// Get vestry details
		// $vestry_member = $vestry->fetch_assoc();
		// Get all vestry members
		$vestry_individuals = $cota_db->read_members_of_vestry();
		while ( $vestry_individual = $vestry_individuals->fetch_assoc() ) {
			echo cota_format_vestry_listing( $vestry_individual );
		}
			$individual = $vestry_member->fetch_assoc(); // Already have member. Parse row.


		if ( $individual ) {
			$formatted_vestry_array[ $member_ctr ][1] = ucwords($individual['first_name'] ?? '');
			$formatted_vestry_array[ $member_ctr ][2] = ucwords($individual['last_name'] ?? '');
			$formatted_vestry_array[ $member_ctr ][3] = $individual['class'] ?? '';
			$formatted_vestry_array[ $member_ctr ][4] = ucwords($individual['role'] ?? '');
			$formatted_vestry_array[ $member_ctr ][5] = ucwords($individual['area_liaison'] ?? '');
			$member_ctr++;

		}


		++$ictr;
	}
	echo "\n</table></body></html>";
}
// Close the file
$cota_db->close_connection();
