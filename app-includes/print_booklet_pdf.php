<?php
require_once '../app-includes/database_functions.php';
require_once '../app-includes/format_family_listing.php';
require_once '../app-includes/print.php';
require_once '../libraries/fpdf/fpdf.php';

$pdf = new FPDF('L', 'in', [8.5, 11]); // Landscape, Inches, Letter Size
$pdf->SetAutoPageBreak(true, 0.5); // Ensure margins

class PDF extends FPDF
{
    function Footer()
    {
        // Go to 1.5 cm from bottom
        $this->SetY(-1);
        // Select Arial italic 10
        $this->SetFont('Arial', 'I', 10);
        // Print centered page number
        $this->Cell(0, 0.3, "Page {$pdf->PageNo()} - " . date('F j, Y'), 0, 0, 'C');
    }
}


// Add the cover
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 24);
$pdf->MultiCell(0, 1, "Church of the Ascension\nMembership Directory\n2025", 0, 'C');



// Load and insert pages
for ($i = 1; $i <= 3; $i++) {
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $text = file_get_contents("../uploads/intro{$i}.txt");
    $pdf->MultiCell(0, 0.3, $text);
}

// 4. Retrieve and Format Membership Entries
// Fetch data from your MySQL database and format it into an address label style layout

$db = new Database();
$conn = $db->getConnection();
               
$families = $conn->query("SELECT * FROM families ORDER BY `familyname`");
$num_families = $families->num_rows;
$ictr = 1;



$pageEntries = 0;
while ($family = $families->fetch_assoc()) {




	// Get family members
	$individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id']);  // no ordering

    if ($pageEntries % 4 == 0) $pdf->AddPage(); // New page every 4 entries
	if ($pageEntries > 0 && $pageEntries % 4 == 0) {
		$pdf->Ln(0.5); // Add some space between entries
	}
    $pdf->SetFont('Arial', '', 12);
    // $pdf->MultiCell(0, 0.5, "{$family['familyname']}\n{$family['address']}", 0, 1);
	format_family_listing_for_print($pdf, $family, $individuals);

    $pageEntries++;
}



// Add the rear cover
$pdf->SetY(-1);
$pdf->SetFont('Arial', 'I', 20);
$pdf->MultiCell(0, 1, "Church of the Ascension, Parkesburg, PA\nPrinted " . date('F j, Y'), 0, 'C');


// Output the PDF
$pdf->Output('F', '../uploads/membership_directory.pdf'); // Save to server
$pdf->Output('I'); // Still display in browser if you want



$db->closeConnection();

echo "<h2>PDF file generated successfully!</h2>";
echo "<p><a href='../uploads/membership_directory.pdf' download>Download PDF Directory</a></p>";
echo "<p><a href='../../index.php'>Return to main menu.</a></p>";