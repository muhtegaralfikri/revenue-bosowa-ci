<?php

namespace App\Controllers;

use App\Libraries\GoogleSheetsService;
use App\Models\SyncSettingsModel;

class SyncController extends BaseController
{
    protected $sheetsService;
    protected $syncSettings;

    public function __construct()
    {
        $this->sheetsService = new GoogleSheetsService();
        $this->syncSettings = new SyncSettingsModel();
    }

    /**
     * Cron endpoint - can be called by external cron service
     * URL: /sync/cron?key=YOUR_CRON_KEY
     */
    public function cron()
    {
        // Simple security - check cron key
        $cronKey = $this->request->getGet('key');
        $expectedKey = env('CRON_SECRET_KEY', 'bosowa-sync-2025');
        
        if ($cronKey !== $expectedKey) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        
        // Check rate limiting
        if (!$this->syncSettings->canSync()) {
            $lastSync = $this->syncSettings->getLastSyncTime();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rate limited. Last sync: ' . $lastSync,
                'time' => date('Y-m-d H:i:s'),
            ]);
        }
        
        $result = $this->sheetsService->sync();
        
        $totalImported = $result['details']['totalImported'] ?? 0;
        $status = $result['success'] ? 'success' : 'failed';
        
        // Update sync stats
        $this->syncSettings->updateSyncStats($status, $totalImported);
        
        log_message('info', "Cron sync via HTTP: {$totalImported} records imported");
        
        return $this->response->setJSON([
            'success' => $result['success'],
            'message' => $result['message'],
            'imported' => $totalImported,
            'time' => date('Y-m-d H:i:s'),
        ]);
    }

    public function index()
    {
        $data = [
            'title' => 'Google Sheets Sync',
            'enabled' => $this->sheetsService->isEnabled(),
            'lastSync' => $this->syncSettings->getLastSyncTime(),
            'lastStatus' => $this->syncSettings->getLastSyncStatus(),
            'lastCount' => $this->syncSettings->get('last_sync_count', '0'),
            'syncInterval' => $this->syncSettings->getSyncInterval(),
        ];

        return view('sync/index', $data);
    }

    public function run()
    {
        if (!$this->sheetsService->isEnabled()) {
            return redirect()->to('/sync')->with('error', 'Google Sheets integration is not enabled.');
        }

        // Manual sync bypasses rate limiting
        $result = $this->sheetsService->sync();
        
        $totalImported = $result['details']['totalImported'] ?? 0;
        $status = $result['success'] ? 'success' : 'failed';
        
        // Update sync stats
        $this->syncSettings->updateSyncStats($status, $totalImported);

        if ($result['success']) {
            return redirect()->to('/sync')
                ->with('success', $result['message'])
                ->with('debug', $result['details'] ?? []);
        } else {
            return redirect()->to('/sync')
                ->with('error', $result['message'])
                ->with('debug', $result['details'] ?? []);
        }
    }
}
