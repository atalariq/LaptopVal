<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
http_response_code(404);
$title = '404 — Halaman Tidak Ditemukan';
require_once 'includes/header_public.php';

// favicon links will be included by header_public.php
?>
<div class="text-center py-5">
    <div class="display-1 fw-bold" style="color: var(--tn-purple, #bb9af7);">404</div>
    <h2 class="mb-3">Halaman Tidak Ditemukan</h2>
    <p class="text-muted mb-4">Laptop yang kamu cari tidak ada atau sudah dihapus.</p>
    <a href="<?= BASE_URL ?>index.php" class="btn btn-primary px-4">
        &larr; Kembali ke Beranda
    </a>
</div>
<?php require_once 'includes/footer.php'; ?>
