<a name="intro"></a>

# Resources

Di sini tersedia sumber daya seperti akun **cPanel**, **passphrase** dan **environtment**

---

- [Introduction](#intro)
- [Fleksibel](#fleksibel)
- [Konsep Digital](#kdi)
- [Relabs](#relabs)
- [Config Database](#db)

Sejauh ini CBMS Auto telah diaplikasikan pada 3 platform, yaitu:

> -   [Fleksibel](https://cloud.fleksibel.com/)
> -   [Konsep Digital Indonesia](https://portal.konsepdigital.id)
> -   [Relabs](https://portal.internetan.id)

Platform diatas masing-masing mempunyai **environtment** berbeda, maka dari itu harus disesuaikan

---

> {warning} Pastikan sebelum mengakses *cPanel* anda sudah punya akun dan terhubung ke VPN Qwords

<a name="fleksibel"></a>

## Fleksibel

Akun cPanel & passphrase yang digunakan:  

```php
username = 'portalflex'
password = 'TO?0=VmP&ReZ'
passphrase = 'gYA-vCRINoHv'
```

Link [**cPanel**](https://cloud.fleksibel.com/cpanel)

### Akun admin
Url: https://cloud.fleksibel.com/admin
```
Username: superuser
Password: Masukah123!
```

---

<a name="kdi"></a>

## Konsep Digital

Akun cPanel & passphrase yang digunakan: 

```php
username = 'portalkdi'
password = '&iMhd^&NQj}{'
passphrase = 'VLBEP.zP1ANO'
```

Link [**cPanel**](https://portal.konsepdigital.id/cpanel)

### Akun admin
Url: https://portal.konsepdigital.id/admin
```
Username: superuser
Password: password
```
---

<a name="relabs"></a>

## Relabs

Akun cPanel & passphrase yang digunakan: 

```php
username = 'relabs'
password = 'gW8pV3sX7lE7jT9l'
passphrase = 'YSRc3llVEvE9'
```
Link [**cPanel**](https://portal.relabs.id/cpanel)

### Akun admin
Url: http://portal.internetan.id/admin
```
Username: superuser
Password: Masukah123!
```

---

<a name="db"></a>

# Database

Untuk pengembangan secara local database yang dipakai harus menyesuaikan dengan platform nya, untuk database nya sendiri bisa diimport dari *cPanel* masing-masing platform, silahkan akses file `.env` untuk mengaturnya.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=NAMA_DATABASE
DB_USERNAME=root
DB_PASSWORD=
```

> {info} **DB_DATABASE** disesuaikan dengan nama database yang telah diimport ke local anda 

---
