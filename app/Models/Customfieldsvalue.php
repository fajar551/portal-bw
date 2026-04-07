<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Customfieldsvalue extends AbstractModel
{
	protected $table = 'customfieldsvalues';
	protected $fillable = ["fieldid", "relid"];
    protected $columnMap = array("relatedId" => "relid");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function getTableName()
	{
		return $this->table;
	}

	public function getRelatedIdAttribute()
	{
		return $this->relid;
	}

	public function customField()
	{
		return $this->belongsTo(Customfield::class, "fieldid");
	}

	public function addon()
	{
		return $this->belongsTo(Addon::class, "relid");
	}

	public function client()
	{
		return $this->belongsTo(Client::class, "relid");
	}
	
	public function service()
	{
		return $this->belongsTo(Hosting::class, "relid");
	}
}
