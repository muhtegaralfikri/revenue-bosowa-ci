<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
    .input-container {
        display: flex;
        justify-content: center;
        padding: 1rem 0;
    }
    
    .input-card {
        width: 100%;
        max-width: 700px;
    }
    
    .input-card .card-header {
        background: transparent;
        border-bottom: 1px solid var(--surface-100);
        padding: 1.25rem 2rem;
    }
    
    .input-card .card-title {
        font-size: 1.35rem;
        font-weight: 600;
        color: var(--surface-900);
        margin: 0;
    }
    
    .input-card .card-body {
        padding: 1.5rem 2rem;
    }
    
    .form-grid {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    
    .form-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-field label {
        font-weight: 600;
        color: var(--surface-700);
        font-size: 0.95rem;
    }
    
    .form-field .form-control,
    .form-field .form-select {
        padding: 0.85rem 1rem;
        border: 1px solid var(--surface-200);
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    
    .form-field .form-control:focus,
    .form-field .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(30, 70, 140, 0.1);
        outline: none;
    }
    
    .submit-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        font-weight: 600;
        color: #fff;
        background-color: #10B981;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.2s;
        margin-top: 0.5rem;
    }
    
    .submit-btn:hover {
        background-color: #059669;
    }
    
    .submit-btn:disabled {
        background-color: #6EE7B7;
        cursor: not-allowed;
    }
    
    @media (max-width: 576px) {
        .input-container {
            padding: 0.5rem 0;
        }
        
        .input-card .card-header {
            padding: 1rem;
        }
        
        .input-card .card-title {
            font-size: 1.1rem;
        }
        
        .input-card .card-body {
            padding: 1rem;
        }
        
        .form-field label {
            font-size: 0.85rem;
        }
        
        .form-field .form-control,
        .form-field .form-select {
            padding: 0.65rem 0.85rem;
            font-size: 0.9rem;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="input-container">
    <div class="card input-card">
        <div class="card-header">
            <h5 class="card-title">Input Revenue</h5>
        </div>
        <div class="card-body">
            <form action="/input/store" method="post" class="form-grid">
                <?= csrf_field() ?>
                
                <div class="form-field">
                    <label for="date">Tanggal</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-field">
                    <label for="type">Jenis</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="realisasi" selected>Realisasi</option>
                        <option value="target">Target</option>
                    </select>
                </div>
                
                <div class="form-field">
                    <label for="company_id">Entity</label>
                    <select class="form-select" id="company_id" name="company_id" required>
                        <option value="">Pilih Entity</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= $company['id'] ?>" data-code="<?= $company['code'] ?>" data-name="<?= $company['name'] ?>">
                                <?= $company['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-field">
                    <label for="amount">Revenue (Rp)</label>
                    <input type="number" class="form-control" id="amount" name="amount" 
                           placeholder="Masukkan jumlah" min="0" required>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="bi bi-check-lg"></i>
                    <span>Simpan</span>
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Format number input with thousand separator display
const amountInput = document.getElementById('amount');
amountInput.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Handle mobile view for company dropdown
const companySelect = document.getElementById('company_id');
const isMobile = () => window.innerWidth <= 576;

function updateCompanyOptions() {
    const options = companySelect.querySelectorAll('option[data-code]');
    options.forEach(option => {
        if (isMobile()) {
            option.textContent = option.dataset.code;
        } else {
            option.textContent = option.dataset.name;
        }
    });
}

// Initial update
updateCompanyOptions();

// Update on resize
window.addEventListener('resize', updateCompanyOptions);
</script>
<?= $this->endSection() ?>
