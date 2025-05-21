<?php
// echo nl2br(__FILE__ . ' loaded' . PHP_EOL);
class Database {
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "cotadirectory");
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn->close();
    }

    public function readDatabase() {
        $families = $this->conn->query("SELECT * FROM families ORDER BY `familyname`");
        if ($families === FALSE) {
            die("Error: " . $this->conn->error);
        }
        return $families;
    }
}
?>
