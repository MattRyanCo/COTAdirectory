<?php
require_once 'cota-database-functions.php';

$db = new COTA_Database();
$conn = $db->get_connection();

    // global $conn;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["family_id"])) {
    $family_id = intval($_POST["family_id"]);

    if (isset($_POST['delall'])) {
        // Delete family members
        $stmt = $conn->prepare("DELETE FROM members WHERE family_id = ?");
        $stmt->bind_param("i", $family_id);
        $stmt->execute();
        $stmt->close();

        // Delete family record
        $stmt = $conn->prepare("DELETE FROM families WHERE id = ?");
        $stmt->bind_param("i", $family_id);
        $stmt->execute();
        $stmt->close();

        echo "<h2>Family deleted successfully!</h2>";
    }
    if (isset($_POST['delselected']) && !empty($_POST['delete_member'])) {
        // Delete only selected members
        foreach ($_POST['delete_member'] as $member_id) {
            $mid = intval($member_id);
            $stmt = $conn->prepare("DELETE FROM members WHERE id = ? AND family_id = ?");
            $stmt->bind_param("ii", $mid, $family_id);
            $stmt->execute();
            $stmt->close();
        }
        echo "<h2>Selected member(s) deleted successfully!</h2>";
    } else if (isset($_POST['delselected'])) {
        echo "<h2>No members selected for deletion.</h2>";
    }
    // echo "<h2>Error: Invalid request.</h2>";
    // echo "<p>Please try again or return to the <a href='index.php'>main menu</a>.</p>";
    echo "<button class='main-menu-return' type='button' ><a href='index.php'>Return to Main Menu</a></button>";
}

$db->close_connection();
?>
<button class="main-menu-return" type="button" ><a href='index.php'>Return to Main Menu</a></button>