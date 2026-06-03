<?php
/**
 * cota_format_vestry_listing
 *
 * @param mysqli_result $vestry_members - Result of database query of all vestry members
 * @return array $formatted_vestry_array - Array formatted for printing - 1 row per vestry member
	 */

function cota_format_vestry_listing( $vestry_member ) {
	$format_string = "<tr class='format_string'><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

	$index = $vestry_member['id'] ?? '';

	// Get first & last name of vestry member from the members table using member_id
	global $cota_db;
	$member_info = $cota_db->read_member_by_id( $vestry_member['member_id'] );
	// $formatted_vestry_array = array(); // Initialize the formatted vestry array
	// $formatted_vestry_array[$index][1] = $member_info['first_name'] . ' ' . $member_info['last_name'] ?? '';
	// $formatted_vestry_array[$index][2] = $vestry_member['class'] ?? '';
	// $formatted_vestry_array[$index][3] = ucwords($vestry_member['vrole'] ?? '');
	// $formatted_vestry_array[$index][4] = ucwords($vestry_member['liaison'] ?? '');

	$formatted_vestry_member = sprintf(
		$format_string,
		$member_info['first_name'] . ' ' . $member_info['last_name'],
		$vestry_member['class'],
		ucwords($vestry_member['vrole']),
		ucwords($vestry_member['liaison'])
	);
	return $formatted_vestry_member; // Indicate success
}

