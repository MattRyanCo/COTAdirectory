<?php
require_once '../app-includes/database-functions.php';
require_once '../app-includes/settings.php';

$db = new COTA_Database();
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $db->show_structure();
    if (isset($_POST["confirm"]) && $_POST["confirm"] === "YES") {

        // Check if the members table exists before deleting rows
        $result = $db->query("SHOW TABLES LIKE 'members'");
        if ($result && $result->num_rows > 0) {
            $db->query("DELETE FROM members");
        }

        // Check if the families table exists before deleting rows
        $result = $db->query("SHOW TABLES LIKE 'families'");
        if ($result && $result->num_rows > 0) {
            $db->query("DELETE FROM families");
        }

        // Disable foreign key checks to avoid constraint errors
        // Drop and recreate the members table
        $db->query("SET FOREIGN_KEY_CHECKS=0");
        $db->query("DROP TABLE IF EXISTS members");
        $db->query("SET FOREIGN_KEY_CHECKS=1"); // Re-enable constraints
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
        if ($db->query($createMembersTableSQL) === TRUE) {
            echo "<p style='color: red;'>Database has been reset and tables recreated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error recreating members table: " . $db->conn->error . "</p>";
        }

        // Drop and recreate the families table
        $db->query("SET FOREIGN_KEY_CHECKS=0");
        $db->query("DROP TABLE IF EXISTS families");
        $db->query("SET FOREIGN_KEY_CHECKS=1"); // Re-enable constraints
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
        if ($db->query($createFamiliesTableSQL) !== TRUE) {
            echo "<p style='color: red;'>Error recreating families table: " . $db->conn->error . "</p>";
            $db->close_connection();
            exit;
        }


    } else {
        echo "<p style='color: red;'>Action canceled. No changes were made.</p>";
    }

    $db->close_connection();
}


// Echo header
echo cota_page_header();

// Dump out remainder of page.

?>
<!-- 
    <br><br><br><br><h3>⚠️ Reset Database ⚠️</h3><br><br><br><br>
    <p style="color: red;">WARNING: This will delete **all data** from the database. Proceed with caution!</p> -->

    <div class="cota-reset-db-container">
        <h3>⚠️ Reset Database ⚠️</h3><br><br>
        <p class="warning">WARNING: This will delete **all data** from the database.<br>Proceed with caution!</p>
        <form class="cota-reset-db" method="post">
            <label>Type "YES" to confirm:</label>
            <input type="text" name="confirm" required>
            <button type="submit" style="background-color: red;">Reset Database</button>
        </form>
    </div>

</body>
</html>