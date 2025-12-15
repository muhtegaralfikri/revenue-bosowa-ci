<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\RevenueTargetModel;
use App\Models\RevenueRealizationModel;

class DashboardController extends BaseController
{
    protected $companyModel;
    protected $targetModel;
    protected $realizationModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->targetModel = new RevenueTargetModel();
        $this->realizationModel = new RevenueRealizationModel();
    }

    public function index()
    {
        $year = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month') ?? date('n');

        $companies = $this->companyModel->getActiveCompanies();
        
        // Get targets and realizations for the selected month
        $targets = $this->targetModel->getTargetsByYearMonth($year, $month);
        $realizations = $this->realizationModel->getRealizationsByYearMonth($year, $month);

        // Map data by company
        $targetsByCompany = [];
        foreach ($targets as $target) {
            $targetsByCompany[$target['company_id']] = $target['target_amount'];
        }

        $realizationsByCompany = [];
        foreach ($realizations as $realization) {
            $realizationsByCompany[$realization['company_id']] = $realization['total_amount'];
        }

        // Prepare summary data
        $summaryData = [];
        $totalTarget = 0;
        $totalRealization = 0;

        foreach ($companies as $company) {
            $target = $targetsByCompany[$company['id']] ?? 0;
            $realization = $realizationsByCompany[$company['id']] ?? 0;
            $percentage = $target > 0 ? ($realization / $target) * 100 : 0;

            $summaryData[] = [
                'company' => $company,
                'target' => $target,
                'realization' => $realization,
                'percentage' => $percentage,
            ];

            $totalTarget += $target;
            $totalRealization += $realization;
        }

        // Get monthly data for chart
        $monthlyData = $this->getMonthlyChartData($year, $companies);

        // Get daily trend data
        $dailyData = $this->getDailyTrendData($year, $month, $companies);

        $data = [
            'title' => 'Dashboard',
            'year' => $year,
            'month' => $month,
            'companies' => $companies,
            'summaryData' => $summaryData,
            'totalTarget' => $totalTarget,
            'totalRealization' => $totalRealization,
            'totalPercentage' => $totalTarget > 0 ? ($totalRealization / $totalTarget) * 100 : 0,
            'monthlyData' => $monthlyData,
            'dailyData' => $dailyData,
            'years' => range(date('Y') - 2, date('Y') + 1),
            'months' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
        ];

        return view('dashboard/index', $data);
    }

    private function getMonthlyChartData($year, $companies)
    {
        $monthlyRealizations = $this->realizationModel->getMonthlyTotalsByCompany($year);
        
        $chartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'datasets' => [],
        ];

        $colors = [
            'BBI' => ['bg' => 'rgba(59, 130, 246, 0.8)', 'border' => 'rgb(59, 130, 246)'],
            'BBA' => ['bg' => 'rgba(16, 185, 129, 0.8)', 'border' => 'rgb(16, 185, 129)'],
            'JAPELIN' => ['bg' => 'rgba(249, 115, 22, 0.8)', 'border' => 'rgb(249, 115, 22)'],
        ];

        foreach ($companies as $company) {
            $data = [];
            for ($m = 1; $m <= 12; $m++) {
                $data[] = $monthlyRealizations[$company['id']][$m] ?? 0;
            }

            $color = $colors[$company['code']] ?? ['bg' => 'rgba(107, 114, 128, 0.8)', 'border' => 'rgb(107, 114, 128)'];

            $chartData['datasets'][] = [
                'label' => $company['code'],
                'data' => $data,
                'backgroundColor' => $color['bg'],
                'borderColor' => $color['border'],
                'borderWidth' => 1,
            ];
        }

        return $chartData;
    }

    public function debug()
    {
        $year = date('Y');
        $data = $this->realizationModel->getMonthlyTotalsByCompany($year);
        
        echo "<pre>";
        echo "Monthly Totals for $year:\n";
        print_r($data);
        
        // Also check raw data
        $raw = $this->realizationModel
            ->where('YEAR(date)', $year)
            ->where('MONTH(date) IN (11, 12)')
            ->findAll();
        echo "\nRaw Nov/Dec data:\n";
        print_r($raw);
        echo "</pre>";
    }

    private function getDailyTrendData($year, $month, $companies)
    {
        $dailyRealizations = $this->realizationModel->getDailyRealizations($year, $month);
        
        // Use date('t') instead of cal_days_in_month() - no calendar extension required
        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $labels = range(1, $daysInMonth);

        $dataByCompany = [];
        foreach ($companies as $company) {
            $dataByCompany[$company['id']] = array_fill(0, $daysInMonth, 0);
        }

        foreach ($dailyRealizations as $realization) {
            $day = (int) date('j', strtotime($realization['date']));
            $dataByCompany[$realization['company_id']][$day - 1] = (float) $realization['amount'];
        }

        $colors = [
            'BBI' => 'rgb(59, 130, 246)',
            'BBA' => 'rgb(16, 185, 129)',
            'JAPELIN' => 'rgb(249, 115, 22)',
        ];

        $datasets = [];
        foreach ($companies as $company) {
            $datasets[] = [
                'label' => $company['code'],
                'data' => array_values($dataByCompany[$company['id']]),
                'borderColor' => $colors[$company['code']] ?? 'rgb(107, 114, 128)',
                'tension' => 0.1,
                'fill' => false,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }
}
