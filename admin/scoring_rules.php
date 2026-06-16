<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$rules = get_scoring_rules($conn);

// Group by factor
$grouped = [];
foreach ($rules as $r) {
    $grouped[$r['factor']][] = $r;
}

$title = 'Scoring Rules';
require_once '../includes/header_admin.php';
?>

<h4 class="fw-bold mb-2">Scoring Rules</h4>
<p class="text-muted mb-4">
    Score dihitung oleh MySQL function <code>fn_calculate_score()</code> menggunakan tabel ini.
    PHP tidak menghitung ulang — hanya membaca hasil dari tabel <code>evaluations</code>.
</p>

<?php foreach ($grouped as $factor => $rows): ?>
<div class="card mb-3">
    <div class="card-header fw-semibold"><?= h($factor) ?></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Value Key</th><th>Points</th><th>Description</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><code><?= h($r['value_key']) ?></code></td>
                    <td>
                        <span class="badge bg-<?= (int)$r['points'] >= 0 ? 'success' : 'danger' ?>">
                            <?= (int)$r['points'] >= 0 ? '+' : '' ?><?= (int)$r['points'] ?>
                        </span>
                    </td>
                    <td><?= h($r['description'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?>
