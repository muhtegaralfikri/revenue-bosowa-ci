<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color: var(--surface-900); font-weight: 600;">Target Revenue</h4>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola target pendapatan per perusahaan</p>
    </div>
    <div class="d-flex gap-2">
        <select class="form-select" style="width: auto;" onchange="window.location.href='/targets?year='+this.value">
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
        <a href="/targets/create" class="btn btn-primary" style="background-color: var(--primary-color); border-color: var(--primary-color);">
            <i class="bi bi-plus-lg me-1"></i> Tambah Target
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: var(--surface-50);">
                    <tr>
                        <th class="px-4 py-3">Perusahaan</th>
                        <th class="py-3">Bulan</th>
                        <th class="py-3">Tahun</th>
                        <th class="py-3 text-end">Target</th>
                        <th class="py-3 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($targets)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                <p class="mt-2 mb-0">Belum ada data target untuk tahun <?= $year ?></p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $badgeColors = [
                            'BBI' => 'background-color: rgba(59, 130, 246, 0.1); color: #3B82F6;',
                            'BBA' => 'background-color: rgba(34, 197, 94, 0.1); color: #22C55E;',
                            'JAPELIN' => 'background-color: rgba(249, 115, 22, 0.1); color: #F97316;',
                        ];
                        foreach ($targets as $target): 
                            $badgeStyle = $badgeColors[$target['code']] ?? 'background-color: #f1f5f9; color: #64748b;';
                        ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="badge rounded-pill" style="<?= $badgeStyle ?> font-weight: 600; padding: 0.35rem 0.75rem;">
                                        <?= $target['code'] ?>
                                    </span>
                                    <span class="ms-2"><?= $target['company_name'] ?></span>
                                </td>
                                <td class="py-3"><?= $months[$target['month']] ?></td>
                                <td class="py-3"><?= $target['year'] ?></td>
                                <td class="py-3 text-end fw-semibold">Rp <?= number_format($target['target_amount'], 0, ',', '.') ?></td>
                                <td class="py-3 text-center pe-4">
                                    <a href="/targets/edit/<?= $target['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
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
