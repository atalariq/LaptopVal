document.addEventListener('DOMContentLoaded', function () {
    initSearch();
    initDeleteModal();
    initFlashDismiss();
    initFormValidation();

    if (document.getElementById('laptopForm')) {
        initLiveScore();
    }
});

// ── Real-time search filter ───────────────────────────────────────────────────
function initSearch() {
    const input = document.getElementById('searchInput');
    if (!input) return;

    input.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();

        // Card grid (public index)
        document.querySelectorAll('.card-laptop-wrapper').forEach(function (wrapper) {
            const text = (wrapper.dataset.model + ' ' + wrapper.dataset.brand).toLowerCase();
            wrapper.style.display = text.includes(query) ? '' : 'none';
        });

        // Table rows (admin lists)
        document.querySelectorAll('tr[data-model]').forEach(function (row) {
            const text = (row.dataset.model + ' ' + row.dataset.brand).toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
}

// ── Delete confirmation modal ─────────────────────────────────────────────────
function initDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const btn    = event.relatedTarget;
        const action = btn ? btn.dataset.action : null;
        const confirmBtn = modal.querySelector('#deleteConfirmBtn');
        if (confirmBtn && action) {
            confirmBtn.setAttribute('formaction', action);
        }
    });
}

// ── Flash auto-dismiss (4 seconds) ───────────────────────────────────────────
function initFlashDismiss() {
    const alert = document.getElementById('flashAlert');
    if (!alert) return;

    setTimeout(function () {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
    }, 4000);
}

// ── Live score preview (mirrors MySQL fn_calculate_score) ─────────────────────
function initLiveScore() {
    const form = document.getElementById('laptopForm');
    if (!form) return;

    const fields = ['cpu_tier', 'ram_gb', 'storage_gb', 'condition', 'has_warranty', 'release_year', 'price'];
    fields.forEach(function (name) {
        const el = form.elements[name];
        if (el) el.addEventListener('change', updateScorePreview);
        if (el) el.addEventListener('input', updateScorePreview);
    });

    // Trigger once for edit mode (form pre-filled)
    updateScorePreview();
}

function calculateScore(cpu_tier, ram_gb, storage_gb, condition, has_warranty, release_year, price) {
    let score = 0;

    // CPU Tier (max 30)
    score += parseInt(cpu_tier) * 10;

    // RAM (max 25)
    const ram = parseInt(ram_gb);
    if      (ram >= 32) score += 25;
    else if (ram >= 16) score += 20;
    else if (ram >= 8)  score += 10;
    else                score += 5;

    // Storage (max 15)
    const storage = parseInt(storage_gb);
    if      (storage >= 1000) score += 15;
    else if (storage >= 512)  score += 12;
    else if (storage >= 256)  score += 7;
    else                      score += 3;

    // Kondisi (max 15)
    score += (parseInt(condition) - 1) * 5;

    // Garansi (max 5)
    if (has_warranty) score += 5;

    // Age penalty
    const year = parseInt(release_year);
    if      (year < 2018) score -= 5;
    else if (year < 2020) score -= 2;

    // Harga (fixed 10)
    if (parseInt(price) > 0) score += 10;

    return Math.max(0, score);
}

function getVerdict(score) {
    if (score >= 70) return { label: 'Great Deal', cls: 'success' };
    if (score >= 50) return { label: 'Fair',       cls: 'info'    };
    if (score >= 30) return { label: 'Overpriced', cls: 'warning' };
    return               { label: 'Avoid',      cls: 'danger'  };
}

function updateScorePreview() {
    const form = document.getElementById('laptopForm');
    if (!form) return;

    const get = function (name) {
        const el = form.elements[name];
        if (!el) return 0;
        if (el.type === 'checkbox') return el.checked ? 1 : 0;
        return el.value;
    };

    const cpu_tier     = get('cpu_tier');
    const ram_gb       = get('ram_gb');
    const storage_gb   = get('storage_gb');
    const condition    = get('condition');
    const has_warranty = get('has_warranty');
    const release_year = get('release_year');
    const price        = get('price');

    if (!cpu_tier || !ram_gb || !storage_gb || !condition || !release_year || !price) {
        return;
    }

    const score   = calculateScore(cpu_tier, ram_gb, storage_gb, condition, has_warranty, release_year, price);
    const verdict = getVerdict(score);

    const scoreEl   = document.getElementById('previewScore');
    const verdictEl = document.getElementById('previewVerdict');

    if (scoreEl)   scoreEl.textContent = score;
    if (verdictEl) {
        verdictEl.textContent = verdict.label;
        verdictEl.className   = 'badge fs-6 bg-' + verdict.cls;
    }
}

// ── Client-side form validation ───────────────────────────────────────────────
function initFormValidation() {
    const form = document.getElementById('laptopForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
}
