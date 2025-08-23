<?php
/**
 * cota_format_family_listing_for_print
 *
 * @param array $family - Result of database query of all data for a specified family ID
 * @param mysqli_result $members - Result of COTA_Database query of all members of a specified family ID
 * @return string | null
 */

function cota_format_family_listing_for_print($family, $members) {

    // Set key logicals
    $num_members = $members->num_rows;
    // var_dump($family['familyname'],$num_members);
    $member_ctr = $left_row_ctr = $right_side_ctr = 1;
    $addr1 = (isset($family['address']) && $family['address'] !== "") ? $family['address'] : false;
    $addr2 = (isset($family['address2']) && $family['address2'] !== "") ? $family['address2'] : false;
    $city = (isset($family['city']) && $family['city'] !== "") ? $family['city'] : false;
    $homephone = (isset($family['homephone']) && $family['homephone'] !== "") ? $family['homephone'] : false;
    $placeholder = '';

    $formatted_family_array = []; // Initialize the formatted family array
    $left_side_array = [];
    $print_it = true; // Set to false to not print the formatted family array

    // Outer loop through left side of printed entry block
    for ( $left_row_ctr = 1; $left_row_ctr <= 4; $left_row_ctr++ ) {
        if ( $left_row_ctr == 1 ) {
            $individual = $members->fetch_assoc();
        // Clear out last names if they match the family name. Redundant. 
            if ( $individual['last_name'] == $family['familyname'] ) $individual['last_name'] = '';

                // Format for printing
            $formatted_family_array[$left_row_ctr][1] = $family['familyname'];
            $formatted_family_array[$left_row_ctr][4] = $individual['first_name'] ?? '';
            $formatted_family_array[$left_row_ctr][5] = $individual['last_name'] ?? '';
            $formatted_family_array[$left_row_ctr][6] = $individual['email'] ?? '';
            $formatted_family_array[$left_row_ctr][7] = $individual['cell_phone'] ?? '';
            $formatted_family_array[$left_row_ctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
            $formatted_family_array[$left_row_ctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;
            $formatted_family_array[0][0] = $left_row_ctr; // Number of left side lines 

        } elseif ( $left_row_ctr == 2 ) {
            $individual = get_next_member($members);
            // If there are more members, format their listing. 
            if ( (is_array($individual) && !empty($individual['last_name']) ) ) {
                if ( $individual['last_name'] == $family['familyname'] ) {
                    $individual['last_name'] = '';
                    if ( $addr1 ) {
                        $left_side = $family['address'] . ' ';
                        if ($print_it) $formatted_family_array[$left_row_ctr][1] = $family['address'];
                        $addr1 = false;
                        if ( $addr2 ) {
                            $left_side .= ', ' . $family['address2'] . ' ';
                            if ($print_it) $formatted_family_array[$left_row_ctr][1] = $family['address'] . ', ' . $family['address2'];
                            $addr2 = false;
                        }
                    } elseif ( $homephone ){
                        $left_side = 'Home: ' . $family['homephone'] . ' ';
                        if ($print_it) $formatted_family_array[$left_row_ctr][1] = 'Home: ' . $family['homephone'];
                        $addr2 = false;
                        $city = false;
                        $homephone = false;
                    } else {
                        // If no address, use placeholder. 
                        // Remaining left side address will be blank. 
                        $left_side = $placeholder;
                        If ($print_it) $formatted_family_array[$left_row_ctr][1] = $formatted_family_array[$left_row_ctr][2] = $formatted_family_array[$left_row_ctr][3] = '';
                        $addr2 = false;
                        $city = false;
                    }
                    // Format for printing
                    $formatted_family_array[$left_row_ctr][4] = $individual['first_name'] ?? '';
                    $formatted_family_array[$left_row_ctr][5] = $individual['last_name'] ?? '';
                    $formatted_family_array[$left_row_ctr][6] = $individual['email'] ?? '';
                    $formatted_family_array[$left_row_ctr][7] = $individual['cell_phone'] ?? '';
                    $formatted_family_array[$left_row_ctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
                    $formatted_family_array[$left_row_ctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;
                    $formatted_family_array[0][0] = $left_row_ctr; // Number of left side lines 
                }
            }

        } elseif ( $left_row_ctr == 3 ) {
            $individual = get_next_member($members);

            // If there are more members, format their listing. 
            if ( (is_array($individual) && !empty($individual['last_name']) ) ) {
                if ( $individual['last_name'] == $family['familyname'] ) {
                    $individual['last_name'] = '';
                    if ( $addr2 ) {
                        $left_side = $family['address2'] . ' ';
                        If ($print_it) {
                            $formatted_family_array[$left_row_ctr][1] = $family['address2'] ?? '';
                        }
                        $addr2 = false;
                    } elseif ( $city ) {
                        $left_side = $family['city'] . ', ' . 
                            $family['state'] . ' ' . 
                            $family['zip'];
                        $city = false;
                        If ($print_it) {
                            $formatted_family_array[$left_row_ctr][1] = $family['city'] . ', '. $family['state'] . ' ' . $family['zip'] ; 
                        }
                    } else {
                        // If no address, use placeholder. 
                        // Remaining left side address will be blank. 
                        $left_side = $placeholder;
                        If ($print_it) $formatted_family_array[$left_row_ctr][1] = ' ';
                    }
                        // Format for printing
                    $formatted_family_array[$left_row_ctr][4] = $individual['first_name'] . ' ' . $individual['last_name'];
                    $formatted_family_array[$left_row_ctr][6] = $individual['email'] ?? '';
                    $formatted_family_array[$left_row_ctr][7] = $individual['cell_phone'] ?? '';
                    $formatted_family_array[$left_row_ctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
                    $formatted_family_array[$left_row_ctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;
                    $formatted_family_array[0][0] = $left_row_ctr; // Number of left side lines 
                }
            }
        } elseif ( $left_row_ctr == 4 ) {
            $individual = get_next_member($members);
            if ( (is_array($individual) && !empty($individual['last_name']) ) ) {
                if ( $individual['last_name'] == $family['familyname'] ) {
                    $individual['last_name'] = '';

                    if ( $homephone ) {
                        $left_side = 'Home: ' . $family['homephone'] . ' ';
                        if ($print_it) $formatted_family_array[$left_row_ctr][1] = 'Home: ' . $family['homephone'];
                        $addr2 = false;
                        $city = false;
                        $homephone = false;
                    } else {
                        // If no address or phone, use placeholder. 
                        $left_side = $placeholder;
                        If ($print_it) $formatted_family_array[$left_row_ctr][1] = '';
                    }
                    // Format for printing
                    $formatted_family_array[$left_row_ctr][4] = $individual['first_name'] ?? '';
                    $formatted_family_array[$left_row_ctr][5] = $individual['last_name'] ?? '';
                    $formatted_family_array[$left_row_ctr][6] = $individual['email'] ?? '';
                    $formatted_family_array[$left_row_ctr][7] = $individual['cell_phone'] ?? '';
                    $formatted_family_array[$left_row_ctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
                    $formatted_family_array[$left_row_ctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;
                    $formatted_family_array[0][0] = $left_row_ctr; // Number of left side lines 
                }
            }
        }
        $right_side_ctr+=1;  // Next line on right side.
    }
    // var_dump($formatted_family_array);      

    $left_side = $placeholder;
    // print_r('left_row_ctr is ' . $left_row_ctr . '   right_side_ctr is ' . $right_side_ctr . '   mctr is ' . $member_ctr . '  formatted_family_array[0][0] = ' . $formatted_family_array[0][0]);echo '<br>';

    // Loop through rest of family members
    $member_ctr = $formatted_family_array[0][0];
    while ($member_ctr <= $num_members) {
        // var_dump($member_ctr);
        $individual = get_next_member($members);
        if ( !$individual ) {
            // No more members found
            break;
        }
        if ( $individual['last_name'] == $family['familyname'] ) $individual['last_name'] = '';

        // Format for printing
        $formatted_family_array[$left_row_ctr][4] = $individual['first_name'] ?? '';
        $formatted_family_array[$left_row_ctr][5] = $individual['last_name'] ?? '';
        $formatted_family_array[$left_row_ctr][6] = $individual['email'] ?? '';
        $formatted_family_array[$left_row_ctr][7] = $individual['cell_phone'] ?? '';
        $formatted_family_array[$left_row_ctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
        $formatted_family_array[$left_row_ctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;
        $member_ctr++;
        $left_row_ctr++;
    }
    // $formatted_family_array[0][0] = number of family listing lines (address lines)
    // $formatted_family_array[0][1] = number of family members 
    $formatted_family_array[0][1] = $member_ctr;
    return $formatted_family_array; // Indicate success
}
/**
 * cota_format_family_listing_for_display
 *
 * @param [type] $family - Result of database query of all data for a specified family ID
 * @param [type] $members - Result of COTA_Database query of all members of a specified family ID
 * @return string | null
 */
function cota_format_family_listing_for_display($family, $members) { 
    // Set key logicals
    $num_members = $members->num_rows;
    $member_ctr = 1;
    $addr1 = (isset($family['address']) && $family['address'] !== "") ? $family['address'] : false;
    $addr2 = (isset($family['address2']) && $family['address2'] !== "") ? $family['address2'] : false;
    $city = (isset($family['city']) && $family['city'] !== "") ? $family['city'] : false;
    $homephone = (isset($family['homephone']) && $family['homephone'] !== "") ? $family['homephone'] : false;
    $placeholder = $formatted_family = '';

        // Format for 1st row of family listing - bolded Family name,  first, last, email, cell, birthday, baptism
    $format_string_row_1 = "<tr class='cota-new-family' ><td><h3>%s</h3></td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

    // Secondary format for family listing - Address component, first, last, email, cell, birthday, baptism
    $format_string = "<tr><td>%s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
    $format_string_city = "<tr><td>%s, %s %s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
    $format_string_homephone = "<tr><td>Home: %s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";


    // Outer loop through left side of display - $left_row_ctr is line in left side
    for ( $left_row_ctr = 1; $left_row_ctr <= 4; $left_row_ctr++ ) {
        if ( $left_row_ctr == 1 ) {
                // Get 1st member of family
            $individual = $members->fetch_assoc();
            if ($individual) {
                $formatted_family = sprintf(
                    $format_string_row_1, 
                    $family['familyname'],
                    $individual['first_name'] ?? '',
                    $individual['last_name'] ?? '',
                    $individual['email'] ?? '',
                    $individual['cell_phone'] ?? '',
                    (is_array($individual) && !empty($individual['birthday'])) ? date('m/d/y', strtotime($individual['birthday'])) : '',
                    (is_array($individual) && !empty($individual['baptism'])) ? date('m/d/y', strtotime($individual['baptism'])) : ''
                );

            } else {
                // No members found
                // break;
            }
 
        } elseif ( $left_row_ctr == 2 ) {
            // Get next member of family
            $individual = get_next_member($members);
            if ( $addr1 ) {
                $left_side = sprintf("%s", $family['address']);
                $addr1 = false;
                if ( $addr2 ) {
                    $left_side .= sprintf(", %s", $family['address2']);
                    $addr2 = false;
                }
            } elseif ( $homephone ){
                $left_side = sprintf(
                    "<tr><td>Home: %s</td>", 
                    $family['homephone']
                );
                $addr2 = false;
                $city = false;
                $homephone = false;
            } else {
                // If no address, use placeholder. 
                // Remaining left side address will be blank. 
                $left_side = $placeholder;
                $addr2 = false;
                $city = false;
            }
            if ((is_array($individual) && !empty($individual['first_name']) && $individual['first_name'] == $family['fname1']) ||  
                (is_array($individual) && !empty($individual['first_name']) && $individual['first_name'] == $family['fname2']) ) {
                $formatted_family .= sprintf(
                    $format_string, 
                    $left_side,
                    $individual['first_name'] ?? '',
                    $individual['last_name'] ?? '',
                    $individual['email'] ?? '',
                    $individual['cell_phone'] ?? '',
                    (is_array($individual) && !empty($individual['birthday'])) ? date('m/d/y', strtotime($individual['birthday'])) : '',
                    (is_array($individual) && !empty($individual['baptism'])) ? date('m/d/y', strtotime($individual['baptism'])) : ''
                );
            } else { // Add year to non-primary family members DoB.
                $formatted_family .= sprintf(
                    $format_string, 
                    $left_side,
                    $individual['first_name'] ?? '',
                    $individual['last_name'] ?? '',
                    $individual['email'] ?? '',
                    $individual['cell_phone'] ?? '',
                    (is_array($individual) && !empty($individual['birthday'])) ? date('m/d/y', strtotime($individual['birthday'])) : '',
                    (is_array($individual) && !empty($individual['baptism'])) ? date('m/d/y', strtotime($individual['baptism'])) : '' 
                );
            }


        } elseif ( $left_row_ctr == 3 ) {
            // Get next member of family
            $individual = get_next_member($members);
            if ( $addr2 ) {
                $left_side = sprintf("%s", $family['address2']);
                $addr2 = false;
            } elseif ( $city ) {
                $left_side = sprintf(
                    "<tr><td>%s, %s %s</td>", 
                    $family['city'], 
                    $family['state'], 
                    $family['zip']);
                $city = false;

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
                (is_array($individual) && !empty($individual['birthday'])) ? date('m/d/y', strtotime($individual['birthday'])) : '',
                (is_array($individual) && !empty($individual['baptism'])) ? date('m/d/y', strtotime($individual['baptism'])) : ''
            );

        } elseif ( $left_row_ctr == 4 ) {
            $individual = get_next_member($members);
            if ( $individual === false ) {
                // No more members found
                break;
            }

            if ( $homephone ) {
                $left_side = sprintf(
                    "<tr><td>Home: %s</td>", 
                    $family['homephone']
                );
                $addr2 = false;
                $city = false;
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
                (is_array($individual) && !empty($individual['birthday'])) ? date('m/d/y', strtotime($individual['birthday'])) : '',
                (is_array($individual) && !empty($individual['baptism'])) ? date('m/d/y', strtotime($individual['baptism'])) : ''
            );

        } else {
            // No data for left side of display
        }
    }
    $left_side = $placeholder;

    // Loop through rest of family members
    while ($member_ctr <= $num_members) {
        $individual = get_next_member($members);
        $formatted_family .= sprintf(
            $format_string, 
            $left_side,
            $individual['first_name'] ?? '',
            $individual['last_name'] ?? '',
            $individual['email'] ?? '',
            $individual['cell_phone'] ?? '',
            (is_array($individual) && !empty($individual['birthday'])) ? date('m/d/y', strtotime($individual['birthday'])) : '',
            (is_array($individual) && !empty($individual['baptism'])) ? date('m/d/y', strtotime($individual['baptism'])) : ''
        );

        $member_ctr++;
    }
    return $formatted_family; // Indicate success
}

function get_next_member($members) {
    $individual = $members->fetch_assoc();
    if (!$individual) return false;
    return $individual;
}