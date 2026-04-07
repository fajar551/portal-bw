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

class DomainStatusSync{

    public static function run(){
        if(! \App\Helpers\Cfg::getValue('DomainSyncEnabled')){
            LogActivity::Save('Domain Sync Cron: Disabled. Run Aborted');
        }else{
            $syncCount = 0;
            try {
                $cronreport = "Domain Synchronisation Cron Report for " . date("d-m-Y H:i:s") . "<br />\n<br />\n";
                $registrarConfiguration = array();
                $curlErrorRegistrars = array();
                $cronreport .= "Active Domain Syncs<br />\n";

                $totalunsynced=\App\Models\Domain::where('registrar','!=','')
                                ->where('status','Active')
                                ->where('synced',0)
                                ->count();
                if (!$totalunsynced) {
                \App\Models\Domain::updated(['synced' => 0 ]);
                }
                $result =\App\Models\Domain::select('id','domain','expirydate','nextduedate','registrar','status')
                                        ->where('registrar','!=','')
                                        ->where('status','Active')
                                        ->where('synced',0)
                                        ->orderBy('status','DESC')
                                        ->orderBy('id','ASC')
                                        ->limit(50)
                                        ->get();
                foreach($result->toArray() as $data){
                    $domainid = $data["id"];
                    $domain = $data["domain"];
                    $registrar = $data["registrar"];
                    $expirydate = $data["expirydate"];
                    $nextduedate = $data["nextduedate"];
                    $status = $data["status"];
                    $domainparts = explode(".", $domain, 2);
                    $params['registrar'] = $registrar;
                    $params["domainid"] = $domainid;
                    $params["domain"] = $domain;
                    $params["domainname"] = $domain;
                    list($params["sld"], $params["tld"]) = $domainparts;
                    $params["registrar"] = $registrar;
                    $params["status"] = $status;
                    $registrar=new Registrar();
                    $curlErrorRegistrars[]=$registrar;
                    $response =$registrar->RegGetRegistrantSync($params);
                    if ($response["active"] && $status != "Active") {
                        $updateqry["status"] = "Active";
                        $synceditems[] = "Status Changed to Active";
                    }
                    if ($response["cancelled"] && $status == "Active") {
                        $updateqry["status"] = "Cancelled";
                        $synceditems[] = "Status Changed to Cancelled";
                    }
                    if ($response["expirydate"] && $expirydate != $response["expirydate"]) {
                        $updateqry["expirydate"] = $response["expirydate"];
                        $updateqry["reminders"] = "";
                        $synceditems[] = "Expiry Date updated to " . (new \App\Helpers\Functions())->fromMySQLDate($response["expirydate"]);
                    }
                    if (array_key_exists("transferredAway", $response) && $response["transferredAway"] == true && $status != "Transferred Away") {
                        $updateqry["status"] = "Transferred Away";
                        $synceditems[] = "Status Changed to Transferred Away";
                    }

                    if(\App\Helpers\Cfg::getValue('DomainSyncNextDueDate') &&  $response["expirydate"] ){
                        $newexpirydate = $response["expirydate"];
                        $syncDueDateDays = \App\Helpers\Cfg::getValue("DomainSyncNextDueDateDays");
                        if($syncDueDateDays){
                            $newexpirydate = explode("-", $newexpirydate);
                            $newexpirydate = date("Y-m-d", mktime(0, 0, 0, $newexpirydate[1], $newexpirydate[2] - $syncDueDateDays, $newexpirydate[0]));
                        }
                        if ($newexpirydate != $nextduedate) {
                            $updateqry["nextduedate"] = $newexpirydate;
                            $updateqry["nextinvoicedate"] = $newexpirydate;
                            $synceditems[] = "Next Due Date updated to " .(new \App\Helpers\Functions())->fromMySQLDate($newexpirydate);
                        }
                    }

                    
                    if(\App\Helpers\Cfg::getValue('DomainSyncNotifyOnly')){
                        $update=\App\Models\Domain::find($domainid);
                        $update->synced=1;
                        $update->save();
                    }
                    $syncCount++;
                    $cronreport .= " - " . $domain . ": ";
                    if(!count($response)) {
                        if (in_array($registrar, $curlErrorRegistrars)) {
                            $cronreport .= "Sync Skipped Due to cURL Error";
                        } else {
                            $cronreport .= "Sync Not Supported by Registrar Module";
                        }
                    }else{
                        if ($response["error"] && strtolower(substr($response["error"], 0, 4)) == "curl") {
                            if (!in_array($registrar, $curlErrorRegistrars)) {
                                $curlErrorRegistrars[] = $registrar;
                            }
                            $cronreport .= "Error: " . $response["error"];
                        } else {
                            //note if (!function_exists($registrar . "_TransfersSync") && $status == "Pending Transfer" && $response["active"]) {
                            if ($status == "Pending Transfer" && $response["active"]) {
                                \App\Helpers\Functions::sendMessage("Domain Transfer Completed", $domainid);
                            }
                            $suffix = "In Sync";
                            if (count($synceditems) && \App\Helpers\Cfg::getValue("DomainSyncNotifyOnly")) {
                                $suffix = "Out of Sync " . implode(", ", $synceditems);
                            } else {
                                if (count($synceditems)) {
                                    $suffix = implode(", ", $synceditems);
                                }
                            }
                            $cronreport .= $suffix;
                        }
                    }
                }
                LogActivity::Save('Domain Sync Cron: Completed');
            } catch (\Exception $e) {
                LogActivity::Save("Domain Sync Cron Error: " . $e->getMessage());
                //$this->output("synced")->write($syncCount);
            }
        }
    }

    public function getFrequencyMinutes()
    {
        $frequency = (int) \App\Helpers\Cfg::getValue("DomainStatusSyncFrequency") * 60;
        if (!$frequency || $frequency < 0) {
            $frequency = $this->defaultFrequency;
        }
        return $frequency;
    }

}