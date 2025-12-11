<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Dashboard</h4>
        <p class="text-muted mb-0">Monitoring realisasi pendapatan Bosowa Bandar Group</p>
    </div>
    
    <!-- Filter -->
    <form class="d-flex gap-2" method="get">
        <select name="month" class="form-select" style="width: auto;">
            <?php foreach ($months as $num => $name): ?>
                <option value="<?= $num ?>" <?= $month == $num ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
        </select>
        <select name="year" class="form-select" style="width: auto;">
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-funnel"></i> Filter
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <?php foreach ($summaryData as $data): ?>
        <?php 
            $companyClass = strtolower($data['company']['code']);
            $percentClass = $data['percentage'] >= 100 ? 'success' : ($data['percentage'] >= 50 ? 'warning' : 'danger');
        ?>
        <div class="col-md-4">
            <div class="card stat-card <?= $percentClass ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="company-badge company-<?= $companyClass ?>"><?= $data['company']['code'] ?></span>
                        <span class="badge bg-<?= $percentClass ?>"><?= number_format($data['percentage'], 1) ?>%</span>
                    </div>
                    <h5 class="card-title mb-1"><?= $data['company']['name'] ?></h5>
                    <div class="stat-value">Rp <?= number_format($data['realization'], 0, ',', '.') ?></div>
                    <div class="stat-label">Target: Rp <?= number_format($data['target'], 0, ',', '.') ?></div>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-<?= $percentClass ?>" style="width: <?= min($data['percentage'], 100) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Total Summary -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center border-end">
                        <div class="stat-label">Total Target</div>
                        <div class="stat-value text-primary">Rp <?= number_format($totalTarget, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-md-4 text-center border-end">
                        <div class="stat-label">Total Realisasi</div>
                        <div class="stat-value text-success">Rp <?= number_format($totalRealization, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="stat-label">Persentase</div>
                        <div class="stat-value <?= $totalPercentage >= 100 ? 'text-success' : ($totalPercentage >= 50 ? 'text-warning' : 'text-danger') ?>">
                            <?= number_format($totalPercentage, 1) ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-3">
    <!-- Daily Trend -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i>Tren Realisasi Harian - <?= $months[$month] ?> <?= $year ?>
            </div>
            <div class="card-body">
                <canvas id="dailyChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Monthly Comparison -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Perbandingan Target vs Realisasi
            </div>
            <div class="card-body">
                <canvas id="comparisonChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Chart -->
<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar3 me-2"></i>Realisasi Bulanan <?= $year ?>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Daily Trend Chart
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'line',
    data: <?= json_encode($dailyData) ?>,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                    }
                }
            }
        }
    }
});

// Comparison Chart
const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
new Chart(comparisonCtx, {
    type: 'doughnut',
    data: {
        labels: ['Realisasi', 'Sisa Target'],
        datasets: [{
            data: [<?= $totalRealization ?>, <?= max(0, $totalTarget - $totalRealization) ?>],
            backgroundColor: ['#198754', '#e9ecef'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: <?= json_encode($monthlyData) ?>,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + (value / 1000000000).toFixed(1) + ' M';
                    }
                }
            }
        }
    }
});
</script>
<?= $this->endSection() ?>
