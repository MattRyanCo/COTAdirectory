<?php
require_once __DIR__ . '/bootstrap.php';
global $cota_db, $connect,  $cota_constants;
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';

$mid = -1; // Default for new family member
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["family_id"])) {

    // Pull off family info from form. 
    $family_id = intval($_POST["family_id"]);
    $familyname = trim($_POST["familyname"]);
    $address = trim($_POST["address"]);
    $city = trim($_POST["city"]);
    $state = trim($_POST["state"]);
    $zip = trim($_POST["zip"]);
    $homephone = trim($_POST["homephone"]);
    // Anniversary date is NULL if blank. All dates are optional and are set to Null if blank. 
    $annday = !empty($_POST['annday']) ? $_POST['annday'] : null;

    // Update family record
    $stmt = $connect->prepare("UPDATE families SET familyname=?, address=?, city=?, state=?, zip=?, homephone=?, annday=? WHERE id=?");
    $stmt->bind_param("sssssssi", $familyname, $address, $city, $state, $zip, $homephone, $annday, $family_id);
    $stmt->execute();
    $stmt->close();

    // Update each member
    //   Pull off any family member info from lower portion of form. 
    if (isset($_POST["members"]["id"])) {
        $member_ids = $_POST["members"]["id"];
        $first_names = $_POST["members"]["first_name"];
        $last_names = $_POST["members"]["last_name"];
        $cell_phones = $_POST["members"]["cell_phone"];
        $emails = $_POST["members"]["email"];

        // Ensure birthdays and baptisms are arrays and set empty values to null
        $birthdays = isset($_POST['members']['birthday']) ? $_POST['members']['birthday'] : [];
        $baptisms = isset($_POST['members']['baptism']) ? $_POST['members']['baptism'] : [];
    }

    for ($i = 0; $i < count($member_ids); $i++) {
        $mid = intval($member_ids[$i]);
        // Check for and add new member here. 
        if ( -1 == $mid && isset($_POST["members"]["first_name"][$i]) && !empty(trim($_POST["members"]["first_name"][$i])) ) {
        // We do have a new member to add. 
            $fname = trim($_POST["members"]["first_name"][$i]);
            $lname = trim($_POST["members"]["last_name"][$i]);
            $cell = cota_validate_phone(trim($_POST["members"]["cell_phone"][$i]));
            $email = trim($_POST["members"]["email"][$i]);
            $bday = isset($_POST['members']['birthday'][$i]) ? trim($_POST['members']['birthday'][$i]) : null;
            $bap = isset($_POST['members']['baptism'][$i]) ? trim($_POST['members']['baptism'][$i]) : null;

            // Set empty date strings to null
            $bday = ($bday === '' ? null : $bday);
            $bap = ($bap === '' ? null : $bap);

            // Insert new member
            $stmt = $connect->prepare("INSERT INTO members (family_id, first_name, last_name, cell_phone, email, birthday, baptism) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $family_id, $fname, $lname, $cell, $email, $bday, $bap);
            $stmt->execute();
            $stmt->close();
        } else {
            $fname = trim($first_names[$i]);
            $lname = trim($last_names[$i]);
            $cell = cota_validate_phone(trim($cell_phones[$i]));
            $email = trim($emails[$i]);
            $bday = isset($birthdays[$i]) ? trim($birthdays[$i]) : null;
            $bap = isset($baptisms[$i]) ? trim($baptisms[$i]) : null;

            // Set empty date strings to null
            $bday = ($bday === '' ? null : $bday);
            $bap = ($bap === '' ? null : $bap);

            $stmt = $connect->prepare("UPDATE members SET first_name=?, last_name=?, cell_phone=?, email=?, birthday=?, baptism=? WHERE id=? AND family_id=?");
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
    echo '<div class="two-button-grid">';
    echo '<div><button class="cota-edit-family" type="button"><a href="edit-family.php?familyname="' . $familyname . '">Edit this family again</a></button></div>';
    echo '<div><button class="cota-search-family" type="button"><a href="search-edit.php">Edit Another Family</a></button></div>';
    echo '</div>';

    echo "</div>";


} else {
    echo "<h2>Error: Invalid request.</h2>";
    echo "<p>Please try again or return to the <a href='index.php'>main menu</a>.</p>";
    echo "<button class='main-menu-return' type='button' ><a href='index.php'>Return to Main Menu</a></button>";
}

$cota_db->close_connection();
