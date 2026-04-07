<?php

namespace App\Http\Controllers\API\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseAPI;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Client;
use App\Models\Admin;


class ProjectManagementController extends Controller
{
    
    public function AddProjectMessage(Request $request){
        $validator = Validator::make($request->all(), [
            'projectid'         => 'required|int',
            'message'           => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        //dd($request->all());
       // try{
           $projectid= (int) $request->projectid;
           $message= $request->message;
           $adminid= (int) $request->adminid;

            $getProject=DB::table('mod_project')->where('id',$projectid)->first();
            if(is_null($getProject)){
                $respone =["result" => "error", "message" => "Project ID not Set"];
                return ResponseApi::Error($respone);
            }

            if(!$adminid){
                $adminid=Auth::id();
            }

            if($adminid){
                $result_adminid=\App\Models\Admin::find($adminid);
                if(is_null($result_adminid)){
                    $respone =["result" => "error", "message" => "Admin ID Not Found"];
                    return ResponseApi::Error($respone);
                }
            }
            $now = Carbon::now();
            
            DB::table('mod_projectmessages')
                                ->insert([
                                            'projectid' => $projectid,
                                            'adminid'   => $adminid,
                                            'message'   => $message,
                                            'date'      => $now
                                        ]);

            $respone = ["result" => "success", "message" => "Message has been added"];
            return ResponseAPI::Success($respone);
        //}catch (\Exception $e) {
        //    return ResponseApi::Error(['message' => $e->getMessage()]);
       //}    

       
    }



    public function AddProjectTask(Request $request){
        $validator = Validator::make($request->all(), [
            'projectid'         => 'required|int',
            'duedate'           => 'required|date_format:Y-m-d',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try{
            $projectid= (int) $request->projectid;
            $duedate= $request->duedate;
            $adminid= (int) $request->adminid;
            $task = $request->task;
            $notes = $request->notes;
            $completed = (int) (bool) $request->completed;
            $billed = (int) (bool) $request->billed;
            $now = Carbon::now();
            $getProject=DB::table('mod_project')->where('id',$projectid)->first();
            if(is_null($getProject)){
                $respone =["result" => "error", "message" => "Project ID not Set"];
                return ResponseApi::Error($respone);
            }
            if($adminid){
                $result_adminid=\App\Models\Admin::find($adminid);
                if(is_null($result_adminid)){
                    $respone =["result" => "error", "message" => "Admin ID Not Found"];
                    return ResponseApi::Error($respone);
                }
            }

            if(empty($task)){
                $respone =["result" => "error", "message" => "A task description must be specified"];
                return ResponseApi::Error($respone);
            }
            $order= DB::table('mod_projecttasks')->select('order')->orderBy('order', 'desc')->first();
            $ordervalue=is_null($order)?0:(int)$order->order;
            $ordervalue++;
            DB::table('mod_projecttasks')
                    ->insert([
                                'projectid' => $projectid,
                                'adminid'   => $adminid,
                                'task'      => $task,
                                'notes'     => $notes,
                                'completed' => $completed,
                                'created'   => $now,
                                'duedate'   => $duedate,
                                'billed'    => $billed,
                                'order'     => $ordervalue
                            ]);

            $respone = ["result" => "success", "message" => "Task has been added"];
            return ResponseAPI::Success($respone);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }  
    }


    public function CreateProject(Request $request){
        $validator = Validator::make($request->all(), [
            'title'         => 'required|string',
            'adminid'           => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try{
            $title       = $request->title;
            $adminid     = (int) $request->adminid;
            $userid      = (int) $request->userid;
            $status      = $request->status;
            $created     = $request->created ?? Carbon::now()->format('Y-m-d') ;
            $duedate     = $request->duedate ??Carbon::now()->format('Y-m-d');
            $completed   = (int) (bool) $request->completed;
            $ticketids   = $request->ticketids ?? '';
            $invoiceids   = $request->invoiceids ?? '';

            if($userid){
                $getClientID=Client::find($userid)->id ?? 0;
                if(!$getClientID){
                    return ResponseAPI::Error(["result" => "error", "message" => "Client ID Not Found"]);
                }
            }

            if($adminid){
                $getClientID=Admin::find($adminid)->id ?? 0;
                if(!$getClientID){
                    return ResponseAPI::Error(["result" => "error", "message" => "Admin ID Not Found"]);
                }
            }
            $version =\App\Models\AddonModule::where("module", "=", "project_management")->where("setting", "=", "version")->first();
            if (!$version) {
               return ResponseAPI::Error(["result" => "error", "message" => "Project Management is not active."]);
            } else {
                    if(!trim($title)){
                        return ResponseAPI::Error(["result" => "error", "message" => "Project Title is Required."]);
                    }else{
                        $statusModule  =\App\Models\AddonModule::select('value')->where('module','project_management')->where('setting','statusvalues')->first();
                        $validStatus = explode(",", $statusModule);
                        $projectStatus = $validStatus[0];
                        if (isset($status) && in_array($status, $validStatus)) {
                            $projectStatus = $status;
                        }
                        $now=Carbon::now();
                        $params=[
                                    'userid'     => $userid,
                                    'title'      => $title,
                                    'ticketids'  => $ticketids,
                                    'invoiceids' => $invoiceids,
                                    'notes'      => '',
                                    'adminid'    => $adminid,
                                    'status'     => $projectStatus,
                                    'created'    => $created,
                                    'duedate'    => $duedate,
                                    'completed'  => $completed,
                                    'lastmodified' => $now
                                ];

                        $projectid= DB::table('mod_project')->insertGetId($params);
                        return ResponseAPI::Success(["result" => "success", "message" => "Project has been created", "projectid" => $projectid]);

                    }
            }

        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        } 
    }

    public function DeleteProjectTask(Request $request){
        $validator = Validator::make($request->all(), [
            'projectid'        => 'required|int',
            'taskid'           => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try{
            $projectid= (int) $request->projectid;
            $taskid= (int) $request->taskid;

            $getProject=DB::table('mod_project')->where('id',$projectid)->first();
            if(!$getProject){
                return ResponseAPI::Error(["result" => "error", "message" => "Project ID Not Found"]);
            }

            $getTasks=DB::table('mod_projecttasks')->where('id',$taskid)->first();
            if(!$getTasks){
                return ResponseAPI::Error(["result" => "error", "message" => "Task ID Not Found"]);
            }

            DB::table('mod_projecttasks')->where('id', $taskid)->where('projectid', $projectid)->delete();
            return ResponseAPI::Success(["result" => "success", "message" => "Task has been deleted"]);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        } 
    }

    public function StartTaskTimer(Request $request){
        $validator = Validator::make($request->all(), [
           /*  'timerid'        => 'required|int', */
            'projectid'      => 'required|int',
            'taskid'         => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try{
            $taskid    = (int) $request->taskid;
            $projectid  = (int) $request->projectid;
            $adminid    = (int) $request->adminid;
            $start_time = $request->start_time ?? time() ;
            $end_time   = $request->end_time;
            $adminid =(!$adminid)?Auth::id():$adminid;

            if($projectid){
                $getProject=DB::table('mod_project')->where('id',$projectid)->first();
                if(!$getProject){
                    return ResponseAPI::Error(["result" => "error", "message" => "Project ID Not Found"]);
                }

            }

            $getClientID=Admin::find($adminid)->id ?? 0;
            if(!$getClientID){
                return ResponseAPI::Error(["result" => "error", "message" => "Admin  ID Not Found"]);
            }

            $getTask=DB::table('mod_projecttasks')->where('id',$taskid)->count();
            if(!$getTask){
                return ResponseAPI::Error(["result" => "error", "message" => "Task  ID Not Found"]);
            }

            $params=[
                        "projectid" => $projectid,
                        "adminid"   => $adminid,
                        "taskid"    => $taskid,
                        "start"     => $start_time,
                        "end"       => $end_time
                    ];
            DB::table('mod_projecttimes')->insert($params);
            return ResponseAPI::Success(["result" => "success", "message" => "Start Timer Has Been Set"]);

        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }



    public function EndTaskTimer(Request $request){
        $validator = Validator::make($request->all(), [
            'timerid'        => 'required|int',
            'projectid'      => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try{
            $timerid    = (int) $request->timerid;
            $projectid  = (int) $request->projectid;
            $adminid    = (int) $request->adminid;
            $end_time   = $request->end_time ?? time();
            $getProject =DB::table('mod_project')->where('id',$projectid)->count();
            if(!$getProject){
                return ResponseAPI::Error(["result" => "error", "message" => "Project ID Not Found"]);
            }

            $adminid =(!$adminid)?Auth::id():$adminid;
            $getClientID=Admin::find($adminid)->id ?? 0;
            if(!$getClientID){
                return ResponseAPI::Error(["result" => "error", "message" => "Admin  ID Not Found"]);
            }
            $params=[
                        'projectid' => $projectid,
                        'adminid'   => $adminid,
                        'end'       => $end_time
                    ];
            DB::table('mod_projecttimes')->where('id',$timerid)->update($params);
            return ResponseAPI::Success(["result" => "success", "message" => "Timer Has Ended"]);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function GetProject(Request $request){
        $validator = Validator::make($request->all(), [
            'projectid'      => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try{
            $projectid = (int) $request->projectid;
            $getProject =DB::table('mod_project')->where('id',$projectid)->first();
            if(!$getProject){
                return ResponseAPI::Error(["result" => "error", "message" => "Project ID Not Found"]);
            }
            $apiresults["projectinfo"] = $getProject;
            $getTask=DB::table('mod_projecttasks')->where('projectid',$projectid)->get();

            foreach($getTask as $r){
                $data_tasks["timelogs"] = array();
                $getTime=DB::table('mod_projecttimes')->where('taskid',$r->id)->get();
                foreach($getTime as $k){
                    $DATA["starttime"] = date("Y-m-d H:i:s", $k->start);
                    $DATA["endtime"] = date("Y-m-d H:i:s", $k->end);
                    $data_tasks["timelogs"]["timelog"][] = $DATA;
                }
                $apiresults["tasks"]["task"][] = $data_tasks;
            }
            $apiresults["messages"] = array();

            $getProjectmessages=DB::table('mod_projectmessages')->where('projectid',$projectid)->get();
            foreach($getProjectmessages as $v){
                $apiresults["messages"]["message"][] = $v;
            }
            return ResponseAPI::Success($apiresults);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetProjects(Request $request){
        $limitstart=(int) $request->limitstart;
        $limitnum=$request->limitnum ?? 25;
        $userid=(int) $request->userid;
        $title=$request->title;
        $ticketids=$request->ticketids;
        $invoiceids=$request->invoiceids;
        $notes=$request->notes;
        $adminid=(int)$request->adminid;
        $status=$request->status;
        $created=$request->created;
        $duedate=$request->duedate;
        $completed=$request->completed;
        $lastmodified=$request->lastmodified;

        $query=DB::table("mod_project");
        if ($userid) {
            $query = $query->where("userid", "=", $userid);
        }
        if ($title) {
            $query = $query->where("title", "like", $title);
        }
        if ($ticketids) {
            $query = $query->where("ticketids", "like", $ticketids);
        }
        if ($invoiceids) {
            $query = $query->where("invoiceids", "like", $invoiceids);
        }
        if ($notes) {
            $query = $query->where("notes", "like", $notes);
        }
        if ($adminid) {
            $query = $query->where("adminid", "=", $adminid);
        }
        if ($status) {
            $query = $query->where("status", "like", $status);
        }
        if ($created) {
            $query = $query->where("created", "like", $created);
        }
        if ($duedate) {
            $query = $query->where("duedate", "like", $duedate);
        }
        if ($completed) {
            $query = $query->where("completed", "like", $completed);
        }
        if ($lastmodified) {
            $query = $query->where("lastmodified", "like", $lastmodified);
        }
        $totalresults = $query->count();
        
        $result = $query->orderBy("id", "ASC")->skip($limitstart)->limit($limitnum)->get();
        $return=[
                    "result"        => "success",
                    "totalresults"  => $totalresults,
                    "startnumber"   => $limitstart,
                    "numreturned"   => count($result),
                    "projects"      => $result
                ];
        return ResponseAPI::Success($return);
    }

    public function UpdateProject(Request $request){
        $validator = Validator::make($request->all(), [
            'projectid'      => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try{
            $projectid=$request->projectid;
            $userid= (int) $request->userid;
            $adminid= (int) $request->adminid;
            $status=$request->status;
            $created=$request->created;
            $duedate=$request->duedate;
            $completed=(int)(bool)$request->completed;
            $title=$request->title;
            $ticketids=$request->ticketids;
            $invoiceids=$request->invoiceids;
            $notes	=$request->notes;
            //dd($request->all());

            $projectCek=DB::table('mod_project')->where('id',$projectid)->count();
            if(!$projectCek){
                return ResponseAPI::Error(["result" => "error", "message" => "Project ID Not Found"]);
            }
            $dataUserId = 0;
            if($userid){
               $getClientID=Client::find($userid);
                if(is_null($getClientID)){
                    return ResponseAPI::Error(["result" => "error", "message" => "Client ID Not Found"]);
                }
                $dataUserId=$getClientID->id;
            }
            $dataAdminId = 0;
            if($adminid){
                $getAdmin=Admin::find($adminid);
                if(is_null($getAdmin)){
                    return ResponseAPI::Error(["result" => "error", "message" => "Admin ID Not Found"]);
                }
                $dataAdminId =$getAdmin->id;
            }

            $status_main = "";
            if($status){
               $status_get=\App\Models\AddonModule::where('module','project_management')->where('setting','statusvalues')->first('value');
               $status_get = explode(",", $status_get->value);
               $status_main = in_array($status, $status_get) ? $status : $status_get[0];
            }

            $adminId = $dataAdminId;
            $userId = $dataUserId;
            $status = $status_main;
            if ($title) {
                $updateQuery["title"] = trim($title);
            }
            if ($adminId) {
                $updateQuery["adminid"] = $adminId;
            }
            if ($userId) {
                $updateQuery["userid"] = $userId;
            }
            if ($ticketids) {
                $updateQuery["ticketids"] = $ticketids;
            }
            if ($invoiceids) {
                $updateQuery["invoiceids"] = $invoiceids;
            }
            if ($notes) {
                $updateQuery["notes"] = $notes;
            }
            if ($status) {
                $updateQuery["status"] = $status;
            }
            if ($duedate) {
                $updateQuery["duedate"] = $duedate;
            }
            //if ($completed) {
                $updateQuery["completed"] = $completed;
           // }
            $now=Carbon::now();
            $updateQuery["lastmodified"]=$now;
            
            if(!DB::table('mod_project')->where('id',$projectid)->update($updateQuery)){
                ResponseAPI::Error(["result" => "error", "message" => "Error update"]);
            }

            return ResponseAPI::Success(["result" => "success", "message" => "Project Has Been Updated"]);

        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function UpdateProjectTask(Request $request){
        $validator = Validator::make($request->all(), [
            'taskid'      => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try{
            $taskid= (int) $request->taskid;
            $projectid= (int) $request->projectid;
            $duedate=$request->duedate;
            $adminid=(int) $request->adminid;
            $task=$request->task;
            $notes=$request->notes;
            $completed=(int) (bool) $request->completed;

            $projectCek=DB::table('mod_project')->where('id',$projectid)->count();
            if(!$projectCek){
                return ResponseAPI::Error(["result" => "error", "message" => "Project ID Not Found"]);
            }

            $getProjectTasks=DB::table('mod_projecttasks')->where('id',$taskid)->count();
            if(!$getProjectTasks){
                return ResponseAPI::Error(["result" => "error", "message" => "Task ID Not Found"]);
            }
            if($adminid){
                $getAdmin=Admin::find($adminid);
                if(is_null($getAdmin)){
                    return ResponseAPI::Error(["result" => "error", "message" => "Admin ID Not Found"]);
                }
            }
            $updateqry = array();
            if ($projectid) {
                $updateqry["projectid"] = $projectid;
            }
            if ($task) {
                $updateqry["task"] = $task;
            }
            if ($notes) {
                $updateqry["notes"] = $notes;
            }
            if ($duedate) {
                $updateqry["duedate"] = $duedate;
            }
            if ($adminid) {
                $updateqry["adminid"] = $adminid;
            }
            $updateqry["completed"] = $completed;
    
            if(!DB::table('mod_projecttasks')->where('id',$taskid)->update($updateqry)){
                ResponseAPI::Error(["result" => "error", "message" => "Error update"]);
            }
            return ResponseAPI::Success(["result" => "success", "message" => "Task Has Been Updated"]);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }

}
