<?php
/**
 * Export directory data to CSV
 */
global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';


header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="directory_export.csv"');

// $db = new COTA_Database();
// $conn = $db->get_connection();
$output = fopen('php://output', 'w');

// Static columns before dynamic member columns
$header = [
    "familyname",
    "fname1",
    "fname2",
    "address",
    "address2",
    "city",
    "state",
    "zip",
    "homephone",
    "cellphone1",
    "cellphone2",
    "email1",
    "email2",
    "bday1",
    "bday2",
    "bap1",
    "bap2",
    "annday"
];

// Add dynamic member columns to header row
$max_members = (isset($cota_constants->MAX_FAMILY_MEMBER))
    ? (int)$cota_constants->MAX_FAMILY_MEMBER
    : 9;
for ($i = 3; $i <= $max_members; $i++) {
    $header[] = "otherfname{$i}";
    $header[] = "otherlname{$i}";
    $header[] = "otherbday{$i}";
    $header[] = "otherbap{$i}";
    $header[] = "othercell{$i}";
    $header[] = "otherem{$i}";
}

fputcsv($output, $header);

$families = $conn->query("SELECT * FROM families");
while ($family = $families->fetch_assoc()) {
    // Initialize family data
    $one_family = sprintf("%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s", 
        $family['familyname'],
        $family['fname1'],
        $family['fname2'],
        $family['address'],
        $family['address2'],
        $family['city'],
        $family['state'],
        $family['zip'],
        $family['homephone'],
        $family['cellphone1'], 
        $family['cellphone2'],
        $family['email1'], 
        $family['email2'], 
        $family['bday1'],
        $family['bday2'],
        $family['bap1'],
        $family['bap2'],
        $family['annday']);

    // Fetch members of this family
    $members = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id']);
    $all_members = [];
    $one_family .= ',';
    // Loop through each member and append their data
    while ($member = $members->fetch_assoc()) {

        $one_family .= sprintf("%s %s,%s,%s,%s,%s,",$member['first_name'],$member['last_name'], $member['birthday'], $member['baptism'] ,$member['cell_phone'],$member['email']);
    }
    // write out all data on a single csv record
    fputcsv($output, explode(",", $one_family));
}
fclose($output);
$cotadb->close_connection();
