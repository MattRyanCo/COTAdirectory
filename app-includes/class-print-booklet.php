<?php
require_once '../app-libraries/fpdf/fpdf.php';


class PDF extends FPDF
{
    private $header_height = 0.0;  // Height of family listing page header.

    /**
	 * Constructor for booklet PDF
	 * Sets up portrait orientation with custom half-letter size for 2-up printing
	 */
	public function __construct( $orientation = 'P', $unit = 'in', $size = 'HalfLetter' ) {
		// For booklet printing, we need half-letter size (5.5" x 8.5") in portrait
		// This will print 2-up on 8.5" x 11" paper
		if ( $size === 'HalfLetter' ) {
			// Create custom page size: 5.5" x 8.5" (half of letter size)
			// For booklet printing, we want portrait orientation with 5.5" width and 8.5" height
			// This will print 2-up on 8.5" x 11" paper
			$size = array( 5.5, 8.5 ); // Width, Height in inches
		}

		parent::__construct( $orientation, $unit, $size );
		// Set up booklet-specific settings
		$this->SetAutoPageBreak( true, 0.5 );
		$this->SetMargins( 0.25, 0.25, 0.25 ); // Smaller margins for booklet format
	}
    	/**
	 * Generate booklet page order for 2-up printing
	 * @param int $total_pages Total number of content pages
	 * @return array Array of page numbers in booklet order
	 */
	public function generate_booklet_order( $total_pages ) {
		// Ensure total pages is a multiple of 4 (each sheet has 2 pages front and back)
		$pages_to_print = ( $total_pages % 4 === 0 ) ? $total_pages : $total_pages + ( 4 - ( $total_pages % 4 ) );

		$booklet_order = array();
		$sheets_needed = $pages_to_print / 4;

		for ( $sheet = 0; $sheet < $sheets_needed; $sheet++ ) {
			// Calculate page numbers for this sheet
			$first_page = $sheet * 4 + 1;
			$last_page  = $pages_to_print - ( $sheet * 4 );

			// Front of sheet: last_page (left) | first_page (right)
			$booklet_order[] = array( $last_page, $first_page );

			// Back of sheet: first_page + 1 (left) | last_page - 1 (right)
			$booklet_order[] = array( $first_page + 1, $last_page - 1 );
		}

		return $booklet_order;
	}

    /**
	 * Add a page to the booklet collection
	 * @param string $content_type Type of content (cover, intro, family, etc.)
	 * @param mixed $content_data Content data for the page
	 */
	public function add_booklet_page( $content_type, $content_data = null ) {
		++$this->current_page_number;
		$this->booklet_pages[] = array(
			'page_number'  => $this->current_page_number,
			'content_type' => $content_type,
			'content_data' => $content_data,
		);
	}

	/**
	 * Generate the final booklet PDF with correct page ordering
	 */
	public function generate_booklet_pdf() {
		$total_pages   = count( $this->booklet_pages );
		$booklet_order = $this->generate_booklet_order( $total_pages );

		// Create a new PDF for the final booklet
		$final_pdf = new PDF( 'P', 'in', 'HalfLetter' );

		$final_pdf->AddPage();
		foreach ( $booklet_order as $sheet ) {
			if ( $sheet[0] <= $total_pages ) {
				$this->render_page_content( $final_pdf, $this->booklet_pages[ $sheet[0] - 1 ], 'left' );
				$final_pdf->AddPage();
				// } else {
				//  // Blank page
				//  $final_pdf->SetFont( 'Arial', '', 8 );
				//  $final_pdf->center_this_text( '(Blank)', 4 );
			}

			// Right page (front of sheet)
			if ( $sheet[1] <= $total_pages ) {
				$this->render_page_content( $final_pdf, $this->booklet_pages[ $sheet[1] - 1 ], 'right' );
				$final_pdf->AddPage();
				// } else {
				//  // Blank page
				//  $final_pdf->SetFont( 'Arial', '', 8 );
				//  $final_pdf->center_this_text( '(Blank)', 4 );
			}
		}

		return $final_pdf;
	}


	/**
	 * Render individual page content
	 * @param PDF $pdf PDF object
	 * @param array $page_data Page data
	 * @param string $position left or right
	 */
	public function render_page_content( $pdf, $page_data, $position ) {
		$content_type = $page_data['content_type'];
		$content_data = $page_data['content_data'];

		switch ( $content_type ) {
			case 'cover':
				$this->render_cover_page( $pdf, $content_data, $position );
				break;
			case 'intro':
				$this->render_intro_page( $pdf, $content_data, $position );
				break;
			case 'family':
				$this->render_family_page( $pdf, $content_data, $position );
				break;
            case 'family_summary':
				$this->render_family_summary_page( $pdf, $content_data, $position );
				break;
			case 'back_cover':
				$this->render_back_cover( $pdf, $content_data, $position );
				break;
		}
	}

	/**
	 * Render cover page
	 */
	public function render_cover_page( $pdf, $data, $position ) {
		$pdf->SetFont( 'Arial', 'I', 14 );
		$pdf->center_this_text( $data['title'], 0.5 );
		$pdf->SetFontSize( 8 );
		$pdf->center_this_text( $data['author'], 0.75 );

		if ( isset( $data['logo'] ) ) {
			$pageWidth  = $pdf->GetPageWidth();
			$imageWidth = 3.5;
			$x          = ( $pageWidth - $imageWidth ) / 2;
			$pdf->Image( $data['logo'], $x, 1.5, $imageWidth );
		}
	}

	/**
	 * Render intro page
	 */
	public function render_intro_page( $pdf, $data, $position ) {
        $pdf->AddPage();
		$pdf->SetFont( 'Arial', '', 10 );
		$pdf->center_this_text( $data['title'], 0.5 );
		$pdf->SetFont( 'Arial', '', 8 );
		$pdf->SetXY( 0.5, 1 );
		$pdf->MultiCell( 4.5, 0.15, $data['content'] );
	}

    /**
	 * Render family summary page
	 */
    public function render_family_summary_page( $pdf, $data, $position ) {
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12 );
        $pdf->center_this_text( $data['title'], 0.5);
        $pdf->SetFont('Arial', 'I', 10);
		$pdf->SetXY( 0.5, 1 );
		$pdf->MultiCell( 4.5, 0.15, $data['content'] );
    }

	/**
	 * Render family listing page
	 */
	public function render_family_page( $pdf, $data, $position ) {
		$pdf->SetFont( 'Arial', 'B', 8 );
		$pdf->center_this_text( 'Family & Members Listing', 0.5 );
		$pdf->SetFont( 'Arial', '', 7 );

		// Render family data here
		if ( isset( $data['families'] ) ) {
			$y = 1;
			foreach ( $data['families'] as $family ) {
				// Display family name as header
				$pdf->SetXY( 0.5, $y );
				$pdf->SetFont( 'Arial', 'B', 8 );
				$pdf->Cell( 4.5, 0.2, $family['name'] );
				$y += 0.25;

				// Display formatted family data
				if ( isset( $family['data'] ) && is_string( $family['data'] ) ) {
					$pdf->SetFont( 'Arial', '', 7 );
					$pdf->SetXY( 0.5, $y );
					// Split the formatted string into lines and display each line
					$lines = explode( "\n", trim( $family['data'] ) );
					foreach ( $lines as $line ) {
						if ( ! empty( trim( $line ) ) ) {
							$pdf->SetXY( 0.5, $y );
							$pdf->Cell( 4.5, 0.15, trim( $line ) );
							$y += 0.2;
						}
					}
				}
				$y += 0.1; // Add space between families
			}
		}
	}

	/**
	 * Render back cover
	 */
	public function render_back_cover( $pdf, $data, $position ) {
        $pdf->AddPage();
		$pdf->SetFont( 'Arial', '', 6 );
		$text = 'Printed ' . date( 'F j, Y' );
		$pdf->center_this_text( $text, 3.5 );
	}



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
        global $title;
        $this->SetFont('Arial','B',15);  // Arial bold 15
        $this->SetTextColor(128);;
        $this->center_this_text( $title, 0 );
        $this->header_height = $this->GetY() + 0.25;
    }

    public function getHeaderHeight(): float
    {
        return $this->header_height;
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
        $text = "Printed " . date('F j, Y');
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

        // @TODO Add new add_booklet_page functionality into here. 

    function print_family_array_headings( $first_time ) {
        $this->SetFont('Arial', '', 8);
        $line_height = .15;
        $left_margin = $this->lMargin;
        $start_heading = $this->getHeaderHeight() + $line_height;

        // Do this setup only first time through
        if ( $first_time ) {
            $large_field_width = round($this->GetStringWidth('Family Name/Address'),1);
            $wline1 = [
                round($this->GetStringWidth('Family Name/Address'),1),  
                round($this->GetStringWidth('Family Members'),1),          
            ];  // width of heading lables line 1. 
            $wline2 = [
                round($this->GetStringWidth('Home: xxx-xxx-xxxx_'),1), // [0]
                round($this->GetStringWidth('LongFirstName '),1), // [1]
                round($this->GetStringWidth('EmailExample@example.com '),1), // [2
                round($this->GetStringWidth('###-###-####__'),1),            // [3
                round($this->GetStringWidth('mm/dd_'),1),                // [4
                round($this->GetStringWidth('mm/dd_'),1)                 // [5
            ]; // width of heading lables line 2.
            $field_widths = $wline2;
            $large_field_width = round($this->GetStringWidth('Family Name/Address'),1) + 0.1;
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
        $this->SetFont('Arial', '', 7);
        $this->SetXY($field_positions[0], $start_heading );
        $this->Cell($wline1[0], $line_height, 'Family Name/Address');

        $this->SetX($field_positions[1]);
        $this->Cell($wline1[1], $line_height, 'Family Members');

        // Table Headers - 2nd line in italics. 
        $this->SetFont('Arial', 'I', 7);

        // Print out column headings.
            // Set x position & print content 'cell'
        $this->SetXY($field_positions[1], $start_heading + 0.15 );
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
     * Function assumes that we have enough space to print family array.
     * Need for AddPage calculated before calling us. 
     *
     * @param [type] $family_array
     * @return void
     */
    function print_family_array($family_array, $field_info ) {

        $this->SetFont('Arial', '', 7);
        $line_height = 0.15;
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

    function enough_room_for_family( $lines_to_output, $line_height=0.15 ) {
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