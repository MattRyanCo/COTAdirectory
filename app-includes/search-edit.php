<?php
/**
 * This script displays the Search form for editing or deleting families. 
 * It offers optional fields to narrow the search for duplicate last names. 
 * 
 * Upon 'SUBMIT' edit-family is run to process the input. 
 */

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect,  $cota_app_settings;
require_once $cota_app_settings->COTA_APP_INCLUDES . 'headers.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

echo cota_page_header();

// Backup the db before any edits are made. This is a precautionary measure to ensure that we have a backup of the database before any changes are made.
$cota_db->dump_database( TRUE, 'EDIT' );

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
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-bold">Enter Family Name:</label>
                <input class="form-control" type="text" name="familyname" value="<?php echo htmlspecialchars($familyname); ?>">
            </div>
            <div class="col-12">
                <p class="mb-0">OPTIONAL: The fields below may be used to differentiate families with same last names.</p>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-bold">Address</label>
                <input class="form-control" type="text" name="address">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-bold">Address 2</label>
                <input class="form-control" type="text" name="address2">
            </div>
            <div class="col-12">
                <button class="w-100" type="submit">Search Family to Edit</button>
            </div>
        </div>
    </form>
</body>
</html>