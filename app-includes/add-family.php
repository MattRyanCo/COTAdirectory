<?php
require_once __DIR__ . '/bootstrap.php';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	// Sanitize & Validate Family Data
	$family_name      = cota_sanitize( $_POST['familyname'] );
	$address          = cota_sanitize( $_POST['address'] );
	$city             = cota_sanitize( $_POST['city'] );
	$state            = cota_sanitize( $_POST['state'] );
	$zip              = cota_sanitize( $_POST['zip'] );
	$home_phone       = cota_validate_phone( $_POST['homephone'] );
	$anniversary_date = cota_format_date( $_POST['annday'] );

	// Insert family record using prepared statements
	$statement = $connect->prepare( 'INSERT INTO families (familyname, address, city, state, zip, homephone, annday) VALUES (?, ?, ?, ?, ?, ?, ?)' );
	$statement->bind_param( 'sssssss', $family_name, $address, $city, $state, $zip, $home_phone, $anniversary_date );

	if ( $statement->execute() ) {
		$family_id = $statement->insert_id;
		$statement->close();

		// Insert members with validation
		$first_names = $_POST['members']['first_name'];

		$fname1 = $fname2 = $lname2 = '';
		foreach ( $first_names as $key => $first_name ) {
			$first_name = cota_sanitize( $first_name );
			$last_name  = cota_sanitize( $_POST['members']['last_name'][ $key ] );

			// Save off 1st 2 names to add back to Families table later.
			if ( '' == $fname1 ) {
				$fname1 = $first_name;
			} elseif ( '' == $fname2 ) {
				$fname2 = $first_name;
				$lname2 = $last_name;
			}

			$cell_phone = cota_validate_phone( $_POST['members']['cell_phone'][ $key ] );
			$email      = cota_sanitize( $_POST['members']['email'][ $key ] );
			$birthday   = cota_format_date( $_POST['members']['birthday'][ $key ] );
			$baptism    = cota_format_date( $_POST['members']['baptism'][ $key ] );

			if ( ! empty( $first_name ) ) {
				$statement = $connect->prepare( 'INSERT INTO members (family_id, first_name, last_name, cell_phone, email, birthday, baptism) VALUES (?, ?, ?, ?, ?, ?, ?)' );
				$statement->bind_param( 'issssss', $family_id, $first_name, $last_name, $cell_phone, $email, $birthday, $baptism );
				$statement->execute();
				// Store results immediately
				$statement->store_result();
				cota_log_error( 'SQL Status (execute): ' . $statement->error );
				$statement->close();
			} else {
				cota_log_error( 'Invalid member data: ' . $first_name . ' - ' . $email . ' - ' . $birthday );
			}
		}
		// @todo Add $fname1, $fname2, $lname2 to family table

		// Echo header
		echo cota_page_header();
		// Dump out remainder of import page.
		echo "<div class='cota-add-container'>";
		// echo "<h2>" . $family_name . " family added successfully!</h2>";
		echo '<h2><a href="' . $constants->COTA_APP_INCLUDES . 'display-one-family.php?familyname=' . urlencode( $family_name ) . '&address=&address2=">' . htmlspecialchars( $family_name ) . ' family added successfully. Click to view.</a></h2>';
		echo '</div>';
	} else {
		cota_log_error( 'SQL Error (execute): ' . $statement->error );
	}
}

$cota_db->close_connection();
