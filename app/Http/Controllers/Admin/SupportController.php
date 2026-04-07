<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Helpers\Cfg;
use Illuminate\Support\Facades\DB;
use App\Helpers\Database;
use Illuminate\Support\Facades\Validator;
use App\Models\Ticket;
use App\Models\Ticketreply;
use App\Helpers\API;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Ticket as TicketHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\LogActivity;

class SupportController extends Controller
{
    protected $bodyContent;
    private $prefix;
    private $adminURL;
    
    public function __construct()
    {
        $this->prefix=Database::prefix();
        $this->adminURL =request()->segment(1);
    }

    public function SupportOverview()
    {
        return view ('pages.support.supportoverview.index');
    }

    public function SupportOverviewPost(Request $request)
    {
        $error = true;
        $data = [];
        $period = $request->display;
        $dateFormat = 'Y-m-d';
        $newTiket = 0;
        $clientReplies = 0;
        $staffReplies = 0;
        $ticketsWithoutReply = 0;
        $AverageFirstResponse = 0;

        switch ($period) {
            case 'today':
                $date = Carbon::now()->format($dateFormat);
                $newTiket = Ticket::where('date', 'LIKE', "%$date%")->count();
                $clientReplies = Ticketreply::where('date', 'LIKE', "%$date%")->where('admin', '=', '')->count();
                $staffReplies = Ticketreply::where('date', 'LIKE', "%$date%")->where('admin', '!=', '')->count();
                break;

            case 'ThisWeek':
                $last_monday = Carbon::now()->modify('last monday')->format($dateFormat);
                $next_sunday = Carbon::now()->modify('next sunday')->format($dateFormat);
                $newTiket = Ticket::whereBetween(DB::raw('DATE(date)'), [$last_monday, $next_sunday])->count();
                $clientReplies = Ticketreply::whereBetween(DB::raw('DATE(date)'), [$last_monday, $next_sunday])->where('admin', '=', '')->count();
                $staffReplies = Ticketreply::whereBetween(DB::raw('DATE(date)'), [$last_monday, $next_sunday])->where('admin', '!=', '')->count();
                break;

            case 'LastMonth':
                $lastMonth = Carbon::now()->modify('last month')->format('Y-m-');
                $newTiket = Ticket::where('date', 'LIKE', "%$lastMonth%")->count();
                $clientReplies = Ticketreply::where('date', 'LIKE', "%$lastMonth%")->where('admin', '=', '')->count();
                $staffReplies = Ticketreply::where('date', 'LIKE', "%$lastMonth%")->where('admin', '!=', '')->count();
                break;

            default: // yesterday
                $yesterday = Carbon::now()->modify('yesterday')->format($dateFormat);
                $newTiket = Ticket::where('date', 'LIKE', "%$yesterday%")->count();
                $clientReplies = Ticketreply::where('date', 'LIKE', "%$yesterday%")->where('admin', '=', '')->count();
                $staffReplies = Ticketreply::where('date', 'LIKE', "%$yesterday%")->where('admin', '!=', '')->count();
                break;
        }

        $params = [
            'newTiket' => $newTiket,
            'clientReplies' => $clientReplies,
            'staffReplies' => $staffReplies,
            'ticketsWithoutReply' => $ticketsWithoutReply,
            'AverageFirstResponse' => $AverageFirstResponse
        ];

        return json_encode($params);
    }


    public function SupportOverviewPie(Request $request)
    {
        $data = [];
        $period = $request->display;
        $dateFormat = 'Y-m-d';
        $now = Carbon::now();
    
        $dataTiket = \App\Models\Ticket::select(
            'id',
            'date',
            DB::raw("(SELECT date FROM tblticketreplies WHERE tblticketreplies.tid=tbltickets.id AND admin!='' LIMIT 1) as datefirstreply")
        );
    
        switch ($period) {
            case 'today':
                $date = $now->format($dateFormat);
                $newtickets = Ticket::where('date', 'LIKE', "%$date%")->count();
                $clientreplies = Ticketreply::where('date', 'LIKE', "%$date%")->where('admin', '=', '')->count();
                $staffreplies = Ticketreply::where('date', 'LIKE', "%$date%")->where('admin', '!=', '')->count();
                $dataTiket->where('date', 'LIKE', "%$date%");
                break;
    
            case 'ThisWeek':
                $last_monday = $now->startOfWeek()->format($dateFormat);
                $next_sunday = $now->endOfWeek()->format($dateFormat);
                $newtickets = Ticket::whereBetween(DB::raw('DATE(date)'), [$last_monday, $next_sunday])->count();
                $clientreplies = Ticketreply::whereBetween(DB::raw('DATE(date)'), [$last_monday, $next_sunday])->where('admin', '=', '')->count();
                $staffreplies = Ticketreply::whereBetween(DB::raw('DATE(date)'), [$last_monday, $next_sunday])->where('admin', '!=', '')->count();
                $dataTiket->whereBetween(DB::raw('DATE(date)'), [$last_monday, $next_sunday]);
                break;
    
            case 'ThisMonth':
                $month = $now->format('Y-m-');
                $newtickets = Ticket::where('date', 'LIKE', "%$month%")->count();
                $clientreplies = Ticketreply::where('date', 'LIKE', "%$month%")->where('admin', '=', '')->count();
                $staffreplies = Ticketreply::where('date', 'LIKE', "%$month%")->where('admin', '!=', '')->count();
                $dataTiket->where('date', 'LIKE', "%$month%");
                break;
    
            case 'LastMonth':
                $lastMonth = $now->subMonth()->format('Y-m-');
                $newtickets = Ticket::where('date', 'LIKE', "%$lastMonth%")->count();
                $clientreplies = Ticketreply::where('date', 'LIKE', "%$lastMonth%")->where('admin', '=', '')->count();
                $staffreplies = Ticketreply::where('date', 'LIKE', "%$lastMonth%")->where('admin', '!=', '')->count();
                $dataTiket->where('date', 'LIKE', "%$lastMonth%");
                break;
    
            default: // yesterday
                $yesterday = $now->subDay()->format($dateFormat);
                $newtickets = Ticket::where('date', 'LIKE', "%$yesterday%")->count();
                $clientreplies = Ticketreply::where('date', 'LIKE', "%$yesterday%")->where('admin', '=', '')->count();
                $staffreplies = Ticketreply::where('date', 'LIKE', "%$yesterday%")->where('admin', '!=', '')->count();
                $dataTiket->where('date', 'LIKE', "%$yesterday%");
                break;
        }
    
        $hours = array_fill(0, 24, 0);
        $replytimes = [1 => 0, 2 => 0, 4 => 0, 8 => 0, 16 => 0, 24 => 0];
        $avefirstresponse = 0;
        $avefirstresponsecount = 0;
        $opennoreply = 0;
    
        $dataTiket = $dataTiket->get();
    
        foreach ($dataTiket as $result) {
            $dateopened = $result->date;
            $datefirstreply = $result->datefirstreply;
    
            $datehour = substr($dateopened, 11, 2);
            $hours[(int)$datehour]++;
    
            if (!$datefirstreply) {
                $opennoreply++;
            } else {
                $timetofirstreply = (strtotime($datefirstreply) - strtotime($dateopened)) / 3600;
                $avefirstresponse += $timetofirstreply;
                $avefirstresponsecount++;
    
                if ($timetofirstreply <= 1) {
                    $replytimes[1]++;
                } elseif ($timetofirstreply <= 4) {
                    $replytimes[2]++;
                } elseif ($timetofirstreply <= 8) {
                    $replytimes[4]++;
                } elseif ($timetofirstreply <= 16) {
                    $replytimes[8]++;
                } elseif ($timetofirstreply <= 24) {
                    $replytimes[16]++;
                } else {
                    $replytimes[24]++;
                }
            }
        }
    
        $avefirstresponse = $avefirstresponsecount > 0 ? round($avefirstresponse / $avefirstresponsecount, 2) : "-";
    
        $respone = [];
        foreach ($replytimes as $hours => $count) {
            if ($count > 0) {
                $respone[] = [
                    'label' => "{$hours}-" . ($hours * 2) . " Hours",
                    'data' => $count
                ];
            }
        }
    
        $hourschartdata = [];
        foreach ($hours as $k => $v) {
            $hourschartdata[] = [(int)$k, $v];
        }
    
        $data = [
            'pie' => $respone,
            'line' => $hourschartdata
        ];
    
        return json_encode($data);
    }
    
    public function Announcements()
    {
        return view ('pages.support.announcements.index');
    }

    public function AnnouncementsGet(Request $request)
    {
        $data = Announcement::select('id', 'date', 'title', 'published');
    
        return Datatables::of($data)
            ->editColumn('date', function (Announcement $data) {
                return Carbon::parse($data->date)->isoFormat(Cfg::get('DateFormat') . ' H:mm');
            })
            ->editColumn('published', function (Announcement $data) {
                return $data->published == 1 ? 'Yes' : 'No';
            })
            ->editColumn('title', function (Announcement $data) {
                return '<a href="./announcements/edit/' . $data->id . '">' . $data->title . '</a>';
            })
            ->addColumn('action', function (Announcement $data) {
                return '<a href="./announcements/edit/' . $data->id . '" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                        <a href="./announcements/destroy/' . $data->id . '" data-id="' . $data->id . '" data-title="' . $data->title . '" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></a>';
            })
            ->rawColumns(['action', 'title'])
            ->toJson();
    }
        
    public function Announcementsdestroy($id){
        $id = (int) $id;
        Announcement::find($id)->delete();
        return back()->with('success', 'User deleted successfully');
    }

    public function Announcements_add()
    {
        return view ('pages.support.announcements.add');
    }
    public function Announcements_edit(Request $request){
        $data = Announcement::find($request->id);
        return view ('pages.support.announcements.edit',['data' => $data]);
    }

    public function Downloads()
    {
        return view ('pages.support.downloads.index');
    }

    public function CategoryStore(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
        ];
    
        $messages = [
            'name.required' => 'Name required.',
            'description.required' => 'Description required.',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        $cat = new \App\Models\Downloadcat();
        $cat->name = $request->name;
        $cat->description = $request->description;
        $cat->hidden = $request->hidden == 'on' ? 1 : 0;
        $cat->parentid = $request->parentid ?? null;
    
        $cat->save();
    
        LogActivity::Save("Added New Download Category - {$request->name}");
    
        return back()->with('success', 'Save Download Category successfully.');
    }

    public function Downloads_list()
    {
        return view ('pages.support.downloads.list');
    }
    
    public function Downloads_detail()
    {
        return view ('pages.support.downloads.detail');
    }

    public function Knowledgebase($id = 0)
    {
        $catID = (int)$id;
        $catAr = [];
        $category = null;
    
        if ($catID) {
            $catAr = DB::table("{$this->prefix}knowledgebasecats as kategori")
                ->join("{$this->prefix}knowledgebaselinks as link", "kategori.id", "=", "link.categoryid")
                ->join("{$this->prefix}knowledgebase as artikel", "link.articleid", "=", "artikel.id")
                ->where('kategori.id', $catID)
                ->select('artikel.id', 'artikel.title', 'artikel.views')
                ->get();
    
            $category = \App\Models\Knowledgebasecat::find($catID);
        } else {
            $category = \App\Models\Knowledgebasecat::where('catid', 0)->get();
        }
    
        $url = $this->adminURL . '/support/';
        $params = [
            'category' => $category,
            'catAr' => $catAr,
            'url' => $url
        ];
    
        return view('pages.support.knowledgebase.index', $params);
    }
    
    public function KnowledgebaseEdit($id)
    {
        $id = (int) $id;
        $category = \App\Models\Knowledgebasecat::find($id);
        $lang = \App\Helpers\HelperMultiLingual::get();
        $catid = [];
    
        foreach ($lang as $K => $v) {
            $perent = \App\Models\Knowledgebasecat::where('catid', $id)->where('language', $v)->first();
            $catid[$K] = [
                'id' => $perent->id ?? '',
                'name' => $perent->name ?? '',
                'description' => $perent->description ?? '',
                'hidden' => $perent->hidden ?? '',
            ];
        }
    
        $Pdata = \App\Models\Knowledgebasecat::where(function ($query) use ($id) {
            $query->where('id', '<>', $id)
                  ->where('catid', '<>', $id);
        })->get();
    
        $url = $this->adminURL . '/support/';
        $params = [
            'category' => $category,
            'catid' => $catid,
            'perent' => $Pdata,
            'url' => $url
        ];
    
        return view('pages.support.knowledgebase.edit', $params);
    }
    
    public function KnowledgebaseUpdate(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
        ];
    
        $messages = [
            'name.required' => 'Name required.',
            'description.required' => 'Description required.',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        $id = (int) $request->id;
        $cat = \App\Models\Knowledgebasecat::find($id);
        $cat->parentid = (int) $request->parentid;
        $cat->name = $request->name;
        $cat->description = $request->description;
        $cat->hidden = $request->hidden == 'on' ? 0 : '';
        $cat->save();
    
        LogActivity::Save("Added New Knowledgebase Category {$request->name} - {$request->description}");
    
        // Multilingual support
        $lang = \App\Helpers\HelperMultiLingual::get();
        foreach ($lang as $k => $v) {
            if (!empty($request->nmultilang_name[$v]) && !empty($request->multilang_desc[$v])) {
                $cek = \App\Models\Knowledgebasecat::where('catid', $id)->where('language', $v)->select('id')->first();
    
                if (is_null($cek)) {
                    $langEntry = new \App\Models\Knowledgebasecat();
                    $langEntry->name = $request->nmultilang_name[$v];
                    $langEntry->description = $request->multilang_desc[$v];
                    $langEntry->hidden = 0;
                    $langEntry->catid = $id;
                    $langEntry->language = $v;
                    $langEntry->save();
    
                    LogActivity::Save("Added New Knowledgebase Category {$v} {$request->multilang_desc[$v]} - {$request->nmultilang_name[$v]}");
                } else {
                    $langEntry = \App\Models\Knowledgebasecat::find($cek->id);
                    $langEntry->name = $request->nmultilang_name[$v];
                    $langEntry->description = $request->multilang_desc[$v];
                    $langEntry->language = $v;
                    $langEntry->save();
    
                    LogActivity::Save("Update Knowledgebase Category {$v} {$request->multilang_desc[$v]} - {$request->nmultilang_name[$v]}");
                }
            }
        }
    
        return back()->with('success', 'Update Knowledgebase Category successfully.');
    }
    
    public function KnowledgebaseDestroy(Request $request)
    {
        $id = (int) $request->id;
    
        $delete = \App\Models\Knowledgebasecat::find($id);
        if ($delete) {
            $delete->delete();
        }
    
        \App\Models\Knowledgebasecat::where('catid', $id)->delete();
    
        return back()->with('success', 'Delete Knowledgebase Category successfully.');
    }

    public function articleDestroy(Request $request)
    {
        $id = (int) $request->id;
        \App\Models\Knowledgebase::find($id)->delete();
        \App\Models\Knowledgebase::where('parentid', $id)->delete();
        return back()->with('success', 'Delete Knowledgebase successfully. ');
    }

    public function KnowledgebaseArticle($id)
    {
        $artikelID = (int) $id;
    
        // Category
        $category = \App\Models\Knowledgebasecat::all();
        $Artikel = DB::table("{$this->prefix}knowledgebasecats as kategori")
            ->join("{$this->prefix}knowledgebaselinks as link", "kategori.id", "=", "link.categoryid")
            ->join("{$this->prefix}knowledgebase as artikel", "link.articleid", "=", "artikel.id")
            ->where('artikel.id', $artikelID)
            ->select('artikel.*', 'kategori.id as kategoriID')
            ->first();
    
    
        $lang = \App\Helpers\HelperMultiLingual::get();
        $dataArtikel = [];
    
        foreach ($lang as $k => $v) {
            /* $getArtikel = DB::table("{$this->prefix}knowledgebasecats as kategori")
                ->join("{$this->prefix}knowledgebaselinks as link", "kategori.id", "=", "link.categoryid")
                ->join("{$this->prefix}knowledgebase as artikel", "link.articleid", "=", "artikel.id")
                ->where('artikel.id', $artikelID)
                ->where('artikel.language', $v)
                ->select('artikel.*', 'kategori.id as kategoriID')
                ->first(); */
            
            $getArtikel = \App\Models\Knowledgebase::where('parentid', $id)->where('language', $v)->first();
    
            $dataArtikel[$k] = [
                'id' => $getArtikel->id ?? '',
                'title' => $getArtikel->title ?? '',
                'article' => $getArtikel->article ?? '',
                /* 'views' => $getArtikel->views ?? '',
                'useful' => $getArtikel->useful ?? '',
                'votes' => $getArtikel->votes ?? '',
                'private' => $getArtikel->private ?? '',
                'order' => $getArtikel->order ?? '',
                'parentid' => $getArtikel->parentid ?? '',
                'language' => $getArtikel->language ?? '', */
                /* 'kategoriID' => $getArtikel->kategoriID ?? '', */
            ];
        }
    
        $url = $this->adminURL . '/support/';
        $catLink = \App\Models\Knowledgebaselink::where('articleid', $artikelID)->select('categoryid')->get();
        $categoriInLink = [];
    
        foreach ($catLink as $key) {
            $categoriInLink[] = $key->categoryid;
        }
    
        $tagSeleted = \App\Models\Knowledgebasetag::where('articleid', $artikelID)->select('tag')->get();
    
        $param = [
            'category' => $category,
            'artikel' => $Artikel,
            'multi' => $dataArtikel,
            'url' => $url,
            'categoriInLink' => $categoriInLink,
            'tagSeleted' => $tagSeleted
        ];
    
        // dd($param);
        return view('pages.support.knowledgebase.ArticleEdit', $param);
    }

    public function articleUpdate(Request $request)
    {
        $rules = [
            'articlename' => 'required',
        ];
    
        $messages = [
            'articlename.required' => 'Articlename required.',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        // Update this article
        $id = (int) $request->id;
        $artikel = \App\Models\Knowledgebase::find($id);
        $artikel->title = $request->articlename;
        $artikel->article = $request->description;
        $artikel->views = $request->views;
        $artikel->useful = $request->useful;
        $artikel->votes = $request->votes;
        $artikel->order = $request->order;
        $artikel->save();
    
        LogActivity::Save("Update New Knowledgebase Article - {$request->articlename}");
    
        // Update tags
        if (isset($request->tag)) {
            \App\Models\Knowledgebasetag::where('articleid', $id)->delete();
            foreach ($request->tag as $v) {
                $tagStorage = new \App\Models\Knowledgebasetag();
                $tagStorage->articleid = $id;
                $tagStorage->tag = $v;
                $tagStorage->save();
            }
        }
    
        // Update links
        if (isset($request->categories)) {
            \App\Models\Knowledgebaselink::where('articleid', $id)->delete();
            foreach ($request->categories as $c) {
                $cat = new \App\Models\Knowledgebaselink();
                $cat->categoryid = $c;
                $cat->articleid = $id;
                $cat->save();
            }
        }
    
        // Insert or update multilingual entries
        foreach ($request->lang as $k => $v) {
            if (!empty($v['articlename']) && !empty($v['description'])) {
                $chackThis = \App\Models\Knowledgebase::where('parentid', $id)->where('language', $k)->select('id')->first();
                if (!is_null($chackThis)) {
                    $chackThis->title = $v['articlename'];
                    $chackThis->article = $v['description'];
                    $chackThis->save();
                    LogActivity::Save("Update Knowledgebase Article - {$v['articlename']} language {$k}");
                } else {
                    $insertArrt = new \App\Models\Knowledgebase();
                    $insertArrt->title = $v['articlename'];
                    $insertArrt->article = $v['description'];
                    $insertArrt->language = $k;
                    $insertArrt->parentid = $id;
                    $insertArrt->save();
                    LogActivity::Save("Added New Knowledgebase Article - {$v['articlename']} language {$k}");
                }
            }
        }
    
        return back()->with('success', 'Update Knowledgebase successfully');
    }

    public function articleStore(Request $request)
    {
        $rules = [
            'articlename' => 'required',
        ];
    
        $messages = [
            'articlename.required' => 'Articlename required.',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        $data = new \App\Models\Knowledgebase();
        $data->title = $request->articlename;
        $data->article = '';
        $data->save();
        $articleID = $data->id;
    
        $link = new \App\Models\Knowledgebaselink();
        $link->categoryid = 0;
        $link->articleid = $articleID;
        $link->save();
    
        $admin = env('ADMIN_ROUTE_PREFIX', 'admin');
        return redirect($admin . '/support/knowledgebase/article/' . $articleID)->with('success', 'Successful article saving');
    }
    
    public function categoryKBStore(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
        ];
    
        $messages = [
            'name.required' => 'Name required.',
            'description.required' => 'Description required.',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        $cat = new \App\Models\Knowledgebasecat();
        $cat->name = $request->name;
        $cat->description = $request->description;
        $cat->hidden = $request->hidden == 'on' ? 1 : 0;
        $cat->parentid = $request->parentid ?? null;
    
        $cat->save();
    
        LogActivity::Save("Added New Knowledgebase Category - {$request->name}");
    
        return back()->with('success', 'Save Knowledgebase Category successfully.');
    }

    public function SupportTickets()
    {
        $getDepartment = \App\Models\Ticketdepartment::select('id', 'name')->get();
        $getStatus = \App\Models\Ticketstatus::select('id', 'title')->get();
        $getTag = \App\Models\Tickettag::select('id', 'tag')->get();
    
        $params = [
            'dep' => $getDepartment,
            'status' => $getStatus,
            'tag' => $getTag
        ];
    
        return view('pages.support.supporttickets.index', $params);
    }
    
    public function SupportTicketsGet(Request $request)
    {
        $userid = (int) $request->client;
        $dep = $request->department_name ?? [];
        $status = $request->status ?? [];
        $urgency = $request->priority;
        $subject = $request->subject ?? '';
        $email = $request->email ?? '';
        $ticket = $request->ticket ?? '';
    
        $data = DB::table("{$this->prefix}tickets as t")
            ->join("{$this->prefix}clients as c", "t.userid", "=", "c.id")
            ->join("{$this->prefix}ticketdepartments as d", "t.did", "=", "d.id")
            ->select('t.id', 't.title as subject', 't.message', 'd.name as departement', 'c.firstname', 'c.lastname', 't.status', 't.lastreply');
    
        if ($request->client) {
            $data->where('t.userid', $userid);
        }
    
        if ($dep) {
            $data->whereIn('t.did', $dep);
        }
    
        if ($status) {
            $data->whereIn('t.status', $status);
        }
    
        if (!empty($urgency)) {
            $data->whereIn('t.urgency', $urgency);
        }
    
        if ($subject) {
            $data->where('t.title', $subject);
        }
    
        if ($email) {
            $data->where('t.email', $email);
        }
    
        if ($ticket) {
            $data->where('t.id', $ticket);
        }
    
        return Datatables::of($data)
            ->addColumn('submitter', function ($data) {
                return '<a href="./clients/clientsummary/' . $data->id . '">' . $data->firstname . ' ' . $data->lastname . '</a>';
            })
            ->addColumn('checkbox', function ($data) {
                return '<div class="custom-control custom-checkbox"><input type="checkbox" name="selectedtickets[]" value="' . $data->id . '" class="custom-control-input" id="ordercheck1"><label class="custom-control-label" for="ordercheck1">&nbsp;</label></div>';
            })
            ->editColumn('lastreply', function ($data) {
                $now = Carbon::now();
                $datework = Carbon::parse($data->lastreply);
                $diff = $datework->diff($now);
    
                return "{$diff->d}d {$diff->h}h {$diff->i}m ";
            })
            ->editColumn('subject', function ($data) {
                return "<a title=\"{$data->message}\" href=\"./support/opennewtickets/{$data->id}\">#{$data->id} - {$data->subject}</a>";
            })
            ->rawColumns(['submitter', 'checkbox', 'subject'])
            ->toJson();
    }

    public function OpenNewTickets()
    {
        $getDepartment = \App\Models\Ticketdepartment::select('id', 'name')->get();
        $params = [
            'dep' => $getDepartment
        ];
    
        return view('pages.support.opennewtickets.index', $params);
    }
    
    public function OpenNewTicketsStore(Request $request)
    {
        $rules = [
            'clientid' => 'required|int',
            'deptid' => 'required|int',
            'subject' => 'required',
            'message' => 'required',
        ];
    
        $messages = [
            'client.required' => 'Client required.',
            'client.int' => 'Client ID must be an integer.',
            'department.required' => 'Select department.',
            'message.required' => 'Message required.',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        // Client data
        $clientData = \App\Models\Client::find($request->clientid);
        $from = [
            'name' => $clientData->firstname . ' ' . $clientData->lastname,
            'email' => $clientData->email
        ];
    
        $service = $request->serviceid ? 'd' . $request->serviceid : '';
        $serviceid = $service . $request->related_service;
    
        if ($request->serviceid) {
            $serviceid = 'S' . $request->serviceid;
        }
    
        if ($request->domainid) {
            $serviceid = 'D' . $request->domainid;
        }
    
        $attachmentString = [];
        if ($request->hasFile('attachments')) {
            // $directory = '/home/hostingnvme/public_html/Files/';
            $directory = 'Files/';
            foreach ($request->file('attachments') as $attachment) {
                $uuid = (string) Str::uuid();
                $filename = $uuid . "." . $attachment->getClientOriginalExtension();
                $content = file_get_contents($attachment);

                // Ensure the directory exists
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Save the file
                file_put_contents($directory . $filename, $content);

                // Store the full path in the attachmentString
                $attachmentString[] = 'Files/' . $filename;
            }
        }
        $attachmentString = implode('|', $attachmentString);
    
        $ticketdata = TicketHelper::OpenNewTicket(
            $request->clientid,
            $contactid = '',
            $request->deptid,
            $request->subject,
            $request->message,
            $request->priority,
            $attachmentString,
            $from,
            $serviceid,
            $cc = "",
            $noemail = '',
            $treatAsAdmin = '',
            $useMarkdown = 'markdown'
        );
    
        $msg = implode(' | ', $ticketdata);
        return back()->with('success', 'Ticket created successfully. ' . $msg);
    }

    public function PredefinedReplies(Request $request)
    {
        $category = \App\Models\Ticketpredefinedcat::all();
        $baseURL = $this->adminURL . '/support/predefinedreplies/';
        $param = [
            'category' => $category,
            'baseURL' => $baseURL
        ];
    
        return view('pages.support.predefinedreplies.index', $param);
    }
    
    public function PredefinedRepliesCategoryEdit($id)
    {
        $baseURL = $this->adminURL . '/support/predefinedreplies/';
        $category = \App\Models\Ticketpredefinedcat::find($id);
        $parent = \App\Models\Ticketpredefinedcat::where('id', '!=', $id)->get();
    
        return view('pages.support.predefinedreplies.category.edit', [
            'category' => $category,
            'baseURL' => $baseURL,
            'parent' => $parent
        ]);
    }
    
    public function PredefinedRepliesCategoryStore(Request $request)
    {
        $rules = [
            'category_name' => 'required',
        ];
    
        $messages = [
            'category_name.required' => 'Category name is required.'
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        LogActivity::Save("Added New Predefined Reply Category - {$request->category_name}");
    
        $category = new \App\Models\Ticketpredefinedcat();
        $category->name = $request->category_name;
        $category->save();
    
        return back()->with('success', 'Saved new Predefined Reply Category.');
    }
    
    public function PredefinedRepliesCategoryUpdate(Request $request)
    {
        $rules = [
            'category_name' => 'required',
        ];
    
        $messages = [
            'category_name.required' => 'Category name is required.'
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        $category = \App\Models\Ticketpredefinedcat::find((int)$request->id);
        $category->parentid = (int) $request->parentid;
        $category->name = $request->category_name;
        $category->save();
    
        LogActivity::Save("Modified Predefined Reply Category (ID: {$request->id})");
    
        return back()->with('success', 'Modified Predefined Category.');
    }
    
    public function PredefinedRepliesCategoryDestroy(Request $request)
    {
        \App\Models\Ticketpredefinedcat::find((int)$request->id)->delete();
        LogActivity::Save("Deleted Predefined Reply Category (ID: {$request->id})");
    
        return back()->with('success', 'Deleted Predefined Category.');
    }

    private function setBodyContent(array $content)
    {
        $this->bodyContent = $content;
    }

    // private function output()
    // {
    //     return $this->bodyContent['body'];
    // }
    
    public function PredefinedRepliesNEW(Request $request)
    {
        $auth = Auth::guard('admin')->user();
        $route = "admin.pages.support.predefinedreplies.index";
        $action = $request->input("action");
        $addreply = $request->input("addreply");
        $sub = $request->input("sub");
        $catid = $request->input("catid");
        $name = $request->input("name");
        $catname = $request->input("catname");
        $reply = $request->input("reply");
        $id = $request->input("id");
        $parentid = $request->input("parentid");
        $addcategory = $request->input("addcategory");
        session()->put('adminid', $auth->id);
    
        if ($action == "parseMarkdown") {
            $markup = new \App\Helpers\ViewMarkup();
            $content = $request->input("content");
            $this->setBodyContent(["body" => "<div class=\"markdown-content\">" . $markup->transform($content, "markdown") . "</div>"]);
            // $this->output();
            \App\Helpers\Terminus::getInstance()->doExit();
        }
    
        if ($addreply == "true") {
            \App\Helpers\AdminFunctions::checkPermission("Create Predefined Replies");
            $lastid = \App\Models\Ticketpredefinedreply::insertGetId(["catid" => $catid, "name" => $name]);
            \App\Helpers\LogActivity::Save("Added New Predefined Reply - " . $name);
            return redirect()->route($route, ['action' => 'edit', 'id' => $lastid]);
        }
    
        if ($sub == "save") {
            \App\Helpers\AdminFunctions::checkPermission("Manage Predefined Replies");
            \App\Models\Ticketpredefinedreply::where("id", $id)->update(["catid" => $catid, "name" => $name, "reply" => $reply]);
            \App\Helpers\LogActivity::Save("Modified Predefined Reply (ID: " . $id . ")");
            return redirect()->route($route, ['catid' => $catid, 'save' => 'true']);
        }
    
        if ($sub == "savecat") {
            \App\Helpers\AdminFunctions::checkPermission("Manage Predefined Replies");
            \App\Models\Ticketpredefinedcat::where("id", $id)->update(["parentid" => $parentid, "name" => $name]);
            \App\Helpers\LogActivity::Save("Modified Predefined Reply Category (ID: " . $id . ")");
            return redirect()->route($route, ['catid' => $parentid, 'savecat' => 'true']);
        }
    
        if ($addcategory == "true") {
            \App\Helpers\AdminFunctions::checkPermission("Create Predefined Replies");
            \App\Models\Ticketpredefinedcat::insert(["parentid" => $catid, "name" => $catname]);
            \App\Helpers\LogActivity::Save("Added New Predefined Reply Category - " . $catname);
            return redirect()->route($route, ['catid' => $catid, 'addedcat' => 'true']);
        }
    
        if ($sub == "delete") {
            \App\Helpers\AdminFunctions::checkPermission("Delete Predefined Replies");
            \App\Models\Ticketpredefinedreply::where("id", $id)->delete();
            \App\Helpers\LogActivity::Save("Deleted Predefined Reply (ID: " . $id . ")");
            return redirect()->route($route, ['catid' => $catid, 'delete' => 'true']);
        }
    
        if ($sub == "deletecategory") {
            \App\Helpers\AdminFunctions::checkPermission("Delete Predefined Replies");
            \App\Models\Ticketpredefinedreply::where("catid", $id)->delete();
            \App\Models\Ticketpredefinedcat::where("id", $id)->delete();
            $this->deletePreDefCat($id);
            \App\Helpers\LogActivity::Save("Deleted Predefined Reply Category (ID: " . $id . ")");
            return redirect()->route($route, ['catid' => $catid, 'deletecat' => 'true']);
        }
    
        $param = [
            'catid' => $catid,
            'route' => $route,
            'addedcat' => $request->input('addedcat'),
            'save' => $request->input('save'),
            'savecat' => $request->input('savecat'),
            'delete' => $request->input('delete'),
            'deletecat' => $request->input('deletecat'),
            'action' => $request->input('action') ?? '',
            'id' => $request->input('id') ?? 0,
            'search' => $request->input('search') ?? 0,
            'title' => $request->input('title') ?? '',
            'message' => $request->input('message') ?? '',
        ];
    
        return view('pages.support.predefinedreplies.index1', $param);
    }
    
    public function buildCategoriesList($level, $parentlevel, $exclude = "")
    {
        // global $catid;
        //  $result = select_query("tblticketpredefinedcats", "", array("parentid" => $level), "name", "ASC");
        //  while ($data = mysql_fetch_array($result)) {
        //      $id = $data["id"];
        //      $parentid = $data["parentid"];
        //      $category = $data["name"];
        //      if ($id == $exclude) {
        //          continue;
        //      }
        //      echo "<option value=\"" . $id . "\"";
        //      if ($id == $catid) {
        //          echo " selected";
        //      }
        //      echo ">";
        //      for ($i = 1; $i <= $parentlevel; $i++) {
        //          echo "- ";
        //      }
        //      echo (string) $category . "</option>";
        //      buildCategoriesList($id, $parentlevel + 1);
        //  }
        $result = \App\Models\Ticketpredefinedcat::where('parentid', $level)->orderBy('name', 'asc')->get();
        foreach ($result as $key => $value) {
            $id = $value->id;
            $parentid = $value->parentid;
            $category = $value->name;
            if ($id == $exclude) {
                continue;
            }
            $code = "<option value=\"". $id ."\">". $category . "</option>";
            // for ($i = 1; $i < $parentlevel; $i++) { 
            //    $code .= "- ";
            // }
            $this->buildCategoriesList($id, $parentlevel + 1);
            return $code;
        }
    }

    private function deletePreDefCat($catid)
    {
        $result = \App\Models\Ticketpredefinedcat::where('parentid', $catid)->get();
        foreach ($result as $v) {
            $id = $v->id;
            \App\Models\Ticketpredefinedreply::where('catid', $id)->delete();
            \App\Models\Ticketpredefinedcat::where('id', $id)->delete();
            $this->deletePreDefCat($id);
        }
    }
    
    public function NetworkIssues()
    {
        return view('pages.support.networkissues.index');
    }
    
    public function NetworkIssuesGet(Request $request)
    {
        $data = \App\Models\Networkissue::select('id', 'title', 'type', 'priority', 'status', 'startdate', 'enddate');
    
        if ($request->option !== null) {
            $data->where('status', $request->option);
        }
    
        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                return '<a href="./networkissues/edit/' . $data->id . '" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                        <a href="./networkissues/destroy/' . $data->id . '" data-id="' . $data->id . '" data-title="' . $data->title . '" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></a>';
            })
            //  ->addColumn('delete', function($data) {
            // return '<a href="./networkissues/delete/'.$data->id.'" class="btn btn-danger btn-dxs" >delete</a>';
            // }) 
            ->rawColumns(['action'])
            ->toJson();
    }
    
    public function NetworkIssuesDestroy($id)
    {
        $id = (int)$id;
        \App\Models\Networkissue::find($id)->delete();
        return back()->with('success', 'Network issue deleted successfully');
    }

    public function NetworkIssues_add()
    {
        $server = \App\Models\Server::select('id', 'name')->where('active', 1)->where('disabled', 0)->get();
    
        $params = [
            'server' => $server,
        ];
    
        return view('pages.support.networkissues.add', $params);
    }
    
    public function NetworkIssuesStore(Request $request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
        ];
    
        $messages = [
            'title.required' => 'Title required.',
            'description.required' => 'Description required.',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        $db = new \App\Models\Networkissue();
        $db->title = $request->title;
        $db->type = $request->type;
        $db->server = $request->server_id;
        $db->priority = $request->priority;
        $db->status = $request->status;
        $db->startdate = $request->startdate;
        $db->enddate = $request->enddate;
        $db->description = $request->description;
        $db->save();
    
        return back()->with('success', 'Network issue created successfully.');
    }
    
    public function NetworkIssuesEdit($id)
    {
        $data = \App\Models\Networkissue::find((int)$id);
        $server = \App\Models\Server::select('id', 'name')->where('active', 1)->where('disabled', 0)->get();
    
        $params = [
            'data' => $data,
            'server' => $server,
        ];
    
        return view('pages.support.networkissues.edit', $params);
    }
    
    public function NetworkIssuesUpdate(Request $request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
        ];
    
        $messages = [
            'title.required' => 'Title required.',
            'description.required' => 'Description required.',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
    
        $db = \App\Models\Networkissue::find((int)$request->id);
        $db->title = $request->title;
        $db->type = $request->type;
        $db->server = $request->server_id;
        $db->priority = $request->priority;
        $db->status = $request->status;
        $db->startdate = $request->startdate;
        $db->enddate = $request->enddate;
        $db->description = $request->description;
        $db->save();
    
        return back()->with('success', 'Network issue updated successfully.');
    }
    
    public function client(Request $request)
    {
        $query = \App\Models\Client::select('id', 'firstname', 'lastname', 'companyname', 'email');
        $search = $request->q;
    
        if ($search) {
            $query->where(function ($qry) use ($search) {
                $qry->orWhere('firstname', 'LIKE', "%{$search}%")
                    ->orWhere('lastname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        } else {
            $query->limit(100);
        }
    
        $data = $query->get();
        return response()->json($data);
    }
    
    public function getservice(Request $request)
    {
        $data = [];
        $clientID = (int) $request->client;
        $service = $request->service;
        $selectedRelatedType = "";
        $selectedRelatedId = "";
    
        if ($service) {
            $selectedRelatedType = substr($service, 0, 1);
            $selectedRelatedId = substr($service, 1);
        }
    
        $clientData = \App\Models\Client::find($clientID, ['id', 'firstname', 'lastname', 'companyname', 'email']);
        $data['client'] = $clientData;
    
        // Hosting
        $service = DB::table("{$this->prefix}hosting as h")
            ->join("{$this->prefix}products as p", "h.packageid", "=", "p.id")
            ->where("h.userid", $clientID)
            ->select(
                'h.id',
                'h.orderid',
                'h.regdate',
                'h.domain',
                'h.amount',
                'h.billingcycle',
                'h.nextduedate',
                'h.domainstatus as status',
                'p.name as paket'
            )
            ->get();
    
        $html = '<tr>
                    <td>
                        <label><input type="radio" name="related_service" data-type="" value="" checked></label>
                    </td>
                    <td>None</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>';
    
        $adminURL = env('ADMIN_ROUTE_PREFIX');
        foreach ($service as $r) {
            $selected = $selectedRelatedType == "S" && $selectedRelatedId == $r->id;
            $selectedHtml = $selected ? 'checked' : '';
            $serviceUrl = route("admin.pages.clients.viewclients.clientservices.index", ['userid' => $clientID, 'productselect' => $r->id]);
            $html .= '<tr>
                        <td>
                            <label><input type="radio" name="related_service" data-type="product" value="' . $r->id . '" ' . $selectedHtml . '></label>
                        </td>
                        <td>
                            <a href="' . $serviceUrl . '" target="_blank"> ' . $r->paket . '</a> - <a href="http://' . $r->domain . '" target="_blank" >' . $r->domain . '</a>
                        </td>
                        <td>
                            ' . \App\Helpers\Format::Currency($r->amount, null, ['prefix' => 'Rp', 'format' => '3']) . '
                        </td>
                        <td>
                            ' . $r->billingcycle . '
                        </td>
                        <td>
                            ' . Carbon::parse($r->regdate)->isoFormat(Cfg::get('DateFormat')) . '
                        </td>
                        <td>
                            ' . Carbon::parse($r->nextduedate)->isoFormat(Cfg::get('DateFormat')) . '
                        </td>
                        <td>
                            ' . $r->status . '
                        </td>
                    </tr>';
        }
    
        // Domain
        $domain = \App\Models\Domain::where('userid', $clientID)
            ->select(
                'id',
                'registrationdate',
                'domain',
                'firstpaymentamount',
                'nextduedate',
                'status'
            )->get();
    
        foreach ($domain as $r) {
            $selected = $selectedRelatedType == "D" && $selectedRelatedId == $r->id;
            $selectedHtml = $selected ? 'checked' : '';
            $html .= '<tr>
                        <td>
                            <label><input type="radio" name="related_service" data-type="domain" value="' . $r->id . '" ' . $selectedHtml . '></label>
                        </td>
                        <td>
                            <a href="/' . $adminURL . '/clients/domainregistrations/' . $r->id . '" target="_blank">Domain  ' . $r->domain . '</a>
                        </td>
                        <td>
                            ' . \App\Helpers\Format::Currency($r->firstpaymentamount, null, ['prefix' => 'Rp', 'format' => '3']) . '
                        </td>
                        <td>
                            1 Year
                        </td>
                        <td>
                            ' . Carbon::parse($r->registrationdate)->isoFormat(Cfg::get('DateFormat')) . '
                        </td>
                        <td>
                            ' . Carbon::parse($r->nextduedate)->isoFormat(Cfg::get('DateFormat')) . '
                        </td>
                        <td>
                            ' . $r->status . '
                        </td>
                    </tr>';
        }
    
        // dd($service);
        // $product=API::post('GetClientsProducts',$param);
        // dd($product);
        /* $dataProduct=array();
        $html='<tr></tr>';
        if($product->result == 'success'){
            $dataProduct=$product->products->product;
            print_r($dataProduct);
            foreach($dataProduct as $r){
                $html.='<tr>
                            <td>
                                <label><input type="radio" name="related_service[]" data-type="product" value="'.$r->serviceid.'"></label>
                            </td>
                            <td>
                                '.$r->name.'
                            </td>
                            <td>
                                '.$r->firstpaymentamount.'
                            </td>
                            <td>
                                '.$r->billingcycle.'
                            </td>
                            <td>
                                '.$r->regdate.'
                            </td>
                            <td>
                                '.$r->nextduedate.'
                            </td>
                            <td>
                                '.$r->status.'
                            </td>
                        </tr>';
            }
        } */
        $data['html'] = $html;
        return response()->json($data);
    }

}
