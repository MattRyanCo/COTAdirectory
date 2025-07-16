<?php
require_once '../app-libraries/fpdf/fpdf.php';

// global $cotadb, $conn, $cota_constants;


// echo nl2br($cota_constants->COTA_APP_ASSETS . ' = COTA_APP_ASSETS' . PHP_EOL);
// echo nl2br($cota_constants->COTA_APP_INCLUDES . ' = COTA_APP_INCLUDES' . PHP_EOL);
// echo nl2br($cota_constants->COTA_APP_LIBRARIES . ' = COTA_APP_LIBRARIES' . PHP_EOL);


// require_once $cota_constants->COTA_APP_LIBRARIES . 'fpdf/fpdf.php';
// require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

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
     * Move along the x axis.
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
            $x = $this->w - $this->rMargin - ($this->GetStringWidth($text)+.25);
            // var_dump($this->w, $this->rMargin,$this->GetStringWidth($text));
        } else {
            // Default to center alignment
            center_this_text($text, $y);
        }
        // var_dump($x, $y, $align, $text);
        // $this->SetXY($x, $y);
        $this->SetX($x);
        // $this->Cell($this->GetStringWidth($text), .25, $text, 0, 1);
        // $this->MultiCell($this->GetStringWidth($text), 0.25, $text);
        $this->Write(0.25, $text );
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

        // $this->Ln(10);
        $this->SetY(3);   // Position cursor after header output
    }

    function Footer()
    {
        $this->SetFont('Arial', 'I', 8);  // Select Arial italic 8
        $this->SetTextColor(128);  // Text color in gray
        $page_no = $this->PageNo();
        $footer_text = "Page " . $page_no;
        $this->SetY(-1);  // Set position from bottom of page. 

        if ( $page_no==1 ) return; // no footer on 1st page. 

        if ( $page_no % 2 == 0 ) {  // even number page, left justify the footer. 
            $this->just_this_text($footer_text, $align='L');
        } else { // odd number page, right justify the footer
            $this->just_this_text($footer_text, $align='R');   
        }
    }


    function ChapterTitle($num, $label)
    {
        // Arial 12
        $this->SetFont('Arial','',12);
        // Background color
        // $this->SetFillColor(200,220,255);

        $chapter_title = "Chapter $num : $label";
        $this->center_this_text( $chapter_title, 1);
    }

    function ChapterBody($file)
    {
        $line_height = 0.2;
        // Read text file
        $content = file_get_contents($file);
        // Ouput file contents
        $this->SetXY(1, 2); // 1 inch from left, 2 inches from top
        if ($content !== false) {
            // MultiCell handles text wrapping automatically
            $this->MultiCell(6.5, 0.2, $content); // 6.5" width, 0.2" height per line
        } else {
            $this->SetTextColor(255, 0, 0);
            $this->MultiCell(6.5, 0.2, 'Could not load {$file}.');
        }
    }

    function PrintChapter($num, $title, $file)
    {
        $this->SetFont('Times','',12);
        $this->AddPage();
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
     * print_family_array
     * 
     * Functions assumes that we have enough space to print family array.
     * Need for AddPage calculated before calling us. 
     *
     * @param [type] $family_array
     * @return void
     */
    function print_family_array($family_array) {
        $this->SetFont('Arial', '', 10);
        $line_height = 0.25;
        $left_margin = 0.25;

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
        $next_row = $next_row + ( 4 * $line_height ); 
        $this->SetXY($left_margin, $next_row);;
    }

    function enough_room_for_family( $lines_to_output, $line_height=0.25, $page_break_now=8 ) {
        if ( 0 == $lines_to_output ) {
            return true;  // weird input. Assume okay. 
        } else {
            // Calculate the room required given number of lines to output
            // Compare to where we are now
            $start_y = $this->GetY(); // Where we are now
            $needed_height = $lines_to_output * $line_height;
            // var_dump($lines_to_output, $start_y, $needed_height, $page_break_now);
            if ( $start_y + $needed_height >= $page_break_now ) {
                return false; // not enough room
            } else {
                return true;  // enough room
            }
        }
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