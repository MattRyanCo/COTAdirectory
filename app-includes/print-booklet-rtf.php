<?php
require_once __DIR__ . '/bootstrap.php';
require_once $constants->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $constants->COTA_APP_INCLUDES . 'print.php';

$print_booklet = new Membership_Directory_Printer();

$intro_files     = array( '../uploads/intro1.txt', '../uploads/intro2.txt', '../uploads/intro3.txt' );
$output_basename = '/downloads/directory_booklet_' . date( 'Y-m-d' ) . '.rtf';
// Ensure the downloads directory exists
if ( ! is_dir( $_SERVER['DOCUMENT_ROOT'] . '/downloads' ) ) {
	mkdir( $_SERVER['DOCUMENT_ROOT'] . '/downloads', 0755, true );
}
$output_filename = $_SERVER['DOCUMENT_ROOT'] . $output_basename;

$rtf_content = $print_booklet->generate_rtf_header();

// Add intro page(s) to output
$rtf_content .= $print_booklet->print_intro_pages( 3 );

$all_families = $cota_db->read_family_database();

// Add family listings
	$rtf_content .= $print_booklet->format_family_listings( $all_families ) . '\\pard\\page\\par';

$rtf_content .= '}';

file_put_contents( $output_filename, $rtf_content );

// Closing the file
$cota_db->close_connection();

// Echo header
echo cota_page_header();

// Dump out remainder of import page.
echo "<div id='cota-print' class='container'>";
echo '<h2>RTF file generated successfully!</h2>';
echo '<h4>File: ' . basename( $output_filename ) . '</h2>';
echo "<button class='cota-print' type='button' ><a href='.." . $output_basename . "' download>Download File</a></button>";
echo '</div><body></html>';
