<?php
/**
 * CSV layout shared by Export CSV Directory, Export Sample CSV and Import CSV Data.
 *
 * The column list is read from the database, so adding a column to the
 * families or members table automatically adds it to the CSV files.
 *
 * Layout (one line per family):
 *   - every families column except `id`, e.g. familyname, address, ... homephone
 *   - then, for each member N = 1, 2, 3, ..., every members column except
 *     `id` and `family_id` with N appended, e.g. first_name1, last_name1, ...
 *
 * Import also accepts the pre-4.2.0 column names (fname1, lname1, bday1, bap1,
 * cell1, email1 and a family-wide annday) so older files still load.
 *
 * @package COTAdirectory
 */

/** Columns that are database bookkeeping, not directory data. */
const COTA_CSV_SKIP_FAMILY_COLUMNS = array( 'id' );
const COTA_CSV_SKIP_MEMBER_COLUMNS = array( 'id', 'family_id' );

/** Pre-4.2.0 member column prefixes => current members column. */
const COTA_CSV_LEGACY_MEMBER_PREFIXES = array(
	'fname' => 'first_name',
	'lname' => 'last_name',
	'bday'  => 'birthday',
	'bap'   => 'baptism',
	'cell'  => 'cell_phone',
	'email' => 'email',
);

/**
 * Describe a table's columns, in table order.
 *
 * @param mysqli $connect Database connection.
 * @param string $table   'families' or 'members'.
 * @param array  $skip    Column names to leave out.
 * @return array<string, array{type:string, length:int|null}> Column name => details.
 */
function cota_csv_table_columns( $connect, $table, $skip = array() ) {
	static $cache = array();
	if ( ! isset( $cache[ $table ] ) ) {
		$cache[ $table ] = array();
		$result          = $connect->query( 'SHOW COLUMNS FROM `' . $connect->real_escape_string( $table ) . '`' );
		while ( $result && ( $col = $result->fetch_assoc() ) ) {
			$type   = strtolower( $col['Type'] );
			$length = preg_match( '/char\((\d+)\)/', $type, $m ) ? (int) $m[1] : null;
			$cache[ $table ][ $col['Field'] ] = array(
				'type'   => $type,
				'length' => $length,
			);
		}
	}
	return array_diff_key( $cache[ $table ], array_flip( $skip ) );
}

/** Family columns used in the CSV. */
function cota_csv_family_columns( $connect ) {
	return cota_csv_table_columns( $connect, 'families', COTA_CSV_SKIP_FAMILY_COLUMNS );
}

/** Member columns used in the CSV (without the member number). */
function cota_csv_member_columns( $connect ) {
	return cota_csv_table_columns( $connect, 'members', COTA_CSV_SKIP_MEMBER_COLUMNS );
}

/**
 * Build the CSV header row.
 *
 * @param mysqli $connect     Database connection.
 * @param int    $max_members Number of member column groups to include.
 * @return string[]
 */
function cota_csv_header( $connect, $max_members ) {
	$header = array_keys( cota_csv_family_columns( $connect ) );
	for ( $n = 1; $n <= $max_members; $n++ ) {
		foreach ( array_keys( cota_csv_member_columns( $connect ) ) as $col ) {
			$header[] = $col . $n;
		}
	}
	return $header;
}

/**
 * Largest number of members in any one family (at least 1).
 */
function cota_csv_max_family_size( $connect ) {
	$result = $connect->query( 'SELECT COALESCE( MAX( cnt ), 0 ) FROM ( SELECT COUNT(*) AS cnt FROM members GROUP BY family_id ) AS counts' );
	$max    = $result ? (int) $result->fetch_row()[0] : 0;
	return max( 1, $max );
}

/**
 * Start a CSV download: send headers, a UTF-8 byte order mark (so Excel shows
 * accented characters correctly) and return the output stream.
 *
 * @param string $filename Download file name.
 * @return resource
 */
function cota_csv_start_download( $filename ) {
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	$output = fopen( 'php://output', 'w' );
	fwrite( $output, "\xEF\xBB\xBF" );
	return $output;
}

/**
 * Write one CSV row. Uses standard (RFC 4180) quoting, matching cota_csv_read_row().
 */
function cota_csv_write_row( $output, $fields ) {
	fputcsv( $output, $fields, ',', '"', '' );
}

/**
 * Read one CSV row, or false at end of file.
 */
function cota_csv_read_row( $handle ) {
	return fgetcsv( $handle, 0, ',', '"', '' );
}

/**
 * Rename pre-4.2.0 column names in a CSV header to the current names.
 *
 * @param string[] $headers Header row as read from the file.
 * @return string[]
 */
function cota_csv_normalize_header( $headers ) {
	foreach ( $headers as $i => $name ) {
		$name = strtolower( trim( $name ) );
		if ( preg_match( '/^(' . implode( '|', array_keys( COTA_CSV_LEGACY_MEMBER_PREFIXES ) ) . ')(\d+)$/', $name, $m ) ) {
			$name = COTA_CSV_LEGACY_MEMBER_PREFIXES[ $m[1] ] . $m[2];
		}
		$headers[ $i ] = $name;
	}
	return $headers;
}
