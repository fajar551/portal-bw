# Route

Route yang di pakai oleh Admin dan Client bisa Anda lihat di masing-masing file, karena file routing untuk keduanya terpisah.

---

## Admin
Anda bisa mengakses halaman admin area menggunakan route `/admin` (default). Atau bisa anda atur pada file `.env` pada bagian ADMIN_ROUTE_PREFIX dan harus disesuaikan dengan `APP_URL_ADMIN`

```php
APP_URL_ADMIN = "http://cbms.local/admin"
# ...

ADMIN_ROUTE_PREFIX = admin
# ...
```

File yang dipakai yaitu ada pada `routes/admin.php`