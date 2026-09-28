<?php
/**
 * Booklet PDF Generator
 *
 * Extends the FPDF library to support half-letter booklet formatting,
 * page ordering for 2-up printing, and custom family directory layout.
 */
require_once '../app-libraries/fpdf/fpdf.php';
require_once '../app-includes/class-app-settings.php';


class PDF extends FPDF {

	public $booklet_pages        = array();
	public $booklet_total_pages  = 0; // Number of pages in document at any time.
	public $sheets_needed        = 0;
	public $page_width           = 0;
	private $current_page_number = 0;
	private $header_height       = 0.0;  // Height of family listing page header.
	private $footer_position     = 'center'; // 'center', 'left' or 'right' for current page
	public $booklet_page_numbers = array(); // Holds the booklet page number for each output page

	/**
	 * Constructor for booklet PDF
	 * Sets up portrait orientation with custom half-letter size for 2-up printing
	 */
	public function __construct( $orientation = 'P', $unit = 'in', $size = 'Letter' ) {
		// Standard letter size: 8.5" x 11"
		$size = array( 8.5, 11 );
		parent::__construct( $orientation, $unit, $size );
		$this->SetAutoPageBreak( true, 0.5 );
		$this->SetMargins( 0.5, 0.5, 0.5 ); // Smaller margins for booklet format
	}
	/**
	 * Generate booklet page order for 2-up printing
	 * @param int $total_pages Total number of content pages
	 * @return array Array of page numbers in booklet order
	 */
	public function generate_booklet_order() {
		// Ensure total pages is a multiple of 4 (each sheet has 2 pages front and back)
		$total_pages = $this->booklet_total_pages;
		$pages_to_print = ( 0 === $total_pages % 4 ) ? $total_pages : $total_pages + ( 4 - ( $total_pages % 4 ) );

		$booklet_order = array();
		$this->sheets_needed = $pages_to_print / 4;
		$last_page  = $this->sheets_needed * 4;

		for ( $sheet = 1; $sheet <= $this->sheets_needed; $sheet++ ) {
			// Calculate page numbers for this sheet
			// $first_page = $sheet * 4 + 1;
			// $last_page  = $pages_to_print - ( $sheet * 4 );

			if ( $sheet % 2 === 0 ) {
				// even sheets
				$front_left = $last_page - ( ( $sheet - 1) * 2);
				$front_right = ($sheet- 1)*2 + 1;
				$rear_left = $front_right + 1;
				$rear_right = $front_left - 1;
			} else {
				// odd sheets				
				$front_left = $last_page - ( ( $sheet - 1) * 2);
				$front_right = ($sheet- 1)*2 + 1;
				$rear_left = $front_right + 1;
				$rear_right = $front_left - 1;
			}
			// Front of sheet: last_page (left) | first_page (right)
			// $booklet_order[] = array( $last_page, $first_page );
			$booklet_order[] = array( $front_left, $front_right );
			 // Back of sheet: first_page + 1 (left) | last_page - 1 (right)
			// Back of sheet: first_page + 1 (left) | last_page - 1 (right)
			// $booklet_order[] = array( $first_page + 1, $last_page - 1 );
			$booklet_order[] = array( $rear_left, $rear_right );
		}
		return $booklet_order;
	}

	/**
	 * Add a page to the booklet collection
	 * 
	 * Page counter/number is incremented PRIOR to adding the page. 
	 * 
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
		$this->booklet_total_pages = $this->current_page_number;
	}

	/**
	 * Generate the final booklet PDF with correct page ordering
	 *
	 * */
	public function generate_booklet_pdf( $size = 'Letter' ) {
		// This is the number of pages with content printed. Not number of sheets*4.  
		$total_pages   = $this->booklet_total_pages;
		$booklet_order = $this->generate_booklet_order();

		// Create a new PDF for the final booklet
		$final_pdf                       = new PDF( 'P', 'in', $size );
		$final_pdf->booklet_page_numbers = array(); // Track booklet page numbers for each output page

		foreach ( range( 1, $total_pages ) as $page_number ) {
			$final_pdf->footer_position = 'center';
			$final_pdf->AddPage();
			$final_pdf->booklet_page_numbers[] = $page_number;
			$this->render_page_content( $final_pdf, $this->booklet_pages[ $page_number - 1 ] );
		}
		return $final_pdf;
	}


	/**
	 * Render individual page content
	 * @param PDF $pdf PDF object
	 * @param array $page_data Page data
	 */
	public function render_page_content( $pdf, $page_data ) {
		$content_type = $page_data['content_type'];
		$content_data = $page_data['content_data'];

		switch ( $content_type ) {
			case 'cover':
				$this->render_cover_page( $pdf, $content_data );
				break;
			case 'intro':
				$this->render_intro_page( $pdf, $content_data );
				break;
			case 'family':
				$this->render_family_page( $pdf, $content_data );
				break;
			case 'family_summary':
				$this->render_family_summary_page( $pdf, $content_data );
				break;
			case 'back_cover':
				$this->render_back_cover( $pdf, $content_data );
				break;
			case 'blank':
				$this->render_blank_page( $pdf, $content_data );
				break;
		}
	}

	/**
	 * Render cover page
	 */
	public function render_cover_page( $pdf, $data ) {
		$pdf->SetFont( 'Arial', 'I', 18 );
		$pdf->center_this_text( $data['title'], 0.5 );
		$pdf->SetFontSize( 14 );
		$pdf->center_this_text( $data['author'], 0.75 );

		if ( isset( $data['logo'] ) ) {
			$page_width  = $pdf->GetPageWidth();
			$image_width = 5.5;
			$x           = ( $page_width - $image_width ) / 2;
			$pdf->Image( $data['logo'], $x, 1.5, $image_width );
		}
	}

	/**
	 * Render intro page
	 */
	public function render_intro_page( $pdf, $data ) {
		$cota_app_settings = new App_Settings();
		$pdf->SetFont( 'Arial', '', $cota_app_settings->INTRO_HEADING_FONT );
		$pdf->center_this_text( $data['title'], 0.5 );
		// Need to use a monospace font to preserve spacing for formatted content like the vestry listing
		$save_left = $pdf->lMargin;
		// Set a narrow left margin for wide intro content, then position using that margin
		$pdf->SetLeftMargin( 0.5 );
		$pdf->SetFont( 'Courier', '', $cota_app_settings->INTRO_CONTENT_FONT );
		// Move to left margin + small offset and set starting Y
		$pdf->SetXY( $pdf->lMargin, 1 );
		$pdf->MultiCell( 5.25, 0.20, $data['content'], 0, 'L', false );
		// Restore previous left margin and keep X aligned to restored margin
		$current_y = $pdf->GetY();
		$pdf->SetLeftMargin( $save_left );
		$pdf->SetXY( $save_left, $current_y );
	}

	/**
	 * Render family summary page
	 */
	public function render_family_summary_page( $pdf, $data ) {
		$cota_app_settings = new App_Settings();
		$pdf->SetFont( 'Arial', 'B', $cota_app_settings->FAMILY_HEADING_FONT );
		$pdf->center_this_text( $data['title'], 0.5 );
		$pdf->SetFont( 'Arial', 'I', $cota_app_settings->FAMILY_HEADING_FONT-2 );
		$pdf->SetXY( 0.5, 1 );
		$pdf->MultiCell( 1.5, 0.15, $data['content'] );
	}

	/**
	 * Render family listing page
	 */
	public function render_family_page( $pdf, $data ) {
		$cota_app_settings = new App_Settings();
		// For booklet pages, we need to render the family data using the array structure
		// that print_family_array expects
		if ( isset( $data['families'] ) && is_array( $data['families'] ) ) {
			// Set up the page for family listing
			$pdf->SetFont( 'Arial', 'B', $cota_app_settings->FAMILY_LISTING_FONT );
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
			$pdf->SetFont( 'Arial', 'B', $cota_app_settings->FAMILY_LISTING_FONT );
			$pdf->center_this_text( 'Family & Members Listing', 0.5 );
			$pdf->SetFont( 'Arial', '', $cota_app_settings->FAMILY_LISTING_FONT-1 );
			$pdf->SetXY( 0.5, 1 );
			$pdf->Cell( 4.5, 0.2, 'Family data not available' );
		}
	}

	/**
	 * Render back cover
	 */
	public function render_back_cover( $pdf, $data ) {
		$cota_app_settings = new App_Settings();
		$pdf->SetFont( 'Arial', '', $cota_app_settings->DIRECTORY_HEADING_FONT );
		$text = 'Printed ' . date( 'F j, Y' );
		$pdf->center_this_text( $text, 3.5 );
	}

	/**
	 * Render blank page
	 */
	public function render_blank_page( $pdf, $data ) {
		$cota_app_settings = new App_Settings();
		// Simply add a blank page with no content
		$pdf->SetFont( 'Arial', '', $cota_app_settings->DIRECTORY_HEADING_FONT_SMALL );
		$text = '(Blank)';
		$pdf->center_this_text( $text, 3.5 );
	}

	function center_this_text( $text, $vertical_position ) {
		$page_width = $this->GetPageWidth();
		$text_width = $this->GetStringWidth( $text );
		// Calculate X position to center
		$x = ( $page_width - $text_width ) / 2;
		$this->SetXY( $x, $vertical_position ); // 1 inch from the top
		$this->Cell( $text_width, 0.25, $text );
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
			// $this->center_this_text( $text, $y );
			$x = $this->x;
		}
		$this->SetX( $x );
		$this->Write( 0.25, $text );
	}

	function Header() {
		$cota_app_settings = new App_Settings();
		$this->SetFont( 'Arial', 'B', $cota_app_settings->DIRECTORY_HEADING_FONT );  // Arial bold 15
		$this->header_height = $this->GetY() + 0.5;
	}

	public function getHeaderHeight(): float {
		return $this->header_height;
	}
	function Footer() {
		$this->SetFont( 'Arial', 'I', 8 );  // Select Arial italic 8
		// $this->SetTextColor( 128 );  // Text color in gray
		$this->footer_position = 'center';

		// Get the correct booklet page number for this output page
		$output_page_index = $this->page - 1; // FPDF's $this->page is 1-based
		$booklet_page_no   = isset( $this->booklet_page_numbers[ $output_page_index ] ) ? $this->booklet_page_numbers[ $output_page_index ] : $this->PageNo();

		$footer_text = '- ' . $booklet_page_no . ' -';
		$this->SetY( -0.25 );  // Set position 1/4" from bottom of page.

		if ( 0 == $output_page_index ) {
			return; // no footer on 1st output page (starts on right)
		}

		// Use the footer_position property to determine justification
		if ( $this->footer_position === 'left' ) {
			$this->just_this_text( $footer_text, $align = 'L' );
		} elseif ( $this->footer_position === 'right' ) {
			$this->just_this_text( $footer_text, $align = 'R' );
		} elseif ( $this->footer_position === 'center' ) {
			$this->just_this_text( $footer_text, $align = 'C' );
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
		$cota_app_settings = new App_Settings();
		$this->SetFont( 'Arial', '', $cota_app_settings->FAMILY_HEADING_FONT );
		$line_height   = $cota_app_settings->FAMILY_LISTING_LINE_HEIGHT;
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
		$this->SetFont( 'Arial', '', $cota_app_settings->FAMILY_LISTING_FONT );
		$this->SetXY( $field_positions[0], $start_heading );
		$this->Cell( $wline1[0], $line_height, 'Family Name/Address' );

		$this->SetX( $field_positions[1] );
		$this->Cell( $wline1[1], $line_height, 'Family Members' );

		// Table Headers - 2nd line in italics.
		$this->SetFont( 'Arial', 'I', $cota_app_settings->FAMILY_LISTING_FONT  );

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
		$cota_app_settings = new App_Settings();

		$this->SetFont( 'Arial', '', $cota_app_settings->FAMILY_LISTING_FONT );
		$line_height     = $cota_app_settings->FAMILY_LISTING_LINE_HEIGHT;
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

			if ( ( is_array( $family_array ) && isset( $family_array[ $i ] ) && is_array( $family_array[ $i ] ) && ! empty( $family_array[ $i ][1] ) ) ) {

				$this->SetXY( $field_positions[0], $next_row );
				$this->Cell( $field_widths[0], $line_height, $family_array[ $i ][1] );  // Left side of listing.

				$this->SetX( $field_positions[1] );
				if ( isset( $family_array[ $i ][3] ) && isset( $family_array[ $i ][2] ) ) {
					// For long names, reduce font size for this line only.
					if ( ! empty( $family_array[ $i ][2] ) && ( 15 <= ( strlen($family_array[ $i ][2]) + strlen($family_array[ $i ][3] ) ) ) ) {
						// save font size, set font size to smaller for long names
						$original_font_size = $this->FontSizePt;
						$this->SetFontSize( $original_font_size - 2 );
					}
					$this->Cell( $field_widths[1], $line_height, $family_array[ $i ][2] . ' ' . $family_array[ $i ][3] );  // Name
					// $this->SetFont( 'Arial', '', 7 );
					$this->SetFont( 'Arial', '', $cota_app_settings->FAMILY_LISTING_FONT );
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
}
