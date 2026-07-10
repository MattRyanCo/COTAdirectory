<?php
/**
 * Display the vestry db listing.
 *
 * @package COTAdirectory
 */
require_once __DIR__ . '/bootstrap.php';

// require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-vestry-listing.php';

// Echo page header
echo cota_page_header();

$vestry     = $cota_db->read_vestry_database();
$num_vestry = $vestry->num_rows;

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
		echo cota_format_vestry_listing_for_display( $vestry_individual );
	}
	echo "\n</table></body></html>";
}
// Close the file
$cota_db->close_connection();

/**
 * cota_format_vestry_listing_for_display
 *
 * @param mysqli_result $vestry_members - Result of database query of all vestry members
 * @return array $formatted_vestry_array - Array formatted for printing - 1 row per vestry member
	 */

function cota_format_vestry_listing_for_display( $vestry_member ) {
	$format_string = "<tr class='format_string'><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

	// Get first & last name of vestry member from the members table using member_id
	global $cota_db;
	$member_info = $cota_db->read_member_by_id_extended( $vestry_member['member_id'] );



	$formatted_vestry_member = sprintf(
		$format_string,
		$member_info['first_name'] . ' ' . $member_info['last_name'],
		$vestry_member['class'],
		ucwords($vestry_member['vrole']),
		ucwords($vestry_member['liaison'])
	);
	return $formatted_vestry_member; // Indicate success
}
