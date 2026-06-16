<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

// POST handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_GET['action'] ?? '';

    if ($action === 'add') {
        $name  = trim($_POST['name'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        if ($name === '') {
            set_flash('Nama brand wajib diisi.', 'danger');
        } else {
            $stmt = $conn->prepare("INSERT INTO brands (name, notes) VALUES (?, ?)");
            $stmt->bind_param('ss', $name, $notes);
            $stmt->execute();
            set_flash('Brand berhasil ditambahkan.', 'success');
        }

    } elseif ($action === 'edit') {
        $id    = (int)($_GET['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        if ($name === '' || $id <= 0) {
            set_flash('Data tidak valid.', 'danger');
        } else {
            $stmt = $conn->prepare("UPDATE brands SET name = ?, notes = ? WHERE id = ?");
            $stmt->bind_param('ssi', $name, $notes, $id);
            $stmt->execute();
            set_flash('Brand berhasil diupdate.', 'success');
        }

    } elseif ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        try {
            $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            set_flash('Brand berhasil dihapus.', 'success');
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1451) {
                set_flash('Gagal: Brand ini masih digunakan oleh data laptop.', 'danger');
            } else {
                set_flash('Gagal menghapus brand.', 'danger');
            }
        }
    }

    header('Location: brands.php');
    exit;
}

$brands = get_brands($conn);
$title  = 'Brands';
require_once '../includes/header_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Brands</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        + Tambah Brand
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>ID</th><th>Name</th><th>Notes</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $b): ?>
                <tr>
                    <td><?= (int)$b['id'] ?></td>
                    <td><?= h($b['name']) ?></td>
                    <td><?= h($b['notes'] ?? '') ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal" data-bs-target="#editModal"
                                data-id="<?= (int)$b['id'] ?>"
                                data-name="<?= h($b['name']) ?>"
                                data-notes="<?= h($b['notes'] ?? '') ?>">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger ms-1"
                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                data-action="brands.php?action=delete&id=<?= (int)$b['id'] ?>">
                            Hapus
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="brands.php?action=add">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editForm" action="">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" id="editNotes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" id="deleteForm" action="">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Brand?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">Aksi ini tidak bisa dibatalkan.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="deleteConfirmBtn" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    document.getElementById('editForm').action = 'brands.php?action=edit&id=' + btn.dataset.id;
    document.getElementById('editName').value  = btn.dataset.name;
    document.getElementById('editNotes').value = btn.dataset.notes;
});
document.getElementById('deleteModal').addEventListener('show.bs.modal', function(e) {
    document.getElementById('deleteForm').action = e.relatedTarget.dataset.action;
});
</script>

<?php require_once '../includes/footer.php'; ?>
