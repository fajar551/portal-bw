<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class TicketWatcher extends AbstractModel
{
	protected $table = 'ticket_watchers';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function scopeOfTicket($query, $ticketId)
    {
        return $query->whereTicketId($ticketId);
    }
    public function scopeByAdmin($query, $adminId)
    {
        return $query->whereAdminId($adminId);
    }
}
