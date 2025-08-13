<?php
require_once __DIR__ . '/bootstrap.php';
/**
 * COTA Membership Directory PDF Generation Script
 * This script prints the COTA directory in booklet format for 2-up printing
 * on 8.5 x 11" paper in landscape mode with 2 pages per sheet.
 */

global $cota_db, $connect, $constants, $header_height;

require_once $constants->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $constants->COTA_APP_INCLUDES . 'format-family-listing-for-fpdf.php';
require_once $constants->COTA_APP_INCLUDES . 'print.php';
require_once $constants->COTA_APP_INCLUDES . 'class-print-booklet.php';

// Create a new PDF instance for booklet format
$pdf = new PDF( 'P', 'in', 'HalfLetter' ); // Portrait, Inches, Half-Letter Size

$title  = 'Church of the Ascension Directory 2025';
$author = 'Vestry & Wardens of Church of the Ascension, Parkesburg';

// Add front cover page
$pdf->add_booklet_page(
	'cover',
	array(
		'title'  => $title,
		'author' => $author,
		'logo'   => '../app-assets/images/cota-logo.png',
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

//    This is just adding a header. No family data yet.
//       THIS header does not appear to be output anywhere????
$pdf->add_booklet_page(
	'family',
	array(
		'title'    => 'Family & Members Listing Overview ------------------------------------',
		'families' => array(), // Will be populated below
	)
);

// Retrieve and Format Membership Entries
$families     = $cota_db->read_family_database();
$num_families = $families->num_rows;

// Process families and add them to booklet pages
$current_page_families = array();
$families_per_page     = 5; // Adjust based on content size
$family_count          = 0;

while ( $family = $families->fetch_assoc() ) {
	// Get family members
	$individuals  = $cota_db->read_members_of_family( $family['id'] );
	$family_array = cota_format_family_listing_for_fpdf( $pdf, $family, $individuals );

	$current_page_families[] = array(
		'name' => $family['familyname'],
		'data' => $family_array, // This is now a formatted string for entire family listing.
	);

	++$family_count;

	// When we reach the limit, add a new page
	// if ( $family_count % $families_per_page === 0 ) {
	if ( $family_count % $constants->FAMILIES_PER_PAGE === 0 ) {
		$pdf->add_booklet_page(
			'family',
			array(
				'title'    => 'Family & Members Listing - Page ' . ( ( $family_count / $families_per_page ) + 1 ),
				'families' => $current_page_families,
			)
		);
		// Reset array in prep of next family
		$current_page_families = array();
	}
}  // end of loop

// Add remaining families to the last page
if ( ! empty( $current_page_families ) ) {
	$pdf->add_booklet_page(
		'family',
		array(
			'title'    => 'Family & Members Listing - Final Page',
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
// $pdf->AddPage();

$final_pdf = $pdf->generate_booklet_pdf();

$output_basename = '/downloads/directory_booklet_' . date( 'Y-m-d' ) . '.pdf';
// Ensure the downloads directory exists
if ( ! is_dir( $_SERVER['DOCUMENT_ROOT'] . '/downloads' ) ) {
	mkdir( $_SERVER['DOCUMENT_ROOT'] . '/downloads', 0755, true );
}
$output_filename = $_SERVER['DOCUMENT_ROOT'] . $output_basename;

// Output the final booklet PDF
$final_pdf->Output( 'F', $output_filename ); // Save to server

$cota_db->close_connection();

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
