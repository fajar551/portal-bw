<?php

namespace App\Http\Controllers\Admin\Setup\Support;

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
class SupportDepartmensController extends Controller
{
    public function __construct() 
    {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index()
    {
        $tiket=\App\Models\Ticketdepartment::select('id','name','description','email','order','hidden')->orderBy('order')->get();
        //dd($tiket);
        return view('pages.setup.support.supportticketdepartments.index',['data' => $tiket]);
    }

    public function getData(Request $request){
        //dd($request->all());
        $data=\App\Models\Ticketdepartment::select('id','name','description','email','order','hidden')->orderBy('order');
        return Datatables::of($data)
    /*             ->editColumn('startdate', function($data) {
                    return  Carbon::parse($data->expirationdate)->isoFormat(Cfg::get('DateFormat'));
                })
                ->editColumn('expirationdate', function($data) {
                    return  Carbon::parse($data->expirationdate)->isoFormat(Cfg::get('DateFormat'));
                }) */
                ->toJson();


    }


    public function add(){
        // $admin = \App\Models\Admin::select('id', 'username', 'firstname', 'lastname')->orderBy('username')->get();
        $admin = \App\Models\Admin::select('id', 'username', 'firstname', 'lastname')->orderBy('username')->get();
        $assignedAdmins = []; // Inisialisasi sebagai array kosong
        return view('pages.setup.support.supportticketdepartments.add',['admin' => $admin, 'assignedAdmins' => $assignedAdmins ]);
    }

    public function store(Request $request){
        //dd($request->all());
        $rules=[
            'email'               => 'required|email|unique:'.$this->prefix.'ticketdepartments',
            'name'                => 'required'
        ];
        $messages = [
            'email.required'        => 'email is required..',
            'email.email'           => 'email Invalid email.',
            'email.unique'           => 'e-mail already used.',
            'name.required'         => 'name id required.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $admin=$request->admin ?? array();

        $lastOrder=\App\Models\Ticketdepartment::orderBy('order','DESC')->first('order');
        $order=$lastOrder->order;
        $order++;

        $save= new \App\Models\Ticketdepartment();
        $save->name               = $request->name;
        $save->description        = \App\Helpers\Sanitize::decode($request->description);
        $save->email              = trim($request->email);
        $save->clientsonly        = $request->clientsonly??'';
        $save->piperepliesonly    = $request->piperepliesonly??'';
        $save->noautoresponder    = $request->noautoresponder??'';
        $save->hidden             = $request->hidden??'';
        $save->order              = $order;
        $save->host               = !empty($request->host)?trim($request->host):'';
        $save->port               = !empty($request->port)?trim($request->port):'';
        $save->login              = !empty($request->port)?trim($request->login):'';
        $save->password           =(new \App\Helpers\Pwd)->encrypt(\App\Helpers\Sanitize::decode($request->password));
        $save->feedback_request   = $request->feedbackrequest??0;
        $save->save();
        $id=$save->id;

        // Simpan data admin yang ditugaskan
    //    $adminIds = $request->admins ?? [];
    //    foreach ($adminIds as $adminId) {
    //        $admin = \App\Models\Admin::find($adminId);
    //        $supportdepts = explode(',', $admin->supportdepts);
    //        if (!in_array($save->id, $supportdepts)) {
    //            $supportdepts[] = $save->id;
    //        }
    //        $admin->supportdepts = implode(',', $supportdepts);
    //        $admin->save();
    //    }

        // Simpan data admin yang ditugaskan
       $adminId = $request->admins[0] ?? null;
       if ($adminId) {
           $admin = \App\Models\Admin::find($adminId);
           $admin->supportdepts = $save->id;
           $admin->save();
       }

        if(\App\Helpers\Cfg::get('EnableTranslations')){
            \App\Models\DynamicTranslation::saveNewTranslations($id,["ticket_department.{id}.name", "ticket_department.{id}.description"]);
        }

        $adminData=\App\Models\Admin::select('id','supportdepts')->where('disabled',0)->get();
        
        foreach($adminData as $r){
            $deptadminid=$r->id;
            $supportdepts=$r->supportdepts;
            $supportdepts = explode(",", $supportdepts);
            if (in_array($deptadminid, $admin)) {
                if (!in_array($id, $supportdepts)) {
                    $supportdepts[] = $id;
                }
            }else{
                if (in_array($id, $supportdepts)) {
                    $supportdepts = array_diff($supportdepts, array($id));
                }
            }

            $update=\App\Models\Admin::find($deptadminid);
            $update->supportdepts = implode(",",$supportdepts);
            $update->save();

        }

        LogActivity::Save("Support Department Created:: ".$request->name,$id);
        return back()->with('success', 'Successfully add Support department ticket.');
    }

    public function Support_ticketdepartments_edit($id){
        //dd($id);
        // $data = \App\Models\Ticketdepartment::find($id);
        //dd($data);
        // $admin = \App\Models\Admin::select('id', 'username', 'firstname', 'lastname')->orderBy('username')->get();

        $data = \App\Models\Ticketdepartment::find($id);
        $admin = \App\Models\Admin::select('id', 'username', 'firstname', 'lastname')->orderBy('username')->get();

         // Ambil admin yang sudah ditugaskan ke departemen ini
    //    $assignedAdmins = explode(',', $data->supportdepts);
    
        // Ambil admin yang sudah ditugaskan
        // $assignedAdmins = DB::table('admin_department')
        // ->where('department_id', $id)
        // ->pluck('admin_id')
        // ->toArray();

        $assignedAdmins = \App\Models\Admin::whereRaw("FIND_IN_SET(?, supportdepts)", [$id])->pluck('id')->toArray();

        // return view('pages.setup.support.supportticketdepartments.edit',['data' => $data, 'admin' => $admin ]);
        return view('pages.setup.support.supportticketdepartments.edit', [
            'data' => $data,
            'admin' => $admin,
            'assignedAdmins' => $assignedAdmins
        ]);
    }

    public function update(Request $request){
        //dd($request->all());
        $id=(int)$request->id;
        $rules=[
            'email'               => 'required|email|unique:'.$this->prefix.'ticketdepartments,email,'.$id,
            'name'                => 'required'
        ];
        $messages = [
            'email.required'        => 'email is required..',
            'email.email'           => 'email Invalid email.',
            'email.unique'           => 'e-mail already used.',
            'name.required'         => 'name id required.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        
        $supportDepartment=\App\Models\Ticketdepartment::find($id);
        $changes = false;
        $saveData=[
                        'name'              => $request->name,
                        'description'       => \App\Helpers\Sanitize::decode($request->description),
                        'email'             => trim($request->email),
                        'clientsonly'       => $request->clientsonly??'',
                        'piperepliesonly'   => $request->piperepliesonly??'',
                        'noautoresponder'   => $request->noautoresponder??'',
                        'hidden'            => $request->hidden??'',
                        'host'              => !empty($request->host)?trim($request->host):'',
                        'port'              => !empty($request->port)?trim($request->port):'',
                        'login'             => !empty($request->login)?trim($request->login):'',
                        'feedback_request'  => $request->feedbackrequest??0
                    ];
        foreach ($saveData as $save => $data) {
            if ($save == "name") {
                if ($supportDepartment->{$save} != $data) {
                    LogActivity::Save("Support Department Modified: " . "Name Changed: '" . $supportDepartment->{$save} . "' to '" . $data . "' - Support Department ID: " . $id);
                }
                continue;
            }

            if (!$changes && $supportDepartment->{$save} != $data) {
                $changes = true;
                break;
            }

        }
        $pass=\App\Models\Ticketdepartment::find($id);
        $newPassword = trim($request->password);
        $valueToStore = \App\Helpers\AdminFunctions::interpretMaskedPasswordChangeForStorage($newPassword,$pass->password);
        if ($valueToStore !== false) {
            $saveData["password"] = $valueToStore;
            if ($newPassword != $pass->password) {
                $changes = true;
            }
        }

        if ($changes) {
            LogActivity::Save("Support Department Modified: '" . $pass->name . "' - Configuration Modified - Support Department ID: " . $id);
        }

        //dd($saveData); 
        $update=\App\Models\Ticketdepartment::find($id);
        foreach($saveData as $k=>$v){
            $update->{$k} =$v;
        }
        $update->save();

        return back()->with('success', 'Successfully update Support department ticket.');

    }



    public function destroy(Request $request){
        //dd($request->all());
        $id=(int)$request->id;
        $data=\App\Models\Ticketdepartment::find($id);
        $order=$data->order;
        $departmentName=$data->name;
        \App\Models\Ticketdepartment::where('order',$order)->update(['order' => '-1' ]);
       try{
            \App\Models\Ticketdepartment::findOrFail($id)->delete();
        }catch (Exception $e) {
        
        }
        LogActivity::Save("Support Department Deleted: '" . $departmentName . "' - Support Department ID: " . $id);
        $newdeptid=DB::table("tblticketdepartments")->min('id');
        \App\Models\Ticket::where('did',$id)->update(['did' => $newdeptid]);
        //dd($newdeptid);
        return back()->with('success', 'Delete Support Ticket Departments');
    }

    public function order(Request $request){
        //dd($request->all());
        $error=true;
        $order=(int)$request->order;
        $data =\App\Models\Ticketdepartment::select('id','order')->where('order',$order)->first();
        /* dd($data); */
        if($request->type == 'down'){
            $premid = $data->id;
            $order1 = $order + 1;
            $otherDepartment =\App\Models\Ticketdepartment::where('order',$order1)->first();
            LogActivity::Save("Support Department Modified: '" . $data->name . "' - Sort Order Lowered - Support Department ID: " . $premid);
            LogActivity::Save("Support Department Modified: '" . $otherDepartment->name . "'" . " - Sort Order Increased - Support Department ID: " . $otherDepartment->id);
            \App\Models\Ticketdepartment::where('order',$order1)->update(['order' => $order]);
            $updateOrder=\App\Models\Ticketdepartment::find($premid);
            $updateOrder->order = $order1;
            $updateOrder->save();
            $error=false;
        }else{
            $premid = $data->id;
            $order1 = $order - 1;
            $otherDepartment =\App\Models\Ticketdepartment::where('order',$order1)->first();
            LogActivity::Save("Support Department Modified: '" . $data->name . "' - Sort Order Lowered - Support Department ID: " . $premid);
            LogActivity::Save("Support Department Modified: '" . $otherDepartment->name . "'" . " - Sort Order Increased - Support Department ID: " . $otherDepartment->id);
            \App\Models\Ticketdepartment::where('order',$order1)->update(['order' => $order]);
            $updateOrder=\App\Models\Ticketdepartment::find($premid);
            $updateOrder->order = $order1;
            $updateOrder->save();
            $error=false;
        }

        if($error){
            $validator=['Error '];
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }else{
            return back()->with('success', 'Success Update order');
        }

    }

   



}