<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Customfield extends AbstractModel
{
	use Filterable;
	protected $table = 'customfields';
	protected $columnMap = array("relatedId" => "relid", "regularExpression" => "regexpr", "showOnOrderForm" => "showorder", "showOnInvoice" => "showinvoice");
    protected $commaSeparated = array("fieldOptions");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function getTableName()
	{
		return $this->table;
	}

	public static function getFieldName($fieldId, $fallback = "", $language = NULL)
	{
		$fieldName = \Lang::get("custom_field." . $fieldId . ".name", array(), "dynamicMessages", $language);
		if ($fieldName == "custom_field." . $fieldId . ".name") {
			if ($fallback) {
					return $fallback;
			}
			return self::find($fieldId, array("fieldname"))->fieldName;
		}
		return $fieldName;
	}
	
	public static function getDescription($fieldId, $fallback = "", $language = NULL)
    {
        $description = \Lang::get("custom_field." . $fieldId . ".description", array(), "dynamicMessages", $language);
        if ($description == "custom_field." . $fieldId . ".description") {
            if ($fallback) {
                return $fallback;
            }
            return self::find($fieldId, array("description"))->description;
        }
        return $description;
    }
}
