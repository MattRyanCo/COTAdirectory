<?php

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect, $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	// Sanitize & Validate Family Data
	$familyname = cota_sanitize( $_POST['familyname'] );
	$address    = cota_sanitize( $_POST['address'] );
	$city       = cota_sanitize( $_POST['city'] );
	$state      = cota_sanitize( $_POST['state'] );
	$zip        = cota_sanitize( $_POST['zip'] );
	$homephone  = cota_validate_phone( $_POST['homephone'] );

	// Insert family record using prepared statements
	$stmt = $connect->prepare( 'INSERT INTO families (familyname, address, city, state, zip, homephone ) VALUES (?, ?, ?, ?, ?, ?)' );
	$stmt->bind_param( 'ssssss', $familyname, $address, $city, $state, $zip, $homephone );

	if ( $stmt->execute() ) {
		$family_id = $stmt->insert_id;
		$stmt->close();

		// Insert members with validation
		$first_names = $_POST['members']['first_name'];

		$fname1 = $fname2 = $lname1 = $lname2 = '';
		foreach ( $first_names as $key => $first_name ) {
			$first_name = cota_sanitize( $first_name );
			$last_name  = cota_sanitize( $_POST['members']['last_name'][ $key ] );

			// Save off 1st 2 names to add back to Families table later.
			// if ( '' == $fname1 ) {
			// 	$fname1 = $firstname;
			// } elseif ( '' == $fname2 ) {
			// 	$fname2 = $firstname;
			// 	$lname2 = $lastname;
			// }

			$cell_phone  = cota_validate_phone( $_POST['members']['cell_phone'][ $key ] );
			$email       = cota_sanitize( $_POST['members']['email'][ $key ] );
			$birthday    = cota_format_date( $_POST['members']['birthday'][ $key ] );
			$baptism     = cota_format_date( $_POST['members']['baptism'][ $key ] );
			$anniversary = cota_format_date( $_POST['members']['anniversary'][ $key ] );

			if ( ! empty( $first_name ) ) {
				$stmt = $connect->prepare( 'INSERT INTO members (family_id, first_name, last_name, cell_phone, email, birthday, baptism, anniversary) VALUES (?, ?, ?, ?, ?, ?, ?, ?)' );
				$stmt->bind_param( 'isssssss', $family_id, $first_name, $last_name, $cell_phone, $email, $birthday, $baptism, $anniversary );
				$stmt->execute();
				// Store results immediately
				$stmt->store_result();
				cota_log_error( 'SQL Status (execute): ' . $stmt->error );
				$stmt->close();
			} else {
				cota_log_error( 'Invalid member data: ' . $first_name . ' - ' . $email . ' - ' . $birthday );
			}
		}
		// Echo header
		echo cota_page_header();
		// Dump out remainder of import page.
		echo "<div class='cota-add-container'>";
		// echo "<h2>" . $familyname . " family added successfully!</h2>";
		echo '<h2><a href="' . $cota_app_settings->COTA_APP_INCLUDES . 'display-one-family.php?familyname=' . $familyname . '&address=&address2=">' . $familyname . ' family added successfully. Click to view.</a></h2>';
		echo '</div>';
	} else {
		cota_log_error( 'SQL Error (execute): ' . $stmt->error );
	}
}

$cota_db->close_connection();
