<?php
require_once __DIR__ . '/bootstrap.php';
/**
 * cota_format_family_listing_for_fpdf
 *
 * Takes a family array and a members result set, formats the family listing for FPDF output.The output is
 * designed to mimic the displayed format. The output is writen to a PDF file for later processing by a
 * PDF booklet printing app.
 *
 * @param [type] $family
 * @param [type] $members
 * @return void
 */
function cota_format_family_listing_for_fpdf( $pdfobj, $family, $members ) {

	// Set key logicals
	$num_members       = $members->num_rows;
	$ictr              = 1;
	$addr1             = ( isset( $family['address'] ) && $family['address'] !== '' ) ? $family['address'] : false;
	$addr2             = ( isset( $family['address2'] ) && $family['address2'] !== '' ) ? $family['address2'] : false;
	$city              = ( isset( $family['city'] ) && $family['city'] !== '' ) ? $family['city'] : false;
	$home_phone        = ( isset( $family['homephone'] ) && $family['homephone'] !== '' ) ? $family['homephone'] : false;
	$placeholder       = '';
	$familystringlines = '';

	// Calling this function to print out 1 entire family to the pdf file.
	// The calling function must set up the pdf object, and handle the 2 line header for
	// the family directory listing
	// It must check the amout of space needed for the family and determine
	// if it needs to add a new page or not.

	// Outer loop through left side of printed entry block
	for ( $lctr = 1; $lctr <= 4; $lctr++ ) {
		if ( $lctr == 1 ) {
			// Build the family header (name and address, other column headings)
			// Get 1st member of family
			$pdfobj->multicell( 2, 0.5, "Family Name/Address Family Members\n", 0, 1 );
			$pdfobj->cell( 2, 0.5, 'Family Name/Address', 0, 0, 'L', false );
			$pdfobj->cell( 2, 0.5, 'Family Members', 0, 1, 'L', false );
			$pdfobj->cell( 2, 0.25, 'Home Phone', 0, 0, 'L', false );
			// $pdfobj->MultiCell(2, 0.5, "Home Phone   Name   Email   Cell   DoB   DoBaptism\n", 0, 1);
			$pdfobj->cell( 1, 0.25, 'Name', 0, 0, 'L', false );
			$pdfobj->cell( 1, 0.25, 'Email', 0, 0, 'L', false );
			$pdfobj->cell( 1, 0.25, 'Cell', 0, 0, 'L', false );
			$pdfobj->cell( 1, 0.25, 'DoB', 0, 0, 'L', false );
			$pdfobj->cell( 1, 0.25, 'DoBaptism', 0, 1, 'L', false );
			$familystringlines  = "Family Name/Address Family Members\n";
			$familystringlines .= "Home Phone   Name   Email   Cell   DoB   DoBaptism\n";

			$individual = $members->fetch_assoc();
			if ( ! $individual ) {
				// No members found
				break;
			}
			// $familystringlines = [];
			$familystringlines .= $family['familyname'] . ' ' .
				( $individual['first_name'] ?? '' ) . ' ' .
				( $individual['last_name'] ?? '' ) . ' ' .
				( $individual['email'] ?? '' ) . ' ' .
				( $individual['cell_phone'] ?? '' ) . ' ' .
				( $individual['birthday'] ?? '' ) . ' ' .
				( $individual['baptism'] ?? '' ) . "\n";
		} elseif ( $lctr == 2 ) {
			$individual = get_next_member_to_print( $members );
			if ( $addr1 ) {
				$left_side = $family['address'] . ' ';
				$addr1     = false;
				if ( $addr2 ) {
					$left_side .= ', ' . $family['address2'] . ' ';
					$addr2      = false;
				}
			} elseif ( $home_phone ) {
				$left_side  = 'Home: ' . $family['homephone'] . ' ';
				$addr2      = false;
				$city       = false;
				$home_phone = false;
			} else {
				// If no address, use placeholder.
				// Remaining left side address will be blank.
				$left_side = $placeholder;
				$addr2     = false;
				$city      = false;
			}
			$familystringlines .= $left_side . ' ' .
				( $individual['first_name'] ?? '' ) . ' ' .
				( $individual['last_name'] ?? '' ) . ' ' .
				( $individual['email'] ?? '' ) . ' ' .
				( $individual['cell_phone'] ?? '' ) . ' ' .
				( $individual['birthday'] ?? '' ) . ' ' .
				( $individual['baptism'] ?? '' ) . "\n";

		} elseif ( $lctr == 3 ) {
			$individual = get_next_member_to_print( $members );
			if ( $addr2 ) {
				$left_side = $family['address2'] . ' ';
				$addr2     = false;
			} elseif ( $city ) {
				$left_side = $family['city'] . ', ' .
				$family['state'] . ' ' .
				$family['zip'] . ' ';
				$city      = false;
			} else {
				// If no address, use placeholder.
				// Remaining left side address will be blank.
				$left_side = $placeholder;
			}
			$familystringlines .= $left_side . ' ' .
				( $individual['first_name'] ?? '' ) . ' ' .
				( $individual['last_name'] ?? '' ) . ' ' .
				( $individual['email'] ?? '' ) . ' ' .
				( $individual['cell_phone'] ?? '' ) . ' ' .
				( $individual['birthday'] ?? '' ) . ' ' .
				( $individual['baptism'] ?? '' ) . "\n";

		} elseif ( $lctr == 4 ) {
			$individual = get_next_member_to_print( $members );
			if ( $individual === false ) {
				// No more members found
				break;
			}
			if ( $home_phone ) {
				$left_side  = 'Home: ' . $family['homephone'] . ' ';
				$addr2      = false;
				$city       = false;
				$home_phone = false;
			} else {
				// If no address or phone, use placeholder.
				$left_side = $placeholder;
			}
			$familystringlines .= $left_side . ' ' .
				( $individual['first_name'] ?? '' ) . ' ' .
				( $individual['last_name'] ?? '' ) . ' ' .
				( $individual['email'] ?? '' ) . ' ' .
				( $individual['cell_phone'] ?? '' ) . ' ' .
				( $individual['birthday'] ?? '' ) . ' ' .
				( $individual['baptism'] ?? '' ) . "\n";
		} else {
			// No data for left side of display
		}
	}

	$left_side = $placeholder;
	// Loop through rest of family members
	while ( $ictr <= $num_members ) {
		$individual         = get_next_member_to_print( $members );
		$familystringlines .= $left_side . ' ' .
			( $individual['first_name'] ?? '' ) . ' ' .
			( $individual['last_name'] ?? '' ) . ' ' .
			( $individual['email'] ?? '' ) . ' ' .
			( $individual['cell_phone'] ?? '' ) . ' ' .
				( $individual['birthday'] ?? '' ) . ' ' .
				( $individual['baptism'] ?? '' ) . "\n";
		++$ictr;
	}

	// Output all lines for this family as a block in the PDF
	// $pdfobj->SetFont('Arial', '', 10);
	// $pdfobj->MultiCell(0, 0.22, implode("\n", $familystringlines), 1, 1);
	// $pdfobj->Ln(0.1); // Small space after each family

	return $familystringlines; // Indicate success
}
