<?php
/* * COTA Booklet Printing Script
 * This script generates a booklet order for printing pages in a specific order.
 * It uses the FPDF library to create a PDF file with the booklet layout.
 */

require_once '../app-includes/cota-class-print-booklet.php';


function generateBookletOrder($totalPages) {
    // Ensure total pages is a multiple of 4, as each sheet contains 4 pages, front and back cover included 
    $pagesToPrint = ($totalPages % 4 === 0) ? $totalPages : $totalPages + (4 - ($totalPages % 4));

    $bookletOrder = [];
    $frontCover = 1;
    $backCover = $pagesToPrint;
    // var_dump($pagesToPrint); // Debugging: Show total pages to print
    for ($i = 0; $i < $pagesToPrint / 2; $i++) {
        $leftPage = $pagesToPrint - $i;  // Back side of a sheet
        $rightPage = $i + 1;             // Front side of a sheet

        $bookletOrder[] = [$leftPage, $rightPage]; // Front of sheet
        $bookletOrder[] = [$rightPage + 1, $leftPage - 1]; // Back of sheet
    }

    return $bookletOrder;
}

$buildpdf = new PDF();
$title = 'Church of the Ascension Directory 2025';
$author = 'Vestry & Wardens of Church of the Ascension, Parkesburg';
$buildpdf->SetTitle($title);
$buildpdf->SetAuthor($author);


$totalPages = 16; // Change this dynamically based on actual content
$pages = generateBookletOrder($totalPages);
// var_dump($pages); // Debugging: Show the generated page pairs

$buildpdf->SetMargins(0.5, 0.5, 0.5);
$buildpdf->SetAutoPageBreak(true, 0.5);
$buildpdf->SetFont('Arial', '', 12);

$logoFile = '../app-assets/images/cota-logo.png';
$buildpdf->cota_front_cover( $title, $author, $logoFile, 5, 50, 200 ); // Add front cover with logo

foreach ($pages as $pair) {
    // var_dump($pair); // Debugging: Show the page pairs being processed
    $buildpdf->AddPage();
    foreach ($pair as $pageNum) {
        if ($pageNum > $totalPages) {
            $buildpdf->Cell(0, 5, "(Blank)", 0, 1, 'C'); // Handle extra blank pages
        } else {
            $buildpdf->Cell(0, 5, "Page " . $pageNum, 0, 1, 'C');
        }
    }
}
$buildpdf->cota_back_cover( 'Back Cover' );


// Output PDF
$buildpdf->Output('F', '../uploads/membership_directory.pdf');
$buildpdf->Output('I');