<?php
require_once '../app-includes/cota-database-functions.php';
require_once '../app-includes/cota-print.php';

$printBooklet = new MembershipDirectoryPrinter();


$introFiles = ['../uploads/intro1.txt', '../uploads/intro2.txt', '../uploads/intro3.txt'];
$outputFile = 'membership_directory.rtf';

$db = new COTA_Database();
$rtfContent = $printBooklet->generateRTFHeader();

// Add intro pages
foreach ($introFiles as $file) {
	if (file_exists($file)) {
		$rtfContent .= $printBooklet->formatText(file_get_contents($file)) . "\\pard\\page\\par";
	}
}
$all_families = $db->read_database();

// Add family listings
// foreach ($all_families as $family) {
	$rtfContent .= $printBooklet->formatFamilyListings($all_families) . "\\pard\\page\\par";
// }

$rtfContent .= "}";

file_put_contents($outputFile, $rtfContent);

// Closing the file 
// fclose($output); 
$db->close_connection();

// printf("<h2>RTF file generated successfully!</h2>");
// printf("<p><a href='%s'>Download file</a></p>", $outputFile);	

// Printf("<p><a href='../../index.php'>Return to main menu.</a></p>");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import CSV Data</title>
    <link rel="stylesheet" href="../app-assets/css/styles.css">
</head>
<body>
    <h2>RTF file generated successfully!</h2>
	<p><button class="button" type="button" ><a href='<?php echo $outputFile; ?>'>Download file</a></button></p>
    <button class="main-menu-return" type="button" ><a href='index.php'>Return to Main Menu</a></button>

</body>
</html>