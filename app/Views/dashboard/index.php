<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
    @media (max-width: 768px) {
        .filter-section {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-item {
            justify-content: space-between;
        }
        
        .filter-item select {
            flex: 1;
            width: auto;
        }
        
        .summary-cards {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .chart-card .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .company-filter {
            width: 100%;
        }
        
        .chart-wrapper {
            height: 220px;
        }
    }
    
    @media (max-width: 576px) {
        .filter-section {
            gap: 0.5rem;
        }
        
        .filter-item label {
            font-size: 0.85rem;
        }
        
        .filter-item select {
            font-size: 0.85rem;
            padding: 0.4rem 0.6rem;
        }
        
        .summary-card .card-body {
            padding: 0.85rem;
        }
        
        .realisasi-value {
            font-size: 1.1rem;
        }
        
        .today-info {
            font-size: 0.8rem;
        }
        
        .chart-card .card-header {
            padding: 0.85rem;
        }
        
        .chart-card .card-title {
            font-size: 0.95rem;
        }
        
        .chart-card .card-body {
            padding: 0.85rem;
        }
        
        .chart-wrapper {
            height: 200px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Filter Section -->
<div class="filter-section">
    <div class="filter-item">
        <label>Bulan:</label>
        <select id="filterMonth" onchange="applyFilter()">
            <?php foreach ($months as $num => $name): ?>
                <option value="<?= $num ?>" <?= $month == $num ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-item">
        <label>Tahun:</label>
        <select id="filterYear" onchange="applyFilter()">
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Summary Cards -->
<div class="summary-cards">
    <?php 
    $colorClasses = [
        'BBI' => 'text-blue',
        'BBA' => 'text-green',
        'JAPELIN' => 'text-orange'
    ];
    foreach ($summaryData as $data): 
        $colorClass = $colorClasses[$data['company']['code']] ?? 'text-blue';
        $percentClass = $data['percentage'] >= 100 ? 'up' : 'down';
        $percentIcon = $data['percentage'] >= 100 ? 'bi-arrow-up' : 'bi-arrow-down';
    ?>
        <div class="card summary-card">
            <div class="card-body">
                <div class="company-title">
                    <span class="company-name-full"><?= $data['company']['name'] ?></span>
                    <span class="company-name-short"><?= $data['company']['code'] ?></span>
                </div>
                <div class="realisasi-value <?= $colorClass ?>">
                    Rp <?= number_format($data['realization'], 0, ',', '.') ?>
                </div>
                <div class="today-info">
                    <span>Bulan ini:</span>
                    <span class="percentage <?= $percentClass ?>">
                        <?= number_format($data['percentage'], 1) ?>%
                    </span>
                    <i class="bi <?= $percentIcon ?> percentage <?= $percentClass ?>"></i>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Charts Section -->
<div class="row">
    <!-- Daily Trend Chart -->
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header">
                <span class="card-title">Tren Realisasi Harian</span>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Comparison Chart -->
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header">
                <span class="card-title">Perbandingan Target vs Realisasi (<?= $year ?>)</span>
                <select id="companyFilter" class="form-select company-filter" onchange="updateComparisonChart()">
                    <option value="">Semua Entity</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['code'] ?>"><?= $company['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="comparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Filter function
function applyFilter() {
    const month = document.getElementById('filterMonth').value;
    const year = document.getElementById('filterYear').value;
    window.location.href = `/dashboard?month=${month}&year=${year}`;
}

// Daily Trend Chart
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
const dailyData = <?= json_encode($dailyData) ?>;

const dailyChart = new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: dailyData.labels,
        datasets: dailyData.datasets.map(ds => ({
            ...ds,
            tension: 0.35,
            fill: false,
            pointRadius: 3,
            borderWidth: 2,
        }))
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const value = ctx.parsed.y ?? 0;
                        return `${ctx.dataset.label}: Rp ${(value / 1000000).toLocaleString('id-ID')} Jt`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                title: { display: true, text: 'Tanggal' }
            },
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Juta Rupiah' },
                ticks: {
                    callback: function(value) {
                        return (value / 1000000).toLocaleString('id-ID') + ' Jt';
                    }
                }
            }
        }
    }
});

// Monthly Comparison Data
const monthlyData = <?= json_encode($monthlyData) ?>;
console.log('monthlyData:', monthlyData);
const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');

// Calculate combined target and realisasi
function getComparisonData(companyCode = null) {
    console.log('getComparisonData called with:', companyCode);
    console.log('monthlyData.datasets:', monthlyData.datasets);
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    
    if (companyCode) {
        // Filter by specific company
        const dataset = monthlyData.datasets.find(ds => ds.label === companyCode);
        if (!dataset) return null;
        
        return {
            labels: labels,
            datasets: [
                {
                    label: 'Target',
                    data: Array(12).fill(0), // We don't have target data in monthlyData
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Realisasi',
                    data: dataset.data,
                    backgroundColor: 'rgba(34, 197, 94, 1)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1,
                }
            ]
        };
    }
    
    // Combine all companies
    const combinedRealisasi = Array(12).fill(0);
    monthlyData.datasets.forEach(ds => {
        console.log('Processing dataset:', ds.label, ds.data);
        ds.data.forEach((val, idx) => {
            combinedRealisasi[idx] += parseFloat(val) || 0;
        });
    });
    console.log('combinedRealisasi:', combinedRealisasi);
    
    return {
        labels: labels,
        datasets: [
            {
                label: 'Target',
                data: Array(12).fill(0),
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1,
            },
            {
                label: 'Realisasi',
                data: combinedRealisasi,
                backgroundColor: 'rgba(34, 197, 94, 1)',
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 1,
            }
        ]
    };
}

let comparisonChart = new Chart(comparisonCtx, {
    type: 'bar',
    data: getComparisonData(),
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const value = ctx.parsed.y ?? 0;
                        return `${ctx.dataset.label}: Rp ${(value / 1000000).toLocaleString('id-ID')} Jt`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                title: { display: true, text: 'Bulan' }
            },
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Juta Rupiah' },
                ticks: {
                    callback: function(value) {
                        return (value / 1000000).toLocaleString('id-ID') + ' Jt';
                    }
                }
            }
        }
    }
});

function updateComparisonChart() {
    const companyCode = document.getElementById('companyFilter').value || null;
    const newData = getComparisonData(companyCode);
    comparisonChart.data = newData;
    comparisonChart.update();
}
</script>
<?= $this->endSection() ?>
