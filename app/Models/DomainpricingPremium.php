<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DomainpricingPremium extends AbstractModel
{
	protected $table = 'domainpricing_premium';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	protected static function booted()
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
			$builder->orderBy("tbldomainpricing_premium.to_amount");
        });
    }

	public static function markupForCost($amount)
    {
        $cost = self::where("to_amount", ">", $amount)->first();
        if (!$cost) {
            return self::where("to_amount", "=", -1)->value("markup");
        }
        return $cost->markup;
    }
}
