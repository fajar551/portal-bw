<?php
namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;
use App\Http\Controllers\HomeController;
use App\Module\Registrar;
use Database;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoSuspensions{

   public static function run(){
      if(! \App\Helpers\Cfg::get('AutoSuspension')){

         return false;
      }

      \App\Models\Hosting::where('overideautosuspend','1')->where('overidesuspenduntil','<',date("Y-m-d"))->where('overidesuspenduntil','!=','0000-00-00')->update(['overideautosuspend' => '']);
      $clientGroups = \App\Models\Clientgroup::pluck("susptermexempt", "id");
      $clients = array();
      $i = 0;
      $suspenddate =Carbon::today()->subDays(\App\Helpers\Cfg::get("AutoSuspensionDays"))->toDateString();
      $hosting=\App\Models\Hosting::where('domainstatus','Active')
                                    ->where('billingcycle','!=','Free Account')
                                    ->where('billingcycle','!=','Free')
                                    ->where('billingcycle','!=','One Time')
                                    ->where('overideautosuspend','!=','1')
                                    ->where('nextduedate','<=',$suspenddate)
                                    ->orderBy('domain')
                                    ->get();
      foreach($hosting as $data){
         $id         = $data->id;
         $userid     = $data->userid;
         $domain     = $data->domain;
         $packageid  = $data->packageid;
         $nextDueDate = $data->nextduedate;
         if (!array_key_exists($userid, $clients)) {
            $client =\App\Models\Client::where('id',$userid)->first(array("firstname", "lastname", "groupid"));
            if (!$client) {
               continue;
            }
            $clients[$userid] = array("firstname" => $client->firstname, "lastname" => $client->lastname, "groupid" => $client->groupid);
         }
         $firstname = $clients[$userid]["firstname"];
         $lastname = $clients[$userid]["lastname"];
         $groupid = $clients[$userid]["groupid"];
         $getproducts=\App\Models\Hosting::with(['product' => function($qry) use ($packageid){
                                                      $qry->where('id',$packageid);
                                                      //$qry->select(['name','servertype']);
                                          }])
                                          ->where('id',$id)
                                          ->select('nextduedate','packageid')
                                          ->first();
         $prodname=$getproducts->product->name;
         $module=$getproducts->product->servertype;
         $nextDueDate2=$getproducts->nextduedate;
         $susptermexempt = 0;
         if ($groupid) {
            $susptermexempt = $clientGroups[$groupid];
         }
         if ($susptermexempt) {
            continue;
          }
         $serverresult = "No Module";
         LogActivity::Save("Cron Job: Suspending Service - Service ID: " . $id);
         if ($module) {
            if ($nextDueDate != $nextDueDate2) {
               continue;
            }
            (new \App\Module\Server())->ServerSuspendAccount($id);
         }
         if ($domain) {
             $domain = " - " . $domain;
         }
         $loginfo = sprintf("%s%s - %s %s (Service ID: %s - User ID: %s)", $prodname, $domain, $firstname, $lastname, $id, $userid);
         if ($serverresult == "success") {
            \App\Helpers\Functions::sendMessage("Service Suspension Notification", $id);
            $msg = "SUCCESS: " . $loginfo;
            //$this->addSuccess(array("service", $id));
            $i++;
         }else{
            $msg = sprintf("ERROR: Manual Suspension Required - %s - %s", $serverresult, $loginfo);
            //$this->addFailure(array("service", $id, $serverresult));
         }
         LogActivity::Save("Cron Job: " . $msg);
      }

      $addons=\App\Models\Hostingaddon::whereHas('service', function ($query) {
            $query->where("overideautosuspend", "!=", 1);
         })->with("client", "productAddon", "service", "service.product")->where("status", "=", "Active")->whereNotIn("billingcycle", array("Free", "Free Account", "One Time", "onetime"))->where("nextduedate", "<=", $suspenddate)->get();
      foreach ($addons as $addon) {
         if (!$addon->service) {
            continue;
        }
        $suspendTerminateExempt = 0;
        if ($addon->client->groupId) {
            $suspendTerminateExempt = $clientGroups[$addon->client->groupId];
        }
        if ($suspendTerminateExempt) {
            continue;
        }
        $id = $addon->id;
        $serviceId = $addon->serviceId;
        $addonId = $addon->addonId;
        $name = $addon->name;
        $userId = $addon->clientId;
        $domain = $addon->service->domain;
        $firstName = $addon->client->firstName;
        $lastName = $addon->client->lastName;
        if (!$name && $addonId) {
            $name = $addon->productAddon->name;
        }
        $noModule = true;
        $automationResult = false;
        $automation = null;
        if ($addon->productAddon->module) {
            //$automation = \WHMCS\Service\Automation\AddonAutomation::factory($addon);
            //$automationResult = $automation->runAction("SuspendAccount", "");
            $noModule = false;
        }else{
          $addon->status = "Suspended";
          $addon->save();
        }
        $msg = "";
        if ($noModule || $automationResult) {
         $logInfo = sprintf("%s - %s %s (Service ID: %d - Addon ID: %d)", $name, $firstName, $lastName, $serviceId, $id);
         $msg = "SUCCESS: " . $logInfo;
         LogActivity::Save("Cron Job: " . $msg);
         //$this->addSuccess(array("addon", $id));
         if (!$noModule) {
            \App\Helpers\Hooks::run_hook("AddonSuspended", array("id" => $id, "userid" => $userId, "serviceid" => $serviceId, "addonid" => $addonId));
          }
          if ($addonId && $addon->productAddon->suspendProduct) {
            $productName = $addon->service->product->name;
            $module = $addon->service->product->module;
            $serverResult = "No Module";
            LogActivity::Save("Cron Job: Suspending Parent Service - Service ID: " . $serviceId);
            if ($module) {
               $serverResult = (new \App\Module\Server())->ServerSuspendAccount($serviceId, "Parent Service Suspended due to Overdue Addon");
            }
            if ($domain) {
                  $domain = " - " . $domain;
            }
            $logInfo = sprintf("%s %s - %s%s (Service ID: %d - User ID: %d)", $firstName, $lastName, $productName, $domain, $serviceId, $userId);
            if ($serverResult == "success") {
               sendMessage("Service Suspension Notification", $serviceId);
               $msg = "SUCCESS: " . $logInfo;
               //$this->addSuccess(array("service", $serviceId));
            } else {
               $msg = sprintf("ERROR: Manual Parent Service Suspension Required - %s - %s", $serverResult, $logInfo);
               //$this->addFailure(array("service", $serviceId, $serverResult));
            }
            LogActivity::Save("Cron Job: " . $msg);

          }
        }else {
            if (!$noModule && !$automationResult) {
               $logInfo = sprintf("%s - %s %s (Service ID: %d - Addon ID: %d)", $name, $firstName, $lastName, $serviceId, $id);
               //$msg = sprintf("ERROR: Manual Suspension Required - %s - %s", @$automation->getError(), $logInfo);
               //$this->addFailure(array("addon", $id, $automation->getError()));
            }
        }

        if ($msg) {
            LogActivity::Save("Cron Job: " . $msg);
         }
         $i++;
      }
         /* $this->output("suspended")->write(count($this->getSuccesses()));
         $this->output("manual")->write(count($this->getFailures()));
         $this->output("action.detail")->write(json_encode($this->getDetail()));
         return $this; */
      
   }

}