<?php
/**
 * cota_format_vestry_listing
 *
 * @param mysqli_result $vestry_members - Result of database query of all vestry members
 * @return array $formatted_vestry_array - Array formatted for printing - 1 row per vestry member
	 */

function cota_format_vestry_listing( $vestry_member ) {

	// Initialize key variables
	// $num_vestry_members = $vestry_member->num_rows;
	$formatted_vestry_array = array(); // Initialize the formatted vestry array
	$formatted_vestry_array[0]['member_ctr']    = 0;

	$member_ctr = 1; // Initialize member counter for formatted array
	// while ( $member_ctr <= $num_vestry_members ) {
		// if ( 1 === $member_ctr ) {  // First time through
			$individual = $vestry_member->fetch_assoc(); // Already have member. Parse row.


		if ( $individual ) {
			$formatted_vestry_array[ $member_ctr ][1] = ucwords($individual['first_name'] ?? '');
			$formatted_vestry_array[ $member_ctr ][2] = ucwords($individual['last_name'] ?? '');
			$formatted_vestry_array[ $member_ctr ][3] = $individual['class'] ?? '';
			$formatted_vestry_array[ $member_ctr ][4] = ucwords($individual['role'] ?? '');
			$formatted_vestry_array[ $member_ctr ][5] = ucwords($individual['area_liaison'] ?? '');
			$member_ctr++;

		}
	// }
	return $formatted_vestry_array; // Indicate success
}

function get_next_vestry_member( $vestry_members ) {
	$individual_member = $vestry_members->fetch_assoc();
	if ( ! $individual_member ) {
		return false;
	}
	foreach ( array( 'first_name', 'last_name', 'class', 'role', 'liasion' ) as $key ) {
		if ( ! isset( $individual_member[ $key ] ) ) {
			$individual_member[ $key ] = '';
		}
	}
	return $individual_member;
}
