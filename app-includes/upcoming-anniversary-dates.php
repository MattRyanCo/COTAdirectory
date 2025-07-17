<?php
/**
 * Get upcoming anniversary dates within the next 15 days.
 */

global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';;

function cota_get_last_name($family_id, $conn) {
    $family = $conn->query("SELECT familyname FROM families WHERE id = " . intval($family_id))->fetch_assoc();

    return $family['familyname'] ?? 'Unknown';
}
function cota_get_next_sunday_date($fromDate = null) {
    // If no date is provided, use today
    $date = $fromDate ? new DateTime($fromDate) : new DateTime();
    $dayOfWeek = $date->format('w'); // 0 (Sunday) to 6 (Saturday)
    if (!$dayOfWeek == 0) {
        // Add days to get to next Sunday
        $daysToAdd = 7 - $dayOfWeek;
        $date->modify("+$daysToAdd days");
    }
    return $date;
}

function cota_get_upcoming_anniversaries() {
    require_once '../app-includes/settings.php';
    global $cotadb, $conn;
    // $db = new COTA_Database();
    // $conn = $db->get_connection();

    $upcoming_sunday = cota_get_next_sunday_date();
    $end = (clone $upcoming_sunday)->modify('+15 days');
    $currentYear = $upcoming_sunday->format('Y');

    $today = $upcoming_sunday;

    $results = [
        'Marriage Anniversaries' => [],
        'Birthdays' => [],
        'Baptisms' => [],
    ];

    // Helper to check if MM/DD is in the next 14 days
    function cota_is_upcoming($mmdd, $today, $end) {
        if (!$mmdd || !preg_match('/^\d{2}\/\d{2}$/', $mmdd)) return false;
        $date = DateTime::createFromFormat('m/d/Y', $mmdd . '/' . $today->format('Y'));
        if (!$date) return false;
        // If the anniversary already passed this year, check next year
        if ($date < $today) {
            $date->modify('+1 year');
        }
        return $date >= $today && $date <= $end;
    }



    // 1. Marriage Anniversaries (families table)
    $families = $conn->query("SELECT familyname, fname1, fname2, annday FROM families WHERE annday IS NOT NULL AND annday != ''");
    while ($fam = $families->fetch_assoc()) {
        if (cota_is_upcoming($fam['annday'], $today, $end)) {
            $names = trim($fam['fname1'] . ' & ' . $fam['fname2'], ' &');
            $results['Marriage Anniversaries'][] = "{$fam['annday']} - {$names} {$fam['familyname']}";
        }
    }

    // 2. Birthdays (members table)
    $members = $conn->query("SELECT family_id, first_name, last_name, birthday FROM members WHERE birthday IS NOT NULL AND birthday != ''");
    while ($mem = $members->fetch_assoc()) {
        if (cota_is_upcoming($mem['birthday'], $today, $end)) {
            if ( $mem['last_name'] === '' ) {
                $mem['last_name'] = cota_get_last_name($mem['family_id'], $conn);
            }
            $results['Birthdays'][] = "{$mem['birthday']} - {$mem['first_name']} {$mem['last_name']}";
        }
    }

    // 3. Baptisms (members table)
    $members = $conn->query("SELECT family_id, first_name, last_name, baptism FROM members WHERE baptism IS NOT NULL AND baptism != ''");
    while ($mem = $members->fetch_assoc()) {
        if (cota_is_upcoming($mem['baptism'], $today, $end)) {
            if ( $mem['last_name'] === '' ) {
                $mem['last_name'] = cota_get_last_name($mem['family_id'], $conn);
            }
            $results['Baptisms'][] = " {$mem['baptism']} - {$mem['first_name']} {$mem['last_name']}";
        }
    }

    $conn->close();
    return $results;
}

// Echo page header
echo cota_page_header();
$families = $cotadb->read_family_database();
$num_families = $families->num_rows;
if ( 0 == $num_families ) {
	empty_database_alert('Display Anniversaries');
    exit();
} 

// Dump out remainder of import page. 
?>
    <div id="cota-anniversary" class="container">
    <h2>Upcoming Anniversaries</h2>

    <p><?php echo "Effective: " . cota_get_next_sunday_date()->format('m/d'); ?></p>
    <ul class='cota-anniversary-list'>
        <?php
        if (function_exists('cota_get_upcoming_anniversaries')) {
            $upcoming_anniversaries = cota_get_upcoming_anniversaries();
            foreach ($upcoming_anniversaries as $category => $anniversaries) {
                echo "<strong>" . htmlspecialchars($category) . "</strong>";
                echo "<ul class='cota-anniversary-sublist'>";
                if (empty($anniversaries)) {
                    echo "<li>No upcoming anniversaries on record.</li>";
                } else {
                    foreach ($anniversaries as $anniversary) {
                        echo "<li>" . htmlspecialchars($anniversary) . "</li>";
                    }
                }
                echo "</ul>";
            }
        } else {
            echo "<li>Error: Function 'cota_get_upcoming_anniversaries' is not defined.</li>";
        }
        ?>
    </ul>
    </div>
</body>
</html>