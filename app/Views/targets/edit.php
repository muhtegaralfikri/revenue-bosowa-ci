<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h4 class="mb-1">Edit Target</h4>
    <p class="text-muted mb-0">Ubah target pendapatan</p>
</div>

<div class="card">
    <div class="card-body">
        <form action="/targets/update/<?= $target['id'] ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Perusahaan</label>
                    <select class="form-select" disabled>
                        <?php foreach ($companies as $company): ?>
                            <option <?= $company['id'] == $target['company_id'] ? 'selected' : '' ?>>
                                <?= $company['code'] ?> - <?= $company['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Bulan</label>
                    <input type="text" class="form-control" value="<?= $months[$target['month']] ?>" disabled>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tahun</label>
                    <input type="text" class="form-control" value="<?= $target['year'] ?>" disabled>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="target_amount" class="form-label">Target (Rp)</label>
                <input type="number" name="target_amount" id="target_amount" class="form-control" 
                       value="<?= $target['target_amount'] ?>" required>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update
                </button>
                <a href="/targets" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
