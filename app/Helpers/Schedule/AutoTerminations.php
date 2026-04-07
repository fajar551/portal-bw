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
use App\Helpers\Cfg;

class AutoTerminations
{

   public static function run()
   {
      if (!Cfg::get('AutoTermination')) {
         /* $this->output("terminations")->write(0);
         $this->output("success.detail")->write("{}");
         $this->output("manual")->write(0);
         $this->output("failure.detail")->write("{}"); */
         return false;
      } else {
         $clientGroups = \App\Models\Clientgroup::pluck('susptermexempt', 'id');
         $clients = array();
         $terminatedate = Carbon::today()->subDays(Cfg::get("AutoTerminationDays"))->toDateString();
         $hosting = \App\Models\Hosting::where(function ($query) {
                                          $query->where('domainstatus', 'Active');
                                          $query->orWhere('domainstatus', 'Suspended');
                                       })
                                       ->where('billingcycle', '!=', 'Free Account')
                                       ->where('billingcycle', '!=', 'One Time')
                                       ->where('billingcycle', '!=', 'onetime')
                                       ->where('nextduedate', '<=', $terminatedate)
                                       ->where('nextduedate', '!=', '0000-00-00')
                                       ->where('overideautosuspend', '!=', '1')
                                       ->orderBy('domain')
                                       ->get();
         foreach ($hosting->toArray() as $data) {
            $serviceid = $data["id"];
            $userid = $data["userid"];
            $domain = $data["domain"];
            $packageid = $data["packageid"];
            $nextDueDate = $data["nextduedate"];
            if (!array_key_exists($userid, $clients)) {
               $client = \App\Models\Client::where("id", $userid)->first(array("firstname", "lastname", "groupid"));
               if (!$client) {
                  continue;
               }
               $clients[$userid] = array("firstname" => $client->firstname, "lastname" => $client->lastname, "groupid" => $client->groupid);
            }
            $firstname = $clients[$userid]["firstname"];
            $lastname = $clients[$userid]["lastname"];
            $groupid = $clients[$userid]["groupid"];
            $getproducts = \App\Models\Hosting::with(['product' => function ($qry) use ($packageid) {
               $qry->where('id', $packageid);
               //$qry->select(['name','servertype']);
            }])
               ->where('id', $serviceid)
               ->select('nextduedate', 'packageid')
               ->first();
            //dd($getproducts);
            $prodname = $getproducts->product->name;
            $module = $getproducts->product->servertype;
            $nextDueDate2 = $getproducts->nextduedate;
            $susptermexempt = 0;
            if ($groupid) {
               $susptermexempt = $clientGroups[$groupid];
            }
            if ($susptermexempt) {
               continue;
            }
            $serverresult = "No Module";
            LogActivity::Save("Cron Job: Terminating Service - Service ID: " . $serviceid);
            if ($module) {
               if ($nextDueDate != $nextDueDate2) {
                  continue;
               }
               $serverresult = (new \App\Module\Server())->ServerTerminateAccount($serviceid);
               if ($domain) {
                  $domain = " - " . $domain;
               }
               $loginfo = sprintf("%s%s - %s %s (Service ID: %s - User ID: %s)", $prodname, $domain, $firstname, $lastname, $serviceid, $userid);
               if($serverresult == "success") {
                  //$this->addSuccess(array("service", $serviceid));
               }else{
                  //$this->addFailure(array("service", $serviceid, $serverresult));
                  LogActivity::save(sprintf("ERROR: Manual Terminate Required - %s - %s", $serverresult, $loginfo));
               }
            }
         }

         $addons=\App\Models\Hostingaddon::whereHas('service', function ($query) {
                                             $query->where("overideautosuspend", "!=", 1);
                                          })->with("client", "productAddon", "service", "service.product")
                                          ->whereIn("status", array("Active", "Suspended"))
                                          ->whereNotIn("billingcycle", array("Free", "Free Account", "One Time"))
                                          ->where("nextduedate", "<=", $terminatedate)
                                          ->where("nextduedate", "!=", "0000-00-00")
                                          ->get();
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
            if ($addon->productAddon->module) {
               //$automation = \WHMCS\Service\Automation\AddonAutomation::factory($addon);
               //$automationResult = $automation->runAction("TerminateAccount");
               if ($automationResult) {
                  $this->addSuccess(array("addon", $addon->id));
               }else{
                  //$this->addFailure(array("addon", $addon->id, $automation->getError()));
                  $logInfo = sprintf("%s - %s %s (Service ID: %d - Addon ID: %d - User ID: %d)", $addon->name ? $addon->name : $addon->productAddon->name, $addon->client->firstName, $addon->client->lastName, $addon->serviceId, $addon->id, $addon->clientId);
                  LogActivity::Save(sprintf("ERROR: Manual Terminate Required - %s - %s", $automation->getError(), $logInfo));
               }
            }else{
               $addon->status = "Terminated";
               $addon->save();
               \App\Helpers\Hooks::run_hook("AddonTerminated", array("id" => $addon->id, "userid" => $addon->clientId, "serviceid" => $addon->serviceId, "addonid" => $addon->addonId));
            }
         }
        /*  $this->output("terminations")->write(count($this->getSuccesses()));
         $this->output("manual")->write(count($this->getFailures()));
         $this->output("action.detail")->write(json_encode($this->getDetail()));
         return true; */
      }
   }
}
