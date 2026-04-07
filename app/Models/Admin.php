<?php

namespace App\Models;

use Auth;
use Database;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;

class Admin extends Authenticatable implements CanResetPasswordContract
{
    //
    use HasRoles, Notifiable, CanResetPassword;

    protected $table = 'admins';
    // protected $guard = 'admin';
    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    public function getTableName()
    {
        return $this->table;
    }

    public static function getAuthenticatedUser()
    {
        $auth = Auth::guard('admin')->user();
        $adminId = $auth ? $auth->id : 0;
        return 0 < $adminId ? self::find($adminId) : null;
    }

    public function getFullNameAttribute()
	{
		return trim($this->firstname . " " . $this->lastname);
	}

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\AdminResetPasswordNotification($token));
    }
}
