# Route

Route API berada pada direktori routes/api. Masing-masing file merupakan group api untuk nanti diakses lewat url.<br><br>
Group api merupakan nama file yang didalamnya memanggil masing-masing nama api. Sebagai contoh:<br><br>
pada file routes/api/orders.php ada POST GetOrders<br><br>
HTTP method yang digunakan route api harus POST dan mempunyai name yang sama dengan nama api. Contoh:<br><br>

```php
<?php

// ...

Route::namespace('API\Orders')->group(function () {
    Route::post('GetOrders', 'OrdersController@GetOrders')->name('GetOrders');
});
```