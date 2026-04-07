<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Deviceauth extends AbstractModel
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
	protected $table = 'deviceauth';
	protected $primaryKey = "id";
    protected $permissions = NULL;
    protected $fillable = array("identifier", "secret", "compat_secret", "user_id", "is_admin", "role_ids", "description");
    protected $booleans = array("is_admin");
    protected $dates = array("last_access");
    protected $casts = array("role_ids" => "json");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
	public static function boot()
    {
        parent::boot();
        static::deleted(function ($model) {
            if ($model->exists && $model->secret) {
                $model->update(array("secret" => "", "compat_secret" => ""));
            }
        });
        static::saving(function ($model) {
            $secret = $model->secret;
            $hasher = new \App\Helpers\Password();
            $hashInfo = $hasher->getInfo($secret);
            if ($hashInfo["algoName"] != \App\Helpers\Password::HASH_BCRYPT) {
                $model->secret = $hasher->hash($secret);
                $model->compat_secret = $hasher->hash(md5($secret));
            }
        });
    }
    public function scopeByIdentifier($query, $identifier)
    {
        return $query->where("identifier", "=", $identifier);
    }
    public function admin()
    {
        if (!$this->is_admin) {
            throw new \RuntimeException("Device identity not associate with an admin user");
        }
        return $this->belongsTo("\\App\\User\\Admin", "user_id");
    }
    public static function newAdminDevice(\App\User\Admin $admin, $description = "")
    {
        $secret = static::generateSecret();
        $attributes = array("identifier" => static::generateIdentifier(), "secret" => $secret, "user_id" => $admin->id, "is_admin" => 1, "description" => (string) $description);
        return new static($attributes);
    }
    public function verify($userInput)
    {
        if (!$this->secret) {
            return false;
        }
        $hasher = new \App\Helpers\Password();
        return $hasher->verify($userInput, $this->secret);
    }
    public function verifyCompat($userInput)
    {
        if (!$this->compat_secret) {
            return false;
        }
        $hasher = new \App\Helpers\Password();
        return $hasher->verify($userInput, $this->compat_secret);
    }
    public static function generateIdentifier()
    {
        return Str::random(32);
    }
    public static function generateSecret()
    {
        return Str::random(32);
    }
    public function rolesCollection()
    {
        $roleIds = $this->role_ids;
        if (!is_array($roleIds)) {
            $roleIds = array();
        }
        $collection = array();
        foreach ($roleIds as $id) {
            // $apiRole = \WHMCS\Api\Authorization\ApiRole::find($id);
            $apiRole = \Spatie\Permission\Models\Role::where(['id' => $id, 'guard_name' => 'api'])->first();
            if (!is_null($apiRole)) {
                $collection[$id] = $apiRole;
            }
        }
        return $collection;
    }
    public function permissions()
    {
        if (!$this->permissions || $this->isDirty("role_ids")) {
            $roles = $this->rolesCollection();
            $perms = [];
            foreach ($roles as $roleId => $role) {
                $perms[] = $role->permissions->pluck('name')->all();
            }
            // $aggregateRolePermissions = new \WHMCS\Authorization\Rbac\AccessList($roles);
            $this->permissions = collect($perms)->flatten()->unique()->values()->all();
        }
        return $this;
    }
    public function isAllowed($item)
    {
        return in_array($item, $this->permissions);
    }
    public function addRole($role)
    {
        $currentRoles = $this->role_ids;
        if (!is_array($currentRoles)) {
            $currentRoles = array();
        }
        $roleId = (int) $role->id;
        if (!in_array($roleId, $currentRoles)) {
            array_push($currentRoles, $roleId);
            $this->role_ids = array_filter($currentRoles);
        }
        return $this;
    }
    public function removeRole($role)
    {
        $this->role_ids = array_diff($this->role_ids, array((int) $role->id));
        return $this;
    }
    public static function purgeRoleFromAllDevices($role)
    {
        $roleId = $role->id;
        $devices = Deviceauth::Where("role_ids", "=", "[" . $roleId . "]")->orWhere("role_ids", "like", "[" . $roleId . ",%")->orWhere("role_ids", "like", "%," . $roleId . ",%")->orWhere("role_ids", "like", "%," . $roleId . "]")->get();
        $updated = array();
        foreach ($devices as $device) {
            if (in_array($roleId, $device->role_ids)) {
                $device->removeRole($role);
                $device->save();
                $updated[] = $device->identifier;
            }
        }
        if ($updated) {
            $msg = sprintf("Removed role \"%d%s\" from API identifiers \"%s\"", $roleId, $role->name ? ": " . $role->name : "", implode(", ", $updated));
            \App\Helpers\LogActivity::Save($msg);
        }
    }
}
