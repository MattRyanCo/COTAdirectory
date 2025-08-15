<?php
/**
 * Function to convert downloaded CSV from Google Form entries to a format useable 
 *   by Directgory app.This will involve renaming column headings and possibly 
 *   performaing some type of mapping from the Google CSV to the DirectoryCSV. 
 */

require_once __DIR__ . '/bootstrap.php';
global $cota_db, $connect,  $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';

// Echo header
echo cota_page_header();
// Handle file upload form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['google_csv'])) {
    $file = $_FILES['google_csv']['tmp_name'];
    if (($handle = fopen($file, 'r')) !== false) {
        $header = fgetcsv($handle);
        // Check if first column is 'Timestamp'
        if (isset($header[0]) && strtolower(trim($header[0])) === 'timestamp') {
            // Map Google CSV columns to Directory CSV columns
            $mapping = [
                // 'Google Column Name' => 'Directory Column Name'
                // 'Timestamp' => 'Date',
                'Family Name (Last name)' => 'familyname',
                'First Name - 1st Person' => 'name1',
                'Cell Phone - 1st Person' => 'cellphone1',
                'Email - 1st Person' => 'email1',
                'Anniversary of Birth - 1st Person' => 'bday1',
                'Anniversary of Baptism - 1st Person' => 'bap1',
                'First Name - 2nd Person' => 'name2',
                'Cell Phone - 2nd Person' => 'cellphone2',
                'Email - 2nd Person' => 'email2',
                'Anniversary of Birth - 2st Person' => 'bday2',
                'Anniversary of Baptism - 2st Person' => 'bap2',
                'Anniversary of Marriage - Persons 1 & 2 ' => 'annday',
                // Add more mappings as needed
            ];
            // Build new header
            $newHeader = [];
            foreach ($header as $col) {
                $col = trim($col);
                $newHeader[] = $mapping[$col] ?? $col;
            }
            $rows = [];
            while (($data = fgetcsv($handle)) !== false) {
                $row = [];
                foreach ($data as $i => $value) {
                    $row[$newHeader[$i]] = $value;
                }
                $rows[] = $row;
            }
            fclose($handle);

            // Output or process $rows as needed for import.php
            echo '<h3>Converted Data Preview</h3>';
            echo '<table border="1"><tr>';
            foreach ($newHeader as $col) {
                echo '<th>' . htmlspecialchars($col) . '</th>';
            }
            echo '</tr>';
            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($newHeader as $col) {
                    echo '<td>' . htmlspecialchars($row[$col] ?? '') . '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
            // You can save $rows to a file or pass to import.php as needed
        } else {
            echo '<div style="color:red;">Error: The uploaded file does not appear to be a Google Form CSV (missing Timestamp column).</div>';
        }
    } else {
        echo '<div style="color:red;">Error: Unable to open uploaded file.</div>';
    }
}

// File upload form
?>
<form method="post" enctype="multipart/form-data">
    <label for="google_csv">Select Google CSV file:</label>
    <input type="file" name="google_csv" id="google_csv" accept=".csv" required>
    <button type="submit">Convert</button>
</form>