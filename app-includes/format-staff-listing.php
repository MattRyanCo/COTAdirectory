<?php
/**
 * cota_format_staff_listing
 *
 * @param mysqli_result $staff_members - Result of database query of all staff members
 * @return array $formatted_staff_array - Array formatted for printing - 1 row per staff member
	 */

function cota_format_staff_listing( $staff_member ) {

	$format_string = "<tr class='format_string'><td>%s</td></tr>";
	$format_string_no_title = "<tr class='format_string'><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
	$format_string_w_title  = "<tr class='format_string'><td>%s</td></tr>";
	$format_string_contact  = "<tr class='format_string'><td>%s</td></tr>";

	if ( ! empty( $staff_member['staff_title'] ) ) {
		$formatted_staff_member = sprintf(
			$format_string,
			$staff_member['staff_title'] . ' ' . $staff_member['staff_first_name'] . ' ' . $staff_member['staff_last_name'] . ', ' . $staff_member['position']
		);
	} else {
		$formatted_staff_member = sprintf(
			$format_string,
			$staff_member['staff_first_name'] . ' ' . $staff_member['staff_last_name'] . ', ' . $staff_member['position'],
		);
	}
	if ( ! empty( $staff_member['staff_phone'] ) ) {
		$formatted_staff_member .= sprintf(
			$format_string,
			$staff_member['staff_phone']
		);
	}
	if ( ! empty( $staff_member['staff_email'] ) ) {
		$formatted_staff_member .= sprintf(
			$format_string,
			$staff_member['staff_email']
		);
	}

	// Add blank line after each staff member for readability
	$blank = "\r\n";
	$formatted_staff_member .= sprintf(
		$format_string,
		nl2br($blank)
	);
	return $formatted_staff_member; // Indicate success
}

