<?php
require_once '../app-includes/database-functions.php';
require_once '../app-includes/print.php';
require_once '../app-includes/settings.php';

$printBooklet = new MembershipDirectoryPrinter();

$introFiles = ['../uploads/intro1.txt', '../uploads/intro2.txt', '../uploads/intro3.txt'];
// $outputFile = '../downloads/membership_directory.rtf';
$output_basename = '/downloads/directory_booklet_' . date('Y-m-d') . '.rtf';
// Ensure the downloads directory exists
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/downloads')) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . '/downloads', 0755, true);
}
$output_filename = $_SERVER['DOCUMENT_ROOT'] . $output_basename;


$db = new COTA_Database();
$rtfContent = $printBooklet->generateRTFHeader();

// Add intro pages
foreach ($introFiles as $file) {
	if (file_exists($file)) {
		$rtfContent .= $printBooklet->formatText(file_get_contents($file)) . "\\pard\\page\\par";
	}
}
$all_families = $db->read_family_database();

// Add family listings
// foreach ($all_families as $family) {
	$rtfContent .= $printBooklet->formatFamilyListings($all_families) . "\\pard\\page\\par";
// }

$rtfContent .= "}";

file_put_contents($output_filename, $rtfContent);

// Closing the file 
// fclose($output); 
$db->close_connection();

// Echo header
echo cota_page_header();

// Dump out remainder of import page. 
echo "<div id='cota-print' class='container'>";
echo "<h2>RTF file generated successfully!</h2>";
echo "<h4>File: " . basename($output_filename) . "</h2>";
echo "<button class='button' type='button' ><a href='.." . $output_basename . "' download>Download File</a></button>";
echo '</div><body></html>';