<?php

namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;
use Database;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\Cfg;
use Exception;


class EmailCampaigns
{
   public static function run(){
      $emailsSent = 0;
      $emailRules = \App\Models\Emailmarketer::where('disable',0)->orderBy('id')->get();
      //dd($emailRules);
      foreach($emailRules as $emailRule){
         $name = $emailRule->name;
         $type = $emailRule->type;
         //$settings = json_decode($emailRule->settings);
         $settings = $emailRule->settings;
         $marketing = $emailRule->marketing;
         $clientnumdays    = (int) $settings->clientnumdays;
         $clientsminactive = $settings->clientsminactive;
         $clientsmaxactive = $settings->clientsmaxactive;
         $clientemailtpl   = $settings->clientemailtpl;
         $products         = $settings->products ??array();
         $addons           = $settings->addons ??array();
         $prodstatus       = $settings->prodstatus;
         $billingCycles    = $settings->product_cycle ??array();
         $prodnumdays      = $settings->prodnumdays;
         $prodfiltertype   = $settings->prodfiltertype;
         $prodexcludepid   = $settings->prodexcludepid??array();
         $prodexcludeaid   = $settings->prodexcludeaid??array();
         $prodemailtpl     = $settings->prodemailtpl;
         $query = '';
         $query1 = '';
         $emailtplid = "";
         $criteria = array();
         $getData=array();
         $getadataAddons=array();
         if ($type == "client") {
            $emailtplid = $clientemailtpl;
            $getData=\App\Models\Client::select('id');
            if (0 < $clientnumdays) {
               //$getData->where('datecreated',date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $clientnumdays, date("Y"))) );
            }
            if (strlen($clientsminactive)) {
               $getData->whereRaw("(SELECT COUNT(*) FROM tblhosting WHERE tblhosting.userid=tblclients.id AND tblhosting.domainstatus='Active')>=" . (int) $clientsminactive);
            }
            if (strlen($clientsmaxactive)){
               $getData->whereRaw("(SELECT COUNT(*) FROM tblhosting WHERE tblhosting.userid=tblclients.id AND tblhosting.domainstatus='Active')<=" . (int) $clientsmaxactive);
            }
            if ($marketing) {
               if(Cfg::get('MarketingEmailConvert') !='on'){
                  $getData->where('marketing_emails_opt_in',1);
               }else{
                  $getData->where('marketing_emails_opt_in',0);
               }
            }
            $getData->get();
         }else{
            if($type == "product"){
               $emailtplid = $prodemailtpl;
               if(count($products)) {
                  $getData=\App\Models\Product::select('id');
                  $getData->whereIn('packageid',$prodstatus);
                  if (0 < $prodnumdays) {
                     if (in_array($prodfiltertype, array("afterorder", "after_order"))) {
                        $getData->where('regdate', date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $prodnumdays, date("Y"))));
                     }else{
                        if(in_array($prodfiltertype, array("beforedue", "before_due"))){
                           $getData->where('nextduedate',date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $prodnumdays, date("Y"))));
                        }else{
                           if(in_array($prodfiltertype, array("afterdue", "after_due"))){
                              $getData->where('nextduedate', date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $prodnumdays, date("Y"))));
                           }else{
                              continue;
                           }
                        }
                     }
                  }
                  if(count($prodstatus)){
                     $getData->whereIn('domainstatus',$prodstatus);
                  }
                  if(count($billingCycles)){
                     $getData->whereIn('domainstatus',$billingCycles);
                  }
                  if(count($prodexcludepid)){
                     $productExcludePidIn = $prodexcludepid;
                     $getData->whereRaw("(SELECT COUNT(*) FROM tblhosting h2 WHERE h2.userid=(SELECT userid FROM tblhosting WHERE tblhosting.id=tblhostingaddons.hostingid) AND h2.packageid IN (" . $productExcludePidIn . ") AND h2.domainstatus='Active')=0");
                  }
                  if(count($prodexcludeaid)){
                     $productExcludeAidIn = $prodexcludeaid;
                     $getData->whereRaw("(SELECT COUNT(*) FROM tblhostingaddons h2 WHERE h2.hostingid=tblhostingaddons.hostingid AND h2.addonid IN (" . $productExcludeAidIn . ") AND h2.status='Active' and h2.id != tblhostingaddons.id)=0");
                  }
                  if($marketing){
                     if(Cfg::get('MarketingEmailConvert') !='on' ){
                        $getData->where('marketing_emails_opt_in',1);
                     }else{
                        $getData->where('marketing_emails_opt_in',0);
                     }
                  }
                  $getData->get();
               }
               if (count($addons)) {
                  $getadataAddons=\App\Models\Hostingaddon::select('hostingid');
                  $getadataAddons->whereIn('addonid',$addons);
                  if(0 < $prodnumdays){
                     if(in_array($prodfiltertype, array("afterorder", "after_order"))){
                        $getadataAddons->where('regdate',date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $prodnumdays, date("Y"))));
                     }else{
                        if(in_array($prodfiltertype, array("afterdue", "after_due"))){
                           $getadataAddons->where('nextduedate', date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $prodnumdays, date("Y"))));
                        }else{
                           continue;
                        }
                     }
                  }
                  if (count($prodstatus)) {
                     $getadataAddons->whereIn('status',$prodstatus);
                  }
                  if (count($billingCycles)) {
                     $getadataAddons->whereIn('billingcycle',$billingCycles);
                  }
                  if (count($prodexcludepid) && ($productExcludePidIn = db_build_in_array($prodexcludepid))) {
                     $getadataAddons->whereRaw("(SELECT COUNT(*) FROM tblhosting h2 WHERE h2.userid=(SELECT userid FROM tblhosting WHERE tblhosting.id=tblhostingaddons.hostingid) AND h2.packageid IN (" . $productExcludePidIn . ") AND h2.domainstatus='Active')=0");
                  }
                  if (count($prodexcludeaid) && ($productExcludeAidIn = db_build_in_array($prodexcludeaid))) {
                     $getadataAddons->whereRaw("(SELECT COUNT(*) FROM tblhostingaddons h2 WHERE h2.hostingid=tblhostingaddons.hostingid AND h2.addonid IN (" . $productExcludeAidIn . ") AND h2.status='Active' and h2.id != tblhostingaddons.id)=0");
                  }
                  if($marketing){
                     if(Cfg::get('MarketingEmailConvert') !='on' ){
                        $getData->where('marketing_emails_opt_in',1);
                     }else{
                        $getData->where('marketing_emails_opt_in',0);
                     }
                     $getadataAddons->whereRaw( "(SELECT COUNT(*) FROM tblclients h3 WHERE h3.id=(SELECT userid FROM tblhosting WHERE tblhosting.id=tblhostingaddons.hostingid) AND h3." . $thisCriteria . ")=1");
                  }
                  $getadataAddons->get();
               }
            }
         }
        
         //dd( $getData->toSql(), $getData->get());
        $mailTemplate=\App\Models\Emailtemplate::find($emailtplid);
        if($getData){
            foreach($getData as $r){
               $id = $r->id;
               \App\Helpers\Functions::sendMessage($mailTemplate, $id);
            }

        }
         if($getadataAddons){
            foreach($getData as $r){
               $id = $r->id;
               \App\Helpers\Functions::sendMessage($mailTemplate, $id);
            }
         }
      }

   }

}