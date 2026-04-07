<?php
namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;
use App\Module\Registrar;

class DomainTransferSync{

   public static function run(){
      global $_LANG;
         if(! \App\Helpers\Cfg::get('DomainSyncEnabled')){
            LogActivity::Save("Domain Transfer Status Cron: Disabled. Run Aborted.");
         }else{
            $syncCount = 0;
            try {
               
               $cronreport = "Domain Transfer Status Checks for " . date("d-m-Y H:i:s") . "<br />\n<br />\n";
               $registrarConfiguration =  array();
               $curlErrorRegistrars = array();
               $transfersreport = "";
                  $domain=\App\Models\Domain::select("id", "domain", "registrar", "registrationperiod", "status", "dnsmanagement", "emailforwarding", "idprotection","expirydate")
                                             ->where('registrar','!=','')
                                             ->where('status','Pending Transfer')
                                             ->orderBy('id')
                                             ->get();
                  foreach($domain->toArray() as $data ){
                     
                     $domainid = $data["id"];
                     $domain = $data["domain"];
                     $registrar = $data["registrar"];
                     $regperiod = $data["registrationperiod"];
                     $expirydate = $data["expirydate"];
                     $status = $data["status"];
                     $domainparts = explode(".",$domain, 2);
                     //$params = is_array($registrarConfiguration[$registrar]) ? $registrarConfiguration[$registrar] : $registrarConfiguration[$registrar];
                     $params=array();
                     $params["domainid"] = $domainid;
                     $params["domain"] = $domain;
                     $params["domainname"] = $domain;
                      list($params["sld"], $params["tld"]) = $domainparts;
                     $params["registrar"] = $registrar;
                     $params["regperiod"] = $regperiod;
                     $params["status"] = $status;
                     $params["dnsmanagement"] = $data["dnsmanagement"];
                     $params["emailforwarding"] = $data["emailforwarding"];
                     $params["idprotection"] = $data["idprotection"];
                     $updateqry=array();
                     $registrar=new Registrar();
                     $response =$registrar->RegGetTransfersSync($params);
                     if (!$response["error"]) {
                        if ($response["active"] || $response["completed"]) {
                           $transfersreport .= "Transfer Completed";
                           $updateqry["status"] = "Active";
                           if (!$response["expirydate"]) {
                              $response = $registrar->RegGetRegistrantSync($params);
                           }
                           if ($response["expirydate"]) {
                              $updateqry["expirydate"] = $response["expirydate"];
                              $updateqry["reminders"] = "";
                              $expirydate = $updateqry["expirydate"];
                              $transfersreport .= " - In Sync";
                          }

                          if(\App\Helpers\Cfg::get('DomainSyncNextDueDate') && $response["expirydate"] ){
                              $syncDueDateDays= \App\Helpers\Cfg::get('DomainSyncNextDueDate');
                              $newexpirydate = $response["expirydate"];
                              $expirydate = $updateqry["expirydate"];
                              if($syncDueDateDays = \App\Helpers\Cfg::get('DomainSyncNextDueDateDays')){
                                 $newexpirydate = explode("-", $newexpirydate);
                                 $newexpirydate = date("Y-m-d", mktime(0, 0, 0, $newexpirydate[1], $newexpirydate[2] - $syncDueDateDays, $newexpirydate[0]));
                              }
                              $updateqry["nextduedate"] = $newexpirydate;
                              $updateqry["nextinvoicedate"] = $newexpirydate;
                          }

                        }else{
                           if ($response["failed"]) {
                              $transfersreport .= "Transfer Failed";
                              $updateqry["status"] = "Cancelled";
                              $failurereason = $response["reason"];
                              if (!$failurereason) {
                                    $failurereason = $_LANG["domaintrffailreasonunavailable"];
                              }
                              \App\Helpers\Functions::sendMessage("Domain Transfer Failed", $domainid, array("domain_transfer_failure_reason" => $failurereason));
                           } else {
                              $transfersreport .= "Transfer Still In Progress";
                           }
                        }
                        
                        if(! \App\Helpers\Cfg::getDomainSyncNotifyOnly('') && count($updateqry)){
                           $update=\App\Models\Domain::find($domainid);
                           foreach( $updateqry as $key=>$val){
                              $update->{$key} =$val;
                           }
                           $update->save();
                           if ($updateqry["status"] == "Active") {
                              \App\Helpers\Functions::sendMessage("Domain Transfer Completed", $domainid);
                              \App\Helpers\Hooks::run_hook("DomainTransferCompleted", array("domainId" => $domainid, "domain" => $domain, "registrationPeriod" => $regperiod, "expiryDate" => $expirydate, "registrar" => $registrar));
                           }else{
                              if ($updateqry["status"] == "Cancelled") {
                                 \App\Helpers\Hooks::run_hook("DomainTransferFailed", array("domainId" => $domainid, "domain" => $domain, "registrationPeriod" => $regperiod, "expiryDate" => $expirydate, "registrar" => $registrar));
                             }
                           }
                        }
                     }else{
                        if ($response["error"] && strtolower(substr($response["error"], 0, 4)) == "curl") {
                           $curlErrorRegistrars[] = $registrar;
                        }else{
                           if ($response["error"]) {
                              $transfersreport .= "Error: " . $response["error"];
                          }
                        }
                     }
                     $transfersreport .= "<br />\n";

                  }
                  if ($transfersreport) {
                     $cronreport .= $transfersreport . "<br />\n";
                     LogActivity::Save("Domain Transfer Status Cron: Completed");
                     \App\Helpers\Functions::sendAdminNotification("system", "WHMCS Domain Transfer Status Cron Report", $cronreport);
                 }
                 //$this->output("synced")->write($syncCount);

            } catch (\Exception $e) {
               LogActivity::Save("Domain Transfer Status Cron Error: " . $e->getMessage());
            }
         }
   }

   public static function getFrequencyMinutes()
    {
        $frequency = (int) \App\Helpers\Cfg::get("DomainTransferStatusCheckFrequency") * 60;
        if (!$frequency || $frequency < 0) {
            //$frequency = $this->defaultFrequency;
        }
        return $frequency;
    }

}