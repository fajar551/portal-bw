<?php

namespace App\Models;

use DB;
use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Ticket extends AbstractModel
{
	use Filterable;
	protected $table = 'tickets';
	// public $timestamps = false;
    protected $columnMap = array("ticketNumber" => "tid", "departmentId" => "did", "subject" => "title", "flaggedAdminId" => "flag", "replyingAdminId" => "replyingadmin", "adminRead" => "adminunread", "priority" => "urgency", "createdByAdminUser" => "admin", "mergedWithTicketId" => "merged_ticket_id");
    protected $commaSeparated = array("adminunread");
    protected $dates = array("date", "lastreply", "replyingtime");
    protected $hidden = array("flag", "adminunread", "clientunread", "replyingadmin", "replyingtime");
    const CREATED_AT = "date";
    const PRIORITY_LOW = "low";
    const PRIORITY_MEDIUM = "medium";
    const PRIORITY_HIGH = "high";

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("ordered", function ($builder) {
            $builder->orderBy("tbltickets.lastreply");
        });
    }

	public function client()
	{
		return $this->belongsTo(Client::class, 'userid', 'id');
	}

	public function contact()
	{
		return $this->belongsTo(Contact::class, 'contactid', 'id');
	}

	public function replies()
	{
		return $this->hasMany(Ticketreply::class, 'tid', 'id');
	}

	public function notes()
	{
		return $this->hasMany(Ticketnote::class, 'ticketid', 'id');
	}

    public function mergedTicket()
    {
        return $this->hasOne(Ticket::class, "merged_ticket_id");
    }

    public function mergeOtherTicketsInToThis(array $ticketIds)
    {
        $saveRequired = false;
        \App\Helpers\Ticket::addTicketLog($this->id, "Merged Tickets " . implode(",", $ticketIds));
        // TODO: getUsersLang($this->userId);
        $merge = \Lang::get("ticketmerge");
        if (!$merge || $merge == "" || $merge == "ticketmerge") {
            $merge = "MERGED";
        }
        if (strpos($this->title, " [" . $merge . "]") === false) {
            $this->title = $this->title . " [" . $merge . "]";
            $saveRequired = true;
        }
        $ticketStatus = $this->status;
        $ticketLastReply = $this->lastReply;
        foreach ($ticketIds as $id) {
            if ($id != $this->id) {
                try {
                    $mergingTicketData = Ticket::findOrFail($id);
                    DB::table("tblticketlog")->where("tid", "=", $id)->update(array("tid" => $this->id));
                    DB::table("tblticketnotes")->where("ticketid", "=", $id)->update(array("ticketid" => $this->id));
                    $mergingTicketData->replies()->update(array("tid" => $this->id));
                    $newReply = new Ticketreply();
                    $newReply->tid = $this->id;
                    $newReply->clientId = $this->userId;
                    $newReply->name = $mergingTicketData->name;
                    $newReply->email = $mergingTicketData->email;
                    $newReply->date = $mergingTicketData->date;
                    $newReply->message = $mergingTicketData->message;
                    $newReply->admin = $mergingTicketData->admin;
                    $newReply->attachment = $mergingTicketData->attachment;
                    $newReply->editor = $mergingTicketData->editor;
                    $newReply->save();
                    if ($ticketLastReply < $mergingTicketData->lastReply) {
                        $ticketLastReply = $mergingTicketData->lastReply;
                        $ticketStatus = $mergingTicketData->status;
                    }
                    $mergingTicketData->mergedTicketId = $this->id;
                    $mergingTicketData->status = "Closed";
                    $mergingTicketData->message = "";
                    $mergingTicketData->admin = "";
                    $mergingTicketData->attachment = "";
                    $mergingTicketData->email = "";
                    $mergingTicketData->flaggedAdminId = 0;
                    $mergingTicketData->save();
                    $mergingTicketData->mergedTicket()->update(array("merged_ticket_id" => $this->id));
                    \App\Helpers\Ticket::addTicketLog($mergingTicketData, "Ticket ID: " . $mergingTicketData->id . " Merged with Ticket ID: " . $this->id);
                } catch (\Exception $e) {
                }
            }
        }
        if ($this->lastReply < $ticketLastReply) {
            $this->lastReply = $ticketLastReply;
            $this->status = $ticketStatus;
            $saveRequired = true;
        }
        if ($saveRequired) {
            $this->save();
        }
    }

    public function department()
    {
        return $this->belongsTo(Ticketdepartment::class, "did");
    }

    public function scopeUserId($query, $userId)
    {
        return $query->where("userid", $userId);
    }

    public function scopeNotMerged($query)
    {
        return $query->where("merged_ticket_id", 0);
    }

    public function flaggedAdmin()
    {
        return $this->belongsTo(Admin::class, "flag");
    }
}
