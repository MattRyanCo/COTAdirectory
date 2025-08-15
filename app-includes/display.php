<?php

require_once __DIR__ . '/bootstrap.php';
global $cota_db, $connect,  $cota_constants;

// require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';

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
		echo '<tr><th>Family Name/Address</th><th><i>Family Members</i></th></tr>';
		echo '<tr><th>Home Phone<td><i>Name</i></td><td><i>Email</i></td><td><i>Cell</i></td><td><i>DoB</i></td><td><i>DoBaptism</i></td></th></tr>';

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
