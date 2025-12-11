<?php

namespace App\Controllers;

use App\Libraries\GoogleSheetsService;

class SyncController extends BaseController
{
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
        
        $sheetsService = new GoogleSheetsService();
        $result = $sheetsService->sync();
        
        $totalImported = $result['details']['totalImported'] ?? 0;
        log_message('info', "Cron sync via HTTP: {$totalImported} records imported");
        
        return $this->response->setJSON([
            'success' => true,
            'message' => $result['message'],
            'imported' => $totalImported,
            'time' => date('Y-m-d H:i:s'),
        ]);
    }

    protected $sheetsService;

    public function __construct()
    {
        $this->sheetsService = new GoogleSheetsService();
    }

    public function index()
    {
        $data = [
            'title' => 'Google Sheets Sync',
            'enabled' => $this->sheetsService->isEnabled(),
        ];

        return view('sync/index', $data);
    }

    public function run()
    {
        if (!$this->sheetsService->isEnabled()) {
            return redirect()->to('/sync')->with('error', 'Google Sheets integration is not enabled.');
        }

        $result = $this->sheetsService->syncAll();

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
