<?php
/**
 * Export the whole directory to CSV, one line per family.
 *
 * Columns come from the database itself (see csv-layout.php), so every family
 * and member field is included, and the file can be loaded back with Import.
 */

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect, $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'csv-layout.php';

$family_columns = array_keys( cota_csv_family_columns( $connect ) );
$member_columns = array_keys( cota_csv_member_columns( $connect ) );
$max_members    = cota_csv_max_family_size( $connect );

$output = cota_csv_start_download( 'directory_export.csv' );
cota_csv_write_row( $output, cota_csv_header( $connect, $max_members ) );

$families = $connect->query( 'SELECT * FROM families ORDER BY familyname, id' );
$stmt     = $connect->prepare( 'SELECT * FROM members WHERE family_id = ? ORDER BY id' );

while ( $family = $families->fetch_assoc() ) {
	$row = array();
	foreach ( $family_columns as $col ) {
		$row[] = $family[ $col ] ?? '';
	}

	// Members in the order they were entered, so the primary adults stay first.
	$stmt->bind_param( 'i', $family['id'] );
	$stmt->execute();
	$members = $stmt->get_result();
	$count   = 0;
	while ( $member = $members->fetch_assoc() ) {
		foreach ( $member_columns as $col ) {
			$row[] = $member[ $col ] ?? '';
		}
		++$count;
	}

	// Pad smaller families so every line has the same number of columns.
	for ( ; $count < $max_members; $count++ ) {
		foreach ( $member_columns as $col ) {
			$row[] = '';
		}
	}

	cota_csv_write_row( $output, $row );
}

$stmt->close();
fclose( $output );
$cota_db->close_connection();
