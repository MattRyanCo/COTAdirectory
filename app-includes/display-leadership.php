<?php
/**
 * Display the leadership db listing.
 *
 * @package COTAdirectory
 */
require_once __DIR__ . '/bootstrap.php';

require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-leadership-listing.php';

// Echo page header
echo cota_page_header();

$leadership     = $cota_db->read_leadership_database();
$num_leadership = $leadership->num_rows;
$ictr         = 1;

if ( 0 === $num_leadership ) {
	empty_database_alert( 'Leadership Listing Display' );
} else {
	// Dump out remainder of page.
	echo '<div class="cota-display-container">';
	echo '<h3>Leadership Listing</h3>';
	echo '<table class="leadership-directory-table">';
	echo '<tr><th>Name</th><th><i>Leadership Area</i></th></tr>';

	$leadership_individuals = $cota_db->read_members_of_leadership();
	while ( $leadership_individual = $leadership_individuals->fetch_assoc() ) {
		echo cota_format_leadership_listing( $leadership_individual );
	}
	echo "\n</table></body></html>";
}
// Close the file
$cota_db->close_connection();
