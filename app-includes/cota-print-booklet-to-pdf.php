<?php
require_once '../app-includes/cota-database-functions.php';
require_once '../app-includes/cota-format-family-listing.php';
require_once '../app-includes/cota-print.php';
require_once '../app-includes/cota-class-print-booklet.php';

// Create a new PDF instance
// $pdf = new FPDF('L', 'in', [8.5, 11]); // Landscape, Inches, Letter Size
// $pdf = new PDF('L', 'in', [8.5, 5.5]); // Landscape, Inches, Half-page Letter Size
$pdf = new PDF(); // Landscape, Inches, Half-page Letter Size

// Add the cover
// $pdf->AddPage();
// $pdf->SetFont('Arial', 'B', 24);
// // $pdf->Cell(0,10,' - '.$i,0,1);  // Line number for debugging
// $pdf->SetY(2); // Set Y position for the title
// $pdf->SetFont('Arial', 'B', 20);    
// $pdf->MultiCell(0, 1, "Church of the Ascension\nMembership Directory\n2025", 0, 'C');


// Load and insert pages
for ($i = 1; $i <= 3; $i++) {
    $pdf->PrintChapter($i,'intro'.$i.'.txt','../uploads/intro'.$i.'.txt');
    // $pdf->MultiCell(0, 0.3, $text);
}

// 4. Retrieve and Format Membership Entries
// Fetch data from your MySQL database and format it into an address label style layout

$db = new COTA_Database();
$conn = $db->get_connection();
               
$families = $conn->query("SELECT * FROM families ORDER BY `familyname`");
$num_families = $families->num_rows;
$ictr = 1;

$pdf->AddPage(); // Start the alpha listing on a new page
$pageEntries = 0;
while ($family = $families->fetch_assoc()) {
    // Get family members
    $individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id']);

    // Build the family string using the new method
    // $familyString = $pdf->BuildFamilyString($family, $individuals);
    $familyString = cota_format_family_listing_for_print($family, $individuals);
    echo nl2br($familyString); // Debugging output
    // $familyString = $pdf->BuildFamilyString($family, $individuals);
    // Print the family string, avoiding page breaks within a family entry
    $pdf->PrintFamilyString($familyString);

    $pageEntries++;
}



// Add the rear cover
// $pdf->SetY(-1);
// $pdf->SetFont('Arial', 'I', 20);
// $pdf->MultiCell(0, 1, "Church of the Ascension, Parkesburg, PA\nPrinted " . date('F j, Y'), 0, 'C');
$pdf->cota_back_cover(1, 'Back Cover');

// Output the PDF
$pdf->Output('F', '../uploads/membership_directory.pdf'); // Save to server
$pdf->Output('I'); // Still display in browser if you want



$db->close_connection();

echo "<h2>PDF file generated successfully!</h2>";
echo "<p><a href='../uploads/membership_directory.pdf' download>Download PDF Directory</a></p>";
echo "<p><a href='../../index.php'>Return to main menu.</a></p>";