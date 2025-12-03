<?php
/**
 * cota_format_family_listing_for_print
 *
 * @param array $family - Result of database query of all family data for a single family ID
 * @param mysqli_result $members - Result of database query of all members associated with a single family ID
 * @return array $formatted_family_array - Array formatted for printing - 1 row per member with address info on left and name on right
 * [0][0] - Number of left side lines
 * [0][1] - Number of family members
 * [x][1]                              [x][4]               [x][5]    [x][6]  [x][7]       [x][8] [x][9]
 * [x][1]                              [x][2]               [x][3]    [x][4]  [x][5]       [x][6] [x][7]
 * [1]Family_Name                      Member 1 First Name  Last Name Email   Cell Phone   DoB    DoBaptism Anniversary
 * [2]Address Line 1, Address Line 2   Member 2 First Name  Last Name Email   Cell Phone   DoB    DoBaptism Anniversary
 * [3]City, State Zip                  Member 3 First Name  Last Name Email   Cell Phone   DoB    DoBaptism Anniversary
 * [4]Home Phone: (xxx) xxx-xxxx       Member 4 First Name  Last Name Email   Cell Phone   DoB    DoBaptism Anniversary
 * [5]                                 Member 5 First Name  Last Name Email   Cell Phone   DoB    DoBaptism Anniversary
 * [6]                                 Member 6 First Name  Last Name Email   Cell Phone   DoB    DoBaptism Anniversary
 * [7]                                 Member 7 First Name  Last Name Email   Cell Phone   DoB    DoBaptism Anniversary
 *
 * If any address fields on left side are blank, those entries will be omitted from the output.
 * If any member fields on the right are blank, those entries will also be omitted from the output.
 * The Last Name of a member is displayed only if it differs from the Family_Name.
 * The Family_Name and Member 1 First Name fields will always be output. They are required for a db entry.
 */

function cota_format_family_listing_for_print( $family, $members ) {

	// Initialize key variables
	$num_members = $members->num_rows;
	// $member_ctr = $left_side_ctr = 1;
	$addr1       = ( isset( $family['address'] ) && $family['address'] !== '' ) ? $family['address'] : false;
	$addr2       = ( isset( $family['address2'] ) && $family['address2'] !== '' ) ? $family['address2'] : false;
	$city        = ( isset( $family['city'] ) && $family['city'] !== '' ) ? $family['city'] : false;
	$homephone   = ( isset( $family['homephone'] ) && $family['homephone'] !== '' ) ? $family['homephone'] : false;
	$placeholder = '';

	$formatted_family_array = array(); // Initialize the formatted family array
	$left_side_array        = array();

	$formatted_family_array[0]['left_side_ctr'] = 1;
	$formatted_family_array[0]['member_ctr']    = 0;

	// Outer loop through left side of printed entry block
	for ( $left_side_ctr = 1; $left_side_ctr <= 4; $left_side_ctr++ ) {

		if ( $left_side_ctr == 1 ) {  // First time through the left listing block
			$individual = $members->fetch_assoc(); // Already have member. Parse row.

			if ( ! $individual ) {
				// No members found
				break;
			}
			$member_ctr = 1;

			// Clear last names if they match the family name. No need to output it.
			if ( $individual['last_name'] == $family['familyname'] ) {
				$individual['last_name'] = '';
			}

			// Format for printing
			$formatted_family_array[ $left_side_ctr ][1] = $family['familyname'];
			$formatted_family_array[ $left_side_ctr ][2] = $individual['first_name'] ?? '';
			$formatted_family_array[ $left_side_ctr ][3] = $individual['last_name'] ?? '';
			$formatted_family_array[ $left_side_ctr ][4] = $individual['email'] ?? '';
			$formatted_family_array[ $left_side_ctr ][5] = $individual['cell_phone'] ?? '';
			$formatted_family_array[ $left_side_ctr ][6] = ( ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : null;
			$formatted_family_array[ $left_side_ctr ][7] = ( ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : null;
			$formatted_family_array[ $left_side_ctr ][8] = ( ! empty( $individual['anniversary'] ) ) ? date( 'm/d', strtotime( $individual['anniversary'] ) ) : null;
			// $formatted_family_array[0][0] = $left_side_ctr; // Number of left side lines
			$formatted_family_array[0]['left_side_ctr'] = $left_side_ctr;

		} elseif ( $left_side_ctr == 2 ) {
			$individual = get_next_member( $members );
			if ( ! $individual ) {
				// No members found
				break;
			}
			$member_ctr += 1;

			// If there are more members, format their listing.
			if ( ( is_array( $individual ) && ! empty( $individual['last_name'] ) ) &&
				( $individual['last_name'] === $family['familyname'] ) ) {
				$individual['last_name'] = '';
			}
			if ( $addr1 ) {
				$left_side                                   = $family['address'] . ' ';
				$formatted_family_array[ $left_side_ctr ][1] = $family['address'];
				$addr1                                       = false;
				if ( $addr2 ) {
					$left_side                                  .= ', ' . $family['address2'] . ' ';
					$formatted_family_array[ $left_side_ctr ][1] = $family['address'] . ', ' . $family['address2'];
					$addr2                                       = false;
				}
			} elseif ( $homephone ) {
				$left_side                                   = 'Home: ' . $family['homephone'] . ' ';
				$formatted_family_array[ $left_side_ctr ][1] = 'Home: ' . $family['homephone'];
				$addr2                                       = false;
				$city                                        = false;
				$homephone                                   = false;
			} else {
				// If no address, use placeholder.
				// Remaining left side address will be blank.
				$left_side = $placeholder;
				$addr2     = false;
				$city      = false;
			}
			// Format for printing
			$formatted_family_array[ $left_side_ctr ][2] = $individual['first_name'] ?? '';
			$formatted_family_array[ $left_side_ctr ][3] = $individual['last_name'] ?? '';
			$formatted_family_array[ $left_side_ctr ][4] = $individual['email'] ?? '';
			$formatted_family_array[ $left_side_ctr ][5] = $individual['cell_phone'] ?? '';
			$formatted_family_array[ $left_side_ctr ][6] = ( ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : null;
			$formatted_family_array[ $left_side_ctr ][7] = ( ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : null;
			$formatted_family_array[ $left_side_ctr ][8] = ( ! empty( $individual['anniversary'] ) ) ? date( 'm/d', strtotime( $individual['anniversary'] ) ) : null;

			$formatted_family_array[0]['left_side_ctr'] = $left_side_ctr;

		} elseif ( $left_side_ctr === 3 ) {
			$individual = get_next_member( $members );
			if ( ! $individual ) {
				// No members found
				break;
			}
			$member_ctr += 1;

			// If there are more members, format their listing.
			if ( ( is_array( $individual ) && ! empty( $individual['last_name'] ) ) &&
				( $individual['last_name'] === $family['familyname'] ) ) {
				$individual['last_name'] = ' ';
			}
			if ( $addr2 ) {
				$left_side                                   = $family['address2'] . ' ';
				$formatted_family_array[ $left_side_ctr ][1] = 'l3-' . $family['address2'] ?? ' ';
				$addr2                                       = false;
			} elseif ( $city ) {
				$left_side                                   = $family['city'] . ', ' .
					$family['state'] . ' ' .
					$family['zip'];
				$city                                        = false;
				$formatted_family_array[ $left_side_ctr ][1] = $family['city'] . ', ' . $family['state'] . ' ' . $family['zip'];
			} else {
				// If no address, use placeholder.
				// Remaining left side address will be blank.
				$left_side = $placeholder;
			}
				// Format for printing
			$formatted_family_array[ $left_side_ctr ][2]  = $individual['first_name'] ?? ' ';
			$formatted_family_array[ $left_side_ctr ][2] .= ' ' . $individual['last_name'] ?? ' ';

			$formatted_family_array[ $left_side_ctr ][4] = $individual['email'] ?? ' ';
			$formatted_family_array[ $left_side_ctr ][5] = $individual['cell_phone'] ?? ' ';
			$formatted_family_array[ $left_side_ctr ][6] = ( ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : null;
			$formatted_family_array[ $left_side_ctr ][7] = ( ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : null;
			$formatted_family_array[ $left_side_ctr ][8] = ( ! empty( $individual['anniversary'] ) ) ? date( 'm/d', strtotime( $individual['anniversary'] ) ) : null;

			$formatted_family_array[0]['left_side_ctr'] = $left_side_ctr; // Number of left side lines

		} elseif ( 4 === $left_side_ctr ) {
			$individual = get_next_member( $members );
			if ( ! $individual ) {
				// No members found
				break;
			}
			$member_ctr += 1;

			if ( ( is_array( $individual ) && ! empty( $individual['last_name'] ) ) &&
				( $individual['last_name'] == $family['familyname'] ) ) {
				$individual['last_name'] = ' ';
			}

			if ( $homephone ) {
				$left_side                                   = 'Home: ' . $family['homephone'] . ' ';
				$formatted_family_array[ $left_side_ctr ][1] = 'Home: ' . $family['homephone'];
				$addr2                                       = false;
				$city                                        = false;
				$homephone                                   = false;
			} else {
				// If no address or phone, use placeholder.
				$left_side                                   = $placeholder;
				$formatted_family_array[ $left_side_ctr ][1] = ' ';

			}
			// Format for printing
			$formatted_family_array[ $left_side_ctr ][2] = $individual['first_name'] ?? ' ';
			$formatted_family_array[ $left_side_ctr ][3] = $individual['last_name'] ?? ' ';
			$formatted_family_array[ $left_side_ctr ][4] = $individual['email'] ?? ' ';
			$formatted_family_array[ $left_side_ctr ][5] = $individual['cell_phone'] ?? ' ';
			$formatted_family_array[ $left_side_ctr ][6] = ( ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : null;
			$formatted_family_array[ $left_side_ctr ][7] = ( ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : null;
			$formatted_family_array[ $left_side_ctr ][8] = ( ! empty( $individual['anniversary'] ) ) ? date( 'm/d', strtotime( $individual['anniversary'] ) ) : null;

			$formatted_family_array[0]['left_side_ctr'] = $left_side_ctr; // Number of left side lines
		}
	}
	// If $left_side_ctr = 5 then have 4 full address lines on left. The last is most likely the phone. Could have 1 or more members.
	if ( 5 === $left_side_ctr ) {
		$left_side_ctr = 4;
	}
	$formatted_family_array[0]['left_side_ctr'] = $left_side_ctr;

	// Loop through rest of family members
	while ( $member_ctr <= $num_members ) {
		$individual = get_next_member( $members );
		if ( ! $individual ) {
			// No more members found
			break;
		}
		$member_ctr += 1;

		if ( $individual['last_name'] == $family['familyname'] ) {
			$individual['last_name'] = ' ';
		}

		// Format for printing
		$formatted_family_array[ $member_ctr ][1] = ' '; // These members have no address components.
		$formatted_family_array[ $member_ctr ][2] = $individual['first_name'] ?? ' ';
		$formatted_family_array[ $member_ctr ][3] = $individual['last_name'] ?? ' ';
		$formatted_family_array[ $member_ctr ][4] = $individual['email'] ?? ' ';
		$formatted_family_array[ $member_ctr ][5] = $individual['cell_phone'] ?? ' ';
		$formatted_family_array[ $member_ctr ][6] = ( ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : null;
		$formatted_family_array[ $member_ctr ][7] = ( ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : null;
		$formatted_family_array[ $member_ctr ][8] = ( ! empty( $individual['anniversary'] ) ) ? date( 'm/d', strtotime( $individual['anniversary'] ) ) : null;
	}

	$formatted_family_array[0]['member_ctr'] = $member_ctr;

	return $formatted_family_array; // Indicate success
}
/**
 * cota_format_family_listing_for_display
 *
 * @param [type] $family - Result of database query of all data for a specified family ID
 * @param [type] $members - Result of COTA_Database query of all members of a specified family ID
 * @return string | null
 */
function cota_format_family_listing_for_display( $family, $members ) {
	// Set key logicals
	$num_members = $members->num_rows;
	$member_ctr  = 1;
	$addr1       = ( isset( $family['address'] ) && $family['address'] !== '' ) ? $family['address'] : false;
	$addr2       = ( isset( $family['address2'] ) && $family['address2'] !== '' ) ? $family['address2'] : false;
	$city        = ( isset( $family['city'] ) && $family['city'] !== '' ) ? $family['city'] : false;
	$homephone   = ( isset( $family['homephone'] ) && $family['homephone'] !== '' ) ? $family['homephone'] : false;
	// set placeholders.
    $placeholder      = '';
    $formatted_family = '';

    // Format strings for various outputs.
    $format_string_row_1       = "<tr class='cota-new-family' ><td><h3>%s</h3></td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
	$format_string             = "<tr class='format_string'><td>%s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
	$format_string_city        = "<tr class='format_string_city'><td>%s, %s %s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
	$format_string_homephone   = "<tr class='format_string_homephone'><td>Home: %s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
	$format_string_anniversary = "<tr class='format_string_anniversary'><td>%s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

	// Outer loop through left side of display - $left_side_ctr is line in left side
	for ( $left_side_ctr = 1; $left_side_ctr <= 4; $left_side_ctr++ ) {
		if ( 1 === $left_side_ctr ) {
				// Get 1st member of family
			$individual = $members->fetch_assoc();
            $is_there_anniversary = $individual['anniversary'] ?? '';
			if ( $individual ) {
				$formatted_family = sprintf(
					$format_string_row_1,
					$family['familyname'],
					$individual['first_name'] ?? '',
					$individual['last_name'] ?? '',
					$individual['email'] ?? '',
					$individual['cell_phone'] ?? '',
					( is_array( $individual ) && ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : '',
					( is_array( $individual ) && ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : '',
                    ( $is_there_anniversary ) ? date( 'm/d', strtotime( $individual['anniversary'] ) ) : ''

				);

			} else {
				// No members found
				// break;
			}
		} elseif ( 2 === $left_side_ctr ) {
			// Get next member of family
			$individual = get_next_member( $members );
			if ( $addr1 ) {
				$left_side = sprintf( '%s', $family['address'] );
				$addr1     = false;
				if ( $addr2 ) {
					$left_side .= sprintf( ', %s', $family['address2'] );
					$addr2      = false;
				}
			} elseif ( $homephone ) {
				$left_side = sprintf(
					'Home: %s',
					$family['homephone']
				);
				$addr2     = false;
				$city      = false;
				$homephone = false;
			} else {
				// If no address, use placeholder.
				// Remaining left side address will be blank.
				$left_side = $placeholder;
				$addr2     = false;
				$city      = false;
			}

			$formatted_family .= sprintf(
				$format_string,
				$left_side,
				$individual['first_name'] ?? '',
				$individual['last_name'] ?? '',
				$individual['email'] ?? '',
				$individual['cell_phone'] ?? '',
				( is_array( $individual ) && ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : '',
				( is_array( $individual ) && ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : ''
			);

		} elseif ( 3 === $left_side_ctr ) {
			// Get next member of family
			$individual = get_next_member( $members );
			if ( $addr2 ) {
				$left_side = sprintf( '%s', $family['address2'] );
				$addr2     = false;
			} elseif ( $city ) {
				$left_side = sprintf(
					'%s, %s %s',
					$family['city'],
					$family['state'],
					$family['zip']
				);
				$city      = false;

			} else {
				// If no address, use placeholder.
				// Remaining left side address will be blank.
				$left_side = $placeholder;
			}
			$formatted_family .= sprintf(
				$format_string,
				$left_side,
				$individual['first_name'] ?? '',
				$individual['last_name'] ?? '',
				$individual['email'] ?? '',
				$individual['cell_phone'] ?? '',
				( is_array( $individual ) && ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : '',
				( is_array( $individual ) && ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : ''
			);

		} elseif ( 4 === $left_side_ctr ) {
			$individual = get_next_member( $members );
			if ( ! $individual ) {
				// No more members found
				break;
			}

			if ( $homephone ) {
				$left_side = sprintf(
					'Home: %s',
					$family['homephone']
				);
				$addr2     = false;
				$city      = false;
				$homephone = false;
			} else {
				// If no address or phone, use placeholder.
				$left_side = $placeholder;

			}
			$formatted_family .= sprintf(
				$format_string,
				$left_side,
				$individual['first_name'] ?? '',
				$individual['last_name'] ?? '',
				$individual['email'] ?? '',
				$individual['cell_phone'] ?? '',
				( is_array( $individual ) && ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : '',
				( is_array( $individual ) && ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : ''
			);

		} else {
			// No data for left side of display
		}
	}
	$left_side = $placeholder;

	// Loop through rest of family members
	while ( $member_ctr <= $num_members ) {
		$individual        = get_next_member( $members );
		$formatted_family .= sprintf(
			$format_string,
			$left_side,
			$individual['first_name'] ?? '',
			$individual['last_name'] ?? '',
			$individual['email'] ?? '',
			$individual['cell_phone'] ?? '',
			( is_array( $individual ) && ! empty( $individual['birthday'] ) ) ? date( 'm/d', strtotime( $individual['birthday'] ) ) : '',
			( is_array( $individual ) && ! empty( $individual['baptism'] ) ) ? date( 'm/d', strtotime( $individual['baptism'] ) ) : ''
		);

		++$member_ctr;
	}
	return $formatted_family; // Indicate success
}

function get_next_member( $members ) {
	$individual = $members->fetch_assoc();
	if ( ! $individual ) {
		return false;
	}
	foreach ( array( 'first_name', 'last_name', 'email', 'cell_phone', 'birthday', 'baptism', 'anniversary' ) as $key ) {
		if ( ! isset( $individual[ $key ] ) ) {
			$individual[ $key ] = '';
		}
	}
	return $individual;
}
