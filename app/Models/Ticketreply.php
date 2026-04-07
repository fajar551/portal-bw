<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Ticketreply extends AbstractModel
{
	protected $table = 'ticketreplies';
	protected $columnMap = array("clientId" => "userid", "contactId" => "contactid");
    protected $dates = array("date");
    protected $hidden = array();
    public $timestamps = false;
    const CREATED_AT = "date";
    protected $guarded = [];

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("ordered", function ($builder) {
            $builder->orderBy("tblticketreplies.date");
        });
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, "tid");
    }

	public function client()
	{
		return $this->belongsTo(Client::class, 'userid', 'id');
	}

	public function contact()
	{
		return $this->belongsTo(Contact::class, 'contactid', 'id');
	}
}
