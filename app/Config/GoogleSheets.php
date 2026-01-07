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
        $credentialsPath = env('google.sheets.credentials_file', 'google-credentials.json');
        
        // Convert relative path to absolute path
        if (!is_file($credentialsPath) && strpos($credentialsPath, ':') === false) {
            // It's a relative path, prepend with WRITEPATH
            $this->credentialsFile = WRITEPATH . ltrim($credentialsPath, '/\\');
        } else {
            $this->credentialsFile = $credentialsPath;
        }
        
        $this->spreadsheetId = env('google.spreadsheet.id', '');
        $this->syncInterval = (int) env('google.sheets.sync_interval', 60);
    }
}
