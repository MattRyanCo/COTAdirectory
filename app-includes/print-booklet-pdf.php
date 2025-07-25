<?php
/**
 * COTA Membership Directory PDF Generation Script
 * This script prints the COTA directory as a linear set of standard 
 * pages (1 through x, with 1 being the front cover and x being the last printed page). 
 * It does not account for 2-sided booklet printing, but rather prints 
 * assuming that the PDF will then be processed by another app that will convert it to 
 * 2-sided, 4 to a page format. 
 */

global $cotadb, $conn, $cota_constants, $header_height;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'print.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'class-print-booklet.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

// Create a new PDF instance
// $pdf = new PDF(); // Landscape, Inches, Half-page Letter Size
$pdf = new PDF('P', 'in', 'Letter'); // Portrait, Inches, Letter Size
$pdf->AddPage();

$title = 'Church of the Ascension Directory 2025';
$author = 'Vestry & Wardens of Church of the Ascension, Parkesburg';
$pdf->SetTitle($title);
$pdf->SetAuthor($author);

$left_margin = $top_margin = $right_margin = 0.5;
$pdf->SetMargins($left_margin, $top_margin, $right_margin);
$pdf->SetAutoPageBreak(true, 2*$top_margin);
// $pdf->SetAutoPageBreak(false);
$pdf->SetFont('Arial', '', 12);
$logoFile = '../app-assets/images/cota-logo.png';
$pdf->front_cover( $title, $author, $logoFile ); // Add front cover with logo

// Retrieve and Format Membership Entries
$families = $cotadb->read_family_database();
$num_families = $families->num_rows;
$field_positions = $field_widths = $field_info = [];

// Load and insert static pages.
for ($i = 1; $i <= 3; $i++) {
    $pdf->PrintChapter($i,'intro'.$i.'.txt','../uploads/intro'.$i.'.txt');
}

$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->center_this_text('Family & Members Listing - '.$num_families . ' families', 0.75);
$pdf->SetFont('Arial', '', 10);
$pdf->center_this_text('Other misc info may be shared here about the membership numbers.', 3);


$pdf->SetFont('Arial', '', 10);  // Reset to normal font
$pdf->AddPage(); // Start the alpha listing on a new page
$line_height = 0.25;  // Set basic line height

// Get things started with headings below titles. 
$field_info = $pdf->print_family_array_headings( TRUE );  // print headings

while ($family = $families->fetch_assoc()) {
    // Get family members
    $individuals = $cotadb->read_members_of_family( $family['id'] );
    $family_array = cota_format_family_listing_for_print($family, $individuals);

    // $family_array[0][0] = number of listing lines on left
    // $family_array[0][1] = number of listing lines on right
    $family_listing_height_in_lines = max($family_array[0][0], $family_array[0][1]);

    if ( $pdf->enough_room_for_family( $family_listing_height_in_lines, $line_height ) ) {
        // Enough space to print out this family. 
        $pdf->SetFont('Arial', '', 10); // Ensure font is reset before headings
        $pdf->print_family_array($family_array, $field_info );
    } else {
        // Not enough room for family. 
        // Add new page. 
        // Print out headings on new page
        $pdf->SetFont('Arial', '', 10); // Ensure font is reset before headings
        $pdf->AddPage();
        $pdf->print_family_array($family_array, $field_info );
    }

}

// Add the rear cover
$pdf->back_cover(1, 'Back Cover');

$output_basename = '/downloads/directory_booklet_' . date('Y-m-d') . '.pdf';
// Ensure the downloads directory exists
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/downloads')) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . '/downloads', 0755, true);
}
$output_filename = $_SERVER['DOCUMENT_ROOT'] . $output_basename;

// Output the PDF
$pdf->Output('F', $output_filename); // Save to server
// $pdf->Output('I'); // Still display in browser if you want

$cotadb->close_connection();

// Echo header
echo cota_page_header();

// Dump out remainder of import page. 
echo "<div id='cota-print' class='container'>";
echo "<h2>PDF file generated successfully!</h2>";
echo "<h4>File: " . basename($output_filename) . "</h2>";
echo "<button class='cota-print' type='button' ><a href='.." . $output_basename . "' download >Download File</a></button>";
echo '</div></body></html>';