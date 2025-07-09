<?php
require_once '../app-includes/database-functions.php';
require_once '../app-includes/format-family-listing.php';
require_once '../app-includes/settings.php';

// Echo page header
echo cota_page_header();

// Dump out remainder of page. 
echo '<div class="cota-display-container">';
echo '<h3>Member Listing</h3>';

$db = new COTA_Database();
// $conn = $db->get_connection();
		
$families = $db->read_family_database();
$num_families = $families->num_rows;
$ictr = 1;

echo '<h3>' . $num_families . ' Families</h3>';
echo '<table class="directory-table">';
	echo '<tr><th>Family Name/Address</th><th><i>Family Members</i></th></tr>';
	echo '<tr><th>Home Phone<td><i>Name</i></td><td><i>Email</i></td><td><i>Cell</i></td><td><i>DoB</i></td><td><i>DoBaptism</i></td></th></tr>';

	while ($ictr < $num_families ) {
		// Get family details
		$family = $families->fetch_assoc();
		// Get all family members
		// $individuals = $db->query("SELECT * FROM members WHERE family_id = " . $family['id'] . " ORDER BY `first_name`");
		// $individuals = $db->query("SELECT * FROM members WHERE family_id = " . $family['id']);  // no ordering
		$individuals = $db->read_members_of_family( $family['id'] );
		echo cota_format_family_listing_for_display($family, $individuals);	
		$ictr++;
	} 
echo "\n</table></body></html>"; 

// Close the file 
$db->close_connection();
