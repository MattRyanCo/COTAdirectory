<?php

global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';



// Echo header
echo cota_page_header();

// Dump out remainder of page.

?>
    <div class="cota-reset-db-container">
        <h3>⚠️ Reset Database ⚠️</h3><br><br>
        <p class="warning">WARNING: This will delete **all data** from the database.<br>Proceed with caution!</p>
        <form class="cota-reset-db" method="post">
            <label>Type "YES" to confirm.<br>Case sensitive.</label>
            <input type="text" name="confirm" required>
            <button type="submit" style="background-color: red;">Reset Database</button>
        </form>
    </div>

</body>
</html>
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // $cotadb->show_structure();
    if (isset($_POST["confirm"]) && $_POST["confirm"] === "YES") {

        // Check if the members table exists before deleting rows
        $result = $cotadb->query("SHOW TABLES LIKE 'members'");
        if ($result && $result->num_rows > 0) {
            $cotadb->query("DELETE FROM members");
        }

        // Check if the families table exists before deleting rows
        $result = $cotadb->query("SHOW TABLES LIKE 'families'");
        if ($result && $result->num_rows > 0) {
            $cotadb->query("DELETE FROM families");
        }

        // Disable foreign key checks to avoid constraint errors
        // Drop and recreate the members table
        $cotadb->query("SET FOREIGN_KEY_CHECKS=0");
        $cotadb->query("DROP TABLE IF EXISTS members");
        $cotadb->query("SET FOREIGN_KEY_CHECKS=1"); // Re-enable constraints
        $createMembersTableSQL = "
            CREATE TABLE members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                family_id INT NOT NULL,
                first_name VARCHAR(100),
                last_name VARCHAR(100),
                cell_phone VARCHAR(20),
                email VARCHAR(255),
                birthday DATE,
                baptism DATE,
                FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE
            )
        ";
        if ($cotadb->query($createMembersTableSQL) === TRUE) {
            write_success_notice("Database table 'Members' has been reset successfully!");
        } else {
            echo "<p style='color: red;'>Error recreating members table: " . $cotadb->conn->error . "</p>";
            $cotadb->close_connection();
            exit(1);
        }

        // Drop and recreate the families table
        $cotadb->query("SET FOREIGN_KEY_CHECKS=0");
        $cotadb->query("DROP TABLE IF EXISTS families");
        $cotadb->query("SET FOREIGN_KEY_CHECKS=1"); // Re-enable constraints
        $createFamiliesTableSQL = "
            CREATE TABLE families (
                id INT AUTO_INCREMENT PRIMARY KEY,
                familyname VARCHAR(255) NOT NULL,
                fname1 VARCHAR(50),
                fname2 VARCHAR(50),
                lname2 VARCHAR(50),
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
                bday1 DATE,
                bday2 DATE,
                bap1 DATE,
                bap2 DATE,
                annday DATE
            )
        ";

        if ($cotadb->query($createFamiliesTableSQL) === TRUE) {
            write_success_notice("Database tables have been reset successfully!");
        } else {
            write_error_notice('Error recreating families table: " . $cotadb->conn->error . "</p>"');
        }
    } else {
        // echo "<p style='color: red;'>Action canceled. No changes were made.</p>";
        write_error_notice('Action canceled. No changes were made.');
    }

    $cotadb->close_connection();
    exit();
}

