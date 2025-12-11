<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Revenue Monitoring' ?> - Bosowa Bandar Group</title>
    
    <!-- Google Fonts - Sora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        
        html {
            height: 100%;
        }
        
        body {
            font-family: 'Sora', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--surface-100);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            text-decoration: none;
            height: 40px;
        }
        
        .brand-logo {
            height: 100%;
            width: auto;
            object-fit: contain;
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
        
        /* Footer */
        .app-footer {
            text-align: center;
            font-size: 0.9rem;
            color: #fff;
            padding: 1rem;
            background: var(--primary-color);
            margin-top: auto;
            flex-shrink: 0;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
        }
        
        /* Slide Menu Overlay */
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .menu-overlay.active {
            display: block;
        }
        
        /* Slide Menu */
        .slide-menu {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100%;
            background: #fff;
            z-index: 1001;
            transition: left 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .slide-menu.active {
            left: 0;
        }
        
        .slide-menu-header {
            background: var(--primary-color);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .slide-menu-header .brand-logo {
            height: 32px;
        }
        
        .slide-menu-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
        }
        
        .slide-menu-nav {
            padding: 1rem 0;
        }
        
        .slide-menu-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1.25rem;
            color: var(--surface-700);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .slide-menu-nav a:hover {
            background: var(--surface-100);
        }
        
        .slide-menu-nav a.active {
            background: var(--surface-100);
            color: var(--primary-color);
            border-left: 3px solid var(--primary-color);
        }
        
        .slide-menu-nav a i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .slide-menu-divider {
            height: 1px;
            background: var(--surface-200);
            margin: 0.5rem 1.25rem;
        }
        
        /* Main Content */
        .main-content {
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
            flex: 1;
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
        
        .summary-card .company-name-short {
            display: none;
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
            
            .chart-wrapper {
                height: 240px;
            }
        }
        
        @media (max-width: 768px) {
            .navbar-shell {
                padding: 0 1rem;
                height: 56px;
                justify-content: space-between;
            }
            
            .brand {
                height: 32px;
            }
            
            /* Hide desktop menu on mobile */
            .desktop-menu {
                display: none;
            }
            
            /* Show hamburger button on mobile */
            .mobile-menu-btn {
                display: block;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .summary-card .card-body {
                padding: 1rem;
            }
            
            .summary-card .company-title {
                font-size: 0.9rem;
            }
            
            .summary-card .company-name-full {
                display: none;
            }
            
            .summary-card .company-name-short {
                display: inline;
            }
            
            .summary-card .realisasi-value {
                font-size: 1.1rem;
            }
            
            .summary-card .today-info {
                font-size: 0.8rem;
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
            
            .chart-card .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .chart-card .card-header .card-title {
                font-size: 0.9rem;
            }
            
            .company-filter {
                width: 100%;
            }
            
            .chart-wrapper {
                height: 220px;
            }
            
            /* Table responsive */
            .table th, .table td {
                font-size: 0.85rem;
                padding: 0.75rem 0.5rem;
            }
            
            .table .px-4 {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            
            .table .pe-4 {
                padding-right: 0.75rem !important;
            }
            
            /* Page headers */
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch !important;
            }
            
            .d-flex.justify-content-between .d-flex.gap-2 {
                flex-wrap: wrap;
            }
            
            .d-flex.justify-content-between .btn {
                flex: 1;
                min-width: 120px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0.75rem;
            }
            
            .card {
                border-radius: 6px;
            }
            
            .summary-card .realisasi-value {
                font-size: 1rem;
            }
            
            .chart-wrapper {
                height: 200px;
            }
            
            .filter-item label {
                font-size: 0.85rem;
            }
            
            .filter-item select {
                font-size: 0.85rem;
                padding: 0.4rem 1.5rem 0.4rem 0.5rem;
            }
            
            h4 {
                font-size: 1.1rem;
            }
            
            /* Hide some table columns on very small screens */
            .table .d-none-xs {
                display: none;
            }
        }
    </style>
    
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <!-- Navbar -->
    <header class="navbar-shell">
        <a href="/dashboard" class="brand">
            <img src="/assets/images/logo.png" alt="Bosowa" class="brand-logo">
        </a>
        
        <nav class="nav-menu desktop-menu">
            <a href="/dashboard" class="<?= uri_string() == 'dashboard' ? 'active' : '' ?>">Beranda</a>
            <a href="/input" class="<?= str_starts_with(uri_string(), 'input') ? 'active' : '' ?>">Input</a>
            <a href="/users" class="<?= str_starts_with(uri_string(), 'users') ? 'active' : '' ?>">Users</a>
            <a href="/sync" class="<?= str_starts_with(uri_string(), 'sync') ? 'active' : '' ?>">Sync</a>
            <a href="/logout">Log Out</a>
        </nav>
        
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleMenu()">
            <i class="bi bi-list"></i>
        </button>
    </header>
    
    <!-- Slide Menu Overlay -->
    <div class="menu-overlay" id="menuOverlay" onclick="toggleMenu()"></div>
    
    <!-- Slide Menu -->
    <nav class="slide-menu" id="slideMenu">
        <div class="slide-menu-header">
            <img src="/assets/images/logo.png" alt="Bosowa" class="brand-logo">
            <button class="slide-menu-close" onclick="toggleMenu()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="slide-menu-nav">
            <a href="/dashboard" class="<?= uri_string() == 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-house"></i>
                <span>Beranda</span>
            </a>
            <a href="/input" class="<?= str_starts_with(uri_string(), 'input') ? 'active' : '' ?>">
                <i class="bi bi-plus-circle"></i>
                <span>Input Revenue</span>
            </a>
            <a href="/users" class="<?= str_starts_with(uri_string(), 'users') ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
            <a href="/sync" class="<?= str_starts_with(uri_string(), 'sync') ? 'active' : '' ?>">
                <i class="bi bi-cloud-download"></i>
                <span>Sync Data</span>
            </a>
            <div class="slide-menu-divider"></div>
            <a href="/logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Log Out</span>
            </a>
        </div>
    </nav>
    
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
    
    <!-- Footer -->
    <footer class="app-footer">
        © <?= date('Y') ?> Bosowa Bandar Group. All rights reserved.
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Slide Menu JS -->
    <script>
    function toggleMenu() {
        const slideMenu = document.getElementById('slideMenu');
        const menuOverlay = document.getElementById('menuOverlay');
        slideMenu.classList.toggle('active');
        menuOverlay.classList.toggle('active');
        document.body.style.overflow = slideMenu.classList.contains('active') ? 'hidden' : '';
    }
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
