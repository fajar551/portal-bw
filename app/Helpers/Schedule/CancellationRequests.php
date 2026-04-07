<?php
namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;
use App\Module\Registrar;
use Database;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CancellationRequests{

   public static function run(){
      if(! \App\Helpers\Cfg::get('AutoCancellationRequests')){
         /* $this->output("cancellations")->write(0);
         $this->output("success.detail")->write("{}");
         $this->output("manual")->write(0);
         $this->output("failed.detail")->write("{}"); */
         //return $this;
      }
      $terminatedate = Carbon::today()->toDateString();
      $prefix=Database::prefix();
      $cencel=DB::table($prefix.'cancelrequests as tblcancelrequests')
                  ->join($prefix.'hosting as tblhosting','tblcancelrequests.relid','=','tblhosting.id')
                  ->where(function ($query){
                     $query->where('tblhosting.domainstatus','!=','Terminated');
                     $query->where('tblhosting.domainstatus','!=','Cancelled');
                  })
                  // ->where('tblcancelrequests.type','Immediate')
                  ->where(function($query) use ($terminatedate){
                     $query->where('tblcancelrequests.type','Immediate');
                     $query->orWhere(function($qry) use ($terminatedate){
                        $qry->where('tblcancelrequests.type','End of Billing Period');
                        $qry->where('tblhosting.nextduedate','<=',$terminatedate);
                     });
                     
                  }) 
                  ->where(function ($query){
                     $query->orWhere('tblhosting.billingcycle','Free');
                     $query->orWhere('tblhosting.billingcycle','Free Account');
                     $query->orWhere('tblhosting.nextduedate','!=','0000-00-00');
                  })
                  ->orderBy('domain','ASC')
                  ->get();
                 // dd($cencel->toArray());
      foreach($cencel as $data){
         //dd($data);
         $id = $data->id;
         $userid = $data->userid;
         $domain = $data->domain;
         $nextduedate = $data->nextduedate;
         $packageid = $data->packageid;
         $nextduedate = (new \App\Helpers\Functions())->fromMySQLDate($nextduedate);
         $client=\App\Models\Client::find($userid);
         $firstname = $client->firstname;
         $lastname = $client->lastname;
         $products=\App\Models\Product::find($packageid);
         $prodname   = $products->name;
         $module     = $products->servertype;
         $freedomain = $products->freedomain;
         if($freedomain){
            $domainData=\App\Models\Domain::where('domain', $domain)->where('recurringamount','0.00')->first();
            $domainid = $domainData->id;
            $regperiod = $domainData->registrationperiod;
            if ($domainid) {
               $domainparts = explode(".", $domain, 2);
               $tld = $domainparts[1];
               (new \App\Helpers\AdminFunctions())->getCurrency($userid);
               $temppricelist = \App\Helpers\Domain::GetTLDPriceList("." . $tld);
               $renewprice = $temppricelist[$regperiod]["renew"];
               $UpdateDomain=\App\Models\Domain::find( $domainid);
               $UpdateDomain->recurringamount =$renewprice;
               $UpdateDomain->save();
            }
         }
         $serverresult = "No Module";
         if ($module) {
            $serverresult =  (new \App\Module\Server())->ServerTerminateAccount($id);
         }
         $loginfo = sprintf("%s%s - %s %s (Due Date: %s)", $prodname, $domain ? " - " . $domain : "", $firstname, $lastname, $nextduedate);
         if ($serverresult == "success") {
            $hosting=\App\Models\Hosting::find($id);
            $hosting->domainstatus = 'Cancelled';
            $hosting->save();

            $addons =\App\Models\Hostingaddon::with('productAddon')->where("hostingid", "=", $id)->whereNotIn("status", array("Cancelled", "Terminated"))->get();
            foreach ($addons as $addon) {
               if ($addon->productAddon->module) {
                  $automation = \WHMCS\Service\Automation\AddonAutomation::factory($addon);
                  $automationResult = $automation->runAction("CancelAccount");
                  $noModule = false;
               }
               if ($noModule || $automationResult) {
                  $addon->status = "Cancelled";
                  $addon->terminationDate = \WHMCS\Carbon::now()->toDateString();
                  $addon->save();
              } else {
                  if (!$noModule && !$automationResult) {
                     $logInfo = sprintf("%s - %s %s (Due Date: %s) - Addon ID: %d", $addon->name ?: $addon->productAddon->name, $firstname, $lastname, fromMySQLDate($addon->nextDueDate), $addon->id);
                     $msg = sprintf("ERROR: Manual Cancellation Required - %s - %s", $automation->getError(), $logInfo);
                     //$this->addFailure(array("addon", $addon->id, $automation->getError()));
                     LogActivity::save("Cron Job: " . $msg);
                  }
              }
              if ($noModule) {
                  \App\Helpers\Hooks::run_hook("AddonCancelled", array("id" => $addon->id, "userid" => $addon->clientId, "serviceid" => $addon->serviceId, "addonid" => $addon->addonId));
               }
            }
            $msg = "SUCCESS: " . $loginfo;
            LogActivity::Save("Cron Job: " . $msg);
            //$this->addSuccess(array("service", $id));

         }else{
            $msg = sprintf("ERROR: Manual Cancellation Required - %s - %s", $serverresult, $loginfo);
            //$this->addFailure(array("service", $id, $serverresult));
            LogActivity::save("Cron Job: " . $msg);
         }

      }
      //$this->output("cancellations")->write(count($this->getSuccesses()));
     // $this->output("manual")->write(count($this->getFailures()));
     // $this->output("action.detail")->write(json_encode($this->getDetail()));
      //return $this;

   }

}