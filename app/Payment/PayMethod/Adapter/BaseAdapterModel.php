<?php

namespace App\Payment\PayMethod\Adapter;

abstract class BaseAdapterModel extends \App\Models\AbstractModel implements \App\Payment\Contracts\PayMethodAdapterInterface
{
    use \App\Payment\PayMethod\Traits\TypeTrait;
    use \App\Payment\PayMethod\Traits\PayMethodFactoryTrait;
    public $timestamps = true;
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($model) {
            if ($model instanceof \App\Payment\Contracts\SensitiveDataInterface) {
                $model->wipeSensitiveData();
                $model->save();
            }
        });
    }
    public function payMethod()
    {
        return $this->morphOne(\App\Payment\PayMethod\Model::class, "payment");
    }
    public function client()
    {
        return $this->payMethod->client();
    }
    public function contact()
    {
        return $this->payMethod->contact();
    }
    public function getEncryptionKey()
    {
        $key = "";
        if ($this->payMethod && $this->client) {
            $userId = $this->client->id;
            $cc_encryption_hash = \Config::get("portal")["hash"]["cc_encryption_hash"];
            $key = md5($cc_encryption_hash . $userId);
        }
        return $key;
    }
}

?>
