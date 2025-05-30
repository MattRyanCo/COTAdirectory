<?php
// filepath: d:\laragon\www\COTAdirectory\app-includes\cota-update-family.php
require_once 'cota-database-functions.php';

$db = new COTA_Database();
$conn = $db->get_connection();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["family_id"])) {
    $family_id = intval($_POST["family_id"]);
    $familyname = trim($_POST["familyname"]);
    $address = trim($_POST["address"]);
    $city = trim($_POST["city"]);
    $state = trim($_POST["state"]);
    $zip = trim($_POST["zip"]);
    $homephone = trim($_POST["homephone"]);
    $annday = trim($_POST["annday"]);

    // Update family record
    $stmt = $conn->prepare("UPDATE families SET familyname=?, address=?, city=?, state=?, zip=?, homephone=?, annday=? WHERE id=?");
    $stmt->bind_param("sssssssi", $familyname, $address, $city, $state, $zip, $homephone, $annday, $family_id);
    $stmt->execute();
    $stmt->close();

    // Update each member
    if (isset($_POST["members"]["id"])) {
        $member_ids = $_POST["members"]["id"];
        $first_names = $_POST["members"]["first_name"];
        $last_names = $_POST["members"]["last_name"];
        $cell_phones = $_POST["members"]["cell_phone"];
        $emails = $_POST["members"]["email"];
        $birthdays = $_POST["members"]["birthday"];
        $baptisms = $_POST["members"]["baptism"];

        for ($i = 0; $i < count($member_ids); $i++) {
            $mid = intval($member_ids[$i]);
            $fname = trim($first_names[$i]);
            $lname = trim($last_names[$i]);
            $cell = trim($cell_phones[$i]);
            $email = trim($emails[$i]);
            $bday = trim($birthdays[$i]);
            $bap = trim($baptisms[$i]);

            $stmt = $conn->prepare("UPDATE members SET first_name=?, last_name=?, cell_phone=?, email=?, birthday=?, baptism=? WHERE id=? AND family_id=?");
            $stmt->bind_param("ssssssii", $fname, $lname, $cell, $email, $bday, $bap, $mid, $family_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo "<h2>Family updated successfully!</h2>";
    echo "<br><br><p><a href='cota-edit-family.php?familyname=" . urlencode($familyname) . "'>Edit this family again</a></p>";
    echo "<p><a href='cota-search.php'>Edit Another Family</a></p>";
} else {
    echo "<h2>Error: Invalid request.</h2>";
    echo "<p><a href='index.php'>Return to Directory</a></p>";
}

$db->close_connection();
?>
<br><p><a href='index.php'>Return to main menu</a></p>