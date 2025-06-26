<?php
require_once '../libraries/fpdf/fpdf.php';

class PDF extends FPDF
{
    function Header()
    {
        global $title;
        $this->Ln(10);  // Move heading block down from top of page. 
        $this->SetFont('Arial','B',15);  // Arial bold 15
        // Calculate width of title and position
        $w = $this->GetStringWidth($title)+6;
        // print_r($w); // Debugging output
        $this->SetX((11-$w)/2);
        // Colors of frame, background and text
        $this->SetDrawColor(0,80,180);
        $this->SetFillColor(230,230,0);
        $this->SetTextColor(220,50,50);
        // Thickness of frame (1 mm)
        $this->SetLineWidth(.25);
        // Title
        $this->Cell($w,9,$title,1,1,'C',true);
        // Line break
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-10);  // Set position from bottom of page. 
        $this->SetFont('Arial', 'I', 8);  // Select Arial italic 8
        $this->SetTextColor(128);  // Text color in gray
        $this->Cell(0, 0.3, "Page {$this->PageNo()}", 0, 0, 'C');
        // Page Number and Date Centered
        // $this->Cell(0, 0.3, "Page {$this->PageNo()} - " . date('F j, Y'), 0, 0, 'C');

    }


    function ChapterTitle($num, $label)
    {
        // Arial 12
        $this->SetFont('Arial','',12);
        // Background color
        $this->SetFillColor(200,220,255);
        // Title
        $this->Cell(0,6,"Chapter $num : $label",0,1,'C',true);
        // Line break
        $this->Ln(4);
    }

    function ChapterBody($file)
    {
        // Read text file
        $txt = file_get_contents($file);
        // Times 12
        $this->SetFont('Times','',12);
        // Output justified text
        $this->MultiCell(0,5,$txt,0,'C',false);
        // Line break
        $this->Ln();

    }

    function PrintChapter($num, $title, $file)
    {
        global $title;
        $this->AddPage();
        $this->ChapterTitle($num,$title);
        $this->ChapterBody($file);
    }

    function cota_back_cover($label)
    {
        global $title;
        // Background color
        $this->SetFillColor(200,220,255);
        $this->AddPage();
        $this->Ln(50);
        $this->SetY(-1);
        $this->SetFont('Arial','',20);
        $this->Cell(0, 1, "Church of the Ascension, Parkesburg, PA", 0, 1, 'C');
        $this->Ln(10);
        $this->SetFont('Arial','',8);
        $this->Cell(0, 1, " Printed " . date('F j, Y'), 0, 1, 'C');

    }
    function cota_front_cover($title, $author, $logoFile )
    {

        // Background color
        $this->SetFillColor(200,220,255);
        // $this->AddPage();
        $this->Ln(4);
        $this->SetY(-1);
        $this->SetFont('Arial', 'I', 20);
        $this->Cell(0, 1, "$title", 0, 1, 'C');
        $this->Ln(10);
        $this->SetFontSize(12);
        $this->Cell(0, 1, "$author", 0, 1, 'C');
        $this->Ln(10);
        $this->Image( $logoFile, 5, 50, 200 );
        // $this->Ln(150);
        $this->SetY(-15);
        $this->SetFontSize(8);
        $this->Cell(0, 1, "CREATED: " . date('F j, Y'), 0, 1, 'C');

    }
    // New function: Retrieve all data for a family and build a string object
    function BuildFamilyString($family, $individuals)
    // 06/05/2025 - 12:15 Currently not used, but could be useful for debugging
    // This function builds a string representation of a family, including its members and contact information.
    {
        $str = "{$family['familyname']}\n";
        if (!empty($family['address'])) {
            $str .= "{$family['address']}\n";
        }
        if (!empty($family['city']) || !empty($family['state']) || !empty($family['zip'])) {
            $str .= trim("{$family['city']} {$family['state']} {$family['zip']}") . "\n";
        }
        if (!empty($family['phone'])) {
            $str .= "Phone: {$family['phone']}\n";
        }
        if (!empty($family['email'])) {
            $str .= "Email: {$family['email']}\n";
        }
        $str .= "Members:\n";
        while ($member = $individuals->fetch_assoc()) {
            $str .= " - {$member['first_name']} {$member['last_name']}";
            if (!empty($member['birthdate'])) {
                $str .= " (b. {$member['birthdate']})";
            }
            $str .= "\n";
        }
        return $str;
    }

    // New function: Print the family string, using the PDF library.
    // Avoid page breaks within a family entry
    // Format for 8.5 x 11 inch paper, landscape orientation, booklet style. 
    //   Currently this does not handle any horizontal formatting.

    function PrintFamilyString($familyString)
    {
        global $title;
        $this->SetFont('Arial', '', 12);
  
        $lineHeight = 7; // in mm (approx. 12pt font height + spacing)
        // Break apart this string into individual lines
        $lines = explode("\n", trim($familyString));
        // Figure out how many lines we need to print
        $neededHeight = count($lines) * $lineHeight;

        // Check if enough space remains, else add a new page
        $bottomMargin = 10; 
        $pageHeight = $this->h; 
        $currentY = $this->GetY();
        if (($currentY + $neededHeight + $bottomMargin) > $pageHeight) {
            $this->AddPage();
        }

        // Dump out each line of family listing
        // Set horizontal position of 1st line of family listing.
        $this->SetX(5);
        foreach ($lines as $line) {
            $this->Cell(0, $lineHeight, $line, 0, 1);
            // Indent following lines of family listing. 
            $this->SetX(10);
        }
        $this->Ln(2);
    }

   function PrintFamilyArray($familyArray)
    {
        global $title;
        // echo nl2br(__METHOD__ . ' called' . PHP_EOL);

        // print_r($familyArray);echo '<br>'; // Debugging output
        $this->SetFont('Arial', '', 8);
  
        $lineHeight = 7; // in mm (approx. 12pt font height + spacing)
        // Break apart this string into individual lines
        // $lines = explode("\n", trim($familyString));
        // Break apart $familyArray by lines

        $lines = $familyArray;
        // Figure out how many lines we need to print
        $neededHeight = count($lines) * $lineHeight;

        // Check if enough space remains, else add a new page
        $bottomMargin = 10; 
        $pageHeight = $this->h; 
        $currentY = $this->GetY();
        if (($currentY + $neededHeight + $bottomMargin) > $pageHeight) {
            $this->AddPage();
        }

        $this->SetMargins(5, 5);

        // Set up column widths
        $w = [25, 5, 5, 25, 25, 15, 15, 10, 10]; // Widths for each column

        // Member Table Headers
        $this->SetFont('Arial', 'B', 8);
        $this->Cell( $w[0], 6, 'Family Name/Address', 1);
        $this->Cell( $w[3], 6, 'Family Members', 1);
        $this->Ln();
        // 2nd line of header
        $this->SetFont('Arial', 'I', 8);
        $this->Cell( $w[0], 5, 'Home Phone', 1);
        $this->Cell( $w[1], 5, ' ', 0);
        $this->Cell( $w[2], 5, ' ', 0);
        $this->Cell($w[3], 5, 'Name', 1);
        $this->Cell($w[5], 5, 'Email', 1);
        $this->Cell($w[6], 5, 'Cell', 1, 1,'C');
        $this->Cell($w[7], 5, 'DoB', 1, 1,'C');
        $this->Cell($w[8], 5, 'DoBap', 1, 1,'C');
        $this->Ln();

        // Process family listing for 1 family
        for ( $i=1; $i <= 10; $i++) {
            $this->Cell($w[0], $lineHeight, $familyArray[$i][1], 0, 0, 'L');
            $this->Cell($w[1], $lineHeight, $familyArray[$i][2], 0, 0, 'L');
            $this->Cell($w[2], $lineHeight, $familyArray[$i][3], 0, 0, 'L');
            $this->Cell($w[3], $lineHeight, $familyArray[$i][4] . ' ' . $familyArray[$i][5], 0, 0, 'L');  // Name
            $this->Cell($w[5], $lineHeight, $familyArray[$i][6], 0, 0, 'L');     // em 
            // $this->Cell($w[6], $lineHeight, $familyArray[$i][7], 1, 0, 'L');  // cell
            $this->Cell($w[7], $lineHeight, $familyArray[$i][8], 1, 0, 'L');     // Dob
            // $this->Cell($w[8], $lineHeight, $familyArray[$i][9], 1, 0, 'L');  // DoBaptism
            // Next line
            $this->Ln();
        }
        // Add some spacing after each family
        $this->Ln(2);
    }



    function dummy_up_pages( $pdfobject, $no_of_pages) {
        foreach ($no_of_pages as $pair) {
            // var_dump($pair); // Debugging: Show the page pairs being processed
            $pdfobject->AddPage();
            foreach ($pair as $pageNum) {
                if ($pageNum > $no_of_pages) {
                    $pdfobject->Cell(0, 5, "(Blank)", 0, 1, 'C'); // Handle extra blank pages
                } else {
                    $pdfobject->Cell(0, 5, "Page " . $pageNum, 0, 1, 'C');
                }
            }
        }
    }
}