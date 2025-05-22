<?php
/**
 * format_family_listing_for_display
 *
 * @param [type] $family - Result of database query of all data for a specified family ID
 * @param [type] $members - Result of database query of all members of a specified family ID
 * @return string | null
 */
Function format_family_listing_for_display($family, $members) {

    // Set key logicals
    $num_members = $members->num_rows;
    $ictr = 1;
    $addr1 = ($family['address'] !== "") ? $family['address'] : false;
    $addr2 = ($family['address2'] !== "") ? $family['address2'] : false;
    $city = ($family['city'] !== "") ? $family['city'] : false;
    $homephone = ($family['homephone'] !== "") ? $family['homephone'] : false;
    if ( $addr1 || $city || $homephone ) {
        $got_left = TRUE;  // Some data for left side of display
    } else {
        $got_left = FALSE;  // No data for left side of display
    }
    $placeholder = '';

    // Blank component for 1st column, standard first, last, email, cell, birthday, baptism on right
    $format_string6 = "<tr><td></td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

    $format_string1 = "<tr><td><strong>%s</strong></td><td>%s</td><td>%s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
    
    // Format for 1st row of family listing - bolded Family name,  first, last, email, cell, birthday, baptism
    $format_string_row_class = "<tr class='new_family'><td><strong>%s</strong></td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

    // Secondary format for family listing - Address component, first, last, email, cell, birthday, baptism
    $format_string7 = "<tr><td>%s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
    $format_string3 = "<tr><td>Home: %s</td><td>%s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

    // Get 1st member of family
    $individual = $members->fetch_assoc();
    $formatted_family = sprintf($format_string_row_class, $family['familyname'],$individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
    // $individual = $members->fetch_assoc();

    // if ( $addr1 ) {
    //     $formatted_family .= sprintf($format_string7, $family['address'], $individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
    // } else {  // If no 1st line of address, skip all address lines and check home phone
    //     $formatted_family .= sprintf($format_string_row_class, $placeholder, $individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
    // }

    while ($ictr <= $num_members || $got_left ) {
        // Get next member of family
        $individual = $members->fetch_assoc();
        if ( is_null($individual) ) {
            // break; // No more members to process
            return $formatted_family;
        }
        // Get address components for left side of display
        if ( $addr1 ) {
            // Have at least one component on left side
            $left_side = sprintf("<tr><td>%s</td>",$family['address']);
            $formatted_family .= sprintf($format_string7, $family['address'], $individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
            $addr1 = FALSE;
            $ictr += 1;
            $individual = $members->fetch_assoc();
        } else {
            // No address 1 component on left side.Skip remaing address and jumpt to Home phone
            if ( $homephone ) { // here home phone. 
                $left_side = sprintf("Home: %s",$family['homephone']);
                $formatted_family .= sprintf($format_string7, $left_side, $individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
                $homephone = FALSE;
                $ictr += 1;
                $individual = $members->fetch_assoc();
            } else { // here no home phone. Need blank left side. This is default output
                $left_side = sprintf("%s",$placeholder);
                $formatted_family .= sprintf($format_string7, $left_side, $individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
                $ictr += 1;
                $individual = $members->fetch_assoc();
            }

        }
        if ( $ictr >= $num_members) break; // here no more members to process.

        if ( $addr2 ) {
            $left_side = sprintf("<tr><td>%s</td>",$family['address2']);
            $formatted_family .= sprintf($format_string7, $family['address2'], $individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
            $ictr += 1;
            $individual = $members->fetch_assoc();
        } else { // here no 2nd line of addr or already done with it. 
            if ( $city ) {  // Here city. Assumes State & Zip to be output.
                $left_side = sprintf("%s, %s %s",$family['city'], $family['state'], $family['zip']);
                $formatted_family .= sprintf($format_string7, $left_side, $individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
                $city = FALSE;
                $ictr += 1;
                $individual = $members->fetch_assoc();
            } else { // here no city. No State or Zip will be printed.Home phone will be printed.
                if ( $homephone ) { // here home phone. 
                    $left_side = sprintf("Home: %s",$family['homephone']);
                    $formatted_family .= sprintf($format_string7, $left_side, $individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
                    $homephone = FALSE;
                    $ictr += 1;
                    $individual = $members->fetch_assoc();
                } else { // here no home phone. Need blank left side. This is default output
                    $left_side = "";
                    $formatted_family .= sprintf($format_string7, $left_side, $individual['first_name'], $individual['last_name'],$individual['email'],$individual['cell_phone'],$individual['birthday'],$individual['baptism']);
                    $ictr += 1;
                    $individual = $members->fetch_assoc();        
                }
            }
        }
        $addr2 = FALSE;
    }
    return $formatted_family;
}
function format_family_listing_for_print($family, $members) {

    return TRUE;
}