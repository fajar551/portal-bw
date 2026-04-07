# Events

Event disini adalah class yang dipakai core untuk kebutuhan custom software

---

- [Daftar Event](#daftarEvent)
- [Custom Event](#customEvent)


Pada dasarnya event adalah class yang dipanggil ketika sebuah hook dibuat. Dan merupakan salah satu bagian penting untuk kustomisasi CBMS. Misalnya digunakan untuk adjustment invoice amount, saat registrasi ataupun login, dan masih banyak lagi.

---

<a name="daftarEvent"></a>

## Daftar Event


Event yang sudah ada terdapat pada direktori `app/Events`.

---

<a name="customEvent"></a>

## Custom Event

Anda bisa menambahkan custom event, dengan perintah berikut:

```php
php artisan make:event YourCustomEvent
```

Ubah class dan constructor menjadi seperti berikut:

```php
<?php

// use ...
// use ...

class YourCustomEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Array $data = [])
    {
        //
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
```

lalu pasang pada controller Anda menggunakan fungsi 

```php
Hooks::run_hook("YourCustomEvent", array("id" => 1));
```