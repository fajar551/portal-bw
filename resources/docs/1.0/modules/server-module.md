# Server Module

Module server menyediakan pengelolaan servis CBMS. Fungsi inti dari module ini adalah creating, suspending, unsuspending, dan terminating produk atau layanan.
<br>
<br>

Lihat dokumentasi selengkapnya di WHMCS

[Provisioning Modules - WHMCS Developer Documentation](https://developers.whmcs.com/provisioning-modules/)

> {info} Dokumentasi selengkapnya masih dalam tahap pengembangan.

---

- [Membuat Module](#createModule)
- [Menggunakan Console](#usingConsole)
- [Menggunakan GUI](#usingGui)
- [Controller](#controller)
- [View Namespace](#namespace)
- [Konfigurasi](#configuration)
 - [Simple Mode](#configSimple)
 - [Loader Function](#configLoader)
- [Meta Data Parameters](#metadataParams)
- [Supported Functions](#supportedFunctions)
- [Module Parameters](#moduleParameters)
- [Custom Functions](#customFunctions)
- [Client Area Output](#clientAreaOutput)
- [Admin Service Tab](#adminService)

<a name="createModule"></a>

# Membuat Module

Untuk membuat Server Module, Anda bisa melakukannya dengan 2 cara:
 
> {info} Direktori tersimpan di `Modules/Servers`


<a name="usingConsole"></a>

## Menggunakan Console

Buka terminal/cmd pada direktori root aplikasi Anda, ketikkan perintah berikut:

```
php artisan servers:make Namamodule
```

<a name="usingGui"></a>

## Menggunakan GUI

Cara ini sangat mudah, namun membuthkan admin page permission untuk mengakses GUI. Pertama, buka menu di sidebar **Addons > CBMS Module Manager**<br>
Pada bagian **Add New Module** isi type dengan Server/Provisioning, lalu masukkan nama dan tekan tombol Create.<br>
Anda akan menemukan list module di bagian bawah.<br>

---

<a name="controller"></a>

# Controller

Setelah Anda berhasil membuat module, buka file controller utama yang terdapat pada 

```
Modules/Servers/Namamodule/Http/Controllers/NamamoduleController.php
```

didalamnya terdapat method-method bawaan, hapus saja, lalu ikuti langkah selanjutnya yaitu [konfigurasi](#configuration).

---

<a name="namespace"></a>

# View Namespace

Namespace setiap module menggunakan konfigurasi dari property masing-masing [provider](/{{route}}/{{version}}/modules/provider). Biasanya menggunakan lower name. Contoh:

```php
<?php

return view("namamodule::index");
```

---

<a name="configuration"></a>

# Configuration Options

Fungsi ini menentukan pengaturan yang dapat dikonfigurasi per produk untuk modul Anda.
Nama fungsi ini harus `ConfigOptions`
Fields konfigurasi yang didukung berikut ini:

> - Text
> - Password
> - Yes/No Checkboxes
> - Dropdown Menus
> - Radio Buttons
> - Text Areas

Di bawah ini adalah contoh parameter yang tersedia untuk setiap fields. Modul server mendukung hingga 24 opsi yang ditentukan dengan cara ini.

### Example Configuration

```php
<?php

namespace Modules\Servers\Namamodule\Http\Controllers;

// use ...
// use ...

class NamamoduleController extends Controller
{
    public function ConfigOptions()
    {
        return [
            "username" => [
                "FriendlyName" => "UserName",
                "Type" => "text", # Text Box
                "Size" => "25", # Defines the Field Width
                "Description" => "Textbox",
                "Default" => "Example",
            ],
            "password" => [
                "FriendlyName" => "Password",
                "Type" => "password", # Password Field
                "Size" => "25", # Defines the Field Width
                "Description" => "Password",
                "Default" => "Example",
            ],
            "usessl" => [
                "FriendlyName" => "Enable SSL",
                "Type" => "yesno", # Yes/No Checkbox
                "Description" => "Tick to use secure connections",
            ],
            "package" => [
                "FriendlyName" => "Package Name",
                "Type" => "dropdown", # Dropdown Choice of Options
                "Options" => "Starter,Advanced,Ultimate",
                "Description" => "Sample Dropdown",
                "Default" => "Advanced",
            ],
            "packageWithNVP" => [
                "FriendlyName" => "Package Name v2",
                "Type" => "dropdown", # Dropdown Choice of Options
                "Options" => [
                    'package1' => 'Starter',
                    'package2' => 'Advanced',
                    'package3' => 'Ultimate',
                ],
                "Description" => "Sample Dropdown",
                "Default" => "package2",
            ],
            "disk" => [
                "FriendlyName" => "Disk Space",
                "Type" => "radio", # Radio Selection of Options
                "Options" => "100MB,200MB,300MB",
                "Description" => "Radio Options Demo",
                "Default" => "200MB",
            ],
            "comments" => [
                "FriendlyName" => "Notes",
                "Type" => "textarea", # Textarea
                "Rows" => "3", # Number of Rows
                "Cols" => "50", # Number of Columns
                "Description" => "Description goes here",
                "Default" => "Enter notes here",
            ],
        ];
    }
}
```

<a name="configSimple"></a>

## Simple Mode

Mode Konfigurasi Simple Mode adalah fitur yang tersedia bagi pengembang modul untuk menyederhanakan proses konfigurasi untuk sebuah modul.
<br>
<br>

Menggunakan Simple Mode memungkinkan Anda mengurangi jumlah field Pengaturan Modul yang ditampilkan kepada admin secara default. Misalnya, Anda dapat membatasi tampilan Pengaturan Modul default hanya ke field paling umum yang perlu disesuaikan pengguna saat membuat produk yang ditetapkan ke modul Anda, dan bidang lain yang kurang umum digunakan hanya ditampilkan jika pengguna beralih ke Mode Advanced.
<br>
<br>

Untuk mengaktifkan Simple Mode untuk modul Anda, yang perlu Anda lakukan hanyalah mengatur parameter tambahan di definisi field ConfigOptions yang ingin Anda tampilkan dalam Simple Mode. Di bawah ini adalah contoh modul dengan dua field, satu dalam Simple Mode, dan yang lainnya hanya ditampilkan saat dalam Advanced Mode:
<br>
<br>

```php
<?php

namespace Modules\Servers\Namamodule\Http\Controllers;

// use ...
// use ...

class NamamoduleController extends Controller
{
    public function ConfigOptions()
    {
        return [
            'Simple Mode Field' => [
                'Type' => 'text',
                'Size' => '25',
                'SimpleMode' => true,
            ],
            'Advanced Mode Field' => [
                'Type' => 'text',
                'Size' => '25',
            ],
        ];
    }
}

```

> {info} Gunakan `'SimpleMode' => true` untuk mengaktifkannya.

<a name="configLoader"></a>

## Loader Function

Menyetel fungsi loader memungkinkan Anda membuat field yang menawarkan dropdown opsi untuk dipilih oleh admin.
<br>
<br>
Tidak seperti jenis field pengaturan "dropdown" standar yang memungkinkan Anda untuk menawarkan pilihan opsi yang telah ditentukan sebelumnya dan hard-coded kepada admin, field dengan fungsi loader akan menampilkan daftar opsi yang telah diambil secara dinamis dari layanan API.
<br>
<br>
Untuk menggunakan fungsi loader untuk field, saat menentukan bidang dalam fungsi ConfigOptions, Anda juga harus menentukan fungsi loader yang akan dipanggil untuk mengisi bidang dengan daftar opsi yang tersedia.
<br>
<br>
Setiap bidang dapat memiliki fungsi pemuat uniknya sendiri yang ditentukan. Bidang ini akan diisi dengan nilai kembalian dari fungsi loader terkait saat mode sederhana digunakan.
<br>
<br>
Berikut adalah contoh field yang mendefinisikan fungsi loader dengan nama fungsi LoaderFunction
<br>
<br>

```php
<?php

namespace Modules\Servers\Namamodule\Http\Controllers;

// use ...
// use ...

class NamamoduleController extends Controller
{
    public function ConfigOptions()
    {
        return [
            'Loader Populated Field' => [
                'Type' => 'text',
                'Size' => '25',
                'SimpleMode' => true,
                'Loader' => 'LoaderFunction',
            ],
        ];
    }
}
```

Fungsi loader yang Anda tetapkan harus dibuat dan harus mengembalikan array key value pairs.
<br>

Kuncinya harus berupa nilai yang diharapkan diterima oleh modul Anda, dan nilainya harus berupa label tampilan yang ramah manusia untuk nilai kunci. Dalam banyak kasus ini mungkin sama.

<a name="errorHandling"></a>

### Error Handling

Jika koneksi ke API  yang diperlukan untuk mengambil value yang dimuat secara dinamis gagal, kode Anda harus menampilkan throw an Exception.
<br><br>
Sistem akan mengenali Exception dan menampilkan pesan error yang dikembalikan dalam exception itu kepada admin.

```php
<?php

namespace Modules\Servers\Namamodule\Http\Controllers;

// use ...
// use ...

class NamamoduleController extends Controller
{
    // public function ConfigOptions() ...
    
    // harus mengembalikan array key value pairs.
    public function LoaderFunction($params) {
        // Make a call to the remote API endpoint
        $ch = curl_init('https://www.example.com/api/function');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
    
        // Check for any curl errors or an empty response
        if (curl_error($ch)) {
            throw new Exception('Unable to connect: ' . curl_errno($ch) . ' - ' . curl_error($ch));
        } elseif (empty($response)) {
            throw new Exception('Empty response');
        }
    
        // We're done with curl so we can release the resource now
        curl_close($ch);
    
        // Attempt to decode the response
        $packageNames = json_decode($response, true);  
    
        // Check to make sure valid json was returned
        if (is_null($packageNames)) {
            throw new Exception('Invalid response format');
        }
    
        // Format the list of values for display
        // ['value' => 'Display Label']
        $list = [];
        foreach ($packageNames as $packageName) {
            $list[$packageName] = ucfirst($packageName);
        }
    
        return $list;
    }
}
```

---

<a name="metadataParams"></a>

# Meta Data Parameters

Lihat referensi parameter pada dokumentasi WHMCS

[Meta Data Parameters - WHMCS Developer Documentation](https://developers.whmcs.com/provisioning-modules/meta-data-params/)

## Example Meta Data Function

```php
<?php

namespace Modules\Servers\Namamodule\Http\Controllers;

// use ...
// use ...

class NamamoduleController extends Controller
{
    // public function ConfigOptions() ...
    
    public function MetaData() {
        return array(
            'DisplayName' => 'myModule',
            'APIVersion' => '1.1',
            'DefaultNonSSLPort' => '1234',
            'DefaultSSLPort' => '4321',
            'ServiceSingleSignOnLabel' => 'Login to myModule Client',
            'AdminSingleSignOnLabel' => 'Login to myModule Admin',
            'ListAccountsUniqueIdentifierDisplayName' => 'Domain',
            'ListAccountsUniqueIdentifierField' => 'domain',
            'ListAccountsProductField' => 'configoption1',
        );
    }
}
```

---

<a name="supportedFunctions"></a>

# Supported Functions

Lihat pada dokumentasi WHMCS

[Supported Functions - WHMCS Developer Documentation](https://developers.whmcs.com/provisioning-modules/supported-functions/)

---

<a name="moduleParameters"></a>

# Module Parameters

Lihat pada dokumentasi WHMCS

[Module Parameters - WHMCS Developer Documentation](https://developers.whmcs.com/provisioning-modules/module-parameters/)

---

<a name="customFunctions"></a>

# Custom Functions

Fungsi kustom memungkinkan Anda untuk menentukan operasi yang dijalankan menggunakan modul. Fungsi kustom dapat melakukan tindakan atau menentukan halaman atau output di client area. Izin dapat diberikan untuk siapa yang dapat menggunakan setiap fungsi kustom (hanya klien, hanya admin, atau keduanya).
<br>
<br>

Aturan untuk nama fungsi kustom sama dengan fungsi modul lainnya. Itu harus berupa public function.

[Module Parameters - WHMCS Developer Documentation](https://developers.whmcs.com/provisioning-modules/module-parameters/)

### Example Custom Function

Perhatikan contoh fungsi reboot dan shutdown berikut di sistem VM atau VP

```php
<?php

namespace Modules\Servers\Namamodule\Http\Controllers;

// use ...
// use ...

class NamamoduleController extends Controller
{
    // public function ConfigOptions() ...
    
    // public function MetaData() ...
    
    public function reboot($params) {

        # Code to perform reboot action goes here...
    
        if ($successful) {
            $result = "success";
        } else {
            $result = "Error Message Goes Here...";
        }
        return $result;
    }
    
    public function shutdown($params) {
    
        # Code to perform shutdown action goes here...
    
        if ($successful) {
            $result = "success";
        } else {
            $result = "Error Message Goes Here...";
        }
        return $result;
    }
}
```

Contoh di atas mendefinisikan fungsi kustom dan menggunakan variabel yang diteruskan. Fungsi kustom mengembalikan sukses atau pesan kesalahan untuk menunjukkan kegagalan.

> {info} Return `success` untuk mengembalikan nilai true pada sistem. Return selain success akan dianggap pesan kesalahan.

Contoh berikut memungkinkan klien untuk melakukan reboot tetapi hanya mengizinkan admin untuk melakukan reboot atau shutdown:

```php
<?php

namespace Modules\Servers\Namamodule\Http\Controllers;

// use ...
// use ...

class NamamoduleController extends Controller
{
    // public function ConfigOptions() ...
    
    // public function MetaData() ...
    
    // public function reboot($params) ...
    
    // public function shutdown($params) ...
    
    public function ClientAreaCustomButtonArray() {
        $buttonarray = array(
         "Reboot Server" => "reboot",
        );
        return $buttonarray;
    }
    
    public function AdminCustomButtonArray() {
        $buttonarray = array(
         "Reboot Server" => "reboot",
         "Shutdown Server" => "shutdown",
        );
        return $buttonarray;
    }
}

```

value dari array adalah apa yang ditampilkan kepada admin dan klien pada tombol atau opsi menu untuk perintah. Nilainya adalah nama fungsi kustom yang sudah dibuat diatas.

---

<a name="clientAreaOutput"></a>

# Client Area Output

Fungsi lain dari modul adalah untuk memberikan klien akses ke opsi dan output tambahan di dalam client area. Dilakukan baik pada halaman detail produk (menggunakan fungsi ClientArea dari sebuah modul), atau sebagai [fungsi kustom](#customFunctions).

## Product Details Page Output

Membuat output untuk ditampilkan pada halaman yang sama dengan detail produk di client area. <br><br>
Buat file template bernama clientarea.blade.php di dalam folder modul view (Resources/views). Variabel akan menjadi vars modul yang sama seperti yang diteruskan ke setiap fungsi modul. <br><br>
Memungkinkan juga untuk melakukan panggilan API. Fungsi ClientArea di dalam modul yang menjalankan kode apa pun dan mengembalikan array dengan file template untuk digunakan, dan variabel apa pun yang diinginkan selain default.

```php
<?php

namespace Modules\Servers\Namamodule\Http\Controllers;

// use ...
// use ...

class NamamoduleController extends Controller
{
    // public function ConfigOptions() ...
    
    // public function MetaData() ...
    
    // .. other functions
    
    public function ClientArea($vars) {
        return array(
            'templatefile' => 'clientarea',
            'vars' => array(
                'test1' => 'hello',
                'test2' => 'world',
            ),
        );
    }
}
```

---

<a name="adminService"></a>

# Admin Services Tab

Fungsi Tab ini memungkinkan definisi filed tambahan yang muncul pada detail produk di admin area. Digunakan untuk keluaran informasi, atau untuk pengaturan dan nilai yang disimpan dalam tabel khusus atau di luar CBMS.<br><br>
CBMS menggunakan ini dalam sistem inti untuk modul tambahan lisensi kami. Bidang khusus lisensi dari sistem yang diizinkan diatur dan dilihat dari detail produk.<br><br>
Ada 2 fungsi yang berkaitan dengan tab layanan - AdminServicesTabFields dan AdminServicesTabFieldsSave. Yang pertama memungkinkan definisi bidang tambahan untuk output. Yang terakhir memungkinkan penanganan input apa pun pada pengiriman/penyimpanan, jika diperlukan.<br><br>
Jadi sebagai contoh, di bawah ini kami tunjukkan cara mendefinisikan 4 bidang tambahan. Contoh ini menunjukkan output input, dropdown, textarea, dan info saja. Contoh terus memperbaruinya di tabel kustom database melalui acara simpan.<br><br>

---









