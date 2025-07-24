<?php
require_once __DIR__ . '/bootstrap.php';
/* COTA Booklet Printing Script
 * This script generates a booklet order for printing pages in a specific order.
 * It uses the FPDF library to create a PDF file with the booklet layout.
 */

global $title;

require_once $constants->COTA_APP_INCLUDES . 'class-print-booklet.php';

function generate_booklet_order( $total_pages ) {
	// Ensure total pages is a multiple of 4, as each sheet contains 4 pages, front and back cover included
	$pages_to_print = ( $total_pages % 4 === 0 ) ? $total_pages : $total_pages + ( 4 - ( $total_pages % 4 ) );

	$booklet_order = array();
	$front_cover   = 1;
	$back_cover    = $pages_to_print;
	for ( $i = 0; $i < $pages_to_print / 2; $i++ ) {
		$left_page  = $pages_to_print - $i;  // Back side of a sheet
		$right_page = $i + 1;             // Front side of a sheet

		$booklet_order[] = array( $left_page, $right_page ); // Front of sheet
		$booklet_order[] = array( $right_page + 1, $left_page - 1 ); // Back of sheet
	}

	return $booklet_order;
}


$title     = 'Church of the Ascension Directory 2025';
$build_pdf = new PDF( 'P', 'in', 'Letter' ); // Portrait, Inches, Letter Size
$author    = 'Vestry & Wardens of Church of the Ascension, Parkesburg';
$build_pdf->SetTitle( $title );
$build_pdf->SetAuthor( $author );
$w = $build_pdf->GetStringWidth( $title ) + 6;
// print_r($w); // Debugging: Show the width of the title

$total_pages = 16; // Change this dynamically based on actual content
$pages       = generate_booklet_order( $total_pages );
// var_dump($pages); // Debugging: Show the generated page pairs

$build_pdf->SetMargins( 0.5, 0.5, 0.5 );
$build_pdf->SetAutoPageBreak( true, 0.5 );
$build_pdf->SetFont( 'Arial', '', 12 );

$logo_file = '../app-assets/images/cota-logo.png';
$build_pdf->front_cover( $title, $author, $logo_file ); // Add front cover with logo

// $build_pdf->dummy_up_pages( $build_pdf, $pages ); // Add dummy pages based on the generated order

$build_pdf->SetFont( 'Arial', 'B', 14 );
$build_pdf->Cell( 0, 10, 'COTA Directory', 0, 1, 'C' );
$build_pdf->SetFont( 'Arial', '', 12 );
$build_pdf->Cell( 0, 10, 'Member Listing - 36 Families', 0, 1, 'C' );
$build_pdf->Ln( 5 );

// loop through families
// Family Heading
$build_pdf->SetFont( 'Arial', 'B', 11 );
$build_pdf->Cell( 0, 8, 'Family: Ammon', 0, 1 );
$build_pdf->SetFont( 'Arial', '', 10 );
$build_pdf->Cell( 0, 6, '62 Compass Rd, Parkesburg, PA 19365', 0, 1 );
$build_pdf->Cell( 0, 6, 'Home: 484-467-1471', 0, 1 );
$build_pdf->Ln( 2 );

// Set up column widths
$w = array( 25, 5, 5, 35, 35, 15, 5, 5 ); // Widths for each column
// Member Table Headers
$build_pdf->SetFont( 'Arial', 'B', 10 );
$build_pdf->Cell( $w[0], 6, ' ', 1 );
$build_pdf->Cell( $w[1], 6, ' ', 1 );
$build_pdf->Cell( $w[2], 6, ' ', 1 );
$build_pdf->Cell( $w[3], 6, 'Name', 1 );
$build_pdf->Cell( $w[5], 6, 'Email', 1 );
$build_pdf->Cell( $w[6], 6, 'Cell', 1 );
$build_pdf->Cell( $w[7], 6, 'DoB', 1 );
$build_pdf->Cell( $w[8], 6, 'DoBaptism', 1 );
$build_pdf->Ln();

// Sample Family Members
$build_pdf->SetFont( 'Arial', '', 10 );
$members = array(
	array( '', '', '', 'Barb Jo', 'bjammon@comcast.net', '484-467-1471', '09/01', '10/01' ),
	array( '', '', '', 'Jim', 'jim@mail.com', '484-457-1472', '07/30', '08/30' ),
);

foreach ( $members as $m ) {
	$build_pdf->Cell( 5, 6, $m[0], 0 );
	$build_pdf->Cell( 5, 6, $m[1], 0 );
	$build_pdf->Cell( 5, 6, $m[2], 0 );
	$build_pdf->Cell( 35, 6, $m[3], 0 );
	$build_pdf->Cell( 55, 6, $m[4], 0 );
	$build_pdf->Cell( 30, 6, $m[5], 0 );
	$build_pdf->Cell( 20, 6, $m[6], 0 );
	$build_pdf->Cell( 30, 6, $m[7], 0 );
	$build_pdf->Ln();
}

$build_pdf->Ln( 4 );

$build_pdf->back_cover( 'Back Cover' );

// Output PDF

$output_basename = '/downloads/directory_booklet_sample_' . date( 'Y-m-d' ) . '.pdf';
// Ensure the downloads directory exists
if ( ! is_dir( $_SERVER['DOCUMENT_ROOT'] . '/downloads' ) ) {
	mkdir( $_SERVER['DOCUMENT_ROOT'] . '/downloads', 0755, true );
}
$output_filename = $_SERVER['DOCUMENT_ROOT'] . $output_basename;
$build_pdf->Output( 'F', $output_filename );  // Enable to dump to screen.
// $build_pdf->Output('I');

// Echo header
echo cota_page_header();


echo "<div id='cota-print' class='container'>";
echo '<h2>PDF file generated successfully!</h2>';
echo '<h4>File: ' . basename( $output_filename ) . '</h2>';
echo "<button class='button' type='button' ><a href='.." . $output_basename . "' download >Download File</a></button>";
echo '</div></body></html>';
