<?php

global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

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
            $cell = cota_validate_phone(trim($cell_phones[$i]));
            $email = trim($emails[$i]);
            $bday = cota_format_date( trim($birthdays[$i]) );
            $bap = cota_format_date( trim($baptisms[$i]) );

            $stmt = $conn->prepare("UPDATE members SET first_name=?, last_name=?, cell_phone=?, email=?, birthday=?, baptism=? WHERE id=? AND family_id=?");
            $stmt->bind_param("ssssssii", $fname, $lname, $cell, $email, $bday, $bap, $mid, $family_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Echo header
    echo cota_page_header();
    // Dump out remainder of import page. 
    echo "<div class='cota-update-container'>";
    echo "<h2>" . $familyname . " family updated!</h2>";
    echo "<br><br>";
    echo "<button class='cota-edit-family' type='button'><a href='edit-family.php?familyname=" . urlencode($familyname) . "'>Edit this family again</a></button>";
    echo "<br><br>";
    echo "<button class='cota-search-family' type='button'><a href='search-edit.php'>Edit Another Family</a></button>";

    echo "</div>";

} else {
    echo "<h2>Error: Invalid request.</h2>";
    echo "<p>Please try again or return to the <a href='index.php'>main menu</a>.</p>";
    echo "<button class='main-menu-return' type='button' ><a href='index.php'>Return to Main Menu</a></button>";
}

$cotadb->close_connection();
