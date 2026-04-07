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

class FixedTermTerminations
{
   public static function run(){
      $product=\App\Models\Product::where('autoterminatedays','>',0)->select('id','autoterminatedays','autoterminateemail','servertype','name')->orderBy('id')->get();
      //$product=\App\Models\Product::select('id','autoterminatedays','autoterminateemail','servertype','name')->orderBy('id')->get();
      foreach($product->toArray() as $data ){
         //list($pid, $autoterminatedays, $autoterminateemail, $module, $prodname) = $data;
         $pid=$data['id'];
         $autoterminatedays=$data['autoterminatedays'];
         $autoterminateemail=$data['autoterminateemail'];
         $servertype=$data['servertype'];
         $name=$data['name'];
         if ($autoterminateemail) {
            $autoTerminateMailTemplate = \App\Models\Emailtemplate::find($autoterminateemail);
            $terminatebefore = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $autoterminatedays, date("Y")));
            $hosting=\App\Models\Hosting::select('id','userid','domain')
                     ->with('client')
                     //->where('packageid',$pid)
                     /* ->where('regdate','<=',$terminatebefore) */
                     ->where(function($qry){
                        $qry->where('domainstatus','Active');
                        $qry->orWhere('domainstatus','Suspended');
                        }) 
                     ->orderBy('id')
                     ->get();

            foreach($hosting as $r){
               $serviceid=$r->id;
               $userid=$r->userid;
               $domain=$r->domain;
               $firstname=$r->client->firstname;
               $lastname=$r->client->lastname;
               $moduleresult = "No Module";
               LogActivity::Save("Cron Job: Auto Terminating Fixed Term Service - Service ID: " . $serviceid);
               if ($module) {
                  $moduleresult = (new \App\Module\Server())->ServerTerminateAccount($serviceid);
               }
               if ($domain) {
                  $domain = " - " . $domain;
               }
               $loginfo = sprintf("%s%s - %s %s (Service ID: %s - User ID: %s)", $prodname, $domain, $firstname, $lastname, $serviceid, $userid);
               if ($moduleresult == "success") {
                  if ($autoterminateemail) {
                      \App\Helpers\Functions::sendMessage($autoTerminateMailTemplate, $serviceid);
                  }
                  $msg = "SUCCESS: " . $loginfo;
                  //$this->addSuccess(array("service", $serviceid));
               }else{
                  $msg = "ERROR: Manual Terminate Required - " . $moduleresult . " - " . $loginfo;
                  //$this->addFailure(array("service", $serviceid, $moduleresult));
               }
               LogActivity::Save("Cron Job: " . $msg);
            }
           /*  $this->output("terminations")->write(count($this->getSuccesses()));
            $this->output("manual")->write(count($this->getFailures()));
            $this->output("action.detail")->write(json_encode($this->getDetail()));
            return $this; */
         }


      }
   }

   
}