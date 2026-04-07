<?php

namespace App\Http\Controllers\Admin\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Carbon;
use DataTables;
use App\Helpers\Cfg;
use Illuminate\Support\Facades\DB;
use Database;
use Validator;
use App\Models\Ticket;
use App\Models\Ticketreply;
use API;
use Ticket as TicketHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\LogActivity;
use PhpParser\Node\Expr\FuncCall;

class AnnouncementsController extends Controller
{
    public function __construct()
    {
        $this->prefix=Database::prefix();
        $this->adminURL =request()->segment(1);
    }

    public function index()
    {
        return view ('pages.support.announcements.index');
    }

    public function AnnouncementsGet(Request $request){
        $data = Announcement::select('id','date','title','published');
       // dd($data);
       return Datatables::of($data)
                        ->editColumn('date', function(Announcement $data) {
                            return  Carbon::parse($data->date)->isoFormat(Cfg::get('DateFormat').' H:mm');
                        })
                        ->editColumn('published', function(Announcement $data) {
                                     return  ($data->published == 1 )?'Yes':'No' ;
                        })
                        ->editColumn('title', function(Announcement $data) {
                                return  '<a href="./announcements/edit/'.$data->id.'">'.$data->title.'</a>';
                        })
                        ->addColumn('action', function(Announcement $data) {
                                return '<a href="./announcements/edit/'.$data->id.'" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                                        <a href="./announcements/destroy/'.$data->id.'" data-id="'.$data->id.'"  data-title="'.$data->title.'" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></a>';
                        })
                       /*  ->addColumn('delete', function(Announcement $data) {
                                return '<a href="./announcements/delete/'.$data->id.'" class="btn btn-danger btn-xs">Delete</a>';
                        }) */
                        ->rawColumns(['action','title'])    
                        //->rawColumns(['delete', 'delete'])    
                        ->toJson();
    }

    public function Announcementsdestroy($id){
        $id=(int)$id;
        Announcement::find($id)->delete();
         return back()->with('success', 'User deleted successfully');
    }

    public function Announcements_add()
    {
        return view ('pages.support.announcements.add');
    }
    public function Announcements_edit($id)
    {
        $announcment = Announcement::findOrFail($id);
        return view ('pages.support.announcements.edit', compact('announcment'));
    }

    public function Announcements_update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $validatedData = $request->validate([
            'date' => 'required|date_format:Y-m-d H:i',
            'title' => 'required|string',
            'message' => 'required',
        ]);

        $date = $request->input('date');
        $title = $request->input('title');
        $message = $request->input('message');
        $published = (bool) $request->input('published');

        $announcement->update(array("date" => $date, "title" => \App\Helpers\Sanitize::decode($title), "announcement" => \App\Helpers\Sanitize::decode($message), "published" => (int) $published));

        \App\Helpers\LogActivity::Save("Modified Announcement (ID: " . $id . ")");
        \App\Helpers\Hooks::run_hook("AnnouncementEdit", array("announcementid" => $id, "date" => $date, "title" => $title, "announcement" => $announcement, "published" => $published));
        
        return redirect()->back()->with(['success' => "Changes Saved Successfully!"]);
    }

    public function Announcements_post(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date_format:Y-m-d H:i',
            'title' => 'required|string',
            'message' => 'required',
        ]);

        $date = $request->input('date');
        $title = $request->input('title');
        $message = $request->input('message');
        $published = (bool) $request->input('published');

        // insert
        $announcment = new \App\Models\Announcement;
        $announcment->date = $date;
        $announcment->title = \App\Helpers\Sanitize::decode($title);
        $announcment->announcement = \App\Helpers\Sanitize::decode($message);
        $announcment->published = (int) $published;
        $announcment->save();
        $id = $announcment->id;

        \App\Helpers\LogActivity::Save("Added New Announcement (" . $title . ")");
        \App\Helpers\Hooks::run_hook("AnnouncementAdd", array("announcementid" => $id, "date" => $date, "title" => $title, "announcement" => $message, "published" => $published));

        return redirect()->back()->with(['success' => "Changes Saved Successfully!"]);
    }

}
