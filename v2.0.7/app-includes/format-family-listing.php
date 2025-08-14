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
    $mctr = $lctr = $rctr = 1;
    $addr1 = (isset($family['address']) && $family['address'] !== "") ? $family['address'] : false;
    $addr2 = (isset($family['address2']) && $family['address2'] !== "") ? $family['address2'] : false;
    $city = (isset($family['city']) && $family['city'] !== "") ? $family['city'] : false;
    $homephone = (isset($family['homephone']) && $family['homephone'] !== "") ? $family['homephone'] : false;
    $placeholder = '';

    $formatted_family_array = []; // Initialize the formatted family array
    $left_side_array = [];
    $print_it = true; // Set to false to not print the formatted family array

    // Outer loop through left side of printed entry block
    for ( $lctr = 1; $lctr <= 4; $lctr++ ) {
        if ( $lctr == 1 ) {

            $individual = $members->fetch_assoc();
            if (!$individual) {
                // No members found
                break;
            }

            // Print out dates in mm/dd format only. 
                // Format for printing
            $formatted_family_array[$lctr][1] = $family['familyname'];
            $formatted_family_array[$lctr][4] = $individual['first_name'] ?? '';
            $formatted_family_array[$lctr][5] = $individual['last_name'] ?? '';
            $formatted_family_array[$lctr][6] = $individual['email'] ?? '';
            $formatted_family_array[$lctr][7] = $individual['cell_phone'] ?? '';
            $formatted_family_array[$lctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
            $formatted_family_array[$lctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;
        } elseif ( $lctr == 2 ) {
            $individual = get_next_member_to_print($members);
            if ( $addr1 ) {
                $left_side = $family['address'] . ' ';
                if ($print_it) $formatted_family_array[$lctr][1] = $family['address'];
                $addr1 = false;
                if ( $addr2 ) {
                    $left_side .= ', ' . $family['address2'] . ' ';
                    if ($print_it) $formatted_family_array[$lctr][1] = $family['address'] . ', ' . $family['address2'];
                    $addr2 = false;
                }
            } elseif ( $homephone ){
                $left_side = 'Home: ' . $family['homephone'] . ' ';
                if ($print_it) $formatted_family_array[$lctr][1] = 'Home: ' . $family['homephone'];
                $addr2 = false;
                $city = false;
                $homephone = false;
            } else {
                // If no address, use placeholder. 
                // Remaining left side address will be blank. 
                $left_side = $placeholder;
                If ($print_it) $formatted_family_array[$lctr][1] = $formatted_family_array[$lctr][2] = $formatted_family_array[$lctr][3] = '';
                $addr2 = false;
                $city = false;
            }
                    // Format for printing
            $formatted_family_array[$lctr][4] = $individual['first_name'] ?? '';
            $formatted_family_array[$lctr][5] = $individual['last_name'] ?? '';
            $formatted_family_array[$lctr][6] = $individual['email'] ?? '';
            $formatted_family_array[$lctr][7] = $individual['cell_phone'] ?? '';
            $formatted_family_array[$lctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
            $formatted_family_array[$lctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;

        } elseif ( $lctr == 3 ) {
            $individual = get_next_member_to_print($members);
            if ( $addr2 ) {
                $left_side = $family['address2'] . ' ';
                If ($print_it) {
                    $formatted_family_array[$lctr][1] = $family['address2'] ?? '';
                }
                $addr2 = false;
            } elseif ( $city ) {
                $left_side = $family['city'] . ', ' . 
                    $family['state'] . ' ' . 
                    $family['zip'];
                $city = false;
                If ($print_it) {
                    $formatted_family_array[$lctr][1] = $family['city'] . ', '. $family['state'] . ' ' . $family['zip'] ; 
                }
            } else {
                // If no address, use placeholder. 
                // Remaining left side address will be blank. 
                $left_side = $placeholder;
                If ($print_it) $formatted_family_array[$lctr][1] = ' ';
            }
                // Format for printing
            $formatted_family_array[$lctr][4] = $individual['first_name'] . ' ' . $individual['last_name'];
            $formatted_family_array[$lctr][6] = $individual['email'] ?? '';
            $formatted_family_array[$lctr][7] = $individual['cell_phone'] ?? '';
            $formatted_family_array[$lctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
            $formatted_family_array[$lctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;
        } elseif ( $lctr == 4 ) {
            $individual = get_next_member_to_print($members);
            // print_r($individual);echo '<br>';
            if ( !$individual ) {
                // No more members found
                // break;
            }
            if ( $homephone ) {
                $left_side = 'Home: ' . $family['homephone'] . ' ';
                if ($print_it) $formatted_family_array[$lctr][1] = 'Home: ' . $family['homephone'];
                $addr2 = false;
                $city = false;
                $homephone = false;
            } else {
                // If no address or phone, use placeholder. 
                $left_side = $placeholder;
                If ($print_it) $formatted_family_array[$lctr][1] = '';
            }
                // Format for printing
            $formatted_family_array[$lctr][4] = $individual['first_name'] ?? '';
            $formatted_family_array[$lctr][5] = $individual['last_name'] ?? '';
            $formatted_family_array[$lctr][6] = $individual['email'] ?? '';
            $formatted_family_array[$lctr][7] = $individual['cell_phone'] ?? '';
            $formatted_family_array[$lctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
            $formatted_family_array[$lctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;
        }
        $rctr+=1;  // Next line on right side.
        $formatted_family_array[0][0] = $lctr; // Number of left side lines 
    }

    $left_side = $placeholder;
    // print_r('lctr is ' . $lctr . '   rctr is ' . $rctr . '   mctr is ' . $mctr);echo '<br>';

    // Loop through rest of family members
    $num_members = $members->num_rows;
    while ($mctr <= $num_members) {
        $individual = get_next_member_to_print($members);
            // Format for printing
        $formatted_family_array[$lctr][4] = $individual['first_name'] ?? '';
        $formatted_family_array[$lctr][5] = $individual['last_name'] ?? '';
        $formatted_family_array[$lctr][6] = $individual['email'] ?? '';
        $formatted_family_array[$lctr][7] = $individual['cell_phone'] ?? '';
        $formatted_family_array[$lctr][8] = (!empty($individual['birthday'])) ? date('m/d', strtotime($individual['birthday'])) : null;
        $formatted_family_array[$lctr][9] = (!empty($individual['baptism'])) ? date('m/d', strtotime($individual['baptism'])) : null;
        $mctr++;
        $lctr+=1;
    }
    $formatted_family_array[0][1] = $mctr - $formatted_family_array[0][0];
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
    $mctr = 1;
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


    // Outer loop through left side of display - $lctr is line in left side
    for ( $lctr = 1; $lctr <= 4; $lctr++ ) {
        if ( $lctr == 1 ) {
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
                    ($individual['birthday'] ? date('m/d', strtotime($individual['birthday'])) : ''),
                    ($individual['baptism'] ? date('m/d', strtotime($individual['baptism'])) : '')
                );

            } else {
                // No members found
                // break;
            }
 
        } elseif ( $lctr == 2 ) {
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
            if ( $individual['first_name'] == $family['fname1'] ||  $individual['first_name'] == $family['fname2'] ) {
                $formatted_family .= sprintf(
                    $format_string, 
                    $left_side,
                    $individual['first_name'] ?? '',
                    $individual['last_name'] ?? '',
                    $individual['email'] ?? '',
                    $individual['cell_phone'] ?? '',
                    ($individual['birthday'] ? date('m/d', strtotime($individual['birthday'])) : ''),
                    ($individual['baptism'] ? date('m/d', strtotime($individual['baptism'])) : '')
                );
            } else { // Add year to non-primary family members DoB.
                $formatted_family .= sprintf(
                    $format_string, 
                    $left_side,
                    $individual['first_name'] ?? '',
                    $individual['last_name'] ?? '',
                    $individual['email'] ?? '',
                    $individual['cell_phone'] ?? '',
                    ($individual['birthday'] ? date('m/d/y', strtotime($individual['birthday'])) : ''),
                    ($individual['baptism'] ? date('m/d/y', strtotime($individual['baptism'])) : '') 
                );
            }


        } elseif ( $lctr == 3 ) {
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
                ($individual['birthday'] ? date('m/d/y', strtotime($individual['birthday'])) : ''),
                ($individual['baptism'] ? date('m/d/y', strtotime($individual['baptism'])) : '')
            );

        } elseif ( $lctr == 4 ) {
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
                // $individual['birthday'] ?? '',
                // $individual['baptism'] ?? '',
                ($individual['birthday'] ? date('m/d/y', strtotime($individual['birthday'])) : ''),
                ($individual['baptism'] ? date('m/d/y', strtotime($individual['baptism'])) : '')
            );

        } else {
            // No data for left side of display
        }
    }
    $left_side = $placeholder;

    // Loop through rest of family members
    while ($mctr <= $num_members) {
        $individual = get_next_member($members);
        $formatted_family .= sprintf(
            $format_string, 
            $left_side,
            $individual['first_name'] ?? '',
            $individual['last_name'] ?? '',
            $individual['email'] ?? '',
            $individual['cell_phone'] ?? '',
            ($individual['birthday'] ? date('m/d/y', strtotime($individual['birthday'])) : ''),
            ($individual['baptism'] ? date('m/d/y', strtotime($individual['baptism'])) : '')
        );

        $mctr++;
    }
    return $formatted_family; // Indicate success
}

function get_next_member($members) {

    $individual = $members->fetch_assoc();
    $formatted_family_member = '';
    // var_dump($individual);
    if ($individual) {
        $formatted_family_member = sprintf(
            "<tr><td>%s %s %s %s %s %s</td></tr>", 
            $individual['first_name'] ?? '',
            $individual['last_name'] ?? '',
            $individual['cell_phone'] ?? '',
            $individual['email'] ?? '',
            $individual['birthday'] ? date('m/d', strtotime($individual['birthday'])) : '',
            $individual['baptism'] ? date('m/d', strtotime($individual['baptism'])) : ''
        );
    } else {
        // No more members found
        return false;
    }
    return $individual;
}

function get_next_member_to_print($members) {

    $individual = $members->fetch_assoc();

    if (!$individual) return false;
    return $individual;
}