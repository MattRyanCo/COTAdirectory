<?php
/**
 * This script displays the Search form for editing or deleting vestry members. 
 * It offers optional fields to narrow the search for duplicate last names. 
 * 
 * Upon 'SUBMIT' update-vestry is run to process the input. 
 */

require_once __DIR__ . '/bootstrap.php';



global $cota_db, $connect,  $cota_app_settings;
require_once $cota_app_settings->COTA_APP_INCLUDES . 'headers.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

echo cota_page_header();
$vestry_members = $cota_db->read_vestry_database();
$num_vestry_members = $vestry_members->num_rows;
if ( 0 == $num_vestry_members ) {
	empty_database_alert('Search / Edit / Delete Vestry Entries');
    // exit();
} 
// Grab a query parm if present. 
$familyname = isset($_GET['familyname']) ? $_GET['familyname'] : '';

// Dump out remainder of page.
?>
    <h2>Search / Edit Vestry Member</h2>
    <form class="cota-search" action="../app-includes/update-vestry.php" method="get">
        <label>Enter Vestry Member Family (Last) Name:</label>
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