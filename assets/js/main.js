document.addEventListener("DOMContentLoaded", function () {
  initThemeToggle();
  initSearch();
  initDeleteModal();
  initFlashDismiss();
  initFormValidation();

  if (document.getElementById("laptopForm")) {
    initLiveScore();
  }
});

// ── Real-time search filter ───────────────────────────────────────────────────
function initSearch() {
  const input = document.getElementById("searchInput");
  if (!input) return;

  input.addEventListener("input", function () {
    const query = this.value.toLowerCase().trim();

    // Table rows (admin lists)
    document.querySelectorAll("tr[data-model]").forEach(function (row) {
      const text = (row.dataset.model + " " + row.dataset.brand).toLowerCase();
      row.style.display = text.includes(query) ? "" : "none";
    });
  });
}

// ── Delete confirmation modal ─────────────────────────────────────────────────
function initDeleteModal() {
  const modal = document.getElementById("deleteModal");
  if (!modal) return;

  modal.addEventListener("show.bs.modal", function (event) {
    const btn = event.relatedTarget;
    const action = btn ? btn.dataset.action : null;
    const confirmBtn = modal.querySelector("#deleteConfirmBtn");
    if (confirmBtn && action) {
      confirmBtn.setAttribute("formaction", action);
    }
  });
}

// ── Flash auto-dismiss (4 seconds) ───────────────────────────────────────────
function initFlashDismiss() {
  const alert = document.getElementById("flashAlert");
  if (!alert) return;

  setTimeout(function () {
    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
    bsAlert.close();
  }, 4000);
}

// ── Live score preview (mirrors MySQL fn_calculate_score) ─────────────────────
function initLiveScore() {
  const form = document.getElementById("laptopForm");
  if (!form) return;

  const fields = [
    "cpu_tier",
    "ram_gb",
    "storage_gb",
    "condition",
    "has_warranty",
    "release_year",
    "price",
  ];
  fields.forEach(function (name) {
    const el = form.elements[name];
    if (el) el.addEventListener("change", updateScorePreview);
    if (el) el.addEventListener("input", updateScorePreview);
  });

  // Trigger once for edit mode (form pre-filled)
  updateScorePreview();
}

let scorePreviewTimer = null;
function updateScorePreview() {
  const form = document.getElementById("laptopForm");
  if (!form) return;
  const get = function (name) {
    const el = form.elements[name];
    if (!el) return "";
    if (el.type === "checkbox") return el.checked ? 1 : 0;
    return el.value;
  };
  const cpu_tier = get("cpu_tier"),
    ram_gb = get("ram_gb"),
    storage_gb = get("storage_gb"),
    condition = get("condition"),
    release_year = get("release_year"),
    price = get("price");
  if (
    !cpu_tier ||
    !ram_gb ||
    !storage_gb ||
    !condition ||
    !release_year ||
    !price
  )
    return;

  const params = new URLSearchParams({
    cpu_tier,
    ram_gb,
    storage_gb,
    condition,
    has_warranty: get("has_warranty"),
    release_year,
    price,
  });

  clearTimeout(scorePreviewTimer);
  scorePreviewTimer = setTimeout(function () {
    fetch("score_preview.php?" + params.toString())
      .then(function (r) {
        return r.json();
      })
      .then(function (d) {
        const scoreEl = document.getElementById("previewScore");
        const verdictEl = document.getElementById("previewVerdict");
        if (scoreEl) scoreEl.textContent = d.score;
        if (verdictEl) {
          verdictEl.textContent = d.verdict;
          const cls =
            {
              "Great Deal": "success",
              Fair: "info",
              Overpriced: "warning",
              Avoid: "danger",
            }[d.verdict] || "secondary";
          verdictEl.className = "badge fs-6 bg-" + cls;
        }
      })
      .catch(function () {
        /* keep previous value on error */
      });
  }, 250);
}

// ── Client-side form validation ───────────────────────────────────────────────
function initFormValidation() {
  const form = document.getElementById("laptopForm");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add("was-validated");
  });
}

// ── Dark / Light theme toggle ─────────────────────────────────────────────────
function initThemeToggle() {
  const btn = document.getElementById("themeToggle");
  if (!btn) return;
  const root = document.documentElement;
  const sync = function () {
    btn.textContent = root.getAttribute("data-theme") === "light" ? "☀️" : "🌙";
  };
  sync();
  btn.addEventListener("click", function () {
    const next = root.getAttribute("data-theme") === "light" ? "dark" : "light";
    root.setAttribute("data-theme", next);
    localStorage.setItem("theme", next);
    sync();
    document.dispatchEvent(new CustomEvent("themechange", { detail: next }));
  });
  window
    .matchMedia("(prefers-color-scheme: light)")
    .addEventListener("change", function (e) {
      if (localStorage.getItem("theme")) return; // manual override wins
      root.setAttribute("data-theme", e.matches ? "light" : "dark");
      sync();
      document.dispatchEvent(new CustomEvent("themechange"));
    });
}
