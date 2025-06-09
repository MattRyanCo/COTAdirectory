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
    <title>Delete Family or Family Member</title>
    <link rel="stylesheet" href="../app-assets/css/styles.css">
</head>
<body>
    <h2>Delete Family</h2>
    <form class="cota-family-delete" action="cota-delete-family-form-handler.php" method="post">
        <input type="hidden" name="family_id" value="<?= $family['id'] ?>">
        <label>Family Name</label>
        <input type="text" name="familyname" value="<?= htmlspecialchars($family['familyname']) ?>" readonly>
        <label>Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($family['address']) ?>" readonly>
        <label>City</label>
        <input type="text" name="city" value="<?= htmlspecialchars($family['city']) ?>" readonly>
        <label>State</label>
        <input type="text" name="state" value="<?= htmlspecialchars($family['state']) ?>" readonly>
        <label>Zip</label>
        <input type="text" name="zip" value="<?= htmlspecialchars($family['zip']) ?>" readonly>
        <label>Home Phone</label>
        <input type="text" name="homephone" value="<?= htmlspecialchars($family['homephone']) ?>" readonly>
        <label>Anniversary</label>
        <input type="text" name="annday" value="<?= htmlspecialchars($family['annday']) ?>" readonly>

        <button type="submit" name="delall" >Delete Family From Directory-Submit</button>

        <h3>Family Members</h3>
        <div id="members">
            <?php
            $first = true;
            while ($member = $members->fetch_assoc()):
            ?>
            <?php if ($first): ?>
                <div class="member-header" >
                <!-- <span style="min-width:120px;">First</span>
                <span style="min-width:120px;">Last</span>
                <span style="min-width:120px;">Cell</span>
                <span style="min-width:180px;">Email</span>
                <span style="min-width:100px;">Birthday</span>
                <span style="min-width:100px;">Baptism</span> -->
                <span style="min-width:75px;">Delete selected member(s)</span>
                </div>
            <?php $first = false; endif; ?>
            <div class="member-row">
                <input type="hidden" name="members[id][]" value="<?= $member['id'] ?>" readonly>
                <input type="text" name="members[first_name][]" value="<?= htmlspecialchars($member['first_name']) ?>" style="width:120px;" readonly>
                <input type="text" name="members[last_name][]" value="<?= !empty($member['last_name']) ? htmlspecialchars($member['last_name']) : htmlspecialchars($family['familyname'] ?? '') ?>" style="width:120px;" readonly>
                <input type="tel" name="members[cell_phone][]" value="<?= htmlspecialchars($member['cell_phone']) ?>" style="width:120px;" readonly>
                <input type="email" name="members[email][]" value="<?= htmlspecialchars($member['email']) ?>" style="width:180px;" readonly>
                <input type="text" name="members[birthday][]" value="<?= htmlspecialchars($member['birthday']) ?>" style="width:100px;" readonly>
                <input type="text" name="members[baptism][]" value="<?= htmlspecialchars($member['baptism']) ?>" style="width:100px;" readonly>
                <!-- <input type="checkbox" id="delete_member" name="delete_member"> -->
                <input type="checkbox" name="delete_member[]" value="<?= $member['id'] ?>">

            </div>
            <?php endwhile; ?>
        </div>

        <button type="submit" name="delselected">Delete Selected Members From Family</button>
    </form>
    
    <button class="main-menu-return" type="button" ><a href='index.php'>Return to Main Menu</a></button>
</body>
</html>
  