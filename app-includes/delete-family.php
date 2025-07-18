<?php
global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

// Get ful URL with query string
$full_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// $addresslike = $address2like = '';
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["familyname"])) {
    $familyname = $_GET["familyname"];

    // Check optional search fields
    $addressEntered = !empty(trim($_GET['address'] ?? ''));
    $address2Entered = !empty(trim($_GET['address2'] ?? ''));

    // Fetch family record
    if ( !$addressEntered && !$address2Entered ) {
        // No extra search fields
        $stmt = $conn->prepare(
            "SELECT * FROM families 
            WHERE familyname = ?");
        $stmt->bind_param("s", $familyname);
    } elseif ( $addressEntered && !$address2Entered ) {
        // Extra search field address only entered 
        $addresslike = '%'. $_GET['address'] . '%';
        $stmt = $conn->prepare(
            "SELECT * FROM families 
            WHERE familyname = ? AND address LIKE ?");
        $stmt->bind_param("ss", $familyname, $addresslike);
    } elseif (!$addressEntered && $address2Entered) {
        // Extra search field address2 only entered 
        $address2like = '%'. $_GET['address2'] . '%';
        $stmt = $conn->prepare(
            "SELECT * FROM families 
            WHERE familyname = ? AND address2 LIKE ?");
        $stmt->bind_param("ss", $familyname, $address2like);
    } elseif ($addressEntered && $address2Entered ) {
        // Extra search field address and address2 entered 
        $addresslike = '%'. $_GET['address'] . '%';
        $address2like = '%'. $_GET['address2'] . '%';
        $stmt = $conn->prepare(
        "SELECT * FROM families 
        WHERE familyname = ? 
        AND ( address LIKE ? OR address2 LIKE ?) ");
        $stmt->bind_param("sss", 
            $familyname, 
            $addresslike, 
            $address2like );
    }
 
    // Execute search
    $stmt->execute();
    $result = $stmt->get_result();

    $family = $result->fetch_assoc();
    $stmt->close();
}
    if (!$family) {
        // Echo header
        echo cota_page_header();
        ?>
        <div id="delete-family" class="cota-delete-container">
            <h2>Delete Family</h2>
            <div class="container error-message"><?php echo ucfirst($familyname);?> family not found<br>
            <a href="../app-includes/search-delete.php">Try again with a different spelling</a></div>
            <?php die();
    }
    if ( $result->num_rows > 1 ) {
    // More than 1 result, need to refine. 
        echo cota_page_header();
        ?>
        <div id="delete-family" class="cota-delete-container">
            <h2>Delete Family</h2>
            <div class="container error-message">
                <?php echo $familyname;?> family search returned multiple results.<br><br> 
                <a href="../app-includes/search-delete.php?familyname=<?php echo $familyname;?>&address=&address2=">Please refine your search with the address fields.</a>
            </div>
            <?php die();
    }

    // Fetch members
    $stmt = $conn->prepare("SELECT * FROM members WHERE family_id = ?");
    $stmt->bind_param("i", $family["id"]);
    $stmt->execute();
    $members = $stmt->get_result();
    $stmt->close();

// Echo header
echo cota_page_header();

// Dump out remainder of page. 
?>

    <div id="delete-family" class="cota-import-container">
    <h2>Delete Family</h2>
    <form class="cota-family-delete" action="delete-family-form-handler.php" method="post">
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

        <button class="delall" type="submit" name="delall" >Delete Entire Family From Directory</button>

        <h3>Family Members</h3>
        <div id="members">
            <?php
            $first = true;
            while ($member = $members->fetch_assoc()):
            ?>
            <?php if ($first): ?>
                <div class="member-header" >
                <span style="min-width:5px;">&nbsp;</span>
                <span style="min-width:8px;">&nbsp;</span>
                <span style="min-width:120px;text-align:left;">First</span>
                <span style="min-width:120px;text-align:left;">Last</span>
                <span style="min-width:120px;text-align:left;">Cell</span>
                <span style="min-width:180px;text-align:left;">Email</span>
                <span style="min-width:100px;text-align:left;">Birthday</span>
                <span style="min-width:100px;text-align:left;">Baptism</span><br>
                </div>
            <?php $first = false; endif; ?>
            <div class="member-row">
                <input type="hidden" name="members[id][]" value="<?= $member['id'] ?>" readonly>
                <input type="checkbox" name="delete_member[]" value="<?= $member['id'] ?>">
                <input type="text" name="members[first_name][]" value="<?= htmlspecialchars($member['first_name']) ?>" style="width:120px;" readonly>
                <input type="text" name="members[last_name][]" value="<?= !empty($member['last_name']) ? htmlspecialchars($member['last_name']) : htmlspecialchars($family['familyname'] ?? '') ?>" style="width:120px;" readonly>
                <input type="text" name="members[cell_phone][]" value="<?= htmlspecialchars($member['cell_phone']) ?>" style="width:120px;" readonly>
                <input type="email" name="members[email][]" value="<?= htmlspecialchars($member['email']) ?>" style="width:180px;" readonly>
                <input type="text" name="members[birthday][]" value="<?= htmlspecialchars($member['birthday']) ?>" style="width:100px;" readonly>
                <input type="text" name="members[baptism][]" value="<?= htmlspecialchars($member['baptism']) ?>" style="width:100px;" readonly>


            </div>
            <?php endwhile; ?>
        </div>

        <button class="delselected" type="submit" name="delselected">Delete Selected Members From Family</button>
    </form>
    </div>
</body>
</html>
  