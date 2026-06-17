<?php
/**
 * COTA Membership Directory RTF Generation Script
 * This script prints the COTA directory as a text formatted set of standard
 * pages in rich text format (RTF). This format is suitable for parsing by
 * external applications.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/class-membership-directory-printer.php';
global $cota_db, $connect, $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'headers.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-family-listing.php';

$printBooklet = new Membership_Directory_Printer();

// Grab all the intro files from the uploads directory
$intro_files = glob( '../uploads/intro*.txt' );
$intro_files_count = count( $intro_files );
$output_basename = '/downloads/directory_booklet_' . date('Y-m-d') . '.rtf';
// Ensure the downloads directory exists
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/downloads')) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . '/downloads', 0755, true);
}
$output_filename = $_SERVER['DOCUMENT_ROOT'] . $output_basename;

$rtf_content = $printBooklet->generateRTFHeader();

// Add intro page(s) to output
$rtf_content .= $printBooklet->print_intro_pages( $intro_files_count );

$all_families = $cota_db->read_family_database();

// Add family listings
	$rtf_content .= $printBooklet->formatFamilyListings($all_families) . "\\pard\\page\\par";

$rtf_content .= "}";

file_put_contents($output_filename, $rtf_content);

// Closing the file 
$cota_db->close_connection();

// Echo header
echo cota_page_header();

// Dump out remainder of import page. 
echo "<div id='cota-print' class='container'>";
echo "<h2>RTF file generated successfully!</h2>";
echo "<h4>File: " . basename($output_filename) . "</h2>";
echo "<button class='cota-print' type='button' ><a href='.." . $output_basename . "' download>Download File</a></button>";
echo '</div><body></html>';