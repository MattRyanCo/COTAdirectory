<?php

function format_family_listing_for_print($pdfobj, $family, $members) {

    // Set key logicals
    $num_members = $members->num_rows;
    $ictr = 1;
    $addr1 = (isset($family['address']) && $family['address'] !== "") ? $family['address'] : false;
    $addr2 = (isset($family['address2']) && $family['address2'] !== "") ? $family['address2'] : false;
    $city = (isset($family['city']) && $family['city'] !== "") ? $family['city'] : false;
    $homephone = (isset($family['homephone']) && $family['homephone'] !== "") ? $family['homephone'] : false;
    $placeholder = '';
    
    // Outer loop through left side of printed entry block
    for ( $lctr = 1; $lctr <= 4; $lctr++ ) {
        if ( $lctr == 1 ) {
            // Build the family header (name and address, other column headings)
            // Get 1st member of family
            // $pdfobj->multicell(2,0.5,"Family Name/Address Family Members\n",0,1);
            $pdfobj->cell(2,0.5,"Family Name/Address",0,0, 'L',false);
            $pdfobj->cell(2,0.5,"Family Members",0,1, 'L',false);
            $pdfobj->cell(2,0.25,"Home Phone",0,0, 'L',false);
            // $pdfobj->MultiCell(2, 0.5, "Home Phone   Name   Email   Cell   DoB   DoBaptism\n", 0, 1);
            $pdfobj->cell(1,0.25,"Name",0,0, 'L',false);
            $pdfobj->cell(1,0.25,"Email",0,0, 'L',false);
            $pdfobj->cell(1,0.25,"Cell",0,0, 'L',false);
            $pdfobj->cell(1,0.25,"DoB",0,0, 'L',false);
            $pdfobj->cell(1,0.25,"DoBaptism",0,1, 'L',false);

            $individual = $members->fetch_assoc();
            if (!$individual) {
                // No members found
                break;
            }
            $lines = [];
            $lines[] = $family['familyname'] . ' ' . 
                ($individual['first_name'] ?? '') .  ' ' . 
                ($individual['last_name'] ?? '' ).  ' ' . 
                ($individual['email'] ?? '' ).  ' ' . 
                ($individual['cell_phone'] ?? '') .  ' ' . 
                ($individual['birthday']??' ' .  ' ' . 
                ($individual['baptism'] ?? ' '));
        } elseif ( $lctr == 2 ) {
            $individual = get_next_member_to_print($members);
            if ( $addr1 ) {
                $left_side = $family['address'] . ' ';
                $addr1 = false;
                if ( $addr2 ) {
                    $left_side .= ', ' . $family['address2'] . ' ';
                    $addr2 = false;
                }
            } elseif ( $homephone ){
                $left_side = 'Home: ' . $family['homephone'] . ' ';
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
            $lines[] = $left_side . ' ' . 
                ($individual['first_name'] ?? '') .  ' ' . 
                ($individual['last_name'] ?? '' ).  ' ' . 
                ($individual['email'] ?? '' ).  ' ' . 
                ($individual['cell_phone'] ?? '') .  ' ' . 
                ($individual['birthday']??' ' .  ' ' . 
                ($individual['baptism'] ?? ' '));

        } elseif ( $lctr == 3 ) {
            $individual = get_next_member_to_print($members);
            if ( $addr2 ) {
                $left_side = $family['address2'] . ' ';
                $addr2 = false;
            } elseif ( $city ) {
                $left_side = $family['city'] . ', ' . 
                $family['state'] . ' ' . 
                $family['zip'] . ' ';
                $city = false;
            } else {
                // If no address, use placeholder. 
                // Remaining left side address will be blank. 
                $left_side = $placeholder;
            }
            $lines[] = $left_side . ' ' . 
                ($individual['first_name'] ?? '') .  ' ' . 
                ($individual['last_name'] ?? '' ).  ' ' . 
                ($individual['email'] ?? '' ).  ' ' . 
                ($individual['cell_phone'] ?? '') .  ' ' . 
                ($individual['birthday']??' ' .  ' ' . 
                ($individual['baptism'] ?? ' '));

        } elseif ( $lctr == 4 ) {
            $individual = get_next_member_to_print($members);
            if ( $individual === false ) {
                // No more members found
                break;
            }
            if ( $homephone ) {
                $left_side = 'Home: ' . $family['homephone'] . ' ';
                $addr2 = false;
                $city = false;
                $homephone = false;
            } else {
                // If no address or phone, use placeholder. 
                $left_side = $placeholder;
            }
            $lines[] = $left_side . ' ' . 
                ($individual['first_name'] ?? '') .  ' ' . 
                ($individual['last_name'] ?? '' ).  ' ' . 
                ($individual['email'] ?? '' ).  ' ' . 
                ($individual['cell_phone'] ?? '') .  ' ' . 
                ($individual['birthday']??' ' .  ' ' . 
                ($individual['baptism'] ?? ' '));
        } else {
            // No data for left side of display
        }
    }

    $left_side = $placeholder;
    // Loop through rest of family members
    while ($ictr <= $num_members) {
        $individual = get_next_member_to_print($members);
        $lines[] = $left_side . ' ' . 
            ($individual['first_name'] ?? '') . ' ' .
            ($individual['last_name'] ?? '') . ' ' .
            ($individual['email'] ?? '') . ' ' .
            ($individual['cell_phone'] ?? '') . ' ' .
            ($individual['birthday'] ?? '') . ' ' .
            ($individual['baptism'] ?? ''   );
        $ictr++;
    }


    // Output all lines for this family as a block in the PDF
    $pdfobj->SetFont('Arial', '', 10);
    $pdfobj->MultiCell(0, 0.22, implode("\n", $lines), 1, 1);
    // $pdfobj->Ln(0.1); // Small space after each family

    return true; // Indicate success
}
/**
 * format_family_listing_for_display
 *
 * @param [type] $family - Result of database query of all data for a specified family ID
 * @param [type] $members - Result of database query of all members of a specified family ID
 * @return string | null
 */
function format_family_listing_for_display($family, $members) { 
    // Set key logicals
    $num_members = $members->num_rows;
    $ictr = 1;
    $addr1 = (isset($family['address']) && $family['address'] !== "") ? $family['address'] : false;
    $addr2 = (isset($family['address2']) && $family['address2'] !== "") ? $family['address2'] : false;
    $city = (isset($family['city']) && $family['city'] !== "") ? $family['city'] : false;
    $homephone = (isset($family['homephone']) && $family['homephone'] !== "") ? $family['homephone'] : false;
    $placeholder = $formatted_family = '';

        // Format for 1st row of family listing - bolded Family name,  first, last, email, cell, birthday, baptism
    $format_string_row_1 = "<tr class='new-family' ><td><h3>%s</h3></td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

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
                    $individual['birthday'] ?? '',
                    $individual['baptism'] ?? ''
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
            $formatted_family .= sprintf(
                $format_string, 
                $left_side,
                $individual['first_name'] ?? '',
                $individual['last_name'] ?? '',
                $individual['email'] ?? '',
                $individual['cell_phone'] ?? '',
                $individual['birthday'] ?? '',
                $individual['baptism'] ?? ''
            );
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
                $individual['birthday'] ?? '',
                $individual['baptism'] ?? ''
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
                $individual['birthday'] ?? '',
                $individual['baptism'] ?? ''
            );
        } else {
            // No data for left side of display
        }
    }
    $left_side = $placeholder;
    // Loop through rest of family members
    while ($ictr <= $num_members) {
        $individual = get_next_member($members);
        $formatted_family .= sprintf(
            $format_string, 
            $left_side,
            $individual['first_name'] ?? '',
            $individual['last_name'] ?? '',
            $individual['email'] ?? '',
            $individual['cell_phone'] ?? '',
            $individual['birthday'] ?? '',
            $individual['baptism'] ?? ''
        );
        $ictr++;
    }
    return $formatted_family;
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
            $individual['birthday'] ?? '',
            $individual['baptism'] ?? ''
        );
    } else {
        // No more members found
        return false;
    }
    return $individual;
    // var_dump($formatted_family_member);
    // return $formatted_family_member;

}

function get_next_member_to_print($members) {

    $individual = $members->fetch_assoc();
    $formatted_family_member = '';
    // Fetch the next member from the result set
    if ($individual) {
        $formatted_family_member = 
            ($individual['first_name'] ?? '') . ' ' .
            ($individual['last_name'] ?? '') . ' ' .
            ($individual['cell_phone'] ?? '') . ' ' .
            ($individual['email'] ?? '') . ' ' .
            ($individual['birthday'] ?? '') . ' ' .
            ($individual['baptism'] ?? '');
    } else {
        // No more members found
        return false;
    }
    return $individual;
}