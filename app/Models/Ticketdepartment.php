<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Ticketdepartment extends Model
{
	protected $table = 'ticketdepartments';
    public $timestamps = false;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function scopeEnforceUserVisibilityPermissions($query)
    {
        if (!Auth::guard('web')->check()) {
            return $query->where("hidden", "")->where("clientsonly", "");
        }
        return $query->where("hidden", "");
    }

	public function getAwaitingreplyAttribute()
	{
		return $this->awaitingreply();
	}
	
	public function getOpenticketsAttribute()
	{
		return $this->opentickets();
	}

	public function awaitingreply()
	{
		$statuses = Ticketstatus::select('title')->awaiting()->get()->pluck('title')->toArray();

		$deptid = $this->id;
		$query = Ticket::query();
		$query->where('merged_ticket_id', 0);
		$query->where('did', $deptid);
		$query->whereIn('status', $statuses);

		return $query->count();
	}

	public function opentickets()
	{
		$statuses = Ticketstatus::select('title')->active()->get()->pluck('title')->toArray();

		$deptid = $this->id;
		$query = Ticket::query();
		$query->where('merged_ticket_id', 0);
		$query->where('did', $deptid);
		$query->whereIn('status', $statuses);

		return $query->count();
	}
}
