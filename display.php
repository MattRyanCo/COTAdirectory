<?php
require_once 'Database.php';

?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Family Directory Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php

        $db = new Database();
        $conn = $db->getConnection();
        // $output = fopen('php://output', 'r');
        
        // fputcsv($output, ["Family Name", "Member Name", "Address", "Address 2", "City", "State", "Zip", "Home Phone", "Cell Phone 1", "Email 1", "Birthday 1", "Cell Phone 2", "Email 2", "Birthday 2", "Anniversary"]);
        
        $families = $conn->query("SELECT * FROM families ORDER BY `family_name`");
		$num_families = $families->num_rows;
		$ictr = 1;

?>
		<center> 
				<h1>COTA Directory</h1> 
				<h3>Member Listing - <?php echo $num_families . ' Families'?></h3> 

		<?php 
		echo "<html><body><table>\n\n"; 
		echo "<tr><th>Family Name/Address</th><th>Cell/ Email/ Birthday</th></tr>";

		while ($ictr < $num_families ) {
			$family = $families->fetch_assoc(); 
				echo "<tr><td colspan='5'>&nbsp;</td></tr>";
				printf("<tr class='new_family'><td><strong>%s</strong></td><td>%s</td><td>%s</td></tr>", $family['family_name'], $family['primary_name_1'],$family['primary_name_2']);
				printf("<tr><td>%s</td><td>C: %s</td><td>%s</td>",$family['address'], $family['primary_cell_1'], $family['primary_cell_2']);
				if ($family['address_2'] != "") {
					printf("<tr><td>%s</td></tr>",$family['address_2']);
				} 
				printf("<tr><td>%s, %s %s</td><td>E: %s</td><td>%s</td></tr>",$family['city'], $family['state'],$family['zip'], $family['primary_email_1'], $family['primary_email_2']);
				if ($family['home_phone'] != "") {
					printf("<tr><td>H: %s</td><td>%s</td><td>%s</td></tr>",$family['home_phone'], $family['primary_bday_1'], $family['primary_bday_2']);
				} else {
					if ($family['primary_bday_1'] != "") {
						printf("<tr><td></td><td>%s</td><td>%s</td></tr>", $family['primary_bday_1'], $family['primary_bday_2']);
					} else {
						printf("<tr><td></td><td></td><td></td></tr>");
					}
				}
				if ( $family['anniversary'] != "") {
					printf("<tr><td>Anniversary: </td><td>%s</td></tr>",$family['anniversary']);

				} else {
					printf("<tr><td></td><td></td><td></td></tr>");
				}
				// Get family members
				$individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id'] . " ORDER BY `first_name`");
				if ( ! $individuals->num_rows == 0) {
					printf("<tr><td></td><td><strong>Family Members</strong></td><td></td></tr>");
					foreach ($individuals as $individual) {
						printf("<tr><td></td><td>%s %s</td><td>%s</td><td>%s</td><td>%s</td></tr>",$individual['first_name'], $individual['last_name'],$individual['birthday'], $individual['cell_phone'], $individual['email']);
					}
				}
			$ictr++;
		} 

		// Closing the file 
		// fclose($output); 
        $db->closeConnection();

		echo "\n</table></body></html>"; 
		?> 
	</center> 
</body> 

</html>
