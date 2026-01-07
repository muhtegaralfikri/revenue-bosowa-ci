<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class GoogleSheets extends BaseConfig
{
    public bool $enabled = true;
    public string $credentialsFile = '';
    public string $spreadsheetId = '';
    public int $syncInterval = 60;

    public function __construct()
    {
        parent::__construct();
        
        $this->enabled = env('google.sheets.enabled', false) === 'true' || env('google.sheets.enabled', false) === true;
        $credentialsPath = trim((string) env('google.sheets.credentials_file', 'google-credentials.json'), " \t\n\r\0\x0B\"'");

        // Respect absolute paths (Unix or Windows) even if the file is missing;
        // otherwise, treat as relative to the writable directory.
        if ($this->isAbsolutePath($credentialsPath)) {
            $this->credentialsFile = $credentialsPath;
        } else {
            $this->credentialsFile = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . ltrim($credentialsPath, '/\\');
        }
        
        $this->spreadsheetId = env('google.spreadsheet.id', '');
        $this->syncInterval = (int) env('google.sheets.sync_interval', 60);
    }

    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        return $path[0] === '/' // Unix-style absolute path
            || $path[0] === '\\' // UNC path
            || preg_match('#^[A-Za-z]:[\\\\/]#', $path) === 1; // Windows drive letter
    }
}
