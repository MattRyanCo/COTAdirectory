<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
/**
 * Display the family listing.
 *
 * @package COTAdirectory
 */
require_once __DIR__ . '/bootstrap.php';

require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-family-listing.php';

// Echo page header
echo cota_page_header();

$families = $cota_db->read_family_database();
$num_families = $families->num_rows;
$ictr = 1;

if ( 0 == $num_families ) {
	empty_database_alert('Member Listing Display');
} else {
	// Dump out remainder of page. 
	echo '<div class="cota-display-container">';
	echo '<h3>Member Listing</h3>';
	echo '<h3>' . $num_families . ' Families</h3>';
	echo '<table class="directory-table">';
		echo '<tr><th>Family Name</th><th><i>Family Members</i></th></tr>';
		echo '<tr><th>Address<td><i>Name</i></td><td><i>Email</i></td><td><i>Cell</i></td><td><i>DoB</i></td><td><i>Baptism</i></td><td><i>Anniversary</i></td></th></tr>';

		while ($ictr < $num_families ) {
			// Get family details
			$family = $families->fetch_assoc();
			// Get all family members
			$individuals = $cota_db->read_members_of_family( $family['id'] );
			echo cota_format_family_listing_for_display($family, $individuals);	
			$ictr++;
		} 
	echo "\n</table></body></html>"; 
}
// Close the file 
$cota_db->close_connection();
