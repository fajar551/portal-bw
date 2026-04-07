# Guard

Guard yang dipakai oleh CBMS terbagi menjadi 2, dipakai di semua fungsi-fungsi yang terhubung.

---

## Admin
Admin guard bisa di akses menggunakan facade Auth atau helper auth(). Dipakai dihalaman admin area.
<br>
<br>

**Example**

```php
<?php

// use ...
use Auth;

class YourController extends Controller
{
    public function index()
    {
        $auth = Auth::guard('admin')->user();
        dd($auth->id);
    }
}
```

---

## Client
Cient guard bisa di akses menggunakan facade Auth atau helper auth(). Dipakai dihalaman client area. Guar ini menggunakan guard bawaan Laravel, yaitu `web`.
<br>
<br>

**Example**

```php
<?php

// use ...
use Auth;

class YourController extends Controller
{
    public function index()
    {
        $auth = Auth::guard('web')->user();
        dd($auth->id);
    }
}
```