<?php
/**
 *
 */
require_once __DIR__ . '/bootstrap.php';

	// This function will prompt the usee to add any informational documents
	// to be included in the directory.
	// These documents will be included in the PDF and RTF printed directory
	// after the cover page and before the member listing.
	// These documents should be text only documents, no images or formatting and
	// should be limited to 1 page in length or less each (approx 60 lines).

		// Echo header
echo cota_page_header();

// Dump out form .
echo "<div class='cota-upload-info-docs-container'>";
	echo '<h2>Upload Informational Documents</h2>';
	echo '<p>These documents will be included in the PDF and RTF printed directory<br>after the cover page and before the member listing.</p>';
	echo '<p>These documents should be text only documents, no images or extensive formatting<br>and should be limited to 1 page in length or less each (approx 60 lines).</p>';

	echo '<form class="cota-upload-info" method="post" enctype="multipart/form-data">';
		echo '<label>Select .txt or .rtf File:</label>';
		// echo '<input type="file" name="info_file" accept=".txt, .rtf" required>';
		echo '<input type="file" name="info_file" accept=".txt" required>';
		echo '<button class="cota-upload-info" type="submit">Upload Info Documents</button>';
	echo '</form>';
echo '</div>';
echo '</body>';
echo '</html>';

// Process input form for files. Max number =
$ictr = 1;
// while ( $ictr <= $constants->MAX_INFORMATIONAL_DOCS ) {
// while ( $ictr <= 5 ) {
if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_FILES['info_file'] ) ) {
	$upload_dir = $constants->UPLOAD_DIR;
	// Sanitize filename: remove dangerous chars, allow only safe chars
	$file_name   = preg_replace( '/[^a-zA-Z0-9_\.-]/', '_', basename( $_FILES['info_file']['name'] ) );
	$upload_file = $upload_dir . $file_name;

	// Ensure the uploads directory exists
	if ( ! is_dir( $upload_dir ) && ! mkdir( $upload_dir, 0755, true ) ) {
		cota_handle_error( 'Failed to find upload directory.', 'U101' );
	}

	// Check for upload errors
	if ( $_FILES['info_file']['error'] !== UPLOAD_ERR_OK ) {
		cota_handle_error( 'File upload error. Code: ' . $_FILES['info_file']['error'], 'U102' );
	}

	// Move uploaded file & perform import.
	if ( move_uploaded_file( $_FILES['info_file']['tmp_name'], $upload_file ) ) {
		cota_import( $upload_file );
	} else {
		cota_handle_error( 'File move error. Code: ' . $_FILES['info_file']['error'], 'U103' );
	}

	write_success_notice( 'File(s) uploaded successfully! ' );
	exit;

}
// }
