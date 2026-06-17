<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_GET['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        $laptop = get_laptop($conn, $id); // fetch image_path before deleting
        $stmt = $conn->prepare("DELETE FROM laptops WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            if ($laptop && !empty($laptop['image_path'])) {
                $file_path = __DIR__ . '/../' . $laptop['image_path'];
                if (file_exists($file_path)) unlink($file_path);
            }
            set_flash('Laptop berhasil dihapus.', 'success');
        } else {
            set_flash('Gagal menghapus laptop.', 'danger');
        }
    }

    header('Location: laptops.php');
    exit;
}

$laptops = get_laptops($conn);
$title   = 'Laptops';
require_once '../includes/header_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Laptops</h4>
    <div class="d-flex gap-2">
        <a href="import.php" class="btn btn-outline-secondary btn-sm">Import CSV</a>
        <a href="export_laptops.php" class="btn btn-outline-secondary btn-sm">Export CSV</a>
        <a href="laptop_form.php" class="btn btn-primary btn-sm">+ Tambah Laptop</a>
    </div>
</div>

<div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="Cari model atau brand...">
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($laptops)): ?>
        <p class="p-3 mb-0 text-muted">Belum ada data.</p>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Model</th><th>Brand</th><th>Harga</th><th>Score</th><th>Verdict</th><th>Oleh</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($laptops as $l): ?>
                <tr data-model="<?= h($l['model']) ?>" data-brand="<?= h($l['brand']) ?>">
                    <td><?= h($l['model']) ?></td>
                    <td><?= h($l['brand']) ?></td>
                    <td><?= h(format_price($l['price'])) ?></td>
                    <td><strong><?= (int)$l['value_score'] ?></strong></td>
                    <td>
                        <span class="badge bg-<?= h(verdict_class($l['verdict'])) ?>">
                            <?= h($l['verdict']) ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= h($l['created_by_name'] ?? '—') ?></td>
                    <td>
                        <a href="laptop_form.php?id=<?= (int)$l['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <button class="btn btn-sm btn-outline-danger ms-1"
                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                data-action="laptops.php?action=delete&id=<?= (int)$l['id'] ?>"
                                aria-label="Hapus laptop">
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" id="deleteForm" action="">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Laptop?</h5>
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

<?php require_once '../includes/footer.php'; ?>
