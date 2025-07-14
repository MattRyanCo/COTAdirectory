<?php
require_once '../app-includes/database-functions.php';
require_once '../app-includes/settings.php';

global $cotadb, $conn;

// GEt ful URL with query string
$full_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

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

        // Echo header
        echo cota_page_header();
        // Dump out remainder of import page. 
        echo "<div class='cota-delete-container'>";
        echo "<h2>Family deleted successfully!</h2>";
        echo "</div>";

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

        // Echo header
        echo cota_page_header();
        // Dump out remainder of import page. 
        echo "<div class='cota-delete-container'>";
        echo "<h2>Selected member(s) deleted successfully!</h2>";
        echo "</div>";

    } else if (isset($_POST['delselected'])) {
        echo "<h2>No members selected for deletion.</h2>";
    }
}

$cotadb->close_connection();