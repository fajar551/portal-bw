<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class DynamicTranslation extends Model
{
	protected $table = 'dynamic_translations';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
    public static function saveNewTranslations($relatedId, array $relatedTypes = array()){
        if ($relatedTypes) {
            DynamicTranslation::whereIn("related_type", $relatedTypes)->where("related_id", "=", 0)->update(array("related_id" => $relatedId));
        }

    }
}
