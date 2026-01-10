<?php
/**
 *
 */
require_once __DIR__ . '/bootstrap.php';
global $cota_app_settings, $cota_db, $connect;

// Echo header
echo cota_page_header();

// Dump out remainder of import page.
?>
	<div class="cota-update-intro-container">
		<h2>Upload text file(s) containing introductory information.</h2>
		<p class="cota-update-intro">Note that uploaded files will overwrite any existing intro files with the same name.<br>
	Filenames should be in the format intro1.txt, intro2.txt, etc.</p>
	<p>You may use the <a href="/app-includes/intro-files-display.php">Intro Files Display</a> page to view the current intro files.</p>
	</div>
	<form class="cota-update-intro" method="post" enctype="multipart/form-data">
		<label>Select TXT File:</label>
		<input type="file" name="txt_file" accept=".txt" required>
		<button class="cota-update-intro" type="submit">Upload</button>
	</form>
</body>
</html>

<?php
// Process input form
if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_FILES['txt_file'] ) ) {
	$upload_dir = '../uploads/';
	// Sanitize filename: remove dangerous chars, allow only safe chars
	$file_name   = preg_replace( '/[^a-zA-Z0-9_\.-]/', '_', basename( $_FILES['txt_file']['name'] ) );
	$upload_file = $upload_dir . $file_name;

	// Ensure the uploads directory exists
	if ( ! is_dir( $upload_dir ) && ! mkdir( $upload_dir, 0755, true ) ) {
		cota_handle_error( 'Failed to find upload directory.', 101 );
	}

	// Check for upload errors
	if ( $_FILES['txt_file']['error'] !== UPLOAD_ERR_OK ) {
		cota_handle_error( 'File upload error. Code: ' . $_FILES['txt_file']['error'], 102 );
	}

	// Move uploaded file to uploads directory.
	if ( move_uploaded_file( $_FILES['txt_file']['tmp_name'], $upload_file ) ) {
		write_success_notice( 'Intro file ' . htmlspecialchars( $file_name ) . ' uploaded successfully! ' );
	} else {
		cota_handle_error( 'File move error. Code: ' . $_FILES['txt_file']['error'], 103 );
	}
	exit;
}
