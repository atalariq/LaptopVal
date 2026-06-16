# PRD — Used Laptop Deal Evaluator

## Problem

Banyak orang yang mau beli laptop bekas kebingungan karena penilaiannya subjektif:
"Ini worth it nggak sih?" sering dijawab berdasarkan feeling, bukan kriteria yang jelas.
Hasilnya: pembeli overpay, dapat kondisi buruk, atau salah pilih spek untuk kebutuhannya.

## Solution

Sistem evaluasi laptop bekas yang transparan dan konsisten.
User input spesifikasi laptop → sistem hitung skor otomatis berdasarkan kriteria eksplisit →
keluar verdict: **Great Deal / Fair / Overpriced / Avoid**.

Kriteria scoring-nya terbuka (bisa dilihat di halaman Scoring Rules), jadi user bisa
memahami *kenapa* sebuah laptop dapat verdict tertentu — bukan black box.

## How It Works

1. **Admin** login → input data laptop bekas (spek, kondisi, harga).
2. **MySQL trigger** otomatis hitung `value_score` dan tentukan `verdict` saat laptop disimpan.
3. **Pengunjung** buka website → lihat daftar laptop dengan verdict badge.
4. Filter by **use case** (Office, Gaming, Edit Video, dll) → sistem saring laptop yang memenuhi spek minimum.
5. Klik laptop → lihat **breakdown score per faktor** (CPU, RAM, storage, kondisi, garansi, umur, harga).

## Users

| User | Goal |
|------|------|
| Admin | Input & kelola data laptop bekas |
| Pengunjung | Temukan laptop bekas yang worth it sesuai kebutuhannya |

## Requirements Checklist

### PPW1
- [x] HTML, CSS, JS, PHP, MySQL
- [x] Tidak pakai framework PHP/JS (Bootstrap CSS OK)
- [x] Responsive: mobile, tablet, laptop (Bootstrap 5)
- [x] Konten dinamis dari DB
- [x] Min 2 query tampil di web: 1 single-table, 1 complex JOIN
- [x] Desain rapi mengikuti prinsip desain
- [x] Interactive/direct feedback via JS (live score preview, search, filter)
- [x] Login admin (bonus)

### PBD
- [x] Min 5 tabel: `users`, `brands`, `laptops`, `scoring_rules`, `use_cases`, `evaluations`
- [x] Min 3 complex query: Q1 (JOIN 3 tabel), Q2 (JOIN + filter use case), Q3 (GROUP BY + HAVING)
- [x] Min 2 view: `v_laptop_evaluations`, `v_best_value_laptops`
- [x] Min 2 function: `fn_calculate_score`, `fn_get_verdict`
- [x] Min 2 trigger: `trg_after_laptop_insert`, `trg_after_laptop_update`

## Pages

| File | Akses | Fungsi |
|------|-------|--------|
| `index.php` | Public | Listing laptop + filter use case + search |
| `detail.php?id=X` | Public | Detail spek + score breakdown |
| `login.php` | Public | Form login admin |
| `logout.php` | Admin | Destroy session |
| `admin/dashboard.php` | Admin | Stat cards + top 5 laptops |
| `admin/laptops.php` | Admin | List laptop |
| `admin/laptop_form.php` | Admin | Add/Edit laptop (halaman terpisah) |
| `admin/brands.php` | Admin | CRUD brands (modal) |
| `admin/use_cases.php` | Admin | CRUD use cases (modal) |
| `admin/scoring_rules.php` | Admin | Lihat scoring rules (read-only) |

## Out of Scope
- Public registration / user reviews
- Image upload
- GPU scoring / benchmark API
- Real-time price scraping
- AI integration
- Pagination (diganti search JS)
- Edit scoring rules via UI
