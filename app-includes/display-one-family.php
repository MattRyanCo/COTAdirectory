<?php
require_once __DIR__ . '/bootstrap.php';
require_once $constants->COTA_APP_INCLUDES . 'format-family-listing.php';


if ( $_SERVER['REQUEST_METHOD'] === 'GET' && isset( $_GET['familyname'] ) ) {
	$family_name = cota_sanitize( $_GET['familyname'] );

	// Check optional search fields
	$address_entered   = ! empty( trim( $_GET['address'] ?? '' ) );
	$address_2_entered = ! empty( trim( $_GET['address2'] ?? '' ) );

	// Fetch family record
	if ( ! $address_entered && ! $address_2_entered ) {
		// No extra search fields
		$statement = $connect->prepare(
			'SELECT * FROM families 
            WHERE familyname = ?'
		);
		$statement->bind_param( 's', $family_name );
	} elseif ( $address_entered && ! $address_2_entered ) {
		// Extra search field address only entered
		$address_like = '%' . $_GET['address'] . '%';
		$statement    = $connect->prepare(
			'SELECT * FROM families 
            WHERE familyname = ? AND address LIKE ?'
		);
		$statement->bind_param( 'ss', $family_name, $address_like );
	} elseif ( ! $address_entered && $address_2_entered ) {
		// Extra search field address2 only entered
		$address_2_like = '%' . $_GET['address2'] . '%';
		$statement      = $connect->prepare(
			'SELECT * FROM families 
            WHERE familyname = ? AND address2 LIKE ?'
		);
		$statement->bind_param( 'ss', $family_name, $address_2_like );
	} elseif ( $address_entered && $address_2_entered ) {
		// Extra search field address and address2 entered
		$address_like   = '%' . $_GET['address'] . '%';
		$address_2_like = '%' . $_GET['address2'] . '%';
		$statement      = $connect->prepare(
			'SELECT * FROM families 
        WHERE familyname = ? 
        AND ( address LIKE ? OR address2 LIKE ?) '
		);
		$statement->bind_param( 'sss', $family_name, $address_like, $address_2_like );
	}
}
	// Execute search
	$statement->execute();
	$result = $statement->get_result();
	$family = $result->fetch_assoc();
	$statement->close();

		// Echo header
	echo cota_page_header();
if ( ! $family ) {

	?>
		<div id="display-family" class="cota-display-container">
			<h2>Display Family</h2>
			<div class="container error-message"><?php echo htmlspecialchars( ucfirst( $family_name ) ); ?> family not found<br><br>
			<a href="../app-includes/display-family.php">Try again with a different spelling.</a></div>
		<?php
			die();
}

if ( $result->num_rows > 1 ) {
	// More than 1 result, need to refine.
	?>
		<div id="display-family" class="cota-display-container">
			<h2>Display Family</h2>
			<div class="container error-message">
						<?php echo htmlspecialchars( $family_name ); ?> family search returned multiple results.<br><br>
			<a href="../app-includes/display-family.php?familyname=<?php echo urlencode( $family_name ); ?>&address=&address2=">Please refine your search with the address fields.</a>

			</div>
		<?php
			die();
}

	// Fetch members
	$statement = $connect->prepare( 'SELECT * FROM members WHERE family_id = ?' );
	$statement->bind_param( 'i', $family['id'] );
	$statement->execute();
	$members = $statement->get_result();
	$statement->close();


// Dump out remainder of import page.

	echo '<div class="cota-display-one-container">';
	echo '<table class="directory-one-table">';
		echo '<tr><th>Family Name/Address</th><th><i>Family Members</i></th></tr>';
		echo '<tr><th>Home Phone<td><i>Name</i></td><td><i>Email</i></td><td><i>Cell</i></td><td><i>DoB</i></td><td><i>DoBaptism</i></td></th></tr>';

		$individuals = $cota_db->read_members_of_family( $family['id'] );
		echo cota_format_family_listing_for_display( $family, $individuals );
	echo "\n</table></body></html>";
