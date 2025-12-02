<?php
/**
 * Export directory data to CSV
 */

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect,  $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="directory_export.csv"');
$output = fopen('php://output', 'w');

// Static columns before dynamic member columns
$header = [
    "familyname",
    "address",
    "address2",
    "city",
    "state",
    "zip",
    "homephone"
];

// Add dynamic member columns to header row
$max_members = (isset($cota_app_settings->MAX_FAMILY_MEMBER))
    ? (int)$cota_app_settings->MAX_FAMILY_MEMBER
    : 9;
for ($i = 1; $i <= $max_members; $i++) {
    $header[] = "fname{$i}";
    $header[] = "lname{$i}";
    $header[] = "bday{$i}";
    $header[] = "bap{$i}";
    $header[] = "cell{$i}";
    $header[] = "email{$i}";
}
$header[] = "annday";

fputcsv($output, $header);

$families = $connect->query("SELECT * FROM families");
while ($family = $families->fetch_assoc()) {
    // Initialize family data
    $one_family = sprintf("%s,%s,%s,%s,%s,%s,%s", 
        $family['familyname'],
        $family['address'],
        $family['address2'],
        $family['city'],
        $family['state'],
        $family['zip'],
        $family['homephone']);

    // Fetch members of this family
    $members = $connect->query("SELECT * FROM members WHERE family_id = " . $family['id']);
    $all_members = [];
    $one_family .= ',';
    // Loop through each member and append their data
    while ($member = $members->fetch_assoc()) {

        $one_family .= sprintf("%s,%s,%s,%s,%s,%s,",$member['first_name'],$member['last_name'], $member['birthday'], $member['baptism'] ,$member['cell_phone'],$member['email']);
    }
    // write out all data on a single csv record
    fputcsv($output, explode(",", $one_family));
}
fclose($output);
$cota_db->close_connection();
