# Used Laptop Deal Evaluator

Sistem evaluasi laptop bekas yang memberikan skor objektif berdasarkan spesifikasi dan harga, sehingga pembeli dapat membuat keputusan pembelian yang lebih eksplisit dan konsisten.

## Cara Kerja

1. **Admin** memasukkan data laptop (spesifikasi + harga) melalui panel admin.
2. **MySQL trigger** (`trg_laptop_insert` / `trg_laptop_update`) otomatis memanggil `fn_calculate_score()` dan menyimpan hasilnya ke tabel `evaluations` beserta verdict (`Great Deal`, `Fair`, `Overpriced`, `Avoid`).
3. **Pengunjung publik** dapat melihat semua laptop, memfilter berdasarkan use case, mencari, dan melihat detail skor per faktor.

## Tech Stack

| Layer | Tech |
|-------|------|
| Backend | Native PHP 8.3 (no framework) |
| Database | MySQL 8.0 |
| CSS | Bootstrap 5.3 via CDN |
| JS | Vanilla JS (no framework) |
| Server | Apache (Docker) |

## Cara Menjalankan

```bash
docker compose up -d --build
```

- **App:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081

## Kredensial Dev

| | |
|--|--|
| Admin username | `admin` |
| Admin password | `admin123` |
| DB host | `db` |
| DB user | `root` |
| DB password | `root` |
| DB name | `laptop_evaluator` |

## Fitur

**Publik:**
- Halaman utama dengan hero section, filter use case, dan pencarian real-time
- Kartu laptop dengan verdict badge dan chips spesifikasi
- Halaman detail dengan tabel spesifikasi dan score breakdown (progress bars per faktor)

**Admin:**
- Login / logout dengan session guard dan CSRF protection
- Dashboard: stat cards (total laptop, brand, rata-rata score) + top 5 laptops
- CRUD Brands (dengan FK constraint error handling)
- CRUD Use Cases (filter berdasarkan min RAM/CPU/Storage)
- CRUD Laptops + form add/edit dengan live score preview (JS mirror formula MySQL)
- Scoring Rules: tabel read-only semua aturan skor grouped by faktor

**Keamanan:**
- Semua query: prepared statements
- Semua output: `htmlspecialchars()` via helper `h()`
- CSRF token pada semua form admin
- Password: `password_hash()` + `password_verify()` (bcrypt)
- `session_regenerate_id(true)` setelah login

## Struktur Folder

```
includes/       config, db, auth, functions, header, footer
admin/          dashboard, laptops, laptop_form, brands, use_cases, scoring_rules
assets/
  css/style.css
  js/main.js
index.php       halaman utama publik
detail.php      halaman detail laptop
login.php
logout.php
database.sql    schema + functions + triggers + views + seed data
docker-compose.yml
Dockerfile
```
