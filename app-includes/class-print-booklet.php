<?php
require_once '../app-libraries/fpdf/fpdf.php';


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
        $this->Cell($textWidth, 0.25, $text);
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
        } else {
            // Default to center alignment
            center_this_text($text, $y);
        }
        $this->SetX($x);
        $this->Write(0.25, $text );
    }

    function Header()
    {
        global $title, $header_height;
        $this->SetFont('Arial','B',15);  // Arial bold 15
        $this->SetTextColor(128);;
        $this->center_this_text( $title, 0 );
        // $this->h  is height of document 11"
        // $this->w  is width of document 8.5"

        // var_dump($this->GetY());
        $this->Ln(1);  // Moves 1 inch down. 
        // $this->SetY(3);   // Position cursor 3" from top of page. 
        $header_height = $this->GetY();
        // var_dump($header_height);
    }

    function Footer()
    {
        $this->SetFont('Arial', 'I', 8);  // Select Arial italic 8
        $this->SetTextColor(128);  // Text color in gray
        $page_no = $this->PageNo();
        $footer_text = "Page " . $page_no;
        $this->SetY(-0.25);  // Set position 1/4" from bottom of page. 

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

    function back_cover($label)
    {

        $this->AddPage();
        $this->SetFont('Arial', '', 8);
        $text = " Printed " . date('F j, Y');
        $this->center_this_text($text, 5 );
    }

    function front_cover($title, $author, $logoFile )
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

        // $this->SetFontSize(8);
        // Center the date.
        // $datetext = "Created: " . date('F j, Y');
        // $this->center_this_text( $datetext, -1 );

    }

/**
 * print_family_array_headings
 *
 * @param [bool] $first_time
 * @return [array] array(
            0 => [sarray] $field_positions
            1 => [array] $field_widths
        )
 */
    function print_family_array_headings( $first_time ) {
        global $header_height;

        $line_height = .25;
        $left_margin = $this->lMargin;
        $start_heading = $header_height + $line_height;

        // Do this setup only first time through
        if ( $first_time ) {
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
            $field_widths = $wline2;
            $large_field_width = round($this->GetStringWidth('Family Name/Address'),1) + 0.5;
            $field_positions = [
                $left_margin,                                         // [0] phone or blank
                $left_margin+$large_field_width,                                            // 1 name
                $left_margin+$large_field_width+$wline2[1],                                 // 2 em
                $left_margin+$large_field_width+$wline2[1]+$wline2[2],                      // 3 cell
                $left_margin+$large_field_width+$wline2[1]+$wline2[2]+$wline2[3],           // 4 DOB
                $left_margin+$large_field_width+$wline2[1]+$wline2[2]+$wline2[3]+$wline2[4] // 5 Bap
            ];  // X position for start of label / fields to write
        }

        // Output headings here. 
        // Table Headers - 1st line
        $this->SetXY($field_positions[0], $start_heading );
        $this->Cell($wline1[0], $line_height, 'Family Name/Address');

        $this->SetX($field_positions[1]);
        $this->Cell($wline1[1], $line_height, 'Family Members');

        // Table Headers - 2nd line in italics. 
        $this->SetFont('Arial', 'I', 10);

        // Print out column headings.
            // Set x position & print content 'cell'
        $this->SetXY($field_positions[1], $start_heading + 0.25 );
        $this->Cell( $field_widths[1], $line_height, 'Name');

        $this->SetX($field_positions[2]);
        $this->Cell($field_widths[2], $line_height, 'Email');   //wline2[2]
        
        $this->SetX($field_positions[3]);
        $this->Cell($field_widths[3], $line_height, 'Cell');
        
        $this->SetX($field_positions[4]);
        $this->Cell($field_widths[4], $line_height, 'DoB' );
        
        $this->SetX($field_positions[5]);
        $this->Cell($field_widths[5], $line_height, 'DoBap' );
        
        // $current_y = $this->GetY();
        return array(
            0 => $field_positions, 
            1 => $field_widths
        );
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
    function print_family_array($family_array, $field_info ) {
        $this->SetFont('Arial', '', 10);  // Set default font. 
        $line_height = 0.25;
        $left_margin = $this->lMargin;
        $field_positions = $field_info[0];
        $field_widths = $field_info[1];
        $family_listing_height_in_lines = max($family_array[0][0], $family_array[0][1]);
        

        // Output a divider with a blank line above it. 
        $this->Ln($line_height);
        $current_y = $this->GetY();  //Where are we on the page?
        $this->line($left_margin, $current_y+$line_height, ($this->w)-$left_margin, $current_y+$line_height);
        $next_row = $this->GetY() + $line_height;

        // Process family listing for 1 family
        for ( $i=1; $i<=$family_listing_height_in_lines; $i++) {

            $this->SetXY($field_positions[0], $next_row);
            $this->Cell( $field_widths[0], $line_height, $family_array[$i][1]);  // Left side of listing. 

            $this->SetX($field_positions[1]);
            $this->Cell($field_widths[1], $line_height, $family_array[$i][4] . ' ' . $family_array[$i][5]);  // Name

            $this->SetX($field_positions[2]);
            $this->Cell($field_widths[2], $line_height, $family_array[$i][6]); // em

            $this->SetX($field_positions[3]);
            $this->Cell($field_widths[3], $line_height, $family_array[$i][7]); // cell

            $this->SetX($field_positions[4]);
            $this->Cell($field_widths[4], $line_height, $family_array[$i][8]); // DoB

            $this->SetX($field_positions[5]);
            $this->Cell($field_widths[5], $line_height, $family_array[$i][9]); // DoBap

            $next_row = $this->GetY() + $line_height;
        }

        // return where we are. 
        return $this->GetY();
    }

    function enough_room_for_family( $lines_to_output, $line_height=0.25 ) {
        $page_break_now = 10;  // If we hit this, time to break to new page. 
        if ( 0 == $lines_to_output ) {
            return true;  // weird input. Assume okay. 
        } else {
            // Calculate the room required given number of lines to output
            // Compare to where we are now
            $start_y = $this->GetY(); // Where we are now
            $needed_height = ( $lines_to_output + 1 ) * $line_height; // Add 1 to account for decorative line.
            if ( $start_y + $needed_height >= $page_break_now ) {
                return false; // not enough room
            } else {
                return true;  // enough room
            }
        }
    }


    function dummy_up_pages( $pdfobject, $no_of_pages) {
        foreach ($no_of_pages as $pair) {
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