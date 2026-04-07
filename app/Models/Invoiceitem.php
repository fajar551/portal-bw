<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Invoiceitem extends AbstractModel
{
	protected $table = 'invoiceitems';
	public $timestamps = false;
	protected $booleans = array("taxed");
    protected $dates = array("dueDate");
    protected $columnMap = array("relatedEntityId" => "relid");

    protected $fillable = [
        'userid',
    ];

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
	public function invoice()
    {
        return $this->belongsTo(Invoice::class, "invoiceid");
    }
    public function addon()
    {
        return $this->belongsTo(Hostingaddon::class, "relid");
    }
    public function domain()
    {
        return $this->belongsTo(Domain::class, "relid");
    }
    public function service()
    {
        return $this->belongsTo(Hosting::class, "relid");
    }
}
