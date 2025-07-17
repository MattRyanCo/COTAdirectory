<?php 

class AppMetadata {
    private string $filePath;
    private array $metadata = [];
    private array $defaults = [
        'Version' => '0.0.0',
        'GitHub URL' => 'https://github.com/MattRyanCo'
    ];

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
        $this->parseMetadata();
    }

    private function parseMetadata(): void {
        $header = '';
        $maxBytes = 8192;

        if (is_readable($this->filePath)) {
            $handle = fopen($this->filePath, 'r');
            if ($handle) {
                $header = fread($handle, $maxBytes);
                fclose($handle);
            }
        }

        foreach ($this->defaults as $key => $default) {
            if (preg_match('/' . preg_quote($key, '/') . ':\s*(.+)/i', $header, $matches)) {
                $this->metadata[$key] = trim($matches[1]);
            } else {
                $this->metadata[$key] = $default;
            }
        }
    }

    public function getVersion(): string {
        return 'v'.$this->metadata['Version'];
    }

    public function getGitHubUrl(): string {
        return $this->metadata['GitHub URL'];
    }

    public function getAll(): array {
        return $this->metadata;
    }
}
