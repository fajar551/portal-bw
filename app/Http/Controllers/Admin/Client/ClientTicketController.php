<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\Client as HelpersClient;
use App\Helpers\ResponseAPI;
use App\Helpers\Ticket as HelpersTicket;

// Models
use App\Models\Ticket;
use App\Models\Note;

// Traits
use App\Traits\DatatableFilter;

class ClientTicketController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
        ]);
        
        if ($validator->fails()) {
            return view('pages.clients.viewclients.clienttickets.index', [
                'invalidClientId' => true,
            ]);
        }

        $userid = $userId = $request->userid;
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $tickets = Ticket::userId($userId)->notMerged()->with("department", "flaggedAdmin")->get();
        $endOfLastMonth = Carbon::now()->subMonth()->lastOfMonth()->endOfDay()->toDateTimeString();
        $endOfTwoMonthsAgo = Carbon::now()->subMonth(2)->lastOfMonth()->endOfDay()->toDateTimeString();
        $firstOfThisMonth = Carbon::now()->firstOfMonth()->toDateTimeString();
        $endOfLastYear = Carbon::now()->subYear(1)->lastOfYear()->endOfDay()->toDateTimeString();
        $endOfTwoYearsAgo = Carbon::now()->subYear(2)->lastOfYear()->endOfDay()->toDateTimeString();
        $firstOfThisYear = Carbon::now()->firstOfYear()->toDateTimeString();

        // Template vars for view usage
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["tickets"] = $tickets;
        $templatevars["notesCount"] = Note::where('userid', $userid)->count();
        $templatevars["ticketCounts"] = [
            "thisMonth" => $tickets->filter(function (Ticket $value) use($endOfLastMonth) {
                return $endOfLastMonth < $value->date;
            })->count(), 
            "lastMonth" => $tickets->filter(function (Ticket $value) use($endOfTwoMonthsAgo, $firstOfThisMonth) {
                return $endOfTwoMonthsAgo < $value->date && $value->date < $firstOfThisMonth;
            })->count(), 
            "thisYear" => $tickets->filter(function (Ticket $value) use($endOfLastYear) {
                return $endOfLastYear < $value->date;
            })->count(), 
            "lastYear" => $tickets->filter(function (Ticket $value) use($endOfTwoYearsAgo, $firstOfThisYear) {
                return $endOfTwoYearsAgo < $value->date && $value->date < $firstOfThisYear;
            })->count()
        ];

        return view('pages.clients.viewclients.clienttickets.index', $templatevars);
    }

    public function clientTicketCommand(Request $request)
    {
        $action = $request->action;
        $ticketIds = $request->ticketIds;
        $userId = $request->userid;

        switch ($action) {
            case 'merge':
                return $this->merge($ticketIds, $userId);
            case 'close':
                return $this->close($ticketIds);
            case 'delete':
                return $this->delete($ticketIds);
            default:
                break;
        }

        return abort(404, "Unknown action!");
    }

    private function merge($ticketIds, $userId)
    {
        sort($ticketIds);
        $mainTicket = $ticketIds[0];
        unset($ticketIds[0]);

        try {
            Ticket::where("userid", $userId)
                    ->where("id", $mainTicket)
                    ->firstOrFail()
                    ->mergeOtherTicketsInToThis($ticketIds);
        } catch (\Exception $e) {
            return ResponseAPI::Error([
                'message' => 'Something went wrong! ' .$e->getMessage(),
            ]);
        }

        return ResponseAPI::Success([
            'message' => "Ticket merged successfully!",
        ]);
    }

    private function close($ticketIds)
    {
        foreach ($ticketIds as $ticketId) {
            try {
                HelpersTicket::CloseTicket($ticketId);
            } catch (\Exception $e) {
                AdminFunctions::logAdminActivity("Unable to close ticket. Ticket ID: $ticketId - Error: " . $e->getMessage());
                return ResponseAPI::Error([
                    'message' => 'Something went wrong! ' .$e->getMessage(),
                ]);
            }
        }

        return ResponseAPI::Success([
            'message' => "Ticket closed successfully!",
        ]);
    }

    private function delete($ticketIds)
    {
        $error = false;

        foreach ($ticketIds as $ticketId) {
            try {
                HelpersTicket::DeleteTicket($ticketId);
            } catch (\App\Exceptions\Fatal $e) {
                AdminFunctions::logAdminActivity("Unable to delete ticket. Ticket ID: $ticketId - Error: " . $e->getMessage());
                $error = $e->getMessage();
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        if ($error) {
            return ResponseAPI::Error([
                'message' => 'Something went wrong! ' .$error,
            ]);
        }

        return ResponseAPI::Success([
            'message' => "Ticket deleted successfully!",
        ]);
    }

    public function dtClientTicket(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $userid = $dataFiltered["userid"];
        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
        ];

        $query = Ticket::userId($userid)->notMerged()->with("department", "flaggedAdmin");

        return datatables()->of($query)
            ->addColumn('flag', function($row) {
                $src = asset("/assets/flag/" .strtolower($row->priority) ."priority.gif");
                $title = $alt = __("admin.status" . strtolower($row->priority));

                return sprintf("<img src=\"%s\" data-toggle=\"tooltip\" title=\"%s\" alt=\"%s\" >", $src, $title, $alt);
            })
            ->editColumn('date', function($row) {
                return \App\Helpers\Carbon::parse($row->date->timestamp)->toAdminDateTimeFormat();
            })
            ->addColumn('department', function($row) {
                $dept = $row->department->name;
                if ($row->flaggedAdminId) {
                    $dept .= " ({$row->flaggedAdmin->fullName}) ";
                }

                return $dept;
            })
            ->addColumn('subject', function($row) {
                $route = route('admin.pages.support.supporttickets.view', ['id' => $row->id]);
                $title = "# {$row->ticketNumber} - {$row->title}";

                return "<a href=\"{$route}\">{$title}</a>";
            })
            ->editColumn('status', function($row) {
                return HelpersTicket::getStatusColour($row->status);
            })
            ->addColumn('last_reply', function($row) {
                $originTime = $row->lastReply->toDateTimeString();

                return $row->lastReply->diffForHumans();
            })
            ->addColumn('raw_id', function($row) {
                $route = route('admin.pages.support.supporttickets.view', ['id' => $row->id]);
                $title = "# {$row->ticketNumber} - {$row->title}";

                return "<a href=\"{$route}\">{$title}</a>";
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('flag', function($query, $order) {
                $query->orderBy('urgency', $order);
            })
            ->orderColumn('subject', function($query, $order) {
                $query->orderBy('tid', $order);
            })
            ->orderColumn('last_reply', function($query, $order) {
                $query->orderBy('lastreply', $order);
            })
            ->rawColumns(['flag', 'raw_id', 'subject', 'status'])
            ->addIndexColumn()
            ->toJson();
    }

}
