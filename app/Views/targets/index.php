<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Target Revenue</h4>
        <p class="text-muted mb-0">Kelola target pendapatan per perusahaan</p>
    </div>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="get">
            <select name="year" class="form-select" style="width: auto;" onchange="this.form.submit()">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <a href="/targets/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Target
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Perusahaan</th>
                        <th>Bulan</th>
                        <th>Tahun</th>
                        <th class="text-end">Target</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($targets)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada data target untuk tahun <?= $year ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($targets as $target): ?>
                            <tr>
                                <td>
                                    <span class="company-badge company-<?= strtolower($target['code']) ?>">
                                        <?= $target['code'] ?>
                                    </span>
                                    <?= $target['company_name'] ?>
                                </td>
                                <td><?= $months[$target['month']] ?></td>
                                <td><?= $target['year'] ?></td>
                                <td class="text-end fw-semibold">Rp <?= number_format($target['target_amount'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <a href="/targets/edit/<?= $target['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/targets/delete/<?= $target['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Yakin ingin menghapus?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
