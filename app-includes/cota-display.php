<?php
require_once '../app-includes/cota-database-functions.php';
require_once '../app-includes/cota-format-family-listing.php';

// echo nl2br(__FILE__ . ' loaded' . PHP_EOL);


?> 
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Family Directory Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
<link rel="stylesheet" href="../app-assets/css/styles.css">
</head>

<?php

        $db = new COTA_Database();
        $conn = $db->get_connection();
               
        $families = $conn->query("SELECT * FROM families ORDER BY `familyname`");
		$num_families = $families->num_rows;
		$ictr = 1;
?>
<body>
<h1>COTA Directory</h1> 
<h3>Member Listing - <?php echo $num_families . ' Families'?></h3> 

<html>
	<body>
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

			echo cota_cota_format_family_listing_for_display($family, $individuals);	
			$ictr++;
		} 

		// Closing the file 
        $db->close_connection();

		echo "\n</table></body></html>"; 
		?> 

	<br><p><a href='index.php'>Return to main menu</a></p>
</body> 

</html>
