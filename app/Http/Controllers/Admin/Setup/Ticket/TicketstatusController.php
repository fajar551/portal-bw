<?php

namespace App\Http\Controllers\Admin\Setup\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\AdminRole;
use App\Models\Admin;
use App\Helpers\LogActivity;
use DataTables;
use Carbon\Carbon;
use Database;
use Validator;
class TicketstatusController extends Controller{

    public function __construct() 
    {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }
    public function index()
    {
        return view('pages.setup.support.ticketstatuses.index');
    }

    public function indextable(Request $request){
        $data=\App\Models\Ticketstatus::orderBy('sortorder');
        return Datatables::of($data)
            /* ->editColumn('startdate', function($data) {
               return  Carbon::parse($data->expirationdate)->isoFormat(Cfg::get('DateFormat'));
           })
           ->editColumn('expirationdate', function($data) {
               return  Carbon::parse($data->expirationdate)->isoFormat(Cfg::get('DateFormat'));
           }) */
           ->toJson();
    }

    public function store(Request $request){
        //dd($request->all());
        $error=true;
        $alert='';

        $title          =$request->title;
        $color          =$request->color;
        $sortorder      =(int)$request->sortorder;
        $showactive     =(int)(bool)$request->showactive;
        $showawaiting   =(int)(bool)$request->showawaiting;
        $autoclose      =(int)(bool)$request->autoclose;

        $status=new \App\Models\Ticketstatus();
        $status->title =  $title ;
        $status->color =  $color ;
        $status->sortorder =  $sortorder ;
        $status->showactive =  $showactive ;
        $status->showawaiting =  $showawaiting ;
        $status->autoclose =  $autoclose ;
        $status->save();
        $id=$status->id;
        LogActivity::Save("Support Ticket Status Created: '" . $title . "' - Ticket Status ID: " . $id);

        $return=['error' =>false, 'alert' => $alert];

        return json_encode($return);
    }

    public function update(Request $request){
        //dd($request->all());
        $error=true;
        $alert='';
        $id             =(int)$request->id;
        $title          =$request->title;
        $color          =$request->color;
        $sortorder      =(int)$request->sortorder;
        $showactive     =(int)(bool)$request->showactive;
        $showawaiting   =(int)(bool)$request->showawaiting;
        $autoclose      =(int)(bool)$request->autoclose;
        
        $ticketStatus   =\App\Models\Ticketstatus::find($id);
        if ($ticketStatus->title != $title) {
            LogActivity::Save("Support Ticket Status Modified: " . "Title Changed: '" . $ticketStatus->title . "' to '" . $title . "' - Ticket Status ID: " . $id);
        }
        if ($ticketStatus->color != $color || $ticketStatus->sortorder != $sortorder || $ticketStatus->showactive != $showactive || $ticketStatus->showawaiting != $showawaiting || $ticketStatus->autoclose != $autoclose) {
            LogActivity::Save("Support Ticket Status Modified: '" . $title . "' - Ticket Status ID: " . $id);
        }
        $update   =\App\Models\Ticketstatus::find($id);
        $update->title =  $title ;
        $update->color =  $color ;
        $update->sortorder =  $sortorder ;
        $update->showactive =  $showactive ;
        $update->showawaiting =  $showawaiting ;
        $update->autoclose =  $autoclose ;
        $update->save();
        $return=['error' =>false, 'alert' => $alert];
        return json_encode($return);
    }

    public function destroy(Request $request){
       // dd($request->all());
        $id             =(int)$request->id;
        $ticketStatus   =\App\Models\Ticketstatus::find($id);
        $title          = $ticketStatus->title;
        
        \App\Models\Ticket::where('status', $title)->update(['status' => 'Closed']);
        \App\Models\Ticketstatus::find($id)->delete();
        LogActivity::Save("Support Ticket Status Deleted: '" . $title . "' - Ticket Status ID: " . $id);

        return back()->with('success', 'Status Delete Successfully');
    }

}