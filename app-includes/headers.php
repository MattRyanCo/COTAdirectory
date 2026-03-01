<?php
/**
 * Header functions.
 *
 */
function cota_page_header( ) {
	global $cota_app_settings, $meta;

	if (!isset($meta) || !is_object($meta)) {
		// Attempt to initialize $meta if not set
		if (file_exists($cota_app_settings->COTA_APP_INCLUDES . 'class-app-meta-data.php')) {
			require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-app-meta-data.php';
			if (class_exists('App_Meta_Data')) {
				$meta_file = $cota_app_settings->COTA_APP_FILE ?? '../index.php';
				$meta = new App_Meta_Data($meta_file);
			}
		}
	}
	if (!isset($meta) || !is_object($meta)) {
		$app_version = 'unknown';
		$app_github_url = '#';
	} else {
		$app_version = $meta->getVersion();
		$app_github_url = $meta->getGitHubUrl();
	}

	$scripts = $cota_app_settings->COTA_APP_ASSETS; 

	return '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>COTA Family Directory Management</title>
<meta name="application-name" content="COTA Family Directory Management">
<link rel="icon" type="image/x-icon" href="/app-assets/images/favicon.ico">
<link rel="stylesheet" href="/app-assets/css/styles.css">
<style>
	.cota-pre-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 1rem;
	}
	.cota-pre-header__brand {
		display: flex;
		align-items: center;
		gap: 0.75rem;
	}
	.cota-pre-header__logo {
		max-height: 64px;
		width: auto;
	}
	.cota-pre-header__meta {
		text-align: right;
		line-height: 1.3;
	}
</style>
</head>
' . cota_add_analytics() . '
<body>
	<script src="/app-assets/js/jquery.min.js"></script>
	<script src="/app-assets/js/clicktoggle.js"></script>
	<div id="main-header" class="container">
	<div id="pre-header" class="cota-pre-header">
		<div class="cota-pre-header__brand">
			<img src="/uploads/directory-app-logo.png" alt="Church of the Ascension logo" class="cota-pre-header__logo">
		</div>
		<div class="cota-pre-header__meta">
			App ' . $app_version . '<br>
			<a href="' . $app_github_url . '" target="_blank">Source</a> | <a href="' . $app_github_url . '/wiki" target="_blank">Wiki</a>' . 
			( ( isset( $GLOBALS['cota_member_auth'] ) && $GLOBALS['cota_member_auth']->is_authenticated() ) 
				? ' | <a href="/app-includes/logout.php">Logout (' . htmlspecialchars( $GLOBALS['cota_member_auth']->get_authenticated_email() ) . ')</a>' 
				: '' ) . '
		</div>
	</div>
	<h1>Church of the Ascension, Parkesburg</h1>
	<h2><a href="/">Family Directory Management</a></h2>

	<nav class="main-menu">
		<ul>
			<li class="has-submenu">
				<a href="#">Main Menu</a>
				<ul class="submenu">
					<li><a href="/app-includes/display.php" target="_blank">Display Directory</a></li>
					<li><a href="/app-includes/display-family.php" >Display One Family</a></li>
					<li><a href="/app-includes/add-family-form.php" >Add Family</a></li>
					<li><a href="/app-includes/search-edit.php" >Edit Family / Family Member(s)</a></li>
					<li><a href="/app-includes/search-delete.php" >Delete Family / Family Member(s)</a></li>
					<li><a href="/app-includes/upcoming-anniversary-dates.php" target="_blank">Upcoming Anniversaries</a></li>
				</ul>
			</li>
			<li class="has-submenu">
				<a href="#">Utilities</a>
				<ul class="submenu">
					<li><a href="/app-includes/import.php">Import CSV Data</a></li>
					<li><a href="/app-includes/export.php">Export CSV Directory</a></li>
					<li><a href="/app-includes/export-sample.php" target="_blank">Export Sample CSV</a></li>
					<li><a href="/app-includes/export-email-addresses.php">Export Email Address list (CSV) </a></li>
					<li><a href="/app-includes/database-details.php">Database Details</a></li> 
					<li><a href="/app-includes/intro-files-display.php">Intro Files Display</a></li> 
					<li><a href="/app-includes/intro-files-update.php">Intro Files Update</a></li>
					<li><a href="/app-includes/reset-db.php" style="color: red;">⚠️ Reset Database ⚠️</a></li>
				</ul>
			</li>
			<li class="has-submenu">
				<a href="#">Print Options</a>
				<ul class="submenu">
					<li><a href="../app-includes/print-booklet-rtf.php">RTF for External Use</a></li>
					<li><a href="../app-includes/print-booklet-pdf.php">PDF for Booklet Printing</a></li>
				</ul>
			</li>
			<li class="has-submenu">
				<a href="#">Cloud Connect</a>
				<ul class="submenu">
 					<li><a href="https://airtable.com/appDcjdkTREcNBq0C/pagWCpvzJva4WXDkN?aLHIC=sfspiZsSOXNCl25vI">COTA Family Entry Dashboard</a></li>
					<li><a href="https://airtable.com/appDcjdkTREcNBq0C/pagKuXDbnoe2YatAq/form">Family Form Entry</a></li>
					<li><a href="../app-includes/form-display.php">FORM: Add Family</a></li>
				</ul>
			</li>
		</ul>
	</nav>

	</div>
	<div class="notice-container"></div>
	<div class="form-container"></div>
';
}

function cota_add_analytics() {
	return '<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-WY4Y6NH0KS"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag(\'js\', new Date());

  gtag(\'config\', \'G-WY4Y6NH0KS\');
</script>';
}