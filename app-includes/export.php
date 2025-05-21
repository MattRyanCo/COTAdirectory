<?php
require_once '../app-includes/database_functions.php';
// echo nl2br(__FILE__ . ' loaded' . PHP_EOL);


header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="directory_export.csv"');

$db = new Database();
$conn = $db->getConnection();
$output = fopen('php://output', 'w');

fputcsv($output, [
    "familyname",
    "name1",
    "name2",
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
    "annday",
    'othername1',
    'otherbday1',
    'otherbap1',
    'othercell1',
    'otherem1',
    'othername2',
    'otherbday2',
    'otherbap2',
    'othercell2',
    'otherem2',
    'othername3',
    'otherbday3',
    'otherbap3',
    'othercell3',
    'otherem3',
    'othername4',
    'otherbday4',
    'otherbap4',
    'othercell4',
    'otherem4',
    'othername5',
    'otherbday5',
    'otherbap5',
    'othercell5',
    'otherem5' 
]);

$families = $conn->query("SELECT * FROM families");
while ($family = $families->fetch_assoc()) {

    // Output the primary members from the families table
    // fputcsv($output, [
    //     $family['familyname'],
    //     $family['name1'],
    //     $family['name2'],
    //     $family['address'],
    //     $family['address2'],
    //     $family['city'],
    //     $family['state'],
    //     $family['zip'],
    //     $family['homephone'],
    //     $family['cellphone1'], 
    //     $family['email1'], 
    //     $family['bday1'],
    //     $family['cellphone2'], 
    //     $family['email2'], 
    //     $family['bday2'],
    //     $family['annday']
    // ]);
    $one_family = sprintf("%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s", 
        $family['familyname'],
        $family['name1'],
        $family['name2'],
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
    while ($member = $members->fetch_assoc()) {

        $one_family .= sprintf("%s,%s,%s,%s,%s,%s",$member['first_name'],$member['last_name'],$member['cell_phone'],$member['email'], $member['birthday'], $member['baptism']);
    }
    // write out all data on a single csv record
    fputcsv($output, explode(",", $one_family));
}
fclose($output);
$db->closeConnection();

?>