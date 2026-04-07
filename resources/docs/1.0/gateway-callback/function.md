# Fungsi (Helper)

Helper yang berguna untuk dipakai pada callback.

---

- [Get Gateway Variable](#variables)
- [Validate Callback Invoice ID](#callbackInvoice)
- [Validate Callback Transaction ID](#callbackTransaction)
- [Log Transaction](#logTransaction)
- [Add Payment to the Invoice](#addPayment)

<a name="variables"></a>

## Get Gateway Variables

```php
<?php

/**
 * Get Gateway Variables.
 *
 * Retrieves configuration setting values for a given module name.
 *
 * @param string $gatewayName
 */
$gatewayParams = \App\Helpers\Gateway::getGatewayVariables('yourgatewayname');

```

---

<a name="callbackInvoice"></a>

## Validate Callback Invoice ID

```php
<?php

/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 *
 * @param int $invoiceId
 * @param string $gatewayName
 */
$invoiceId = \App\Helpers\Gateway::checkCbInvoiceID($invoiceId, $gatewayName);

```

Gunakan fungsi ini untuk memverifikasi bahwa ID invoice yang diterima dalam callback adalah valid.
Masukkan `$invoiceid` dan nama gateway ke dalam fungsi. 
<br>
<br>

Jika nomor invoice tidak valid, eksekusi skrip callback akan dihentikan.

---

<a name="callbackTransaction"></a>

## Validate Callback Transaction ID

```php
<?php

/**
 * Check Callback Transaction ID.
 *
 * Performs a check for any existing transactions with the same given
 * transaction number.
 *
 * Performs a die upon encountering a duplicate.

 * @param string $transactionId
 */
\App\Helpers\Gateway::checkCbTransID($transactionId);

```

Gunakan fungsi ini untuk memeriksa transaksi yang ada untuk ID transaksi yang diberikan. Ini melindungi terhadap callback duplikat untuk transaksi yang sama.
<br>
<br>

Jika ID transaksi sudah ada di database, eksekusi skrip callback akan dihentikan.

---

<a name="logTransaction"></a>

## Log Transaction

```php
<?php

/**
 * Log Transaction.
 *
 * Add an entry to the Gateway Log for debugging purposes.
 *
 * The debug data can be a string or an array.
 *
 * @param string $gatewayName Display label
 * @param string|array $debugData Data to log
 * @param string $transactionStatus Status
 */
\App\Helpers\Gateway::logTransaction($gatewayName, request()->all(), $transactionStatus);

```

> Gunakan fungsi ini untuk membuat entri log gateway.
> - Parameter input pertama harus berupa nama modul gateway
> - Parameter input kedua harus berupa larik data yang diterima. Misalnya, menggunakan `request()+>all()`.
> - Parameter input terakhir harus berupa hasil atau status yang dapat dibaca manusia untuk ditampilkan di log.

---

<a name="addPayment"></a>

## Add Payment to the Invoice

```php
<?php

/**
 * Add Invoice Payment.
 *
 * Apply a payment to the given invoice ID.
 *
 * @param int $invoiceId         Invoice ID
 * @param string $transactionId  Transaction ID
 * @param float $paymentAmount   Amount paid (defaults to full balance)
 * @param float $paymentFee      Payment fee (optional)
 * @param string $gatewayModule  Gateway module name
 */
\App\Helpers\Gateway::addInvoicePayment(
    $invoiceId,
    $transactionId,
    $paymentAmount,
    $paymentFee,
    $gatewayModuleName
);
```
> Gunakan fungsi ini untuk menerapkan pembayaran ke faktur.
> - Parameter pertama harus berupa ID invoice untuk menerapkan pembayaran.
> - Parameter kedua harus berupa ID transaksi unik yang disediakan oleh gateway pembayaran.
> - Parameter ketiga harus jumlah yang akan dikreditkan ke invoice. Jika nilai ini adalah 0 atau string kosong, pembayaran akan dianggap sebagai saldo penuh untuk invoice.
> - Parameter keempat harus menjadi biaya yang dibebankan oleh gateway. Jika ini tidak tersedia, setel ini ke 0,00.
> - Parameter kelima harus menjadi nama modul gateway Anda. Anda dapat menggunakan  

        $gatewaytParams['paymentmethod']

---