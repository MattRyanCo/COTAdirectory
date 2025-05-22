<?php
require_once '../app-includes/database_functions.php';
require_once '../app-includes/format_family_listing.php';

// echo nl2br(__FILE__ . ' loaded' . PHP_EOL);


?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Family Directory Management</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<?php

        $db = new Database();
        $conn = $db->getConnection();
               
        $families = $conn->query("SELECT * FROM families ORDER BY `familyname`");
		$num_families = $families->num_rows;
		$ictr = 1;

?>
		<h1>COTA Directory</h1> 
		<h3>Member Listing - <?php echo $num_families . ' Families'?></h3> 

		<html>
			<body>
				<table class="directory-table">
					<tr><th>Family Name/Address</th><th><i>Family Members</i></th></tr>
					<tr><th><i><td>Name</td><td>Email</td><td>Cell</td><td>DoB</td><td>DoBaptism</td></i></th></tr>
		<?php
		while ($ictr < $num_families ) {
			// Get family details
			$family = $families->fetch_assoc(); 
			// Get all family members
			$individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id'] . " ORDER BY `first_name`");

			$result = format_family_listing_for_display($family, $individuals);	
			var_dump($result);
			$ictr++;
		} 

		// Closing the file 
		// fclose($output); 
        $db->closeConnection();

		echo "\n</table></body></html>"; 
		?> 

	<br><p><a href='index.php'>Return to main menu</a></p>
</body> 

</html>
