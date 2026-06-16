<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$id      = isset($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit = $id !== null && $id > 0;
$laptop  = null;
$errors  = [];
$original_image_path = null;

if ($is_edit) {
    $laptop = get_laptop($conn, $id);
    if ($laptop === null) {
        set_flash('Laptop tidak ditemukan.', 'danger');
        header('Location: laptops.php');
        exit;
    }
    $original_image_path = $laptop['image_path'] ?? null;
}

$brands = get_brands($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $model        = trim($_POST['model'] ?? '');
    $brand_id     = (int)($_POST['brand_id'] ?? 0);
    $release_year = (int)($_POST['release_year'] ?? 0);
    $cpu_tier     = (int)($_POST['cpu_tier'] ?? 0);
    $ram_gb       = (int)($_POST['ram_gb'] ?? 0);
    $storage_gb   = (int)($_POST['storage_gb'] ?? 0);
    $condition    = (int)($_POST['condition'] ?? 0);
    $has_warranty = isset($_POST['has_warranty']) ? 1 : 0;
    $price        = (int)($_POST['price'] ?? 0);

    if ($model === '')                                $errors[] = 'Model wajib diisi.';
    if (strlen($model) > 100)                         $errors[] = 'Model maksimal 100 karakter.';
    if ($brand_id <= 0)                               $errors[] = 'Brand wajib dipilih.';
    if ($release_year < 2000 || $release_year > 2026) $errors[] = 'Tahun rilis harus antara 2000–2026.';
    if ($cpu_tier < 1 || $cpu_tier > 3)               $errors[] = 'CPU Tier tidak valid.';
    if ($ram_gb <= 0)                                  $errors[] = 'RAM harus lebih dari 0.';
    if ($storage_gb <= 0)                              $errors[] = 'Storage harus lebih dari 0.';
    if ($condition < 1 || $condition > 4)              $errors[] = 'Kondisi tidak valid.';
    if ($price <= 0)                                   $errors[] = 'Harga harus lebih dari 0.';

    // Image upload validation (only runs if a file was submitted)
    $image_path = $original_image_path; // default: keep existing (edit) or null (add)
    $has_file   = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($has_file) {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload gagal (kode error: ' . $file['error'] . ').';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran gambar maksimal 2 MB.';
        } else {
            $info = @getimagesize($file['tmp_name']);
            $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
            if (!$info || !in_array($info[2], $allowed)) {
                $errors[] = 'Format gambar harus JPG, PNG, atau WebP.';
            }
        }
    }

    if (empty($errors)) {
        // Commit image: upload new file or remove
        if ($has_file) {
            $info = getimagesize($_FILES['image']['tmp_name']);
            $ext  = match($info[2]) {
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG  => 'png',
                IMAGETYPE_WEBP => 'webp',
                default        => 'jpg',
            };
            $upload_dir = __DIR__ . '/../uploads/laptops/';
            $filename   = uniqid('laptop_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                // Delete old file if replacing
                if ($original_image_path && file_exists(__DIR__ . '/../' . $original_image_path)) {
                    @unlink(__DIR__ . '/../' . $original_image_path);
                }
                $image_path = 'uploads/laptops/' . $filename;
            } else {
                $errors[] = 'Gagal menyimpan gambar. Pastikan folder uploads/ dapat ditulis.';
            }
        }

        // Handle "remove image" checkbox (only if no new file uploaded)
        if (!$has_file && isset($_POST['remove_image'])) {
            if ($original_image_path && file_exists(__DIR__ . '/../' . $original_image_path)) {
                @unlink(__DIR__ . '/../' . $original_image_path);
            }
            $image_path = null;
        }
    }

    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $conn->prepare(
                "UPDATE laptops
                 SET model=?, brand_id=?, release_year=?, cpu_tier=?,
                     ram_gb=?, storage_gb=?, `condition`=?, has_warranty=?,
                     price=?, image_path=?
                 WHERE id=?"
            );
            $stmt->bind_param('siiiiiiiisi',
                $model, $brand_id, $release_year, $cpu_tier,
                $ram_gb, $storage_gb, $condition, $has_warranty,
                $price, $image_path, $id
            );
            $stmt->execute();
            set_flash('Laptop berhasil diupdate.', 'success');
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO laptops
                 (model, brand_id, release_year, cpu_tier, ram_gb, storage_gb,
                  `condition`, has_warranty, price, image_path)
                 VALUES (?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param('siiiiiiisi',
                $model, $brand_id, $release_year, $cpu_tier,
                $ram_gb, $storage_gb, $condition, $has_warranty,
                $price, $image_path
            );
            $stmt->execute();
            set_flash('Laptop berhasil ditambahkan.', 'success');
        }

        header('Location: laptops.php');
        exit;
    }

    // Re-populate form on validation failure
    $laptop = [
        'model'        => $model,
        'brand_id'     => $brand_id,
        'release_year' => $release_year,
        'cpu_tier'     => $cpu_tier,
        'ram_gb'       => $ram_gb,
        'storage_gb'   => $storage_gb,
        'condition'    => $condition,
        'has_warranty' => $has_warranty,
        'price'        => $price,
        'image_path'   => $original_image_path, // keep original on re-render
    ];
}

$title = $is_edit ? 'Edit Laptop' : 'Tambah Laptop';
require_once '../includes/header_admin.php';
?>

<div class="mb-3">
    <a href="laptops.php" class="text-decoration-none">&larr; Kembali ke Laptops</a>
</div>
<h4 class="fw-bold mb-4"><?= h($title) ?></h4>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
        <li><?= h($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" id="laptopForm" class="needs-validation" novalidate
      enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-4">

        <!-- Left: form fields -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Model <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="model" maxlength="100" required
                               value="<?= h($laptop['model'] ?? '') ?>">
                        <div class="invalid-feedback">Model wajib diisi (maks 100 karakter).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Brand <span class="text-danger">*</span></label>
                        <select class="form-select" name="brand_id" required>
                            <option value="">-- Pilih Brand --</option>
                            <?php foreach ($brands as $b): ?>
                            <option value="<?= (int)$b['id'] ?>"
                                <?= (int)($laptop['brand_id'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>>
                                <?= h($b['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Brand wajib dipilih.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Rilis <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="release_year"
                                   min="2000" max="2026" required
                                   value="<?= h((string)($laptop['release_year'] ?? '')) ?>">
                            <div class="invalid-feedback">Tahun 2000–2026.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CPU Tier <span class="text-danger">*</span></label>
                            <select class="form-select" name="cpu_tier" required>
                                <option value="">-- Pilih --</option>
                                <option value="1" <?= (int)($laptop['cpu_tier'] ?? 0) === 1 ? 'selected' : '' ?>>Low</option>
                                <option value="2" <?= (int)($laptop['cpu_tier'] ?? 0) === 2 ? 'selected' : '' ?>>Mid</option>
                                <option value="3" <?= (int)($laptop['cpu_tier'] ?? 0) === 3 ? 'selected' : '' ?>>High</option>
                            </select>
                            <div class="invalid-feedback">CPU Tier wajib dipilih.</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">RAM (GB) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="ram_gb" min="1" required
                                   value="<?= h((string)($laptop['ram_gb'] ?? '')) ?>">
                            <div class="invalid-feedback">RAM harus lebih dari 0.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Storage (GB) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="storage_gb" min="1" required
                                   value="<?= h((string)($laptop['storage_gb'] ?? '')) ?>">
                            <div class="invalid-feedback">Storage harus lebih dari 0.</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kondisi <span class="text-danger">*</span></label>
                            <select class="form-select" name="condition" required>
                                <option value="">-- Pilih --</option>
                                <option value="1" <?= (int)($laptop['condition'] ?? 0) === 1 ? 'selected' : '' ?>>Buruk</option>
                                <option value="2" <?= (int)($laptop['condition'] ?? 0) === 2 ? 'selected' : '' ?>>Cukup</option>
                                <option value="3" <?= (int)($laptop['condition'] ?? 0) === 3 ? 'selected' : '' ?>>Baik</option>
                                <option value="4" <?= (int)($laptop['condition'] ?? 0) === 4 ? 'selected' : '' ?>>Mulus</option>
                            </select>
                            <div class="invalid-feedback">Kondisi wajib dipilih.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga (ribu IDR) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price" min="1" required
                                   value="<?= h((string)($laptop['price'] ?? '')) ?>">
                            <div class="form-text">Contoh: 3500 = Rp 3.500.000</div>
                            <div class="invalid-feedback">Harga harus lebih dari 0.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_warranty"
                                   id="has_warranty" value="1"
                                   <?= !empty($laptop['has_warranty']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="has_warranty">Ada Garansi</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Laptop</label>
                        <?php if (!empty($laptop['image_path'])): ?>
                        <div class="mb-2">
                            <img src="<?= BASE_URL . h($laptop['image_path']) ?>"
                                 alt="Foto saat ini"
                                 class="img-thumbnail d-block mb-1"
                                 style="max-height:120px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="remove_image" id="remove_image">
                                <label class="form-check-label text-danger small"
                                       for="remove_image">Hapus foto ini</label>
                            </div>
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="image"
                               accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG, PNG, atau WebP · Maks 2 MB · Opsional</div>
                    </div>

                </div>
                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Laptop' ?>
                    </button>
                    <a href="laptops.php" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </div>

        <!-- Right: live score preview -->
        <div class="col-md-5">
            <div class="card score-preview-panel">
                <div class="card-header fw-bold">Live Score Preview</div>
                <div class="card-body text-center py-4">
                    <p class="text-muted small mb-1 text-uppercase">Estimasi Score</p>
                    <div class="score-display text-primary" id="previewScore">—</div>
                    <span id="previewVerdict" class="badge fs-6 bg-secondary mt-2">—</span>
                    <p class="text-muted small mt-3 mb-0">
                        Preview estimasi saja.<br>Score final dihitung oleh database.
                    </p>
                </div>
            </div>
        </div>

    </div>
</form>

<?php require_once '../includes/footer.php'; ?>
