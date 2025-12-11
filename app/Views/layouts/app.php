<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Revenue Monitoring' ?> - Bosowa Bandar Group</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #1e468c;
            --primary-dark: #163666;
            --accent-blue: #3B82F6;
            --accent-green: #22C55E;
            --accent-orange: #F97316;
            --surface-50: #f8fafc;
            --surface-100: #f1f5f9;
            --surface-200: #e2e8f0;
            --surface-500: #64748b;
            --surface-600: #475569;
            --surface-700: #334155;
            --surface-900: #0f172a;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--surface-100);
            min-height: 100vh;
        }
        
        /* Navbar */
        .navbar-shell {
            background: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.75rem;
            height: 60px;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .brand i {
            font-size: 1.5rem;
        }
        
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-menu a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        
        /* Main Content */
        .main-content {
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Cards */
        .card {
            background: #fff;
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--surface-700);
            margin-bottom: 0;
        }
        
        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .summary-card .card-body {
            padding: 1.25rem;
        }
        
        .summary-card .company-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--surface-700);
            margin-bottom: 0.75rem;
        }
        
        .summary-card .realisasi-value {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .summary-card .realisasi-value.text-blue { color: var(--accent-blue); }
        .summary-card .realisasi-value.text-green { color: var(--accent-green); }
        .summary-card .realisasi-value.text-orange { color: var(--accent-orange); }
        
        .summary-card .today-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--surface-500);
        }
        
        .summary-card .percentage {
            font-weight: 600;
        }
        
        .summary-card .percentage.up { color: var(--accent-green); }
        .summary-card .percentage.down { color: #dc3545; }
        
        /* Filter Section */
        .filter-section {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }
        
        .filter-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-item label {
            font-weight: 500;
            color: var(--surface-700);
            font-size: 0.9rem;
        }
        
        .filter-item select {
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border: 1px solid var(--surface-200);
            border-radius: 6px;
            font-size: 0.9rem;
            background-color: #fff;
            min-width: 140px;
        }
        
        /* Chart Section */
        .chart-card {
            margin-bottom: 1rem;
        }
        
        .chart-card .card-header {
            background: transparent;
            border-bottom: 1px solid var(--surface-100);
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chart-card .card-body {
            padding: 1rem 1.25rem;
        }
        
        .chart-wrapper {
            width: 100%;
            height: 280px;
            position: relative;
        }
        
        /* Company Filter in Chart */
        .company-filter {
            min-width: 150px;
        }
        
        /* Alerts */
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .navbar-shell {
                padding: 0 1rem;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-item {
                justify-content: space-between;
            }
            
            .filter-item select {
                flex: 1;
            }
        }
    </style>
    
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <!-- Navbar -->
    <header class="navbar-shell">
        <a href="/dashboard" class="brand">
            <i class="bi bi-graph-up-arrow"></i>
            <span>BOSOWA</span>
        </a>
        
        <nav class="nav-menu">
            <a href="/dashboard" class="<?= uri_string() == 'dashboard' ? 'active' : '' ?>">Beranda</a>
            <a href="/targets" class="<?= str_starts_with(uri_string(), 'targets') ? 'active' : '' ?>">Target</a>
            <a href="/realizations" class="<?= str_starts_with(uri_string(), 'realizations') ? 'active' : '' ?>">Realisasi</a>
            <a href="/logout">Log Out</a>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?= $this->renderSection('content') ?>
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
