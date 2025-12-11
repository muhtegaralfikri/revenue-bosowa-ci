<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e468c;
            --surface-100: #f1f5f9;
            --surface-500: #64748b;
            --surface-700: #334155;
            --surface-900: #0f172a;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Sora', sans-serif;
            background: var(--surface-100);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .error-container {
            text-align: center;
            max-width: 500px;
        }
        
        .error-icon {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--surface-900);
            margin-bottom: 1rem;
        }
        
        .error-message {
            font-size: 1rem;
            color: var(--surface-500);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary-color);
            color: #fff;
            padding: 0.85rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        
        .btn-home:hover {
            background: #163a73;
            color: #fff;
        }
        
        @media (max-width: 576px) {
            .error-code {
                font-size: 4rem;
            }
            
            .error-title {
                font-size: 1.25rem;
            }
            
            .error-message {
                font-size: 0.9rem;
            }
            
            .error-icon {
                font-size: 4rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="bi bi-exclamation-triangle error-icon"></i>
        <div class="error-code">404</div>
        <h1 class="error-title">Halaman Tidak Ditemukan</h1>
        <p class="error-message">
            Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan. 
            Silakan kembali ke halaman utama.
        </p>
        <a href="/dashboard" class="btn-home">
            <i class="bi bi-house"></i>
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>
