# Membuat Hook

Hook tersimpan pada direktori  __*app/Hooks*__ dan autoload saat event yang digunakan tersedia dan running pada sistem container CBMS. Membuat hook ada 2 cara yaitu:

---

- [Menggunakan Console](#console)
- [Menggunakan GUI](#gui)
- [Hook Return](#hookReturn)
- [Hook Parameter](#hookParameter)
- [Hook Example](#hookExample)

<a name="console"></a>

## Menggunakan Console

Buka terminal/cmd pada direktori root aplikasi Anda, ketikkan perintah berikut:

```php
php artisan hook:make NamaHookAnda
```

Atau dengan tambahan parameter `--event` untuk lebih spesifik merujuk pada event:


```php
php artisan hook:make NamaHookAnda --event=YourCustomEvent
```

---

<a name="gui"></a>

## Menggunakan GUI

Cara ini sangat mudah, namun membutuhkan admin page permission untuk mengakses GUI. Pertama, buka menu di sidebar *Addons > CBMS Hooks & Callback Manager*
<br>
<br>

Pada bagian *Add New Script* isi type dengan Hook, piih event (Opsional), lalu masukkan nama hook dan tekan tombol Create.

<br>

Anda akan menemukan list hook dibagian bawah.

---

<a name="hookReturn"></a>

## Hook Return

Hook yang sudah dibuat, bisa me-return void, array, view, string, dsb. Tergantung dari kebutuhan [event](/{{route}}/{{version}}/hooks/events) yang direturn. Dan harus didalam method handle. Lihat [contoh](#hookExample) dibawah.

---

<a name="hookParameter"></a>

## Hook Parameter

Fungsi handle pada hook digunakan untuk mengambil parameter yang disediakan [event](/{{route}}/{{version}}/hooks/events) berupa objek.<br>
Contoh:

```php
<?php

// class NamaHookAnda {...

public function handle(\App\Events\InvoiceCreated $event)
{
    $invoiceid = $event->invoiceid;
    // ... rest of your hook
}
```

---

<a name="hookExample"></a>

## Hook Example

```php
<?php

namespace App\Hooks;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class NamaHookAnda
{
	/**
	 * Ini adalah example untuk handle hook
	 * 
	 * Hooks return harus salah satu dari: void, string, array
	 * 
	 * @param Event $event merupakan event yang sudah tersedia di App\Events dan mengembalikan object
	 * @return Void|String|Array|View
	 */
	public function handle(\App\Events\EmailPreSend $event)
	{
		/**
		 * @return void
		 * 
		 * berarti tidak mengembalikan apapun
		 * dipakai misalnya untuk logging, call api, dll.
		 */

		/**
		 * @return string
		 * 
		 * mengembalikan string
		*/
		// return "ini hooks return";

		/**
		 * @return array
		 * 
		 * mengembalikan array
		 */
		return [
			'abortsend' => false,
		];

		/**
		 * @return view
		 * 
		 * mengembalikan view html,js,css,etc...
		 */
		// return view("namafile"); // blade
	}
}
```