<?php
/**
 * COTA Membership Directory PDF Generation Script
 * This script prints the COTA directory as a linear set of standard
 * pages (1 through x, with 1 being the front cover and x being the last printed page).
 * It does not account for 2-sided booklet printing, but rather prints
 * assuming that the PDF will then be processed by another app that will convert it to
 * 2-sided, 4 to a page format.
 */

// New header info
// require_once __DIR__ . '/bootstrap.php';
/**
 * COTA Membership Directory PDF Generation Script
 * This script prints the COTA directory in booklet format for 2-up printing
 * on 8.5 x 11" paper in landscape mode with 2 pages per sheet.
 */
// END New header info

global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'print.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'class-print-booklet.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

// Create a new PDF instance
// $pdf = new PDF(); // Landscape, Inches, Half-page Letter Size
// $pdf = new PDF('P', 'in', 'Letter'); // Portrait, Inches, Letter Size

// This to use new half page format
$pdf = new PDF( 'P', 'in', 'HalfLetter' ); // Portrait, Inches, Half-Letter Size


$pdf->AddPage();

$title  = 'Church of the Ascension Directory 2025';
$author = 'Vestry & Wardens of Church of the Ascension, Parkesburg';
$pdf->SetTitle( $title );
$pdf->SetAuthor( $author );

$pdf->SetFont( 'Arial', '', 12 );
$logoFile = '../app-assets/images/cota-logo.png';

// Initialize database connection
$cotadb = new COTA_Database();

// Retrieve and Format Membership Entries; Get number of families
$families     = $cotadb->read_family_database();
$num_families = $families->num_rows;

// Add front cover page to booklet
$pdf->add_booklet_page(
	'cover',
	array(
		'title'  => $title,
		'author' => $author,
		'logo'   => $logoFile,
	)
);

// Load and insert static intro pages
for ( $i = 1; $i <= 3; $i++ ) {
	$intro_content = file_get_contents( '../uploads/intro' . $i . '.txt' );
	$pdf->add_booklet_page(
		'intro',
		array(
			'title'   => 'Introduction ' . $i,
			'content' => $intro_content,
		)
	);
}

// Add family listing header page
$pdf->add_booklet_page(
	'family_summary',
	array(
		'title'   => 'Family & Members Listing - ' . $num_families . ' families',
		'content' => 'Other misc info may be shared here about the membership numbers.',
	)
);

// Process families and add them to booklet pages
$current_page_families = array();
$families_per_page     = 8; // Adjust based on content size
$family_count          = 0;

while ( $family = $families->fetch_assoc() ) {
	// Get family members
	$individuals  = $cotadb->read_members_of_family( $family['id'] );
	$family_array = cota_format_family_listing_for_print( $family, $individuals );

	// Get field info for the first family (we'll reuse this)
	if ( $family_count === 0 ) {
		$field_info = $pdf->print_family_array_headings( true );
	}

	$current_page_families[] = array(
		'family_array' => $family_array,
		'field_info'   => $field_info,
	);

	++$family_count;

	// When we reach the limit, add a new page
	if ( $family_count % $families_per_page === 0 ) {
		$pdf->add_booklet_page(
			'family',
			array(
				'families' => $current_page_families,
			)
		);
		$current_page_families = array();
	}
}

// Add remaining families to the last page
if ( ! empty( $current_page_families ) ) {
	$pdf->add_booklet_page(
		'family',
		array(
			'families' => $current_page_families,
		)
	);
}

// Add the back cover
$pdf->add_booklet_page(
	'back_cover',
	array(
		'date' => date( 'F j, Y' ),
	)
);

// Generate the final booklet PDF with correct page ordering
$final_pdf = $pdf->generate_booklet_pdf();


$output_basename = '/downloads/directory_booklet_' . date( 'Y-m-d' ) . '.pdf';
// Ensure the downloads directory exists
if ( ! is_dir( $_SERVER['DOCUMENT_ROOT'] . '/downloads' ) ) {
	mkdir( $_SERVER['DOCUMENT_ROOT'] . '/downloads', 0755, true );
}
$output_filename = $_SERVER['DOCUMENT_ROOT'] . $output_basename;

// Output the final booklet PDF
$final_pdf->Output( 'F', $output_filename ); // Save to server

$cotadb->close_connection();

// Echo header
echo cota_page_header();

// Dump out remainder of import page.
echo "<div id='cota-print' class='container'>";
echo '<h2>Booklet PDF file generated successfully!</h2>';
echo '<h4>File: ' . basename( $output_filename ) . '</h4>';
echo '<p><strong>Booklet Format:</strong> This PDF is configured for 2-up printing on 8.5 x 11" paper in portrait mode.</p>';
echo '<p><strong>Printing Instructions:</strong> When printing, select "2 pages per sheet" and "Portrait" orientation for proper booklet format.</p>';
echo '<p><strong>Total Pages:</strong> ' . count( $pdf->booklet_pages ) . ' content pages</p>';
echo "<button class='cota-print' type='button' ><a href='.." . $output_basename . "' download >Download Booklet PDF</a></button>";
echo '</div></body></html>';
