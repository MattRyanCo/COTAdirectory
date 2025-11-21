<?php
class COTA_Database {
	// Local
	private const LOCAL_DB_NAME     = 'cotadirectory';
	private const LOCAL_DB_USER     = 'root';
	private const LOCAL_DB_PASSWORD = '';
	private const LOCAL_DB_HOST     = 'localhost';

	// Live - Use environment variables for security
	private const LIVE_DB_NAME     = null; // Set via environment variable DB_NAME
	private const LIVE_DB_USER     = null; // Set via environment variable DB_USER
	private const LIVE_DB_PASSWORD = null; // Set via environment variable DB_PASSWORD
	private const LIVE_DB_HOST     = null; // Set via environment variable DB_HOST

	public $conn;

	public function __construct() {
		$this->conn = new \mysqli(
			self::get_db_host(),
			self::get_db_user(),
			self::get_db_password(),
			self::get_db_name()
		);
		if ( $this->conn->connect_error ) {
			die( 'Connection failed: Errno ' . $this->conn->connect_errno . ' Error ' . $this->conn->connect_error );
		}
	}

	// Selected DB constants - use environment variables for production
	public static function get_db_name() {
		return PHP_OS_FAMILY === 'Windows' ? self::LOCAL_DB_NAME : ( $_ENV['DB_NAME'] ?? self::LIVE_DB_NAME );
	}

	public static function get_db_user() {
		return PHP_OS_FAMILY === 'Windows' ? self::LOCAL_DB_USER : ( $_ENV['DB_USER'] ?? self::LIVE_DB_USER );
	}

	public static function get_db_password() {
		return PHP_OS_FAMILY === 'Windows' ? self::LOCAL_DB_PASSWORD : ( $_ENV['DB_PASSWORD'] ?? self::LIVE_DB_PASSWORD );
	}

	public static function get_db_host() {
		return PHP_OS_FAMILY === 'Windows' ? self::LOCAL_DB_HOST : ( $_ENV['DB_HOST'] ?? self::LIVE_DB_HOST );
	}

	public function get_connection() {
		return $this->conn;
	}

	public function close_connection() {
		$this->conn->close();
	}

	public function read_family_database() {
		$families = $this->conn->query( 'SELECT * FROM families ORDER BY `familyname`' );
		if ( $families === false ) {
			die( 'Error: ' . $this->conn->error );
		}
		return $families;
	}

	public function read_a_family( $family_id ) {
		$statement = $this->conn->prepare( 'SELECT * FROM families WHERE family_id = ?' );
		$statement->bind_param( 'i', $family_id );
		$statement->execute();
		$families = $statement->get_result();
		if ( $families === false ) {
			die( 'Error: ' . $this->conn->error );
		}
		$statement->close();
		return $families;
	}

	public function read_members_of_family( $family_id ) {
		// echo nl2br( PHP_EOL . ' Method ' . __METHOD__ . ' loaded' . PHP_EOL );
		// echo nl2br( PHP_EOL . ' $members of family =========>>> ' . $family_id . PHP_EOL );

		$statement = $this->conn->prepare( 'SELECT * FROM members WHERE family_id = ? ' );
		$statement->bind_param( 'i', $family_id );
		$statement->execute();
		$members = $statement->get_result();
		if ( false === $members ) {
			die( 'Error: ' . $this->conn->error );
		}
		$statement->close();
		// var_dump( $members );

		return $members;
	}

	public function activate_reporting() {
		$this->report_mode = MYSQLI_REPORT_ALL;
		return;
	}

	public function query( $sql ) {
		return $this->conn->query( $sql );
	}

	// public function prepare($sql) {
	//     return $this->conn->prepare($sql);
	// }

	public function show_connection_info() {
		echo nl2br(' Method ' . __METHOD__ . ' loaded' . PHP_EOL);

		// Get connection info
		$host_info = $this->conn->host_info;
		$db_name   = $this->conn->query( 'SELECT DATABASE()' )->fetch_row()[0];
		$user      = $this->conn->query( 'SELECT USER()' )->fetch_row()[0];

		// Parse host and port
		$host         = $this->conn->host_info;
		$host_parts   = explode( ':', $this->conn->host_info );
		$host_display = $host_parts[0];
		$port = isset( $host_parts[1] ) ? $host_parts[1] : 'default';

		echo '<h3>Retrieved Database Connection Information</h3>';
		echo '<ul>';
		echo '<li><strong>Database Name:</strong> ' . htmlspecialchars( $db_name ) . '</li>';
		echo '<li><strong>User:</strong> ' . htmlspecialchars( $user ) . '</li>';
		echo '<li><strong>Host:</strong> ' . htmlspecialchars( $this->conn->host_info ) . '</li>';
		echo '<li><strong>Port:</strong> ' . htmlspecialchars( $port ) . '</li>';
		echo '<li><strong>client_info:</strong> ' . htmlspecialchars( $this->conn->client_info ) . '</li>';
		echo '<li><strong>server_info:</strong> ' . htmlspecialchars( $this->conn->server_info ) . '</li>';
		echo '</ul>';
	}

	public function show_structure() {
		$result = $this->conn->query( 'SHOW TABLES' );
		if ( $result === false ) {
			echo 'Error: ' . $this->conn->error;
			return;
		}
		echo '<ul>';
		while ( $row = $result->fetch_array() ) {
			$table = $row[0];

			// Get record count for the table
			$count_result = $this->conn->query( "SELECT COUNT(*) as cnt FROM `$table`" );
			$count        = 0;
			if ( $count_result && $count_row = $count_result->fetch_assoc() ) {
				$count = $count_row['cnt'];
			}
			if ( $count_result ) {
				$count_result->free();
			}

			echo '<li><strong>' . htmlspecialchars( $table ) . '</strong> (Records: ' . $count . ')';
			$desc = $this->conn->query( "DESCRIBE `$table`" );
			if ( $desc === false ) {
				echo ' (Error: ' . $this->conn->error . ')';
			} else {
				echo '<ul>';
				while ( $col = $desc->fetch_assoc() ) {
					echo '<li>' .
						htmlspecialchars( $col['Field'] ) . ' - ' .
						htmlspecialchars( $col['Type'] ) .
						( isset( $col['Null'] ) ? ' - Null: ' . htmlspecialchars( $col['Null'] ) : '' ) .
						( isset( $col['Key'] ) && $col['Key'] ? ' - Key: ' . htmlspecialchars( $col['Key'] ) : '' ) .
						( isset( $col['Default'] ) && $col['Default'] !== null ? ' - Default: ' . htmlspecialchars( $col['Default'] ) : '' ) .
						( isset( $col['Extra'] ) && $col['Extra'] ? ' - Extra: ' . htmlspecialchars( $col['Extra'] ) : '' ) .
						'</li>';
				}
				echo '</ul>';
				$desc->free();
			}
			echo '</li>';
		}
		echo '</ul>';
		$result->free();
		return;
	}
}
