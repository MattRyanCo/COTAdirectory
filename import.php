<?php
require_once 'database_functions.php';

class CSVImporter {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function import($filename) {
        if (!file_exists($filename)) {
            die("Error: CSV file not found.");
        }

        $file = fopen($filename, "r");
        if (!$file) {
            die("Error: Failed to open CSV file.");
        }

        fgetcsv($file); // Skip header

        while (($row = fgetcsv($file)) !== false) {
            // var_dump($row); // Debugging output

            if ($this->validateRow($row)) {
                $data = $this->explodeRow($row); // Explode the row into a keyed array
                $this->insertFamily($data); // Insert family data into the database
                // Uncomment the next line to stop after the first row for testing  
            } else {
                $this->logError("Invalid data row: " . implode(", ", $row));
            }
            // Uncomment the next line to stop after the first row for testing  
        }

        fclose($file);
        echo "<p style='color:green;'>CSV imported successfully!</p>";
        }

    private function explodeRow($row) {
        // This function explodes the input row into a keyed array for readability.

        $data = [];
        // Assuming the CSV columns are in the following order:
        $data = [
            'family_name' => $row[0],
            'primary_name_1' => $row[1],
            'primary_name_2' => $row[2],
            'address' => $row[3],
            'address_2' => $row[4],
            'city' => $row[5],
            'state' => $row[6],
            'zip' => $row[7],
            'home_phone' => $row[8],
            'primary_cell_1' => $row[9],
            'primary_cell_2' => $row[10],
            'primary_email_1' => $row[11],
            'primary_email_2' => $row[12],
            'primary_bday_1' => $this->formatDate($row[13]), // MM/DD format
            'primary_bday_2' => $this->formatDate($row[14]),
            'anniversary' => $this->formatDate($row[15])
        ];

        // Add other family members (e.g., children, live-in parents, etc.)
        for ($i = 1; $i <= 5; $i++) {
            $nameIdx = 17 + ($i - 1) * 4;   // Other names begin in column 17
            if (isset($row[$nameIdx])) {
                $data['other_name_' . $i] = $row[$nameIdx];
                $data['other_bday_' . $i] = $this->formatDate($row[$nameIdx + 1]); // MM/DD format
                $data['other_cell_' . $i] = isset($row[$nameIdx + 2]) ? $row[$nameIdx + 2] : '';
                $data['other_email_' . $i] = isset($row[$nameIdx + 3]) ? $row[$nameIdx + 3] : '';
            }
        }

        return $data;
    }


    private function validateRow($row) {
        return !empty($row[0]) && !empty($row[1]); // Ensuring Family Name and at least one Member Name exist
    }

    private function formatDate($date) {
        if (empty($date)) {
            return ""; // Return empty if date is not provided
        }
        // Check if the date is in MM/DD format and normalize it        
        if (preg_match('/^(\\d{1,2})\/(\\d{1,2})(?:\/\\d{2,4})?$/', $date, $matches)) {
            return sprintf("%02d/%02d", $matches[1], $matches[2]); // Normalize MM/DD format
        }
    }

    private function insertFamily($data) {
        $stmt = $this->conn->prepare("INSERT INTO families (family_name, primary_name_1, primary_name_2, address, address_2, city, state, zip, home_phone, primary_cell_1, primary_cell_2, primary_email_1, primary_email_2, primary_bday_1, primary_bday_2, anniversary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            $this->logError("SQL Error (prepare): " . $this->conn->error);
            return;
        }

        $stmt->bind_param("ssssssssssssssss", $data['family_name'], $data['primary_name_1'], $data['primary_name_2'], $data['address'], $data['address_2'], $data['city'], $data['state'], $data['zip'], $data['home_phone'], $data['primary_cell_1'], $data['primary_cell_2'], $data['primary_email_1'], $data['primary_email_2'], $data['primary_bday_1'], $data['primary_bday_2'], $data['anniversary']);

        if (!$stmt->execute()) {
            $this->logError("SQL Error (execute): " . $stmt->error);
            return;
        }

        $family_id = $stmt->insert_id;
        $stmt->close();

        $this->insertMembers($family_id, $data);
    }

    // Insert additional family members into the database. Only the Other Name field is required. Bday, cell phone, and email are optional.
    private function insertMembers($family_id, $data) {
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($data['other_name_' . $i])) {
                $stmt = $this->conn->prepare("INSERT INTO members (family_id, first_name, birthday, cell_phone, email ) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $this->logError("SQL Error (prepare): " . $this->conn->error);
                    continue;
                }
                
                $birthday = $this->formatDate($data['other_bday_' . $i]); // Extract MM/DD only
                $stmt->bind_param("issss", $family_id, $data['other_name_' . $i], $data['other_bday_' . $i], $data['other_cell_' . $i], $data['other_email_' . $i]);

                if (!$stmt->execute()) {
                    $this->logError("SQL Error (execute): " . $stmt->error);
                }

                $stmt->close();
            }
        }
    }

    private function logError($message) {
        error_log($message . PHP_EOL, 3, "import_errors.log");
    }
}

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["csv_file"])) {
    $uploadDir = "uploads/";
    $filePath = $uploadDir . basename($_FILES["csv_file"]["name"]);

    if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $filePath)) {
        $importer = new CSVImporter();
        $importer->import($filePath);
    } else {
        echo "<p style='color:red;'>Error: File upload failed.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import CSV Data</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Upload CSV File for Import</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Select CSV File:</label>
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit">Upload & Import</button>
    </form>

    <p><a href='index.php'>Return to main menu</a></p>
</body>
</html>