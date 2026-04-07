<?php

namespace App\Http\Controllers\Admin\Setup\Escalationrules;

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
class EscalationrulesController extends Controller{

    public function index()
    {
        return view('pages.setup.support.escalationrules.index');
    }
    public function indexdata(Request $request){
        $data=\App\Models\Ticketescalation::select('id','name');
        return Datatables::of($data)
             /* ->editColumn('startdate', function($data) {
                    return  Carbon::parse($data->expirationdate)->isoFormat(Cfg::get('DateFormat'));
                })
                ->editColumn('expirationdate', function($data) {
                    return  Carbon::parse($data->expirationdate)->isoFormat(Cfg::get('DateFormat'));
                }) */
                ->toJson();
    }
   /*  public function Support_escalationrules()
    {
        return view('pages.setup.support.escalationrules.index');
    } */
    public function add()
    {
        $dep=\App\Models\Ticketdepartment::select('id','name')->orderBy('name')->get();
        $status=\App\Models\Ticketstatus::select('title')->orderBy('sortorder')->get();
        $admin=\App\Models\Admin::select('id','username','firstname','lastname')->orderBy('username')->get();
        //dd($admin);
        $param=[
                    'dept'   => $dep,
                    'status' => $status,
                    'admin'  => $admin
                ];

        return view('pages.setup.support.escalationrules.add',$param);
    } 

    public function store(Request $request){

        $rules=[
            'name'               => 'required'
        ];
        $messages = [
            'name.required'         => 'Support Ticket Escalations name  required.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
        //dd($request->all());
        $name           = $request->name;
        $departments    = $request->departments ?? array() ;
        $statuses       = $request->statuses ?? array() ;
        $priorities     = $request->priorities ?? array();
        $timeelapsed    = $request->timeelapsed??0;
        $newstatus      = $request->newstatus??'';
        $newpriority    = $request->newpriority??'';
        $flagto         = $request->flagto??'';
        $notify         = $request->notify ?? array();
        $addreply       = $request->addreply??'';
        $newdepartment       = $request->newdepartment;
        if (is_array($departments)) {
            $departments = implode(",", $departments);
        }
        $statuses = json_encode($statuses);
        if (is_array($priorities)) {
            $priorities = implode(",", $priorities);
        }
        if (is_array($notify)) {
            $notify = implode(",", $notify);
        }

        $ticketescalations=new \App\Models\Ticketescalation();
        $ticketescalations->name = $name;
        $ticketescalations->departments = $departments;
        $ticketescalations->statuses = $statuses;
        $ticketescalations->priorities = $priorities;
        $ticketescalations->timeelapsed = $timeelapsed;
        $ticketescalations->newdepartment = $newdepartment;
        $ticketescalations->newstatus = $newstatus;
        $ticketescalations->newpriority = $newpriority;
        $ticketescalations->flagto = $flagto;
        $ticketescalations->notify = $notify;
        $ticketescalations->addreply = $addreply;
        $ticketescalations->editor = 'markdown';
        $ticketescalations->save();
        $id=$ticketescalations->id;
        LogActivity::Save("Ticket Escalation Created: '" . $name . "' - Escalation ID: " . $id);

        return back()->with('success', 'Success  Created Ticket Escalation');
    }

    public function edit($id){
        $id=(int)$id;
        $data=\App\Models\Ticketescalation::find($id);
        //dd($data);
        $dep=\App\Models\Ticketdepartment::select('id','name')->orderBy('name')->get();
        $status=\App\Models\Ticketstatus::select('title')->orderBy('sortorder')->get();
        $admin=\App\Models\Admin::select('id','username','firstname','lastname')->orderBy('username')->get();
        $param=[
                    'data'   => $data,
                    'dept'   => $dep,
                    'status' => $status,
                    'admin'  => $admin
                ];

        return view('pages.setup.support.escalationrules.edit',$param);
    }

    public function update(Request $request){
        //dd($request->all());
        $rules=[
            'name'               => 'required'
        ];
        $messages = [
            'name.required'         => 'Support Ticket Escalations name  required.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $id             = (int) $request->id;
        $name           = $request->name;
        $departments    = $request->departments ?? array() ;
        $statuses       = $request->statuses ?? array() ;
        $priorities     = $request->priorities ?? array();
        $timeelapsed    = $request->timeelapsed;
        $newstatus      = $request->newstatus;
        $newpriority    = $request->newpriority;
        $flagto         = $request->flagto;
        $notify         = $request->notify ?? array();
        $addreply       = $request->addreply;
        $newdepartment       = $request->newdepartment;
        if (is_array($departments)) {
            $departments = implode(",", $departments);
        }
        $statuses = json_encode($statuses);
        if (is_array($priorities)) {
            $priorities = implode(",", $priorities);
        }
        if (is_array($notify)) {
            $notify = implode(",", $notify);
        }

        $Escalation=\App\Models\Ticketescalation::find($id);

        if ($Escalation->name != $name) {
            LogActivity::Save("Ticket Escalation Modified: Name Changed: " . "'" . $Escalation->name . "' to '" . $name . "' - Escalation ID: " . $id);
        }
        if ($Escalation->departments != $departments || $Escalation->statuses != $statuses || $Escalation->priorities != $priorities || $Escalation->timeelapsed != $timeelapsed || $Escalation->newdepartment != $newdepartment || $Escalation->newstatus != $newstatus || $Escalation->newpriority != $newpriority || $Escalation->flagto != $flagto || $Escalation->notify != $notify || $Escalation->addreply != $addreply) {
            LogActivity::Save("Ticket Escalation Modified: '" . $name . "' - Escalation ID: " . $id);
        }


        $ticketescalations=\App\Models\Ticketescalation::find($id);
        $ticketescalations->name = $name;
        $ticketescalations->departments = $departments;
        $ticketescalations->statuses = $statuses;
        $ticketescalations->priorities = $priorities;
        $ticketescalations->timeelapsed = $timeelapsed;
        $ticketescalations->newdepartment = $newdepartment;
        $ticketescalations->newstatus = $newstatus;
        $ticketescalations->newpriority = $newpriority;
        $ticketescalations->flagto = $flagto;
        $ticketescalations->notify = $notify;
        $ticketescalations->addreply = $addreply;
        $ticketescalations->save();

        return back()->with('success', 'Success  Update Ticket Escalation');
    }

    public function destroy(Request $request){
        //dd($request->all());
        $id=(int)$request->id;
        $ticketEscalation=\App\Models\Ticketescalation::find($id);
        \App\Models\Ticketescalation::find($id)->delete();
        LogActivity::Save("Ticket Escalation Deleted: '" . $ticketEscalation->name . "' - Escalation ID: " . $id);
        return back()->with('success', 'Success  escalation rule');
    }

}