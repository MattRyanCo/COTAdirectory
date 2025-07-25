<?php

class COTA_Database {
    // private $conn;

    // Local
    private const LOCAL_DB_NAME = 'cotadirectory';
    private const LOCAL_DB_USER = 'root';
    private const LOCAL_DB_PASSWORD = '';
    private const LOCAL_DB_HOST = 'localhost';

    // Live - Use environment variables for security
    private const LIVE_DB_NAME = null; // Set via environment variable DB_NAME
    private const LIVE_DB_USER = null; // Set via environment variable DB_USER  
    private const LIVE_DB_PASSWORD = null; // Set via environment variable DB_PASSWORD
    private const LIVE_DB_HOST = null; // Set via environment variable DB_HOST

    // Selected DB constants - use environment variables for production
    public static function getDbName() {
        return PHP_OS_FAMILY === 'Windows' ? self::LOCAL_DB_NAME : ($_ENV['DB_NAME'] ?? self::LIVE_DB_NAME);
    }
    
    public static function getDbUser() {
        return PHP_OS_FAMILY === 'Windows' ? self::LOCAL_DB_USER : ($_ENV['DB_USER'] ?? self::LIVE_DB_USER);
    }
    
    public static function getDbPassword() {
        return PHP_OS_FAMILY === 'Windows' ? self::LOCAL_DB_PASSWORD : ($_ENV['DB_PASSWORD'] ?? self::LIVE_DB_PASSWORD);
    }
    
    public static function getDbHost() {
        return PHP_OS_FAMILY === 'Windows' ? self::LOCAL_DB_HOST : ($_ENV['DB_HOST'] ?? self::LIVE_DB_HOST);
    }
    
    public function __construct() {
        $this->conn = new mysqli(
            self::getDbHost(), 
            self::getDbUser(), 
            self::getDbPassword(), 
            self::getDbName());
        // var_dump($this->conn);
        if ($this->conn->connect_error) {
            die("Connection failed: Errno " . $this->conn->connect_errno . ' Error ' . $this->conn->connect_error);
        }
    }

    public function get_connection() {
        return $this->conn;
    }

    public function close_connection() {
        $this->conn->close();
    }

    public function read_family_database() {
        $families = $this->conn->query("SELECT * FROM families ORDER BY `familyname`");
        if ($families === FALSE) {
            die("Error: " . $this->conn->error);
        }
        return $families;
    }

    public function read_a_family($family_id) {
        $stmt = $this->conn->prepare("SELECT * FROM families WHERE family_id = ?");
        $stmt->bind_param("i", $family_id);
        $stmt->execute();
        $families = $stmt->get_result();
        if ($families === FALSE) {
            die("Error: " . $this->conn->error);
        }
        $stmt->close();
        return $families;
    }

    public function read_members_of_family( $family_id ) {
        // @TODO Modify this to return the 1 or 2 primary members first (as noted in family table) 
        //  followed by all the other family members in birthday order. 
        $stmt = $this->conn->prepare("SELECT * FROM members WHERE family_id = ? ORDER BY `birthday`");
        $stmt->bind_param("i", $family_id);
        $stmt->execute();
        $members = $stmt->get_result();
        if ($members === FALSE) {
            die("Error: " . $this->conn->error);
        }
        $stmt->close();
        return $members;
    }

    public function activate_reporting() {
        $this->report_mode = MYSQLI_REPORT_ALL;
        return;
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    // public function prepare($sql) {
    //     return $this->conn->prepare($sql);
    // }

    public function show_connection_info( ) {
        // echo nl2br(' Method ' . __METHOD__ . ' loaded' . PHP_EOL);

        // Get connection info
        $host_info = $this->conn->host_info;
        $db_name = $this->conn->query("SELECT DATABASE()")->fetch_row()[0];
        $user = $this->conn->query("SELECT USER()")->fetch_row()[0];

        // Parse host and port
        $host = $this->conn->host_info;
        $host_parts = explode(":", $this->conn->host_info);
        $host_display = $host_parts[0];
        $port_display = isset($host_parts[1]) ? $host_parts[1] : 'default';

        echo "<h3>Retrieved Database Connection Information</h3>";
        echo "<ul>";
        echo "<li><strong>Database Name:</strong> " . htmlspecialchars($db_name) . "</li>";
        echo "<li><strong>User:</strong> " . htmlspecialchars($user) . "</li>";
        echo "<li><strong>Host:</strong> " . htmlspecialchars($this->conn->host) . "</li>";
        echo "<li><strong>Port:</strong> " . htmlspecialchars($this->conn->port) . "</li>";
        echo "<li><strong>client_info:</strong> " . htmlspecialchars($this->conn->client_info) . "</li>";
        echo "<li><strong>server_info:</strong> " . htmlspecialchars($this->conn->server_info) . "</li>";
        echo "</ul>";
    }

    public function show_structure() {
        // echo nl2br(' Method ' . __METHOD__ . ' loaded' . PHP_EOL);

        $result = $this->conn->query("SHOW TABLES");
        if ($result === FALSE) {
            echo "Error: " . $this->conn->error;
            return;
        }
        echo "<ul>";
        while ($row = $result->fetch_array()) {
            $table = $row[0];

            // Get record count for the table
            $count_result = $this->conn->query("SELECT COUNT(*) as cnt FROM `$table`");
            $count = 0;
            if ($count_result && $count_row = $count_result->fetch_assoc()) {
                $count = $count_row['cnt'];
            }
            if ($count_result) $count_result->free();

            echo "<li><strong>" . htmlspecialchars($table) . "</strong> (Records: " . $count . ")";
            $desc = $this->conn->query("DESCRIBE `$table`");
            if ($desc === FALSE) {
                echo " (Error: " . $this->conn->error . ")";
            } else {
                echo "<ul>";
                while ($col = $desc->fetch_assoc()) {
                    echo "<li>" . 
                        htmlspecialchars($col['Field']) . " - " . 
                        htmlspecialchars($col['Type']) . 
                        (isset($col['Null']) ? " - Null: " . htmlspecialchars($col['Null']) : "") . 
                        (isset($col['Key']) && $col['Key'] ? " - Key: " . htmlspecialchars($col['Key']) : "") . 
                        (isset($col['Default']) && $col['Default'] !== null ? " - Default: " . htmlspecialchars($col['Default']) : "") . 
                        (isset($col['Extra']) && $col['Extra'] ? " - Extra: " . htmlspecialchars($col['Extra']) : "") .
                        "</li>";
                }
                echo "</ul>";
                $desc->free();
            }
            echo "</li>";
        }
        echo "</ul>";
        $result->free();
        return;
    }
}
global $cotadb, $conn;

$cotadb = new COTA_Database();
$conn = $cotadb->get_connection();