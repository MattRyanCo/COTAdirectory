<?php
/**
 * format_leadership_listing
 *
 * @param mysqli_result $leadership_members - Result of database query of all leadership members
 * @return array $formatted_leadership_array - Array formatted for printing - 1 row per leadership member
	 */

function cota_format_leadership_listing( $leadership_member, $mode = 'display' ) {
	if ( 'display' === $mode ) {
		$format_string = "<tr class='format_string'><td>%s</td><td><i>%s</i></td></tr>";
	} elseif ( 'print' === $mode ) {
		$format_string = "%-20s%s\n";
	} else {
		die( 'Error: Invalid mode passed to cota_format_leadership_listing. Must be "display" or "print".' );
	}

	// Get first & last name of leadership member from the members table using member_id
	global $cota_db;
	$member_info = $cota_db->read_member_by_id_extended( $leadership_member['member_id'] );

	$formatted_leadership_member = sprintf(
		$format_string,
		$member_info['first_name'] . ' ' . $member_info['last_name'],
		trim( ucwords( $leadership_member['leadership_position'] ) )
	);
	return $formatted_leadership_member;
}

function cota_generate_leadership_listing_for_print() {
	global $cota_db;
	// Create the printed output for the leadership listing and return as
	// content string for cota_parse_intro_content.
	$leadership     = $cota_db->read_leadership_database();
	$num_leadership = $leadership->num_rows;
	$content_replace = '';
	$mode = 'print';

	// Print Out Table Headings for the Leadership Listing
	if ( 'print' === $mode ) {
		$content_replace .= sprintf(
			"%-20s%s\n",
			'Name',
			'Leadership Area'
			);
	}

	if ( 0 === $num_leadership ) {
		empty_database_alert( 'Leadership Listing Display' );
	} else {
		$leadership_individuals = $cota_db->read_members_of_leadership();
		while ( $leadership_individual = $leadership_individuals->fetch_assoc() ) {
			$content_replace .= sprintf( '%s', cota_format_leadership_listing( $leadership_individual, $mode ) );
		}
	}
	return $content_replace;
}