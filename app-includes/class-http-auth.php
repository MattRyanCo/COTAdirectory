<?php
/**
 * HTTP Basic Authentication Class
 * 
 * Provides basic HTTP authentication for the application.
 * Credentials are stored in environment variables for security.
 * 
 * @package     FamilyDirectory
 * @author      Matt Ryan
 * @version     1.0.0
 */
class COTA_HTTP_Auth {
	
	/**
	 * Check if authentication is enabled
	 * 
	 * @return bool True if authentication should be enforced
	 */
	private static function is_auth_enabled() {
		// Disable authentication on Windows (local development)
		// if ( PHP_OS_FAMILY === 'Windows' ) {
		// 	return false;
		// }
		
		// Check environment variable to enable/disable auth
		$auth_enabled = $_ENV['AUTH_ENABLED'] ?? 'true';
		return strtolower( $auth_enabled ) === 'true';
	}
	
	/**
	 * Get the expected username from environment variables
	 * 
	 * @return string|null The username or null if not set
	 */
	private static function get_auth_username() {
		return $_ENV['AUTH_USER'] ?? null;
	}
	
	/**
	 * Get the expected password from environment variables
	 * 
	 * @return string|null The password or null if not set
	 */
	private static function get_auth_password() {
		return $_ENV['AUTH_PASSWORD'] ?? null;
	}
	
	/**
	 * Get the realm name for the authentication prompt
	 * 
	 * @return string The realm name
	 */
	private static function get_realm() {
		return $_ENV['AUTH_REALM'] ?? 'COTA Family Directory';
	}
	
	/**
	 * Check if the provided credentials are valid
	 * 
	 * @param string $username The username to check
	 * @param string $password The password to check
	 * @return bool True if credentials are valid
	 */
	private static function validate_credentials( $username, $password ) {
		$expected_username = self::get_auth_username();
		$expected_password = self::get_auth_password();
		
		// If credentials are not configured, deny access
		if ( empty( $expected_username ) || empty( $expected_password ) ) {
			return false;
		}
		
		// Use secure comparison to prevent timing attacks
		return hash_equals( $expected_username, $username ) && 
		       hash_equals( $expected_password, $password );
	}
	
	/**
	 * Send HTTP 401 Unauthorized response and prompt for credentials
	 */
	private static function require_authentication() {
		$realm = self::get_realm();
		
		header( 'WWW-Authenticate: Basic realm="' . $realm . '"' );
		header( 'HTTP/1.0 401 Unauthorized' );
		
		// Output a simple message
		echo '<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>401 Unauthorized</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
			margin: 0;
			background-color: #f5f5f5;
		}
		.container {
			text-align: center;
			padding: 2rem;
			background: white;
			border-radius: 8px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}
		h1 {
			color: #d32f2f;
			margin-bottom: 1rem;
		}
		p {
			color: #666;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>401 Unauthorized</h1>
		<p>Authentication required to access this site.</p>
	</div>
</body>
</html>';
		exit;
	}
	
	/**
	 * Authenticate the user
	 * 
	 * This method checks if authentication is enabled and validates
	 * the provided credentials. If authentication fails, it sends
	 * a 401 response and exits.
	 */
	public static function authenticate() {
		// Skip authentication if disabled
		if ( ! self::is_auth_enabled() ) {
			return true;
		}
		
		// Get credentials from server (set by web server)
		$username = $_SERVER['PHP_AUTH_USER'] ?? null;
		$password = $_SERVER['PHP_AUTH_PW'] ?? null;
		
		// Handle Authorization header manually (for Nginx or other servers)
		if ( empty( $username ) && ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$auth_header = $_SERVER['HTTP_AUTHORIZATION'];
			if ( preg_match( '/Basic\s+(.*)$/i', $auth_header, $matches ) ) {
				$decoded = base64_decode( $matches[1], true );
				if ( $decoded !== false ) {
					list( $username, $password ) = explode( ':', $decoded, 2 );
				}
			}
		}
		
		// If no credentials provided, request them
		if ( empty( $username ) || empty( $password ) ) {
			self::require_authentication();
			return false;
		}
		
		// Validate credentials
		if ( ! self::validate_credentials( $username, $password ) ) {
			self::require_authentication();
			return false;
		}
		
		// Authentication successful
		return true;
	}
}

