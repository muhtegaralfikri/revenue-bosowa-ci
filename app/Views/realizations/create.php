<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h4 class="mb-1">Tambah Realisasi</h4>
    <p class="text-muted mb-0">Tambahkan data realisasi pendapatan</p>
</div>

<div class="card">
    <div class="card-body">
        <form action="/realizations/store" method="post">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="company_id" class="form-label">Perusahaan</label>
                    <select name="company_id" id="company_id" class="form-select" required>
                        <option value="">Pilih Perusahaan</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= $company['id'] ?>"><?= $company['code'] ?> - <?= $company['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="date" class="form-label">Tanggal</label>
                    <input type="date" name="date" id="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="amount" class="form-label">Jumlah (Rp)</label>
                <input type="number" name="amount" id="amount" class="form-control" 
                       placeholder="Masukkan jumlah dalam Rupiah" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Keterangan (Opsional)</label>
                <textarea name="description" id="description" class="form-control" rows="2" 
                          placeholder="Keterangan tambahan"></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
                <a href="/realizations" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
