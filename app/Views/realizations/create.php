<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h4 class="mb-1" style="color: var(--surface-900); font-weight: 600;">Tambah Realisasi</h4>
    <p class="text-muted mb-0" style="font-size: 0.9rem;">Tambahkan data realisasi pendapatan</p>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body p-4">
        <form action="/realizations/store" method="post">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="company_id" class="form-label fw-semibold">Perusahaan</label>
                    <select name="company_id" id="company_id" class="form-select" required>
                        <option value="">Pilih Perusahaan</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= $company['id'] ?>"><?= $company['code'] ?> - <?= $company['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="date" class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="date" id="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="amount" class="form-label fw-semibold">Jumlah (Rp)</label>
                <input type="number" name="amount" id="amount" class="form-control" 
                       placeholder="Masukkan jumlah dalam Rupiah" required>
            </div>
            
            <div class="mb-4">
                <label for="description" class="form-label fw-semibold">Keterangan (Opsional)</label>
                <textarea name="description" id="description" class="form-control" rows="2" 
                          placeholder="Keterangan tambahan"></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" style="background-color: var(--primary-color); border-color: var(--primary-color);">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
                <a href="/realizations" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
