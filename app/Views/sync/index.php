<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
    .sync-container {
        display: flex;
        justify-content: center;
        padding: 2rem 0;
    }
    
    .sync-card {
        width: 100%;
        max-width: 500px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        max-width: 520px;
    }
    
    .sync-card .card-header {
        padding: 1.25rem;
        border-bottom: 1px solid var(--surface-100);
        text-align: center;
    }
    
    .sync-card .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--surface-900);
        margin: 0;
    }
    
    .sync-card .card-body {
        padding: 1.5rem;
        text-align: center;
    }
    
    .sync-icon {
        font-size: 4rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }
    
    .sync-status {
        margin-bottom: 1.5rem;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-enabled {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-disabled {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .sync-description {
        color: var(--surface-600);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }
    
    .btn-sync {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.85rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        color: #fff;
        background: var(--primary-color);
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
        position: relative;
        min-width: 190px;
    }
    
    .btn-sync:hover:not(:disabled) {
        background: #163a73;
    }
    
    .btn-sync:disabled {
        background: var(--surface-300);
        cursor: not-allowed;
    }
    
    @media (max-width: 576px) {
        .sync-container {
            padding: 1rem 0;
        }
        
        .sync-card .card-body {
            padding: 1.25rem;
        }
        
        .sync-icon {
            font-size: 3rem;
        }
        
        .sync-card {
            margin: 0 0.5rem;
        }
    }

    /* Debug box to prevent horizontal scroll on mobile */
    .debug-box {
        margin-top: 1.5rem;
        text-align: left;
        background: #f5f5f5;
        padding: 1rem;
        border-radius: 6px;
        font-size: 0.8rem;
        overflow-x: auto;
        max-width: 100%;
    }
    .debug-box pre {
        white-space: pre-wrap;
        word-break: break-word;
        margin: 0;
    }

    /* Loading spinner */
    .spinner {
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        display: none;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .btn-sync.loading .spinner {
        display: inline-block;
    }
    .btn-sync.loading .btn-label {
        opacity: 0.8;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="sync-container">
    <div class="sync-card">
        <div class="card-header">
            <h5 class="card-title">Google Sheets Sync</h5>
        </div>
        <div class="card-body">
            <i class="bi bi-cloud-download sync-icon"></i>
            
            <div class="sync-status">
                <?php if ($enabled): ?>
                    <span class="status-badge status-enabled">
                        <i class="bi bi-check-circle me-1"></i>Enabled
                    </span>
                <?php else: ?>
                    <span class="status-badge status-disabled">
                        <i class="bi bi-x-circle me-1"></i>Disabled
                    </span>
                <?php endif; ?>
            </div>
            
            <p class="sync-description">
                Sinkronisasi data realisasi revenue dari Google Spreadsheet ke database lokal.
                Data yang sudah ada dari sync sebelumnya akan dihapus dan diganti dengan data terbaru.
            </p>
            
            <?php if ($lastSync): ?>
            <div style="margin-bottom: 1rem; padding: 0.75rem; background: var(--surface-50); border-radius: 6px; font-size: 0.85rem;">
                <div><strong>Sync terakhir:</strong> <?= $lastSync ?></div>
                <div><strong>Status:</strong> 
                    <span style="color: <?= $lastStatus === 'success' ? '#10B981' : ($lastStatus === 'failed' ? '#EF4444' : '#6B7280') ?>">
                        <?= ucfirst($lastStatus) ?>
                    </span>
                </div>
                <div><strong>Records imported:</strong> <?= $lastCount ?></div>
                <div><strong>Auto-sync interval:</strong> <?= $syncInterval ?> menit</div>
            </div>
            <?php endif; ?>
            
            <form action="/sync/run" method="post" id="syncForm">
                <?= csrf_field() ?>
                <button type="submit" class="btn-sync" id="syncButton" <?= !$enabled ? 'disabled' : '' ?>>
                    <span class="btn-label"><i class="bi bi-arrow-repeat"></i> Sync Sekarang</span>
                    <span class="spinner" id="syncSpinner" aria-hidden="true"></span>
                </button>
            </form>
            
            <?php if (ENVIRONMENT === 'development' && session()->getFlashdata('debug')): ?>
            <div class="debug-box">
                <strong>Debug Info:</strong>
                <pre><?= print_r(session()->getFlashdata('debug'), true) ?></pre>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    const form = document.getElementById('syncForm');
    if (!form) return;
    const btn = document.getElementById('syncButton');
    const spinner = document.getElementById('syncSpinner');
    const label = btn ? btn.querySelector('.btn-label') : null;

    form.addEventListener('submit', function() {
        if (!btn) return;
        btn.disabled = true;
        btn.classList.add('loading');
        if (label) label.innerHTML = '<i class="bi bi-arrow-repeat"></i> Syncing...';
    });
})();
</script>
<?= $this->endSection() ?>
