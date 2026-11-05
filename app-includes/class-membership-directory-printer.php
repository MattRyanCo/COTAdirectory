<?php
/**
 * Membership Directory Printer
 *
 * Responsible for generating RTF output for the directory including
 * family listings and introductory content pages.
 */

require_once __DIR__ . '/intro-content.php';

class Membership_Directory_Printer {

	public string $rtfContent;

	/**
	 * Start the RTF document. Font 0 (Arial) is the normal text font, matching
	 * the PDF; font 1 (Courier New) is used for intro page text so the
	 * fixed-width staff / vestry / leadership columns line up.
	 */
	public function generateRTFHeader() {
		return '{\\rtf1\\ansi\\ansicpg1252\\deff0\\nouicompat'
			. '{\\fonttbl{\\f0\\fswiss\\fcharset0 Arial;}{\\f1\\fmodern\\fcharset0 Courier New;}}'
			. '\\f0\\fs24 ';
	}

	/**
	 * Escape plain text for RTF; line breaks become RTF paragraph breaks.
	 */
	public function formatText( $text ) {
		$text = str_replace( array( "\r\n", "\r" ), "\n", (string) $text );
		return str_replace( "\n", '\\par ', $this->escape( $text ) );
	}

	/**
	 * Escape text for RTF.
	 *
	 * Text saved by the Add Family form is HTML-encoded (e.g. &#039; for an
	 * apostrophe), so entities are decoded first. Then RTF's special characters
	 * (\ { }) are escaped and any non-ASCII character is written as \uN? so
	 * accented names print correctly.
	 */
	private function escape( $text ) {
		$text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = strtr(
			$text,
			array(
				'\\' => '\\\\',
				'{'  => '\\{',
				'}'  => '\\}',
			)
		);
		return preg_replace_callback(
			'/[^\x00-\x7F]/u',
			function ( $m ) {
				$code = mb_ord( $m[0], 'UTF-8' );
				if ( $code > 0xFFFF ) {
					return '?'; // Outside RTF's 16-bit \u range.
				}
				// RTF \u takes a signed 16-bit number; ? is the fallback for old readers.
				return '\\u' . ( $code > 32767 ? $code - 65536 : $code ) . '?';
			},
			$text
		);
	}

	/**
	 * Format Family Listings
	 *
	 * @param mysqli_result $families all family data
	 * @return string RTF content
	 */
	public function formatFamilyListings( $families ) {
		global $cota_db, $connect;

		$ictr    = 1;
		$listing = ' ';
		while ( $ictr <= $families->num_rows ) {
			$one_family = $families->fetch_assoc();

			// Get family members
			$individuals = $connect->query( 'SELECT * FROM members WHERE family_id = ' . (int) $one_family['id'] . ' ORDER BY `first_name`' );
			$family_name = (string) $one_family['familyname'];
			$address     = (string) $one_family['address'];
			$address2    = (string) $one_family['address2'];
			$city        = (string) $one_family['city'];
			$state       = (string) $one_family['state'];
			$zip         = (string) $one_family['zip'];
			$homephone   = (string) $one_family['homephone'];

			$listing .= '\\par\\pard\\keepn\\b ' . $this->escape( $family_name ) . '\\plain';
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
						$individual_name_listing = ucwords( (string) $individual['first_name'] ) . ' ' . ucwords( (string) $individual['last_name'] );
					} else {
						$individual_name_listing = ucwords( (string) $individual['first_name'] );
					}

					$linePrefix = '\\par\\pard\\keepn' . $tabStop . '    ';
					if ( ! empty( $individual['birthday'] ) ) {
						$listing .= $linePrefix . $this->escape( $individual_name_listing ) . ' \\tab DoB: ' . date( 'm/d', strtotime( $individual['birthday'] ) );
					} else {
						$listing .= $linePrefix . $this->escape( $individual_name_listing );
					}
				}
			}
			++$ictr;
			$listing .= '\\par';
		}
		return $listing;
	}

	/**
	 * Intro pages, one per intro file (intro1.txt, intro2.txt, ...), each on
	 * its own page. As in the PDF, the first line of a file is the page title
	 * and [staff], [vestry] and [leadership] are replaced with the current lists.
	 *
	 * @param int $num_intro_pages Number of intro files to include.
	 * @return string RTF content
	 */
	public function print_intro_pages( $num_intro_pages = 3 ) {
		$rtfContent = '';
		for ( $i = 1; $i <= $num_intro_pages; $i++ ) {
			$intro = cota_read_intro_file( $i );
			if ( null === $intro ) {
				continue;
			}
			$rtfContent .= '\\pard\\qc\\b\\f0\\fs28 ' . $this->formatText( $intro['title'] ) . '\\b0\\par\\pard\\par '
				. '{\\f1\\fs20 ' . $this->formatText( $intro['content'] ) . '}'
				. '\\f0\\fs24';
			// Page break after each intro page except the last.
			$rtfContent .= ( $i == $num_intro_pages ) ? '\\pard\\par' : '\\pard\\page\\par';
		}
		return $rtfContent;
	}
}
