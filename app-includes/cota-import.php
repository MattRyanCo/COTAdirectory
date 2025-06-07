<?php
/**
 * 
 */
require_once '../app-includes/cota-database-functions.php';

class COTA_Csv_Importer {
    private $conn;

    public function __construct() {
        $db = new COTA_Database();
        $this->conn = $db->get_connection();
    }

    public function cota_read_csv_to_assoc_array($filename) {
        $data = [];
        if (($handle = fopen($filename, "r")) !== false) {
            // Remove BOM from the first line if present
            $firstLine = fgets($handle);
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
            $headers = str_getcsv($firstLine);
            while (($row = fgetcsv($handle)) !== false) {
                // Explicitly convert from Windows-1252 to UTF-8
                foreach ($row as &$value) {
                    // Remove quoted-printable artifacts
                    $value = quoted_printable_decode($value);
                    $value = trim(mb_convert_encoding($value, 'UTF-8', 'UTF-8'));  
                    $value = trim(mb_convert_encoding(quoted_printable_decode($value), 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252'));              
                    $value = $this->cota_fix_mime_artifacts($value); 
                }
                $data[] = array_combine($headers, $row);
                // var_dump($data);
            }
            fclose($handle);
        }
        return $data;
    }

    public function cota_import($filename ) {
        if (!file_exists($filename)) {
            die("Error: CSV file not found.");
        }
        $csvData = $this->cota_read_csv_to_assoc_array($filename);

        foreach ($csvData as $row) {
            // Skip if familyname is missing
            if (empty($row['familyname'])) {
                $this->cota_log_error("Missing familyname in row: " . print_r($row, true));
                continue;
            }

            // Insert family and get ID
            $family_id = $this->cota_insert_family_and_get_id($row);

            // Insert primary members (parents)
            if (!empty($row['name1'])) {
                $this->cota_insert_member($family_id, $row['name1'], '', $row['cellphone1'] ?? '', $row['email1'] ?? '', $row['bday1'] ?? '', $row['bap1'] ?? '');
            }
            if (!empty($row['name2'])) {
                $this->cota_insert_member($family_id, $row['name2'], '', $row['cellphone2'] ?? '', $row['email2'] ?? '', $row['bday2'] ?? '', $row['bap2'] ?? '');
            }

            // Insert other members (children, etc.)
            // Get maxFamilyMembers from cota-class-family-directory-app.php
            require_once '../app-includes/cota-class-family-directory-app.php';
            for ($i = 1; $i <= COTA_Family_Directory_App::maxFamilyMembers; $i++) {
                $name = $row["othername$i"] ?? '';
                if (!empty($name)) {
                    $this->cota_insert_member(
                        $family_id,
                        $name,
                        '', // last_name (optional)
                        $row["othercell$i"] ?? '',
                        $row["otherem$i"] ?? '',
                        $row["otherbday$i"] ?? '',
                        $row["otherbap$i"] ?? ''
                    );
                }
            }
        }
        return;

    }

    private function cota_format_date($date) {
        if (empty($date)) {
            return "";
        }
        if (preg_match('/^(\\d{1,2})\/(\\d{1,2})(?:\/\\d{2,4})?$/', $date, $matches)) {
            return sprintf("%02d/%02d", $matches[1], $matches[2]);
        }
        return $date;
    }

    private function cota_format_phone($phone) {
        // Remove all non-digit characters
        $digits = preg_replace('/\D+/', '', $phone);
        // Format if 10 digits
        if (strlen($digits) === 10) {
            return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
        }
        // Return original if not 10 digits
        return $phone;
    }
/**
 * The artifacts like +AC0- are not standard quoted-printable or encoding issues—they are MIME encoded-words artifacts (from email exports), where +AC0- represents a hyphen (-), +AEA- is @, and so on.
 * These are not standard UTF-8 or ISO-8859-1 issues, but rather specific to how certain email clients encode special characters.
 * You need to explicitly replace these sequences.
 * The fixMimeArtifacts function is designed to handle these specific cases.
 *
 * @param [type] $value
 * @return void
 */
    private function cota_fix_mime_artifacts($value) {
        // Replace common MIME artifacts
        $patterns = [
            '/\+AC0-/' => '-',   // hyphen
            '/\+AEA-/' => '@',   // at symbol
            '/\+AF8-/' => '_',   // underscore
            '/\+ADw-/' => '<',   // less than
            '/\+AD4-/' => '>',   // greater than
            '/\+ACI-/' => '"',   // double quote
            '/\+AFs-/' => '[',   // left bracket
            '/\+AF0-/' => ']',   // right bracket
            '/\+ACM-/' => '#',   // hash
            '/\+ACY-/' => '&',   // ampersand
            '/\+ACU-/' => '%',   // percent
            '/\+ACQ-/' => '$',   // dollar
            '/\+ACC-/' => ',',   // comma
            '/\+AHs-/' => '{',   // left curly
            '/\+AH0-/' => '}',   // right curly
            '/\+ABw-/' => '\\',  // backslash
            '/\+AB8-/' => '|',   // pipe
            '/\+AC8-/' => '/',   // slash
            '/\+ACs-/' => ';',   // semicolon
            '/\+ACc-/' => ':',   // colon
            '/\+ACo-/' => '*',   // asterisk
            '/\+ACg-/' => '(',   // left paren
            '/\+ACk-/' => ')',   // right paren
            '/\+ADs-/' => ';',   // semicolon
            '/\+ACw-/' => '-',   // hyphen (again)
            '/\+AD0-/' => '=',   // equals
            '/\+AF4-/' => '^',   // caret
            '/\+AC4-/' => '.',   // period
            '/\+ADY-/' => '6',   // 6 (rare)
            '/\+ADc-/' => '7',   // 7 (rare)
            '/\+ADg-/' => '8',   // 8 (rare)
            '/\+ADk-/' => '9',   // 9 (rare)
            // Add more as needed
        ];
        return preg_replace(array_keys($patterns), array_values($patterns), $value);
    }

    private function cota_log_error($message) {
        error_log($message . PHP_EOL, 3, "import_errors.log");
    }

    private function cota_insert_family_and_get_id($data) {
        $stmt = $this->conn->prepare("INSERT INTO families (
            familyname, name1, name2, address, address2, city, state, zip, homephone,
            cellphone1, cellphone2, email1, email2, bday1, bday2,
            bap1, bap2, annday
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $homephone   = $this->cota_format_phone($data['homephone']);
        $cellphone1  = $this->cota_format_phone($data['cellphone1']);
        $cellphone2  = $this->cota_format_phone($data['cellphone2']);
        $bday1       = $this->cota_format_date($data['bday1']);
        $bday2       = $this->cota_format_date($data['bday2']);
        $bap1        = $this->cota_format_date($data['bap1']);
        $bap2        = $this->cota_format_date($data['bap2']);
        $annday      = $this->cota_format_date($data['annday']);

        $stmt->bind_param(
            "ssssssssssssssssss",
            $data['familyname'],
            $data['name1'],
            $data['name2'],
            $data['address'],
            $data['address2'],
            $data['city'],
            $data['state'],
            $data['zip'],
            $homephone,
            $cellphone1,
            $cellphone2,
            $data['email1'],
            $data['email2'],
            $bday1,
            $bday2,
            $bap1,
            $bap2,
            $annday
        );
        $stmt->execute();
        $family_id = $stmt->insert_id;
        $stmt->close();
        return $family_id;
    }

    /**
     * Insert a member into the database
     *
     * Table: members will have a record for EVERY family member
     * 
     * @param int $family_id
     * @param string $first_name
     * @param string $last_name
     * @param string $cell_phone
     * @param string $email
     * @param string $birthday
     * @param string $baptism
     */
    private function cota_insert_member($family_id, $first_name, $last_name, $cell_phone, $email, $birthday, $baptism) {
        $stmt = $this->conn->prepare("INSERT INTO members (family_id, first_name, last_name, cell_phone, email, birthday, baptism) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $cellphone  = $this->cota_format_phone($cell_phone);
        $bday       = $this->cota_format_date($birthday);
        $bap        = $this->cota_format_date($baptism);

        $stmt->bind_param(
            "issssss", 
            $family_id, 
            $first_name, 
            $last_name, 
            $cellphone,
            $email, 
            $bday, 
            $bap
        );
        $stmt->execute();
        $stmt->close();
    }
}

function cota_handle_error($message, $code) {
    echo "<p style='color: red;'>Error $code: $message</p>";
    exit;
}

// echo "About to instantiate COTA_Csv_Importer class" . PHP_EOL;
$importAll = new COTA_Csv_Importer();
// var_dump($importAll);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    $uploadDir = "uploads/";
    $fileName = basename($_FILES["csv_file"]["name"]);
    $uploadFile = $uploadDir . $fileName;

    // Ensure the uploads directory exists
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        cota_handle_error("Failed to find upload directory.", 101);
    }

    // Check for upload errors
    if ($_FILES["csv_file"]["error"] !== UPLOAD_ERR_OK) {
        cota_handle_error("File upload error. Code: " . $_FILES["csv_file"]["error"], 102);
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $uploadFile)) {
        $importer = new COTA_Csv_Importer();
        $importer->cota_import($uploadFile );
    } else {
        cota_handle_error("File save error. Code: " . $_FILES["csv_file"]["error"], 103);
    }

    echo "<h3 style='color:green;'>CSV imported successfully! " . $uploadFile . "</h3>";

    $importAll->cota_read_csv_to_assoc_array($uploadFile);
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
    <h2>Upload CSV File containing Family Import</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Select CSV File:</label>
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit">Upload & Import</button>
    </form>

    <button class="main-menu-return" type="button" ><a href='index.php'>Return to Main Menu</a></button>

</body>
</html>