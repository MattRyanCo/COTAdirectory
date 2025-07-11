<?php
require_once '../libraries/fpdf/fpdf.php';

class PDF extends FPDF
{
    public function getPageBreakTrigger(): float
    {
        return $this->PageBreakTrigger;
    }

    function center_this_text($text, $vertical_position) {
        $pageWidth = $this->GetPageWidth();
        $textWidth = $this->GetStringWidth($text);
        // Calculate X position to center
        $x = ($pageWidth - $textWidth) / 2;
        $this->SetXY($x, $vertical_position); // 1 inch from the top
        $this->Cell($textWidth, 0.5, $text);
    }

    /**
     * just_this_text
     * 
     * Justify text at the left or right margin. 
     *
     * @param [type] $text
     * @param string $align
     * @return void
     */
    function just_this_text($text, $align='L') {
        $y = $this->GetY();
        if ($align === 'L') {
            $x = $this->lMargin;
        } elseif ($align === 'R') {
            $x = $this->w - $this->rMargin - $this->GetStringWidth($text);
        } else {
            // Default to center alignment
            $x = ($this->w - $this->GetStringWidth($text)) / 2;
        }
        $this->SetXY($x, $y);
        $this->Cell($this->GetStringWidth($text), 10, $text, 0, 1);
    }

    function Header()
    {
        global $title;
        $this->Ln(10);  // Move heading block down from top of page. 
        $this->SetFont('Arial','B',15);  // Arial bold 15

        $this->center_this_text( $title, 0.5 );

        $this->SetDrawColor(0,80,180);
        $this->SetFillColor(230,230,0);
        $this->SetTextColor(220,50,50);
        // Thickness of frame (1 mm)
        $this->SetLineWidth(.25);

        $this->Ln(10);
    }

    function Footer()
    {

        // $this->SetY(-10);  // Set position from bottom of page. 
        $this->SetFont('Arial', 'I', 8);  // Select Arial italic 8
        $this->SetTextColor(128);  // Text color in gray
        $page_no = $this->PageNo();
        $footer_text = "Page {$page_no}";

        if ( $page_no==1 ) return;
        // $this->center_this_text( $footer_text, -0.5 );

        if ( $page_no % 2 == 0 ) {  // even number page, left just the footer. 
            $this->just_this_text($footer_text, $align='L');
        } else { // odd number page, right just the footer
            $this->just_this_text($footer_text, $align='R');   
        }
    }


    function ChapterTitle($num, $label)
    {
        // Arial 12
        $this->SetFont('Arial','',12);
        // Background color
        $this->SetFillColor(200,220,255);

        $chapter_title = "Chapter $num : $label";
        $this->center_this_text( $chapter_title, 1);
    }

    function ChapterBody($file)
    {
        // Read text file
        $content = file_get_contents($file);
        // Ouput file contents
        if ($content !== false) {
            // Set X and Y position
            $this->SetXY(1, 2); // 1 inch from left, 2 inches from top

            // MultiCell handles text wrapping automatically
            $this->MultiCell(6.5, 0.2, $content); // 6.5" width, 0.2" height per line
        } else {
            $this->SetXY(1, 2);
            $this->SetTextColor(255, 0, 0);
            $this->MultiCell(6.5, 0.2, 'Could not load {$file}.');
        }
    }

    function PrintChapter($num, $title, $file)
    {
        $this->AddPage();
        $this->SetFont('Times','',12);
        $this->ChapterTitle($num,$title);
        $this->ChapterBody($file);
    }

    function cota_back_cover($label)
    {

        $this->AddPage();
        $this->SetFont('Arial', '', 8);
        $text = " Printed " . date('F j, Y');
        $this->center_this_text($text, 5 );
    }

    function cota_front_cover($title, $author, $logoFile )
    {

        // Background color
        $this->SetFillColor(200,220,255);
        // $this->AddPage();
        $this->Ln(4);
        $this->SetY(-1);
        $this->SetFont('Arial', 'I', 20);

        // Center the title
        $this->center_this_text( $title, 1 );
        $this->SetFontSize(12);
        // Output the author
        $this->center_this_text( $author, 1.5 );
        $this->Ln(10);

        // Center the logo
        $pageWidth = $this->GetPageWidth();
        // $logowidth = 200;
        $imageWidth = 7; // image size
        $x = ($pageWidth - $imageWidth ) / 2;
        $this->SetXY($x, 3 );  // Place it in the center 3" from top. 
        $this->Image( $logoFile );

        $this->SetFontSize(8);

        // Center the date.
        $datetext = "Created: " . date('F j, Y');
        $this->center_this_text( $datetext, -1 );

    }
    
    /**
     * PrintFamilyArray
     *
     * @param [type] $family_array
     * @return void
     */
    function PrintFamilyArray($family_array) {
        $this->SetFont('Arial', '', 10);

        $line_height = 0.25;
        $left_margin = .25;

        // Figure out how many lines we need to print
        $needed_height = count($family_array) * $line_height;


        // Check if enough space remains, else add a new page
        $bottom_margin = 10.5; 
        $page_height = $this->h; 
        // $current_y = $this->GetY();
        // $space_needed = ($current_y + $needed_height + $bottom_margin) / 2;


        // if ($space_needed > $page_height) {
            // $this->AddPage();
        // }

// print_r('count l '. count($family_array)); echo '<br>';
// print_r('need h ' .$needed_height); echo '<br>';
// print_r('curt y ' . $current_y); echo '<br>';
// print_r('bot marg '.$bottom_margin);echo '<br>';
// print_r('space needed '.$space_needed);echo '<br>';
// print_r('page h '.$page_height); echo '==============<br>';

        $large_field_width = round($this->GetStringWidth('Family Name/Address'),1);
        $wline1 = [
            round($this->GetStringWidth('Family Name/Address'),1),  // [0]
            round($this->GetStringWidth('Family Members'),1),       // [1]                                             // [1]
        ];  // width of heading lables line 1. 
        $wline2 = [
            round($this->GetStringWidth('Home: xxx-xxx-xxxx_'),1),           // [0]
            round($this->GetStringWidth('MyLongFirstName '),1), // [1]
            round($this->GetStringWidth('LongEmailExample@example.com '),1),           // [2
            round($this->GetStringWidth('###-###-####__'),1),                // [3
            round($this->GetStringWidth('mm/dd_'),1),                // [4
            round($this->GetStringWidth('mm/dd_'),1)                 // [5
        ]; // width of heading lables line 2.
        $large_field_width = round($this->GetStringWidth('Family Name/Address'),1) + 0.5;
        $wline3 = [
            $left_margin,                                         // [0] phone or blank
            $left_margin+$large_field_width,                                            // 1 name
            $left_margin+$large_field_width+$wline2[1],                                 // 2 em
            $left_margin+$large_field_width+$wline2[1]+$wline2[2],                      // 3 cell
            $left_margin+$large_field_width+$wline2[1]+$wline2[2]+$wline2[3],           // 4 DOB
            $left_margin+$large_field_width+$wline2[1]+$wline2[2]+$wline2[3]+$wline2[4] // 5 Bap
        ];  // X position for start of label / fields to write


        // Table Headers
        $this->SetXY($left_margin,1);
        $this->Cell($wline1[0], $line_height, 'Family Name/Address');

        $this->SetX($large_field_width+0.25);
        $this->Cell($wline3[1], $line_height, 'Family Members');

        // Print out name column headings in italics. 
        $this->SetFont('Arial', 'I', 10);
        $this->SetXY($large_field_width+0.25, 1.25);
        $this->Cell($wline3[1], $line_height, 'Name');
        $this->SetX($wline3[2]);
        $this->Cell($wline3[2], $line_height, 'Email');
        $this->SetX($wline3[3]);
        $this->Cell($wline3[3], $line_height, 'Cell');
        $this->SetX($wline3[4]);
        $this->Cell($wline3[4], $line_height, 'DoB' );
        $this->SetX($wline3[5]);
        $this->Cell($wline3[5], $line_height, 'DoBap' );
        
        $current_y = $this->GetY();
        $this->line($left_margin, $current_y+$line_height, ($this->w)-$left_margin, $current_y+$line_height);



        // Process family listing for 1 family
        $next_row = $current_y + $line_height;
        $this->SetFont('Arial', '', 10);



        for ( $i=1; $i<=10; $i++) {


            $this->SetXY($left_margin, $next_row);
            $this->Cell($wline3[0], $line_height, $family_array[$i][1]);  // Left side of listing. 

            $this->SetX($wline3[1]);
            $this->Cell($wline3[1], $line_height, $family_array[$i][4] . ' ' . $family_array[$i][5]);  // Name

            $this->SetX($wline3[2]);
            $this->Cell($wline3[2], $line_height, $family_array[$i][6]); // em

            $this->SetX($wline3[3]);
            $this->Cell($wline3[3], $line_height, $family_array[$i][7]); // cell

            $this->SetX($wline3[4]);
            $this->Cell($wline3[4], $line_height, $family_array[$i][8]); // DoB

            $this->SetX($wline3[5]);
            $this->Cell($wline3[5], $line_height, $family_array[$i][9]); // DoBap

            $next_row = $next_row + $line_height;
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