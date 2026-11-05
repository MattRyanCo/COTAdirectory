<?php
/**
 * Import CSV Data: add families (and their members) from a CSV file.
 *
 * The expected columns come from the database (see csv-layout.php): one line
 * per family, family columns first, then numbered member columns such as
 * first_name1, last_name1, ... first_name2, ... Columns are matched by name,
 * so their order doesn't matter. Pre-4.2.0 files (fname1, bday1, annday, ...)
 * are still accepted.
 *
 * Importing always ADDS families; it never updates or replaces existing ones.
 */
require_once __DIR__ . '/bootstrap.php';
global $cota_app_settings, $cota_db, $connect;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'csv-layout.php';

/**
 * Read a CSV file into an array of rows keyed by (normalized) column name.
 *
 * @param string $filename Path to the CSV file.
 * @return array<int, array{line:int, data:array<string,string>}>
 */
function cota_read_csv_to_assoc_array( $filename ) {
	$rows   = array();
	$handle = fopen( $filename, 'r' );
	if ( false === $handle ) {
		return $rows;
	}

	$headers = cota_csv_read_row( $handle );
	if ( false === $headers ) {
		fclose( $handle );
		return $rows;
	}
	// Remove a UTF-8 byte order mark from the first column name, if present.
	$headers[0]   = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $headers[0] );
	$headers      = cota_csv_normalize_header( $headers );
	$header_count = count( $headers );
	$line_number  = 1;

	while ( ( $row = cota_csv_read_row( $handle ) ) !== false ) {
		++$line_number;
		if ( array( null ) === $row ) {
			continue; // Blank line.
		}

		foreach ( $row as &$value ) {
			// Clean up encodings left behind by spreadsheet and email programs.
			$value = trim( mb_convert_encoding( quoted_printable_decode( (string) $value ), 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252' ) );
			$value = cota_fix_mime_artifacts( $value );
		}
		unset( $value );

		$row_count = count( $row );
		if ( $row_count !== $header_count ) {
			cota_log_error( "CSV line {$line_number}: Column count mismatch. Headers: {$header_count}, Row: {$row_count}." );
			$row = ( $row_count < $header_count ) ? array_pad( $row, $header_count, '' ) : array_slice( $row, 0, $header_count );
		}

		$rows[] = array(
			'line' => $line_number,
			'data' => array_combine( $headers, $row ),
		);
	}
	fclose( $handle );
	return $rows;
}

/**
 * Convert one CSV value to what the database column expects.
 *
 * - date columns: mm/dd, mm/dd/yyyy or yyyy-mm-dd -> yyyy-mm-dd (blank or unreadable -> NULL)
 * - phone columns: 10 digits -> xxx-xxx-xxxx
 * - text columns: cut to the column's maximum length
 *
 * @param string $column Column name.
 * @param array  $info   Column details from cota_csv_table_columns().
 * @param string $value  Value from the CSV.
 * @return string|null
 */
function cota_import_value( $column, $info, $value ) {
	$value = trim( (string) $value );

	if ( 0 === strpos( $info['type'], 'date' ) ) {
		return cota_import_date( $value );
	}
	if ( false !== strpos( $column, 'phone' ) ) {
		$value = cota_format_phone( $value );
	}
	if ( null !== $info['length'] ) {
		$value = mb_substr( $value, 0, $info['length'] );
	}
	return $value;
}

/**
 * Parse a date written as mm/dd, mm/dd/yyyy (or mm/dd/yy) or yyyy-mm-dd.
 * A date without a year gets the current year. Returns NULL when blank or unreadable.
 */
function cota_import_date( $value ) {
	if ( '' === $value ) {
		return null;
	}
	if ( preg_match( '/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $m ) ) {
		list( , $year, $month, $day ) = $m;
	} elseif ( preg_match( '/^(\d{1,2})\/(\d{1,2})(?:\/(\d{2}|\d{4}))?$/', $value, $m ) ) {
		$month = $m[1];
		$day   = $m[2];
		$year  = isset( $m[3] ) ? $m[3] : date( 'Y' );
		if ( 2 === strlen( $year ) ) {
			$year = DateTime::createFromFormat( 'y', $year )->format( 'Y' );
		}
	} else {
		cota_log_error( 'Import: unreadable date "' . $value . '" left blank.' );
		return null;
	}
	if ( ! checkdate( (int) $month, (int) $day, (int) $year ) ) {
		cota_log_error( 'Import: invalid date "' . $value . '" left blank.' );
		return null;
	}
	return sprintf( '%04d-%02d-%02d', $year, $month, $day );
}

/**
 * Insert a row into a table using the given column => value pairs.
 *
 * @return int New row id.
 * @throws mysqli_sql_exception On a database error.
 */
function cota_import_insert( $connect, $table, $values ) {
	$columns      = array_keys( $values );
	$placeholders = implode( ', ', array_fill( 0, count( $columns ), '?' ) );
	$sql          = 'INSERT INTO `' . $table . '` (`' . implode( '`, `', $columns ) . '`) VALUES (' . $placeholders . ')';
	$stmt         = $connect->prepare( $sql );
	$params       = array_values( $values );
	$stmt->bind_param( str_repeat( 's', count( $params ) ), ...$params );
	$stmt->execute();
	$id = $stmt->insert_id;
	$stmt->close();
	return $id;
}

/**
 * Import every family in a CSV file.
 *
 * Each family and its members are saved together: if anything about a family
 * fails, none of that family is saved and the line is reported as skipped.
 *
 * @param string $filename Path to the CSV file.
 * @return array{families:int, members:int, skipped:int[]} Counts, and the line numbers skipped.
 */
function cota_import_families_from_one_line( $filename ) {
	global $connect;

	$family_columns = cota_csv_family_columns( $connect );
	$member_columns = cota_csv_member_columns( $connect );
	$summary        = array(
		'families' => 0,
		'members'  => 0,
		'skipped'  => array(),
	);

	foreach ( cota_read_csv_to_assoc_array( $filename ) as $csv_row ) {
		$row  = $csv_row['data'];
		$line = $csv_row['line'];

		if ( '' === trim( $row['familyname'] ?? '' ) ) {
			cota_log_error( "Import: CSV line {$line} skipped - no family name." );
			$summary['skipped'][] = $line;
			continue;
		}

		// Family values, by database column.
		$family = array();
		foreach ( $family_columns as $col => $info ) {
			$family[ $col ] = cota_import_value( $col, $info, $row[ $col ] ?? '' );
		}

		// Member numbers present in the file (first_name1, first_name2, ...).
		$member_numbers = array();
		foreach ( array_keys( $row ) as $name ) {
			if ( preg_match( '/^first_name(\d+)$/', $name, $m ) ) {
				$member_numbers[] = (int) $m[1];
			}
		}
		sort( $member_numbers );

		// Pre-4.2.0 files have one family-wide anniversary column.
		$family_anniversary = $row['annday'] ?? '';

		$connect->begin_transaction();
		try {
			$family_id     = cota_import_insert( $connect, 'families', $family );
			$members_added = 0;

			foreach ( $member_numbers as $n ) {
				if ( '' === trim( $row[ "first_name{$n}" ] ?? '' ) ) {
					continue; // Unused member slot.
				}
				$member = array( 'family_id' => $family_id );
				foreach ( $member_columns as $col => $info ) {
					$value = $row[ $col . $n ] ?? '';
					if ( 'anniversary' === $col && '' === trim( $value ) ) {
						$value = $family_anniversary;
					}
					$member[ $col ] = cota_import_value( $col, $info, $value );
				}
				cota_import_insert( $connect, 'members', $member );
				++$members_added;
			}

			$connect->commit();
			++$summary['families'];
			$summary['members'] += $members_added;
		} catch ( mysqli_sql_exception $e ) {
			$connect->rollback();
			cota_log_error( "Import: CSV line {$line} skipped - " . $e->getMessage() );
			$summary['skipped'][] = $line;
		}
	}

	return $summary;
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
 * Replace MIME encoded-word artifacts such as +AC0- (a hyphen) or +AEA- (@),
 * which some email programs leave in exported data.
 *
 * @param string $value
 * @return string
 */
function cota_fix_mime_artifacts( $value ) {
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
	);
	return preg_replace( array_keys( $patterns ), array_values( $patterns ), $value );
}

// Echo header
echo cota_page_header();

// Dump out remainder of import page.
?>
	<div class="cota-import-container">
		<h2>Upload CSV File containing Family listing</h2>
		<p>Use <a href="/app-includes/export-sample.php" target="_blank">Export Sample CSV</a> for the expected layout. Imported families are <strong>added</strong> to the directory; existing families aren't changed.</p>
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
	if ( $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
		cota_handle_error( 'File upload error. Code: ' . (int) $_FILES['csv_file']['error'], 102 );
	}

	// Back up the database before adding anything.
	$cota_db->dump_database( true, 'IMPORT' );

	// Import straight from PHP's temporary upload file. The uploaded CSV holds
	// members' personal details, so it is never copied into the website folders;
	// PHP deletes the temporary file when the request ends.
	$file_name = basename( $_FILES['csv_file']['name'] );
	echo '<p>Processing file: ' . htmlspecialchars( $file_name ) . '</p>';
	$summary = cota_import_families_from_one_line( $_FILES['csv_file']['tmp_name'] );

	$message = sprintf(
		'CSV imported successfully! %d %s and %d %s added.',
		$summary['families'],
		1 === $summary['families'] ? 'family' : 'families',
		$summary['members'],
		1 === $summary['members'] ? 'member' : 'members'
	);
	if ( empty( $summary['skipped'] ) ) {
		write_success_notice( $message );
	} else {
		write_error_notice( $message . ' Skipped CSV line(s): ' . implode( ', ', $summary['skipped'] ) . '.' );
	}
	exit;
}
