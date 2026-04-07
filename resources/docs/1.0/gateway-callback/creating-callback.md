# Membuat Callback

Jika Anda sedang membuat module payment gateway, yang membutuhkan notifikasi ketika sebuah pembayaran dibuat, berhasil atau gagal, Anda bisa membuat file callback dengan 2 cara:

>{info} Callback file disini adalah Controller class yang biasa dipakai untuk routing. File callback terisimpan di direktori ```app/Http/Controllers/Callback```

---

- [Menggunakan Console](#console)
- [Menggunakan GUI](#gui)
- [Example](#example)
- [Callback URL](#callbackUrl)

<a name="console"></a>

## Menggunakan Console

Buka terminal/cmd pada direktori root aplikasi Anda, ketikkan perintah berikut:

```php
php artisan callback:make NamaCallback
```

---

<a name="gui"></a>

## Menggunakan GUI

Cara ini sangat mudah, namun membuthkan admin page permission untuk mengakses GUI. Pertama, buka menu di sidebar **Addons > CBMS Hooks & Callback Manager**
<br>
<br>

Pada bagian **Add New Script** isi type dengan Gateway Callback, lalu masukkan nama callback dan tekan tombol Create.
<br>
<br>

Anda akan menemukan list callback dibagian bawah.

---

<a name="example"></a>

## Example

```php
<?php

namespace App\Http\Controllers\Callback;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NamaCallback extends Controller
{
    // HTTP post
    public function notification(Request $request)
    {
        \Log::debug($request->all());
    }
}
```

---

<a name="callbackUrl"></a>

## Callback URL

Anda bisa mengakses callback url (POST atau GET) menggunakan link berikut:

```php
http://domain.com/modules/gateways/callback/NamaCallback/namaMethod
```

`NamaCallback` merupakan nama Controller class yang sudah Anda buat, dan nama case sensitif.
<br>
<br>

`nameMethod` merupakan nama method pada Controller class Anda (dalam Example diatas methodnya `notification`).


---