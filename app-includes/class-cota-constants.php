<?php
// require_once __DIR__ . '/bootstrap.php';

class Constants {
	const MAX_FAMILY_MEMBERS = 10; // Maximum number of family members
	const ENVIRONMENT_TYPE   = 'laragon';
	// const ABSPATH                = __DIR__ . '/';
	const COTA_APP_ASSETS        = __DIR__ . '/app-assets/';
	const COTA_APP_INCLUDES      = __DIR__ . '/app-includes/';
	const COTA_APP_LIBRARIES     = __DIR__ . '/app-libraries/';
	const MAX_INFORMATIONAL_DOCS = 5; // Maximum number of informational documents
	const UPLOAD_DIR             = '../uploads/'; // Directory for uploaded files

	public $COTA_APP_FILE;
	public function __construct() {
		$this->$COTA_APP_FILE = dirname( __DIR__ ) . '/index.php';
	}
}
