<?php
/**
 * COTA Membership Directory PDF Generation Script
 * This script prints the COTA directory as a linear set of standard
 * pages (1 through x, with 1 being the front cover and x being the last printed page).
 * It does not account for 2-sided booklet printing, but rather prints
 * assuming that the PDF will then be processed by another app that will convert it to
 * 2-sided, 4 to a page format.
 */

require_once __DIR__ . '/bootstrap.php';

// END New header info

require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'print.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-print-booklet.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

// Create a new PDF instance - half page format
$pdf = new PDF( 'P', 'in', 'HalfLetter' ); // Portrait, Inches, Half-Letter Size

$pdf->AddPage();

$pdf->SetFont( 'Arial', '', 12 );
$logoFile = '../app-assets/images/cota-logo.png';

$title  = 'Church of the Ascension Directory ' . (string) date( 'Y' );
$author = 'Vestry & Wardens of Church of the Ascension, Parkesburg';

// Retrieve and Format Membership Entries; Get number of families
$families     = $cota_db->read_family_database();
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
	$first_line = strtok( $intro_content, "\n" );
	$intro_title = trim( $first_line );
	$intro_content = substr( $intro_content, strlen( $first_line ) + 1 );
	$pdf->add_booklet_page(
		'intro',
		array(
			'title'   => $intro_title,
			'content' => $intro_content,
		)
	);
}
// Generate family summary content
$family_summary_content  = 'This directory contains ' . $num_families . ' families.';
$family_summary_content .= "\n\nOther misc info may be shared here about the membership numbers.";

// Add family listing page
$pdf->add_booklet_page(
	'family_summary',
	array(
		'title'   => 'Family & Members Listing Summary',
		'content' => $family_summary_content,
	)
);

// Process families and add them to booklet pages
$current_page_families = array();
$families_per_page     = 8; // Adjust based on content size
$family_count          = 0;

while ( $family = $families->fetch_assoc() ) {
	// Get family members
	$individuals  = $cota_db->read_members_of_family( $family['id'] );
	$family_array = cota_format_family_listing_for_print( $family, $individuals );
	// Get field info for the first family
	if ( $family_count === 0 ) {
		$field_info = $pdf->print_family_array_headings( true );
	}

	$current_page_families[] = array(
		'family_array' => $family_array,
		'field_info'   => $field_info,
	);
	++$family_count;

	// When we reach the limit, add the page and reset the current page families
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

$cota_db->close_connection();

// Echo header
echo cota_page_header();

// Dump out remainder of import page.
echo "<div id='cota-print' class='container'>";
echo '<h2>Booklet-formatted PDF file generated successfully!</h2>';
echo '<h4>File: ' . basename( $output_filename ) . '</h4>';
echo '<p><strong>Printing Instructions:</strong><br>Download the booklet PDF and open in your PDF applicaiton.<br>
Select the following options in the PDF app for the printer<br>to print the booklet ready for binding and folding.<br>
You may need to adjust these settings based on your specific printer and PDF application, but generally look for the following:<br>
	<ul>
	<li>2 pages per sheet on 8 1/2 x 11" paper</li>
	<li>2-sided printing -> flip on the short edge</li>
	<li>Orientation: portrait<li>
	<li>Scale: to fit</li>
	</ul>
	Confirm the order of the pages prior to copying.</p>';
echo '<p><strong>Total Pages:</strong> ' . count( $pdf->booklet_pages ) . ' content pages</p>';
echo "<button class='cota-print' type='button' ><a href='.." . $output_basename . "' download >Download Booklet PDF</a></button>";
echo '</div></body></html>';
