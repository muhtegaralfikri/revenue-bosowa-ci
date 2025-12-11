<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\GoogleSheetsService;

class SyncSheets extends BaseCommand
{
    protected $group       = 'Sync';
    protected $name        = 'sync:sheets';
    protected $description = 'Sync data from Google Sheets/Excel';
    protected $usage       = 'sync:sheets';

    public function run(array $params)
    {
        CLI::write('Starting Google Sheets sync...', 'yellow');
        
        try {
            $sheetsService = new GoogleSheetsService();
            $result = $sheetsService->sync();
            
            if (strpos($result['message'], 'disabled') !== false) {
                CLI::write('Google Sheets sync is disabled.', 'red');
                return;
            }
            
            $totalImported = $result['details']['totalImported'] ?? 0;
            CLI::write("Sync completed! {$totalImported} records imported.", 'green');
            
            // Log the sync
            log_message('info', "Cron sync completed: {$totalImported} records imported");
            
        } catch (\Exception $e) {
            CLI::write('Sync failed: ' . $e->getMessage(), 'red');
            log_message('error', 'Cron sync failed: ' . $e->getMessage());
        }
    }
}
