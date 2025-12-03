<?php
require_once '../app-libraries/fpdf/fpdf.php';

class PDF extends FPDF {

	public $booklet_pages        = array();
	public $pageWidth            = 0;
	private $current_page_number = 0;
	private $header_height       = 0.0;  // Height of family listing page header.
	private $footer_position     = null; // 'left' or 'right' for current page
	public $booklet_page_numbers = array(); // Holds the booklet page number for each output page

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
	 *
	 * */
	public function generate_booklet_pdf() {
		$total_pages   = count( $this->booklet_pages );
		$booklet_order = $this->generate_booklet_order( $total_pages );
		// Create a new PDF for the final booklet
		$final_pdf                       = new PDF( 'P', 'in', 'HalfLetter' );
		$final_pdf->booklet_page_numbers = array(); // Track booklet page numbers for each output page

		$page_number_in_booklet = 1;
		foreach ( $booklet_order as $page_pair ) {
			// Left page
			$final_pdf->footer_position = 'left';
			$final_pdf->AddPage();
			if ( $page_pair[0] <= $total_pages ) {
				$final_pdf->booklet_page_numbers[] = $page_pair[0];
				$this->render_page_content( $final_pdf, $this->booklet_pages[ $page_pair[0] - 1 ], 'left' );
			} else {
				// Padded blank page for imposition; suppress footer numbering
				$final_pdf->booklet_page_numbers[] = 0;
				$final_pdf->SetFont( 'Arial', '', 8 );
				$final_pdf->center_this_text( '(Blank)', 4 );
			}

			// Right page
			$final_pdf->footer_position = 'right';
			$final_pdf->AddPage();
			if ( $page_pair[1] <= $total_pages ) {
				$final_pdf->booklet_page_numbers[] = $page_pair[1];
				$this->render_page_content( $final_pdf, $this->booklet_pages[ $page_pair[1] - 1 ], 'right' );
			} else {
				// Padded blank page for imposition; suppress footer numbering
				$final_pdf->booklet_page_numbers[] = 0;
				$final_pdf->SetFont( 'Arial', '', 8 );
				$final_pdf->center_this_text( '(Blank)', 4 );
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
			$page_width  = $pdf->GetPageWidth();
			$image_width = 3.5;
			$x           = ( $page_width - $image_width ) / 2;
			$pdf->Image( $data['logo'], $x, 1.5, $image_width );
		}
	}

	/**
	 * Render intro page
	 */
	public function render_intro_page( $pdf, $data, $position ) {
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
		$pdf->SetFont( 'Arial', 'B', 12 );
		$pdf->center_this_text( $data['title'], 0.5 );
		$pdf->SetFont( 'Arial', 'I', 10 );
		$pdf->SetXY( 0.5, 1 );
		$pdf->MultiCell( 4.5, 0.15, $data['content'] );
	}

	/**
	 * Render family listing page
	 */
	public function render_family_page( $pdf, $data, $position ) {
		// For booklet pages, we need to render the family data using the array structure
		// that print_family_array expects
		if ( isset( $data['families'] ) && is_array( $data['families'] ) ) {
			// Set up the page for family listing
			$pdf->SetFont( 'Arial', 'B', 10 );
			$pdf->center_this_text( 'Family & Members Listing', 0.5 );
			$pdf->print_family_array_headings( true );

			// Print each family using the existing print_family_array method
			foreach ( $data['families'] as $family_data ) {
				if ( isset( $family_data['family_array'] ) && isset( $family_data['field_info'] ) ) {
					$pdf->print_family_array( $family_data['family_array'], $family_data['field_info'] );
				}
			}
		} else {
			// Fallback for other data structures
			$pdf->SetFont( 'Arial', 'B', 8 );
			$pdf->center_this_text( 'Family & Members Listing', 0.5 );
			$pdf->SetFont( 'Arial', '', 7 );
			$pdf->SetXY( 0.5, 1 );
			$pdf->Cell( 4.5, 0.2, 'Family data not available' );
		}
	}

	/**
	 * Render back cover
	 */
	public function render_back_cover( $pdf, $data, $position ) {
		$pdf->SetFont( 'Arial', '', 6 );
		$text = 'Printed ' . date( 'F j, Y' );
		$pdf->center_this_text( $text, 3.5 );
	}

	// public function getPageBreakTrigger(): float
	// {
	//     return $this->PageBreakTrigger;
	// }

	function center_this_text( $text, $vertical_position ) {
		$pageWidth = $this->GetPageWidth();
		$textWidth = $this->GetStringWidth( $text );
		// Calculate X position to center
		$x = ( $pageWidth - $textWidth ) / 2;
		$this->SetXY( $x, $vertical_position ); // 1 inch from the top
		$this->Cell( $textWidth, 0.25, $text );
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
	function just_this_text( $text, $align = 'L' ) {
		$y = $this->GetY();
		if ( $align === 'L' ) {
			$x = $this->lMargin;
		} elseif ( $align === 'R' ) {
			$x = $this->w - $this->rMargin - ( $this->GetStringWidth( $text ) + .25 );
		} else {
			// Default to center alignment
			center_this_text( $text, $y );
		}
		$this->SetX( $x );
		$this->Write( 0.25, $text );
	}

	function Header() {
		$this->SetFont( 'Arial', 'B', 15 );  // Arial bold 15
		$this->header_height = $this->GetY() + 0.5;
	}

	public function getHeaderHeight(): float {
		return $this->header_height;
	}
	function Footer() {
		$this->SetFont( 'Arial', 'I', 8 );  // Select Arial italic 8
		$this->SetTextColor( 128 );  // Text color in gray

		// Get the correct booklet page number for this output page
		$output_page_index = $this->page - 1; // FPDF's $this->page is 1-based
		$booklet_page_no   = isset( $this->booklet_page_numbers[ $output_page_index ] ) ? $this->booklet_page_numbers[ $output_page_index ] : $this->PageNo();

		// Suppress footer on padded blank pages
		if ( $booklet_page_no === 0 ) {
			return;
		}

		$footer_text = 'Page ' . $booklet_page_no;
		$this->SetY( -0.25 );  // Set position 1/4" from bottom of page.

		if ( $output_page_index == 1 ) {
			return; // no footer on 1st output page (starts on right)
		}

		// Use the footer_position property to determine justification
		if ( $this->footer_position === 'left' ) {
			$this->just_this_text( $footer_text, $align = 'L' );
		} elseif ( $this->footer_position === 'right' ) {
			$this->just_this_text( $footer_text, $align = 'R' );
		} else {
			// fallback to even/odd logic if position is not set
			if ( $booklet_page_no % 2 == 0 ) {
				$this->just_this_text( $footer_text, $align = 'L' );
			} else {
				$this->just_this_text( $footer_text, $align = 'R' );
			}
		}
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
		$this->SetFont( 'Arial', '', 8 );
		$line_height   = .15;
		$left_margin   = $this->lMargin;
		$start_heading = $this->getHeaderHeight() + $line_height;

		// Do this setup only first time through
		if ( $first_time ) {
			$large_field_width = round( $this->GetStringWidth( 'Family Name/Address' ), 1 );
			$wline1            = array(
				round( $this->GetStringWidth( 'Family Name/Address' ), 1 ),
				round( $this->GetStringWidth( 'Family Members' ), 1 ),
			);  // width of heading lables line 1.
			$wline2            = array(
				round( $this->GetStringWidth( 'Home: xxx-xxx-xxxx_' ), 1 ), // [0]
				round( $this->GetStringWidth( 'LongFirstName ' ), 1 ), // [1]
				round( $this->GetStringWidth( 'EmailExample@example.com ' ), 1 ), // [2
				round( $this->GetStringWidth( '###-###-####__' ), 1 ),            // [3
				round( $this->GetStringWidth( 'mm/dd_' ), 1 ),                // [4
				round( $this->GetStringWidth( 'mm/dd_' ), 1 ),                 // [5
			); // width of heading lables line 2.
			$field_widths      = $wline2;
			$large_field_width = round( $this->GetStringWidth( 'Family Name/Address' ), 1 ) + 0.1;
			$field_positions   = array(
				$left_margin,                                         // [0] phone or blank
				$left_margin + $large_field_width,                                            // 1 name
				$left_margin + $large_field_width + $wline2[1],                                 // 2 em
				$left_margin + $large_field_width + $wline2[1] + $wline2[2],                      // 3 cell
				$left_margin + $large_field_width + $wline2[1] + $wline2[2] + $wline2[3],           // 4 DOB
				$left_margin + $large_field_width + $wline2[1] + $wline2[2] + $wline2[3] + $wline2[4], // 5 Bap
			);  // X position for start of label / fields to write
		}

		// Output headings here.
		// Table Headers - 1st line
		$this->SetFont( 'Arial', '', 7 );
		$this->SetXY( $field_positions[0], $start_heading );
		$this->Cell( $wline1[0], $line_height, 'Family Name/Address' );

		$this->SetX( $field_positions[1] );
		$this->Cell( $wline1[1], $line_height, 'Family Members' );

		// Table Headers - 2nd line in italics.
		$this->SetFont( 'Arial', 'I', 7 );

		// Print out column headings.
			// Set x position & print content 'cell'
		$this->SetXY( $field_positions[1], $start_heading + 0.15 );
		$this->Cell( $field_widths[1], $line_height, 'Name' );

		$this->SetX( $field_positions[2] );
		$this->Cell( $field_widths[2], $line_height, 'Email' );   //wline2[2]

		$this->SetX( $field_positions[3] );
		$this->Cell( $field_widths[3], $line_height, 'Cell' );

		$this->SetX( $field_positions[4] );
		$this->Cell( $field_widths[4], $line_height, 'DoB' );

		$this->SetX( $field_positions[5] );
		$this->Cell( $field_widths[5], $line_height, 'DoBap' );

		// $current_y = $this->GetY();
		return array(
			0 => $field_positions,
			1 => $field_widths,
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
	function print_family_array( $family_array, $field_info ) {

		$this->SetFont( 'Arial', '', 7 );
		$line_height     = 0.15;
		$left_margin     = $this->lMargin;
		$field_positions = $field_info[0];
		$field_widths    = $field_info[1];
		// $family_listing_height_in_lines = max($family_array[0][0], $family_array[0][1]);
		$family_listing_height_in_lines = max(
			$family_array[0]['left_side_ctr'],
			$family_array[0]['member_ctr']
		);

		// Output a divider with a blank line above it.
		$this->Ln( $line_height );
		$current_y = $this->GetY();
		$this->line( $left_margin, $current_y + $line_height, ( $this->w ) - $left_margin, $current_y + $line_height );
		$next_row = $this->GetY() + $line_height;

		// Process family listing for 1 family
		for ( $i = 1; $i <= $family_listing_height_in_lines; $i++ ) {

			if ( ( is_array( $family_array ) && ! empty( $family_array[ $i ][1] ) ) ) {

				$this->SetXY( $field_positions[0], $next_row );
				$this->Cell( $field_widths[0], $line_height, $family_array[ $i ][1] );  // Left side of listing.

				$this->SetX( $field_positions[1] );
				if ( isset( $family_array[ $i ][3] ) && ! empty( $family_array[ $i ][3] ) ) {
					// For long names, reduce font size for this line only.
					if ( ! empty( $family_array[ $i ][2] ) && ( 15 <= ( strlen($family_array[ $i ][2]) + strlen($family_array[ $i ][3] ) ) ) ) {
						// save font size, set font size to smaller for long names
						$original_font_size = $this->FontSizePt;
						$this->SetFontSize( $original_font_size - 2 );
					}
					$this->Cell( $field_widths[1], $line_height, $family_array[ $i ][2] . ' ' . $family_array[ $i ][3] );  // Name
					$this->SetFont( 'Arial', '', 7 );
				} else {
					$this->Cell( $field_widths[1], $line_height, $family_array[ $i ][2] );  // Name
				}

				$this->SetX( $field_positions[2] );
				$this->Cell( $field_widths[2], $line_height, $family_array[ $i ][4] ); // em

				$this->SetX( $field_positions[3] );
				$this->Cell( $field_widths[3], $line_height, $family_array[ $i ][5] ); // cell

				$this->SetX( $field_positions[4] );
				$this->Cell( $field_widths[4], $line_height, $family_array[ $i ][6] ); // DoB

				$this->SetX( $field_positions[5] );
				$this->Cell( $field_widths[5], $line_height, $family_array[ $i ][7] ); // DoBap

				$next_row = $this->GetY() + $line_height;
			}
		}

		// return where we are.
		return $this->GetY();
	}

	function enough_room_for_family( $lines_to_output, $line_height = 0.15 ) {
		$page_break_now = 7;  // If we hit this, time to break to new page.
		if ( 0 == $lines_to_output ) {
			return true;  // weird input. Assume okay.
		} else {
			// Calculate the room required given number of lines to output
			// Compare to where we are now
			$start_y       = $this->GetY(); // Where we are now
			$needed_height = ( $lines_to_output + 1 ) * $line_height; // Add 1 to account for decorative line.
			if ( $start_y + $needed_height >= $page_break_now ) {
				return false; // not enough room
			} else {
				return true;  // enough room
			}
		}
	}


	function dummy_up_pages( $pdfobject, $no_of_pages ) {
		foreach ( $no_of_pages as $pair ) {
			$pdfobject->AddPage();
			foreach ( $pair as $pageNum ) {
				if ( $pageNum > $no_of_pages ) {
					$pdfobject->Cell( 0, 5, '(Blank)', 0, 1, 'C' ); // Handle extra blank pages
				} else {
					$pdfobject->Cell( 0, 5, 'Page ' . $pageNum, 0, 1, 'C' );
				}
			}
		}
	}
}
