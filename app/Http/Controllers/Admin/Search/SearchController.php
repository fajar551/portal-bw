<?php
namespace App\Http\Controllers\Admin\Search;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{   
    const TYPE_CLIENT = "client";
    const TYPE_CONTACT = "contact";
    const TYPE_SERVICE = "service";
    const TYPE_DOMAIN = "domain";
    const TYPE_INVOICE = "invoice";
    const TYPE_TICKET = "ticket";
    const TYPE_OTHER = "other";
    protected $numResults = 1;


    
    public function __construct() {
       
    }

    public function index(Request $request){
        $error=true;
        $data=array();
        $search=trim($request->searchterm);
        $hideInactive=(int)$request->hide_inactive;
        $numResults = $this->numResults;
        $pagination=$request->action;
        /* client */
        /* if(strlen($search) < 3 && !is_numeric($search)){
            return response()->json([
                    'error' => $error,
                    'data'  => array()
                    ]);
        } */

        

        $getClient=\App\Models\Client::select('id','firstname','lastname','companyname','status','email')->where(function($qry) use ($search){
                        if (3 < strlen($search)) {
                            $qry->orWhere('firstname','LIKE',"%{$search}%");
                            $qry->orWhere('lastname','LIKE',"%{$search}%");
                            $qry->orWhere('companyname','LIKE',"%{$search}%");
                            $qry->orWhere('address1','LIKE',"%{$search}%");
                            $qry->orWhere('address2','LIKE',"%{$search}%");
                            $qry->orWhere('postcode','LIKE',"%{$search}%");
                            $qry->orWhere('phonenumber','LIKE',"%{$search}%");
                            $qry->orWhere('tax_id','LIKE',"%{$search}%");
                        }
                        if (is_numeric($search)) {
                            $qry->orWhere("id", $search);
                        }
                        if (is_numeric($search) && strlen($search) == 4) {
                            $qry->orWhere("cardlastfour", $search);
                        }
                        if (!is_numeric($search)) {
                            $qry->orWhere('email','LIKE',"%{$search}%");
                            $qry->orWhere('city','LIKE',"%{$search}%");
                            $qry->orWhere('state','LIKE',"%{$search}%");
                        }
                        });
        if($hideInactive){
            $getClient->where('status','Active');
        }
        $countClient=$getClient->count();

        if($pagination =='client'){
            $getClient->offset($numResults)->limit(PHP_INT_MAX);
            $getClient=$getClient->get();
            $data=[
                                'page' => 'client',
                                'data'  => $getClient->toArray()
                            ];

            return response()->json($data);

        }else{
            $getClient->limit($numResults);
        }


        $getClient=$getClient->get();
        $data['client']=[
                            'count' => (int) $countClient,
                            'data'  => $getClient->toArray()
                        ];
        
        /* Contacts  */
        $getContact=\App\Models\Contact::select('id','firstname','lastname','email','companyname','userid');

        if($hideInactive){
            $getContact->whereHas('client', function($q)
                            {
                                $q->where('status','=', 'Active');
                            });
        }

        $getContact->where(function($qry) use ($search){
                    if (3 < strlen($search)) {
                        $qry->orWhere('firstname','LIKE',"%{$search}%");
                        $qry->orWhere('lastname','LIKE',"%{$search}%");
                        $qry->orWhere('companyname','LIKE',"%{$search}%");
                        $qry->orWhere('address1','LIKE',"%{$search}%");
                        $qry->orWhere('address2','LIKE',"%{$search}%");
                        $qry->orWhere('postcode','LIKE',"%{$search}%");
                        $qry->orWhere('phonenumber','LIKE',"%{$search}%");
                        $qry->orWhere('tax_id','LIKE',"%{$search}%");
                    }
                    if (is_numeric($search)) {
                        $qry->orWhere("id", $search);
                    } else {
                        $qry->orWhere("email", "LIKE", "%" . $search . "%");
                        $qry->orWhere("city", "LIKE", "%" . $search . "%");
                        $qry->orWhere("state", "LIKE", "%" . $search . "%");
                    }
        });
        $totalResults=$getContact->count();
        if($pagination =='contacts'){
            $getContact->offset($numResults)->limit(PHP_INT_MAX);
            $getContact=$getContact->get();
            $data=[
                                'page' => 'contacts',
                                'data'  => $getContact->toArray()
                            ];

            return response()->json($data);

        }else{
            $getContact->limit($numResults);

        }
       
        $getContact=$getContact->get();

        $data['contacts']=[
            'count' => (int)$totalResults,
            'data'  => $getContact
        ];

        /* service */
        $matchingServices=\App\Models\Hosting::query();
        //$matchingServices->with('product');
        $matchingServices->with(['client' => function($qry) use ($hideInactive) {
            //$qry->select('firstname','lastname','companyname');
            if($hideInactive){
                $qry->where('status','Active');
            }
        }]);
        $matchingServices->with('product');
        $matchingServices->where(function($qry) use ($search){
            if (is_numeric($search)) {
                $qry->where("id", $search);
            }
            if (3 < strlen($search)){
                $qry->orWhere('domain','LIKE',"%{$search}%");
                $qry->orWhere('username','LIKE',"%{$search}%");
                $qry->orWhere('dedicatedip','LIKE',"%{$search}%");
                $qry->orWhere('assignedips','LIKE',"%{$search}%");
                $qry->orWhere('notes','LIKE',"%{$search}%");
            }
        });
        $matchingServices->select('id','userid','domain','domainstatus','packageid');
        $totalResults = $matchingServices->count();
        if($pagination =='service'){
            $matchingServices->offset($numResults)->limit(PHP_INT_MAX);
        }else{
            $matchingServices->limit($numResults);
        }

        $matchingServices=$matchingServices->get();
        //dd($matchingServices);
        $service=array();
        foreach($matchingServices as $r){
            $service[]=[
                           'id'     => $r->id,
                           'userid' => $r->userid,
                           'domain' => $r->domain,
                           'domainstatus' => $r->domainstatus,
                           'firstname'    => $r->client->firstname,
                           'lastname'    => $r->client->lastname,
                           'companyname'  => $r->client->companyname,
                           'product_name' => $r->product->name,

                    ];
        }

        if($pagination =='service'){
            $data=[
                                'page' => 'service',
                                'data'  => $service
                            ];
            return response()->json($data);
        }

        $data['service']=[
            'count' => (int)$totalResults,
            'data'  => $service
        ];

        /* print_r(json_decode(json_encode($matchingServices)));
        dd($matchingServices); */

        /*Domain*/
        $matchingDomains = DB::table("tbldomains")
                            ->select(array("tblclients.firstname", "tblclients.lastname", "tblclients.companyname", "tbldomains.id", "tbldomains.userid", "tbldomains.domain", "tbldomains.status"))
                            ->join("tblclients", "tblclients.id", "=", "tbldomains.userid")
                            ->where(function ($whereQuery) use($search) {
                                if (3 < strlen($search)) {
                                    $whereQuery->where("domain", "LIKE", "%" . $search . "%")->orWhere("additionalnotes", "LIKE", "%" . $search . "%");
                                }
                                if (is_numeric($search)) {
                                    $whereQuery->orWhere("tbldomains.id", $search);
                                }
                             });
                            if ($hideInactive) {
                                $matchingDomains->where("tblclients.status", "Active");
                            }
        $totalResults = $matchingDomains->count();
        
        if($pagination =='domain'){
            $matchingDomains->offset($numResults)->limit(PHP_INT_MAX);
            $domain = $matchingDomains->get();
            $data=[
                'page' => 'domain',
                'data'  => $domain
            ];
            return response()->json($data);
            
        }else{
            $matchingDomains->limit($numResults);
        }
        
        $domain = $matchingDomains->get();
        $data['domain']=[
            'count' => (int)$totalResults,
            'data'  => $domain
        ];
        /* Ticket */
        $matchingTickets = DB::table("tbltickets")
                            ->select(array("tbltickets.id", "tbltickets.tid", "tbltickets.did", "tbltickets.userid", "tbltickets.date", "tbltickets.title", "tbltickets.urgency", "tbltickets.status", "tbltickets.lastreply", "tbltickets.name", "tblclients.firstname", "tblclients.lastname", "tblclients.companyname"))
                            ->leftJoin("tblclients", "tblclients.id", "=", "tbltickets.userid")
                            ->where(function ($whereQuery) use($search) {
                                $whereQuery->where("tid", $search)->orWhere("title", "LIKE", "%" . $search . "%");
                            })->orderBy("lastreply", "desc");
        $ticketDepartments = null;
        if ($hideInactive) {
            $matchingTickets->where(function ($whereQuery) {
                $whereQuery->where("tblclients.status", "Active")->orWhereNull("tblclients.status");
            });
        }
        $totalResults = $matchingTickets->count();
        if($pagination =='ticket'){
            $matchingTickets->offset($numResults)->limit(PHP_INT_MAX);
            $ticket = $matchingTickets->get();
            $data=[
                'page' => 'ticket',
                'data'  => $ticket
            ];
            return response()->json($data);
            
        }

        $matchingTickets->limit($numResults);
        $ticket= $matchingTickets->get();

        $data['ticket']=[
            'count' => (int)$totalResults,
            'data'  => $ticket
        ];


        $matchingInvoices =DB::table("tblinvoices")->select(array("tblclients.firstname", "tblclients.lastname", "tblclients.companyname", "tblinvoices.id", "tblinvoices.userid", "tblinvoices.invoicenum", "tblinvoices.date", "tblinvoices.total", "tblinvoices.status", "tblinvoices.paymentmethod"))->join("tblclients", "tblclients.id", "=", "tblinvoices.userid")->where(function ($whereQuery) use($search) {
            $whereQuery->where("invoicenum", "LIKE", "%" . $search . "%");
            if (is_numeric($search)) {
                $whereQuery->orWhere("tblinvoices.id", $search);
            }
        });

        if($hideInactive){
            $matchingInvoices->where("tblclients.status", "Active");
        }
        $totalResultsInvoice = $matchingInvoices->count();
        if($pagination =='invoices'){
            $matchingInvoices->offset($numResults)->limit(PHP_INT_MAX);
            $invoices = $matchingInvoices->get();
            $data=[
                'page' => 'invoices',
                'data'  => $invoices
            ];
            return response()->json($data);
        }
        $matchingInvoices->limit($numResults);
        $invoices= $matchingInvoices->get();
        $data['invoices']=[
            'count' => (int)$totalResultsInvoice,
            'data'  => $invoices
        ];
        /*end invoice */

        return response()->json($data);
        
    }

}