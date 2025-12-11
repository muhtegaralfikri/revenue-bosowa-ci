<?php

namespace App\Libraries;

use Config\GoogleSheets;
use App\Models\CompanyModel;
use App\Models\RevenueRealizationModel;
use Firebase\JWT\JWT;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GoogleSheetsService
{
    protected $config;
    protected $accessToken;
    protected $companyModel;
    protected $realizationModel;
    
    protected $monthNames = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
    protected $monthNamesEng = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

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
            log_message('error', 'Failed to get access token: ' . $e->getMessage());
            return false;
        }
    }

    protected function getAccessToken(): string
    {
        $credentials = json_decode(file_get_contents($this->config->credentialsFile), true);
        
        $now = time();
        $payload = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive.readonly',
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

    protected function downloadExcelFile(): ?string
    {
        $url = "https://www.googleapis.com/drive/v3/files/{$this->config->spreadsheetId}?alt=media";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', "Failed to download file ({$httpCode})");
            return null;
        }

        // Save to temp file
        $tempFile = WRITEPATH . 'uploads/temp_' . time() . '.xlsx';
        if (!is_dir(WRITEPATH . 'uploads')) {
            mkdir(WRITEPATH . 'uploads', 0755, true);
        }
        file_put_contents($tempFile, $response);
        
        return $tempFile;
    }

    public function syncAll(): array
    {
        $results = [
            'success' => false,
            'message' => '',
            'details' => [],
        ];

        if (!$this->initialize()) {
            $results['message'] = 'Failed to initialize. Check credentials file.';
            return $results;
        }

        try {
            // Download Excel file from Google Drive
            $tempFile = $this->downloadExcelFile();
            
            if (!$tempFile || !file_exists($tempFile)) {
                $results['message'] = 'Failed to download Excel file from Google Drive.';
                return $results;
            }

            // Load Excel file
            $spreadsheet = IOFactory::load($tempFile);
            
            // Clear old data before sync
            $this->clearOldGoogleSheetsData();

            $totalImported = 0;
            $sheetNames = $spreadsheet->getSheetNames();
            $results['details']['sheets'] = $sheetNames;
            
            foreach ($sheetNames as $sheetName) {
                // Process REVENUE sheet (like NestJS)
                if (strtoupper($sheetName) === 'REVENUE') {
                    $worksheet = $spreadsheet->getSheetByName($sheetName);
                    $parseResult = $this->parseRevenueSheet($worksheet);
                    $results['details'][] = [
                        'sheet' => $sheetName,
                        'imported' => $parseResult['count'],
                        'debug' => $parseResult['debug'] ?? [],
                    ];
                    $totalImported += $parseResult['count'];
                }
            }

            // Clean up temp file
            @unlink($tempFile);

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

    protected function parseCompanySheet($worksheet, array $company): array
    {
        $result = ['count' => 0, 'debug' => []];
        $data = $worksheet->toArray();
        
        // Find header rows
        $monthHeaderRow = -1;
        $columnHeaderRow = -1;
        
        for ($i = 0; $i < min(15, count($data)); $i++) {
            $row = $data[$i];
            if (!$row) continue;
            
            $rowStr = strtoupper(implode(' ', array_filter($row)));
            
            // Check for month names
            foreach (array_merge($this->monthNames, $this->monthNamesEng) as $month) {
                if (strpos($rowStr, $month) !== false) {
                    $monthHeaderRow = $i;
                    break;
                }
            }
            
            // Check for REALISASI
            if (strpos($rowStr, 'REALISASI') !== false) {
                $columnHeaderRow = $i;
            }
        }

        $result['debug']['monthHeaderRow'] = $monthHeaderRow;
        $result['debug']['columnHeaderRow'] = $columnHeaderRow;

        if ($monthHeaderRow === -1 || $columnHeaderRow === -1) {
            $result['debug']['error'] = 'Could not find month/column headers';
            return $result;
        }

        // Map REALISASI columns to months
        $monthRow = $data[$monthHeaderRow];
        $colHeaderRow = $data[$columnHeaderRow];
        $monthColumns = [];
        $mappedMonths = [];
        $currentYear = (int) date('Y');

        for ($col = 0; $col < count($colHeaderRow); $col++) {
            $colHeader = strtoupper(trim($colHeaderRow[$col] ?? ''));
            
            if ($colHeader === 'REALISASI') {
                for ($searchCol = $col; $searchCol >= 0; $searchCol--) {
                    $cell = strtoupper(trim($monthRow[$searchCol] ?? ''));
                    
                    $monthIdx = $this->findMonthIndex($cell, $this->monthNames);
                    if ($monthIdx === -1) {
                        $monthIdx = $this->findMonthIndex($cell, $this->monthNamesEng);
                    }
                    
                    if ($monthIdx !== -1) {
                        $monthNum = $monthIdx + 1;
                        if (!isset($mappedMonths[$monthNum])) {
                            $mappedMonths[$monthNum] = true;
                            $monthColumns[] = ['month' => $monthNum, 'year' => $currentYear, 'colIndex' => $col];
                        }
                        break;
                    }
                }
            }
        }

        $result['debug']['monthColumns'] = count($monthColumns);

        if (empty($monthColumns)) {
            $result['debug']['error'] = 'No REALISASI columns mapped to months';
            return $result;
        }

        // Find Rupiah section start
        $dataStartRow = $columnHeaderRow + 1;
        for ($i = $columnHeaderRow + 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (!$row) continue;
            $firstCell = strtolower(trim($row[0] ?? ''));
            if (strpos($firstCell, 'rupiah') !== false) {
                $dataStartRow = $i + 1;
                break;
            }
        }
        $result['debug']['dataStartRow'] = $dataStartRow;

        // Aggregate all amounts by month for this company
        $aggregated = [];

        for ($i = $dataStartRow; $i < count($data); $i++) {
            $row = $data[$i];
            if (!$row || empty($row[0])) continue;
            
            $itemName = strtoupper(trim($row[0]));
            
            // Skip total rows and empty
            if (strpos($itemName, 'TOTAL') !== false) continue;
            if (empty($itemName)) continue;

            foreach ($monthColumns as $mc) {
                $cellValue = $row[$mc['colIndex']] ?? null;
                $amount = $this->parseAmount($cellValue);
                if ($amount <= 0) continue;

                $key = "{$mc['year']}-{$mc['month']}";
                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = 0;
                }
                $aggregated[$key] += $amount;
            }
        }

        $result['debug']['aggregatedMonths'] = array_keys($aggregated);

        // Save aggregated data
        foreach ($aggregated as $key => $totalAmount) {
            list($year, $month) = explode('-', $key);

            $entryDate = sprintf('%04d-%02d-01', $year, $month);

            $this->realizationModel->insert([
                'company_id' => $company['id'],
                'date' => $entryDate,
                'amount' => $totalAmount,
                'description' => 'Google Sheets Sync',
            ]);
            $result['count']++;
        }

        return $result;
    }

    protected function parseRevenueSheet($worksheet): array
    {
        $result = ['count' => 0, 'debug' => []];
        $data = $worksheet->toArray();
        
        // Find header rows
        $monthHeaderRow = -1;
        $columnHeaderRow = -1;
        
        for ($i = 0; $i < min(15, count($data)); $i++) {
            $row = $data[$i];
            if (!$row) continue;
            
            $rowStr = strtoupper(implode(' ', array_filter($row)));
            
            // Check for month names
            foreach (array_merge($this->monthNames, $this->monthNamesEng) as $month) {
                if (strpos($rowStr, $month) !== false) {
                    $monthHeaderRow = $i;
                    break;
                }
            }
            
            // Check for REALISASI
            if (strpos($rowStr, 'REALISASI') !== false) {
                $columnHeaderRow = $i;
            }
        }

        $result['debug']['monthHeaderRow'] = $monthHeaderRow;
        $result['debug']['columnHeaderRow'] = $columnHeaderRow;

        if ($monthHeaderRow === -1 || $columnHeaderRow === -1) {
            $result['debug']['error'] = 'Could not find month/column headers';
            return $result;
        }

        // Map REALISASI columns to months
        $monthRow = $data[$monthHeaderRow];
        $colHeaderRow = $data[$columnHeaderRow];
        $monthColumns = [];
        $mappedMonths = [];
        $currentYear = (int) date('Y');

        for ($col = 0; $col < count($colHeaderRow); $col++) {
            $colHeader = strtoupper(trim($colHeaderRow[$col] ?? ''));
            
            if ($colHeader === 'REALISASI') {
                // Find which month this belongs to
                for ($searchCol = $col; $searchCol >= 0; $searchCol--) {
                    $cell = strtoupper(trim($monthRow[$searchCol] ?? ''));
                    
                    $monthIdx = $this->findMonthIndex($cell, $this->monthNames);
                    if ($monthIdx === -1) {
                        $monthIdx = $this->findMonthIndex($cell, $this->monthNamesEng);
                    }
                    
                    if ($monthIdx !== -1) {
                        $monthNum = $monthIdx + 1;
                        if (!isset($mappedMonths[$monthNum])) {
                            $mappedMonths[$monthNum] = true;
                            $monthColumns[] = ['month' => $monthNum, 'year' => $currentYear, 'colIndex' => $col];
                        }
                        break;
                    }
                }
            }
        }

        $result['debug']['monthColumns'] = $monthColumns;

        if (empty($monthColumns)) {
            $result['debug']['error'] = 'No REALISASI columns mapped to months';
            return $result;
        }

        // Find Rupiah section start
        $dataStartRow = $columnHeaderRow + 1;
        for ($i = $columnHeaderRow + 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (!$row) continue;
            $firstCell = strtolower(trim($row[0] ?? ''));
            if (strpos($firstCell, 'rupiah') !== false) {
                $dataStartRow = $i + 1;
                break;
            }
        }
        $result['debug']['dataStartRow'] = $dataStartRow;

        // Get companies
        $companies = $this->companyModel->getActiveCompanies();
        $companyMap = [];
        foreach ($companies as $company) {
            $companyMap[strtoupper($company['code'])] = $company;
        }
        $result['debug']['companies'] = array_keys($companyMap);

        // Aggregate by company-month (like NestJS)
        $aggregated = [];
        $sampleRows = [];
        $matchedRows = 0;
        $skippedRows = 0;

        for ($i = $dataStartRow; $i < count($data); $i++) {
            $row = $data[$i];
            if (!$row || empty($row[0])) continue;
            
            $itemName = strtoupper(trim($row[0]));
            
            // Skip total rows
            if (strpos($itemName, 'TOTAL') !== false) continue;
            
            // Identify company from item name (like NestJS)
            $companyCode = null;
            if (strpos($itemName, 'BBI') !== false) {
                $companyCode = 'BBI';
            } elseif (strpos($itemName, 'BBA') !== false) {
                $companyCode = 'BBA';
            } elseif (strpos($itemName, 'JAPELIN') !== false) {
                $companyCode = 'JAPELIN';
            }
            
            // Collect sample rows for debug
            if (count($sampleRows) < 5) {
                $sampleRows[] = ['row' => $i, 'item' => substr($itemName, 0, 50), 'company' => $companyCode];
            }
            
            if (!$companyCode) {
                $skippedRows++;
                continue;
            }
            
            $matchedRows++;

            foreach ($monthColumns as $mc) {
                $cellValue = $row[$mc['colIndex']] ?? null;
                $amount = $this->parseAmount($cellValue);
                if ($amount <= 0) continue;

                $key = "{$companyCode}-{$mc['year']}-{$mc['month']}";
                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = 0;
                }
                $aggregated[$key] += $amount;
            }
        }

        $result['debug']['sampleRows'] = $sampleRows;
        $result['debug']['matchedRows'] = $matchedRows;
        $result['debug']['skippedRows'] = $skippedRows;
        $result['debug']['aggregatedKeys'] = array_keys($aggregated);

        // Save aggregated data
        foreach ($aggregated as $key => $totalAmount) {
            list($companyCode, $year, $month) = explode('-', $key);
            
            $company = $companyMap[$companyCode] ?? null;
            if (!$company) continue;

            // Use first day of month for monthly realization
            $entryDate = sprintf('%04d-%02d-01', $year, $month);

            $this->realizationModel->insert([
                'company_id' => $company['id'],
                'date' => $entryDate,
                'amount' => $totalAmount,
                'description' => 'Google Sheets Sync',
            ]);
            $result['count']++;
        }

        return $result;
    }

    protected function findMonthIndex(string $cell, array $monthNames): int
    {
        foreach ($monthNames as $idx => $month) {
            if (strpos($cell, $month) !== false) {
                return $idx;
            }
        }
        return -1;
    }

    protected function findCompanyInRow(array $row, array $companyMap): ?array
    {
        foreach ($row as $cell) {
            $cellUpper = strtoupper(trim($cell ?? ''));
            foreach ($companyMap as $code => $company) {
                if (strpos($cellUpper, $code) !== false) {
                    return $company;
                }
            }
        }
        return null;
    }

    protected function parseDay($value): ?int
    {
        if (empty($value)) return null;
        
        if (is_numeric($value)) {
            $num = (int) $value;
            if ($num >= 1 && $num <= 31) {
                return $num;
            }
        }
        
        return null;
    }

    protected function parseAmount($value): float
    {
        if (empty($value)) return 0;
        if (is_numeric($value)) return (float) $value;
        
        $cleaned = preg_replace('/[Rp\s\.]/i', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);
        
        return (float) $cleaned;
    }
}
