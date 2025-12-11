<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Revenue Monitoring Bosowa</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d3b66;
            --secondary-color: #1e5f8a;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #eef5ff 0%, #fdf7ff 100%);
        }
        
        .login-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 25px 60px -35px rgba(15, 23, 42, 0.35);
            overflow: hidden;
        }
        
        .login-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-hero h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .login-hero p {
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }
        
        .hero-stat {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 0.75rem;
            text-align: center;
        }
        
        .hero-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .hero-stat-label {
            font-size: 0.75rem;
            opacity: 0.8;
        }
        
        .login-form {
            padding: 3rem;
        }
        
        .login-form h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-form .subtitle {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 59, 102, 0.15);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        @media (max-width: 768px) {
            .login-hero {
                padding: 2rem;
            }
            
            .hero-stats {
                grid-template-columns: 1fr;
            }
            
            .login-form {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="row g-0">
                <div class="col-lg-6 login-hero">
                    <p class="eyebrow mb-3">Revenue Monitoring System</p>
                    <h1>Pantau realisasi pendapatan secara real-time.</h1>
                    <p>Platform terintegrasi untuk monitoring target & realisasi pendapatan Bosowa Bandar Group.</p>
                    
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-value">3</div>
                            <div class="hero-stat-label">Perusahaan</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">24/7</div>
                            <div class="hero-stat-label">Monitoring</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value"><i class="bi bi-graph-up"></i></div>
                            <div class="hero-stat-label">Trend Analysis</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 login-form">
                    <h2>Login Sistem</h2>
                    <p class="subtitle">Gunakan kredensial yang diberikan admin untuk mengakses dashboard.</p>
                    
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/login" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Masukkan email" value="<?= old('email') ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Masukkan password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <p class="text-muted small text-center mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Perlu akses baru? Hubungi administrator Bosowa Bandar Group.
                    </p>
                </div>
            </div>
        </div>
        
        <p class="text-center text-muted mt-4 small">
            &copy; <?= date('Y') ?> Bosowa Bandar Group. All rights reserved.
        </p>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Focus password field on Enter in email field
        document.getElementById('email').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });
    </script>
</body>
</html>
