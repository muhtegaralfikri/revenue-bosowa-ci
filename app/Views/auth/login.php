<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Revenue Monitoring Bosowa</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    
    <!-- Google Fonts - Sora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1e468c;
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
            font-family: 'Sora', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: #f1f5f9;
        }
        
        .auth-page__inner {
            width: min(1100px, 100%);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            background: linear-gradient(135deg, #eef5ff 0%, #fdf7ff 100%);
            border-radius: 1.5rem;
            padding: 3rem;
            box-shadow: 0 25px 60px -35px rgba(15, 23, 42, 0.35);
        }
        
        .auth-page__hero {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--surface-500);
        }
        
        .auth-page__hero h1 {
            font-size: clamp(2rem, 4vw, 2.75rem);
            line-height: 1.2;
            margin: 0;
            color: var(--surface-900);
        }
        
        .subtitle {
            color: var(--surface-600);
            max-width: 36ch;
            line-height: 1.6;
        }
        
        .hero-stats {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            padding: 0;
            margin: 1rem 0 0;
        }
        
        .hero-stats li {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 0.85rem;
            padding: 1rem;
            border: 1px solid rgba(15, 23, 42, 0.05);
        }
        
        .hero-stats .stat-value {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        
        .hero-stats .stat-desc {
            font-size: 0.8rem;
            color: var(--surface-600);
            margin: 0;
        }
        
        /* Auth Card */
        .auth-card {
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px -35px rgba(15, 23, 42, 0.45);
            padding: 2.5rem;
            position: relative;
        }
        
        .back-to-home {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: #fff;
            border-radius: 50%;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.2s;
        }
        
        .back-to-home:hover {
            background: #163666;
            color: #fff;
            transform: scale(1.05);
        }
        
        .auth-card .card-eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.15em;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--surface-500);
            margin-bottom: 0.5rem;
        }
        
        .auth-card h2 {
            font-size: 1.75rem;
            color: var(--surface-900);
            margin: 0 0 1rem 0;
        }
        
        .auth-card .card-subtitle {
            color: var(--surface-600);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        
        .form-field {
            margin-bottom: 1rem;
        }
        
        .form-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.35rem;
            color: var(--surface-700);
            font-size: 0.9rem;
        }
        
        .form-field .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        
        .form-field .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 70, 140, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
        }
        
        .btn-primary:hover {
            background-color: #163666;
            border-color: #163666;
        }
        
        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 1.5rem 0;
        }
        
        .help-text {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: var(--surface-600);
        }
        
        .help-text i {
            margin-right: 0.5rem;
        }
        
        .alert {
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .auth-page__inner {
                padding: 1.5rem;
                gap: 1.5rem;
            }
            
            .auth-page__hero h1 {
                font-size: 1.5rem;
            }
            
            .subtitle {
                font-size: 0.9rem;
            }
            
            .hero-stats {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .hero-stats li {
                padding: 0.75rem;
            }
            
            .hero-stats .stat-value {
                font-size: 1rem;
            }
            
            .hero-stats .stat-desc {
                font-size: 0.75rem;
            }
            
            .auth-card {
                padding: 1.5rem;
            }
            
            .auth-card h2 {
                font-size: 1.4rem;
            }
            
            .auth-card .card-subtitle {
                font-size: 0.85rem;
            }
            
            .form-field label {
                font-size: 0.85rem;
            }
            
            .form-field .form-control {
                padding: 0.65rem 0.85rem;
                font-size: 0.9rem;
            }
            
            .btn-primary {
                padding: 0.65rem 1rem;
            }
        }
        
        @media (max-width: 576px) {
            body {
                padding: 0.75rem;
            }
            
            .auth-page__inner {
                padding: 1.25rem;
                border-radius: 1rem;
            }
            
            .eyebrow {
                font-size: 0.65rem;
            }
            
            .auth-page__hero h1 {
                font-size: 1.3rem;
            }
            
            .auth-card {
                padding: 1.25rem;
                border-radius: 0.85rem;
            }
            
            .auth-card h2 {
                font-size: 1.25rem;
            }
            
            .help-text {
                font-size: 0.8rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="auth-page__inner">
        <div class="auth-page__hero">
            <p class="eyebrow">Revenue Monitoring System</p>
            <h1>Pantau realisasi pendapatan secara real-time.</h1>
            <p class="subtitle">
                Platform terintegrasi untuk monitoring target & realisasi pendapatan 
                Bosowa Bandar Group. Data akurat, laporan lengkap.
            </p>
            
            <ul class="hero-stats">
                <li>
                    <div class="stat-value">3</div>
                    <p class="stat-desc">Perusahaan dalam satu dashboard terpadu.</p>
                </li>
                <li>
                    <div class="stat-value">24/7</div>
                    <p class="stat-desc">Pemantauan target & realisasi harian.</p>
                </li>
                <li>
                    <div class="stat-value"><i class="bi bi-graph-up"></i></div>
                    <p class="stat-desc">Analisis tren pendapatan bulanan.</p>
                </li>
            </ul>
        </div>
        
        <div class="auth-card">
            <a href="/dashboard" class="back-to-home" title="Kembali ke Beranda">
                <i class="bi bi-arrow-left"></i>
            </a>
            <p class="card-eyebrow">Masuk ke akun Anda</p>
            <h2>Login Sistem</h2>
            <p class="card-subtitle">Gunakan kredensial yang diberikan admin untuk mengakses dashboard.</p>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success mb-3">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="/login" method="post">
                <?= csrf_field() ?>
                
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Masukkan email" value="<?= old('email') ?>" required>
                </div>
                
                <div class="form-field">
                    <label for="password">Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Masukkan password" required style="padding-right: 40px;">
                        <span class="position-absolute top-50 end-0 translate-middle-y me-3" id="togglePassword" style="cursor: pointer;">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                </button>
            </form>
            
            <div class="divider"></div>
            
            <div class="help-text">
                <i class="bi bi-info-circle"></i>
                Perlu akses baru? Hubungi administrator Bosowa Bandar Group.
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('email').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const icon = togglePassword.querySelector('i');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // toggle the icon
            if (type === 'password') {
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>
