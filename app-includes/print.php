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
            printf("Processing family: %s<br>", $one_family['familyname']);
            $listing .= "\\par\\pard\\keepn\\b " . htmlspecialchars($one_family['familyname']) . "\\plain";
            if ( $one_family['name2']!= "") {
                $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['name1']) . " & " . htmlspecialchars($one_family['name2']);
            } else {
                $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['name1']);
            }   

            $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['address']) . " " . htmlspecialchars($one_family['address2']);
            if ( $one_family['city']!= "") {
                $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['city']) . ", " . htmlspecialchars($one_family['state']) . " " . htmlspecialchars($one_family['zip']);
            } else {
                $listing .= "\\par\\pard\\keepn "; 
            }
            if ($one_family['homephone'] != "") {
                $listing .= "\\par\\pard\\keepn H: " . htmlspecialchars($one_family['homephone']);
            }
            if ($one_family['cellphone1'] != "" && $one_family['cellphone2'] != "") {
                $listing .= "\\par\\pard\\keepn " . "c: " . $one_family['name1'] . " c: " . $one_family['cellphone1'] . "  c: " .  $one_family['cellphone2'];
            } elseif ($one_family['cellphone1'] != "") {
                $listing .= "\\par\\pard\\keepn " .  "c: " . $one_family['name1'] ;
            } elseif ($one_family['cellphone2'] != "") {
                $listing .= "\\par\\pard\\keepn " . "c: " . $one_family['name2'] ;
            } else {
                $listing .= "\\par\\pard\\keepn ";
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
$families = $conn->query("SELECT * FROM families ORDER BY `familyname`");
$num_families = $families->num_rows;
$ictr = 1;

$printer = new MembershipDirectoryPrinter();
  
?>