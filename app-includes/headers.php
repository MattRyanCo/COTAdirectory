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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="/app-assets/css/styles.css">
</head>
' . cota_add_analytics() . '
<body>
	<script src="/app-assets/js/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="/app-assets/js/clicktoggle.js"></script>
	<div id="main-header" class="container py-3">
	<div id="pre-header" class="cota-pre-header mb-3">
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
	<div class="text-center mb-3">
		<h1 class="h2 mb-2">Church of the Ascension, Parkesburg</h1>
		<h2 class="h4 mb-0"><a href="/" class="text-decoration-none">Family Directory Management</a></h2>
	</div>

	<nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm border">
		<div class="container-fluid">
			<a class="navbar-brand d-lg-none fw-bold" href="/">Menu</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cotaMainNav" aria-controls="cotaMainNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="cotaMainNav">
				<ul class="navbar-nav w-100 justify-content-lg-between">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Main Menu</a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="/app-includes/display.php" target="_blank">Display Directory</a></li>
							<li><a class="dropdown-item" href="/app-includes/display-family.php">Display One Family</a></li>
							<li><a class="dropdown-item" href="/app-includes/add-family-form.php">Add Family</a></li>
							<li><a class="dropdown-item" href="/app-includes/search-edit.php">Edit Family / Family Member(s)</a></li>
							<li><a class="dropdown-item" href="/app-includes/search-delete.php">Delete Family / Family Member(s)</a></li>
							<li><a class="dropdown-item" href="/app-includes/upcoming-anniversary-dates.php" target="_blank">Upcoming Anniversaries</a></li>
						</ul>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Ministry Maintenance</a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="/app-includes/display-vestry.php">Display Vestry</a></li>
							<li><a class="dropdown-item" href="/app-includes/update-vestry.php">Update Vestry</a></li>
							<li><a class="dropdown-item" href="/app-includes/display-leadership.php">Display Leadership</a></li>
							<li><a class="dropdown-item" href="/app-includes/update-leadership.php">Update Leadership</a></li>
							<li><a class="dropdown-item" href="/app-includes/display-staff.php">Display Staff</a></li>
							<li><a class="dropdown-item" href="/app-includes/update-staff.php">Update Staff</a></li>
						</ul>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Utilities</a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="/app-includes/import.php">Import CSV Data</a></li>
							<li><a class="dropdown-item" href="/app-includes/export.php">Export CSV Directory</a></li>
							<li><a class="dropdown-item" href="/app-includes/export-sample.php" target="_blank">Export Sample CSV</a></li>
							<li><a class="dropdown-item" href="/app-includes/export-email-addresses.php">Export Email Address list (CSV)</a></li>
							<li><a class="dropdown-item" href="/app-includes/database-details.php">Database Details</a></li>
							<li><a class="dropdown-item" href="/app-includes/database-dump.php">Dump Database</a></li>
							<li><a class="dropdown-item" href="/app-includes/intro-files-display.php">Intro Files Display</a></li>
							<li><a class="dropdown-item" href="/app-includes/intro-files-update.php">Intro Files Update</a></li>
							<li><a class="dropdown-item text-danger" href="/app-includes/reset-db.php">⚠️ Reset Database ⚠️</a></li>
						</ul>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Print Options</a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="../app-includes/print-booklet-rtf.php">RTF for External Use</a></li>
							<li><a class="dropdown-item" href="../app-includes/print-booklet-pdf.php">PDF for Booklet Printing</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
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