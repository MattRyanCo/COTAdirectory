<?php
/**
 * Get upcoming anniversary dates within the next $look_forward days.
 */
require_once __DIR__ . '/bootstrap.php';
global $cota_db, $connect,  $cota_app_settings;
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

function cota_get_last_name($family_id) {
    global $cota_db, $connect;
    $family = $connect->query("SELECT familyname FROM families WHERE id = " . intval($family_id))->fetch_assoc();

    return $family['familyname'] ?? 'Unknown';
}

function get_anniversary_members($family_id) {
    global $cota_db, $connect;
    $members = [];
    $result = $connect->query("SELECT first_name, last_name FROM members WHERE family_id = " . intval($family_id));
    while ($row = $result->fetch_assoc()) {
        $members[] = trim($row['first_name'] . ' ' . $row['last_name']);
  }
    return implode(' & ', $members);
}

// $from_date format = '2024-06-14';
// Returns DateTime object of next Sunday from given date (or today if null)
function cota_get_next_sunday_date($from_date = null) {
    // If no date is provided, use today
    $date = $from_date ? new DateTime($from_date) : new DateTime();
    $day_of_week = $date->format('w'); // 0 (Sunday) to 6 (Saturday)
    if ($day_of_week != 0) {
        // Add days to get to next Sunday
        $days_to_add = 7 - $day_of_week;
        $date->modify("+$days_to_add days");
    }
    return $date;
}

function cota_get_upcoming_anniversaries( $look_forward ) {
    global $cota_db, $connect;

    $upcoming_sunday = cota_get_next_sunday_date();
    $end = (clone $upcoming_sunday)->modify("+$look_forward days");
    $current_year = $upcoming_sunday->format('Y');
    $today = $upcoming_sunday;
    $results = [
        'Marriage Anniversaries' => [],
        'Birthdays' => [],
        'Baptisms' => [],
    ];

    // Robust helper to check whether a stored date (various formats) occurs
    // between $today and $end. Accepts formats: YYYY-MM-DD, MM/DD/YYYY, MM/DD.
    $is_upcoming = function($date_to_check, DateTime $today, DateTime $end) {
        if (empty($date_to_check)) return false;

        $month = null;
        $day = null;

        // YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to_check)) {
            $src = DateTime::createFromFormat('Y-m-d', $date_to_check);
            if (!$src) return false;
            $month = $src->format('m');
            $day = $src->format('d');
        }
        // MM/DD/YYYY
        elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date_to_check)) {
            $src = DateTime::createFromFormat('m/d/Y', $date_to_check);
            if (!$src) return false;
            $month = $src->format('m');
            $day = $src->format('d');
        }
        // MM/DD
        elseif (preg_match('/^\d{2}\/\d{2}$/', $date_to_check)) {
            list($month, $day) = explode('/', $date_to_check);
        } else {
            return false;
        }

        // Normalize times for comparison
        $today_clone = (clone $today)->setTime(0,0,0);
        $end_clone = (clone $end)->setTime(23,59,59);

        // Build anniversary date in current year
        $anniv_str = $today_clone->format('Y') . "-{$month}-{$day}";
        $anniv = DateTime::createFromFormat('Y-m-d', $anniv_str);
        if (!$anniv) return false;
        $anniv->setTime(0,0,0);

        // If already passed this year, use next year
        if ($anniv < $today_clone) {
            $anniv->modify('+1 year');
        }

        return $anniv >= $today_clone && $anniv <= $end_clone;
    };



    // 1. Marriage Anniversaries (families table)
    $members = $connect->query("SELECT family_id, first_name, last_name, anniversary FROM members WHERE anniversary IS NOT NULL");
    while ( $mem = $members->fetch_assoc() ) {
        if ($is_upcoming($mem['anniversary'], $today, $end)) {
            $names = get_anniversary_members($mem['family_id']);
            $familyname = cota_get_last_name($mem['family_id']);
            $results['Marriage Anniversaries'][] = "{$mem['anniversary']} - {$names} ({$familyname})";
        }
    }
    // 2. Birthdays (members table)
    $members = $connect->query("SELECT family_id, first_name, last_name, birthday FROM members WHERE birthday IS NOT NULL");
    while ($mem = $members->fetch_assoc()) {
        if ($is_upcoming($mem['birthday'], $today, $end)) {
            if ($mem['last_name'] === '') {
                $mem['last_name'] = cota_get_last_name($mem['family_id']);
            }
            $results['Birthdays'][] = "{$mem['birthday']} - {$mem['first_name']} {$mem['last_name']}";
        }
    }

    // 3. Baptisms (members table)
    $members = $connect->query("SELECT family_id, first_name, last_name, baptism FROM members WHERE baptism IS NOT NULL");
    while ($mem = $members->fetch_assoc()) {
        if ($is_upcoming($mem['baptism'], $today, $end)) {
            if ($mem['last_name'] === '') {
                $mem['last_name'] = cota_get_last_name($mem['family_id']);
            }
            $results['Baptisms'][] = "{$mem['baptism']} - {$mem['first_name']} {$mem['last_name']}";
        }
    }

    return $results;
}

// Echo page header
echo cota_page_header();
$families = $cota_db->read_family_database();
$num_families = $families->num_rows;
if ( 0 === $num_families ) {
	empty_database_alert( 'Display Anniversaries' );
    exit();
} 
$look_forward = 7;
// Dump out remainder of import page. 
?>
    <div id="cota-anniversary" class="container">
    <h2>Upcoming Anniversaries</h2>

    <p><?php echo 'Effective: ' . $look_forward . ' days from ' . cota_get_next_sunday_date()->format('m/d'); ?></p>
    <ul class='cota-anniversary-list'>
        <?php
        $upcoming_anniversaries = cota_get_upcoming_anniversaries( $look_forward );
        foreach ($upcoming_anniversaries as $category => $anniversaries) {
            echo "<strong>" . htmlspecialchars($category) . "</strong>";
            echo "<ul class='cota-anniversary-sublist'>";
            if (empty($anniversaries)) {
                echo '<li>No upcoming anniversaries on record.</li>';
            } else {
                foreach ($anniversaries as $anniversary) {
                    echo '<li>' . htmlspecialchars($anniversary) . '</li>';
                }
            }
            echo '</ul>';
        }
        ?>
    </ul>
    </div>
</body>
</html>