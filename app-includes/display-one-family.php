<?php
require_once __DIR__ . '/bootstrap.php';
global $cota_db, $connect, $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'headers.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';


$familyname     = trim( $_GET['familyname'] ?? '' );
$family_matches = array();
$fuzzy_matches  = array();
$family         = null;

if ( $_SERVER['REQUEST_METHOD'] === 'GET' && '' !== $familyname ) {
	$address  = $_GET['address'] ?? '';
	$address2 = $_GET['address2'] ?? '';
	$results  = cota_search_families( $connect, $familyname, $address, $address2 );
	$family_matches = $results['matches'];
	$fuzzy_matches  = $results['fuzzy'];
	$family         = $family_matches[0] ?? null;
}

// Echo header
echo cota_page_header();
if ( ! $family ) {

	?>
		<div id="display-family" class="cota-display-container">
			<h2>Display Family</h2>
			<div class="container error-message"><?php echo htmlspecialchars( ucfirst( $familyname ) ); ?> family not found<br><br>
			<?php if ( ! empty( $fuzzy_matches ) ) : ?>
			<p>Here are nearby names you can review:</p>
			<?php echo cota_render_family_suggestions( $fuzzy_matches, 'display-one-family.php' ); ?>
			<?php endif; ?>
			<a href="../app-includes/display-family.php">Try again with a different spelling.</a></div>
		<?php
			die();
}

$match_count = count( $family_matches );
if ( $match_count > 1 ) {
	// More than 1 result, need to refine.
	?>
		<div id="display-family" class="cota-display-container">
			<h2>Display Family</h2>
			<div class="container error-message">
			<?php echo htmlspecialchars( $familyname ); ?> family search returned multiple results.<br>
			<p>Select one of the matching families below or refine your search.</p>
			<?php echo cota_render_family_suggestions( $family_matches, 'display-one-family.php' ); ?>

			</div>
		<?php
			die();
}

	// Fetch members
	$stmt = $connect->prepare( 'SELECT * FROM members WHERE family_id = ?' );
	$stmt->bind_param( 'i', $family['id'] );
	$stmt->execute();
	$members = $stmt->get_result();
	$stmt->close();


// Dump out remainder of import page.

	echo '<div class="cota-display-one-container">';
	echo '<table class="directory-one-table">';
		echo '<tr><th>Family Name</th><th><i>Family Members</i></th></tr>';
		echo '<tr><th>Address<td><i>Name</i></td><td><i>Email</i></td><td><i>Cell</i></td><td><i>DoB</i></td><td><i>Baptism</i></td><td><i>Anniversary</i></td></th></tr>';

		$individuals = $cota_db->read_members_of_family( $family['id'] );
		echo cota_format_family_listing_for_display( $family, $individuals );
	echo "\n</table></body></html>";
