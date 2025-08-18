<?php 
class Membership_Directory_Printer
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
     * Format Family Listings
     *
     * @param [type] $families all family data 
     * @return void
     */
    public function formatFamilyListings($families)
    {
        global $cota_db, $connect;

        $ictr = 1;
        $listing =" ";
        while ($ictr < $families->num_rows ) {
            $one_family = $families->fetch_assoc();
            $email2 = false;
            $cell2 = false;
            $listing .= "\\par\\pard\\keepn\\b " . htmlspecialchars($one_family['familyname']) . "\\plain";
            if ( $one_family['fname2']!= "") {
                $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['fname1']) . " & " . htmlspecialchars($one_family['fname2'] );
            } else {
                $listing .= "\\par\\pard\\keepn " . htmlspecialchars($one_family['fname1']);
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

            // Add primary members with contact info
            $name1 = $one_family['fname1'];
            if ( isset($one_family['cellphone1']) && $one_family['cellphone1'] != "") {
                $name1 .= " c: " . htmlspecialchars($one_family['cellphone1']);
            }
            if ( isset($one_family['email1']) && $one_family['email1'] != "") {
                $name1 .= " e: " . htmlspecialchars($one_family['email1']);
            }
            $name2 = isset($one_family['fname2']) ? $one_family['fname2'] : '';
            if ( isset($one_family['cellphone2']) && $one_family['cellphone2'] != "") {
                $name2 .= " c: " . htmlspecialchars($one_family['cellphone2']);
                $cell2 = true;
            }
            if ( isset($one_family['email2']) && $one_family['email2'] != "") {
                $name2 .= " e: " . htmlspecialchars($one_family['email2']);
                $email2 = true;
            }

            $listing .= "\\par\\pard\\keepn   " . $name1;
            if ( $cell2 || $email2 ) $listing .= "\\par\\pard\\keepn   " . $name2;

            // Get family members
            $individuals = $connect->query("SELECT * FROM members WHERE family_id = " . $one_family['id'] . " ORDER BY `first_name`");
            if ( ! $individuals->num_rows == 0) {
                $listing .= "   \\par\\pard\\keepn\\i " . "    Family Members \\plain";
                foreach ($individuals as $individual) {
                    // $listing .= "\\par\\pard\\keepn " . "    " . $individual['first_name'] . " DoB: " . date('m/d', strtotime($individual['birthday'])). " " . htmlspecialchars($individual['cell_phone']) . " " . htmlspecialchars($individual['email']);
                    $listing .= "\\par\\pard\\keepn " . "    " . $individual['first_name'] . " DoB: " . date('m/d', strtotime($individual['birthday']));
                }
            }
            $ictr++;
            $listing .="\\par";
        }
        return $listing;
    }

    public function print_intro_pages( $num_intro_pages=3) {
        // Load and insert static pages.
        for ($i = 1; $i <= $num_intro_pages; $i++) {
            $file = '../uploads/intro'.$i.'.txt';
            if (file_exists($file)) {
                if ( $i == $num_intro_pages ) {
                    // last document, no page break
                    $rtfContent .= $this->formatText(file_get_contents($file)) . "\\pard\\par";
                } else {
                    $rtfContent .= $this->formatText(file_get_contents($file)) . "\\pard\\page\\par";
                }
            }
        }
        return $rtfContent;
    }
}
