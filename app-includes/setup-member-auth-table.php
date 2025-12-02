<?php
/**
 * Setup script for member authentication table
 * Run this once to create the member_passwords table
 * 
 * This script creates a table to store password hashes for members
 * who want to authenticate using their email address.
 */

ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );

require_once __DIR__ . '/bootstrap.php';

// Check if table already exists
$table_exists = false;
$result = $cota_db->query( "SHOW TABLES LIKE 'member_passwords'" );
if ( $result && $result->num_rows > 0 ) {
	$table_exists = true;
}

if ( ! $table_exists ) {
	// Create the member_passwords table
	$createTableSQL = "CREATE TABLE member_passwords (
		id INT AUTO_INCREMENT PRIMARY KEY,
		member_id INT NOT NULL,
		email VARCHAR(100) NOT NULL UNIQUE,
		password_hash VARCHAR(255) NOT NULL,
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
		INDEX idx_email (email)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

	if ( $cota_db->query( $createTableSQL ) === true ) {
		echo '<div style="padding: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin: 20px;">';
		echo '<h3>✓ Success!</h3>';
		echo '<p>The <code>member_passwords</code> table has been created successfully.</p>';
		echo '<p>You can now use the member authentication system.</p>';
		echo '</div>';
	} else {
		echo '<div style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;">';
		echo '<h3>✗ Error</h3>';
		echo '<p>Error creating table: ' . htmlspecialchars( $cota_db->conn->error ) . '</p>';
		echo '</div>';
	}
} else {
	echo '<div style="padding: 20px; background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; border-radius: 4px; margin: 20px;">';
	echo '<h3>ℹ Information</h3>';
	echo '<p>The <code>member_passwords</code> table already exists. No action needed.</p>';
	echo '</div>';
}

$cota_db->close_connection();

