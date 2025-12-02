<?php
/**
 * Member Authentication Class
 * 
 * Provides database-backed authentication for members using their email addresses.
 * Members must exist in the members table and can set a password on first visit.
 * 
 * @package     FamilyDirectory
 * @author      Matt Ryan
 * @version     2.0.0
 */
class COTA_Member_Auth {
	
	private $db;
	
	public function __construct( $database_connection ) {
		$this->db = $database_connection;
	}
	
	/**
	 * Check if authentication is enabled
	 * 
	 * @return bool True if authentication should be enforced
	 */
	public static function is_auth_enabled() {
		// Disable authentication on Windows (local development)
		// if ( PHP_OS_FAMILY === 'Windows' ) {
		// 	return false;
		// }
		
		// Check environment variable to enable/disable auth
		$auth_enabled = $_ENV['AUTH_ENABLED'] ?? 'true';
		return strtolower( $auth_enabled ) === 'true';
	}
	
	/**
	 * Start session if not already started
	 */
	private function start_session() {
		if ( session_status() === PHP_SESSION_NONE ) {
			session_start();
		}
	}
	
	/**
	 * Check if user is authenticated
	 * 
	 * @return bool True if user is authenticated
	 */
	public function is_authenticated() {
		$this->start_session();
		return isset( $_SESSION['member_email'] ) && isset( $_SESSION['member_authenticated'] ) && $_SESSION['member_authenticated'] === true;
	}
	
	/**
	 * Get the authenticated member's email
	 * 
	 * @return string|null The email address or null if not authenticated
	 */
	public function get_authenticated_email() {
		$this->start_session();
		return $_SESSION['member_email'] ?? null;
	}
	
	/**
	 * Get member information by email
	 * 
	 * @param string $email The email address to look up
	 * @return array|null Member data or null if not found
	 */
	public function get_member_by_email( $email ) {
		$email = trim( strtolower( $email ) );
		$stmt = $this->db->prepare( 'SELECT * FROM members WHERE LOWER(email) = ? LIMIT 1' );
		$stmt->bind_param( 's', $email );
		$stmt->execute();
		$result = $stmt->get_result();
		$member = $result->fetch_assoc();
		$stmt->close();
		return $member ? $member : null;
	}
	
	/**
	 * Check if member has a password set
	 * 
	 * @param int $member_id The member ID
	 * @return bool True if password is set
	 */
	public function has_password( $member_id ) {
		$stmt = $this->db->prepare( 'SELECT id FROM member_passwords WHERE member_id = ? LIMIT 1' );
		$stmt->bind_param( 'i', $member_id );
		$stmt->execute();
		$result = $stmt->get_result();
		$has_password = $result->num_rows > 0;
		$stmt->close();
		return $has_password;
	}
	
	/**
	 * Verify password for a member
	 * 
	 * @param string $email The email address
	 * @param string $password The password to verify
	 * @return bool True if password is correct
	 */
	public function verify_password( $email, $password ) {
		$email = trim( strtolower( $email ) );
		
		// Get member
		$member = $this->get_member_by_email( $email );
		if ( ! $member ) {
			return false;
		}
		
		// Get password hash
		$stmt = $this->db->prepare( 'SELECT password_hash FROM member_passwords WHERE member_id = ? LIMIT 1' );
		$stmt->bind_param( 'i', $member['id'] );
		$stmt->execute();
		$result = $stmt->get_result();
		$password_data = $result->fetch_assoc();
		$stmt->close();
		
		if ( ! $password_data ) {
			return false;
		}
		
		// Verify password
		return password_verify( $password, $password_data['password_hash'] );
	}
	
	/**
	 * Set password for a member
	 * 
	 * @param string $email The email address
	 * @param string $password The password to set
	 * @return bool True if password was set successfully
	 */
	public function set_password( $email, $password ) {
		$email = trim( strtolower( $email ) );
		
		// Get member
		$member = $this->get_member_by_email( $email );
		if ( ! $member ) {
			return false;
		}
		
		// Hash password
		$password_hash = password_hash( $password, PASSWORD_DEFAULT );
		
		// Check if password record exists
		$stmt = $this->db->prepare( 'SELECT id FROM member_passwords WHERE member_id = ? LIMIT 1' );
		$stmt->bind_param( 'i', $member['id'] );
		$stmt->execute();
		$result = $stmt->get_result();
		$exists = $result->num_rows > 0;
		$stmt->close();
		
		if ( $exists ) {
			// Update existing password
			$stmt = $this->db->prepare( 'UPDATE member_passwords SET password_hash = ?, email = ? WHERE member_id = ?' );
			$stmt->bind_param( 'ssi', $password_hash, $email, $member['id'] );
		} else {
			// Insert new password
			$stmt = $this->db->prepare( 'INSERT INTO member_passwords (member_id, email, password_hash) VALUES (?, ?, ?)' );
			$stmt->bind_param( 'iss', $member['id'], $email, $password_hash );
		}
		
		$success = $stmt->execute();
		$stmt->close();
		
		return $success;
	}
	
	/**
	 * Authenticate user (login)
	 * 
	 * @param string $email The email address
	 * @param string $password The password
	 * @return bool True if authentication successful
	 */
	public function login( $email, $password ) {
		if ( $this->verify_password( $email, $password ) ) {
			$this->start_session();
			$_SESSION['member_email'] = trim( strtolower( $email ) );
			$_SESSION['member_authenticated'] = true;
			return true;
		}
		return false;
	}
	
	/**
	 * Logout user
	 */
	public function logout() {
		$this->start_session();
		unset( $_SESSION['member_email'] );
		unset( $_SESSION['member_authenticated'] );
		session_destroy();
	}
	
	/**
	 * Require authentication - redirect to login if not authenticated
	 */
	public function require_authentication() {
		if ( ! $this->is_authenticated() ) {
			// Store the current URL to redirect back after login
			$this->start_session();
			$_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
			
			// Redirect to login page
			header( 'Location: /app-includes/login.php' );
			exit;
		}
	}
	
	/**
	 * Check if member needs to set up password (first visit)
	 * 
	 * @param string $email The email address
	 * @return bool True if password needs to be set
	 */
	public function needs_password_setup( $email ) {
		$member = $this->get_member_by_email( $email );
		if ( ! $member ) {
			return false;
		}
		return ! $this->has_password( $member['id'] );
	}
}

