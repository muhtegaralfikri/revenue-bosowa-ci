<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
    .user-shell {
        max-width: 1100px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    
    .dashboard-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .dashboard-card .card-header {
        padding: 1.25rem;
        border-bottom: 1px solid var(--surface-100);
    }
    
    .dashboard-card .card-body {
        padding: 1.25rem;
    }
    
    .eyebrow {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--primary-color);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    
    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--surface-900);
        margin: 0;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        align-items: end;
    }
    
    .form-field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    
    .form-field label {
        font-weight: 600;
        color: var(--surface-700);
        font-size: 0.9rem;
    }
    
    .form-field .form-control {
        padding: 0.6rem 0.85rem;
        border: 1px solid var(--surface-200);
        border-radius: 6px;
        font-size: 0.95rem;
    }
    
    .form-field .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(30, 70, 140, 0.1);
        outline: none;
    }
    
    .btn-create {
        background: var(--primary-color);
        color: #fff;
        border: none;
        padding: 0.6rem 1.25rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        height: 2.5rem;
    }
    
    .btn-create:hover {
        background: #163a73;
    }
    
    .user-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }
    
    .user-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .user-table th,
    .user-table td {
        padding: 0.85rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        text-align: left;
    }
    
    .user-table th {
        font-weight: 600;
        color: var(--surface-600);
        font-size: 0.9rem;
    }
    
    .user-name {
        font-weight: 600;
        color: var(--surface-900);
    }
    
    .actions-col {
        white-space: nowrap;
        text-align: right;
    }
    
    .btn-action {
        padding: 0.35rem 0.75rem;
        border: none;
        border-radius: 4px;
        font-size: 0.85rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-left: 0.25rem;
    }
    
    .btn-edit {
        background: transparent;
        color: var(--primary-color);
    }
    
    .btn-edit:hover {
        background: rgba(30, 70, 140, 0.1);
    }
    
    .btn-delete {
        background: transparent;
        color: #dc3545;
    }
    
    .btn-delete:hover {
        background: rgba(220, 53, 69, 0.1);
    }
    
    .empty-state {
        text-align: center;
        color: var(--surface-500);
        padding: 2rem;
    }
    
    /* Modal */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s, visibility 0.2s;
    }
    
    .modal-backdrop.show {
        opacity: 1;
        visibility: visible;
    }
    
    .modal-content {
        background: #fff;
        border-radius: 8px;
        width: 90%;
        max-width: 480px;
        transform: translateY(-20px);
        transition: transform 0.2s;
    }
    
    .modal-backdrop.show .modal-content {
        transform: translateY(0);
    }
    
    .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--surface-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: var(--surface-500);
    }
    
    .modal-body {
        padding: 1.25rem;
    }
    
    .form-stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .modal-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--surface-100);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
    
    .btn-cancel {
        background: transparent;
        border: 1px solid var(--surface-200);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .btn-save {
        background: var(--primary-color);
        color: #fff;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-save:hover {
        background: #163a73;
    }
    
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .user-table th,
        .user-table td {
            padding: 0.65rem 0.5rem;
            font-size: 0.85rem;
        }
        
        .col-created {
            display: none;
        }
        
        .btn-action span {
            display: none;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="user-shell">
    <!-- Create User Card -->
    <div class="dashboard-card">
        <div class="card-header">
            <p class="eyebrow">Kelola Pengguna</p>
            <h2 class="card-title">Tambah Pengguna Baru</h2>
        </div>
        <div class="card-body">
            <form action="/users/store" method="post" class="form-grid">
                <?= csrf_field() ?>
                <div class="form-field">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           placeholder="Mis. Admin Bosowa" required>
                </div>
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="admin@example.com" required>
                </div>
                <div class="form-field">
                    <label for="password">Password Awal</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Minimal 8 karakter" required minlength="8">
                </div>
                <button type="submit" class="btn-create">
                    <i class="bi bi-person-plus"></i>
                    <span>Simpan Pengguna</span>
                </button>
            </form>
        </div>
    </div>
    
    <!-- User List Card -->
    <div class="dashboard-card">
        <div class="card-header">
            <p class="eyebrow">Daftar Pengguna</p>
            <h2 class="card-title">Pengguna Aktif</h2>
        </div>
        <div class="card-body">
            <div class="user-table-wrapper">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th class="col-created">Dibuat</th>
                            <th class="actions-col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="4" class="empty-state">Belum ada pengguna lain.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><span class="user-name"><?= esc($user['name']) ?></span></td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td class="col-created"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                    <td class="actions-col">
                                        <button type="button" class="btn-action btn-edit" 
                                                onclick="openEditModal(<?= htmlspecialchars(json_encode($user)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                            <span>Edit</span>
                                        </button>
                                        <?php if ($user['id'] != session()->get('user_id')): ?>
                                            <button type="button" class="btn-action btn-delete" 
                                                    onclick="confirmDelete(<?= $user['id'] ?>, '<?= esc($user['name']) ?>')">
                                                <i class="bi bi-trash"></i>
                                                <span>Hapus</span>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-backdrop" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Edit Pengguna</h5>
            <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" method="post">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-stack">
                    <div class="form-field">
                        <label for="edit_name">Nama Lengkap</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-field">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="form-field">
                        <label for="edit_password">Password Baru (Opsional)</label>
                        <input type="password" class="form-control" id="edit_password" name="password" 
                               placeholder="Biarkan kosong jika tidak diganti">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-save">
                    <i class="bi bi-check-lg"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Form (hidden) -->
<form id="deleteForm" method="get" style="display: none;"></form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function openEditModal(user) {
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_password').value = '';
    document.getElementById('editForm').action = '/users/update/' + user.id;
    document.getElementById('editModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
}

function confirmDelete(id, name) {
    if (confirm('Hapus pengguna ' + name + '? Tindakan ini tidak dapat dibatalkan.')) {
        window.location.href = '/users/delete/' + id;
    }
}

// Close modal on backdrop click
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});
</script>
<?= $this->endSection() ?>
