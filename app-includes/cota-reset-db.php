<?php
require_once '../app-includes/cota-database-functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $db = new COTA_Database();
    $conn = $db->get_connection();

    if (isset($_POST["confirm"]) && $_POST["confirm"] === "YES") {
        // Delete all records from the members table to handle foreign key constraints
        $conn->query("DELETE FROM members");
        $conn->query("DELETE FROM families");

        // Disable foreign key checks to avoid constraint errors
        // Drop and recreate the members table
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $conn->query("DROP TABLE IF EXISTS members");
        $conn->query("SET FOREIGN_KEY_CHECKS=1"); // Re-enable constraints
        $createMembersTableSQL = "
            CREATE TABLE members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                family_id INT NOT NULL,
                first_name VARCHAR(100),
                last_name VARCHAR(100),
                cell_phone VARCHAR(20),
                email VARCHAR(255),
                birthday VARCHAR(5),
                baptism VARCHAR(5),
                FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE
            )
        ";
        if ($conn->query($createMembersTableSQL) === TRUE) {
            echo "<p style='color: red;'>Database has been reset and tables recreated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error recreating members table: " . $conn->error . "</p>";
        }

        // Drop and recreate the families table
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $conn->query("DROP TABLE IF EXISTS families");
        $conn->query("SET FOREIGN_KEY_CHECKS=1"); // Re-enable constraints
        $createFamiliesTableSQL = "
            CREATE TABLE families (
                id INT AUTO_INCREMENT PRIMARY KEY,
                familyname VARCHAR(255) NOT NULL,
                name1 VARCHAR(50),
                name2 VARCHAR(50),
                address VARCHAR(255),
                address2 VARCHAR(255),
                city VARCHAR(100),
                state VARCHAR(10),
                zip VARCHAR(20),
                homephone VARCHAR(20),
                cellphone1 VARCHAR(20),
                cellphone2 VARCHAR(20),
                email1 VARCHAR(50),
                email2 VARCHAR(50),
                bday1 VARCHAR(5),
                bday2 VARCHAR(5),
                bap1 VARCHAR(5),
                bap2 VARCHAR(5),
                annday VARCHAR(5)
            )
        ";
        if ($conn->query($createFamiliesTableSQL) !== TRUE) {
            echo "<p style='color: red;'>Error recreating families table: " . $conn->error . "</p>";
            $db->close_connection();
            exit;
        }


    } else {
        echo "<p style='color: red;'>Action canceled. No changes were made.</p>";
    }

    $db->close_connection();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Database</title>
    <link rel="stylesheet" href="../app-assets/css/styles.css">
</head>
<body>
    <h2>⚠️ Reset Database ⚠️</h2>
    <p style="color: red;">WARNING: This will delete **all data** from the database. Proceed with caution!</p>

    <form class="cota-reset-db" method="post">
        <label>Type "YES" to confirm:</label>
        <input type="text" name="confirm" required>
        <button type="submit" style="background-color: red;">Reset Database</button>
    </form>

        <!-- <br><p><a href='../index.php'>Return to main menu</a></p> -->
    <button class="main-menu-return" type="button" ><a href='index.php'>Return to Main Menu</a></button>

</body>
</html>