<?php
/**
 * This script displays the Search form for editing or deleting families. 
 * It offers optional fields to narrow the search for duplicate last names. 
 * 
 * Upson 'SUBMIT' edit-family is run to process the input. 
 */

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect,  $cota_constants;
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';

echo cota_page_header();
$families = $cota_db->read_family_database();
$num_families = $families->num_rows;
if ( 0 == $num_families ) {
	empty_database_alert('Search / Edit / Delete Directory Entries');
    exit();
} 
// Grab a query parm if present. 
$familyname = isset($_GET['familyname']) ? $_GET['familyname'] : '';

// Dump out remainder of page.
?>
    <h2>Search / Edit Family</h2>
    <form class="cota-search" action="../app-includes/edit-family.php" method="get">
        <label>Enter Family Name:</label>
        <input type="text" name="familyname" value="<?php echo htmlspecialchars($familyname); ?>">
        <p>OPTIONAL: The fields below may be used to differentiate families with same last names. </p>
        <label>Address</label>
        <input type="text" name="address">
        <label>Address 2</label>
        <input type="text" name="address2">
        <button type="submit">Search</button>
    </form>
</body>
</html>