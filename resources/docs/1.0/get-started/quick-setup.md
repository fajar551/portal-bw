#  Quick Setup

Clone source code dari gitserver ke lokal laptop Anda. Ikuti instruksi berikut:

---

>{warning} Pastikan Anda sudah mendapatkan branch Anda sendiri di gitserver oleh atasan Anda.

<a name="step-1"></a>
## Step 1
Buka terminal/cmd, ubah direktori dimana tempat Anda akan menyimpan source code. 

```php
cd path/to/your/dir
```

Clone dari gitserver

```php
git clone git@103.28.12.31:cbms-auto/cbms-auto.git
```

Ubah direktori ke cbms

```php
cd cbms-auto
```

Checkout ke branch Anda sendiri. Misalkan disini branch Anda adalah `joko`

```php
git checkout joko
```

---


<a name="step-2"></a>
## Step 2

Install dependencies menggunakan composer
```php
composer install
```

---

<a name="step-3"></a>
## Step 3

Copy .env file lalu setup database, smptp mail, app url, dll. Jangan lupa generate security key.

```php
cp .env.example .env
```

```php
php artisan key:generate
```

---

<a name="step-4"></a>
## Step 4

Import database terbaru. Minta akses database ke atasan Anda lalu untuk panduan confignya <br> [di sini](/{{route}}/{{version}}/get-started/resources#db).

---

<a name="step-5"></a>
## Step 5

Buka web browser, kemudian buka alamat situs Anda. Misal Anda menggunakan perintah

```php
php artisan serve
```
buka http://127.0.0.1:8000 lalu jika sudah benar maka akan muncul halaman login client area.

---

<a name="step-6"></a>
## Step 6

Masih pada terminal/cmd Anda, jika ada perubahan maka push ke gitserver.
Add lalu Commit terlebih dahulu jika ada perubahan pada core.

```php
git push origin joko
```

Atau langsung `git push`.

---