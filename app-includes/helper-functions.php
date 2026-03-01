<?php

/**
 * Helper functions.
 *
 */
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
                <input type="date" id="members[baptism]" name="members[baptism][]" placeholder="mm/dd"><br>
				<label for="members[anniversary][]">Marrage Anniversary</label>
                <input type="date" id="members[anniversary]" name="members[anniversary][]" placeholder="mm/dd"><br><br><br>
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
	echo 'Use <a href="/app-includes/import.php">Import CSV Data</a> or <a href="/app-includes/add-family-form.php">Add New Family</a> to add data to database.</div>';
	echo '</div>';
}


/**
 * Convert Markdown to HTML.
 *
 * Uses Parsedown if it's available via Composer. Falls back to a
 * lightweight, safe converter when Parsedown is not installed.
 */
function cota_markdown_to_html( $markdown ) {
	if ( empty( $markdown ) ) {
		return '';
	}

	// Prefer a full Markdown library if present
	if ( class_exists( 'Parsedown' ) ) {
		$pd = new Parsedown();
		// Keep raw HTML disabled by default for safety
		if ( method_exists( $pd, 'setSafeMode' ) ) {
			$pd->setSafeMode( true );
		}
		return $pd->text( $markdown );
	}

	// Lightweight fallback: escape, then apply common markdown rules.
	$text = htmlspecialchars( $markdown, ENT_QUOTES, 'UTF-8' );

	// Code blocks ```code```
	$text = preg_replace_callback('/```(.*?)```/s', function( $m ) {
		return '<pre><code>' . htmlspecialchars( $m[1], ENT_QUOTES, 'UTF-8' ) . '</code></pre>';
	}, $text );

	// ATX headings
	$text = preg_replace('/^###\s*(.+)$/m', '<h3>$1</h3>', $text);
	$text = preg_replace('/^##\s*(.+)$/m', '<h2>$1</h2>', $text);
	$text = preg_replace('/^#\s*(.+)$/m', '<h1>$1</h1>', $text);

	// Bold **text** and Italic *text* (simple)
	$text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
	$text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);

	// Links [text](url)
	$text = preg_replace_callback('/\[(.*?)\]\((.*?)\)/', function( $m ) {
		$label = htmlspecialchars( $m[1], ENT_QUOTES, 'UTF-8' );
		$url = htmlspecialchars( $m[2], ENT_QUOTES, 'UTF-8' );
		return '<a href="' . $url . '">' . $label . '</a>';
	}, $text );

	// Unordered lists: consecutive lines starting with - or *
	$text = preg_replace_callback('/(?:^\s*[-\*]\s+.+(?:\r?\n|$))+/', function( $m ) {
		$lines = preg_split('/\r?\n/', trim( $m[0] ));
		$out = "<ul>";
		foreach ( $lines as $line ) {
			$item = preg_replace('/^\s*[-\*]\s+/', '', $line );
			$out .= '<li>' . $item . '</li>';
		}
		$out .= "</ul>";
		return $out;
	}, $text );

	// Convert paragraphs (double newline separates paragraphs)
	$parts = preg_split('/\r?\n\r?\n/', $text);
	$out = '';
	foreach ( $parts as $p ) {
		$p = trim( $p );
		if ( $p === '' ) {
			continue;
		}
		// If already a block-level element, don't wrap in <p>
		if ( preg_match('/^<(h[1-6]|ul|pre|blockquote|ol|li|table)/', $p) ) {
			$out .= $p;
		} else {
			// Preserve single line breaks inside paragraphs
			$p = nl2br( $p );
			$out .= '<p>' . $p . '</p>';
		}
	}

	return $out;
}


/**
 * Convert Markdown to plain text, preserving paragraph and line breaks.
 *
 * This returns readable plain text suitable for printing (keeps newlines).
 */
function cota_markdown_to_plaintext( $markdown ) {
	if ( empty( $markdown ) ) {
		return '';
	}

	// Get HTML from Parsedown if available, otherwise reuse existing converter
	if ( class_exists( 'Parsedown' ) ) {
		$pd = new Parsedown();
		if ( method_exists( $pd, 'setSafeMode' ) ) {
			$pd->setSafeMode( true );
		}
		$html = $pd->text( $markdown );
	} else {
		$html = cota_markdown_to_html( $markdown );
	}

	// If the markdown produced tables, convert them to tab-separated plain text
	if ( class_exists( 'DOMDocument' ) ) {
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		// Ensure proper UTF-8 handling
		$dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
		$tables = $dom->getElementsByTagName('table');
		// Iterate backwards so replacements don't affect upcoming nodes
		for ( $ti = $tables->length - 1; $ti >= 0; $ti-- ) {
			$table = $tables->item( $ti );
			$rows = $table->getElementsByTagName('tr');
			$table_rows = array();
			foreach ( $rows as $row ) {
				$cells = array();
				foreach ( $row->childNodes as $cell ) {
					if ( in_array( strtolower( $cell->nodeName ), array( 'th', 'td' ), true ) ) {
						$cells[] = trim( preg_replace('/\s+/', ' ', $cell->textContent) );
					}
				}
				if ( ! empty( $cells ) ) {
					$table_rows[] = $cells;
				}
			}

			// Compute column widths
			$col_widths = array();
			foreach ( $table_rows as $r ) {
				foreach ( $r as $i => $cell ) {
					$len = mb_strlen( $cell, 'UTF-8' );
					if ( ! isset( $col_widths[ $i ] ) || $len > $col_widths[ $i ] ) {
						$col_widths[ $i ] = $len;
					}
				}
			}

			// Build padded text table using spaces for alignment
			$textTable = '';
			foreach ( $table_rows as $r ) {
				$padded = array();
				$cols = count( $col_widths );
				for ( $i = 0; $i < $cols; $i++ ) {
					$cell = isset( $r[ $i ] ) ? $r[ $i ] : '';
					// Add one space padding between columns
					$padded[] = str_pad( $cell, $col_widths[ $i ] );
				}
				// Use two spaces between columns to improve readability
				$textTable .= implode('  ', $padded ) . "\n";
			}

			// Replace the table HTML with a <pre> block containing the plain table text
			$tableHtml = $dom->saveHTML( $table );
			$replacement = '<pre class="cota-table-plaintext">' . htmlspecialchars( $textTable, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) . '</pre>';
			$html = str_replace( $tableHtml, $replacement, $html );
		}
		libxml_clear_errors();
	}

	// Replace common block tags with newlines to preserve spacing when stripped
	$break_tags = array(
		'#<(br)\s*/?>#i',
		'#</p>#i',
		'#</h[1-6]>#i',
		'#</div>#i',
		'#</li>#i',
		'#</tr>#i',
		'#</td>#i',
		'#</blockquote>#i'
	);
	// $html = preg_replace( $break_tags, "\n", $html );

	// Convert remaining tags to nothing, decode entities
	$text = strip_tags( $html );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	// Normalize newlines and trim excessive blank lines
	$text = str_replace( "\r", "\n", $text );
	$text = preg_replace('/\n{3,}/', "\n\n", $text);
	$lines = explode("\n", $text);
	// Trim each line but preserve single blank lines
	foreach ( $lines as &$ln ) {
		$ln = rtrim( $ln );
	}
	$text = implode("\n", $lines);

	// Trim leading/trailing whitespace/newlines
	$text = trim( $text, "\n\t \0\x0B" );

	return $text;
}

/**
 * Look up families by last name with optional address filters.
 * Falls back to returning nearby names when nothing matches exactly.
 *
 * @param mysqli      $connect    Active database connection.
 * @param string      $familyname Family name to search for.
 * @param string|null $address    Optional address filter.
 * @param string|null $address2   Optional second address filter.
 *
 * @return array{matches: array<int, array>, fuzzy: array<int, array>}
 */
function cota_search_families( $connect, $familyname, $address = null, $address2 = null ) {
	$familyname = trim( (string) $familyname );
	$address    = trim( (string) ( $address ?? '' ) );
	$address2   = trim( (string) ( $address2 ?? '' ) );

	if ( $familyname === '' || ! $connect ) {
		return array( 'matches' => array(), 'fuzzy' => array() );
	}

	$matches = array();

	if ( '' === $address && '' === $address2 ) {
		$stmt = $connect->prepare( 'SELECT * FROM families WHERE familyname = ? ORDER BY familyname' );
		$stmt->bind_param( 's', $familyname );
	} elseif ( '' !== $address && '' === $address2 ) {
		$addresslike = '%' . $address . '%';
		$stmt        = $connect->prepare( 'SELECT * FROM families WHERE familyname = ? AND address LIKE ? ORDER BY familyname' );
		$stmt->bind_param( 'ss', $familyname, $addresslike );
	} elseif ( '' === $address && '' !== $address2 ) {
		$address2like = '%' . $address2 . '%';
		$stmt         = $connect->prepare( 'SELECT * FROM families WHERE familyname = ? AND address2 LIKE ? ORDER BY familyname' );
		$stmt->bind_param( 'ss', $familyname, $address2like );
	} else {
		$addresslike  = '%' . $address . '%';
		$address2like = '%' . $address2 . '%';
		$stmt         = $connect->prepare( 'SELECT * FROM families WHERE familyname = ? AND ( address LIKE ? OR address2 LIKE ? ) ORDER BY familyname' );
		$stmt->bind_param( 'sss', $familyname, $addresslike, $address2like );
	}

	if ( $stmt && $stmt->execute() ) {
		$result = $stmt->get_result();
		while ( $result && ( $row = $result->fetch_assoc() ) ) {
			$matches[] = $row;
		}
	}

	if ( $stmt ) {
		$stmt->close();
	}

	return array(
		'matches' => $matches,
		'fuzzy'   => empty( $matches ) ? cota_fetch_neighboring_families( $connect, $familyname, 2 ) : array()
	);
}

/**
 * Fetch up to $limit families before and after a given name alphabetically.
 */
function cota_fetch_neighboring_families( $connect, $familyname, $limit = 2 ) {
	$neighbors = array();

	if ( ! $connect || $limit <= 0 ) {
		return $neighbors;
	}

	$familyname = trim( (string) $familyname );

	$before = array();
	$stmt   = $connect->prepare( 'SELECT * FROM families WHERE familyname < ? ORDER BY familyname DESC LIMIT ?' );
	if ( $stmt ) {
		$stmt->bind_param( 'si', $familyname, $limit );
		if ( $stmt->execute() ) {
			$result = $stmt->get_result();
			while ( $result && ( $row = $result->fetch_assoc() ) ) {
				$before[] = $row;
			}
		}
		$stmt->close();
	}

	$after = array();
	$stmt  = $connect->prepare( 'SELECT * FROM families WHERE familyname > ? ORDER BY familyname ASC LIMIT ?' );
	if ( $stmt ) {
		$stmt->bind_param( 'si', $familyname, $limit );
		if ( $stmt->execute() ) {
			$result = $stmt->get_result();
			while ( $result && ( $row = $result->fetch_assoc() ) ) {
				$after[] = $row;
			}
		}
		$stmt->close();
	}

	if ( ! empty( $before ) ) {
		$before = array_reverse( $before );
	}

	return array_merge( $before, $after );
}

/**
 * Build an HTML list of suggestion links for families.
 */
function cota_render_family_suggestions( $families, $targetScript ) {
	if ( empty( $families ) || empty( $targetScript ) ) {
		return '';
	}

	$list = '<ul class="cota-family-suggestions">';
	foreach ( $families as $family ) {
		$query = array( 'familyname' => $family['familyname'] );
		if ( ! empty( $family['address'] ) ) {
			$query['address'] = $family['address'];
		}
		if ( ! empty( $family['address2'] ) ) {
			$query['address2'] = $family['address2'];
		}
		$url = $targetScript . '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
		$name = htmlspecialchars( $family['familyname'], ENT_QUOTES );
		$detailsParts = array_filter(
			array(
				$family['address'] ?? '',
				$family['city'] ?? '',
				$family['state'] ?? '',
				$family['zip'] ?? ''
			)
		);
		$details = '';
		if ( ! empty( $detailsParts ) ) {
			$details = ' <span class="family-suggestion-meta">(' . htmlspecialchars( implode( ', ', $detailsParts ), ENT_QUOTES ) . ')</span>';
		}
		$list .= '<li><a href="' . htmlspecialchars( $url, ENT_QUOTES ) . '">' . $name . '</a>' . $details . '</li>';
	}
	$list .= '</ul>';

	return $list;
}
