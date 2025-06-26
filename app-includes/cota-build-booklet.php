<?php
/* COTA Booklet Printing Script
 * This script generates a booklet order for printing pages in a specific order.
 * It uses the FPDF library to create a PDF file with the booklet layout.
 */

require_once '../app-includes/cota-class-print-booklet.php';
global $title;

function generateBookletOrder($totalPages) {
    // Ensure total pages is a multiple of 4, as each sheet contains 4 pages, front and back cover included 
    $pagesToPrint = ($totalPages % 4 === 0) ? $totalPages : $totalPages + (4 - ($totalPages % 4));

    $bookletOrder = [];
    $frontCover = 1;
    $backCover = $pagesToPrint;
    for ($i = 0; $i < $pagesToPrint / 2; $i++) {
        $leftPage = $pagesToPrint - $i;  // Back side of a sheet
        $rightPage = $i + 1;             // Front side of a sheet

        $bookletOrder[] = [$leftPage, $rightPage]; // Front of sheet
        $bookletOrder[] = [$rightPage + 1, $leftPage - 1]; // Back of sheet
    }

    return $bookletOrder;
}
$title = 'Church of the Ascension Directory 2025';
$buildpdf = new PDF('P', 'in', 'Letter'); // Portrait, Inches, Letter Size
$author = 'Vestry & Wardens of Church of the Ascension, Parkesburg';
$buildpdf->SetTitle($title);
$buildpdf->SetAuthor($author);
$w=$buildpdf->GetStringWidth($title)+6;
print_r($w); // Debugging: Show the width of the title



$totalPages = 16; // Change this dynamically based on actual content
$pages = generateBookletOrder($totalPages);
// var_dump($pages); // Debugging: Show the generated page pairs

$buildpdf->SetMargins(0.5, 0.5, 0.5);
$buildpdf->SetAutoPageBreak(true, 0.5);
$buildpdf->SetFont('Arial', '', 12);

$logoFile = '../app-assets/images/cota-logo.png';
$buildpdf->cota_front_cover( $title, $author, $logoFile ); // Add front cover with logo

// $buildpdf->dummy_up_pages( $buildpdf, $pages ); // Add dummy pages based on the generated order

$buildpdf->SetFont('Arial', 'B', 14);
$buildpdf->Cell(0, 10, 'COTA Directory', 0, 1, 'C');
$buildpdf->SetFont('Arial', '', 12);
$buildpdf->Cell(0, 10, 'Member Listing - 36 Families', 0, 1, 'C');
$buildpdf->Ln(5);

// loop through families
// Family Heading
$buildpdf->SetFont('Arial', 'B', 11);
$buildpdf->Cell(0, 8, 'Family: Ammon', 0, 1);
$buildpdf->SetFont('Arial', '', 10);
$buildpdf->Cell(0, 6, '62 Compass Rd, Parkesburg, PA 19365', 0, 1);
$buildpdf->Cell(0, 6, 'Home: 484-467-1471', 0, 1);
$buildpdf->Ln(2);

// Set up column widths
$w = [25, 5, 5, 35, 35, 15, 5, 5]; // Widths for each column
// Member Table Headers
$buildpdf->SetFont('Arial', 'B', 10);
$buildpdf->Cell( $w[0], 6, ' ', 1);
$buildpdf->Cell( $w[1], 6, ' ', 1);
$buildpdf->Cell( $w[2], 6, ' ', 1);
$buildpdf->Cell($w[3], 6, 'Name', 1);
$buildpdf->Cell($w[5], 6, 'Email', 1);
$buildpdf->Cell($w[6], 6, 'Cell', 1);
$buildpdf->Cell($w[7], 6, 'DoB', 1);
$buildpdf->Cell($w[8], 6, 'DoBaptism', 1);
$buildpdf->Ln();

// Sample Family Members
$buildpdf->SetFont('Arial', '', 10);
$members = [
  ['','','','Barb Jo', 'bjammon@comcast.net', '484-467-1471', '09/01', '10/01'],
  ['','','','Jim', 'jim@mail.com', '484-457-1472', '07/30', '08/30'],
];

foreach ($members as $m) {
    $buildpdf->Cell(5, 6, $m[0], 0);
    $buildpdf->Cell(5, 6, $m[1], 0);
    $buildpdf->Cell(5, 6, $m[2], 0);
  $buildpdf->Cell(35, 6, $m[3], 0);
  $buildpdf->Cell(55, 6, $m[4], 0);
  $buildpdf->Cell(30, 6, $m[5], 0);
  $buildpdf->Cell(20, 6, $m[6], 0);
  $buildpdf->Cell(30, 6, $m[7], 0);
  $buildpdf->Ln();
}

$buildpdf->Ln(4);



$buildpdf->cota_back_cover( 'Back Cover' );

// Output PDF
$buildpdf->Output('F', '../uploads/membership_directory.pdf');
$buildpdf->Output('I');