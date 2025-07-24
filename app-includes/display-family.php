<?php
require_once __DIR__ . '/bootstrap.php';
/**
 * This script displays the Search form for displaying one family.
 * It offers optional fields to narrow the search for duplicate last names.
 *
 * Upson 'SUBMIT' display-family-form-handler is run to process the input.
 */

echo cota_page_header();
$families     = $cota_db->read_family_database();
$num_families = $families->num_rows;
if ( 0 == $num_families ) {
	empty_database_alert( 'Display One Family' );
	exit();
}

// Grab a query parm if present.
$family_name = isset( $_GET['familyname'] ) ? $_GET['familyname'] : '';

// Dump out remainder of page.
?>
	<h2>Display Family</h2>
	<form class="cota-display-family" action="../app-includes/display-one-family.php" method="get">
		<label>Enter Family Name:</label>
		<input type="text" name="familyname" value="<?php echo htmlspecialchars( $family_name ); ?>">
		<p>OPTIONAL: The fields below may be used to differentiate families with same last names. </p>
		<label>Address</label>
		<input type="text" name="address">
		<label>Address 2</label>
		<input type="text" name="address2">
		<button class="cota-display-family" type="submit">Search</button>
	</form>
</body>
</html>
