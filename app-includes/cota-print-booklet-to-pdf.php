<?php
/**
 * COTA Membership Directory PDF Generation Script
 * This script prints the COTA directory as a linear set of standard 
 * pages (1 through x, with 1 being the front cover and x being the last printed page). 
 * It does not account for 2-sided booklet printing, but rather prints 
 * assuming that the PDF will then be processed by another app that will convert it to 
 * 2-sided, 4 to a page format. 
 */
require_once '../app-includes/cota-database-functions.php';
require_once '../app-includes/cota-format-family-listing.php';
require_once '../app-includes/cota-format-family-listing-for-fpdf.php';
require_once '../app-includes/cota-print.php';
require_once '../app-includes/cota-class-print-booklet.php';

// Create a new PDF instance
// $pdf = new PDF(); // Landscape, Inches, Half-page Letter Size
$pdf = new PDF('P', 'in', 'Letter'); // Portrait, Inches, Letter Size
$pdf->AddPage();
$title = 'Church of the Ascension Directory 2025';
$author = 'Vestry & Wardens of Church of the Ascension, Parkesburg';
$pdf->SetTitle($title);
$pdf->SetAuthor($author);

$w=$pdf->GetStringWidth($title)+6;


$pdf->SetMargins(0.5, 0.5, 0.5);
$pdf->SetAutoPageBreak(true, 0.5);
$pdf->SetFont('Arial', '', 12);

$logoFile = '../app-assets/images/cota-logo.png';
$pdf->cota_front_cover( $title, $author, $logoFile, 5, 50, 200 ); // Add front cover with logo



// Load and insert static pages.
for ($i = 1; $i <= 3; $i++) {
    $pdf->PrintChapter($i,'intro'.$i.'.txt','../uploads/intro'.$i.'.txt');
    // $pdf->MultiCell(0, 0.3, $text);
}

// Retrieve and Format Membership Entries
// Fetch data from your MySQL database and format it into an address label style layout

$db = new COTA_Database();
$conn = $db->get_connection();
               
$families = $conn->query("SELECT * FROM families ORDER BY `familyname`");
$num_families = $families->num_rows;
$ictr = 1;

$pdf->AddPage(); // Start the alpha listing on a new page
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'COTA Directory', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Member Listing - '.$num_families . ' families', 0, 1, 'C');
$pdf->Ln(5);

$pageEntries = 0;
while ($family = $families->fetch_assoc()) {
    // Get family members
    $individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id']);

    // Build the family string using the new method
    // $familyString = $pdf->BuildFamilyString($family, $individuals);
    // var_dump($family); // Debugging output
    $familyArray = cota_format_family_listing_for_print($family, $individuals);
    // var_dump($familyArray); // Debugging output

    // $familyString = cota_format_family_listing_for_fpdf($pdf, $family, $individuals);
    
    // echo nl2br($familyString); // Debugging output
    // $familyString = $pdf->BuildFamilyString($family, $individuals);
    // Print the family string, avoiding page breaks within a family entry
    // $pdf->PrintFamilyString($familyString);
    $pdf->PrintFamilyArray($familyArray);

    $pageEntries++;
}

// Add the rear cover
$pdf->cota_back_cover(1, 'Back Cover');

// Output the PDF
$pdf->Output('F', '../uploads/membership_directory.pdf'); // Save to server
$pdf->Output('I'); // Still display in browser if you want

$db->close_connection();

echo "<h2>PDF file generated successfully!</h2>";
echo "<p><a href='../uploads/membership_directory.pdf' download>Download PDF Directory</a></p>";
echo "<p><a href='../../index.php'>Return to main menu.</a></p>";