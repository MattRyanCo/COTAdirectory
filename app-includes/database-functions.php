<?php
// echo nl2br(__FILE__ . ' loaded' . PHP_EOL);
class COTA_Database {
    private $conn;
    
    public function __construct() {
        // echo nl2br(' Method ' . __METHOD__ . ' loaded' . PHP_EOL);
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        // var_dump($this->conn);
        if ($this->conn->connect_error) {
            die("Connection failed: Errno " . $this->conn->connect_error . ' Error ' . $this->conn->connect_error);
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

    public function read_a_family() {
        $families = $this->conn->query("SELECT * FROM families WHERE family_id = " . $family_id);
        if ($families === FALSE) {
            die("Error: " . $this->conn->error);
        }
        return $families;
    }

    public function read_members_of_family( $family_id ) {
        $members = $this->conn->query("SELECT * FROM members WHERE family_id = " . $family_id);
        if ($members === FALSE) {
            die("Error: " . $this->conn->error);
        }
        return $members;
    }

    public function activate_reporting() {
        $this->report_mode = MYSQLI_REPORT_ALL;
        return;
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

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
?>
