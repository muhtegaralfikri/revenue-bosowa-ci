<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Realisasi Revenue</h4>
        <p class="text-muted mb-0">Data realisasi pendapatan harian</p>
    </div>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="get">
            <select name="month" class="form-select" style="width: auto;">
                <?php foreach ($months as $num => $name): ?>
                    <option value="<?= $num ?>" <?= $month == $num ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="form-select" style="width: auto;">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-funnel"></i>
            </button>
        </form>
        <a href="/realizations/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Perusahaan</th>
                        <th class="text-end">Jumlah</th>
                        <th>Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($realizations)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada data realisasi untuk <?= $months[$month] ?> <?= $year ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($realizations as $realization): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($realization['date'])) ?></td>
                                <td>
                                    <span class="company-badge company-<?= strtolower($realization['code']) ?>">
                                        <?= $realization['code'] ?>
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">Rp <?= number_format($realization['amount'], 0, ',', '.') ?></td>
                                <td class="text-muted"><?= $realization['description'] ?? '-' ?></td>
                                <td class="text-center">
                                    <a href="/realizations/edit/<?= $realization['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/realizations/delete/<?= $realization['id'] ?>" class="btn btn-sm btn-outline-danger"
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
