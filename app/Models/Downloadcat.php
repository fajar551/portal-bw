<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Downloadcat extends AbstractModel
{
	protected $table = 'downloadcats';
	protected $columnMap = array("isHidden" => "hidden");
    protected $booleans = array("isHidden");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function parentCategory()
    {
        return $this->hasOne(Category::class, "id", "parentid");
    }
    public function childCategories()
    {
        return $this->hasMany(Category::class, "parentid");
    }
    public function downloads()
    {
        return $this->hasMany(Download::class, "category");
    }
    public function scopeOfParent($query, $parentId = 0)
    {
        return $query->where("parentid", "=", $parentId);
    }
    public function scopeVisible($query)
    {
        return $query->where("hidden", "=", "0");
    }
}
