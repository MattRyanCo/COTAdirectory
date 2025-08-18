<?php
// require_once __DIR__ . '/bootstrap.php';

class Constants {
	const MAX_FAMILY_MEMBERS     = 10; // Maximum number of family members
	const MAX_INFORMATIONAL_DOCS = 5; // Maximum number of informational documents

	public $UPLOAD_DIR;

	public $COTA_APP;
	public $COTA_APP_FILE;
	public $COTA_APP_ASSETS;
	public $COTA_APP_INCLUDES;
	public $COTA_APP_LIBRARIES;
	public $FAMILIES_PER_PAGE;
	// Define constants for app paths

	public function __construct() {
		$this->COTA_APP           = dirname( __DIR__ );
		$this->COTA_APP_FILE      = $this->COTA_APP . '/index.php';
		$this->COTA_APP_ASSETS    = $this->COTA_APP . '/app-assets/';
		$this->COTA_APP_INCLUDES  = $this->COTA_APP . '/app-includes/';
		$this->COTA_APP_LIBRARIES = $this->COTA_APP . '/app-libraries/';
		$this->UPLOAD_DIR         = '../uploads/'; // Directory for uploaded files

		$this->FAMILIES_PER_PAGE = 5;
	}
}
