<?php

namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\y;
use Database;
use Illuminate\Support\Facades\DB;

use App\Helpers\Cfg;


class AutoClientStatusSync
{

   public static function run(){
      if(Cfg::get('AutoClientStatusChange') ==1){
         /* $this->output("processed")->write(0);
         $this->output("active.product.addon")->write(0);
         $this->output("active.product.domain")->write(0);
         $this->output("active.product.service")->write(0);
         $this->output("inactive.login")->write(0);
         $this->output("success.detail")->write("{}");
         $this->output("failure.detail")->write("{}"); */
         return false;
      }
     
      AutoClientStatusSync::deactivateClientsWithoutLoginActivity();
      AutoClientStatusSync::activateClientsWithActiveHostingProduct();
      AutoClientStatusSync::activateClientsWithActiveProductAddon();
      AutoClientStatusSync::activateClientsWithActiveDomainProduct();
      AutoClientStatusSync::activateClientsWithActiveBillableItems();
   }
   protected static function activateClientsWithActiveDomainProduct()
   {
      $prefix=Database::prefix();
      $clientIds = DB::table($prefix."domains as tbldomains")->join($prefix."clients as tblclients", "tblclients.id", "=", "tbldomains.userid")->where("tblclients.status", \App\Models\Client::STATUS_INACTIVE)->where("tblclients.overrideautoclose", "0")->whereIn("tbldomains.status", array(\App\Models\Domain::ACTIVE, \App\Models\Domain::PENDING_TRANSFER))->pluck("tbldomains.userid");
      if (count($clientIds)) {
         DB::table("tblclients")->whereIn("id", $clientIds)->update(array("status" => \App\Models\Client::STATUS_ACTIVE));
      }
      //$this->output("active.product.domain")->write(count($clientIds));
      //$this->activeClients = array_merge($this->activeClients, $clientIds);
      //return $this;
   }
   protected static function activateClientsWithActiveProductAddon()
   {
      $prefix=Database::prefix();
       $clientIds = DB::table($prefix."hostingaddons")->join($prefix."hosting", $prefix."hosting.id", "=", $prefix."hostingaddons.hostingid")->join($prefix."clients", $prefix."clients.id", "=", $prefix."hosting.id")->where($prefix."clients.status", \App\Models\Client::STATUS_INACTIVE)->where($prefix."clients.overrideautoclose", "0")->whereIn($prefix."hostingaddons.status", array(\App\Models\Hosting::STATUS_ACTIVE, \App\Models\Hosting::STATUS_SUSPENDED))->pluck($prefix."hosting.userid");
       if (count($clientIds)) {
           DB::table($prefix."clients")->whereIn("id", $clientIds)->update(array("status" => \App\Models\Client::STATUS_ACTIVE));
       }
       //$this->output("active.product.addon")->write(count($clientIds));
       //$this->activeClients = array_merge($this->activeClients, $clientIds);
       //return $this;
   }
   protected static function activateClientsWithActiveHostingProduct()
   {
      $prefix=Database::prefix();  
      $clientIds = DB::table($prefix."hosting as tblhosting")->join($prefix."clients as tblclients", "tblclients.id", "=", "tblhosting.userid")->where("tblclients.status", \App\Models\Client::STATUS_INACTIVE)->where("tblclients.overrideautoclose", "0")->whereIn("tblhosting.domainstatus", array( \App\Models\Hosting::STATUS_ACTIVE,  \App\Models\Hosting::STATUS_SUSPENDED))->pluck("tblhosting.userid");
        if (count($clientIds)) {
            DB::table("tblclients")->whereIn("id", $clientIds)->update(array("status" => \App\Models\Client::STATUS_ACTIVE));
        }
        /* $this->output("active.product.service")->write(count($clientIds));
        $this->activeClients = array_merge($this->activeClients, $clientIds);
        return $this; */
   }
   protected static function activateClientsWithActiveBillableItems()
    {
      $prefix=Database::prefix(); 
      $ids =DB::table($prefix."billableitems as tblbillableitems")->join($prefix."clients as tblclients", "tblclients.id", "=", "tblbillableitems.userid")->where("tblclients.status", "=", "Inactive")->where("tblclients.overrideautoclose", "=", 0)->where("tblbillableitems.invoiceaction", "=", 4)->where("tblbillableitems.recurfor", ">", "tblbillableitems.invoicecount")->pluck("tblclients.id");
      DB::table("tblclients")->whereIn("id", $ids)->update(array("status" =>  \App\Models\Client::STATUS_ACTIVE));
        /* $this->output("active.billable.item")->write(count($ids));
        $this->activeClients = array_merge($this->activeClients, $ids);
        return $this; */
    }
    protected static function deactivateClientsWithoutLoginActivity()
    {
      $prefix=Database::prefix(); 
        $clientsModified = array();
        $query = "SELECT id,lastlogin FROM tblclients" . " WHERE status='Active'" . " AND overrideautoclose='0'" . " AND (" . "SELECT COUNT(id) FROM tblhosting" . " WHERE tblhosting.userid=tblclients.id" . " AND domainstatus IN ('Active','Suspended')" . ")=0";
        if (Cfg::get("AutoClientStatusChange") == "3") {
            $query .= sprintf(" AND lastlogin<='%s'", date("Y-m-d", mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"))));
        }

        $client=DB::table($prefix.'clients')
               ->where('status','Active')
               ->where('overrideautoclose',0)
               ->whereRaw("(SELECT COUNT(id) FROM ". $prefix."hosting WHERE ".$prefix."hosting.userid=". $prefix."clients.id AND domainstatus IN ('Active','Suspended'))=0")
               ->where('lastlogin','<=',date("Y-m-d", mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"))))
               ->select('id','lastlogin')
               ->get();
        //dd($client->toArray());
         foreach($client as $data){
            $userid = $data->id;
            $totalactivecount=DB::table($prefix.'clients')->selectRaw("(SELECT COUNT(*) FROM {$prefix}hosting WHERE userid={$prefix}clients.id AND domainstatus IN ('Active','Suspended'))
                                                                        +
                                                                     (SELECT COUNT(*) FROM {$prefix}hostingaddons WHERE hostingid IN (SELECT id FROM {$prefix}hosting WHERE userid={$prefix}clients.id) AND status IN ('Active','Suspended'))
                                                                     +
                                                                     (SELECT COUNT(*) FROM {$prefix}domains WHERE userid={$prefix}clients.id AND status IN ('Active'))
                                                                     AS activeservices")
                                                                     ->where('id',$userid)
                                                                     ->first();
            $totalN=$totalactivecount->activeservices??0;
            if($totalN == 0){
               $client=\App\Models\Client::find($userid);
               $client->status ='Inactive';
               $client->save();
               $clientsModified[] = $userid;
            }
         }

        
    }

}