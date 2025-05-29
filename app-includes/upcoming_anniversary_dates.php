<?php
/**
 * Get upcoming anniversary dates within the next 15 days.
 */
require_once '../app-includes/database_functions.php';

function getLastName($family_id, $conn) {
    $family = $conn->query("SELECT familyname FROM families WHERE id = " . intval($family_id))->fetch_assoc();

    return $family['familyname'] ?? 'Unknown';
}

function getUpcomingAnniversaries() {
    $db = new Database();
    $conn = $db->getConnection();

    $today = new DateTime();
    $end = (clone $today)->modify('+15 days');
    $currentYear = $today->format('Y');

    $results = [
        'Marriage Anniversaries' => [],
        'Birthdays' => [],
        'Baptisms' => [],
    ];

    // Helper to check if MM/DD is in the next 14 days
    function isUpcoming($mmdd, $today, $end) {
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
    $families = $conn->query("SELECT familyname, name1, name2, annday FROM families WHERE annday IS NOT NULL AND annday != ''");
    while ($fam = $families->fetch_assoc()) {
        if (isUpcoming($fam['annday'], $today, $end)) {
            $names = trim($fam['name1'] . ' & ' . $fam['name2'], ' &');
            $results['Marriage Anniversaries'][] = "{$fam['annday']} - {$names} {$fam['familyname']}";
        }
    }

    // 2. Birthdays (members table)
    $members = $conn->query("SELECT family_id, first_name, last_name, birthday FROM members WHERE birthday IS NOT NULL AND birthday != ''");
    while ($mem = $members->fetch_assoc()) {
        if (isUpcoming($mem['birthday'], $today, $end)) {
            if ( $mem['last_name'] === '' ) {
                $mem['last_name'] = getLastName($mem['family_id'], $conn);
            }
            $results['Birthdays'][] = "{$mem['birthday']} - {$mem['first_name']} {$mem['last_name']}";
        }
    }

    // 3. Baptisms (members table)
    $members = $conn->query("SELECT family_id, first_name, last_name, baptism FROM members WHERE baptism IS NOT NULL AND baptism != ''");
    while ($mem = $members->fetch_assoc()) {
        if (isUpcoming($mem['baptism'], $today, $end)) {
            if ( $mem['last_name'] === '' ) {
                $mem['last_name'] = getLastName($mem['family_id'], $conn);
            }
            $results['Baptisms'][] = " {$mem['baptism']} - {$mem['first_name']} {$mem['last_name']}";
        }
    }

    $conn->close();
    return $results;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import CSV Data</title>
    <link rel="stylesheet" href="../app-assets/css/styles.css">
</head>
<body>
    <h2>Upcoming Anniversaries</h2>
    <ul>    
        <?php
        if (function_exists('getUpcomingAnniversaries')) {
            $upcoming_anniversaries = getUpcomingAnniversaries();
            foreach ($upcoming_anniversaries as $category => $anniversaries) {
                echo "<strong>" . htmlspecialchars($category) . "</strong>";
                echo "<ul>";
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
            echo "<li>Error: Function 'getUpcomingAnniversaries' is not defined.</li>";
        }
        ?>
    </ul>


    <p><a href='index.php'>Return to main menu</a></p>
</body>
</html>