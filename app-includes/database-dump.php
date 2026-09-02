<?php
/**
 * Database Dump Page
 *
 * Initializes application settings, establishes a database connection,
 * and then dumps the complete database to an sql file for archive.
 */

require_once __DIR__ . '/bootstrap.php';

// Echo header
echo cota_page_header();

$cota_db->dump_database();
// Close the file 
$cota_db->close_connection();
