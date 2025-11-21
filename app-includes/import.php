<?php
/**
 *
 */
require_once __DIR__ . '/bootstrap.php';
global $cota_app_settings, $cota_db, $connect;

function cota_read_csv_to_assoc_array( $filename ) {
	$data   = array();
	$escape = '\\';
    $handle = fopen( $filename, 'r' );
	if ( false !== $handle ) {
		// Remove BOM from the first line if present
		$first_line = fgets( $handle );
		$first_line = preg_replace( '/^\xEF\xBB\xBF/', '', $first_line );
		$headers   = str_getcsv( $first_line, ',', '"', $escape );
		while ( ( $row = fgetcsv( $handle, 0, ',', '"', $escape ) ) !== false ) {
			// Explicitly convert from Windows-1252 to UTF-8
			foreach ( $row as &$value ) {
				// Remove quoted-printable artifacts
				$value = quoted_printable_decode( $value );
				$value = trim( mb_convert_encoding( $value, 'UTF-8', 'UTF-8' ) );
				$value = trim( mb_convert_encoding( quoted_printable_decode( $value ), 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252' ) );
				$value = cota_fix_mime_artifacts( $value );
			}
			$data[] = array_combine( $headers, $row );
		}
		fclose( $handle );
	}
	return $data;
}
	/**
	 * cota_import_families_from_one_line
	 *
	 * Import families from CSV file where all family data is on one line.
	 * Each family member is in a separate column, e.g., fname1, lname1, fname2, lname2, bday1, bap1, cell1, email1, etc.
	 *
	 * @param [type] $filename
	 * @return void
	 */
function cota_import_families_from_one_line( $filename ) {
	global $cota_app_settings;
	global $cota_db, $connect;

	require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

	if ( ! file_exists( $filename ) ) {
		die( 'Error: CSV file not found.' );
	}
	// Read input file containing families into array
	$csv_data = cota_read_csv_to_assoc_array( $filename );

	foreach ( $csv_data as $row ) {
		// Skip if familyname is missing
		if ( empty( $row['familyname'] ) ) {
			cota_log_error( 'Missing Family Name in record. Data skipped: ' . print_r( $row, true ) );
			continue;
		}
		// Process Family record
		// The $family_id will be added to each member record for linking.
		$family_id = cota_insert_family( $row );
		if ( ! $family_id ) {
			cota_log_error( 'Failed to insert family. Data skipped: ' . print_r( $row, true ) );
			continue;
		}

		// Now process members in the row
		$member_index = 1;
		while ( isset( $row[ "fname{$member_index}" ] ) && ! empty( $row[ "fname{$member_index}" ] ) ) {
			$member_data = array(
				'first_name' => $row[ "fname{$member_index}" ],
				'last_name'  => ( isset( $row[ "lname{$member_index}" ] ) && $row[ "lname{$member_index}" ] !== '' ) ? $row[ "lname{$member_index}" ] : '',
				'cellphone'  => $row[ "cell{$member_index}" ] ?? '',
				'email'      => $row[ "email{$member_index}" ] ?? '',
				'birthday'   => $row[ "bday{$member_index}" ] ?? '',
				'baptism'    => $row[ "bap{$member_index}" ] ?? '',
			);
			// Only one anniversary date possible on a record.
			$member_data['annday'] = ( isset( $row['annday'] ) && $row['annday'] !== '' ) ? $row['annday'] : '';

			$member_id = cota_insert_member( $family_id, $member_data );
			if ( ! $member_id ) {
				cota_log_error( PHP_EOL . 'Failed to insert member. Data skipped: ' . print_r( $member_data, true ) );
			}
			++$member_index;
		}
	}
	return;
}

function cota_format_phone( $phone ) {
	// Remove all non-digit characters
	$digits = preg_replace( '/\D+/', '', $phone );
	// Format if 10 digits
	if ( strlen( $digits ) === 10 ) {
		return substr( $digits, 0, 3 ) . '-' . substr( $digits, 3, 3 ) . '-' . substr( $digits, 6, 4 );
	}
	// Return original if not 10 digits
	return $phone;
}
	/**
	 * The artifacts like +AC0- are not standard quoted-printable or encoding issues—they are MIME encoded-words artifacts (from email exports), where +AC0- represents a hyphen (-), +AEA- is @, and so on.
	 * These are not standard UTF-8 or ISO-8859-1 issues, but rather specific to how certain email clients encode special characters.
	 * You need to explicitly replace these sequences.
	 * The fixMimeArtifacts function is designed to handle these specific cases.
	 *
	 * @param [type] $value
	 * @return void
	 */
	// private function cota_fix_mime_artifacts($value) {
function cota_fix_mime_artifacts( $value ) {
	// Replace common MIME artifacts
	$patterns = array(
		'/\+AC0-/' => '-',   // hyphen
		'/\+AEA-/' => '@',   // at symbol
		'/\+AF8-/' => '_',   // underscore
		'/\+ADw-/' => '<',   // less than
		'/\+AD4-/' => '>',   // greater than
		'/\+ACI-/' => '"',   // double quote
		'/\+AFs-/' => '[',   // left bracket
		'/\+AF0-/' => ']',   // right bracket
		'/\+ACM-/' => '#',   // hash
		'/\+ACY-/' => '&',   // ampersand
		'/\+ACU-/' => '%',   // percent
		'/\+ACQ-/' => '$',   // dollar
		'/\+ACC-/' => ',',   // comma
		'/\+AHs-/' => '{',   // left curly
		'/\+AH0-/' => '}',   // right curly
		'/\+ABw-/' => '\\',  // backslash
		'/\+AB8-/' => '|',   // pipe
		'/\+AC8-/' => '/',   // slash
		'/\+ACs-/' => ';',   // semicolon
		'/\+ACc-/' => ':',   // colon
		'/\+ACo-/' => '*',   // asterisk
		'/\+ACg-/' => '(',   // left paren
		'/\+ACk-/' => ')',   // right paren
		'/\+ADs-/' => ';',   // semicolon
		'/\+ACw-/' => '-',   // hyphen (again)
		'/\+AD0-/' => '=',   // equals
		'/\+AF4-/' => '^',   // caret
		'/\+AC4-/' => '.',   // period
		'/\+ADY-/' => '6',   // 6 (rare)
		'/\+ADc-/' => '7',   // 7 (rare)
		'/\+ADg-/' => '8',   // 8 (rare)
		'/\+ADk-/' => '9',   // 9 (rare)
		// Add more as needed
	);
	return preg_replace( array_keys( $patterns ), array_values( $patterns ), $value );
}

function get_family_id( $familyname ) {
	global $cota_app_settings;
	global $cota_db, $connect;

	require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

	$stmt = $cota_db->conn->prepare( 'SELECT id FROM families WHERE familyname = ?' );
	if ( ! $stmt ) {
		// Prepare failed
		return false;
	}

	if ( ! $stmt->bind_param( 's', $familyname ) ) {
		// Bind failed
		$stmt->close();
		return false;
	}

	if ( ! $stmt->execute() ) {
		// Execute failed
		$stmt->close();
		return false;
	}

	$stmt->bind_result( $family_id );
	if ( $stmt->fetch() ) {
		$stmt->close();
		return $family_id;
	} else {
		// No matching family found
		$stmt->close();
		return false;
	}
}


/**
 * Insert a family into the database and return the new family ID
 * @param array $data Associative array with 1 row of family data = 1 family
 * @return int New family ID in families table || bool false on error
 */
function cota_insert_family( $data ) {
    // echo nl2br( ' Method ' . __METHOD__ . ' loaded' . PHP_EOL );
	require_once __DIR__ . '/bootstrap.php';

	global $cota_app_settings;
	global $cota_db, $connect;

	require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

	// var_dump($data);
	$stmt = $cota_db->conn->prepare(
		'INSERT INTO families (
            familyname, address, address2, city, state, zip, homephone
        ) VALUES (?, ?, ?, ?, ?, ?, ?)'
	);
	if ( ! $stmt ) {
		// Prepare failed
                // echo 'Prepare Failed<br>';
		return false;
	}

	$homephone = cota_format_phone( $data['homephone'] );

	if ( ! $stmt->bind_param(
		'sssssss',
		$data['familyname'],
		$data['address'],
		$data['address2'],
		$data['city'],
		$data['state'],
		$data['zip'],
		$homephone
	) ) {
		// Bind failed
		$stmt->close();
        // echo 'Bind Failed<br>';
		return false;
	}

	if ( ! $stmt->execute() ) {
		// Execute failed
		$stmt->close();
        // echo 'Execute Failed<br>';
		return false;
	}

	$family_id = $stmt->insert_id;
	$stmt->close();
    // echo 'Family ID : ' . htmlspecialchars( $family_id ) . '<br>';

    // var_dump($family_id);
    // echo 'Nothing Failed<br>';
	return $family_id;
}

	/**
	 * Insert a member into the database
	 *
	 * Table: members will have a record for EVERY family member
	 *
	 * @param int $family_id
	 * @param array $data Associative array with 1 row of member data = 1 family
	 * @return int New family ID in families table || bool false on error
	 */
function cota_insert_member( $family_id, $data ) {
	global $cota_app_settings;
	global $cota_db, $connect;

	require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';
	error_log(
		print_r(
			(object) array(
				'line' => __LINE__,
				'file' => __FILE__,
				'dump' => array(
					$data,
				),
			),
			true
		)
	);
	$stmt = $cota_db->conn->prepare(
		'INSERT INTO members (
            family_id, first_name, last_name, cell_phone, email, birthday, baptism, anniversary 
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
	);
	if ( ! $stmt ) {
		// Prepare failed
		return false;
	}

	// Format data before insert for consistancy
	$cellphone   = cota_format_phone( $data['cellphone'] );
	$birthday    = cota_format_date( $data['birthday'] );
	$baptism     = cota_format_date( $data['baptism'] );
	$anniversary = cota_format_date( $data['annday'] );

	if ( ! $stmt->bind_param(
		'isssssss',
		$family_id,
		$data['first_name'],
		$data['last_name'],
		$cellphone,
		$data['email'],
		$birthday,
		$baptism,
		$anniversary
	) ) {
		// Bind failed
		$stmt->close();
		return false;
	}
	if ( ! $stmt->execute() ) {
		// Execute failed
		$stmt->close();
		return false;
	}

	$member_id = $stmt->insert_id;
	$stmt->close();
	return $member_id;
}

// Echo header
echo cota_page_header();

// Dump out remainder of import page.
?>
	<div class="cota-import-container">
		<h2>Upload CSV File containing Family listing (not members)</h2>
		<form class="cota-import" method="post" enctype="multipart/form-data">
			<label>Select CSV File:</label>
			<input type="file" name="csv_file" accept=".csv" required>
			<button class="cota-import" type="submit">Upload & Import</button>
		</form>
	</div>
</body>
</html>

<?php
// Process input form
if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_FILES['csv_file'] ) ) {
	$upload_dir = 'uploads/';
	// Sanitize filename: remove dangerous chars, allow only safe chars
	$file_name   = preg_replace( '/[^a-zA-Z0-9_\.-]/', '_', basename( $_FILES['csv_file']['name'] ) );
	$upload_file = $upload_dir . $file_name;

	// Ensure the uploads directory exists
	if ( ! is_dir( $upload_dir ) && ! mkdir( $upload_dir, 0755, true ) ) {
		cota_handle_error( 'Failed to find upload directory.', 101 );
	}

	// Check for upload errors
	if ( $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
		cota_handle_error( 'File upload error. Code: ' . $_FILES['csv_file']['error'], 102 );
	}

	// Move uploaded file & perform import.
	if ( move_uploaded_file( $_FILES['csv_file']['tmp_name'], $upload_file ) ) {
		echo 'Processing file: ' . htmlspecialchars( $file_name ) . '<br>';
		cota_import_families_from_one_line( $upload_file );
	} else {
		cota_handle_error( 'File move error. Code: ' . $_FILES['csv_file']['error'], 103 );
	}

	// cota_read_csv_to_assoc_array($upload_file);
	write_success_notice( 'CSV imported successfully! ' );
	exit;

}
