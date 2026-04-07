<?php
namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;
use Database;
use Illuminate\Support\Facades\DB;
use Whoops\Run;

class InvoiceReminders{

    public function __construct()
	{
        
	}

    public static function run(){
        if(\App\Helpers\Cfg::getValue('SendReminder') && \App\Helpers\Cfg::getValue('SendInvoiceReminderDays')){
            InvoiceReminders::sendUnpaidInvoiceReminders();
        }
        InvoiceReminders::sendOverdueInvoiceReminders();
        return true;
    }    


    public static function sendUnpaidInvoiceReminders(){
        $invoicedateyear = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") +\App\Helpers\Cfg::getValue('SendInvoiceReminderDays'), date("Y")));
        //$data=\App\Models\Invoice::where('duedate', $invoicedateyear)->where('status','Unpaid')->select('id')->limit(3)->get();
        $data=\App\Models\Invoice::where('duedate', $invoicedateyear)->where('status','Unpaid')->select('id')->get();
        //dd($data);

        foreach($data as $r){
            $id=$r->id;
            \App\Helpers\Functions::sendMessage('Invoice Payment Reminder',$id);
            \App\Helpers\Hooks::run_hook('InvoicePaymentReminder',array("invoiceid" => $id, "type" => "reminder"));
            //$this->addCustom("unpaid", array("invoice", $id));
        }

        //$this->output("unpaid")->write(count($this->getCustom("unpaid")));
        //return $this;
    }

    public static function sendOverdueInvoiceReminders(){
        $prefix=Database::prefix();
        $types = ['First', 'Second', 'Third'];
        foreach ($types as $type) {
            if(\App\Helpers\Cfg::getValue('Send' . $type . 'OverdueInvoiceReminder')){
                $adddate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - (int) \App\Helpers\Cfg::getValue("Send" . $type . "OverdueInvoiceReminder"), date("Y")));
                $resul=DB::table($prefix.'invoices as tblinvoices')
                        ->join($prefix.'clients as tblclients','tblinvoices.userid','=','tblclients.id')
                        ->where('tblinvoices.duedate',$adddate)
                        ->where('tblinvoices.status','Unpaid')
                        ->where('tblclients.overideduenotices',0)
                        ->select('tblinvoices.id','tblinvoices.userid','tblclients.firstname','tblclients.lastname')
                        ->get();
                foreach($resul as $data){
                    $invoiceid  = $data->id;
                    $firstname  = $data->firstname;
                    $lastname   = $data->lastname;
                    $numoverideautosuspend = DB::table($prefix.'invoiceitems as tblinvoiceitems')
                                            ->join($prefix.'hosting as tblhosting','tblinvoiceitems.relid','=','tblhosting.id')
                                            ->where('tblinvoiceitems.type','Hosting')
                                            ->where('tblhosting.overideautosuspend',1)
                                            ->where('tblhosting.overidesuspenduntil','>',date('Y-m-d'))
                                            ->where('tblhosting.overidesuspenduntil','!=','0000-00-00')
                                            ->where('tblinvoiceitems.invoiceid',(int) $invoiceid)
                                            ->count('tblinvoiceitems.id');
                    $typeKey = strtolower($type);
                    if ($numoverideautosuspend == "0") {
                        \App\Helpers\Functions::sendMessage($type . " Invoice Overdue Notice", $invoiceid);
                        \App\Helpers\Hooks::run_hook('InvoicePaymentReminder',['invoiceid' => $invoiceid, 'type' => $typeKey . 'overdue']);

                    }
                   /*  foreach ($types as $type) {
                        $typeKey = strtolower($type);
                        $this->output("overdue." . $typeKey)->write(count($this->getCustom($typeKey)));
                    }
                    $this->output("action.detail")->write(json_encode($this->getDetail()));
                    return $this; */

                }


            }

        }

    }



}