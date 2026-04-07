# Addons Module

Addons Module merupakan esktensi dari CBMS, dimana didalamnya Anda bisa membuat route, provider, database, model, dan masih banyak lagi.

---

Penerapan addons module lebih fleksibel karena Anda bebas me-routing, seperti Anda membuat aplikasi itu sendiri.
<br>
<br>

Aktifkasi melalui **Setup > Addon Modules** menu di sidebar admin area. Sekali di aktifasi, menunya akan tersedia pada sidebar admin area, **Addons > Nama Module Anda** (Jika permissionnya tepat dan ada fungsi output ke admin area)

> {info} Dokumentasi selengkapnya masih dalam tahap pengembangan.

- [Membuat Module](#createModule)
- [Menggunakan Console](#usingConsole)
- [Menggunakan GUI](#usingGui)
- [Controller](#controller)
- [View Namespace](#namespace)
- [Konfigurasi](#configuration)
- [Function Activate & Deactivate](#activeDeactive)
- [Admin Area Output](#adminAreaOutput)
- [Client Area Output](#clientAreaOutput)

<a name="createModule"></a>

# Membuat Module

Untuk membuat Addons Module, Anda bisa melakukannya dengan 2 cara:

> {info} Direktori tersimpan di `Modules/Addons`

---

<a name="usingConsole"></a>

## Menggunakan Console

Buka terminal/cmd pada direktori root aplikasi Anda, ketikkan perintah berikut:

```php
php artisan addons:make Namamodule
```

<a name="usingGui"></a>

## Menggunakan GUI

Cara ini sangat mudah, namun membuthkan admin page permission untuk mengakses GUI. Pertama, buka menu di sidebar **Addons > CBMS Module Manager**
<br>
<br>

Pada bagian **Add New Module** isi type dengan Addons Module, lalu masukkan nama dan tekan tombol Create.
<br>
<br>

Anda akan menemukan list module di bagian bawah.

---

<a name="controller"></a>

# Controller

Setelah Anda berhasil membuat module, buka file controller utama yang terdapat pada
<br>

```
Modules/Addons/Namamodule/Http/Controllers/NamamoduleController.php
```
<br>

didalamnya terdapat method-method bawaan, hapus saja, lalu ikuti langkah selanjutnya yaitu [konfigurasi](#configuration).

---

<a name="namespace"></a>

# View Namespace

Namespace setiap module menggunakan konfigurasi dari property masing-masing [provider](/{{route}}/{{version}}/modules/provider). Biasanya menggunakan lower name. <br>
Contoh:
```php
<?php

return view("namamodule::index");
```

---

<a name="configuration"></a>

# Konfigurasi
Langkah pertama yaitu membuat method config, untuk mendefinisikan data konfigurasi. 


### Example Config

```php
<?php

namespace Modules\Addons\Namamodule\Http\Controllers;

// use ...
// use ...

class NamamoduleController extends Controller
{
    public function config()
    {
        return array(
            "name" => "Addon Example",
            "description" => "This is a sample config function for an addon module",
            "version" => "1.0",
            "author" => "CBMS",
            "fields" => array(
                "option1" =>
                    array (
                        "FriendlyName" => "Option1",
                        "Type" => "text",
                        "Size" => "25",
                        "Description" => "Textbox",
                        "Default" => "Example",
                    ),
                "option2" =>
                    array (
                        "FriendlyName" => "Option2",
                        "Type" => "password",
                        "Size" => "25",
                        "Description" => "Password",
                    ),
                "option3" =>
                    array (
                        "FriendlyName" => "Option3",
                        "Type" => "yesno",
                        "Size" => "25",
                        "Description" => "Sample Check Box",
                    ),
                "option4" =>
                    array (
                        "FriendlyName" => "Option4",
                        "Type" => "dropdown",
                        "Options" => "1,2,3,4,5",
                        "Description" => "Sample Dropdown",
                        "Default" => "3",
                    ),
                "option5" =>
                    array (
                        "FriendlyName" => "Option5",
                        "Type" => "radio",
                        "Options" => "Demo1,Demo2,Demo3",
                        "Description" => "Radio Options Demo",
                    ),
                "option6" =>
                    array (
                        "FriendlyName" => "Option6",
                        "Type" => "textarea",
                        "Rows" => "3",
                        "Cols" => "50",
                        "Description" => "Description goes here",
                        "Default" => "Test",
                    ),
            ),
        );
    }
}
```

Baca lebih lanjut langsung pada dokumentasi WHMCS

[Configuration - WHMCS Developer Documentation](https://developers.whmcs.com/addon-modules/configuration/)

---

<a name="activeDeactive"></a>

# Fungsi Activate & Deactivate

Modul dapat berisi fungsi activate dan deactivate. Fungsi-fungsi ini berjalan ketika pengguna admin mengaktifkan atau menonaktifkan modul di area konfigurasi Modul Addon.


### Example Activate Function

```php

<?php

namespace Modules\Addons\Namamodule\Http\Controllers;

// use ...
// use ...
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class NamamoduleController extends Controller
{
    // public function config() ...
    
    public function activate()
    {
        try {
            if (!Schema::hasTable('mod_example')) {
                //
                Schema::create('mod_example', function (Blueprint $table) {
                    $table->id();
                    $table->string('name')->nullable();
                    $table->integer('total')->nullable();
                    $table->timestamps();
                });
            }

            return [
                // Supported values here include: success, error or info
                'status' => 'success',
                'description' => 'Module enabled'
            ];
        } catch (\Exception $e) {
            return [
                // Supported values here include: success, error or info
                'status' => "error",
                'description' => 'Unable to create your module: ' . $e->getMessage(),
            ];
        }
    }
}
```

### Example Deactivate Function

```php
<?php

namespace Modules\Addons\Namamodule\Http\Controllers;

// use ...
// use ...
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class NamamoduleController extends Controller
{
    // public function config() ...

    // public function activate() ...
    
    public function deactivate()
    {
        // Undo any database and schema modifications made by your module here
        try {
            Schema::dropIfExists('mod_example');

            return [
                // Supported values here include: success, error or info
                'status' => 'success',
                'description' => 'Module has been disabled'
            ];
        } catch (\Exception $e) {
            return [
                // Supported values here include: success, error or info
                'status' => "error",
                'description' => 'Unable to drop your module: ' . $e->getMessage(),
            ];
        }
    }
}

```

---

<a name="adminAreaOutput"></a>

# Admin Area Output

### Example Output Function

Output dapat berupa return view atau string html

```php
<?php

namespace Modules\Addons\Namamodule\Http\Controllers;

// use ...
// use ...
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class NamamoduleController extends Controller
{
    // public function config() ...
    
    public function output($vars)
    {
        $modulelink = $vars['modulelink'];
        $version = $vars['version'];
        $option1 = $vars['option1'];
        $option2 = $vars['option2'];
        $option3 = $vars['option3'];
        $option4 = $vars['option4'];
        $option5 = $vars['option5'];
        $option6 = $vars['option6'];
        
        return view("namamodule::index");
    }
}
```


<a name="clientAreaOutput"></a>

# Client Area Output

Modul Addon juga mendukung output client area. Ini dilakukan dengan menggunakan fungsi clientarea di dalam modul.<br>
Fungsionalitas memungkinkan modul untuk mengembalikan output dalam bentuk file template. File template disimpan di dalam folder modul (Resources/views).<br>
Anda dapat mengembalikan judul halaman dan variabel template. Anda juga dapat meminta login klien dengan respons benar/salah yang sederhana.<br>
Akses modul client area menggunakan URL dalam format index.php?m=namamodule<br>

### Example Client Area Function

```php
<?php

namespace Modules\Addons\Namamodule\Http\Controllers;

// use ...
// use ...
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class NamamoduleController extends Controller
{
    // public function config() ...
    
    public function clientarea($vars)
    {
        $modulelink = $vars['modulelink'];
        $version = $vars['version'];
        $option1 = $vars['option1'];
        $option2 = $vars['option2'];
        $option3 = $vars['option3'];
        $option4 = $vars['option4'];
        $option5 = $vars['option5'];
        $option6 = $vars['option6'];
        
        return array(
            'pagetitle' => 'Addon Module',
            'templatefile' => 'namamodule::clienthome',
            'requirelogin' => true, # accepts true/false
            'vars' => array(
                'testvar' => 'demo',
                'anothervar' => 'value',
                'sample' => 'test',
            ),
        );
    }
}
```
Di atas mengasumsikan sebuah template, clienthome.blade.php, yang ada di dalam folder modul untuk digunakan sebagai output.

> {info} Catatan: templatefile mengikuti aturan [view namespace](#namespace).


