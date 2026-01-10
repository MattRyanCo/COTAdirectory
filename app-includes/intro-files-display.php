<?php
/**
 *
 */
require_once __DIR__ . '/bootstrap.php';
global $cota_app_settings, $cota_db, $connect;

// Echo header
echo cota_page_header();

// Display directory of intro files available
echo '<div class="cota-intro-files-display-container">';
echo '<h2>Available Intro Files</h2>';
$intro_files = glob( '../uploads/intro*.txt' );
if ( ! empty( $intro_files ) ) {
	echo '<ul>';
	foreach ( $intro_files as $file ) {
		$filename = basename( $file );
		echo '<li style="margin-bottom: 10px;"><h2>' . htmlspecialchars( $filename ) . '</h2></li>';
		// Open file and dump its contents to the screen.
		$contents = file_get_contents( $file );
		echo '<pre>' . htmlspecialchars( $contents ) . '</pre>';
	}
	echo '</ul>';
} else {
	echo '<p>No intro files found. Please upload intro files using the <a href="/app-includes/intro-files-update.php">Intro Files Update</a> page.</p>';
}
echo '</div>';
