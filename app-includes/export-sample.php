<?php
/**
 * Export sample directory data to CSV
 */

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="directory_export_sample.csv"');
$output = fopen('php://output', 'w');
$sampleData = [
    "familyname,fname1,fname2,lname2,address,address2,city,state,zip,homephone,cellphone1,cellphone2,email1,email2,bday1,bday2,bap1,bap2,annday,othername3,otherbday3,otherbap3,othercell3,otherem3,othername4,otherbday4,otherbap4,othercell4,otherem4,othername5,otherbday5,otherbap5,othercell5,otherem5,othername6,otherbday6,otherbap6,othercell6,otherem6,othername7,otherbday7,otherbap7,othercell7,otherem7,othername8,otherbday8,otherbap8,othercell8,otherem8,othername9,otherbday9,otherbap9,othercell9,otherem9,",
    "Sample,Joe,Jane,Smith,123 Main St,2nd Floor,Parkesburg, PA, 19365,610-555-1234,717-555-5678,484-555-0000,joe@example.com,jane@example.com,01/01/1980,02/02/1981,03/03,04/04,05/05,Other3,01/01, 02/01, 610-555-1111, other3_email@example.com, Other4,01/02, 02/02, 610-555-2222, other4_email@example.com, Other5 Other5-last,01/03,02/03,610-555-3333,other5_email@example.com, Other6 Other6-Last,01/06, 02/06, 610-555-6666, other6_email@example.com, Other7,01/07, 02/07,484-777-7777, other7_email@example.com, Other8,01/08, 02/08,484-798-8888, other8_email@example.com,Other9,Other9-Last,01/09,02/09,484-555-9999, other9_email@example.com"
];

foreach ($sampleData as $line) {
    fputcsv($output, explode(",", $line));
}
fclose($output);
