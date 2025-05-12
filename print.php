<?php
require_once 'Database.php';


class MembershipDirectoryPrinter
{
    private $introFiles = ['intro1.txt', 'intro2.txt', 'intro3.txt'];
    private $outputFile = 'membership_directory.rtf';

    public function printDirectory($families)
    {
        $rtfContent = $this->generateRTFHeader();

        // Add intro pages
        foreach ($this->introFiles as $file) {
            if (file_exists($file)) {
                $rtfContent .= $this->formatText(file_get_contents($file)) . "\\par\\page\\par";
            }
        }

        // Add family listings
        foreach ($families as $family) {
            $rtfContent .= $this->formatFamilyListing($family) . "\\par\\page\\par";
        }

        $rtfContent .= "}";

        file_put_contents($this->outputFile, $rtfContent);
    }

    private function generateRTFHeader()
    {
        return "{\\rtf1\\ansi\\deff0\\nouicompat\\fs24 ";
    }

    private function formatText($text)
    {
        return str_replace("\n", "\\par ", htmlspecialchars($text));
    }

    private function formatFamilyListing($family)
    {
        header('Content-Type: text/rtf');
        header('Content-Disposition: attachment; filename="membership_directory.rtf"');

        $db = new Database();
        $conn = $db->getConnection();
        $output = fopen('php://output', 'w');

        fputcsv($output, ["Family Name", "Member Name", "Address", "Address 2", "City", "State", "Zip", "Home Phone", "Cell Phone 1", "Email 1", "Birthday 1", "Cell Phone 2", "Email 2", "Birthday 2", "Anniversary"]);

        $families = $conn->query("SELECT * FROM families");
        while ($family = $families->fetch_assoc()) {

            // Output the primary members from the families table
            fputcsv($output, [
                $family['family_name'], 
                $family['primary_name_1'] . " & " . $family['primary_name_2'],
                $family['address'] . " " . $family['address_2'],
                $family['city'] . ", " . $family['state'] . " " . $family['zip'],
                "Home Phone: " . $family['home_phone'],
                $family['primary_name_1'] . " cell: " . $family['primary_cell_1'] . " email: " . $family['primary_email_1'] . " birthday: " . $family['primary_bday_1'],
                $family['primary_name_2'] . " cell: " . $family['primary_cell_2'] . " email: " . $family['primary_email_2'] . " birthday: " . $family['primary_bday_2'],     
                $family['anniversary']
            ]);


            $listing = "{\\b " . htmlspecialchars($family['family_name']) . "}\\par ";
            $listing .= htmlspecialchars($family['primary_name_1']) . " & " . htmlspecialchars($family['primary_name_2']) . "\\par ";
            $listing .= htmlspecialchars($family['address']) . " " . htmlspecialchars($family['address_2']) . "\\par ";
            $listing .= htmlspecialchars($family['city']) . ", " . htmlspecialchars($family['state']) . " " . htmlspecialchars($family['zip']);
            return $listing;
        }
    }
}

// Example usage
// $families = [
//     [
//         'family_name' => 'Smith',
//         'primary_name_1' => 'John',
//         'primary_name_2' => 'Jane',
//         'address' => '123 Main St',
//         'address_2' => 'Apt 4B',
//         'city' => 'Springfield',
//         'state' => 'IL',
//         'zip' => '62704'
//     ],
//     // Add more families as needed
// ];

// $printer = new MembershipDirectoryPrinter();
// $printer->printDirectory($families);
?>