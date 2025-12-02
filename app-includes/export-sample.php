<?php
/**
 * Export sample directory data to CSV
 */

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="directory_export_sample.csv"');
$output = fopen('php://output', 'w');
$sampleData = [
    "familyname,address,address2,city,state,zip,homephone,fname1,lname1,bday1,bap1,cell1,email1,fname2,lname2,bday2,bap2,cell2,email2,annday,fname3,lname3,bday3,bap3,cell3,email3,fname4,lname4,bday4,bap4,cell4,email4,fname5,lname5,bday5,bap5,cell5,email5,fname6,lname6,bday6,bap6,cell6,email6,fname7,lname7,bday7,bap7,cell7,email7,fname8,lname8,bday8,bap8,cell8,email8,fname9,lname9,bday9,bap9,cell9,email9",
    "Zook,123 Main St,2nd Floor,Parkesburg, PA, 19365,610-555-1234,joe,,01/01/1980,02/02/1981,610-555-9999,joe@example.com,jane, jackson,03/03,04/04,610-555-5678,jane@example.com,01/02/2023,joey,,01/01, 02/01, 610-555-1111, name3_email@example.com, name4,,01/02, 02/02, 610-555-2222, name4_email@example.com, name5, name5last,01/03,02/03,610-555-3333,name5_email@example.com, name6, name6last,01/06, 02/06, 610-555-6666, name6_email@example.com, name7,,01/07, 02/07,484-777-7777, name7_email@example.com, name8,,01/08, 02/08,484-798-8888, name8_email@example.com,name9,name9Last,01/09,02/09,484-555-9999, name9_email@example.com"
];

foreach ($sampleData as $line) {
    fputcsv($output, explode(",", $line));
}
fclose($output);
