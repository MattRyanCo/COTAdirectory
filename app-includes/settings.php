<?php
/**
 * Used to set up and fix common variables and include
 * the app procedural and class library.
 *
 */

if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) ) {
    define( 'WP_ENVIRONMENT_TYPE', 'laragon' );
}
// if ( 'laragon' !== WP_ENVIRONMENT_TYPE ) { // Define Gridpane settings
    // define( 'DB_NAME', 'cotadirectory' );
    // define( 'DB_USER', 'cotadirectory' );
    // define( 'DB_PASSWORD', 'xo=BqIGmfJxc!+V7LNe97K9^V4p?86Lq' );
    // // define( 'DB_HOST', 'localhost:/var/run/mysqld/mysqld.sock' );
    // define( 'DB_HOST', '64.176.198.28:3306' );
// } else {
    // Define Laragon (local) settings
    define( 'DB_NAME', 'cotadirectory' );
    define( 'DB_USER', 'root' );
    define( 'DB_PASSWORD', '' );
    define( 'DB_HOST', 'Localhost' );
// }
class Constants {
    const MAX_FAMILY_MEMBERS = 10; // Maximum number of family members
}


function cota_page_header() {
	return '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>COTA Family Directory Management</title>
<meta name="application-name" content="COTA Family Directory Management">
<link rel="icon" type="image/x-icon" href="/app-assets/images/favicon-white.ico">
<meta name="msapplication-TileColor" content="#ffc40d">
<meta name="theme-color" content="#ffffff">
<link rel="stylesheet" type="text/css" href="/app-assets/css/all_style.css.php" media="all">
<link rel="stylesheet" href="/app-assets/css/styles.css">
</head>
<body>
	<script src="/app-assets/js/jquery.min.js"></script>
	<script src="/app-assets/js/flexdropdown.min.js"></script>
	<div id="main-header" class="container">
	<h1>Church of the Ascension, Parkesburg</h1>
	<h2>Family Directory Management</h2>
	<nav id="main-menu" class="primary" >
	<ul>
        <li class="left"><a href="/">Home</a></li>
		<li class="left"><a href="#" id="" data-flexmenu="drop_main" data-dir="v" class="down">Main Menu</a></li>
		<li class="left"><a href="#" id="" data-flexmenu="drop_utilities" data-dir="v" class="down">Utilities</a></li>
		<li class="left"><a href="#" id="" data-flexmenu="drop_print" data-dir="v" class="down">Print Options</a></li>
		<li class="left"><a href="#" id="" data-flexmenu="drop_shared" data-dir="v" class="down">Shared Drive [Google]</a></li>
	</ul>
	<ul id="drop_main" class="flexdropdownmenu">
		<li class="left"><a href="/app-includes/display.php" target="_blank">Display Directory</a></li>
		<li class="left"><a href="/app-includes/add-family-form.php" target="_blank">Add New Family</a></li>
		<li class="left"><a href="/app-includes/search-edit.php" target="_blank">Search & Edit Family</a></li>
		<li class="left"><a href="/app-includes/search-delete.php" target="_blank">Delete Family or Family Member</a></li>
		<li class="left"><a href="/app-includes/upcoming-anniversary-dates.php" target="_blank">Display Upcoming Anniversaries</a></li>
	</ul>
	<ul id="drop_utilities" class="flexdropdownmenu">
		<li class="left"><a href="/app-includes/import.php">Import CSV Data</a></li>
		<li class="left"><a href="/app-includes/export.php">Export Directory as CSV</a></li>
		<li class="left"><a href="/app-includes/export-sample.php" target="_blank">Export Sample Directory as CSV</a></li>
		<li class="left"><a href="/app-includes/database-details.php">Database Details</a></li>
		<li class="left"><a href="/app-includes/reset-db.php" style="color: red;">⚠️ Reset Database ⚠️</a></li>
	</ul>
	<ul id="drop_print" class="flexdropdownmenu">
		<li class="left"><a href="/app-includes/print-booklet-rtf.php">Generate Directory RTF - Download</a></li>
		<li class="left"><a href="/app-includes/print-booklet-pdf.php" target="_blank">Generate Directory Booklet PDF</a></li>
		<li class="left"><a href="/app-includes/build-booklet-sample.php" target="_blank">Build Sample Booklet</a></li>
	</ul>
	<ul id="drop_shared" class="flexdropdownmenu">
		<li class="left"><a href="https://docs.google.com/forms/d/e/1FAIpQLSd9ZMiaeO6btCJo2BQ7lgBlOYsJwNBC4aPLRYdr4m90pwN7wA/viewform?usp=header" target="_blank">Google Form Based Family Entry</a></li>
		<li class="left"><a href="https://docs.google.com/spreadsheets/d/1anupShYGmySUjrA16yGC5HQ3uucfi6HdMm-CujOqHxc/edit?usp=sharing" target="_blank">Google Form Sheet</a></li>
	</ul>
	</nav>
	</div>
	<div class="notice-container"></div>
	<div class="form-container"></div>
';
}