<?php
require_once 'Database.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="directory_export.csv"');

$db = new Database();
$conn = $db->getConnection();
$output = fopen('php://output', 'w');

fputcsv($output, ["Family Name", "Member Name", "Address", "Address 2", "City", "State", "Zip", "Home Phone", "Cell Phone 1", "Email 1", "Birthday 1", "Cell Phone 2", "Email 2", "Birthday 2", "Anniversary"]);

$families = $conn->query("SELECT * FROM families");
while ($family = $families->fetch_assoc()) {

    // Output the primary members from the families table
    fputcsv($output, [
        $family['family_name'], 
        $family['primary_name_1'] . ' & ' . $family['primary_name_2'],
        $family['address'],
        $family['address_2'],
        $family['city'],
        $family['state'],
        $family['zip'],
        $family['home_phone'],
        $family['primary_cell_1'], 
        $family['primary_email_1'], 
        $family['primary_bday_1'],
        $family['primary_cell_2'], 
        $family['primary_email_2'], 
        $family['primary_bday_2'],
        $family['anniversary']
    ]);

    // Fetch members of this family
    $members = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id']);
    while ($member = $members->fetch_assoc()) {

        fputcsv($output, [
            $family['family_name'], 
            $member['first_name'] . ' ' . $member['last_name'],
            $member['cell_phone'],
            '','','','','','', 
            $member['email'], 
            $member['birthday']
        ]);
    }
}
fclose($output);
$db->closeConnection();

function formatDate($date) {
    if (preg_match('/^(\\d{1,2})\/(\\d{1,2})(?:\/\\d{2,4})?$/', $date, $matches)) {
        return sprintf("%02d/%02d", $matches[1], $matches[2]); // Normalize MM/DD format
    }
    return ""; // Return empty if invalid
}
?>