<?php
require_once 'cota-database-functions.php';

$db = new COTA_Database();
$conn = $db->get_connection();

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["familyname"])) {
    $familyname = $_GET["familyname"];
    
    // Fetch family record
    $stmt = $conn->prepare("SELECT * FROM families WHERE familyname = ?");
    $stmt->bind_param("s", $familyname);
    $stmt->execute();
    $result = $stmt->get_result();
    $family = $result->fetch_assoc();
    $stmt->close();

    if (!$family) {
        die("Family not found. Try again or Return to the <a href='index.php'>main menu</a>.");
    }

    // Fetch members
    $stmt = $conn->prepare("SELECT * FROM members WHERE family_id = ?");
    $stmt->bind_param("i", $family["id"]);
    $stmt->execute();
    $members = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Family</title>
</head>
<body>
    <h2>Edit Family</h2>
    <form action="cota-update-family.php" method="post">
        <input type="hidden" name="family_id" value="<?= $family['id'] ?>">
        <label>Family Name:</label>
        <input type="text" name="familyname" value="<?= htmlspecialchars($family['familyname']) ?>" required><br>
        <label>Address:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($family['address']) ?>"><br>
        <label>City:</label>
        <input type="text" name="city" value="<?= htmlspecialchars($family['city']) ?>"><br>
        <label>State:</label>
        <input type="text" name="state" value="<?= htmlspecialchars($family['state']) ?>"><br>
        <label>Zip Code:</label>
        <input type="text" name="zip" value="<?= htmlspecialchars($family['zip']) ?>"><br>
        <label>Home Phone:</label>
        <input type="text" name="homephone" value="<?= htmlspecialchars($family['homephone']) ?>"><br>
        <label>annday (MM/DD):</label>
        <input type="text" name="annday" value="<?= htmlspecialchars($family['annday']) ?>"><br>

        <h3>Members</h3>
        <?php while ($member = $members->fetch_assoc()): ?>
            <div>
                <input type="hidden" name="members[id][]" value="<?= $member['id'] ?>">
                <label>Name: </label>
                <input type="text" name="members[first_name][]" value="<?= htmlspecialchars($member['first_name']) ?>">
                <input type="text" name="members[last_name][]" value="<?= !empty($member['last_name']) ? htmlspecialchars($member['last_name']) : htmlspecialchars($family['familyname'] ?? '') ?>">
                <label>Cell: </label>
                <input type="text" name="members[cell_phone][]" value="<?= htmlspecialchars($member['cell_phone']) ?>">
                <label>Email:</label>
                <input type="email" name="members[email][]" value="<?= htmlspecialchars($member['email']) ?>">
                <label>Birthday:</label>
                <input type="text" name="members[birthday][]" value="<?= htmlspecialchars($member['birthday']) ?>">
                <label>Baptism:</label>
                <input type="text" name="members[baptism][]" value="<?= htmlspecialchars($member['baptism']) ?>"><br>
            </div>
        <?php endwhile; ?>

        <button type="submit">Update</button>
    </form>
    <br><p><a href='index.php'>Return to main menu</a></p>
</body>
</html>