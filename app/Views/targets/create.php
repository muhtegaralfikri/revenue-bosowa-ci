<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h4 class="mb-1">Tambah Target</h4>
    <p class="text-muted mb-0">Tambahkan target pendapatan baru</p>
</div>

<div class="card">
    <div class="card-body">
        <form action="/targets/store" method="post">
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
                
                <div class="col-md-3 mb-3">
                    <label for="month" class="form-label">Bulan</label>
                    <select name="month" id="month" class="form-select" required>
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= date('n') == $num ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="year" class="form-label">Tahun</label>
                    <select name="year" id="year" class="form-select" required>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= date('Y') == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="target_amount" class="form-label">Target (Rp)</label>
                <input type="number" name="target_amount" id="target_amount" class="form-control" 
                       placeholder="Masukkan target dalam Rupiah" required>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
                <a href="/targets" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
