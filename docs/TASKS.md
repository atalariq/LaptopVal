# Tasks — Used Laptop Deal Evaluator

Urutan implementasi: foundation → auth → public → admin.
Setiap task bisa dieksekusi independen setelah dependency-nya selesai.

---

## Phase 1 — Foundation (DONE)
- [x] `database.sql` — schema, functions, triggers, views, seed data
- [x] `includes/config.php` — DB credentials + constants
- [x] `includes/db.php` — mysqli connection
- [x] `includes/auth.php` — session guard, `require_login()`, `is_logged_in()`
- [x] `docker-compose.yml` + `Dockerfile`
- [x] `includes/functions.php` — helpers + all DB query functions
  - ⚠️ `format_price()` fixed: price × 1000 sebelum format (price stored as ribuan IDR)

---

## Phase 2 — Shared Layout

- [x] **T01** `includes/header_public.php`
  - `<!DOCTYPE html>` → `<head>` (Bootstrap 5 CDN, style.css)
  - Public navbar: APP_NAME kiri, "Admin Login" link kanan
  - Terima `$title` variabel

- [x] **T02** `includes/header_admin.php`
  - `<head>` sama seperti public
  - Admin topbar: logo kiri, nav links (Dashboard / Laptops / Brands / Use Cases / Scoring Rules), Logout kanan
  - Highlight active link berdasarkan `SCRIPT_NAME`
  - Terima `$title` variabel

- [x] **T03** `includes/footer.php`
  - Render flash message (Bootstrap alert, auto-dismiss 4 detik via JS)
  - Bootstrap JS bundle CDN
  - `assets/js/main.js` script tag
  - Tutup `</body></html>`

- [x] **T04** `assets/css/style.css`
  - Verdict badge color overrides
  - Score number large font (detail page)
  - Card hover shadow
  - Sticky score preview panel
  - Hero gradient

- [x] **T05** `assets/js/main.js`
  - `initSearch()` — real-time DOM filter cards/rows by model + brand
  - `initDeleteModal()` — set action URL ke confirm button modal
  - `initFlashDismiss()` — auto fade-out alert setelah 4 detik
  - `initLiveScore()` — live score calculator (mirror MySQL fn_calculate_score)
  - `initFormValidation()` — block submit + tampilkan pesan error
  - Semua pakai `addEventListener`, tidak ada `onclick` inline

---

## Phase 3 — Auth

- [x] **T06** `login.php`
  - Form card centered (Bootstrap)
  - POST handler: query user by username → `password_verify()` → `session_regenerate_id(true)` → set session
  - Gagal: flash error + stay di halaman
  - Sudah login: redirect `admin/dashboard.php`

- [x] **T06b** CSRF token setup di `includes/auth.php`
  - Generate `$_SESSION['csrf_token']` saat session start (jika belum ada)
  - Tambah helper `csrf_token(): string` dan `verify_csrf(): void` (redirect + flash jika token tidak cocok)
  - Semua form admin wajib include hidden input token ini

- [x] **T07** `logout.php`
  - `session_destroy()` → redirect `login.php`

---

## Phase 4 — Public Pages

- [x] **T08** `index.php`
  - Hero section (judul + tagline)
  - Use case button group (fetch dari DB `get_use_cases()`)
  - Search bar (input, JS filter real-time)
  - Loop laptop cards — pakai `get_laptops($conn, $use_case_id)`
  - Card: Brand+Model, verdict badge, harga, RAM/Storage/CPU chips, tombol detail
  - Pass `$use_case_id` dari `$_GET['use_case_id']` (int cast, default 0)

- [x] **T09** `detail.php`
  - `$id = (int)($_GET['id'] ?? 0); if ($id <= 0)` → redirect index + flash error (explicit cast, cegah PHP 8 TypeError)
  - Jika `get_laptop()` return null → `set_flash('Laptop tidak ditemukan.', 'danger')` + redirect index
  - Layout 2-column: specs table kiri, score card kanan
  - Progress bars per faktor (CPU, RAM, Storage, Kondisi, Garansi, Age, Harga)
  - Hitung poin per faktor di PHP (bukan recalculate score, hanya breakdown display)

---

## Phase 5 — Admin Pages

- [x] **T10** `admin/dashboard.php`
  - `require_login()`
  - Stat cards: Total Laptops, Total Brands, Avg Score
  - Tabel Top 5 Laptops dari `get_best_laptops($conn, 5)`

- [x] **T11** `admin/brands.php`
  - `require_login()`
  - Tabel list brands
  - Modal Add: form Name + Notes → POST `?action=add`
  - Modal Edit: populate via JS data attributes → POST `?action=edit&id=X`
  - Modal Delete konfirmasi → POST `?action=delete&id=X`
  - Handler POST di atas halaman: prepared statement INSERT/UPDATE/DELETE
  - DELETE: wrap dalam `try-catch` — jika FK constraint violation (errno 1451), flash error "Brand ini masih digunakan oleh data laptop"
  - Semua POST handler: `verify_csrf()` sebelum eksekusi query

- [x] **T12** `admin/use_cases.php`
  - `require_login()`
  - Tabel list use cases
  - Modal Add/Edit: Name, Min RAM, Min CPU Tier (select), Min Storage
  - Modal Delete konfirmasi
  - Handler POST

- [x] **T13** `admin/laptops.php`
  - `require_login()`
  - Tabel list: Model, Brand, Harga, Score, Verdict badge, Actions
  - Tombol "Tambah Laptop" → link ke `laptop_form.php`
  - Tombol Edit → link ke `laptop_form.php?id=X`
  - Modal Delete konfirmasi → POST `?action=delete&id=X`

- [x] **T14** `admin/laptop_form.php` _(depends on T06b for CSRF)_
  - `require_login()`
  - Detect mode: `$id = $_GET['id'] ?? null` → add atau edit
  - Layout 2-column: form kiri (`col-md-7`), live preview kanan (`col-md-5`, sticky)
  - Semua field: Model, Brand select, Year, CPU Tier, RAM, Storage, Kondisi, Garansi, Harga
  - Client-side validation via `initFormValidation()`
  - Live score preview via `initLiveScore()` — panggil sekali di `DOMContentLoaded` untuk mode edit (form pre-filled)
  - POST handler: validate PHP → INSERT atau UPDATE → redirect `laptops.php` + flash

- [x] **T15** `admin/scoring_rules.php`
  - `require_login()`
  - Tabel read-only dari `get_scoring_rules($conn)`, grouped by factor
  - Penjelasan singkat formula di atas tabel

---

## Phase 6 — Finishing

- [x] **T16** `README.md`
  - Problem statement (singkat, dari PRD)
  - Cara kerja sistem (flow: admin input → trigger → score → verdict)
  - Tech stack
  - Cara run Docker (`docker compose up -d --build`)
  - Kredensial dev: admin/admin123, ports 8080/8081
  - Daftar fitur lengkap
  - Struktur folder

- [x] **T17** Final check
  - Semua query pakai prepared statements
  - Semua output pakai `h()` / `htmlspecialchars`
  - `condition` selalu backtick di semua query
  - Test responsive: mobile, tablet, laptop
  - Test flow: add laptop → cek trigger auto-insert evaluation
  - Test flow: edit laptop → cek trigger auto-update evaluation
  - Test filter use case
  - Test live score preview
  - Test delete confirmation modal
  - Zip project + export .sql → siap submit

---

## Dependency Map

```
T01, T02, T03 → diperlukan semua halaman
T04, T05      → diperlukan semua halaman (CSS/JS)
T06, T07      → auth, diperlukan semua admin pages
T08, T09      → public, bisa paralel setelah T01/T03/T04/T05
T10–T15       → admin, bisa paralel setelah T01/T02/T03/T04/T05/T06
T16, T17      → terakhir, setelah semua done
```
