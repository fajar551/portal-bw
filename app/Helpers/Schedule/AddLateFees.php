<?php
namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;
use Database;
use Illuminate\Support\Facades\DB;

class AddLateFees{

    public function __construct()
	{
        
	}

    

    public static function run(){
        global $_LANG;
        $prefix=Database::prefix();
        $configTaxLateFee = \App\Helpers\Cfg::getValue("TaxLateFee");
        $configInvoiceLateFeeAmount =(int) \App\Helpers\Cfg::getValue("InvoiceLateFeeAmount");
        $configAddLateFeeDays = \App\Helpers\Cfg::getValue("AddLateFeeDays");
        $configLateFeeType = \App\Helpers\Cfg::getValue("LateFeeType");
        
        $configLateFeeMinimum = \App\Helpers\Cfg::getValue("LateFeeMinimum");
        if ($configTaxLateFee) {
            $taxlatefee = "1";
        }
        $invoiceids = array();
        if ($configInvoiceLateFeeAmount != "0.00") {
            if ($configAddLateFeeDays == "") {
                $configAddLateFeeDays = "0";
            }
            $adddate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $configAddLateFeeDays, date("Y")));
            $result=DB::table( $prefix.'invoices as  tblinvoices')
                    ->join($prefix.'clients as tblclients','tblinvoices.userid','=','tblclients.id')
                    ->where('duedate','<=',$adddate)
                    ->where('tblinvoices.status','Unpaid')
                    /* ->where('tblinvoices.userid',7 )*/
                    ->whereDate('tblinvoices.duedate','!=',DB::raw('date(tblinvoices.date)'))
                    ->where('tblclients.latefeeoveride',0)
                    ->select('tblinvoices.*')
                    /* ->limit(3) */
                    ->get();
                //dd($result);
            foreach($result as $data){
                $userid         = $data->userid;
                $invoiceid      = $data->id;
                $duedate        = $data->duedate;
                $paymentmethod  = $data->paymentmethod;
                $total          = $data->total;
                $lateFeeInvoiceCount=\App\Models\Invoiceitem::where('type','LateFee')->where('invoiceid',$invoiceid)->count();
                if(!$lateFeeInvoiceCount){
                    if ($configLateFeeType == "Percentage") {
                        $amountpaid=\App\Models\Account::selectRaw('SUM(amountin)-SUM(amountout) as amountpaid')->where('invoiceid',$invoiceid)->first();
                        $amountpaid=(int)$amountpaid->amountpaid;
                        $balance = round($total - $amountpaid, 2);
                        $latefeeamount=\App\Helpers\Functions::format_as_currency($balance * $configInvoiceLateFeeAmount / 100);
                    }else{
                        $latefeeamount = $configInvoiceLateFeeAmount;
                    }
                    if (0 < $configLateFeeMinimum && $latefeeamount < $configLateFeeMinimum) {
                        $latefeeamount = $configLateFeeMinimum;
                    }
                    \App\Helpers\Functions::getUsersLang($userid);
                    
                    $invoice=new \App\Models\Invoiceitem();
                    $invoice->userid = $userid;
                    $invoice->type = 'LateFee';
                    $invoice->invoiceid = $invoiceid;
                    $invoice->description = sprintf("%s (%s %s)", $_LANG["latefee"],  $_LANG["latefeeadded"],(new \App\Helpers\Functions())->fromMySQLDate(date("Y-m-d")));
                    $invoice->amount = $latefeeamount;
                    $invoice->duedate = $duedate;
                    $invoice->paymentmethod = $paymentmethod;
                    $invoice->taxed = $taxlatefee;
                    $invoice->save();
                    \App\Helpers\Invoice::UpdateInvoiceTotal($invoiceid);
                    \App\Helpers\Hooks::run_hook('AddInvoiceLateFee',['invoiceid',$invoiceid]);
                    try{
                        \App\Helpers\Functions::sendMessage('Invoice Modified',$invoiceid);
                    }catch (\Exception $e) {

                    }
                    $invoiceids[] = $invoiceid;
                }
            }
            $invoiceTotalCount = count($invoiceids);
            $invoiceTotalMessage = "";
            if ($invoiceTotalCount) {
                $invoiceTotalMessage = " to Invoice Numbers " . implode(",", $invoiceids);
            }
            LogActivity::Save(sprintf("Cron Job: Late Invoice Fees added to %s Invoices%s", $invoiceTotalCount, $invoiceTotalMessage));
            //$this->output("invoice.latefees")->write(count($this->getSuccesses()));
            //$this->output("action.detail")->write(json_encode($this->getDetail()));
            //dd(sprintf("Cron Job: Late Invoice Fees added to %s Invoices%s", $invoiceTotalCount, $invoiceTotalMessage));
    
        }
    }




}