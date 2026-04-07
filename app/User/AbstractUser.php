<?php

namespace App\User;

use Illuminate\Support\Str;

abstract class AbstractUser extends \App\Models\AbstractModel
{
    public abstract function isAllowedToAuthenticate();
    public static function findUuid($uuid)
    {
        if (!$uuid) {
            return null;
        }
        return static::where("uuid", "=", $uuid)->first();
    }
    public static function boot()
    {
        parent::boot();
        static::creating(function (AbstractUser $model) {
            if (!$model->uuid) {
                // $uuid = \Ramsey\Uuid\Uuid::uuid4();
                $model->uuid = (string) Str::uuid();
            }
        });
        static::saving(function (AbstractUser $model) {
            if (!$model->uuid) {
                // $uuid = \Ramsey\Uuid\Uuid::uuid4();
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}

?>
