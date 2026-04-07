# About this application
Cloud Billing Management System Automatic

# Requirements
- PHP minimal 7.4.*
- Laptop MAC

# [FRESH] Installation
- on progress

# [PROD] Installation
- Niat
- Wudu
- Clone projek
- Run `composer install`
- Run this on console:
    - `php artisan migrate`
    - `php artisan db:seed`
    - `php artisan adminpermissions:generate`
    - `php artisan apipermissions:generate`
- Cek/Buat file `modules_statuses.json` di root
- Buat file .env
- Setting APP_URL, Database dan Email
- Setting Admin Area:
    - Ubah email template sesuaikan dengan syntak blade
- Buat .htaccess file (optional)

# [LOCAL] Installation
- on progress

## Virtualizor (Dynamic) Installation
Price /hour
- Setup Cron job
- Runner script `artisan virtualizor:run`
    - Contoh: `* * * * * php path/to/projek/artisan virtualizor:run`

## Update ke Production pake Git 
### Local - Update ke branch dev
misal active branch: `andiw`
- push perubahan kita dulu ke gitserver
- `git pull origin dev`
- resolve konflik jika ada
- `git push origin andiw`
- merge request `andiw` into `dev`

### 
