<?php
/**
 * 
 */

require_once __DIR__ . '/bootstrap.php';
global $cota_constants, $cota_db, $connect;

require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';

    function cota_read_csv_to_assoc_array($filename) {
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
                    $value = cota_fix_mime_artifacts($value); 
                }
                $data[] = array_combine($headers, $row);
                // var_dump($data);
            }
            fclose($handle);
        }
        return $data;
    }

    function cota_import($filename ) {
        global $cota_constants;
        global $cota_db, $conn;

        require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';

        if (!file_exists($filename)) {
            die("Error: CSV file not found.");
        }
        $csvData = cota_read_csv_to_assoc_array($filename);

        foreach ($csvData as $row) {
            // Skip if familyname is missing
            if (empty($row['familyname'])) {
                cota_log_error("Missing familyname in row: " . print_r($row, true));
                continue;
            }

            // Insert family and get ID
            $family_id = cota_insert_family_and_get_id($row);

            // Insert primary members (parents)
            if (!empty($row['fname1'])) {
                cota_insert_member($family_id, $row['fname1'], '', $row['cellphone1'] ?? '', $row['email1'] ?? '', $row['bday1'] ?? '', $row['bap1'] ?? '');
            }
            if (!empty($row['fname2'])) {
                if (!empty($row['lname2'])) {
                    cota_insert_member($family_id, $row['fname2'], $row['lname2'], $row['cellphone2'] ?? '', $row['email2'] ?? '', $row['bday2'] ?? '', $row['bap2'] ?? '');
                } else {
                    // No last name for 2nd family member. Leave blank to use FamilyName
                    cota_insert_member($family_id, $row['fname2'], '', $row['cellphone2'] ?? '', $row['email2'] ?? '', $row['bday2'] ?? '', $row['bap2'] ?? '');
                }
            }

            // Insert other members (children, etc.)
            // for ($i = 1; $i <= $cota_constants->MAX_FAMILY_MEMBERS; $i++) {
            for ($i = 1; $i <= 9; $i++) {
                $name = $row["otherfname$i"] ?? '';
                if (!empty($name)) {
                    cota_insert_member(
                        $family_id,
                        $name,
                        $row["otherlname$i"] ?? '',
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

    function cota_format_phone($phone) {
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
    // private function cota_fix_mime_artifacts($value) {
    function cota_fix_mime_artifacts($value) {
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

    function cota_insert_family_and_get_id($data) {
        require_once __DIR__ . '/bootstrap.php';

        global $cota_constants;
        global $cota_db, $connect;

        require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';

        $stmt = $cota_db->conn->prepare("INSERT INTO families (
            familyname, fname1, fname2, lname2, address, address2, city, state, zip, homephone,
            cellphone1, cellphone2, email1, email2, bday1, bday2,
            bap1, bap2, annday
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $homephone   = cota_format_phone($data['homephone']);
        $cellphone1  = cota_format_phone($data['cellphone1']);
        $cellphone2  = cota_format_phone($data['cellphone2']);
        $bday1       = cota_format_date($data['bday1']);
        $bday2       = cota_format_date($data['bday2']);
        $bap1        = cota_format_date($data['bap1']);
        $bap2        = cota_format_date($data['bap2']);
        $annday      = cota_format_date($data['annday']);
        // $bday1       = $data['bday1'] ?? null;
        // $bday2       = $data['bday2'] ?? null;
        // $bap1        = $data['bap1'] ?? null;
        // $bap2        = $data['bap2'] ?? null;
        // $annday      = $data['annday'] ?? null;

        $stmt->bind_param(
            "sssssssssssssssssss",
            $data['familyname'],
            $data['fname1'],
            $data['fname2'],
            $data['lname2'],
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
            $bday1,   // Should be 's' (string) if using DATE or DATETIME in MySQL
            $bday2,   // Should be 's'
            $bap1,    // Should be 's'
            $bap2,    // Should be 's'
            $annday   // Should be 's'
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
    function cota_insert_member($family_id, $first_name, $last_name, $cell_phone, $email, $birthday, $baptism) {
        global $cota_constants;
        global $cota_db, $connect;

        require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';

        $stmt = $cota_db->conn->prepare("INSERT INTO members (family_id, first_name, last_name, cell_phone, email, birthday, baptism) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $cellphone  = cota_format_phone($cell_phone);
        $bday       = cota_format_date($birthday);
        $bap       = cota_format_date($baptism);
        // $bday       = $birthday ?? null;
        // $bap        = $baptism ?? null;

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

// Echo header
echo cota_page_header();

// Dump out remainder of import page. 
?>
    <div class="cota-import-container">
        <h2>Upload CSV File containing Family Import</h2>
        <form class="cota-import" method="post" enctype="multipart/form-data">
            <label>Select CSV File:</label>
            <input type="file" name="csv_file" accept=".csv" required>
            <button class="cota-import" type="submit">Upload & Import</button>
        </form>
    </div>
</body>
</html>

<?php
// Process input form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    $uploadDir = "uploads/";
    // Sanitize filename: remove dangerous chars, allow only safe chars
    $fileName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES["csv_file"]["name"]));
    $uploadFile = $uploadDir . $fileName;

    // Ensure the uploads directory exists
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        cota_handle_error("Failed to find upload directory.", 101);
    }

    // Check for upload errors
    if ($_FILES["csv_file"]["error"] !== UPLOAD_ERR_OK) {
        cota_handle_error("File upload error. Code: " . $_FILES["csv_file"]["error"], 102);
    }

    // Move uploaded file & perform import. 
    if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $uploadFile)) {
        cota_import($uploadFile);
    } else {
        cota_handle_error("File move error. Code: " . $_FILES["csv_file"]["error"], 103);
    }

    // cota_read_csv_to_assoc_array($uploadFile);
    write_success_notice("CSV imported successfully! ");
    exit;

}
