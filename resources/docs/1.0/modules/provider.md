# Provider

Setiap module yang dibuat, mempunyai provider, bisa Anda lihat pada direktori Modules/JenisModule/Namamodule/Providers<br><br>
didalamnya terdapat property $moduleName dan $moduleNameLower yang digunakan untuk memanggil module itu sendiri, baik memanggil view, collection, dll.<br><br>
JenisModule merujuk pada jenis module seperti Addons, Gateways, dll.<br><br>

## Example Module Provider

```php
<?php
 
namespace Modules\JenisModule\Namamodule\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
 
class NamamoduleServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Namamodule';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'namamodule';
 
    /
```

> {warning} Jangan mengubah property nama module, karena bersifat unik dan terintegrasi.