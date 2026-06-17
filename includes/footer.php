<?php
$flash = get_flash();
?>
<?php if (!empty($flash)): ?>
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="flashAlert" class="alert alert-<?= h($flash['type']) ?> alert-dismissible shadow" role="alert">
        <?= h($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

<footer class="text-muted small py-4 mt-4 border-top">
    <div class="container text-center">
        <p class="mb-2">LaptopVal ngitung skor objektif buat laptop bekas dari spek, kondisi, sampai harga.</p>
        <p class="mb-2">Skor ini panduan, bukan jaminan. Keputusan beli tetap di tangan kamu.</p>
        <p class="mb-2">
            <a href="<?= BASE_URL ?>index.php">Beranda</a>
            <span class="mx-2">&middot;</span>
            <a href="<?= BASE_URL ?>login.php">Admin Login</a>
            <span class="mx-2">&middot;</span>
            <a href="<?= h(REQUEST_UPDATE_URL) ?>" target="_blank" rel="noopener">Request update data &#8599;</a>
        </p>
        <p class="mb-0"><?= h(APP_NAME) ?> &copy; 2026 <span class="mx-2">&middot;</span> Dibuat untuk tugas UAS</p>
    </div>
</footer>

</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
