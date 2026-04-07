# Activator

File Activator

---

Setiap module mempunyai nama-nama yang unik. Singkronisasi antara sistem module dengan database terkadang membutuhkan activator ini. File tersimpan dengan nama             
`modules_statuses.json.`
<br>
Aktifator dibuat otomatis oleh sistem jika belum ada. Didalamnya berupa file json dengan value boolean.
<br>

## Default Activator File

```php
{
    "CbmsModuleManager": true,
    "Cbmshookcallbackmanager": true,
    "Cbmsthememanager": true,
    "MaxMind": true
}
```

> {info} File akan menambahkan value otomatis dari module-module yang sudah dibuat. Anda bisa memeriksa singkronisasi module pada menu **Addons > CBMS Module Manager**.