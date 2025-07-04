<?php
/**
 * COTA Membership Directory PDF Generation Script
 * This script prints the COTA directory as a linear set of standard 
 * pages (1 through x, with 1 being the front cover and x being the last printed page). 
 * It does not account for 2-sided booklet printing, but rather prints 
 * assuming that the PDF will then be processed by another app that will convert it to 
 * 2-sided, 4 to a page format. 
 */
require_once '../app-includes/database-functions.php';
require_once '../app-includes/format-family-listing.php';
require_once '../app-includes/format-family-listing-for-fpdf.php';
require_once '../app-includes/print.php';
require_once '../app-includes/class-print-booklet.php';
require_once '../app-includes/settings.php';

// Create a new PDF instance
// $pdf = new PDF(); // Landscape, Inches, Half-page Letter Size
$pdf = new PDF('P', 'in', 'Letter'); // Portrait, Inches, Letter Size
$pdf->AddPage();

$title = 'Church of the Ascension Directory 2025';
$author = 'Vestry & Wardens of Church of the Ascension, Parkesburg';
$pdf->SetTitle($title);
$pdf->SetAuthor($author);

$pdf->SetMargins(0.5, 0.5, 0.5);
$pdf->SetAutoPageBreak(true, 0.5);
$pdf->SetFont('Arial', '', 12);

$logoFile = '../app-assets/images/cota-logo.png';
$pdf->cota_front_cover( $title, $author, $logoFile, 5, 50, 200 ); // Add front cover with logo

// Load and insert static pages.
for ($i = 1; $i <= 3; $i++) {
    $pdf->PrintChapter($i,'intro'.$i.'.txt','../uploads/intro'.$i.'.txt');
}

// Retrieve and Format Membership Entries
// Fetch data from your MySQL database and format it into an address label style layout

$db = new COTA_Database();
$conn = $db->get_connection();
               
$families = $conn->query("SELECT * FROM families ORDER BY `familyname`");
$num_families = $families->num_rows;
$ictr = 1;


$pdf->SetFont('Arial', 'B', 14);
$pdf->center_this_text('Family & Members Listing', 1.5);
$pdf->SetFont('Arial', '', 12);
$pdf->center_this_text('Member Listing - '.$num_families . ' families', 2);
$pdf->AddPage(); // Start the alpha listing on a new page

$pageEntries = 0;
while ($family = $families->fetch_assoc()) {
    // Get family members
    $individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $family['id']);

    $family_array = cota_format_family_listing_for_print($family, $individuals);


        // 1. Clone and simulate
        $clone = clone $pdf;
        $clone->printFamilyArray($family_array);

        // 2. Compute height used
        $startY         = $pdf->GetY();
        $requiredHeight = $clone->GetY() - $startY;


        // 3. Page‐break if needed
        if ($startY + $requiredHeight > $pdf->getPageBreakTrigger) {
            $pdf->AddPage();
        }

        // 4. Finally draw on the real PDF
        $pdf->PrintFamilyArray($family_array);


    // $pdf->AddPage(); // Start the alpha listing on a new page
    // $pdf->PrintFamilyArray($family_array);

    $pageEntries++;
}

// protected function PrintFamilyArray(array $families)
// {
//     foreach ($families as $family) {
//         // 1. Clone and simulate
//         $clone = clone $this;
//         $clone->printFamily($family);
        
//         // 2. Compute height used
//         $startY         = $this->GetY();
//         $requiredHeight = $clone->GetY() - $startY;

//         // 3. Page‐break if needed
//         if ($startY + $requiredHeight > $this->PageBreakTrigger) {
//             $this->AddPage();
//         }

//         // 4. Finally draw on the real PDF
//         $this->printFamily($family);
//     }
// }








// Add the rear cover
$pdf->cota_back_cover(1, 'Back Cover');

$output_basename = '/downloads/directory_booklet_' . date('Y-m-d') . '.pdf';
// Ensure the downloads directory exists
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/downloads')) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . '/downloads', 0755, true);
}
$output_filename = $_SERVER['DOCUMENT_ROOT'] . $output_basename;

// Output the PDF
// $pdf->Output('F', '../uploads/membership_directory.pdf'); // Save to server
$pdf->Output('F', $output_filename); // Save to server
// $pdf->Output('I'); // Still display in browser if you want

$db->close_connection();
$basen = basename($output_filename);
// Echo header
echo cota_page_header();

// Dump out remainder of import page. 
echo "<div id='cota-print' class='container'>";
echo "<h2>PDF file generated successfully!</h2>";
echo "<h4>File: " . basename($output_filename) . "</h2>";
echo "<button class='button' type='button' ><a href='.." . $output_basename . "' download >Download File</a></button>";
echo '</div></body></html>';

// End of script
// Note: Ensure that the database connection is closed and any resources are released properly.
?>  