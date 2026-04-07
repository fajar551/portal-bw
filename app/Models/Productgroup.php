<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Productgroup extends AbstractModel
{
	protected $table = 'productgroups';
	protected $guarded = ['_token'];
	protected $columnMap = array("orderFormTemplate" => "orderfrmtpl", "disabledPaymentGateways" => "disabledgateways", "isHidden" => "hidden", "displayOrder" => "order");
    protected $booleans = array("isHidden");
    protected $commaSeparated = array("disabledPaymentGateways");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	protected static function booted()
	{
		static::addGlobalScope('order', function (Builder $builder) {
			$t = new self();
			$table = $t->table;
			$builder->orderBy("{$table}.order")->orderBy("{$table}.id");
		});
	}

	public function getTableName()
	{
		return $this->table;
	}

	public function products()
	{
		return $this->hasMany(Product::class, "gid");
	}

	public static function getGroupName($groupId, $fallback = "", $language = NULL)
	{
		$name = \Lang::get("product_group." . $groupId . ".name", array(), "dynamicMessages", $language);
		if ($name == "product_group." . $groupId . ".name") {
			if ($fallback) {
				return $fallback;
			}
            $g = self::find($groupId, array("name"));
			return $g ? $g->name : "";
		}
		return $name;
	}
}
