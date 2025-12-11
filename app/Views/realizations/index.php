<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color: var(--surface-900); font-weight: 600;">Realisasi Revenue</h4>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">Data realisasi pendapatan harian</p>
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
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-funnel"></i>
            </button>
        </form>
        <a href="/realizations/create" class="btn btn-primary" style="background-color: var(--primary-color); border-color: var(--primary-color);">
            <i class="bi bi-plus-lg me-1"></i> Tambah
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: var(--surface-50);">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="py-3">Perusahaan</th>
                        <th class="py-3 text-end">Jumlah</th>
                        <th class="py-3">Keterangan</th>
                        <th class="py-3 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($realizations)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                <p class="mt-2 mb-0">Belum ada data realisasi untuk <?= $months[$month] ?> <?= $year ?></p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $badgeColors = [
                            'BBI' => 'background-color: rgba(59, 130, 246, 0.1); color: #3B82F6;',
                            'BBA' => 'background-color: rgba(34, 197, 94, 0.1); color: #22C55E;',
                            'JAPELIN' => 'background-color: rgba(249, 115, 22, 0.1); color: #F97316;',
                        ];
                        foreach ($realizations as $realization): 
                            $badgeStyle = $badgeColors[$realization['code']] ?? 'background-color: #f1f5f9; color: #64748b;';
                        ?>
                            <tr>
                                <td class="px-4 py-3"><?= date('d/m/Y', strtotime($realization['date'])) ?></td>
                                <td class="py-3">
                                    <span class="badge rounded-pill" style="<?= $badgeStyle ?> font-weight: 600; padding: 0.35rem 0.75rem;">
                                        <?= $realization['code'] ?>
                                    </span>
                                </td>
                                <td class="py-3 text-end fw-semibold">Rp <?= number_format($realization['amount'], 0, ',', '.') ?></td>
                                <td class="py-3 text-muted"><?= $realization['description'] ?? '-' ?></td>
                                <td class="py-3 text-center pe-4">
                                    <a href="/realizations/edit/<?= $realization['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
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
