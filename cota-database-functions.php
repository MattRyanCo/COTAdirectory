<?php
// echo nl2br(__FILE__ . ' loaded' . PHP_EOL);
class COTA_Database {
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "cotadirectory");
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function get_connection() {
        return $this->conn;
    }

    public function close_connection() {
        $this->conn->close();
    }

    public function read_database() {
        $families = $this->conn->query("SELECT * FROM families ORDER BY `familyname`");
        if ($families === FALSE) {
            die("Error: " . $this->conn->error);
        }
        return $families;
    }
}
?>
