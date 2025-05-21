<?php
require_once 'database_functions.php';

$db = new Database();
$conn = $db->getConnection();

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
        die("Family not found.");
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
    <form action="update_family.php" method="post">
        <input type="hidden" name="family_id" value="<?= $family['id'] ?>">
        <label>Family Name:</label>
        <input type="text" name="familyname" value="<?= $family['familyname'] ?>" required><br>
        <label>Address:</label>
        <input type="text" name="address" value="<?= $family['address'] ?>"><br>
        <label>City:</label>
        <input type="text" name="city" value="<?= $family['city'] ?>"><br>
        <label>State:</label>
        <input type="text" name="state" value="<?= $family['state'] ?>"><br>
        <label>Zip Code:</label>
        <input type="text" name="zip" value="<?= $family['zip'] ?>"><br>
        <label>Home Phone:</label>
        <input type="text" name="homephone" value="<?= $family['homephone'] ?>"><br>
        <label>annday (MM/DD):</label>
        <input type="text" name="annday" value="<?= $family['annday'] ?>"><br>

        <h3>Members</h3>
        <?php while ($member = $members->fetch_assoc()): ?>
            <div>
                <label>Name:</label>
                <input type="text" name="members[first_name][]" value="<?= $member['first_name'] ?>">
                <label>Cell Phone:</label>
                <input type="text" name="members[cell_phone][]" value="<?= $member['cell_phone'] ?>">
                <label>email:</label>
                <input type="email" name="members[email][]" value="<?= $member['email'] ?>">
                <label>Birthday:</label>
                <input type="text" name="members[birthday][]" value="<?= $member['birthday'] ?>">
                <label>Baptism:</label>
                <input type="text" name="members[baptism][]" value="<?= $member['baptism'] ?>"><br>
            </div>
        <?php endwhile; ?>

        <button type="submit">Update</button>
    </form>
</body>
</html>