<?php
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
    // var_dump($num_members);
    $ictr = 1;
    $addr1 = (isset($family['address']) && $family['address'] !== "") ? $family['address'] : false;
    $addr2 = (isset($family['address2']) && $family['address2'] !== "") ? $family['address2'] : false;
    $city = (isset($family['city']) && $family['city'] !== "") ? $family['city'] : false;
    $homephone = (isset($family['homephone']) && $family['homephone'] !== "") ? $family['homephone'] : false;

    if ( $addr1 || $city || $homephone ) {
        $got_left = TRUE;  // Some data for left side of display
    } else {
        $got_left = FALSE;  // No data for left side of display
    }
    $placeholder = '';
   
    // Format for 1st row of family listing - bolded Family name,  first, last, email, cell, birthday, baptism
    $format_string_row_class = "<tr class='new-family' ><td><h3>%s</h3></td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

    // Secondary format for family listing - Address component, first, last, email, cell, birthday, baptism
    $format_string = "<tr><td>%s</td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

    // Get 1st member of family
    $individual = $members->fetch_assoc();
    if ($individual) {
        $formatted_family = sprintf($format_string_row_class, $family['familyname'],
            $individual['first_name'] ?? '',
            $individual['last_name'] ?? '',
            $individual['email'] ?? '',
            $individual['cell_phone'] ?? '',
            $individual['birthday'] ?? '',
            $individual['baptism'] ?? ''
        );
    } else {
        // No members found
        return '';
    }

    // Loop through remaining members
    while ($ictr <= $num_members) {
        $individual = $members->fetch_assoc();
        // if (!$individual) break;

        // Get address components for left side of display
        if ($addr1) {
            $left_side = $family['address'];
            $formatted_family .= sprintf($format_string, $left_side,
                $individual['first_name'] ?? '',
                $individual['last_name'] ?? '',
                $individual['email'] ?? '',
                $individual['cell_phone'] ?? '',
                $individual['birthday'] ?? '',
                $individual['baptism'] ?? ''
            );
            $addr1 = false;
        } elseif ($addr2) {
            $left_side = $family['address2'];
            $formatted_family .= sprintf($format_string, $left_side,
                $individual['first_name'] ?? '',
                $individual['last_name'] ?? '',
                $individual['email'] ?? '',
                $individual['cell_phone'] ?? '',
                $individual['birthday'] ?? '',
                $individual['baptism'] ?? ''
            );
            $addr2 = false;
        } elseif ($city) {
            $left_side = sprintf("%s, %s %s", $family['city'], $family['state'], $family['zip']);
            $formatted_family .= sprintf($format_string, $left_side,
                $individual['first_name'] ?? '',
                $individual['last_name'] ?? '',
                $individual['email'] ?? '',
                $individual['cell_phone'] ?? '',
                $individual['birthday'] ?? '',
                $individual['baptism'] ?? ''
            );
            $city = false;
        } elseif ($homephone) {
            $left_side = sprintf("Home: %s", $family['homephone']);
            $formatted_family .= sprintf($format_string, $left_side,
                $individual['first_name'] ?? '',
                $individual['last_name'] ?? '',
                $individual['email'] ?? '',
                $individual['cell_phone'] ?? '',
                $individual['birthday'] ?? '',
                $individual['baptism'] ?? ''
            );
            $homephone = false;
        } else {
            $left_side = $placeholder;
            $formatted_family .= sprintf($format_string, $left_side,
                $individual['first_name'] ?? '',
                $individual['last_name'] ?? '',
                $individual['email'] ?? '',
                $individual['cell_phone'] ?? '',
                $individual['birthday'] ?? '',
                $individual['baptism'] ?? ''
            );
        }

        $ictr++;
    }
    if ( !$addr1 && !$addr2 && $city)  {
    $left_side = sprintf("%s, %s %s", $family['city'], $family['state'], $family['zip']);
    $formatted_family .= sprintf($format_string, $left_side,
            $individual['first_name'] ?? '',
            $individual['last_name'] ?? '',
            $individual['email'] ?? '',
            $individual['cell_phone'] ?? '',
            $individual['birthday'] ?? '',
            $individual['baptism'] ?? ''
        );
        $city = false;
    }
    if ( !$addr1 && $addr2 && $city) {
    $left_side = sprintf("%s, %s %s", $family['city'], $family['state'], $family['zip']);
    $formatted_family .= sprintf($format_string, $left_side,
            $individual['first_name'] ?? '',
            $individual['last_name'] ?? '',
            $individual['email'] ?? '',
            $individual['cell_phone'] ?? '',
            $individual['birthday'] ?? '',
            $individual['baptism'] ?? ''
        );
        $city = false;
        $addr2 = false;
    }
    if ( !$addr1 && !$addr2 && !$city && $homephone) {
    $left_side = sprintf("Home: %s", $family['homephone']);
    $formatted_family .= sprintf($format_string, $left_side,
            $individual['first_name'] ?? '',
            $individual['last_name'] ?? '',
            $individual['email'] ?? '',
            $individual['cell_phone'] ?? '',
            $individual['birthday'] ?? '',
            $individual['baptism'] ?? ''
        );
        $homephone = false;
    }


    return $formatted_family;
}

function format_family_listing_for_print($family, $members) {
    return TRUE;
}