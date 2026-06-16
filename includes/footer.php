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

</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
