<?php
require_once '../libraries/fpdf/fpdf.php';

class PDF extends FPDF
{
    function Header()
    {
        global $title;

        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Calculate width of title and position
        $w = $this->GetStringWidth($title)+6;
        $this->SetX((210-$w)/2);
        // Colors of frame, background and text
        $this->SetDrawColor(0,80,180);
        $this->SetFillColor(230,230,0);
        $this->SetTextColor(220,50,50);
        // Thickness of frame (1 mm)
        $this->SetLineWidth(1);
        // Title
        $this->Cell($w,9,$title,1,1,'C',true);
        // Line break
        $this->Ln(10);
    }

    function Footer()
    {
        // Go to 1.5 cm from bottom
        $this->SetY(-1);
        // Select Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Text color in gray
        $this->SetTextColor(128);
        // Print centered page number
        $this->Cell(0, 0.3, "Page {$this->PageNo()} - " . date('F j, Y'), 0, 0, 'C');
    }

    function ChapterTitle($num, $label)
    {
        // Arial 12
        $this->SetFont('Arial','',12);
        // Background color
        $this->SetFillColor(200,220,255);
        // Title
        $this->Cell(0,6,"Chapter $num : $label",0,1,'L',true);
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
        $this->MultiCell(0,5,$txt);
        // Line break
        $this->Ln();
        // Mention in italics
        // $this->SetFont('','I');
        // $this->Cell(0,5,'(end of excerpt)');
    }

    function PrintChapter($num, $title, $file)
    {
        $this->AddPage();
        $this->ChapterTitle($num,$title);
        $this->ChapterBody($file);
    }

    function cota_back_cover($label)
    {
        // Background color
        $this->SetFillColor(200,220,255);
        $this->AddPage();
        $this->Ln(4);
        $this->SetY(-1);
        $this->SetFont('Arial', 'I', 8);

        $this->Cell(0,6,"Back Cover: $label",0,1,'C',true);
        // Line break
        $this->Ln(5);
        $this->SetFont('Arial','',20);

        $this->Cell(0, 1, "Church of the Ascension, Parkesburg, PA", 0, 1, 'C');
        $this->Ln(10);
        $this->Cell(0, 1, " Printed " . date('F j, Y'), 0, 1, 'C');

    }
    function cota_front_cover($title, $author, $logoFile, $logoXPos = 10, $logoYPos = 10, $logoWidth = 300)
    {

        // Background color
        $this->SetFillColor(200,220,255);
        // $this->AddPage();
        $this->Ln(4);
        $this->SetY(-1);
        $this->SetFont('Arial', 'I', 20);
        $this->Cell(0, 1, "$title", 0, 1, 'C');
        $this->Ln(10);
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(0, 1, "$author", 0, 1, 'C');
        $this->Ln(10);
        $this->Image( $logoFile, $logoXPos, $logoYPos, $logoWidth );
        $this->Ln(150);
        $this->Cell(0, 1, "GENERATED: " . date('F j, Y'), 0, 1, 'C');

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
    function PrintFamilyString($familyString)
    {
        $this->SetFont('Arial', '', 12);
        $lineHeight = 7; // in mm (approx. 12pt font height + spacing)
        // Break apart this string into individual lines
        $lines = explode("\n", trim($familyString));
        // Figure out how many lines we need to print
        $neededHeight = count($lines) * $lineHeight;

        // Check if enough space remains, else add a new page
        $bottomMargin = 10; // in mm
        $pageHeight = $this->h; // in mm
        $currentY = $this->GetY();
        if (($currentY + $neededHeight + $bottomMargin) > $pageHeight) {
            $this->AddPage();
        }

        // Dump out each line of family listing
        foreach ($lines as $line) {
            $this->Cell(0, $lineHeight, $line, 0, 1);
        }
        $this->Ln(2);
    }
}