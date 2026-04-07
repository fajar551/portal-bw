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

class DownloadsController extends Controller
{
    public function __construct()
    {
        $this->prefix=Database::prefix();
        $this->adminURL =request()->segment(1);
    }

    public function index()
    {
        return view ('pages.support.downloads.index');
    }

    public function CategoryStore(Request $request){
        //dd($request->all());
        $rules=[
            'name'          => 'required',
            'description'   => 'required',
        ];
        $messages = [
            'name.required'           => 'Name required.',
            'description.required'    => 'description required.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $cat=new \App\Models\Downloadcat();
        $cat->name = $request->name;
        $cat->description = $request->description;
        if($request->hidden == 'on' ){
            $cat->hidden = 1;
        }
        if($request->parentid){
            $cat->parentid = $request->parentid;
        }

        $cat->save();
        LogActivity::Save("Added New Download Category - {$request->name}");

        return back()->with('success', 'Save Download Category successfully. ');

    }

    public function Downloads_list()
    {
        return view ('pages.support.downloads.list');
    }
    public function Downloads_detail()
    {
        return view ('pages.support.downloads.detail');
    }
}