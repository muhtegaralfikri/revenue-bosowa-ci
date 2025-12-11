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
        $this->credentialsFile = env('google.sheets.credentials_file', WRITEPATH . 'google-credentials.json');
        $this->spreadsheetId = env('google.spreadsheet.id', '');
        $this->syncInterval = (int) env('google.sheets.sync_interval', 60);
    }
}
