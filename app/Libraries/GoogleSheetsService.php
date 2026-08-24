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
            // Try to get existing token first (from cache if available)
            $this->accessToken = $this->getAccessToken();
            if (empty($this->accessToken)) {
                throw new \Exception('Empty access token');
            }

            log_message('debug', 'Google access token obtained successfully');
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get access token: ' . $e->getMessage());
            return false;
        }
    }

    protected function getAccessToken(): string
    {
        if (!file_exists($this->config->credentialsFile)) {
            throw new \Exception("Credentials file not found: {$this->config->credentialsFile}");
        }

        $credentials = json_decode(file_get_contents($this->config->credentialsFile), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse credentials JSON: " . json_last_error_msg());
        }

        if (!isset($credentials['client_email']) || !isset($credentials['private_key'])) {
            throw new \Exception("Invalid credentials format. Missing client_email or private_key.");
        }

        // Enable SSL verification in production; allow opt-out via env for debugging
        $verifySsl = env('google.sheets.verify_ssl', ENVIRONMENT === 'production');

        $now = time();
        $payload = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        try {
            $jwt = JWT::encode($payload, $credentials['private_key'], 'RS256');
        } catch (\Exception $e) {
            throw new \Exception("Failed to encode JWT: " . $e->getMessage());
        }

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
            CURLOPT_SSL_VERIFYPEER => (bool) $verifySsl, // Disable SSL verification for development/debugging
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0, // CURLOPT_SSL_VERIFYHOST requires 2 for verification
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception("CURL error: {$curlError} ({$curlErrno})");
        }

        if ($httpCode !== 200) {
            throw new \Exception("Failed to get access token (HTTP {$httpCode}): {$response}");
        }

        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            throw new \Exception("Invalid token response: {$response}");
        }

        return $data['access_token'];
    }

    protected function downloadExcelFile(): ?string
    {
        // Increase PHP execution time limit for large file downloads
        set_time_limit(300); // 5 minutes
        $verifySsl = env('google.sheets.verify_ssl', ENVIRONMENT === 'production');

        $url = "https://www.googleapis.com/drive/v3/files/{$this->config->spreadsheetId}?alt=media";

        log_message('debug', "Downloading Excel file from: {$url}");

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300,        // Maximum 300 seconds for download (increased from 60)
            CURLOPT_CONNECTTIMEOUT => 30,   // Maximum 30 seconds to connect (increased from 15)
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
            ],
            CURLOPT_SSL_VERIFYPEER => (bool) $verifySsl, // Disable SSL verification for development/debugging
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0, // CURLOPT_SSL_VERIFYHOST requires 2 for verification
            CURLOPT_VERBOSE => true, // Enable verbose logging
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($curlError) {
            log_message('error', "CURL error: {$curlError} ({$curlErrno})");
        }

        if ($httpCode !== 200) {
            log_message('error', "Failed to download file (HTTP {$httpCode})");
            log_message('error', "Response body: " . substr($response, 0, 500));
            return null;
        }

        log_message('debug', "File downloaded successfully (HTTP {$httpCode}), size: " . strlen($response) . " bytes");

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

            // If download fails, try refreshing token and retry
            if (!$tempFile || !file_exists($tempFile)) {
                log_message('debug', 'Download failed, trying to refresh access token and retry...');
                $this->accessToken = $this->getAccessToken();
                $tempFile = $this->downloadExcelFile();

                if (!$tempFile || !file_exists($tempFile)) {
                    $results['message'] = 'Failed to download Excel file from Google Drive (after token refresh).';
                    return $results;
                }
            }

            // Load Excel file
            $spreadsheet = IOFactory::load($tempFile);
            
            // Clear old data before sync
            $this->clearOldGoogleSheetsData();

            $totalImported = 0;
            $sheetNames = $spreadsheet->getSheetNames();
            $results['details']['sheets'] = $sheetNames;
            
            // 1. Process REVENUE summary sheets (REVENUE 2025, REVENUE 2026, etc.)
            foreach ($sheetNames as $sheetName) {
                if (preg_match('/^REVENUE\s+(\d{4})$/i', $sheetName, $matches)) {
                    $sheetYear = (int) $matches[1];
                    $worksheet = $spreadsheet->getSheetByName($sheetName);

                    if ($sheetYear >= 2026) {
                        $parseResult = $this->parseRevenueSheet($worksheet, $sheetYear, $sheetName);
                        if ($parseResult['count'] === 0) {
                            $parseResult = $this->parseRevenueSheetByKeyword($worksheet, $sheetYear);
                        }
                    } else {
                        $parseResult = $this->parseRevenueSheet($worksheet, $sheetYear);
                    }

                    $results['details'][] = [
                        'sheet' => $sheetName,
                        'type' => 'summary',
                        'imported' => $parseResult['count'],
                        'debug' => $parseResult['debug'] ?? [],
                    ];
                    $totalImported += $parseResult['count'];
                }
            }

            // 2. Process individual daily company sheets (e.g. BBI, BBA, JAPELIN, BBI 2026, BBA 2026, JAPELIN 2026)
            foreach ($sheetNames as $sheetName) {
                $companyCode = null;
                if (preg_match('/^(BBI|BBA|JAPELIN)(?:\s+(\d{4}))?$/i', trim($sheetName), $matches)) {
                    $companyCode = strtoupper($matches[1]);
                }

                if ($companyCode) {
                    $worksheet = $spreadsheet->getSheetByName($sheetName);
                    $parseResult = $this->parseDailyCompanySheet($worksheet, $companyCode);
                    if ($parseResult['count'] > 0) {
                        $results['details'][] = [
                            'sheet' => $sheetName,
                            'type' => 'daily',
                            'imported' => $parseResult['count'],
                            'debug' => $parseResult['debug'] ?? [],
                        ];
                        $totalImported += $parseResult['count'];
                    }
                }
            }

            // Clean up temp file
            @unlink($tempFile);
            
            // Clear cache after successful sync
            $this->realizationModel->clearCache();
            
            $results['details']['totalImported'] = $totalImported;
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
        $this->realizationModel->whereIn('description', [
            'Google Sheets Sync',
            'Google Sheets Sync Summary',
            'Google Sheets Sync Daily'
        ])->delete();
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

    protected function parseRevenueSheet($worksheet, int $year = null, string $sheetName = null): array
    {
        $result = ['count' => 0, 'debug' => []];
        $data = $worksheet->toArray();
        $currentYear = $year ?? (int) date('Y');

        // Cari baris header bulan dan REALISASI dalam 15 baris pertama
        $monthHeaderRow = -1;
        $columnHeaderRow = -1;
        for ($i = 0; $i < min(15, count($data)); $i++) {
            $row = $data[$i];
            if (!$row) {
                continue;
            }

            $rowStr = strtoupper(implode(' ', array_filter($row)));

            foreach (array_merge($this->monthNames, $this->monthNamesEng) as $month) {
                if (strpos($rowStr, $month) !== false) {
                    $monthHeaderRow = $i;
                    break;
                }
            }

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

        // Map kolom REALISASI ke bulan
        $monthRow = $data[$monthHeaderRow];
        $colHeaderRow = $data[$columnHeaderRow];
        $monthColumns = [];
        for ($col = 0; $col < count($colHeaderRow); $col++) {
            $colHeader = strtoupper(trim($colHeaderRow[$col] ?? ''));

            if ($colHeader === 'REALISASI') {
                for ($searchCol = $col; $searchCol >= 0; $searchCol--) {
                    $cell = strtoupper(trim($monthRow[$searchCol] ?? ''));

                    // Hanya pakai sel pertama yang terisi di kiri kolom REALISASI
                    if ($cell === '') {
                        continue;
                    }
                    if (strpos($cell, 'TOTAL') !== false) {
                        break;
                    }

                    $monthIdx = $this->findMonthIndex($cell, $this->monthNames);
                    if ($monthIdx === -1) {
                        $monthIdx = $this->findMonthIndex($cell, $this->monthNamesEng);
                    }

                    if ($monthIdx !== -1) {
                        $monthColumns[] = [
                            'month' => $monthIdx + 1,
                            'year' => $currentYear,
                            'colIndex' => $col,
                        ];
                        break;
                    }
                }
            }
        }

        if (empty($monthColumns)) {
            $result['debug']['error'] = 'No REALISASI columns mapped to months';
            return $result;
        }

        // Cari baris mulai data (setelah "Rupiah" di kolom A/B)
        $dataStartRow = $columnHeaderRow + 1;
        for ($i = $columnHeaderRow + 1; $i < min(100, count($data)); $i++) {
            $row = $data[$i];
            if (!$row) {
                continue;
            }
            $cellA = strtolower(trim($row[0] ?? ''));
            $cellB = strtolower(trim($row[1] ?? ''));
            if (strpos($cellA, 'rupiah') !== false || strpos($cellB, 'rupiah') !== false) {
                $dataStartRow = $i + 1;
                break;
            }
        }

        // Siapkan mapping perusahaan dari database
        $companies = $this->companyModel->getActiveCompanies();
        $companyMap = [];
        foreach ($companies as $company) {
            $companyMap[strtoupper($company['code'])] = $company;
        }

        $aggregated = [];
        $grandTotals = []; // Pendapatan lain-lain BBA setelah TOTAL REVENUE BBI
        $currentBlock = 'JAPELIN';
        $afterBbi = false;

        for ($i = $dataStartRow; $i < count($data); $i++) {
            $row = $data[$i];
            if (!$row) {
                continue;
            }

            $cellA = strtoupper(trim($row[0] ?? ''));
            $cellB = strtoupper(trim($row[1] ?? ''));

            if ($cellA === '' && $cellB === '') {
                continue;
            }

            // Penanda blok
            if (str_contains($cellA, 'TOTAL REVENUE JAPELIN') || str_contains($cellB, 'TOTAL REVENUE JAPELIN')) {
                $currentBlock = 'BBA';
                continue;
            }
            if (str_contains($cellA, 'TOTAL REVENUE BBA') || str_contains($cellB, 'TOTAL REVENUE BBA')) {
                $currentBlock = 'BBI';
                continue;
            }
            if (str_contains($cellA, 'TOTAL REVENUE BBI') || str_contains($cellB, 'TOTAL REVENUE BBI')) {
                $currentBlock = null;
                $afterBbi = true;
                continue;
            }
            // Grand total diabaikan
            if (str_contains($cellA, 'TOTAL REVENUE') || str_contains($cellB, 'TOTAL REVENUE')) {
                break;
            }

            // Baris total atau kosong dilewati
            if (str_contains($cellA, 'TOTAL') || str_contains($cellB, 'TOTAL')) {
                continue;
            }

            $targetCode = $currentBlock;
            if ($afterBbi) {
                if (str_contains($cellA, 'BBI') || str_contains($cellB, 'BBI')) {
                    $targetCode = 'BBI';
                } elseif (str_contains($cellA, 'BBA') || str_contains($cellB, 'BBA')) {
                    $targetCode = 'BBA';
                } elseif (str_contains($cellA, 'JAPELIN') || str_contains($cellB, 'JAPELIN')) {
                    $targetCode = 'JAPELIN';
                } else {
                    $targetCode = 'GRAND';
                }
            }

            if (!$targetCode) {
                continue;
            }

            foreach ($monthColumns as $mc) {
                $cellValue = $row[$mc['colIndex']] ?? null;
                $amount = $this->parseAmount($cellValue);
                if ($amount <= 0) {
                    continue;
                }

                $key = "{$targetCode}-{$mc['year']}-{$mc['month']}";
                if ($targetCode === 'GRAND') {
                    if (!isset($grandTotals[$key])) {
                        $grandTotals[$key] = 0;
                    }
                    $grandTotals[$key] += $amount;
                } else {
                    if (!isset($aggregated[$key])) {
                        $aggregated[$key] = 0;
                    }
                    $aggregated[$key] += $amount;
                }
            }
        }

        // Simpan per perusahaan
        foreach ($aggregated as $key => $totalAmount) {
            [$companyCode, $yr, $mo] = explode('-', $key);
            $company = $companyMap[$companyCode] ?? null;
            if (!$company) {
                continue;
            }

            $entryDate = sprintf('%04d-%02d-01', $yr, $mo);
            $this->realizationModel->insert([
                'company_id' => $company['id'],
                'date' => $entryDate,
                'amount' => $totalAmount,
                'description' => 'Google Sheets Sync Summary',
            ]);
            $result['count']++;
        }

        if (ENVIRONMENT === 'development') {
            $result['debug']['monthColumns'] = $monthColumns;
            $result['debug']['aggregatedKeys'] = array_keys($aggregated);
            $result['debug']['grandTotals'] = $grandTotals;
            $result['debug']['dataStartRow'] = $dataStartRow;
            $result['debug']['totalRows'] = count($data);
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
        if ($value === null || $value === '') return 0;

        // If already a number from PhpSpreadsheet, return it directly (rounded)
        if (is_numeric($value)) {
            return round((float) $value);
        }

        $strValue = trim((string) $value);
        
        // Detect Indonesian vs International format
        $hasDot = strpos($strValue, '.') !== false;
        $hasComma = strpos($strValue, ',') !== false;
        
        $cleaned = '';
        
        if ($hasDot && $hasComma) {
            // Indonesian format: 1.234.567,89 -> dots are thousands, comma is decimal
            $cleaned = str_replace(['.', ' ', 'Rp'], '', $strValue);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif ($hasDot && !$hasComma) {
            // Could be "1.234.567" (Indo thousands) or "1234567.89" (decimal)
            $temp = preg_replace('/[Rp\s]/i', '', $strValue);
            $parts = explode('.', $temp);
            if (count($parts) > 2 || (count($parts) === 2 && strlen($parts[count($parts)-1]) === 3)) {
                // Multiple dots or last part is 3 digits = thousand separators
                $cleaned = str_replace('.', '', $temp);
            } else {
                // Single dot with non-3-digit decimal = decimal point
                $cleaned = $temp;
            }
        } elseif ($hasComma && !$hasDot) {
            // Comma only: could be "1,234,567" (thousands) or "1234567,89" (Indo decimal)
            $temp = preg_replace('/[Rp\s]/i', '', $strValue);
            $parts = explode(',', $temp);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                // Single comma with 1-2 digits after = Indonesian decimal
                $cleaned = str_replace(',', '.', $temp);
            } else {
                // Multiple commas or 3+ digits after = thousand separators
                $cleaned = str_replace(',', '', $temp);
            }
        } else {
            // No dots or commas, just remove currency symbols and spaces
            $cleaned = preg_replace('/[Rp\s]/i', '', $strValue);
        }

        $amount = (float) $cleaned;
        return round($amount);
    }

    /**
     * Parse REVENUE 2026+ sheet using Keyword Search Strategy
     * Looks for specific keywords in Column B (Index 1) and gets realization from Column D (Index 3)
     */
    protected function parseRevenueSheetByKeyword($worksheet, int $year = null): array
    {
        $result = ['count' => 0, 'debug' => []];
        $data = $worksheet->toArray();

        // Get active companies from database first
        $companies = $this->companyModel->getActiveCompanies();

        // Build Company Mapping dynamically from database
        // Maps keywords in Column B to actual database company_id
        $companyMap = [];
        foreach ($companies as $company) {
            $companyCode = strtoupper(trim($company['code']));
            $keyword = "TOTAL REVENUE {$companyCode}";
            $companyMap[$keyword] = $company['id'];
        }

        $result['debug']['totalRows'] = count($data);
        $result['debug']['companyMap'] = array_keys($companyMap);
        $result['debug']['companyIds'] = array_values($companyMap);
        $result['debug']['companiesFound'] = count($companies);

        // Safety check: no companies found in database
        if (empty($companyMap)) {
            $result['debug']['error'] = 'No active companies found in database';
            return $result;
        }

        // Find month header row (JAN, FEB, MAR, etc.)
        $monthHeaderRow = -1;
        $realisasiHeaderRow = -1;

        for ($i = 0; $i < min(20, count($data)); $i++) {
            $row = $data[$i];
            if (!$row) continue;

            $rowStr = strtoupper(implode(' ', array_filter($row)));

            // Check for month names
            foreach (array_merge($this->monthNames, $this->monthNamesEng) as $month) {
                if (strpos($rowStr, $month) !== false) {
                    $monthHeaderRow = $i;
                    $result['debug'][] = "Found month header at row $i: " . substr($rowStr, 0, 100);
                    break 2; // Break both loops
                }
            }

            // Check for REALISASI (might be in different row)
            if (strpos($rowStr, 'REALISASI') !== false) {
                $realisasiHeaderRow = $i;
                $result['debug'][] = "Found REALISASI header at row $i: " . substr($rowStr, 0, 100);
            }
        }

        // If no month header found, assume single total column (Column D)
        $useMonthlyColumns = ($monthHeaderRow !== -1);

        $result['debug']['monthHeaderRow'] = $monthHeaderRow;
        $result['debug']['realisasiHeaderRow'] = $realisasiHeaderRow;
        $result['debug']['useMonthlyColumns'] = $useMonthlyColumns;

        // If using monthly columns, map column indices to months
        $monthColumns = [];
        if ($useMonthlyColumns) {
            $monthRow = $data[$monthHeaderRow];
            $currentYear = $year ?: (int) date('Y');

            for ($col = 0; $col < count($monthRow); $col++) {
                $cell = strtoupper(trim($monthRow[$col] ?? ''));

                $monthIdx = $this->findMonthIndex($cell, $this->monthNames);
                if ($monthIdx === -1) {
                    $monthIdx = $this->findMonthIndex($cell, $this->monthNamesEng);
                }

                if ($monthIdx !== -1) {
                    $monthNum = $monthIdx + 1;

                    // Find REALISASI column for this month
                    // Search in the same row or nearby rows
                    $realisasiColIndex = -1;

                    // First, try to find REALISASI in the same row, after the month column
                    if ($realisasiHeaderRow === $monthHeaderRow) {
                        for ($searchCol = $col; $searchCol < count($monthRow); $searchCol++) {
                            $cellHeader = strtoupper(trim($monthRow[$searchCol] ?? ''));
                            if ($cellHeader === 'REALISASI') {
                                $realisasiColIndex = $searchCol;
                                break;
                            }
                        }
                    }
                    // Second, try to find REALISASI in the realisasiHeaderRow, same column position
                    elseif ($realisasiHeaderRow !== -1) {
                        $realisasiRow = $data[$realisasiHeaderRow];
                        for ($searchCol = $col; $searchCol < count($realisasiRow); $searchCol++) {
                            $cellHeader = strtoupper(trim($realisasiRow[$searchCol] ?? ''));
                            if ($cellHeader === 'REALISASI') {
                                $realisasiColIndex = $searchCol;
                                break;
                            }
                        }
                    }

                    // If REALISASI column found, add to mapping
                    if ($realisasiColIndex !== -1) {
                        $monthColumns[] = [
                            'month' => $monthNum,
                            'year' => $currentYear,
                            'colIndex' => $realisasiColIndex
                        ];
                        $result['debug'][] = "Mapped month " . date('F', mktime(0,0,0,$monthNum,1)) . " to column $realisasiColIndex (found at col $col)";
                    }
                }
            }

            $result['debug']['monthColumnsMapped'] = count($monthColumns);
        }

        $matchedKeywords = [];

        // Search Logic (The Loop)
        // Iterate through all rows, looking for keywords in Column B
        foreach ($data as $rowIndex => $row) {
            // Get Column B (Index 1) - this is where keywords are
            $columnB = isset($row[1]) ? strtoupper(trim($row[1])) : '';

            // Skip empty rows
            if (empty($columnB)) {
                continue;
            }

            // Check if Column B matches any key in companyMap
            $companyId = null;
            foreach ($companyMap as $keyword => $id) {
                if ($columnB === $keyword) {
                    $companyId = $id;
                    $matchedKeywords[] = [
                        'row' => $rowIndex,
                        'keyword' => $keyword,
                        'company_id' => $id,
                    ];
                    $result['debug'][] = "Matched keyword '$keyword' at row $rowIndex (Column B)";
                    break;
                }
            }

            // If keyword matched, process the row
            if ($companyId !== null) {
                // If using monthly columns, process for each month
                if ($useMonthlyColumns) {
                    foreach ($monthColumns as $mc) {
                        // Get value from REALISASI column for this month
                        $cellValue = $row[$mc['colIndex']] ?? null;
                        $amount = $this->parseAmount($cellValue);

                        if ($amount > 0) {
                            $monthName = date('F', mktime(0,0,0,$mc['month'],1));
                            $result['debug'][] = "Processing $columnB for $monthName: column {$mc['colIndex']}, raw value: " . var_export($cellValue, true) . ", parsed amount: " . number_format($amount, 0, ',', '.');
                        }

                        if ($amount <= 0) continue;

                        // Use date for this month (e.g., 2026-01-01, 2026-02-01, etc.)
                        $entryDate = sprintf('%04d-%02d-01', $mc['year'], $mc['month']);

                        // Check if record exists and update, or insert new
                        $existing = $this->realizationModel
                            ->where('company_id', $companyId)
                            ->where('date', $entryDate)
                            ->where('description', 'Google Sheets Sync')
                            ->first();

                        if ($existing) {
                            // Update existing record
                            $this->realizationModel->update($existing['id'], [
                                'amount' => $amount,
                            ]);
                            $monthName = date('F', mktime(0,0,0,$mc['month'],1));
                            $result['debug'][] = "Row $rowIndex: Updated $columnB for $monthName (ID: $companyId) with amount: " . number_format($amount, 0, ',', '.');
                        } else {
                            // Insert new record
                            $this->realizationModel->insert([
                                'company_id' => $companyId,
                                'date' => $entryDate,
                                'amount' => $amount,
                                'description' => 'Google Sheets Sync',
                            ]);
                            $monthName = date('F', mktime(0,0,0,$mc['month'],1));
                            $result['debug'][] = "Row $rowIndex: Inserted $columnB for $monthName (ID: $companyId) with amount: " . number_format($amount, 0, ',', '.');
                        }

                        $result['count']++;
                    }
                } else {
                    // Not using monthly columns (fallback to single column)
                    // Get value from Column D (Index 3) - This is Realization/Nominal
                    $columnD = $row[3] ?? null;

                    // Clean value: remove 'Rp', dots, commas
                    $amount = $this->parseAmount($columnD);

                    if ($amount <= 0) {
                        // Log zero or negative amounts
                        $result['debug'][] = "Row $rowIndex: Matched '$columnB' but amount is $columnD";
                        continue;
                    }

                    // Update/Insert record into database
                    // Using monthly date (first day of month from the sheet year)
                    $entryDate = sprintf('%04d-01-01', $year);

                    // Check if record exists and update, or insert new
                    $existing = $this->realizationModel
                        ->where('company_id', $companyId)
                        ->where('date', $entryDate)
                        ->where('description', 'Google Sheets Sync')
                        ->first();

                    if ($existing) {
                        // Update existing record
                        $this->realizationModel->update($existing['id'], [
                            'amount' => $amount,
                        ]);
                        $result['debug'][] = "Row $rowIndex: Updated $columnB (ID: $companyId) with amount: " . number_format($amount, 0, ',', '.');
                    } else {
                        // Insert new record
                        $this->realizationModel->insert([
                            'company_id' => $companyId,
                            'date' => $entryDate,
                            'amount' => $amount,
                            'description' => 'Google Sheets Sync Summary',
                        ]);
                        $result['debug'][] = "Row $rowIndex: Inserted $columnB (ID: $companyId) with amount: " . number_format($amount, 0, ',', '.');
                    }

                    $result['count']++;
                }
            }
        }

        $result['debug']['matchedKeywords'] = $matchedKeywords;
        $result['debug']['totalMatched'] = count($matchedKeywords);

        return $result;
    }

    protected function parseIndonesianDate(string $cellDate): ?int
    {
        if (empty($cellDate)) return null;

        // Quick check with strtotime first
        $ts = strtotime($cellDate);
        if ($ts && (int) date('Y', $ts) >= 2020) {
            return $ts;
        }

        // Map Indonesian month names to English
        $indoMonths = [
            'januari' => 'january', 'jan' => 'january',
            'februari' => 'february', 'feb' => 'february',
            'maret' => 'march', 'mar' => 'march',
            'april' => 'april', 'apr' => 'april',
            'mei' => 'may',
            'juni' => 'june', 'jun' => 'june',
            'juli' => 'july', 'jul' => 'july',
            'agustus' => 'august', 'agu' => 'august', 'agt' => 'august',
            'september' => 'september', 'sep' => 'september',
            'oktober' => 'october', 'okt' => 'october',
            'november' => 'november', 'nov' => 'november',
            'desember' => 'december', 'des' => 'december',
        ];

        $normalized = strtolower(trim($cellDate));
        foreach ($indoMonths as $indo => $eng) {
            if (str_contains($normalized, $indo)) {
                $normalizedEng = str_replace($indo, $eng, $normalized);
                $ts = strtotime($normalizedEng);
                if ($ts && (int) date('Y', $ts) >= 2020) {
                    return $ts;
                }
            }
        }

        return null;
    }

    protected function parseDailyCompanySheet($worksheet, string $companyCode): array
    {
        $result = ['count' => 0, 'debug' => []];
        $data = $worksheet->toArray();

        $companies = $this->companyModel->getActiveCompanies();
        $companyMap = [];
        foreach ($companies as $comp) {
            $companyMap[strtoupper($comp['code'])] = $comp['id'];
        }

        $companyId = $companyMap[strtoupper($companyCode)] ?? null;
        if (!$companyId) {
            $result['debug']['error'] = "Company not found for code: {$companyCode}";
            return $result;
        }

        // 1. Find Header Row and identify column indices strictly for "REVENUE"
        $revenueColIndices = [];
        $headerRowIndex = -1;

        for ($i = 0; $i < min(15, count($data)); $i++) {
            $row = $data[$i];
            if (!$row) continue;

            foreach ($row as $colIdx => $cell) {
                $cellStr = strtoupper(trim((string) $cell));
                if ($cellStr === 'REVENUE' || str_contains($cellStr, 'REVENUE')) {
                    if (str_contains($cellStr, 'TOTAL REVENUE')) {
                        continue;
                    }
                    $revenueColIndices[] = $colIdx;
                    $headerRowIndex = $i;
                }
            }
            if (!empty($revenueColIndices)) {
                break;
            }
        }

        $revenueColIndices = array_values(array_unique($revenueColIndices));
        $useRevenueHeaderOnly = !empty($revenueColIndices);
        $result['debug']['revenueColIndices'] = $revenueColIndices;
        $result['debug']['headerRowIndex'] = $headerRowIndex;

        $dailyTotals = [];

        foreach ($data as $rowIndex => $row) {
            if (!$row || empty($row[0])) {
                continue;
            }

            // Skip header rows
            if ($headerRowIndex !== -1 && $rowIndex <= $headerRowIndex) {
                continue;
            }

            $cellDate = trim((string) $row[0]);
            
            // Skip total rows at the bottom
            if (str_contains(strtoupper($cellDate), 'TOTAL')) {
                continue;
            }

            $timestamp = $this->parseIndonesianDate($cellDate);
            if (!$timestamp) {
                continue;
            }

            $year = (int) date('Y', $timestamp);
            if ($year < 2020) {
                continue;
            }

            $formattedDate = date('Y-m-d', $timestamp);

            $rowSum = 0;
            if ($useRevenueHeaderOnly) {
                // Sum ONLY columns that have "REVENUE" header
                foreach ($revenueColIndices as $colIdx) {
                    $val = $row[$colIdx] ?? null;
                    $amt = $this->parseAmount($val);
                    if ($amt > 0) {
                        $rowSum += $amt;
                    }
                }
            } else {
                // Fallback if no "REVENUE" header found
                for ($col = 1; $col < count($row); $col++) {
                    $val = $row[$col] ?? null;
                    $amt = $this->parseAmount($val);
                    if ($amt > 1000) {
                        $rowSum += $amt;
                    }
                }
            }

            if ($rowSum > 0) {
                if (!isset($dailyTotals[$formattedDate])) {
                    $dailyTotals[$formattedDate] = 0;
                }
                $dailyTotals[$formattedDate] += $rowSum;
            }
        }

        foreach ($dailyTotals as $entryDate => $totalAmount) {
            $this->realizationModel->insert([
                'company_id' => $companyId,
                'date' => $entryDate,
                'amount' => $totalAmount,
                'description' => 'Google Sheets Sync Daily',
            ]);
            $result['count']++;
        }

        return $result;
    }
}