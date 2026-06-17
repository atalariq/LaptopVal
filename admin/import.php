<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$results    = null;
$errors     = [];
$row_log    = [];
$inserted   = 0;
$skipped    = 0;

$EXPECTED = ['brand','model','release_year','cpu_tier','ram_gb','storage_gb','condition','has_warranty','price','source'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $file_error = $_FILES['csv_file']['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($file_error !== UPLOAD_ERR_OK) {
        $errors[] = 'File CSV tidak ditemukan atau upload gagal (kode: ' . (int)$file_error . ').';
    } else {
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$handle) {
            $errors[] = 'Tidak dapat membaca file CSV.';
        } else {
            $raw_header = fgetcsv($handle);
            $header = array_map('strtolower', array_map('trim', $raw_header ?: []));

            if ($header !== $EXPECTED) {
                $errors[] = 'Header tidak valid. Diharapkan: ' . implode(',', $EXPECTED);
            } else {
                $row_num = 1;
                while (($row = fgetcsv($handle)) !== false) {
                    $row_num++;
                    if (count($row) < 9) {
                        $row_log[] = ['row' => $row_num, 'status' => 'error', 'msg' => 'Kolom tidak lengkap'];
                        continue;
                    }

                    $brand_name   = trim($row[0]);
                    $model        = trim($row[1]);
                    $release_year = (int) $row[2];
                    $cpu_tier     = (int) $row[3];
                    $ram_gb       = (int) $row[4];
                    $storage_gb   = (int) $row[5];
                    $condition    = (int) $row[6];
                    $has_warranty = (int) $row[7];
                    $price        = (int) $row[8];
                    $source_url   = trim($row[9] ?? '');

                    $row_errors = [];
                    if ($brand_name === '')                            $row_errors[] = 'brand kosong';
                    if ($model === '')                                 $row_errors[] = 'model kosong';
                    if (mb_strlen($brand_name) > 50)                  $row_errors[] = 'brand maksimal 50 karakter';
                    if (mb_strlen($model) > 100)                      $row_errors[] = 'model maksimal 100 karakter';
                    if ($release_year < 2000 || $release_year > 2026) $row_errors[] = 'tahun tidak valid';
                    if ($cpu_tier < 1 || $cpu_tier > 3)               $row_errors[] = 'cpu_tier harus 1-3';
                    if ($ram_gb <= 0)                                  $row_errors[] = 'ram_gb harus > 0';
                    if ($storage_gb <= 0)                              $row_errors[] = 'storage_gb harus > 0';
                    if ($condition < 1 || $condition > 4)              $row_errors[] = 'condition harus 1-4';
                    if (!in_array($has_warranty, [0, 1], true))        $row_errors[] = 'has_warranty harus 0 atau 1';
                    if ($price <= 0)                                   $row_errors[] = 'price harus > 0';
                    if ($source_url !== '' && !filter_var($source_url, FILTER_VALIDATE_URL)) $row_errors[] = 'source url tidak valid';
                    if (mb_strlen($source_url) > 255)                  $row_errors[] = 'source maksimal 255 karakter';

                    if (!empty($row_errors)) {
                        $row_log[] = ['row' => $row_num, 'status' => 'error',
                                      'model' => $model ?: '?',
                                      'msg' => implode('; ', $row_errors)];
                        continue;
                    }

                    // Brand lookup or auto-create
                    $stmt = $conn->prepare("SELECT id FROM brands WHERE LOWER(name) = LOWER(?)");
                    $stmt->bind_param('s', $brand_name);
                    $stmt->execute();
                    $brand_row = $stmt->get_result()->fetch_assoc();

                    if ($brand_row) {
                        $brand_id = (int) $brand_row['id'];
                    } else {
                        $stmt = $conn->prepare("INSERT INTO brands (name) VALUES (?)");
                        $stmt->bind_param('s', $brand_name);
                        if (!$stmt->execute()) {
                            $row_log[] = ['row' => $row_num, 'status' => 'error',
                                          'model' => $model,
                                          'msg' => 'Gagal menambah brand: ' . $conn->error];
                            continue;
                        }
                        $brand_id = (int) $conn->insert_id;
                    }

                    // Duplicate check (same model + brand)
                    $stmt = $conn->prepare("SELECT id FROM laptops WHERE model = ? AND brand_id = ?");
                    $stmt->bind_param('si', $model, $brand_id);
                    $stmt->execute();
                    if ($stmt->get_result()->fetch_assoc()) {
                        $row_log[] = ['row' => $row_num, 'status' => 'skip',
                                      'model' => $brand_name . ' ' . $model,
                                      'msg' => 'Sudah ada — dilewati'];
                        $skipped++;
                        continue;
                    }

                    // Insert — trigger fires automatically for scoring
                    $stmt = $conn->prepare(
                        "INSERT INTO laptops
                         (model, brand_id, release_year, cpu_tier, ram_gb, storage_gb,
                          `condition`, has_warranty, price, source_url)
                         VALUES (?,?,?,?,?,?,?,?,?,?)"
                    );
                    $stmt->bind_param('siiiiiiiis',
                        $model, $brand_id, $release_year, $cpu_tier,
                        $ram_gb, $storage_gb, $condition, $has_warranty, $price,
                        $source_url === '' ? null : $source_url
                    );
                    if (!$stmt->execute()) {
                        $row_log[] = ['row' => $row_num, 'status' => 'error',
                                      'model' => $brand_name . ' ' . $model,
                                      'msg' => 'Gagal insert: ' . $conn->error];
                        continue;
                    }
                    $inserted++;
                    $row_log[] = ['row' => $row_num, 'status' => 'ok',
                                  'model' => $brand_name . ' ' . $model,
                                  'msg' => 'Berhasil diimpor'];
                }
                fclose($handle);
            }
        }
    }

    $results = ['inserted' => $inserted, 'skipped' => $skipped,
                'errors' => count(array_filter($row_log, fn($r) => $r['status'] === 'error'))];
}

$title = 'Import CSV Laptops';
require_once '../includes/header_admin.php';
?>

<div class="mb-3">
    <a href="laptops.php" class="text-decoration-none">&larr; Kembali ke Laptops</a>
</div>
<h4 class="fw-bold mb-4">Import Laptop via CSV</h4>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<?php if ($results !== null): ?>
<div class="alert alert-<?= $results['errors'] === 0 && $results['skipped'] === 0 ? 'success' : 'info' ?>">
    <strong>Import selesai:</strong>
    <?= (int)$results['inserted'] ?> diimpor,
    <?= (int)$results['skipped']  ?> dilewati (duplikat),
    <?= (int)$results['errors']   ?> error.
</div>
<?php if (!empty($row_log)): ?>
<div class="card mb-4">
    <div class="card-header fw-bold">Log per Baris</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light"><tr><th>#Baris</th><th>Model</th><th>Status</th><th>Keterangan</th></tr></thead>
            <tbody>
                <?php foreach ($row_log as $log): ?>
                <tr>
                    <td><?= (int)$log['row'] ?></td>
                    <td><?= h($log['model'] ?? '—') ?></td>
                    <td>
                        <?php if ($log['status'] === 'ok'): ?>
                            <span class="badge bg-success">OK</span>
                        <?php elseif ($log['status'] === 'skip'): ?>
                            <span class="badge bg-secondary">Skip</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Error</span>
                        <?php endif; ?>
                    </td>
                    <td><?= h($log['msg']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="card">
    <div class="card-header fw-bold">Upload File CSV</div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Format CSV (header wajib ada, urutan kolom harus tepat):<br>
            <code>brand,model,release_year,cpu_tier,ram_gb,storage_gb,condition,has_warranty,price,source</code><br>
            <strong>cpu_tier:</strong> 1–3 &nbsp;|&nbsp;
            <strong>condition:</strong> 1–4 &nbsp;|&nbsp;
            <strong>has_warranty:</strong> 0 atau 1 &nbsp;|&nbsp;
            <strong>price:</strong> ribuan IDR (3500 = Rp 3.5jt) &nbsp;|&nbsp;
            <strong>source:</strong> URL (opsional, max 255 karakter)
        </p>
        <p class="mb-3">
            <a href="sample_laptops.csv" download class="btn btn-outline-secondary btn-sm">
                &#8659; Download contoh CSV
            </a>
        </p>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="mb-3">
                <label for="csv_file" class="form-label">File CSV</label>
                <input type="file" class="form-control" id="csv_file" name="csv_file"
                       accept=".csv,text/csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
            <a href="laptops.php" class="btn btn-secondary ms-2">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
