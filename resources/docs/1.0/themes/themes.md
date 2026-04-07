# Custom Template

Di CBMS Anda dapat menambahkan tema custom khusus untuk client area.

---

- [Client Area Template](#clientAreaTheme)
- [Order Form Template](#orderFormTheme)
- [Menambahkan Tema / Template](#addTheme)
- [Mengunduh Tema / Template](#downloadTheme)
- [Mengunggah Tema / Template](#uploadTheme)

Tema dipisahkan menjadi 2 kategori, yaitu:

> - [Client Area Template](#clientAreaTheme)
> - [Order Form Template](#orderFormTheme)

<a name="clientAreaTheme"></a>

## Client Area Template

Client Area Template adalah tampilan khusus yang diakses oleh *User/Client*, maka dari itu Anda bisa membuat tema baru menyesuaikan Tema Client Area sesuai kebutuhan.


<a name="orderFormTheme"></a>

## Order Form Template

Order Form Template adalah kumpulan halaman mulai dari menampilkan produk sampai dengan proses checkout selesai.

---

<a name="addTheme"></a>

# Menambahkan Tema / Template
Untuk menambahkan tema atau template baru di CBMS cukup mudah karena sudah disediakan interface nya yaitu dengan cara membuka **Admin Area** terlebih dahulu, lalu pilih di sidebar menu **Addons > CBMS Theme Manager**.

<br>

#### Step 1

Pada *Card* **Add New Theme** isi bagian *Type* dengan opsi [**Client Area Template**](#clientAreaTheme) Atau [**Order Form Template**](#orderFormTheme).

<br>

#### Step 2 (Optional)

Bagian *Parent Theme* yaitu referensi struktur dan asset yang akan dipakai, untuk **Client Area Template** sifat nya optional dan untuk **Order Form Template** wajib untuk memilih *Parent Theme*.

<br>

#### Step 3 

Pada bagian *Name* formatnya diharuskan berbentuk **lowercase**.

<br>

#### Step 4

Bagian *Description* berperan untuk memberi keterangan untuk tema yang akan dibuat.

<br>

#### Step 5

Terakhir klik Button **Create** Untuk memulai men-generate tema Anda.

> {info} Setelah berhasil, Folder Client Area Template yang Anda buat tersimpan di direktori `themes\cbms\NamaTema` dan Order Form Template tersimpan di direktori `themes\orderform\NamaTema`.

---

<a name="downloadTheme"></a>

# Mengunduh Tema / Template

Jika template anda ada perubahan di *production*, lebih baik unduh terlebih dahulu template yang akan dirubah dengan cara meng-klik ikon [<i class="fa fa-download" aria-hidden="true"></i>] di table list theme, tema atau template yang sudah diunduh akan berupa file (.zip).

<br>

Setelah template berhasil diunduh, Anda bisa [mengunggahnya](#uploadTheme) ke *local environtment* untuk melakukan update atau perubahan dan bisa mengunggahnya kembali ke *production*.


---

<a name="uploadTheme"></a>

# Mengunggah Tema / Template
Sama seperti membuat tema atau template di CBMS, disediakan juga interface untuk memudahkan pengembangan yang menu nya tersedia di Admin area menu **Addons > CBMS Theme Manager**.

<br>

Sebelum mengunggah template sebaiknya perhatikan format dan struktur foldernya di bawah ini

```
[template.zip]
     │
     └─ public
     └─ resource
     │      └─ views
     └─ composer.json
```

> {warning} Jika ada tema yang terpasang dengan nama yang sama, hapus terlebih dahulu.

Setelah format dan struktur nya sesuai, Anda bisa mengunggah template tersebut ke *local environtment* atau *production*, dengan cara:

<br>

#### Step 1

Pilih *Type* sesuai template yang akan diunggah [**Client Area Template**](#clientAreaTheme) atau [**Order Form Template**](#orderFormTheme).

<br>

#### Step 2

*Default Status* adalah untuk menentukan status tema Anda *Active* atau *Disabled* setelah diunggah  

<br>

#### Step 3

Pilih file .zip yang sudah siap diunggah, lalu klik button **Upload & Extract**

---



