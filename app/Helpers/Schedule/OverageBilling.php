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
use App\Payment\PayMethod\Model;

class OverageBilling
{
      public static function  monthlyDayOfExecution(){
         return Carbon::now()->endOfMonth();
      }

      public static function anticipatedNextMonthlyRun($checkDay,Carbon $date=null){
         $checkDate = Carbon::now()->second("00");
         $nextMonth = Carbon::now()->second("00")->startOfMonth()->addMonth()->hour($checkDate->format("H"))->minute($checkDate->format("i"));
         $daysInMonth = $nextMonth->daysInMonth;
         if ($daysInMonth < $checkDay) {
            $checkDay = $daysInMonth;
         }
         $checkDate->day($checkDay);
         if (is_null($date)) {
               $date = Carbon::now()->second("00");
         } else {
               $date = $date->copy();
         }
         if ($date->isFuture()) {
               return $date;
         }
         if ($date->format("d") === $checkDate->format("d")) {
               return $date->addMonthNoOverflow();
         }
         return $nextMonth->day($checkDay);
      }

      public static function anticipatedNextRun(Carbon $date = NULL){
         $endNextMonth = Carbon::now()->startOfMonth()->addMonth()->endOfMonth();
         $correctDayDate = OverageBilling::anticipatedNextMonthlyRun((int) $endNextMonth->format("d"), $date);
         if ($date) {
            $correctDayDate->hour($date->format("H"))->minute($date->format("i"));
         }
         return $correctDayDate;
      }
      public static function run(){
         global $_LANG;
         if (! Carbon::now()->isSameDay(OverageBilling::monthlyDayOfExecution())) {
            return false;
         }
         $invoiceaction = Cfg::get('OverageBillingMethod');
         if (!$invoiceaction) {
            $invoiceaction = "1";
         }

         $product=\App\Models\Product::select('id','name','overagesenabled','overagesdisklimit','overagesbwlimit','overagesdiskprice','overagesbwprice')
                                       ->where('overagesenabled','!=','0')
                                       ->get();
        // dd($product);
         foreach($product->toArray() as $data){
            $pid = $data["id"];
            $prodname = $data["name"];
            $overagesenabled = $data["overagesenabled"];
            $overagesdisklimit = $data["overagesdisklimit"];
            $overagesbwlimit = $data["overagesbwlimit"];
            $overagesbasediskprice = $data["overagesdiskprice"];
            $overagesbasebwprice = $data["overagesbwprice"];
            if(!$overagesenabled){
               $p=\App\Models\Product::find($pid);
               $p->overagesenabled = '';
               $p->save(); 
            }
            $overagesenabled = explode(",", $overagesenabled);
           
            $hosting=\App\Models\Hosting::with('client')
                                          ->where('packageid',$pid)
                                          ->where(function($q){
                                                   $q->orWhere('domainstatus','Active');
                                                   $q->orWhere('domainstatus','Suspended');
                                             })
                                          ->get();
            //dd($hosting);
            foreach($hosting as $r){
               $serviceid  = $r->id;
               $userid     = $r->userid;
               $currency   = $r->client->currency;
               $domain     = $r->domain;
               $diskusage  = $r->diskusage;
               $bwusage    = $r->bwusage;
               $getCurrency  =\App\Models\Currency::find($currency);
               $convertrate = $getCurrency->rate;
               if (!$convertrate) {
                  $convertrate = 1;
               }
               $overagesdiskprice = $overagesbasediskprice * $convertrate;
               $overagesbwprice = $overagesbasebwprice * $convertrate;
               $moduleparams = (new \App\Module\Server())->ModuleBuildParams($serviceid);
               //dd($moduleparams);
               $thisoveragesdisklimit = $overagesdisklimit;
               $thisoveragesbwlimit = $overagesbwlimit;
               if(@$moduleparams["customfields"]["Disk Space"]) {
                  $thisoveragesdisklimit = $moduleparams["customfields"]["Disk Space"];
               }
               if(@$moduleparams["customfields"]["Bandwidth"]) {
                  $thisoveragesbwlimit = $moduleparams["customfields"]["Bandwidth"];
               }
               if(@$moduleparams["configoptions"]["Disk Space"]) {
                  $thisoveragesdisklimit = $moduleparams["configoptions"]["Disk Space"];
               }
               if(@$moduleparams["configoptions"]["Bandwidth"]) {
                  $thisoveragesbwlimit = $moduleparams["configoptions"]["Bandwidth"];
               }
               $diskunits = "MB";
               if(@$overagesenabled[1] == "GB") {
                  $diskunits = "GB";
                  $diskusage = $diskusage / 1024;
               }else{
                    if(@$overagesenabled[1] == "TB") {
                        $diskunits = "TB";
                        $diskusage = $diskusage / (1024 * 1024);
                     }
                }
                $bwunits = "MB";
               if(@$overagesenabled[2] == "GB") {
                  $bwunits = "GB";
                    $bwusage = $bwusage / 1024;
               }else{
                  if(@$overagesenabled[2] == "TB") {
                     $bwunits = "TB";
                     $bwusage = $bwusage / (1024 * 1024);
                  }
               }
               $diskoverage = $diskusage - $thisoveragesdisklimit;
               $bwoverage = $bwusage - $thisoveragesbwlimit;
               $overagedesc = $prodname;
               if($domain) {
                  $overagedesc .= " - " . $domain;
               }
               \App\Helpers\Functions::getUsersLang($userid);
               if (0 < $diskoverage) {
                  if ($diskoverage < 0) {
                      $diskoverage = 0;
                  }
                  $diskoverage = round($diskoverage, 2);
                  $diskoveragedesc = sprintf("%s\n%s = %s %s - %s = %s %s @ %s/%s", $overagedesc, $_LANG["overagestotaldiskusage"], $diskusage, $diskunits, $_LANG["overagescharges"], $diskoverage, $diskunits, $overagesdiskprice, $diskunits);
                  $diskoverageamount = $diskoverage * $overagesdiskprice;
                 
                  $billableitems=new \App\Models\Billableitem();
                  $billableitems->userid =$userid;
                  $billableitems->description =$diskoveragedesc;
                  $billableitems->amount =$diskoverageamount;
                  $billableitems->recur =0;
                  $billableitems->recurcycle =0;
                  $billableitems->recurfor =0;
                  $billableitems->invoiceaction =$invoiceaction;
                  $billableitems->duedate =date('Y-m-d');
                  $billableitems->save();
               }
               if(0 < $bwoverage){
                  if ($bwoverage < 0) {
                     $bwoverage = 0;
                  }
                  $bwoverage = round($bwoverage, 2);
                     $bwoveragedesc = sprintf("%s\n%s = %s %s - %s = %s %s @ %s/%s", $overagedesc, $_LANG["overagestotalbwusage"], $bwusage, $bwunits,  $_LANG["overagescharges"], $bwoverage, $bwunits, $overagesbwprice, $bwunits);
                     $bwoverageamount = $bwoverage * $overagesbwprice;

                     $billableitems=new \App\Models\Billableitem();
                     $billableitems->userid =$userid;
                     $billableitems->description =$bwoveragedesc;
                     $billableitems->amount =$bwoverageamount;
                     $billableitems->recur =0;
                     $billableitems->recurcycle =0;
                     $billableitems->recurfor =0;
                     $billableitems->invoiceaction =$invoiceaction;
                     $billableitems->duedate =date('Y-m-d');
                     $billableitems->save();
               }

            }                                 
         }
         \App\Helpers\ProcessInvoices::createInvoices("", "", "", "", null);
         return true;
      }


}