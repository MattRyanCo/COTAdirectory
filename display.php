<?php
require_once 'Database.php';


?>
<!DOCTYPE html> 
<html> 

<body> 
	<center> 
		<h1>COTA Directory</h1> 
		<h3>Member Listing</h3> 

		<?php 
		echo "<html><body><table>\n\n"; 

        $db = new Database();
        $conn = $db->getConnection();
        $output = fopen('php://output', 'r');
        
        // fputcsv($output, ["Family Name", "Member Name", "Address", "Address 2", "City", "State", "Zip", "Home Phone", "Cell Phone 1", "Email 1", "Birthday 1", "Cell Phone 2", "Email 2", "Birthday 2", "Anniversary"]);
        
        $families = $conn->query("SELECT * FROM families");
        while ($family = $families->fetch_assoc()) {

			// HTML tag for placing in row format 
			echo "<tr>"; 
			foreach ($family as $i) { 
				echo "<td>" . $i['family_name'] . "</td>";
				echo "<td>" . $i['primary_name_1'] . " & " . $i['primary_name_2'] . "</td>";
				echo "<td>" . $i['address'] . "</td>";
				echo "<td>" . $i['address_2'] . "</td>";
				echo "<td>" . $i['city'] . "</td>";
				echo "<td>" . $i['state'] . "</td>";
				echo "<td>" . $i['zip'] . "</td>";
				echo "<td>" . $i['home_phone'] . "</td>";
				echo "<td>" . $i['cell_phone_1'] . "</td>";
				echo "<td>" . $i['email_1'] . "</td>";
				echo "<td>" . $i['birthday_1'] . "</td>";
				echo "<td>" . $i['cell_phone_2'] . "</td>";
				echo "<td>" . $i['email_2'] . "</td>";
				echo "<td>" . $i['birthday_2'] . "</td>";
				echo "<td>" . $i['anniversary'] . "</td>";
			echo "</tr> \n"; 
			}
		} 

		// Closing the file 
		fclose($output); 
        $db->closeConnection();

		echo "\n</table></center></body></html>"; 
		?> 
	</center> 
</body> 

</html>
