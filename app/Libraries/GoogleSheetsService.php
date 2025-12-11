<?php

namespace App\Libraries;

use Config\GoogleSheets;
use App\Models\CompanyModel;
use App\Models\RevenueRealizationModel;
use Firebase\JWT\JWT;

class GoogleSheetsService
{
    protected $config;
    protected $accessToken;
    protected $companyModel;
    protected $realizationModel;
    
    protected $monthNamesInd = ['', 'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 
                                 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
    protected $monthNamesEng = ['', 'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
                                 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
    protected $monthNamesShort = ['', 'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN',
                                   'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
    protected $monthNamesShortEng = ['', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
                                      'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

    public function __construct()
    {
        $this->config = new GoogleSheets();
        $this->companyModel = new CompanyModel();
        $this->realizationModel = new RevenueRealizationModel();
    }

    public function isEnabled(): bool
    {
        return $this->config->enabled;
    }

    public function initialize(): bool
    {
        if (!$this->config->enabled) {
            return false;
        }

        if (!file_exists($this->config->credentialsFile)) {
            log_message('error', 'Google credentials file not found: ' . $this->config->credentialsFile);
            return false;
        }

        try {
            $this->accessToken = $this->getAccessToken();
            return !empty($this->accessToken);
        } catch (\Exception $e) {
            log_message('error', 'Failed to initialize Google Sheets: ' . $e->getMessage());
            return false;
        }
    }

    protected function getAccessToken(): string
    {
        $credentials = json_decode(file_get_contents($this->config->credentialsFile), true);
        
        $now = time();
        $payload = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $jwt = JWT::encode($payload, $credentials['private_key'], 'RS256');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://oauth2.googleapis.com/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to get access token: ' . $response);
        }

        $data = json_decode($response, true);
        return $data['access_token'] ?? '';
    }

    protected function apiRequest(string $endpoint): ?array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            log_message('error', "API request failed ({$httpCode}): {$response}");
            return $data; // Return error data for better error messages
        }

        return $data;
    }

    public function syncAll(): array
    {
        $results = [
            'success' => false,
            'message' => '',
            'details' => [],
        ];

        if (!$this->initialize()) {
            $results['message'] = 'Failed to initialize Google Sheets service. Check credentials file.';
            return $results;
        }

        try {
            // Get spreadsheet info
            $spreadsheetUrl = "https://sheets.googleapis.com/v4/spreadsheets/{$this->config->spreadsheetId}";
            $spreadsheet = $this->apiRequest($spreadsheetUrl);

            if (!$spreadsheet || !isset($spreadsheet['sheets'])) {
                $errorMsg = $spreadsheet['error']['message'] ?? 'Unknown error';
                $results['message'] = "Failed to get spreadsheet info: {$errorMsg}. Make sure spreadsheet is shared with service account.";
                return $results;
            }

            // Clear old Google Sheets data before sync
            $this->clearOldGoogleSheetsData();

            $totalImported = 0;
            foreach ($spreadsheet['sheets'] as $sheet) {
                $sheetTitle = $sheet['properties']['title'] ?? '';
                $company = $this->matchCompanyBySheetTitle($sheetTitle);
                
                if ($company) {
                    $imported = $this->syncSheet($sheetTitle, $company);
                    $results['details'][] = [
                        'sheet' => $sheetTitle,
                        'company' => $company['code'],
                        'imported' => $imported,
                    ];
                    $totalImported += $imported;
                }
            }

            $results['success'] = true;
            $results['message'] = "Sync completed. Total {$totalImported} records imported.";
        } catch (\Exception $e) {
            $results['message'] = 'Sync failed: ' . $e->getMessage();
            log_message('error', 'Google Sheets sync error: ' . $e->getMessage());
        }

        return $results;
    }

    protected function clearOldGoogleSheetsData(): void
    {
        $this->realizationModel->where('description', 'Google Sheets Sync')->delete();
    }

    protected function matchCompanyBySheetTitle(string $title): ?array
    {
        $titleUpper = strtoupper($title);
        $companies = $this->companyModel->getActiveCompanies();

        foreach ($companies as $company) {
            if (strpos($titleUpper, strtoupper($company['code'])) !== false) {
                return $company;
            }
        }

        return null;
    }

    protected function syncSheet(string $sheetTitle, array $company): int
    {
        try {
            $encodedTitle = urlencode($sheetTitle);
            $range = "{$encodedTitle}!A1:ZZ1000";
            $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->config->spreadsheetId}/values/{$range}";
            
            $response = $this->apiRequest($url);

            if (!$response || !isset($response['values'])) {
                return 0;
            }

            return $this->parseAndImportData($response['values'], $company);
        } catch (\Exception $e) {
            log_message('error', "Error syncing sheet {$sheetTitle}: " . $e->getMessage());
            return 0;
        }
    }

    protected function parseAndImportData(array $data, array $company): int
    {
        $headerRow = null;
        $columnMapping = [];
        $imported = 0;
        $mappedMonths = [];

        // Find header row and map columns
        foreach ($data as $rowIndex => $row) {
            if ($this->isHeaderRow($row)) {
                $headerRow = $rowIndex;
                $columnMapping = $this->mapColumns($row, $mappedMonths);
                break;
            }
        }

        if ($headerRow === null || empty($columnMapping)) {
            return 0;
        }

        // Process data rows
        for ($i = $headerRow + 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (empty($row) || !isset($row[0])) {
                continue;
            }

            $dateValue = $this->parseDate($row[0] ?? '');
            if (!$dateValue) {
                continue;
            }

            foreach ($columnMapping as $colIndex => $monthInfo) {
                if (!isset($row[$colIndex])) {
                    continue;
                }

                $amount = $this->parseAmount($row[$colIndex]);
                if ($amount <= 0) {
                    continue;
                }

                // Build date for this entry
                $entryDate = sprintf('%04d-%02d-%02d', $monthInfo['year'], $monthInfo['month'], $dateValue['day']);

                $this->realizationModel->insert([
                    'company_id' => $company['id'],
                    'date' => $entryDate,
                    'amount' => $amount,
                    'description' => 'Google Sheets Sync',
                ]);
                $imported++;
            }
        }

        return $imported;
    }

    protected function isHeaderRow(array $row): bool
    {
        foreach ($row as $cell) {
            $cellUpper = strtoupper(trim($cell ?? ''));
            if (strpos($cellUpper, 'REALISASI') !== false || strpos($cellUpper, 'TANGGAL') !== false) {
                return true;
            }
        }
        return false;
    }

    protected function mapColumns(array $headerRow, array &$mappedMonths): array
    {
        $mapping = [];
        $currentYear = date('Y');

        foreach ($headerRow as $colIndex => $header) {
            $headerUpper = strtoupper(trim($header ?? ''));
            
            if (strpos($headerUpper, 'REALISASI') !== false) {
                $monthYear = $this->extractMonthYear($headerUpper, $currentYear);
                if ($monthYear) {
                    $monthKey = $monthYear['month'] . '-' . $monthYear['year'];
                    if (!isset($mappedMonths[$monthKey])) {
                        $mappedMonths[$monthKey] = true;
                        $mapping[$colIndex] = $monthYear;
                    }
                }
            }
        }

        return $mapping;
    }

    protected function extractMonthYear(string $header, int $defaultYear): ?array
    {
        // Try to find year in header
        preg_match('/20\d{2}/', $header, $yearMatch);
        $year = !empty($yearMatch) ? (int) $yearMatch[0] : $defaultYear;

        // Try Indonesian month names
        foreach ($this->monthNamesInd as $monthNum => $monthName) {
            if ($monthNum > 0 && strpos($header, $monthName) !== false) {
                return ['month' => $monthNum, 'year' => $year];
            }
        }

        // Try English month names
        foreach ($this->monthNamesEng as $monthNum => $monthName) {
            if ($monthNum > 0 && strpos($header, $monthName) !== false) {
                return ['month' => $monthNum, 'year' => $year];
            }
        }

        // Try short Indonesian month names
        foreach ($this->monthNamesShort as $monthNum => $monthName) {
            if ($monthNum > 0 && strpos($header, $monthName) !== false) {
                return ['month' => $monthNum, 'year' => $year];
            }
        }

        // Try short English month names
        foreach ($this->monthNamesShortEng as $monthNum => $monthName) {
            if ($monthNum > 0 && strpos($header, $monthName) !== false) {
                return ['month' => $monthNum, 'year' => $year];
            }
        }

        return null;
    }

    protected function parseDate($value): ?array
    {
        if (empty($value)) {
            return null;
        }

        // If numeric (Excel serial date or day number)
        if (is_numeric($value)) {
            $num = (int) $value;
            if ($num >= 1 && $num <= 31) {
                return ['day' => $num];
            }
        }

        // Try to parse as date string
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return ['day' => (int) date('j', $timestamp)];
        }

        return null;
    }

    protected function parseAmount($value): float
    {
        if (empty($value)) {
            return 0;
        }

        // If already numeric, return directly
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove currency symbols and spaces
        $cleaned = preg_replace('/[Rp\s\.]/i', '', $value);
        // Replace comma with dot for decimal
        $cleaned = str_replace(',', '.', $cleaned);
        
        return (float) $cleaned;
    }
}
