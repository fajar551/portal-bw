# Portal Bikin Website

Portal untuk **penjualan dan pengelolaan layanan website**—pelanggan dapat memesan, mengelola billing, dan mendukung siklus hidup layanan web dari satu sistem.

Basis teknis mengacu pada stack **Cloud Billing Management System (CBMS)** / otomasi billing.

## Persyaratan

- PHP minimal **7.4.x** (sesuaikan dengan versi yang dipakai di environment Anda)

## Instalasi [FRESH]

- Dalam pengembangan

## Instalasi [PROD]

- Siapkan environment produksi
- Clone repositori
- Jalankan `composer install`
- Di terminal, jalankan:
  - `php artisan migrate`
  - `php artisan db:seed`
  - `php artisan adminpermissions:generate`
  - `php artisan apipermissions:generate`
- Cek atau buat file `modules_statuses.json` di root
- Salin `.env` dari `.env.example` dan sesuaikan
- Atur **APP_URL**, database, dan email
- Area admin:
  - Sesuaikan template email dengan sintaks Blade
- File `.htaccess` (opsional, sesuai server)

## Instalasi [LOCAL]

- Dalam pengembangan

## Virtualizor (dynamic)

Harga per jam

- Pasang cron job
- Jalankan: `php artisan virtualizor:run`
  - Contoh: `* * * * * php /path/ke/projek/artisan virtualizor:run`

## Update ke production dengan Git

### Lokal — sinkron ke branch `dev`

Contoh branch aktif: `andiw`

- Push perubahan ke git server
- `git pull origin dev`
- Selesaikan konflik jika ada
- `git push origin andiw`
- Buat merge request dari `andiw` ke `dev`
