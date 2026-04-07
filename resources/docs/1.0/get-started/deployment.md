# Deployment

Proses untuk mengupload project CBMS dari _Git_ ke _Production_

---

> {warning} Diperlukan basic knowledge _Git_ dan _cPanel_

<a name="step-1"></a>

## Step 1

Langkah pertama yang perlu dilakukan adalah **Merge Request** dari _Source branch_ anda sendiri yang sebelumnya sudah dibuat [di sini](/{{route}}/{{version}}/get-started/quick-setup#step-1) ke _Target branch_ `dev`.

<br>

Jika terjadi **conflict**, bisa solve terlebih dahulu di local dengan mengikuti langkah di <br> [**Merge Changes**](/{{route}}/{{version}}/get-started/merge-changes)

<br>

Setelah **conflict** disolve bisa diulangi step ke 1 sehingga branch anda dan `dev` berhasil dimerge dan dapat melanjutkan ke step berikutnya.

---

<a name="step-2"></a>

## Step 2

Setelah step ke 1 berhasil, sekarang branch `dev` sudah membawa update atau progress terbaru dari _branch_ anda.

<br>

Untuk selanjutnya hampir mirip dengan step ke 1, lakukan **Merge Request** dari _**Source branch**_ `dev` lalu pilih _**Target branch**_ salah satu dari list branch yang ada di bawah ini:

> {primary} Perlu diketahui ada beberapa branch `dev` lainnya yaitu:
>
> -   `devRelabs` untuk platform Relabs
> -   `devFlesibel` untuk platform Fleksibel
> -   `devKdi` untuk platform Konsep Digital

Seperti biasa jika ada **conflict** bisa disolve terlebih dahulu sebelum melanjutkan ke step berikutnya.

---

<a name="step-3"></a>

## Step 3

Branch yang sudah anda pilih sebagai _**Target branch**_ di step sebelumnya sudah mendapatkan update terbaru dari branch `dev` utama atau **core**, yang harus dilakukan selanjutnya adalah melakukan **Merge Request** lagi dengan _*Source branch*_ nya adalah _*Target branch*_ yang anda pilih di step ke 2 dan pada **Merge Request** kali ini yang menjadi _*Target branch*_ adalah sebagai berikut:

> {primary} Perlu diketahui lagi ada branch yang berperan seperti branch `master` yaitu:
>
> -   `relabs` untuk platform Relabs
> -   `fleksibel` untuk platform Fleksibel
> -   `kdi` untuk platform Konsep Digital

##### <u>Perhatikan</u>

> {danger.fa-close} Pada **Merge Request** kali ini _Source_ dan _Target_ nya harus berpasangan, contoh: `devRelabs` => `relabs`

Seperti biasa jika ada conflict bisa disolve terlebih dahulu sebelum melanjutkan ke step berikutnya.

---

<a name="step-4"></a>

## Step 4

Setelah branch _"master"_ platform nya mendapatkan update atau perubahan terbaru dari branch _"dev"_ platform nya maka step selanjutnya yaitu mengupload project ke hosting atau server dengan cara:

> 1. Buka _**cPanel**_ pilih platform mana yang akan di update. Untuk akun nya bisa dilihat [di sini](/{{route}}/{{version}}/get-started/resources)
> 2. Setelah berhasil masuk login ke _**cPanel**_ lanjut pilih **Terminal** lalu ketik command:

##### Relabs

```install
cd public_html/portal
```

##### Fleksibel & Konsep Digital

```install
cd public_html/
```

> 3. Lanjut ketik command di bawah ini untuk mengtahui apakah ada perubahan secara live/production agar tidak terjadi conflict.

```install
git status
```

> 4. Jika ada perubahan secara live maka perubahan itu harus di commit dan dipush ke *master_branch_platform* project tersebut, jika tidak ada silahkah lewati step ini.

<br>

> {warning} On Progress

<br>

> 5. Selanjutnya mengupdate project production dengan cara menuliskan command di bawah ini:

```install
git pull origin `master_branch_platform`
```

> 6. Copy & Paste `passphrase` dan sesuaikan dengan platform yang akan diupdate semuanya sudah disediakan [di sini](/{{route}}/{{version}}/get-started/resources).
> 7. Selesai. 

---

## Flowchart
Lihat proses bagaimana cara deploy dalam flowchart berikut:

### Contoh kasus portal relabs
Menjelaskan bagaimana cara update core ke relabs
![image](/assets/images/flowchart/flowchart-deploy-relabs.png)
