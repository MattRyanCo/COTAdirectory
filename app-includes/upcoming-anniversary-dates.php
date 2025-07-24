<?php
require_once __DIR__ . '/bootstrap.php';
/**
 * Get upcoming anniversary dates within the next 15 days.
 */
function cota_get_last_name( $family_id, $connect ) {
	global $cota_db, $connect;

	$family = $connect->query( 'SELECT familyname FROM families WHERE id = ' . intval( $family_id ) )->fetch_assoc();

	return $family['familyname'] ?? 'Unknown';
}
function cota_get_next_sunday_date( $from_date = null ) {
	// If no date is provided, use today
	$date        = $from_date ? new DateTime( $from_date ) : new DateTime();
	$day_of_week = $date->format( 'w' ); // 0 (Sunday) to 6 (Saturday)
	if ( ! $day_of_week == 0 ) {
		// Add days to get to next Sunday
		$days_to_add = 7 - $day_of_week;
		$date->modify( "+$days_to_add days" );
	}
	return $date;
}

function cota_get_upcoming_anniversaries( $look_forward ) {
	global $cota_db, $connect;

	$upcoming_sunday = cota_get_next_sunday_date();
	$end             = ( clone $upcoming_sunday )->modify( "+$look_forward days" );
	$current_year    = $upcoming_sunday->format( 'Y' );

	$today = $upcoming_sunday;

	$results = array(
		'Marriage Anniversaries' => array(),
		'Birthdays'              => array(),
		'Baptisms'               => array(),
	);

	// Helper to check if MM/DD is in the next x days
	/**
	 * cota_is_upcoming
	 *
	 * @param [string] $mmdd - Date being checked to see if it falls within the period.
	 * @param [DateTime] $today
	 * @param [DateTime] $end
	 * @return void
	 */
	function cota_is_upcoming( $date_string, DateTimeInterface $today, DateTimeInterface $end ) {
		try {
			$date = new DateTimeImmutable( $date_string );
		} catch ( Exception $e ) {
			return false; // Invalid date string
		}

		return $date >= $today && $date <= $end;
	}

	// 1. Marriage Anniversaries (families table)
	// $families = $connect->query( "SELECT familyname, fname1, fname2, annday FROM families WHERE annday IS NOT NULL AND annday != ''" );
	$families = $connect->query( 'SELECT familyname, fname1, fname2, annday FROM families WHERE annday IS NOT NULL' );
	while ( $fam = $families->fetch_assoc() ) {
		if ( cota_is_upcoming( $fam['annday'], $today, $end ) ) {
			$names                               = trim( $fam['fname1'] . ' & ' . $fam['fname2'], ' &' );
			$results['Marriage Anniversaries'][] = "{$fam['annday']} - {$names} {$fam['familyname']}";
		}
	}

	// 2. Birthdays (members table)
	$members = $connect->query( 'SELECT family_id, first_name, last_name, birthday FROM members WHERE birthday IS NOT NULL' );
	while ( $mem = $members->fetch_assoc() ) {
		if ( cota_is_upcoming( $mem['birthday'], $today, $end ) ) {
			if ( $mem['last_name'] === '' ) {
				$mem['last_name'] = cota_get_last_name( $mem['family_id'], $connect );
			}
			$results['Birthdays'][] = "{$mem['birthday']} - {$mem['first_name']} {$mem['last_name']}";
		}
	}

	// 3. Baptisms (members table)
	$members = $connect->query( 'SELECT family_id, first_name, last_name, baptism FROM members WHERE baptism IS NOT NULL' );
	while ( $mem = $members->fetch_assoc() ) {
		if ( cota_is_upcoming( $mem['baptism'], $today, $end ) ) {
			if ( $mem['last_name'] === '' ) {
				$mem['last_name'] = cota_get_last_name( $mem['family_id'], $connect );
			}
			$results['Baptisms'][] = " {$mem['baptism']} - {$mem['first_name']} {$mem['last_name']}";
		}
	}

	$connect->close();
	return $results;
}

// Echo page header
echo cota_page_header();
$families     = $cota_db->read_family_database();
$num_families = $families->num_rows;
if ( 0 == $num_families ) {
	empty_database_alert( 'Display Anniversaries' );
	exit();
}
$look_forward = 7;
// Dump out remainder of import page.
?>
	<div id="cota-anniversary" class="container">
	<h2>Upcoming Anniversaries</h2>

	<p><?php echo 'Effective: ' . $look_forward . ' days from ' . cota_get_next_sunday_date()->format( 'm/d' ); ?></p>
	<ul class='cota-anniversary-list'>
		<?php
		if ( function_exists( 'cota_get_upcoming_anniversaries' ) ) {
			$upcoming_anniversaries = cota_get_upcoming_anniversaries( 14 );
			foreach ( $upcoming_anniversaries as $category => $anniversaries ) {
				echo '<strong>' . htmlspecialchars( $category ) . '</strong>';
				echo "<ul class='cota-anniversary-sublist'>";
				if ( empty( $anniversaries ) ) {
					echo '<li>No upcoming anniversaries on record.</li>';
				} else {
					foreach ( $anniversaries as $anniversary ) {
						echo '<li>' . htmlspecialchars( $anniversary ) . '</li>';
					}
				}
				echo '</ul>';
			}
		} else {
			echo "<li>Error: Function 'cota_get_upcoming_anniversaries' is not defined.</li>";
		}
		?>
	</ul>
	</div>
</body>
</html>
