<?php
/**
 * Download a sample CSV import file: the current column names (read from the
 * database, see csv-layout.php) plus one made-up example family.
 */

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect, $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'csv-layout.php';

// Example values by column name. Any column not listed here is left blank.
$sample_family  = array(
	'familyname' => 'Zook',
	'address'    => '123 Main St',
	'address2'   => '2nd Floor',
	'address3'   => '',
	'city'       => 'Parkesburg',
	'state'      => 'PA',
	'zip'        => '19365',
	'homephone'  => '610-555-1234',
);
$sample_members = array(
	array(
		'first_name'  => 'Joe',
		'last_name'   => '',
		'cell_phone'  => '610-555-9999',
		'email'       => 'joe@example.com',
		'birthday'    => '1980-01-01',
		'baptism'     => '1981-02-02',
		'anniversary' => '2005-06-18',
	),
	array(
		'first_name'  => 'Jane',
		'last_name'   => 'Jackson',
		'cell_phone'  => '610-555-5678',
		'email'       => 'jane@example.com',
		'birthday'    => '1982-03-03',
		'baptism'     => '1983-04-04',
		'anniversary' => '2005-06-18',
	),
	array(
		'first_name'  => 'Joey',
		'last_name'   => '',
		'cell_phone'  => '',
		'email'       => '',
		'birthday'    => '2010-01-01',
		'baptism'     => '2010-02-01',
		'anniversary' => '',
	),
);

$row = array();
foreach ( array_keys( cota_csv_family_columns( $connect ) ) as $col ) {
	$row[] = $sample_family[ $col ] ?? '';
}
foreach ( $sample_members as $member ) {
	foreach ( array_keys( cota_csv_member_columns( $connect ) ) as $col ) {
		$row[] = $member[ $col ] ?? '';
	}
}

$output = cota_csv_start_download( 'directory_export_sample.csv' );
cota_csv_write_row( $output, cota_csv_header( $connect, count( $sample_members ) ) );
cota_csv_write_row( $output, $row );
fclose( $output );
$cota_db->close_connection();
