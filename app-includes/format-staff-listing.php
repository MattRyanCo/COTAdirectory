<?php
/**
 * cota_format_staff_listing
 *
 * @param mysqli_result $staff_members - Result of database query of all staff members
 * @return array $formatted_staff_array - Array formatted for printing - 1 row per staff member
	 */

function cota_format_staff_listing( $staff_member, $mode = 'display' ) {

	if ( 'display' === $mode ) {
		$format_string       = "<tr class='format_string'><td>%s</td></tr>";
		$format_string_email = "<tr class='format_string'><td>%s</td></tr>";
		$format_string_no_title = "<tr class='format_string'><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
		$format_string_w_title  = "<tr class='format_string'><td>%s</td></tr>";
		$format_string_contact  = "<tr class='format_string'><td>%s</td></tr>";
	} elseif ( 'print' === $mode ) {
		$format_string = "%s\n";
		$format_string_email = "%s\n\n";
		$format_string_no_title = "%s, %s, %s, %s, %s\n";
		$format_string_w_title  = "%s\n";
		$format_string_contact  = "%s\n";
	} else {
		die( 'Error: Invalid mode passed to cota_format_staff_listing. Must be "display" or "print".' );
	}

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
			$format_string_email,
			$staff_member['staff_email']
		);
	}

	if ( 'display' === $mode ) {
		// Add blank line after each staff member for readability
		$blank = "\r\n";
		$formatted_staff_member .= sprintf(
			$format_string,
			nl2br( $blank )
		);
	}
	return $formatted_staff_member;
}

function cota_generate_staff_listing_for_print() {
	global $cota_db;
	// Create the printed output for the staff listing and return as
	// content string for cota_parse_intro_content.
	$staff     = $cota_db->read_staff_database();
	$num_staff = $staff->num_rows;
	$content_replace = '';
	$mode = 'print';

	if ( 0 === $num_staff ) {
		empty_database_alert( 'Staff Listing Display' );
	} else {
		$staff_individuals = $cota_db->read_members_of_staff();
		while ( $staff_individual = $staff_individuals->fetch_assoc() ) {
			$content_replace .= sprintf( '%s', cota_format_staff_listing( $staff_individual, $mode ) );
		}
	}
	return $content_replace;
}