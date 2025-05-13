<?php
// require_once 'database_functions.php';



class MembershipDirectoryPrinter
{

    public function generateRTFHeader()
    {
        return "{\\rtf1\\ansi\\deff0\\nouicompat\\fs24 ";
    }

    public function formatText($text)
    {
        return str_replace("\n", "\\par ", htmlspecialchars($text));
    }

    /**
     * Undocumented function
     *
     * @param [type] $families all family data 
     * @return void
     */
    public function formatFamilyListings($families)
    {
        $db = new Database();
        $conn = $db->getConnection();
        $ictr = 1;
        $listing =" ";
        while ($ictr < $families->num_rows ) {
            $one_family = $families->fetch_assoc();
            // var_dump($one_family);
            printf("Processing family: %s<br>", $one_family['family_name']);
            $listing .= "\\par\\pard\\keepn\\b " . htmlspecialchars($one_family['family_name']) . "\\plain";
            if ( $one_family['primary_name_2']!= "") {
                $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['primary_name_1']) . " & " . htmlspecialchars($one_family['primary_name_2']);
            } else {
                $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['primary_name_1']);
            }   

            $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['address']) . " " . htmlspecialchars($one_family['address_2']);
            $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['city']) . ", " . htmlspecialchars($one_family['state']) . " " . htmlspecialchars($one_family['zip']);
            if ($one_family['home_phone'] != "") {
                $listing .= "\\par\\pard\\keepn H: " . htmlspecialchars($one_family['home_phone']);
            }
            if ($one_family['primary_cell_2'] != "") {
                $listing .= "\\par\\pard\\keepn " . $one_family['primary_name_1'] . " c: " . $one_family['primary_cell_1'] . "  " . $one_family['primary_name_2'] . " c: " .  $one_family['primary_cell_2'];
            } else {
                $listing .= "\\par\\pard\\keepn " . $one_family['primary_name_1'] . " c: " . $one_family['primary_cell_1'];
            }

            // Get family members
            $individuals = $conn->query("SELECT * FROM members WHERE family_id = " . $one_family['id'] . " ORDER BY `first_name`");
            if ( ! $individuals->num_rows == 0) {
                $listing .= "   \\par\\pard\\keepn\\i " . "    Family Members \\plain";
                foreach ($individuals as $individual) {
                    $listing .= "\\par\\pard\\keepn " . "    " . $individual['first_name'] . " DOB: " . htmlspecialchars($individual['birthday']);
                }
            }
            $ictr++;
            $listing .="\\par";
        }
        return $listing;
    }
}

$db = new Database();
$conn = $db->getConnection();
$families = $conn->query("SELECT * FROM families ORDER BY `family_name`");
$num_families = $families->num_rows;
$ictr = 1;

$printer = new MembershipDirectoryPrinter();
  
?>