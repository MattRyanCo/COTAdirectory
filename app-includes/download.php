<?php
/**
 * Download a generated directory file (PDF or RTF booklet).
 *
 * The files in /downloads contain the whole directory, so the web server is
 * told not to serve that folder directly (see .htaccess). This script serves
 * them instead, after bootstrap.php has checked the user is signed in.
 *
 * Usage: /app-includes/download.php?file=directory_booklet_2026-11-05.pdf
 *
 * @package COTAdirectory
 */

require_once __DIR__ . '/bootstrap.php';

$file = basename( (string) ( $_GET['file'] ?? '' ) );

// Only the booklet files this app generates may be downloaded.
if ( ! preg_match( '/^directory_booklet_\d{4}-\d{2}-\d{2}\.(pdf|rtf)$/', $file, $m ) ) {
	http_response_code( 404 );
	exit( 'File not found.' );
}

$path = $_SERVER['DOCUMENT_ROOT'] . '/downloads/' . $file;
if ( ! is_file( $path ) ) {
	http_response_code( 404 );
	exit( 'File not found.' );
}

$content_types = array(
	'pdf' => 'application/pdf',
	'rtf' => 'application/rtf',
);

header( 'Content-Type: ' . $content_types[ $m[1] ] );
header( 'Content-Disposition: attachment; filename="' . $file . '"' );
header( 'Content-Length: ' . filesize( $path ) );
header( 'Cache-Control: private, no-store' );
header( 'X-Content-Type-Options: nosniff' );
readfile( $path );
exit;
