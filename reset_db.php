<?php
require_once 'database_functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $db = new Database();
    $conn = $db->getConnection();

    if (isset($_POST["confirm"]) && $_POST["confirm"] === "YES") {
        // Delete all records from the members table to handle foreign key constraints
        $conn->query("DELETE FROM members");
        $conn->query("DELETE FROM families");

        // Drop and recreate the families table
        $conn->query("DROP TABLE IF EXISTS families");
        $createFamiliesTableSQL = "
            CREATE TABLE families (
                id INT AUTO_INCREMENT PRIMARY KEY,
                family_name VARCHAR(255) NOT NULL,
                primary_name_1 VARCHAR(50),
                primary_name_2 VARCHAR(50),
                address VARCHAR(255),
                address2 VARCHAR(255),
                city VARCHAR(100),
                state VARCHAR(10),
                zip VARCHAR(20),
                home_phone VARCHAR(20),
                primary_cell_1 VARCHAR(20),
                primary_cell_2 VARCHAR(20),
                primary_email_1 VARCHAR(50),
                primary_email_2 VARCHAR(50),
                primary_bday_1 VARCHAR(5),
                primary_bday_2 VARCHAR(5),    
                anniversary VARCHAR(5),
                update_info TEXT
            )
        ";
        if ($conn->query($createFamiliesTableSQL) !== TRUE) {
            echo "<p style='color: red;'>Error recreating families table: " . $conn->error . "</p>";
            $db->closeConnection();
            exit;
        }

        // Drop and recreate the members table
        $conn->query("DROP TABLE IF EXISTS members");
        $createMembersTableSQL = "
            CREATE TABLE members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                family_id INT NOT NULL,
                first_name VARCHAR(100),
                last_name VARCHAR(100),
                cell_phone VARCHAR(20),
                email VARCHAR(255),
                birthday VARCHAR(5),
                FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE
            )
        ";
        if ($conn->query($createMembersTableSQL) === TRUE) {
            echo "<p style='color: red;'>Database has been reset and tables recreated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error recreating members table: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Action canceled. No changes were made.</p>";
    }

    $db->closeConnection();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Database</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>⚠️ Reset Database ⚠️</h2>
    <p style="color: red;">WARNING: This will delete **all data** from the database. Proceed with caution!</p>

    <form method="post">
        <label>Type "YES" to confirm:</label>
        <input type="text" name="confirm" required>
        <button type="submit" style="background-color: red;">Reset Database</button>
    </form>

    <br><p><a href='index.php'>Return to main menu</a></p>
</body>
</html>