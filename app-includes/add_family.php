<?php
require_once '../app-includes/database_functions.php';

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize & Validate Family Data
    $familyname = sanitize($_POST["familyname"]);
    $address = sanitize($_POST["address"]);
    $city = sanitize($_POST["city"]);
    $state = sanitize($_POST["state"]);
    $zip = sanitize($_POST["zip"]);
    $homephone = sanitize($_POST["homephone"]);
    $annday = formatDate($_POST["annday"]);

    // Insert family record using prepared statements
    $stmt = $conn->prepare("INSERT INTO families (familyname, address, city, state, zip, homephone, annday) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $familyname, $address, $city, $state, $zip, $homephone, $annday);

    if ($stmt->execute()) {
        $family_id = $stmt->insert_id;
        $stmt->close();

        // Insert members with validation
        $first_names = $_POST["members"]["first_name"];
        // if (!is_array($first_names)) {
        //     $first_names = [$first_names];
        //     // Repeat for other member fields as well
        // }
        foreach ($first_names as $key => $first_name) {
            $first_name = sanitize($first_name);
            $last_name = sanitize($_POST["members"]["last_name"][$key]);
            $cell_phone = sanitize($_POST["members"]["cell_phone"][$key]);
            $email = sanitize($_POST["members"]["email"][$key]);
            $birthday = formatDate($_POST["members"]["birthday"][$key]);
            $baptism = formatDate($_POST["members"]["baptism"][$key]);

            if (!empty($first_name) && validateEmail($email) && validateDate($birthday)) {
                $stmt = $conn->prepare("INSERT INTO members (family_id, first_name, last_name, cell_phone, email, birthday, baptism) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssss", $family_id, $first_name, $last_name, $cell_phone, $email, $birthday, $baptism);
                $stmt->execute();
                $stmt->close();
            } else {
                logError("Invalid member data: " . $first_name . " - " . $email . " - " . $birthday);
            }
        }

        echo "Family added successfully!";
        ?>
        <br><p><a href='index.php'>Return to main menu</a></p>
        <?php
    } else {
        logError("SQL Error (execute): " . $stmt->error);
    }
}

$db->closeConnection();

// Sanitize Input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES);
}

// Validate email Format
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Format MM/DD Date Correctly
function formatDate($date) {
    if (preg_match('/^(\\d{1,2})\/(\\d{1,2})(?:\/\\d{2,4})?$/', $date, $matches)) {
        return sprintf("%02d/%02d", $matches[1], $matches[2]);
    }
    return "";
}

// Validate Date (MM/DD Format)
function validateDate($date) {
    return preg_match('/^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])$/', $date);
}

// Log Errors to a File
function logError($message) {
    error_log($message . PHP_EOL, 3, "error_log.log");
}
?>