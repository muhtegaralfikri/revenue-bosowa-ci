<?php

namespace App\Controllers;

use App\Libraries\GoogleSheetsService;

class SyncController extends BaseController
{
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
