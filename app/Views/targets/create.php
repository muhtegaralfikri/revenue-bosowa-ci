<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h4 class="mb-1" style="color: var(--surface-900); font-weight: 600;">Tambah Target</h4>
    <p class="text-muted mb-0" style="font-size: 0.9rem;">Tambahkan target pendapatan baru</p>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body p-4">
        <form action="/targets/store" method="post">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label for="company_id" class="form-label fw-semibold">Perusahaan</label>
                <select name="company_id" id="company_id" class="form-select" required>
                    <option value="">Pilih Perusahaan</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['id'] ?>"><?= $company['code'] ?> - <?= $company['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="month" class="form-label fw-semibold">Bulan</label>
                    <select name="month" id="month" class="form-select" required>
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= date('n') == $num ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="year" class="form-label fw-semibold">Tahun</label>
                    <select name="year" id="year" class="form-select" required>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= date('Y') == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="target_amount" class="form-label fw-semibold">Target (Rp)</label>
                <input type="number" name="target_amount" id="target_amount" class="form-control" 
                       placeholder="Masukkan target dalam Rupiah" required>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" style="background-color: var(--primary-color); border-color: var(--primary-color);">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
                <a href="/targets" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
