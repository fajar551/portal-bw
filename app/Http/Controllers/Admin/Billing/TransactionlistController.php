<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Database;
use DataTables;
use DB;
use \App\Helpers\Cfg;
use Illuminate\Support\Carbon;
use \App\Helpers\Format;
use App\Helpers\LogActivity;
use Validator;
use \App\Helpers\HelperApi as LocalApi;
use \App\Helpers\Invoice;
use Illuminate\Database\Eloquent\Model;

class TransactionlistController extends Controller
{

   public function __construct()
    {
        $this->prefix=Database::prefix();
        $this->adminURL =request()->segment(1).'/'.request()->segment(2).'/';
    }

    public function TransactionList()
    {   
        $gateway=\App\Helpers\Gateway::GetGatewaysArray();
        $currency=\App\Models\Currency::all();
        return view('pages.billing.transactionlist.index',['baseURL' =>  $this->adminURL, 'gateway' => $gateway, 'currency' =>$currency ]);
    }

    public function TransactionStore(Request $request){
       // dd($request->all());
        
       
        //dd($date);

        $rules=[
            /* 'invoiceids'          => 'required', */
            'amountin'            => 'required|numeric|min:0|not_in:0',
            'amountout'           => 'required|numeric|min:0|not_in:0',
            /* 'fees'                => 'required|int', */
            'paymentmethod'       => 'required',
        ];
        $messages = [
            'invoiceids.required'   => 'Invoice ID or description is required..',
            'amountin.required'     => 'amountin required.',
            'fees.required'         => 'amountout required.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with('tabAddClient',1);
        }


        $amountIn=(int) $request->amountin;
        $amountOut=(int) $request->amountout;
        $fees=(int) $request->fees;
        $transactionID=$request->transid ?? 0;
        $addCredit=$request->addcredit ??null;
        $paymentMethod=$request->paymentmethod;
        $client=$request->client;
        $currency=$request->currency;
        $date = $request->date;
        $description=$request->description;

        $inputInvoiceid=explode(",", $request->invoiceids);
        $cleanedInvoiceIDs=array();
        foreach ($inputInvoiceid as $tmpInvID) {
            $tmpInvID = trim($tmpInvID);
            if (is_numeric($tmpInvID)) {
                $cleanedInvoiceIDs[] = (int) $tmpInvID;
            }
        }

        $error=array();
        if(count($cleanedInvoiceIDs) == 0 && !$request->description){
            $error[]='The fee being entered must be less than the amount in value.';
        }

        if ((!$request->amountout || $request->amountout == 0) && (!$request->amounti|| $request->amounti == 0) && (!$request->fees || $request->fees == 0)) {
            $error[]= 'Amount In, Amount Out or Fee is required.';
        }

        if ($amountIn && $fees && $amountIn < $fees) {
            $error[]='The fee being entered must be less than the amount in value.';
        }
        /* if ($amountIn && $fees && $fees < 0) {
            $error[]='Fee for Amount In transaction must be a positive value.';
        }
        if (0 < $amountIn && 0 < $amountOut) {
            $error[]='Fee for Amount In transaction must be a positive value.';
        }
        if ($addCredit && (0 < $amountOut)) {
            $error[]='You cannot use Add as Credit and Amount Out. Please use the Manage Credits from the Client Summary.';
        } */

        if (0 < $addCredit && 0 < count($cleanedInvoiceIDs)) {
            $error[]='You cannot use Add as Credit and specify an Invoice ID. Overpayments on an invoice will automatically be credited.';
        }
        
        if ($transactionID && !Invoice::isUniqueTransactionID($transactionID, $paymentMethod)) {
            $error[]='A unique transaction ID is required.';
        }

        if(!empty($error)){
            return redirect()->back()->withErrors($error)->withInput($request->all())->with('tabAddClient',1);
        }


        $DuplicateTransaction=array();

        if(count($cleanedInvoiceIDs) <= 1){
            $invoiceid = count($cleanedInvoiceIDs) ? $cleanedInvoiceIDs[0] : "";
            if ($transactionID && !Invoice::isUniqueTransactionID($transactionID, $paymentMethod)){

                $DuplicateTransaction[]=[
                                            "invoiceid" => $invoiceid,
                                             "transid" =>   $transactionID, 
                                             "amountin" => $amountIn, 
                                             "fees" => $fees, 
                                             "paymentmethod" => $paymentMethod, 
                                             "date" => $date, 
                                             "amountout" => $amountOut, 
                                             "description" => $description, 
                                             "addcredit" => $addCredit,
                                             "userid" => $client,
                                             "currency" => $currency
                                            ];
            }
            
            Invoice::addTransaction($client, $currency, $description, $amountIn, $fees, $amountOut, $paymentMethod, $transactionID, $invoiceid, $date,'','');
            //dd($testing);
            if ($client && $addCredit && (!is_int($invoiceid) || $invoiceid == 0)) {
                if ($transactionID) {
                    $description .= " ( Trans ID: " . $transactionID . ")";
                }
                $date = $date ? (new \App\Helpers\SystemHelper())->toMySQLDate($date): date('Y-m-d H:i:s');
                $Credit=new \App\Models\Credit();
                $Credit->clientid       = $client;
                $Credit->date           = $date;
                $Credit->description    = $description;
                $Credit->amount         = $amountIn;
                $Credit->save();

                /* update cleint credit */
                $client = \App\Models\Client::find((int)$client);
                $client->credit     = "+=".$amountIn;
                $client->save();

                if(is_int($invoiceid)){
                    $gettotalPaid=\App\Models\Account::where('invoiceid',$invoiceid)->selectRaw('SUM(amountin)-SUM(amountout) as totalpaid')->first();
                    $totalPaid=$gettotalPaid->totalpaid;

                    $getDataINV=\App\Models\Invoice::find($invoiceid);
                    $balance = $getDataINV->total - $totalPaid;
                    if ($balance <= 0 && $ $getDataINV->status == "Unpaid") {
                        Invoice::processPaidInvoice($invoiceid, "", $date);
                    }
                }
            }else{
                if (1 < count($cleanedInvoiceIDs)) {
                    $invoicestotal=\App\Models\Invoices::whereIn('id',$cleanedInvoiceIDs)->sum('total');
                    $totalleft = $amountIn;
                    $fees = round($fees / count(@$invoices), 2);
                    foreach ($cleanedInvoiceIDs as $invoiceid) {
                        if (0 < $totalleft) {
                            $invoicetota=\App\Models\Invoice::find($invoiceid)->total;
                            $totalin=\App\Models\Account::where('invoiceid',$invoiceid)->sum('amountin');
                            $paymentdue = $invoicetota - $totalin;
                            if ($paymentdue < $totalleft) {
                                Invoice::addInvoicePayment($invoiceid, $transactionID, $paymentdue, $fees, $paymentMethod, "", $date);
                                $totalleft -= $paymentdue;
                            }else{
                                Invoice::addInvoicePayment($invoiceid, $transactionID, $totalleft, $fees, $paymentMethod, "", $date);
                                $totalleft = 0;
                            }

                        }
                    }

                }
                if (@$totalleft) {
                    Invoice::addInvoicePayment($invoiceid, $transactionID, $totalleft, $fees, $paymentMethod, "", $date);
                }

            }

        }


        if(!empty($DuplicateTransaction)){
            $error=array();
            $error[]='A unique transaction ID is required.';
            
            $valError='';
            foreach($DuplicateTransaction as $e){
                $valError.="{$e} <br />";
            }
            $error[]=$valError;
            if(!empty($error)){
                return redirect()->back()->withErrors($error)->withInput($request->all())->with('tabAddClient',1);
            }
        }

        return back()->with('success', 'The transaction has been added successfully.');

    }


    public function TransactionListData(Request $request){
        $adminURL=request()->segment(1);
        $gateway=\App\Helpers\Gateway::GetGatewaysArray();
        //SELECT tblaccounts.*,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid,tblclients.currency AS currencyid FROM tblaccounts LEFT JOIN tblclients ON tblclients.id=tblaccounts.userid" . $query . " LIMIT " . (int) ($page * $limit) . "," . (int) $limit;
        $transaction=DB::table("{$this->prefix}accounts as accounts")
                            ->leftJoin("{$this->prefix}clients as clients","accounts.userid","=","clients.id")
                            ->select(
                                        'accounts.*',
                                        'clients.firstname',
                                        'clients.lastname',
                                        'clients.companyname',
                                        'clients.groupid',
                                        'clients.currency as currencyid',
                                    );
        if($request->show){
            if($request->show == 'received'){
                $transaction->where('accounts.amountin','>',0);
            }else{
                $transaction->where('accounts.amountout','>',0);
            }
        }

        if($request->filterdescription){
            $transaction->where('accounts.description','LIKE','%'.$request->filterdescription.'%');
        }

        if($request->filtertransid){
            $transaction->where('accounts.transid','=', (int) $request->filtertransid);
        }

        if($request->paymentmethod){
            $transaction->where('accounts.gateway','=',$request->paymentmethod);
        }

        if($request->daterange){
            $date=explode('|',$request->daterange);
            $startDate= Carbon::parse(trim($date[0]))->format('Y-m-d');
            $endDate=Carbon::parse(trim($date[1]))->format('Y-m-d');
            $transaction->whereBetween(DB::raw('DATE(accounts.date)'),[$startDate,$endDate]);
        }
        if($request->amount){
            $amount=(int) $request->amount;
            $transaction->where(function ($query) use ($amount){
                $query->where('accounts.amountin',$amount)->orWhere('accounts.amountout',$amount);
            });
        }
        $transaction->orderBy('accounts.id','DESC');
        //dd($request->all());
        return Datatables::of($transaction)
                            ->addColumn('gateway', function($data) use ( $gateway) {
                                return  $gateway[$data->gateway]??'';
                             })
                            ->addColumn('client', function($data) use ($adminURL) {
                                return '<a href="'.url($adminURL.'/clients/clientsummary?userid='.$data->userid).'">'.ucfirst($data->firstname).' '.ucfirst($data->lastname).'</a>';
                             })
                            ->editColumn('date', function($data) {
                                return  Carbon::parse($data->date)->isoFormat(Cfg::get('DateFormat'));
                            })
                            ->editColumn('description', function($data) {
                                return $data->description.' (#'.$data->invoiceid.') Trans ID: '.$data->transid;
                            })
                            ->editColumn('amountin', function($data) {
                                return Format::Currency((int)$data->amountin,null,['prefix' => 'Rp', 'format' => '3']);
                            })
                            ->editColumn('amountout', function($data) {
                                return Format::Currency((int)$data->amountout,null,['prefix' => 'Rp', 'format' => '3']);
                            })
                            ->editColumn('fees', function($data) {
                                return Format::Currency((int)$data->fees,null,['prefix' => 'Rp', 'format' => '3']);
                            })
                            ->addColumn('action', function($data) {

                                $description= $data->description.' (#'.$data->invoiceid.') Trans ID: '.$data->transid;
                                return '
                                        <form id="fd'.$data->id.'" action="'.url($this->adminURL.'transactionlist/destroy/').'" method="POST">
                                            <input type="hidden" name="_token" value="'.csrf_token().'" />
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="id" value="'.$data->id.'">
                                            <a href="'.url($this->adminURL.'transactionlist/edit/'.$data->id).'" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                                            <button  type="button" data-id="'.$data->id.'"  data-title="'.$description.'" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                                        </form>   
                                            ';
                             })
                            // >removeColumn('password')
                            ->rawColumns(['client','action'])    
                ->toJson();
    }

    public function TransactionEdit($id){
        $id=(int) $id;
        $gateway=\App\Helpers\Gateway::GetGatewaysArray();
        $currency=\App\Models\Currency::all();
        $accounts=\App\Models\Account::find($id);
        $accounts->date = Carbon::parse($accounts->date)->format('d/m/Y');
        $client=\App\Models\Client::find($accounts->userid);
        //$accounts->client = $client->firstname.' '.$client->lastname.' ('.$client->companyname.')';
        $dataCleint=[
                        'id' => $accounts->userid,
                        'firstname' => $client->firstname,
                        'lastname' => $client->lastname,
                        'companyname' => $client->companyname,
                        'email' => $client->email,
                    ];
        $param=[
                    'baseURL' =>  $this->adminURL,
                    'gateway' => $gateway, 
                    'currency' =>$currency,
                    'data'  => $accounts,
                    'select' =>   $dataCleint   
                ];


        return view('pages.billing.transactionlist.edit',$param);

    }

    public function transactionlistUpdate(Request $request){
        $rules=[
            /* 'invoiceids'          => 'required', */
            'amountin'            => 'required|numeric|min:0|not_in:0',
            'amountout'           => 'required|numeric|min:0',
            /* 'fees'                => 'required|int', */
            'paymentmethod'       => 'required',
        ];
        $messages = [
            'invoiceids.required'   => 'Invoice ID or description is required..',
            'amountin.required'     => 'amountin required.',
            'fees.required'         => 'amountout required.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }
        $id=(int) $request->id;
        $date = $request->date ? (new \App\Helpers\SystemHelper())->toMySQLDate($request->date): date('Y-m-d H:i:s');
       // dd($date);
        $account=\App\Models\Account::find($id);
        $account->userid = $request->client;
        $account->date = $date;
        $account->description = $request->description;
        $account->amountin = $request->amountin;
        $account->fees = $request->fees;
        $account->amountout = $request->amountout;
        $account->gateway = $request->paymentmethod;
        $account->transid = $request->transid;
        $account->invoiceid = $request->invoiceids;
        $account->save();
        LogActivity::Save("Modified Transaction - Transaction ID: " . $id, $request->client);
        return back()->with('success', 'Modified Transaction successfully.');

    }



    public function TransactionDestroy(Request $request){
        $id=(int) $request->id;
        $transaction=\App\Models\Account::find($id);
        $userId=$transaction->userid;
        $transaction->delete();
        LogActivity::Save("Deleted Transaction - Transaction ID: {$id}",$userId);
        return back()->with('success', 'Deleted Transaction successfully');
    }

    public function getclientjson(Request $request){
       
        $data = [];
        $query = \App\Models\Client::select('id','firstname','lastname','companyname','email');
        $search = $request->q;
        if($search){
             $query->where('firstname','LIKE',"%{$search}%");
        }else{
            $query->limit(100);
        }
        $data=$query->get();
        return response()->json($data);
    }
}