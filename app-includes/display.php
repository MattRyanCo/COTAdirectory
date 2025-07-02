<?php
require_once '../app-includes/database-functions.php';
require_once '../app-includes/format-family-listing.php';
require_once '../app-includes/settings.php';

// Echo page header
echo cota_page_header();

// Dump out remainder of import page. 
?>
    <div class="cota-display-container">
        <h2>Upload CSV File containing Family Import</h2>
		<h3>COTA Directory - Member Listing</h3>


<?php

        $db = new COTA_Database();
        $conn = $db->get_connection();
               
        $families = $conn->query("SELECT * FROM families ORDER BY `familyname`");
		$num_families = $families->num_rows;
		$ictr = 1;
?>

<h3><?php echo $num_families . ' Families'?></h3> 

		<table class="directory-table">
			<tr><th>Family Name/Address</th><th><i>Family Members</i></th></tr>
			<tr><th>Home Phone<td><i>Name</i></td><td><i>Email</i></td><td><i>Cell</i></td><td><i>DoB</i></td><td><i>DoBaptism</i></td></th></tr>

<?php
		while ($ictr < $num_families ) {
			// Get family details
			$family = $families->fetch_assoc();

			// Get all family members
			// $individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id'] . " ORDER BY `first_name`");
			$individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id']);  // no ordering

			echo cota_format_family_listing_for_display($family, $individuals);	
			$ictr++;
		} 

		// Closing the file 
        $db->close_connection();

		echo "\n</table></body></html>"; 
		?> 

	<!-- <br><p><a href='index.php'>Return to main menu</a></p> -->
	<!-- <button class="main-menu-return" type="button" ><a href='index.php'>Return to Main Menu</a></button> -->
</body> 

</html>
