<?php

/**
 * Helper functions.
 *
 */
function cota_page_header() {
	global $cota_constants, $meta;

	if (!isset($meta) || !is_object($meta)) {
		// Attempt to initialize $meta if not set
		if (file_exists($cota_constants->COTA_APP_INCLUDES . 'class-app-meta-data.php')) {
			require_once $cota_constants->COTA_APP_INCLUDES . 'class-app-meta-data.php';
			if (class_exists('AppMetadata')) {
				$meta_file = $cota_constants->COTA_APP_FILE ?? '../index.php';
				$meta = new AppMetadata($meta_file);
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

	$scripts = $cota_constants->COTA_APP_ASSETS; 

	return '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>COTA Family Directory Management</title>
<meta name="application-name" content="COTA Family Directory Management">
<link rel="icon" type="image/x-icon" href="/app-assets/images/favicon.ico">
<!-- <link rel="stylesheet" type="text/css" href="/app-assets/css/all_style.css.php"> -->
<link rel="stylesheet" href="/app-assets/css/styles.css">
</head>
<body>
	<script src="/app-assets/js/jquery.min.js"></script>
	<script src="/app-assets/js/clicktoggle.js"></script>
	<div id="main-header" class="container">
	<div id="pre-header">
		App ' . $app_version . '<br>
		<a href="' . $app_github_url . '" target="_blank">Source</a>  
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
					<li><a href="/app-includes/database-details.php">Database Details</a></li>
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
				<a href="#">Google Connect</a>
				<ul class="submenu">
					<li><a href="https://forms.gle/AriY71y8gvhyNkv77">Google Form Based Family Entry</a></li>
					<li><a href="https://docs.google.com/spreadsheets/d/1anupShYGmySUjrA16yGC5HQ3uucfi6HdMm-CujOqHxc/edit?usp=sharing">Google Form Sheet</a></li>
				</ul>
			</li>
		</ul>
	</nav>

	</div>
	<div class="notice-container"></div>
	<div class="form-container"></div>
';
}

function cota_add_member_script() {
	return '
	    <script>
        function cota_add_member() {
            const membersDiv = document.getElementById("members");
            const memberCount = membersDiv.children.length;

            if (memberCount < 7) {
                const newMember = document.createElement("div");
                newMember.innerHTML = `

                <label >First Name</label>
                <input type="text" name="members[first_name][]" style="text-transform:capitalize;" required>
                <label for="members[last_name][]">Last Name (if different than family name)</label>
                <input type="text" id="members[last_name][]" name="members[last_name][]" style="text-transform:capitalize;"><br>
                <label for="members[cell_phone][]">Cell Phone</label>
                <input type="text" id="members[cell_phone][]" name="members[cell_phone][]" placeholder="xxx-xxx-xxxx" ><br>
                <label for="members[email][]">Email</label>
                <input type="email" id="members[email][]" name="members[email][]"><br>
                <label for="members[birthday][]">Birthday</label>
                <input type="date" id="members[birthday][]" name="members[birthday][]" placeholder="mm/dd"><br>
                <label for="members[baptism][]">Anniversary of Baptism</label>
                <input type="date" id="members[baptism]" name="members[baptism][]" placeholder="mm/dd"><br><br><br>
                `;
                membersDiv.appendChild(newMember);
            } else {
                alert("Maximum of 7 members allowed. Please send us a note if you wish to add additional family members.");
            }
        }
    </script>';
}


// Sanitize Input
function cota_sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES);
}

// Validate email Format
function cota_validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Format MM/DD Date Correctly
function cota_format_date($date) {
	// Convert date to YYYY-MM-DD 
	// If date is empty, return empty string
        if (empty($date)) {
            return null;
        }
		$current_year = date("Y");
        if (preg_match('/^(\\d{1,2})\/(\\d{1,2})(?:\/\\d{2,4})?$/', $date, $matches)) {
			// $datereturned = sprintf("%02d/%02d", $matches[1], $matches[2]);
			$datereturned = sprintf("%s-%02d-%02d", $current_year, $matches[1], $matches[2]);
            return $datereturned;
        }
        return $date;
}

function cota_format_date_to_db($date) {
	// Convert date to YYYY-MM-DD format for database storage. or return null if empty.
	if (empty($date)) {
		return null;
	}
	// $date_parts = explode('/', $date);
	// if (count($date_parts) == 3) {
	// 	return sprintf("%04d-%02d-%02d", $date_parts[2], $date_parts[0], $date_parts[1]);
	// } elseif (count($date_parts) == 2) {
	// 	// If only month and day are provided, leave year blank
	// 	// $datereturned = sprintf("%02d-%02d", $date_parts[0], $date_parts[1] );
	// 	$datereturned = sprintf("%s-%02d-%02d", $current_year, $matches[1], $matches[2]);
	// }
	// This works if the year is included or the column allows partial dates.
	$datereturned = !empty($date) ? "STR_TO_DATE('{$date}', '%m/%d')" : null;
	return $datereturned; 
}


// Validate Date (MM/DD Format)
function cota_validate_date_entry($date) {
    $datereturned = preg_match('/^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])$/', $date);
	return $datereturned ? true : false;
}

// Validate Phone Number format (111-222-3333)
function cota_validate_phone($phone) {
    // Remove spaces
    $phone = str_replace(' ', '', $phone);

    // Check for invalid characters
    if (preg_match('/[^0-9\-\(\)]/', $phone)) {
        return false;
    }

    // Extract digits
    $digits = preg_replace('/[^\d]/', '', $phone);

    // Must be exactly 10 digits
    if (strlen($digits) !== 10) {
        return false;
    }

    // Format as xxx-xxx-xxxx
    return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
}

// Log Errors to a File
function cota_log_error($message) {
    error_log($message . PHP_EOL, 3, "error_log.log");
}

function write_success_notice($msg) {
	$escaped_msg = htmlspecialchars($msg, ENT_QUOTES);
	echo "
	<script>
	(function() {
		var notice = document.querySelector('.notice-container');
		if (notice) {
			notice.innerHTML = '<h3 style=\"color:green;\">' + " . json_encode($escaped_msg) . " + '</h3>';
		} else {
			document.write('<h3 style=\"color:green;\">' + " . json_encode($escaped_msg) . " + '</h3>');
		}
	})();
	</script>
	<noscript><h3 style='color:green;'>$escaped_msg</h3></noscript>
	";
}

function write_error_notice($msg) {
	$escaped_msg = htmlspecialchars($msg, ENT_QUOTES);
	echo "
	<script>
	(function() {
		var notice = document.querySelector('.notice-container');
		if (notice) {
			notice.innerHTML = '<h3 style=\"color:red;\">' + " . json_encode($escaped_msg) . " + '</h3>';
		} else {
			document.write('<h3 style=\"color:red;\">' + " . json_encode($escaped_msg) . " + '</h3>');
		}
	})();
	</script>
	<noscript><h3 style='color:red;'>$escaped_msg</h3></noscript>
	";
}

function cota_handle_error($message, $code) {
    echo "<p style='color: red;'>Error $code: $message</p>";
    exit;
}

function empty_database_alert( $text ) {
	// database has been recently reset, import required
	// Dump out remainder of import page. 
	echo '<div id="empty-notice" class="container">';
	echo '<h3>' . $text . '</h3>';
	echo '<h3 style="color:red; font-weight: 700;"> 0 Families</h3>';
	echo '<h4>Directory Database is Empty</h4>';
	echo '<div id="empty-notice">The directory database has been recently reset.<br>';
	echo 'Use <a href="http://cotadirectory.test/app-includes/import.php">Import CSV Data</a> or <a href="http://cotadirectory.test/app-includes/add-family-form.php">Add New Family</a> to add data to database.</div>';
	echo '</div>';
}
