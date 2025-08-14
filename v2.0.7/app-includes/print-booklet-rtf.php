<?php

global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'print.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

$printBooklet = new MembershipDirectoryPrinter();

$introFiles = ['../uploads/intro1.txt', '../uploads/intro2.txt', '../uploads/intro3.txt'];
$output_basename = '/downloads/directory_booklet_' . date('Y-m-d') . '.rtf';
// Ensure the downloads directory exists
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/downloads')) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . '/downloads', 0755, true);
}
$output_filename = $_SERVER['DOCUMENT_ROOT'] . $output_basename;

$rtfContent = $printBooklet->generateRTFHeader();

// Add intro page(s) to output
$rtfContent .= $printBooklet->print_intro_pages(3);

$all_families = $cotadb->read_family_database();

// Add family listings
	$rtfContent .= $printBooklet->formatFamilyListings($all_families) . "\\pard\\page\\par";

$rtfContent .= "}";

file_put_contents($output_filename, $rtfContent);

// Closing the file 
$cotadb->close_connection();

// Echo header
echo cota_page_header();

// Dump out remainder of import page. 
echo "<div id='cota-print' class='container'>";
echo "<h2>RTF file generated successfully!</h2>";
echo "<h4>File: " . basename($output_filename) . "</h2>";
echo "<button class='cota-print' type='button' ><a href='.." . $output_basename . "' download>Download File</a></button>";
echo '</div><body></html>';