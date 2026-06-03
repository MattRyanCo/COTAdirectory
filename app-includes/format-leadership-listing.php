<?php
/**
 * cota_format_leadership_listing
 *
 * @param mysqli_result $leadership_members - Result of database query of all leadership members
 * @return array $formatted_leadership_array - Array formatted for printing - 1 row per leadership member
	 */

function cota_format_leadership_listing( $leadership_member ) {
	$format_string = "<tr class='format_string'><td>%s</td><td>%s</td></tr>";

	$index = $leadership_member['id'] ?? '';

	// Get first & last name of leadership member from the members table using member_id
	global $cota_db;
	$member_info = $cota_db->read_member_by_id( $leadership_member['member_id'] );

	$formatted_leadership_member = sprintf(
		$format_string,
		$member_info['first_name'] . ' ' . $member_info['last_name'],
		ucwords($leadership_member['leadership_position'])
	);
	return $formatted_leadership_member; // Indicate success
}

