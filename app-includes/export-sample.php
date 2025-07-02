<?php
/**
 * Export sample directory data to CSV
 */
// require_once '../app-includes/database-functions.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="directory_export_sample.csv"');
$output = fopen('php://output', 'w');
$sampleData = [
    "familyname,name1,name2,address,address2,city,state,zip,homephone,cellphone1,cellphone2,email1,email2,bday1,bday2,bap1,bap2,annday,othername1,otherbday1,otherbap1,othercell1,otherem1,othername2,otherbday2,otherbap2,othercell2,otherem2,othername3,otherbday3,otherbap3,othercell3,otherem3,othername4,otherbday4,otherbap4,othercell4,otherem4,othername5,otherbday5,otherbap5,othercell5,otherem5,othername6,otherbday6,otherbap6,othercell6,otherem6,othername7,otherbday7,otherbap7,othercell7,otherem7",
    "Johnston,Joe,Jane,123 Main St,2nd Floor,Parkesburg, PA, 19365,610-555-1234,717-555-5678,484-555-0000,joe@example.com,jane@example.com,01/01,02/02,03/03,04/04,05/05,Other1,01/01, 02/01, 610-555-1111, other1_email@example.com, Other2,01/02, 02/02, 610-555-2222, other2_email@example.com, Other3 Other3-last,01/03,02/03,610-555-3333,other3_email@example.com, Other4 Other4-Last,01/04, 02/04, 610-555-4444, other4_email@example.com, Other5,01/05, 02/05,484-555-5555, other5_email@example.com, Other6,01/06, 02/06,484-798-6666, other6_email@example.com,Other7 Other7-Last,01/07,02/07,484-555-7777, other7_email@example.com"
];

foreach ($sampleData as $line) {
    fputcsv($output, explode(",", $line));
}
fclose($output);
