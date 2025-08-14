<?php
global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

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
    } elseif ($addressEntered && !$address2Entered ) {
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
        $stmt->bind_param("sss", $familyname, $addresslike, $address2like );
    }
}
    // Execute search
    $stmt->execute();
    $result = $stmt->get_result();
    $family = $result->fetch_assoc();
    $stmt->close();

        // Echo header
    echo cota_page_header();
    if (!$family) {

        ?>
        <div id="display-family" class="cota-display-container">
            <h2>Display Family</h2>
            <div class="container error-message"><?php echo ucfirst($familyname);?> family not found<br><br>
            <a href="../app-includes/display-family.php">Try again with a different spelling.</a></div>
            <?php die();
    } 

    if ( $result->num_rows > 1 ) {
    // More than 1 result, need to refine. 
        ?>
        <div id="display-family" class="cota-display-container">
            <h2>Display Family</h2>
            <div class="container error-message">
                <?php echo $familyname;?> family search returned multiple results.<br><br> 
                <a href="../app-includes/display-family.php?familyname=<?php echo $familyname;?>&address=&address2=">Please refine your search with the address fields.</a>

            </div>
            <?php die();
    }

    // Fetch members
    $stmt = $conn->prepare("SELECT * FROM members WHERE family_id = ?");
    $stmt->bind_param("i", $family["id"]);
    $stmt->execute();
    $members = $stmt->get_result();
    $stmt->close();


// Dump out remainder of import page. 

	echo '<div class="cota-display-one-container">';
	echo '<table class="directory-one-table">';
		echo '<tr><th>Family Name/Address</th><th><i>Family Members</i></th></tr>';
		echo '<tr><th>Home Phone<td><i>Name</i></td><td><i>Email</i></td><td><i>Cell</i></td><td><i>DoB</i></td><td><i>DoBaptism</i></td></th></tr>';

		$individuals = $cotadb->read_members_of_family( $family['id'] );
		echo cota_format_family_listing_for_display($family, $individuals);	
	echo "\n</table></body></html>"; 