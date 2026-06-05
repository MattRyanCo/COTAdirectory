<?php
/**
 * cota_format_vestry_listing
 *
 * @param mysqli_result $vestry_members - Result of database query of all vestry members
 * @return array $formatted_vestry_array - Array formatted for printing - 1 row per vestry member
 */

function cota_format_vestry_listing( $vestry_member, $mode = 'display' ) {

	$placeholder = ' ';
	if ( 'display' === $mode ) {
		$format_string = "<tr class='format_string'><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
	} elseif ( 'print' === $mode ) {
		// $format_string = "%s%s%10' '%s%10' '%s\n";
		$format_string = "%-7s%-20s%-18s%s\n";
	} else {
		die( 'Error: Invalid mode passed to cota_format_vestry_listing. Must be "display" or "print".' );
	}

	// Get first & last name of vestry member from the members table using member_id
	global $cota_db;
	$member_info = $cota_db->read_member_by_id_extended( $vestry_member['member_id'] );

	$formatted_vestry_member = sprintf(
		$format_string,
		$vestry_member['class'] . ' - ',
		$member_info['first_name'] . ' ' . $member_info['last_name'],
		ucwords( '' === trim( $vestry_member['vrole'] ) ? $placeholder : $vestry_member['vrole'] ),
		ucwords($vestry_member['liaison'])
	);

	if ( 'display' === $mode ) {
		// Add blank line after each vestry member for readability
		$blank = "\r\n";
		$formatted_vestry_member .= sprintf(
			$format_string,
			nl2br( $blank )
		);
	}

	return $formatted_vestry_member;
}

function cota_generate_vestry_listing_for_print() {
	global $cota_db;
	// Create the printed output for the vestry listing and return as
	// content string for cota_parse_intro_content.
	$vestry     = $cota_db->read_vestry_database();
	$num_vestry = $vestry->num_rows;
	$content_replace = '';
	$mode = 'print';

	// Print Out Table Headings for the Vestry Listing
	if ( 'print' === $mode ) {
		// %-7s means left align Class within 7 character width, 
		// %-25s means left align Name within 25 character width,
		// %-15s means left align Role within 15 character width,
		// %s means Area Liaison with no specific width (will take remaining space).
		$content_replace .= sprintf(
			"%-7s%-20s%-18s%s\n",
			'Class',
			'Name',
			'Role',
			'Area Liaison'
			);
	}

	if ( 0 === $num_vestry ) {
		empty_database_alert( 'Vestry Listing Display' );
	} else {
		$vestry_individuals = $cota_db->read_members_of_vestry();
		while ( $vestry_individual = $vestry_individuals->fetch_assoc() ) {
			$content_replace .= sprintf( '%s', cota_format_vestry_listing( $vestry_individual, $mode ) );
		}
	}
	return $content_replace;
}
