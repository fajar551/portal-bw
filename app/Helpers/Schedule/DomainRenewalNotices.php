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
use \App\Module\Registrar;
class DomainRenewalNotices{
    public function __construct()
	{
        
	}

    public static function run(){
        $renewalTypes =['first','second','third','fourth','fifth'];
        $renewalsNoticesCount = 0;
        $DomainRenewalNotices=\App\Helpers\Cfg::get('DomainRenewalNotices');
        $renewals =explode(',', $DomainRenewalNotices);
        //dd($renewals);
        foreach ($renewals as $count => $renewal) {
            if ((int) $renewal != 0) {
                $renewalDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + (int) $renewal, date("Y")));
                if ($renewal < -1) {
                    $status = ['Expired','Grace','Redemption'];
                    $emailToSend = "Expired Domain Notice";
                } else {
                    if ($renewal == -1) {
                        $status = ['Active'];
                        $emailToSend = "Expired Domain Notice";
                    } else {
                        $status = ['Active'];
                        $emailToSend = "Upcoming Domain Renewal Notice";
                    }
                }

                
            
                $result=\App\Models\Domain::select('id','userid','domain','registrar','reminders')
                                        //->whereIn('status', $status)->where('nextduedate',$renewalDate)
                                        ->where('recurringamount','!=','0.00')
                                        ->where('reminders','not like','%|'.(int) $renewal.'|%')
                                        //->where('userid',7)
                                        ->get();
                
                foreach($result as $data){
                    $params = array();
                    $params["domainid"] = $data->id;
                    $domainParts = explode(".", $data->domain,2);
                    list($params["sld"], $params["tld"]) = $domainParts;
                    $params["registrar"] = $data->registrar;
                    $registrar=new Registrar();
                    $extra =$registrar->RegGetRegistrantContactEmailAddress($params);
                    $client=new \App\Helpers\ClientClass($data->userid);
                    $details = $client->getDetails();
                    $recipients = array();
                    $recipients[] = $details["email"];
                    if (isset($extra["registrantEmail"])) {
                        $recipients[] = $extra["registrantEmail"];
                    }
                   //dd($details);
                    $emailSent=\App\Helpers\Functions::sendMessage($emailToSend, $data->id, $extra);
                    if ($emailSent === true) {
                        $updateDomain=\App\Models\Domain::find($data->id);
                        $updateDomain->reminders = $data->reminders.'|'.(int) $renewal.'|';
                        $updateDomain->save();

                        $reminders= new \App\Models\Domainreminder();
                        $reminders->domain_id = $data->id;
                        $reminders->date = date("Y-m-d");
                        $reminders->recipients =  implode(",", $recipients);
                        $reminders->type =  $count + 1;
                        $reminders->days_before_expiry =  $renewal;
                        $reminders->save();
                        //$this->addCustom($renewalTypes[$count], array("domain", $data["id"], ""));
                    }else{
                        if (is_string($emailSent)) {
                            //$this->addCustom("failed", array("domain", $data["id"], $emailSent));
                        }
                    }

                }
            }
        }


    }



}