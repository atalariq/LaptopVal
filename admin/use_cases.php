<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_GET['action'] ?? '';

    if ($action === 'add') {
        $name        = trim($_POST['name'] ?? '');
        $min_ram     = (int)($_POST['min_ram_gb'] ?? 0);
        $min_cpu     = (int)($_POST['min_cpu_tier'] ?? 1);
        $min_storage = (int)($_POST['min_storage'] ?? 0);
        if ($name === '') {
            set_flash('Nama use case wajib diisi.', 'danger');
        } elseif ($min_cpu < 1 || $min_cpu > 3 || $min_ram < 0 || $min_storage < 0) {
            set_flash('Data tidak valid.', 'danger');
        } else {
            $stmt = $conn->prepare("INSERT INTO use_cases (name, min_ram_gb, min_cpu_tier, min_storage) VALUES (?,?,?,?)");
            $stmt->bind_param('siii', $name, $min_ram, $min_cpu, $min_storage);
            $stmt->execute();
            set_flash('Use case berhasil ditambahkan.', 'success');
        }

    } elseif ($action === 'edit') {
        $id          = (int)($_GET['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $min_ram     = (int)($_POST['min_ram_gb'] ?? 0);
        $min_cpu     = (int)($_POST['min_cpu_tier'] ?? 1);
        $min_storage = (int)($_POST['min_storage'] ?? 0);
        if ($name === '' || $id <= 0 || $min_cpu < 1 || $min_cpu > 3 || $min_ram < 0 || $min_storage < 0) {
            set_flash('Data tidak valid.', 'danger');
        } else {
            $stmt = $conn->prepare("UPDATE use_cases SET name=?, min_ram_gb=?, min_cpu_tier=?, min_storage=? WHERE id=?");
            $stmt->bind_param('siiii', $name, $min_ram, $min_cpu, $min_storage, $id);
            $stmt->execute();
            set_flash('Use case berhasil diupdate.', 'success');
        }

    } elseif ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM use_cases WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        set_flash('Use case berhasil dihapus.', 'success');
    }

    header('Location: use_cases.php');
    exit;
}

$use_cases = get_use_cases($conn);
$title = 'Use Cases';
require_once '../includes/header_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Use Cases</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        + Tambah Use Case
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($use_cases)): ?>
        <p class="p-3 mb-0 text-muted">Belum ada data.</p>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>ID</th><th>Name</th><th>Min RAM</th><th>Min CPU Tier</th><th>Min Storage</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($use_cases as $uc): ?>
                <tr>
                    <td><?= (int)$uc['id'] ?></td>
                    <td><?= h($uc['name']) ?></td>
                    <td><?= (int)$uc['min_ram_gb'] ?> GB</td>
                    <td><?= h(cpu_label($uc['min_cpu_tier'])) ?></td>
                    <td><?= (int)$uc['min_storage'] ?> GB</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal" data-bs-target="#editModal"
                                data-id="<?= (int)$uc['id'] ?>"
                                data-name="<?= h($uc['name']) ?>"
                                data-ram="<?= (int)$uc['min_ram_gb'] ?>"
                                data-cpu="<?= (int)$uc['min_cpu_tier'] ?>"
                                data-storage="<?= (int)$uc['min_storage'] ?>">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger ms-1"
                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                data-action="use_cases.php?action=delete&id=<?= (int)$uc['id'] ?>">
                            Hapus
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="use_cases.php?action=add">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Use Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min RAM (GB)</label>
                        <input type="number" class="form-control" name="min_ram_gb" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min CPU Tier</label>
                        <select class="form-select" name="min_cpu_tier">
                            <option value="1">Low</option>
                            <option value="2">Mid</option>
                            <option value="3">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min Storage (GB)</label>
                        <input type="number" class="form-control" name="min_storage" min="0" value="0">
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
                    <h5 class="modal-title">Edit Use Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min RAM (GB)</label>
                        <input type="number" class="form-control" name="min_ram_gb" id="editRam" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min CPU Tier</label>
                        <select class="form-select" name="min_cpu_tier" id="editCpu">
                            <option value="1">Low</option>
                            <option value="2">Mid</option>
                            <option value="3">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min Storage (GB)</label>
                        <input type="number" class="form-control" name="min_storage" id="editStorage" min="0">
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
                    <h5 class="modal-title">Hapus Use Case?</h5>
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
    document.getElementById('editForm').action    = 'use_cases.php?action=edit&id=' + btn.dataset.id;
    document.getElementById('editName').value     = btn.dataset.name;
    document.getElementById('editRam').value      = btn.dataset.ram;
    document.getElementById('editCpu').value      = btn.dataset.cpu;
    document.getElementById('editStorage').value  = btn.dataset.storage;
});
document.getElementById('deleteModal').addEventListener('show.bs.modal', function(e) {
    document.getElementById('deleteForm').action = e.relatedTarget.dataset.action;
});
</script>

<?php require_once '../includes/footer.php'; ?>
