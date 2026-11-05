<?php
/**
 * Intro page content shared by the PDF and RTF directory files.
 *
 * Intro files (uploads/intro1.txt, intro2.txt, ...) can contain the
 * placeholders [staff], [vestry] and [leadership]; each is replaced with the
 * current list from Ministry Maintenance.
 *
 * @package COTAdirectory
 */

require_once __DIR__ . '/format-vestry-listing.php';
require_once __DIR__ . '/format-leadership-listing.php';
require_once __DIR__ . '/format-staff-listing.php';

/**
 * Replace intro content placeholders with the generated plain-text listings.
 *
 * @param string $content Intro content containing optional placeholders.
 * @return string Content with placeholders replaced.
 */
function cota_parse_intro_content( $content ) {
	$generators = array(
		'[staff]'      => 'cota_generate_staff_listing_for_print',
		'[vestry]'     => 'cota_generate_vestry_listing_for_print',
		'[leadership]' => 'cota_generate_leadership_listing_for_print',
	);
	foreach ( $generators as $placeholder => $generator ) {
		if ( false !== strpos( $content, $placeholder ) ) {
			$content = str_replace( $placeholder, $generator(), $content );
		}
	}
	return $content;
}

/**
 * Read intro file N and split it into its title (first line) and content
 * (the rest, with placeholders replaced). Returns null if the file doesn't exist.
 *
 * @param int $number Intro file number (intro1.txt is 1).
 * @return array{title:string, content:string}|null
 */
function cota_read_intro_file( $number ) {
	$file = __DIR__ . '/../uploads/intro' . (int) $number . '.txt';
	if ( ! is_file( $file ) ) {
		return null;
	}
	$text  = str_replace( array( "\r\n", "\r" ), "\n", (string) file_get_contents( $file ) );
	$parts = explode( "\n", $text, 2 );
	return array(
		'title'   => trim( $parts[0] ),
		'content' => cota_parse_intro_content( $parts[1] ?? '' ),
	);
}
