<?php
/**
 * This script displays the Search form for deleting families. 
 * It offers optional fields to narraw the search for duplicate last names. 
 * 
 * Upson 'SUBMIT' delete-family is run to process the input. 
 */

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect,  $cota_app_settings;
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

// Echo page header
echo cota_page_header();
$families = $cota_db->read_family_database();
$num_families = $families->num_rows;
if ( 0 == $num_families ) {
	empty_database_alert('Search / Edit / Delete Directory Entries');
    exit();
} 

// Grab a query parm if present. 
$familyname = isset($_GET['familyname']) ? $_GET['familyname'] : '';

// Dump out remainder of import page. 
?>
    <h2>Search / Edit / Delete Family</h2>
    <form class="cota-search" action="../app-includes/delete-family.php" method="get">
        <label>Enter Family Name:</label>
		<input type="text" name="familyname" value="<?php echo htmlspecialchars($familyname); ?>">
        <p>OPTIONAL: The fields below may be used to differentiate families with same last names. </p>
        <label>Address</label>
        <input type="text" name="address">
        <label>Address 2</label>
        <input type="text" name="address2">
        <button type="submit">Search Family to Delete</button>
    </form>
</body>
</html>