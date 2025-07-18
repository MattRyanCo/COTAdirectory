<?php
/**
 * This script displays the Search form for deleting families. 
 * It offers optional fields to narraw the search for duplicate last names. 
 * 
 * Upson 'SUBMIT' delete-family is run to process the input. 
 */
global $cotadb, $conn, $cota_constants;
require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

// Echo page header
echo cota_page_header();
$families = $cotadb->read_family_database();
$num_families = $families->num_rows;
if ( 0 == $num_families ) {
	empty_database_alert('Search / Edit / Delete Directory Entries');
    exit();
} 

// Dump out remainder of import page. 
?>
    <h2>Search / Edit / Delete Family</h2>
    <form class="cota-search" action="../app-includes/delete-family.php" method="get">
        <label>Enter Family Name:</label>
        <input type="text" name="familyname" required>
        <p>OPTIONAL: The fields below may be used to differentiate families with same last names. </p>
        <label>Address</label>
        <input type="text" name="address">
        <label>Address 2</label>
        <input type="text" name="address2">
        <button type="submit">Search Family to Delete</button>
    </form>
</body>
</html>