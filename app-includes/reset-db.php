<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/bootstrap.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

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

    // $cota_db->show_structure();
    if (isset($_POST["confirm"]) && $_POST["confirm"] === "YES") {

        // Check if both tables exist before attempting any DELETE or DROP operations
        $membersTableExists = false;
        $familiesTableExists = false;

        $result = $cota_db->query("SHOW TABLES LIKE 'members'");
        if ($result && $result->num_rows > 0) {
            $membersTableExists = true;
        }

        $result = $cota_db->query("SHOW TABLES LIKE 'families'");
        if ($result && $result->num_rows > 0) {
            $familiesTableExists = true;
        }

        // Only attempt DELETE if the table exists
        if ($membersTableExists) {
            $cota_db->query("DELETE FROM members");
        }
        if ($familiesTableExists) {
            $cota_db->query("DELETE FROM families");
        }

        // Drop and recreate the families table
        $cota_db->query("SET FOREIGN_KEY_CHECKS=0");
        $cota_db->query("DROP TABLE IF EXISTS families");
        $cota_db->query("SET FOREIGN_KEY_CHECKS=1"); // Re-enable constraints
        $createFamiliesTableSQL = "
            CREATE TABLE families (
                id INT AUTO_INCREMENT PRIMARY KEY,
                familyname VARCHAR(50) NOT NULL,
                address VARCHAR(50),
                address2 VARCHAR(20),
                city VARCHAR(20),
                state VARCHAR(10),
                zip VARCHAR(10),
                homephone VARCHAR(20)
            )
        ";

        if ($cota_db->query($createFamiliesTableSQL) === TRUE) {
            write_success_notice("Database table 'Families' has been reset successfully!");
        } else {
            write_error_notice('Error recreating families table: " . $cota_db->conn->error . "</p>"');
        }


        // Disable foreign key checks to avoid constraint errors
        // Drop and recreate the members table
        $cota_db->query("SET FOREIGN_KEY_CHECKS=0");
        $cota_db->query("DROP TABLE IF EXISTS members");
        $cota_db->query("SET FOREIGN_KEY_CHECKS=1"); // Re-enable constraints
        $createMembersTableSQL = "CREATE TABLE members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                family_id INT NOT NULL,
                first_name VARCHAR(50),
                last_name VARCHAR(50),
                cell_phone VARCHAR(20),
                email VARCHAR(100),
                birthday DATE,
                baptism DATE,
                anniversary DATE,
                FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE )";
                
        $cota_db->query($createMembersTableSQL);
        if ($cota_db->query($createMembersTableSQL) === TRUE) {
            write_success_notice("Database table 'Members' has been reset successfully!");
            write_success_notice("Database tables have been reset successfully!");
        } else {
            echo "<p style='color: red;'>Error recreating members table: " . $cota_db->conn->error . "</p>";
            $cota_db->close_connection();
            exit(1);
        }



    } else {
        // echo "<p style='color: red;'>Action canceled. No changes were made.</p>";
        write_error_notice('Action canceled. No changes were made.');
    }

    $cota_db->close_connection();
    exit();
}

