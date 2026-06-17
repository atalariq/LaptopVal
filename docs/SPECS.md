# Technical Specs — Used Laptop Deal Evaluator

## Stack
- **Backend:** Native PHP 8.3
- **Database:** MySQL 8.0
- **CSS:** Bootstrap 5.3 via CDN
- **JS:** Vanilla JS (no framework)
- **Server:** Apache (Docker)

---

## File Structure

```
project/
├── includes/
│   ├── config.php          ← gitignored, DB credentials
│   ├── config.example.php  ← committed template
│   ├── db.php              ← mysqli connection
│   ├── auth.php            ← session guard + helpers
│   ├── functions.php       ← PHP helpers + DB query functions
│   ├── header_public.php   ← <head> + public navbar
│   ├── header_admin.php    ← <head> + admin topbar
│   └── footer.php          ← closing HTML
├── assets/
│   ├── css/style.css       ← custom overrides on Bootstrap
│   └── js/main.js          ← all vanilla JS
├── admin/
│   ├── dashboard.php
│   ├── laptops.php
│   ├── laptop_form.php     ← add/edit form (halaman terpisah)
│   ├── brands.php
│   ├── use_cases.php
│   └── scoring_rules.php
├── index.php
├── detail.php
├── login.php
├── logout.php
├── database.sql
├── docker-compose.yml
├── Dockerfile
└── README.md
```

---

## Shared Partials

### `includes/header_public.php`
- Terima variabel `$title` (string)
- Output: `<!DOCTYPE html>` → `<head>` (Bootstrap CDN, style.css) → public navbar
- Public navbar: logo/brand name kiri, "Admin Login" link kanan
- Responsive: Bootstrap navbar collapse di mobile

### `includes/header_admin.php`
- Terima variabel `$title` (string)
- Output: `<!DOCTYPE html>` → `<head>` → admin topbar
- Admin topbar: logo kiri, nav links (Dashboard, Laptops, Brands, Use Cases, Scoring Rules), "Logout" kanan
- Active link di-highlight berdasarkan `$_SERVER['SCRIPT_NAME']`

### `includes/footer.php`
- Output: `</main>`, script tags (Bootstrap JS, main.js), `</body></html>`

---

## Public Pages

### `index.php`
**Layout:**
- Hero section: judul + tagline
- Filter bar: button group use cases (fetch dari DB) + tombol "Semua"
- Search bar: input text, filter by model/brand via JS (no page reload)
- Grid laptop cards (Bootstrap `col-md-4`)

**Card content:**
- Brand + Model (judul)
- Verdict badge (color-coded)
- Harga (format Rp)
- Chips: RAM, Storage, CPU tier, Kondisi
- Tombol "Lihat Detail →"

**JS behavior:**
- Click use case button → `fetch()` atau form submit ke `?use_case_id=X`
- Search input `addEventListener('input')` → filter cards DOM secara real-time
- Active state button yang dipilih

**Queries used:**
- Q1 (all laptops JOIN brands + evaluations) — default
- Q2 (JOIN + filter use case) — saat use case dipilih

---

### `detail.php?id=X`
**Layout:**
- Breadcrumb: Home > [Model]
- Kiri: specs table (Model, Brand, Tahun, CPU, RAM, Storage, Kondisi, Garansi, Harga)
- Kanan: score card (angka besar + verdict badge) + progress bars per faktor

**Progress bars (score breakdown):**
| Faktor | Max | Formula display |
|--------|-----|-----------------|
| CPU Tier | 30 | tier × 10 |
| RAM | 25 | <8→5, 8→10, 16→20, ≥32→25 |
| Storage | 15 | <256→3, 256→7, 512→12, ≥1000→15 |
| Kondisi | 15 | (kondisi-1) × 5 |
| Garansi | 5 | ada→5, tidak→0 |
| Age penalty | -5 | <2018→-5, <2020→-2, ≥2020→0 |
| Harga | 10 | fixed 10 (neutral) |

**Error handling:** `$id = (int)($_GET['id'] ?? 0); if ($id <= 0)` → redirect `index.php` + flash error "Laptop tidak ditemukan." (explicit cast sebelum query untuk menghindari PHP 8 TypeError).

---

## Auth Pages

### `login.php`
- Form centered, card Bootstrap
- Field: username, password
- POST → `password_verify()` → `session_regenerate_id(true)` → set `$_SESSION['user_id']` + `$_SESSION['username']`
- Gagal → flash error di halaman yang sama
- Sudah login → redirect `admin/dashboard.php`

### `logout.php`
- `session_destroy()` → redirect `login.php`

---

## Admin Pages

### `admin/dashboard.php`
**Sections:**
1. **Stat cards (3 kartu):**
   - Total Laptops
   - Total Brands
   - Rata-rata Score (AVG dari semua evaluations)
2. **Top 5 Laptops** — tabel dari `v_best_value_laptops` LIMIT 5
   - Kolom: Model, Brand, Score, Verdict badge, Harga

### `admin/brands.php`
**Layout:** Tabel list brands + tombol "Tambah Brand" → modal

**Modal Add/Edit:**
- Field: Name (required), Notes (textarea, optional)
- Submit → POST → INSERT atau UPDATE → redirect back + flash

**Delete:** Tombol delete → Bootstrap modal konfirmasi → POST `?action=delete&id=X`
- Wrap DELETE query dalam `try-catch`. Jika MySQL throws FK constraint violation (errno 1451), redirect back + `set_flash('Gagal: Brand ini masih digunakan oleh data laptop.', 'danger')`.

**Columns tabel:** ID, Name, Notes, Actions (Edit / Delete)

### `admin/use_cases.php`
**Layout:** Tabel list + tombol "Tambah Use Case" → modal

**Modal Add/Edit:**
- Field: Name, Min RAM (number), Min CPU Tier (select 1/2/3), Min Storage (number)

**Delete:** Bootstrap modal konfirmasi

### `admin/laptops.php`
**Layout:** Tabel list semua laptop + tombol "Tambah Laptop"

**Columns:** Model, Brand, Harga, Score, Verdict, Oleh (Creator), Actions (Edit / Delete)

**Delete:** Bootstrap modal konfirmasi

**Tombol Add/Edit** → arahkan ke `laptop_form.php` (halaman terpisah)

### `admin/laptop_form.php?id=X` (edit) atau tanpa param (add)
**Layout:** 2-column grid
- **Kiri (col-md-7):** form fields
- **Kanan (col-md-5):** live score preview panel (sticky)

**Form fields:**
| Field | Input type | Validasi |
|-------|-----------|----------|
| Model | text | required, max 100 |
| Brand | select (dari DB) | required |
| Release Year | number | required, 2000–2025 |
| CPU Tier | select (Low/Mid/High) | required |
| RAM (GB) | number | required, >0 |
| Storage (GB) | number | required, >0 |
| Kondisi | select (Buruk/Cukup/Baik/Mulus) | required |
| Garansi | checkbox | — |
| Harga (ribu IDR) | number | required, >0 |
| Foto Laptop | file (JPG/PNG/WebP) | opsional, max 2MB |

**Live score preview (JS):**
- `addEventListener('input'/'change')` pada semua field
- Hitung estimasi score dengan formula yang sama (JS mirror dari MySQL function)
- Update panel kanan: angka score + verdict label + color — real-time tanpa submit
- Pada mode **edit** (form pre-filled dari DB): panggil fungsi kalkulasi sekali di `DOMContentLoaded` agar preview tidak kosong saat halaman baru dibuka

**Validation:**
- Client-side: JS cek field kosong + range sebelum submit
- Server-side: PHP cek ulang semua field, return error flash jika invalid

**Submit:**
- Add → INSERT → trigger otomatis isi `evaluations` → redirect `laptops.php` + flash sukses
- Edit → UPDATE → trigger otomatis update `evaluations` → redirect `laptops.php` + flash sukses

### `admin/scoring_rules.php`
**Layout:** Tabel read-only, grouped by factor

**Columns:** Factor, Value Key, Points, Description

---

## JS (`assets/js/main.js`)

| Fungsi | Trigger | Behavior |
|--------|---------|----------|
| Search filter | `input` event pada search bar | Filter cards/rows secara real-time by model + brand name |
| Use case filter | `click` button group | Submit filter form atau set hidden input + submit |
| Live score preview | `input`/`change` semua form fields | Hitung score, update panel preview |
| Delete modal | `click` tombol delete | Set action URL ke modal confirm button |
| Form validation | `submit` event | Block submit jika ada field invalid, tampilkan pesan |
| Flash auto-dismiss | DOM ready | Flash alert fade out setelah 4 detik |

---

## CSS (`assets/css/style.css`)

Custom overrides minimal, semua komponen pakai Bootstrap default:
- Verdict badge colors (success/info/warning/danger)
- Score number styling (font besar di detail page)
- Card hover effect (subtle shadow)
- Sticky score preview panel
- Hero section gradient

---

## Security Rules
- Semua output: `h()` = `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
- Semua query: prepared statements, tidak ada string concat ke SQL
- `condition` selalu backtick: `` `condition` ``
- Admin pages: `require_login()` di baris pertama setelah includes
- Password: `password_hash()` + `password_verify()` (bcrypt)
- Login: `session_regenerate_id(true)` setelah `password_verify()` berhasil (cegah session fixation)
- CSRF: generate `$_SESSION['csrf_token']` di `includes/auth.php` saat session start. Semua form admin wajib include `<input type="hidden" name="csrf_token" value="...">`. Validasi token di setiap POST handler sebelum eksekusi query.

---

## Flash Message System
- Set: `set_flash('pesan', 'success'|'danger'|'warning')`
- Get + clear: `get_flash()` → array `['msg' => ..., 'type' => ...]`
- Render: Bootstrap alert di awal `<main>`, auto-dismiss via JS setelah 4 detik

---

## Credentials (Dev/Docker)
- DB Host: `db`, User: `root`, Pass: `root`, DB: `laptop_evaluator`
- Admin login: `admin` / `admin123`
- App: http://localhost:8080
- phpMyAdmin: http://localhost:8081
