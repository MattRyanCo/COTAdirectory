<?php
class App_Settings {

	public $MAX_FAMILY_MEMBERS; // Maximum number of family members
	public $MAX_INFORMATIONAL_DOCS; // Maximum number of informational documents
	public $FAMILIES_PER_PAGE;

	// Declare values for FPDF 
	public $FAMILY_LISTING_FONT;
	public $FAMILY_LISTING_FONT_SMALL;
	public $FAMILY_LISTING_LINE_HEIGHT;
	public $FAMILY_LISTING_LINE_HEIGHT_TALL;
	public $FAMILY_HEADING_FONT;
	public $DIRECTORY_HEADING_FONT;
	public $DIRECTORY_HEADING_FONT_SMALL;

	// Resource locations
	public $COTA_UPLOAD_DIR;
	public $COTA_APP;
	public $COTA_APP_FILE;
	public $COTA_APP_ASSETS;
	public $COTA_APP_INCLUDES;
	public $COTA_APP_LIBRARIES;


	public function __construct() {
		$this->COTA_APP           = dirname( __DIR__ );
		$this->COTA_APP_FILE      = $this->COTA_APP . '/index.php';
		$this->COTA_APP_ASSETS    = $this->COTA_APP . '/app-assets/';
		$this->COTA_APP_INCLUDES  = $this->COTA_APP . '/app-includes/';
		$this->COTA_APP_LIBRARIES = $this->COTA_APP . '/app-libraries/';
		
		$this->COTA_UPLOAD_DIR    = '../uploads/'; // Directory for uploaded files

		// Initialize values for FPDF 
		$this->FAMILIES_PER_PAGE = 5;
		$this->MAX_FAMILY_MEMBERS     = 10; // Maximum number of family members
		$this->MAX_INFORMATIONAL_DOCS = 5;

		$this->FAMILY_LISTING_FONT = 8;
		$this->FAMILY_LISTING_FONT_SMALL = 7;
		$this->FAMILY_LISTING_LINE_HEIGHT = .15;
		$this->FAMILY_LISTING_LINE_HEIGHT_TALL = .25;
		$this->FAMILY_HEADING_FONT = 10;
		$this->DIRECTORY_HEADING_FONT = 12;
		$this->DIRECTORY_HEADING_FONT_SMALL = 10;

	}
}
