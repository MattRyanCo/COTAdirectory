<?php
/**
 * Export directory data as email list CSV
 */

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect,  $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="cota_directory_email_export.csv"');
$output = fopen('php://output', 'w');

$header = [
    "first_name",
    "last_name",
    "email_address"
];

fputcsv( $output, $header );

$families = $connect->query( 'SELECT * FROM families' );
while ( $family = $families->fetch_assoc() ) {
    // Fetch members of this family
    $members = $connect->query( 'SELECT * FROM members WHERE family_id = ' . $family['id'] );
    // Loop through each member and output their data
    while ( $member = $members->fetch_assoc() ) {
        if ( empty ($member['last_name'] )) {
            $member['last_name'] = $family['familyname'];
        }
        if ( ! empty( $member['email'] ) ) {
            fputcsv( $output, [ $member['first_name'], $member['last_name'], $member['email'] ] );
        }
    }
}

fclose( $output );
$cota_db->close_connection();