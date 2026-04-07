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
use App\Models\Knowledgebase;
use App\Models\Knowledgebasecat;
use App\Models\Knowledgebaselink;
use App\Models\Knowledgebasetag;  // Sesuaikan dengan nama file model yang ada

class KnowledgebaseController extends Controller
{
    public function __construct()
    {
        $this->prefix=Database::prefix();
        $this->adminURL =request()->segment(1);
    }

    public function Knowledgebase($id=0)
    {
        $catID=(int)$id;
        if($catID){
            $catAr=DB::table("{$this->prefix}knowledgebasecats as kategori")
                            ->join("{$this->prefix}knowledgebaselinks as link","kategori.id","=","link.categoryid")
                            ->join("{$this->prefix}knowledgebase as artikel","link.articleid","=","artikel.id")
                            ->where('kategori.id',$catID)
                            ->select('artikel.id','artikel.title','artikel.views')->get();
            $category=\App\Models\Knowledgebasecat::find($catID);
           // $category=\App\Models\Knowledgebasecat::where('catid',0)->get();
        }else{
            $catAr=array();
            $category=\App\Models\Knowledgebasecat::where('catid',0)->get();
        }
       //dd($catAr);
        $url=$this->adminURL.'/support/';
        $params=[
                    'category'  =>  $category,
                    'catAr'     => $catAr,
                    'url'       => $url
                ];
       // dd($params);
        return view ('pages.support.knowledgebase.index',$params);
    }

    public function KnowledgebaseEdit($id){
        $id=(int) $id;
        $category=\App\Models\Knowledgebasecat::find($id);
        $lang=\App\Helpers\HelperMultiLingual::get();
        $catid=array();
        foreach($lang as $K => $v){
            $perent=\App\Models\Knowledgebasecat::where('catid',$id)->where('language',$v)->first();
            $catid[$K]=[
                            'id' => $perent->id ?? '',
                            'name' => $perent->name ?? '',
                            'description' => $perent->description ?? '',
                            'hidden'    => $perent->hidden ?? '',
                        ];
        };
        $Pdata=\App\Models\Knowledgebasecat::where(function($query) use ($id) {
                                                    $query->Where('id', '<>',$id)
                                                        ->Where('catid', '<>', $id);
                                                })->get();
        //dd($Pdata);
        $url=$this->adminURL.'/support/';
        $params=[
                    'category'  => $category,
                    'catid'     => $catid,
                    'perent'    => $Pdata,
                    'url'       => $url
                ];

        return view ('pages.support.knowledgebase.edit',$params);
    }

    public function KnowledgebaseUpdate(Request $request){
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

        $id=(int)$request->id;
        $cat=\App\Models\Knowledgebasecat::find($id);
        $cat->parentid = (int)$request->parentid;
        $cat->name = $request->name;
        $cat->description = $request->description;
        $cat->hidden = ($request->hidden == 'on' )?0:'';
        $cat->save();
        LogActivity::Save("Added New Knowledgebase Category {$request->name} - {$request->description}");
        /* multilang */
        $lang=\App\Helpers\HelperMultiLingual::get();
        foreach($lang as $k =>$v){

            if(!empty($request->nmultilang_name[$v]) && !empty($request->multilang_desc[$v]) ){
                $cek=\App\Models\Knowledgebasecat::where('catid',$id)->where('language',$v)->select('id')->first();
                if(is_null($cek)){

                    $lang= new \App\Models\Knowledgebasecat();
                    $lang->name = $request->nmultilang_name[$v];
                    $lang->description = $request->multilang_desc[$v];
                    $lang->hidden = 0;
                    $lang->catid =$id;
                    $lang->language = $v;
                    $lang->save();
                     LogActivity::Save("Added New Knowledgebase Category {$v} {$request->multilang_desc[$v]} - {$request->nmultilang_name[$v]}");

                }else{
                    $lang=\App\Models\Knowledgebasecat::find($cek->id);
                    $lang->name = $request->nmultilang_name[$v];
                    $lang->description = $request->multilang_desc[$v];
                    //$lang->hidden = 0;
                    //$lang->hidden =$id;
                    $lang->language = $v;
                    $lang->save();

                    LogActivity::Save("Update Knowledgebase Category {$v}  {$request->multilang_desc[$v]} - {$request->nmultilang_name[$v]}");
                }
            }
        }


        return back()->with('success', 'Update Knowledgebase Category successfully. ');

    }

    public function KnowledgebaseDestroy(Request $request){
        //dd($request->all());
        $id=(int) $request->id;
        $delete=\App\Models\Knowledgebasecat::find($id);
        $delete->delete();

        \App\Models\Knowledgebasecat::where('catid',$id)->delete();;


        return back()->with('success', 'Delete Knowledgebase Category successfully. ');
    }


    public function articleDestroy(Request $request){
        //dd($request->all());
        $id=(int) $request->id;
        \App\Models\Knowledgebase::find($id)->delete();
        \App\Models\Knowledgebase::where('parentid',$id)->delete();
        return back()->with('success', 'Delete Knowledgebase successfully. ');
    }

    public function KnowledgebaseArticle($id)
{
    try {
        $artikel = Knowledgebase::find($id);
        
        if(!$artikel) {
            return redirect()->back()->with('error', 'Article not found');
        }

        $category = Knowledgebasecat::all();
        
        $categoriInLink = Knowledgebaselink::where('articleid', $id)
                            ->pluck('categoryid')
                            ->toArray();

        $tagSeleted = Knowledgebasetag::where('articleid', $id)->get();

        // Ubah format multi language
        $multi = [];
        $languages = \App\Helpers\HelperMultiLingual::get();
        foreach($languages as $lang) {
            $translation = Knowledgebase::where('parentid', $id)
                            ->where('language', $lang)
                            ->first();
            
            $multi[$lang] = [
                'title' => $translation->title ?? '',
                'article' => $translation->article ?? ''
            ];
        }
        
        $url = $this->adminURL.'/support/';

        return view('pages.support.knowledgebase.ArticleEdit', compact(
            'artikel',
            'category', 
            'categoriInLink',
            'tagSeleted',
            'multi',
            'url'
        ));

    } catch (\Exception $e) {
        \Log::error('Error in KnowledgebaseArticle: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error loading article');
    }
}

    public function articleUpdate(Request $request){
        $rules=[
            'articlename'          => 'required',
        ];
        $messages = [
            'articlename.required'           => 'articlename required.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
        //dd($request->all());
        /*update this artikel */
        $id=(int) $request->id;
        $artikel=\App\Models\Knowledgebase::find($id);
        $artikel->title = $request->articlename;
        $artikel->article = $request->description;
        $artikel->views = $request->views;
        $artikel->useful = $request->useful;
        $artikel->votes = $request->votes;
        $artikel->order = $request->order;
        $artikel->save();

        LogActivity::Save("Update New Knowledgebase Articel - {$request->articlename} ");

        /*tag*/
        if(isset($request->tag)){
            \App\Models\Knowledgebasetag::where('articleid',$id)->delete();
            foreach($request->tag as $k=>$v){
                $tagStorage=new \App\Models\Knowledgebasetag();
                $tagStorage->articleid = $id;
                $tagStorage->tag = $v;
                $tagStorage->save();
            }
        }

        /* link */
        if(isset($request->categories)){
            \App\Models\Knowledgebaselink::where('articleid',$id)->delete();
            //dd($allcategory);
            foreach($request->categories as $c){
                $cat= new \App\Models\Knowledgebaselink();
                $cat->categoryid =$c;
                $cat->articleid =$id;
                $cat->save();
            }
        }


        /* insert or update */
        foreach($request->lang as $k=>$v){

            if(!empty($v['articlename']) && !empty($v['description'])){

                $chackThis=\App\Models\Knowledgebase::where('parentid',$id)->where('language',$k)->select('id')->first();
                if(!is_null($chackThis)){
                    $chackThis->title       = $v['articlename'];
                    $chackThis->article = $v['description'];
                    $chackThis->save();
                    /* log */
                    LogActivity::Save("Update New Knowledgebase Articel - {$v['articlename']} language {$k}");
                }else{
                    $insertArrt=new \App\Models\Knowledgebase();
                    $insertArrt->title       = $v['articlename'];
                    $insertArrt->article = $v['description'];
                    $insertArrt->language = $k;
                    $insertArrt->parentid = $id;
                    $insertArrt->save();
                    LogActivity::Save("Added New Knowledgebase Articel - {$v['articlename']} language {$k} ");
                    /* log */
                }

            }

        }

        return back()->with('success', 'Update Knowledgebase successfully');
    }


    public function articleStore(Request $request){
        //dd($request->all());
        $rules=[
            'articlename'          => 'required',
        ];
        $messages = [
            'articlename.required'           => 'articlename required.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $data=new \App\Models\Knowledgebase();
        $data->title = $request->articlename;
        $data->article = '';
        $data->save();
        $articelID=$data->id;

        $link= new \App\Models\Knowledgebaselink();
        $link->categoryid =0;
        $link->articleid =$articelID;
        $link->save();

        $admin=env('ADMIN_ROUTE_PREFIX', 'admin');
        return redirect($admin.'/support/knowledgebase/article/'.$articelID)->with('success', 'successful article saving');
    }

    public function categoryKBStore(Request $request){
       // dd($request->all());
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
        $cat=new \App\Models\Knowledgebasecat();
        $cat->name = $request->name;
        $cat->description = $request->description;
        $cat->hidden =($request->hidden == 'on')?1:0;
        if($request->parentid){
            $cat->parentid = $request->parentid;
        }

        $cat->save();
        LogActivity::Save("Added New Knowledgebase Category - {$request->name}");
        //redir("catid=" . $newcatid);
        return back()->with('success', 'Save Knowledgebase Category successfully. ');
    }

}