<?php
require_once __DIR__ . '/bootstrap.php';

$member_id = -1; // Default for new family member
if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['family_id'] ) ) {

	// Pull off family info from form.
	$family_id   = intval( $_POST['family_id'] );
	$family_name = trim( $_POST['familyname'] );
	$address     = trim( $_POST['address'] );
	$city        = trim( $_POST['city'] );
	$state       = trim( $_POST['state'] );
	$zip         = trim( $_POST['zip'] );
	$home_phone  = trim( $_POST['homephone'] );
	// Anniversary date is NULL if blank. All dates are optional and are set to Null if blank.
	$anniversary_date = ! empty( $_POST['annday'] ) ? $_POST['annday'] : null;

	// Update family record
	$statement = $connect->prepare( 'UPDATE families SET familyname=?, address=?, city=?, state=?, zip=?, homephone=?, annday=? WHERE id=?' );
	$statement->bind_param( 'sssssssi', $family_name, $address, $city, $state, $zip, $home_phone, $anniversary_date, $family_id );
	$statement->execute();
	$statement->close();

	// Update each member
	// Pull off any family member info from lower portion of form.
	if ( isset( $_POST['members']['id'] ) ) {
		$member_ids  = $_POST['members']['id'];
		$first_names = $_POST['members']['first_name'];
		$last_names  = $_POST['members']['last_name'];
		$cell_phones = $_POST['members']['cell_phone'];
		$emails      = $_POST['members']['email'];

		// Ensure birthdays and baptisms are arrays and set empty values to null
		$birthdays = isset( $_POST['members']['birthday'] ) ? $_POST['members']['birthday'] : array();
		$baptisms  = isset( $_POST['members']['baptism'] ) ? $_POST['members']['baptism'] : array();
	}

	$member_count = count( $member_ids );
	for ( $i = 0; $i < $member_count; $i++ ) {
		$member_id = intval( $member_ids[ $i ] );
		// Check for and add new member here.
		if ( -1 == $member_id && isset( $_POST['members']['first_name'][ $i ] ) && ! empty( trim( $_POST['members']['first_name'][ $i ] ) ) ) {
			// We do have a new member to add.
			$fname = trim( $_POST['members']['first_name'][ $i ] );
			$lname = trim( $_POST['members']['last_name'][ $i ] );
			$cell  = cota_validate_phone( trim( $_POST['members']['cell_phone'][ $i ] ) );
			$email = trim( $_POST['members']['email'][ $i ] );
			$bday  = isset( $_POST['members']['birthday'][ $i ] ) ? trim( $_POST['members']['birthday'][ $i ] ) : null;
			$bap   = isset( $_POST['members']['baptism'][ $i ] ) ? trim( $_POST['members']['baptism'][ $i ] ) : null;

			// Set empty date strings to null
			$bday = ( $bday === '' ? null : $bday );
			$bap  = ( $bap === '' ? null : $bap );

			// Insert new member
			$statement = $connect->prepare( 'INSERT INTO members (family_id, first_name, last_name, cell_phone, email, birthday, baptism) VALUES (?, ?, ?, ?, ?, ?, ?)' );
			$statement->bind_param( 'issssss', $family_id, $fname, $lname, $cell, $email, $bday, $bap );
			$statement->execute();
			$statement->close();
		} else {
			$fname = trim( $first_names[ $i ] );
			$lname = trim( $last_names[ $i ] );
			$cell  = cota_validate_phone( trim( $cell_phones[ $i ] ) );
			$email = trim( $emails[ $i ] );
			$bday  = isset( $birthdays[ $i ] ) ? trim( $birthdays[ $i ] ) : null;
			$bap   = isset( $baptisms[ $i ] ) ? trim( $baptisms[ $i ] ) : null;

			// Set empty date strings to null
			$bday = ( $bday === '' ? null : $bday );
			$bap  = ( $bap === '' ? null : $bap );

			$statement = $connect->prepare( 'UPDATE members SET first_name=?, last_name=?, cell_phone=?, email=?, birthday=?, baptism=? WHERE id=? AND family_id=?' );
			$statement->bind_param( 'ssssssii', $fname, $lname, $cell, $email, $bday, $bap, $member_id, $family_id );
			$statement->execute();
			$statement->close();
		}
	}

	// Echo header
	echo cota_page_header();
	// Dump out remainder of import page.
	echo "<div class='cota-update-container'>";
	echo '<h2>' . htmlspecialchars( $family_name ) . ' family updated!</h2>';
	echo '<br><br>';
	echo '<div class="two-button-grid">';
	echo '<div><button class="cota-edit-family" type="button"><a href="edit-family.php?familyname=' . urlencode( $family_name ) . '">Edit this family again</a></button></div>';
	echo '<div><button class="cota-search-family" type="button"><a href="search-edit.php">Edit Another Family</a></button></div>';
	echo '</div>';

	echo '</div>';


} else {
	echo '<h2>Error: Invalid request.</h2>';
	echo "<p>Please try again or return <a href='index.php'>home</a>.</p>";
	echo "<button class='main-menu-return' type='button' ><a href='index.php'>Return Home</a></button>";
}

$cota_db->close_connection();
