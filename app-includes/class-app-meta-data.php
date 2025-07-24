<?php
require_once __DIR__ . '/bootstrap.php';

class App_Meta_Data {
	private string $file_path;
	private array $metadata = array();
	private array $defaults = array(
		'Version'    => '0.0.0',
		'GitHub URL' => 'https://github.com/MattRyanCo',
	);

	public function __construct( string $file_path ) {
		$this->file_path = $file_path;
		$this->parse_meta_data();
	}

	private function parse_meta_data(): void {
		$header    = '';
		$max_bytes = 8192;

		if ( is_readable( $this->file_path ) ) {
			$handle = fopen( $this->file_path, 'r' );
			if ( $handle ) {
				$header = fread( $handle, $max_bytes );
				fclose( $handle );
			}
		}

		foreach ( $this->defaults as $key => $default ) {
			if ( preg_match( '/' . preg_quote( $key, '/' ) . ':\s*(.+)/i', $header, $matches ) ) {
				$this->metadata[ $key ] = trim( $matches[1] );
			} else {
				$this->metadata[ $key ] = $default;
			}
		}
	}

	public function getVersion(): string {
		return 'v' . $this->metadata['Version'];
	}

	public function getGitHubUrl(): string {
		return $this->metadata['GitHub URL'];
	}

	public function getAll(): array {
		return $this->metadata;
	}
}
