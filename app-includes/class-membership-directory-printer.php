<?php
/**
 * Membership Directory Printer
 *
 * Responsible for generating RTF output for the directory including
 * family listings and introductory content pages.
 */
class Membership_Directory_Printer {

	public string $rtfContent;

	public function generateRTFHeader() {
		return "{\\rtf1\\ansi\\deff0\\nouicompat\\fs24 ";
	}

	public function formatText( $text ) {
		return str_replace( "\n", '\\par ', $this->escape( $text ) );
	}

	private function escape( $text ) {
		return htmlspecialchars( (string) $text );
	}

	/**
	 * Format Family Listings
	 *
	 * @param [type] $families all family data
	 * @return void
	 */
	public function formatFamilyListings( $families ) {
		global $cota_db, $connect;

		$ictr    = 1;
		$listing = ' ';
		while ( $ictr <= $families->num_rows ) {
			$one_family = $families->fetch_assoc();

			// Get family members
			$individuals = $connect->query( 'SELECT * FROM members WHERE family_id = ' . $one_family['id'] . ' ORDER BY `first_name`' );
			$family_name = (string) $one_family['familyname'];
			$address     = (string) $one_family['address'];
			$address2    = (string) $one_family['address2'];
			$city        = (string) $one_family['city'];
			$state       = (string) $one_family['state'];
			$zip         = (string) $one_family['zip'];
			$homephone   = (string) $one_family['homephone'];

			$listing .= "\\par\\pard\\keepn\\b " . $this->escape( $family_name ) . '\\plain';
			if ( '' !== $address2 ) {
				$listing .= '\\par\\pard\\keepn ' . $this->escape( $address ) . ' ' . $this->escape( $address2 );
			} else {
				$listing .= '\\par\\pard\\keepn ' . $this->escape( $address );
			}
			if ( $city !== '' ) {
				$listing .= '\\par\\pard\\keepn ' . $this->escape( $city ) . ', ' . $this->escape( $state ) . ' ' . $this->escape( $zip );
			} else {
				$listing .= '\\par\\pard\\keepn ';
			}
			if ( $homephone !== '' ) {
				$listing .= '\\par\\pard\\keepn H: ' . $this->escape( $homephone );
			}

			// Get family members
			if ( $individuals->num_rows !== 0 ) {
				// set a tab stop so DoB lines up regardless of name length
				$tabStop = '\\tx3600';
				$listing .= '   \\par\\pard\\keepn\\i ' . $tabStop . '    Family Members \\plain';
				foreach ( $individuals as $individual ) {
					if ( $individual['last_name'] !== $family_name ) {
						$individual_name_listing = ucwords($individual['first_name']) . ' ' . ucwords($individual['last_name']);
					} else {
						$individual_name_listing = ucwords($individual['first_name']);
					}

					$linePrefix = '\\par\\pard\\keepn' . $tabStop . '    ';
					if ( ! empty( $individual['birthday'] ) ) {
						$listing .= $linePrefix . htmlspecialchars( $individual_name_listing ) . ' \\tab DoB: ' . date( 'm/d', strtotime( $individual['birthday'] ) );
					} else {
						$listing .= $linePrefix . htmlspecialchars( $individual_name_listing );
					}
				}
			}
			++$ictr;
			$listing .= '\\par';
		}
		return $listing;
	}

	public function print_intro_pages( $num_intro_pages = 3 ) {
		// Load and insert static pages.
		$rtfContent = '';
		for ( $i = 1; $i <= $num_intro_pages; $i++ ) {
			$file = '../uploads/intro' . $i . '.txt';
			if ( file_exists( $file ) ) {
				if ( $i == $num_intro_pages ) {
					// last document, no page break
					$rtfContent .= $this->formatText( file_get_contents( $file ) ) . '\\pard\\par';
				} else {
					$rtfContent .= $this->formatText( file_get_contents( $file ) ) . '\\pard\\page\\par';
				}
			}
		}
		return $rtfContent;
	}
}
