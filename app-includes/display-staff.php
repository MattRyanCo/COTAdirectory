<?php
/**
 * Display the staff db listing. Staff does not need to be a member. 
 *
 * @package COTAdirectory
 */
require_once __DIR__ . '/bootstrap.php';

require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-staff-listing.php';

// Echo page header
echo cota_page_header();

$staff     = $cota_db->read_staff_database();
$num_staff = $staff->num_rows;

if ( 0 === $num_staff ) {
	empty_database_alert( 'Staff Listing Display' );
} else {
	// Dump out remainder of page.
	echo '<div class="cota-display-container">';
	echo '<h3>Staff Listing</h3>';
	echo '<table class="staff-directory-table">';
	// echo '<tr></tr>';

	$staff_individuals = $cota_db->read_members_of_staff();
	while ( $staff_individual = $staff_individuals->fetch_assoc() ) {
		echo cota_format_staff_listing( $staff_individual );
	}
	echo '</table></body></html>';
}
