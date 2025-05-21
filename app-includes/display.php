<?php
require_once '../app-includes/database_functions.php';

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
        // $output = fopen('php://output', 'r');
        
        // fputcsv($output, ["Family Name", "Member Name", "Address", "Address 2", "City", "State", "Zip", "Home Phone", "Cell Phone 1", "email 1", "Birthday 1", "Cell Phone 2", "email 2", "Birthday 2", "annday"]);
        
        $families = $conn->query("SELECT * FROM families ORDER BY `familyname`");
		$num_families = $families->num_rows;
		$ictr = 1;

?>
		<center> 
				<h1>COTA Directory</h1> 
				<h3>Member Listing - <?php echo $num_families . ' Families'?></h3> 

		<?php 
		echo "<html><body><table>\n\n"; 
		echo "<tr><th>Family Name/Address</th><th>Cell/ email/ Birthday</th></tr>";

		while ($ictr < $num_families ) {
			$family = $families->fetch_assoc(); 
				echo "<tr><td colspan='5'>&nbsp;</td></tr>";
				printf("<tr class='new_family'><td><strong>%s</strong></td><td>%s</td><td>%s</td></tr>", $family['familyname'], $family['name1'],$family['name2']);
				printf("<tr><td>%s</td><td>C: %s</td><td>%s</td>",$family['address'], $family['cellphone1'], $family['cellphone2']);
				if ($family['address2'] != "") {
					printf("<tr><td>%s</td></tr>",$family['address2']);
				} 
				printf("<tr><td>%s, %s %s</td><td>E: %s</td><td>%s</td></tr>",$family['city'], $family['state'],$family['zip'], $family['email1'], $family['email2']);
				if ($family['homephone'] != "") {
					printf("<tr><td>Home:  %s</td><td></td><td></td></tr>",$family['homephone']);
				}
				if ( $family['annday'] != "") {
					printf("<tr><td>Anniversary of Marriage: </td><td>%s</td></tr>",$family['annday']);

				} else {
					printf("<tr><td></td><td></td><td></td></tr>");
				}
				if ( $family['bday1'] != "" || $family['bday2'] != "") {
					printf("<tr><td>Anniversary of Birth: </td><td>%s</td><td>%s</td></tr>",$family['bday1'], $family['bday2']);
				} else {
					printf("<tr></tr>");
				}
				if ( $family['bap1'] != "" || $family['bap2'] != "") {
					printf("<tr><td>Anniversary of Baptism: </td><td>%s</td><td>%s</td></tr>",$family['bap1'], $family['bap2']);
				} else {
					printf("<tr></tr>");
				}
				// Get family members
				$individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id'] . " ORDER BY `first_name`");
				if ( ! $individuals->num_rows == 0) {
					printf("<tr><td></td><td><i>Family Members</i></td><td></td></tr>");
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

	<br><p><a href='index.php'>Return to main menu</a></p>
</body> 

</html>
