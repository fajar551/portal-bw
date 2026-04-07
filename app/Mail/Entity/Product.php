<?php

namespace App\Mail\Entity;

use DB;

class Product extends \App\Helpers\Emailer
{
    protected function getEntitySpecificMergeData($serviceId, $extraParams)
    {
        $gatewaysarray = DB::table("tblpaymentgateways")->where("setting", "name")->orderBy("order")->pluck("value", "gateway");
        $email_merge_fields = array();
        if (is_array($extraParams) && array_key_exists("addon_id", $extraParams) && $extraParams["addon_id"]) {
            $email_merge_fields = $this->getAddonSpecificMergeData($extraParams["addon_id"], $gatewaysarray);
            $this->setRecipient($email_merge_fields["client_id"]);
        } else {
            $result = \App\Models\Hosting::selectRaw("tblhosting.*,tblproducts.name,tblproducts.description")->where("tblhosting.id", $serviceId)->join("tblproducts", "tblproducts.id","=","tblhosting.packageid")->first();
            if (!$result) {
                throw new \Exception("Invalid service id provided");
            }
            $data = $result->toArray();
            $id = $data["id"];
            $userid = $data["userid"];
            $currency = \App\Helpers\Format::getCurrency($userid);
            $orderid = $data["orderid"];
            $regdate = $data["regdate"];
            $nextduedate = $data["nextduedate"];
            $domain = $data["domain"];
            $server = $data["server"];
            $package = \App\Models\Product::getProductName($data["packageid"], $data["name"]);
            $productdescription = \App\Models\Product::getProductDescription($data["packageid"], $data["description"]);
            $packageid = $data["packageid"];
            $paymentmethod = $data["paymentmethod"];
            $paymentmethod = $gatewaysarray[$paymentmethod];
            $firstpaymentamount = $data["firstpaymentamount"];
            $recurringamount = $data["amount"];
            $billingcycle = $data["billingcycle"];
            $domainstatus = $data["domainstatus"];
            $username = $data["username"];
            $password = (new \App\Helpers\Pwd())->decrypt($data["password"]);
            $dedicatedip = $data["dedicatedip"];
            $assignedips = nl2br($data["assignedips"]);
            $dedi_ns1 = $data["ns1"];
            $dedi_ns2 = $data["ns2"];
            $subscriptionid = $data["subscriptionid"];
            $suspendreason = $data["suspendreason"];
            $canceltype = \App\Models\Cancelrequest::select("type")->where("relid", $data["id"])->orderBy("id", "DESC")->value("type");
            $regdate = (new \App\Helpers\Functions())->fromMySQLDate($regdate, 0, 1);
            $this->setRecipient($userid);
            if ($nextduedate == "0000-00-00" && ($billingcycle == "One Time" || $billingcycle == "Free Account")) {
                $nextduedate = "-";
            }
            if ($nextduedate != "-") {
                $nextduedate = (new \App\Helpers\Functions())->fromMySQLDate($nextduedate, 0, 1);
            }
            if ($domainstatus == "Suspended" && !$suspendreason) {
                $suspendreason = \Lang::get("suspendreasonoverdue");
            }
            $domainstatus = \Lang::get("clientarea" . strtolower(str_replace(" ", "", $domainstatus)));
            $canceltype = $canceltype ? \Lang::get("clientareacancellation" . strtolower(str_replace(" ", "", $canceltype))) : "";
            $servername = $serverip = $serverhostname = "";
            $ns1 = $ns1ip = $ns2 = $ns2ip = $ns3 = $ns3ip = $ns4 = $ns4ip = $ns5 = $ns5ip = "";
            if ($server) {
                $result3 = \App\Models\Server::find($server);
                $data3 = $result3->toArray();
                $servername = $data3["name"];
                $serverip = $data3["ipaddress"];
                $serverhostname = $data3["hostname"];
                $ns1 = $data3["nameserver1"];
                $ns1ip = $data3["nameserver1ip"];
                $ns2 = $data3["nameserver2"];
                $ns2ip = $data3["nameserver2ip"];
                $ns3 = $data3["nameserver3"];
                $ns3ip = $data3["nameserver3ip"];
                $ns4 = $data3["nameserver4"];
                $ns4ip = $data3["nameserver4ip"];
                $ns5 = $data3["nameserver5"];
                $ns5ip = $data3["nameserver5ip"];
            }
            $billingcycleforconfigoptions = strtolower($billingcycle);
            $billingcycleforconfigoptions = preg_replace("/[^a-z]/i", "", $billingcycleforconfigoptions);
            $langbillingcycle = $billingcycleforconfigoptions;
            $billingcycleforconfigoptions = str_replace("lly", "l", $billingcycleforconfigoptions);
            if ($billingcycleforconfigoptions == "free account") {
                $billingcycleforconfigoptions = "monthly";
            }
            $configoptions = array();
            $configoptionshtml = "";
            $result4 = \App\Models\Hostingconfigoption::selectRaw("tblproductconfigoptions.id, tblproductconfigoptions.optionname AS confoption, tblproductconfigoptions.optiontype AS conftype, tblproductconfigoptionssub.optionname, tblhostingconfigoptions.qty")
            ->join("tblproductconfigoptions", "tblproductconfigoptions.id", "=", "tblhostingconfigoptions.configid")
            ->join("tblproductconfigoptionssub", "tblproductconfigoptionssub.id", "=", "tblhostingconfigoptions.optionid")
            ->join("tblhosting", "tblhosting.id","=","tblhostingconfigoptions.relid")
            ->join("tblproductconfiglinks", "tblproductconfiglinks.gid","=","tblproductconfigoptions.gid")
            ->where("tblhostingconfigoptions.relid", (int) $serviceId)
            ->whereRaw("tblproductconfiglinks.pid=tblhosting.packageid")
            ->orderByRaw("tblproductconfigoptions.order,tblproductconfigoptions.id ASC")
            ->get();
            foreach ($result4->toArray() as $data) {
                $confoption = $data4["confoption"];
                $conftype = $data4["conftype"];
                if (strpos($confoption, "|")) {
                    $confoption = explode("|", $confoption);
                    $confoption = trim($confoption[1]);
                }
                $optionname = $data4["optionname"];
                $optionqty = $data4["qty"];
                if (strpos($optionname, "|")) {
                    $optionname = explode("|", $optionname);
                    $optionname = trim($optionname[1]);
                }
                if ($conftype == 3) {
                    if ($optionqty) {
                        $optionname = \Lang::get("yes");
                    } else {
                        $optionname = \Lang::get("no");
                    }
                } else {
                    if ($conftype == 4) {
                        $optionname = (string) $optionqty . " x " . $optionname;
                    }
                }
                $configoptions[] = array("id" => $data4["id"], "option" => $confoption, "type" => $conftype, "value" => $optionname, "qty" => $optionqty, "setup" => \App\Helpers\Format::formatCurrency($data4["setup"], $currency), "recurring" => \App\Helpers\Format::formatCurrency($data4["recurring"], $currency));
                $configoptionshtml .= (string) $confoption . ": " . $optionname . " " . \App\Helpers\Format::formatCurrency($data4["recurring"], $currency) . "<br>\n";
            }
            $email_merge_fields["service_order_id"] = $orderid;
            $email_merge_fields["service_id"] = $id;
            $email_merge_fields["service_reg_date"] = $regdate;
            $email_merge_fields["service_product_name"] = $package;
            $email_merge_fields["service_product_description"] = $productdescription;
            $email_merge_fields["service_config_options"] = $configoptions;
            $email_merge_fields["service_config_options_html"] = $configoptionshtml;
            $email_merge_fields["service_domain"] = $domain;
            $email_merge_fields["service_server_name"] = $servername;
            $email_merge_fields["service_server_hostname"] = $serverhostname;
            $email_merge_fields["service_server_ip"] = $serverip;
            $email_merge_fields["service_dedicated_ip"] = $dedicatedip;
            $email_merge_fields["service_assigned_ips"] = $assignedips;
            if ($dedi_ns1 != "") {
                $email_merge_fields["service_ns1"] = $dedi_ns1;
                $email_merge_fields["service_ns2"] = $dedi_ns2;
            } else {
                $email_merge_fields["service_ns1"] = $ns1;
                $email_merge_fields["service_ns2"] = $ns2;
                $email_merge_fields["service_ns3"] = $ns3;
                $email_merge_fields["service_ns4"] = $ns4;
                $email_merge_fields["service_ns5"] = $ns5;
            }
            $email_merge_fields["service_ns1_ip"] = $ns1ip;
            $email_merge_fields["service_ns2_ip"] = $ns2ip;
            $email_merge_fields["service_ns3_ip"] = $ns3ip;
            $email_merge_fields["service_ns4_ip"] = $ns4ip;
            $email_merge_fields["service_ns5_ip"] = $ns5ip;
            $email_merge_fields["service_payment_method"] = $paymentmethod;
            $email_merge_fields["service_first_payment_amount"] = \App\Helpers\Format::formatCurrency($firstpaymentamount);
            $email_merge_fields["service_recurring_amount"] = \App\Helpers\Format::formatCurrency($recurringamount);
            $email_merge_fields["service_billing_cycle"] = \Lang::get("orderpaymentterm" . $langbillingcycle);
            $email_merge_fields["service_next_due_date"] = $nextduedate;
            $email_merge_fields["service_status"] = $domainstatus;
            $email_merge_fields["service_username"] = $username;
            $email_merge_fields["service_password"] = $password;
            $email_merge_fields["service_subscription_id"] = $subscriptionid;
            $email_merge_fields["service_suspension_reason"] = $suspendreason;
            $email_merge_fields["service_cancellation_type"] = $canceltype;
            $customfields = \App\Helpers\Customfield::getCustomFields("product", $packageid, $serviceId, true, "");
            $email_merge_fields["service_custom_fields"] = array();
            foreach ($customfields as $customfield) {
                $customfieldname = preg_replace("/[^0-9a-z]/", "", strtolower($customfield["name"]));
                $email_merge_fields["service_custom_field_" . $customfieldname] = $customfield["value"];
                $email_merge_fields["service_custom_fields"][] = $customfield["value"];
                $email_merge_fields["service_custom_fields_by_name"][] = array("name" => $customfield["name"], "value" => $customfield["value"]);
            }
            if ($this->getExtra("addonemail")) {
                $addonID = $this->getExtra("addonid");
                $addonData = \App\Models\Hostingaddon::selectRaw("tblhostingaddons.*, tbladdons.name as definedName")->where("tblhostingaddons.id", $addonID)->join("tbladdons", "tblhostingaddons.addonid", "=", "tbladdons.id");
                $addonData = $addonData->toArray();
                $email_merge_fields["addon_reg_date"] = $addonData["regdate"];
                $email_merge_fields["addon_product"] = $email_merge_fields["service_product_name"];
                $email_merge_fields["addon_domain"] = $email_merge_fields["service_domain"];
                $email_merge_fields["addon_name"] = $addonData["name"] ? $addonData["name"] : $addonData["definedName"];
                $email_merge_fields["addon_setup_fee"] = $addonData["setupfee"];
                $email_merge_fields["addon_recurring_amount"] = $addonData["recurring"];
                $email_merge_fields["addon_billing_cycle"] = $addonData["billingcycle"];
                $email_merge_fields["addon_payment_method"] = $addonData["paymentmethod"];
                $email_merge_fields["addon_next_due_date"] = (new \App\Helpers\Functions())->fromMySQLDate($addonData["nextduedate"], 0, 1);
                $email_merge_fields["addon_status"] = $addonData["status"];
            }
            $moduleInterface = new \App\Module\Server();
            $moduleInterface->setServiceId($serviceId);
            $additionalEmailVariables = $moduleInterface->call("entity_specific_merge_data");
            if (is_array($additionalEmailVariables) && 0 < count($additionalEmailVariables)) {
                foreach ($additionalEmailVariables as $variableName => $variableValue) {
                    $email_merge_fields[$variableName] = $variableValue;
                }
            }
        }
        $this->massAssign($email_merge_fields);
    }
    protected function getAddonSpecificMergeData($addonId, array $gatewaysArray = array())
    {
        $addonData = \App\Models\Hostingaddon::with("productAddon", "service", "serverModel")->find($addonId);
        $addonName = $addonData->name;
        $addonDescription = "";
        if (!$addonName && $addonData->addonId) {
            $addonName = $addonData->productAddon->name;
        }
        if ($addonData->addonId) {
            $addonDescription = $addonData->productAddon->description;
        }
        $domain = $addonData->service->domain;
        $serverName = $serverIp = $serverHostname = "";
        $ns1 = $ns1Ip = $ns2 = $ns2Ip = $ns3 = $ns3Ip = $ns4 = $ns4Ip = $ns5 = $ns5Ip = "";
        if ($addonData->serverId && $addonData->serverModel) {
            $serverData = $addonData->serverModel;
            $serverName = $serverData->name;
            $serverIp = $serverData->ipAddress;
            $serverHostname = $serverData->hostname;
            $ns1 = $serverData->nameserverOne;
            $ns1Ip = $serverData->nameserverOneIpAddress;
            $ns2 = $serverData->nameserverTwo;
            $ns2Ip = $serverData->nameserverTwoIpAddress;
            $ns3 = $serverData->nameserverThree;
            $ns3Ip = $serverData->nameserverThreeIpAddress;
            $ns4 = $serverData->nameserverFour;
            $ns4Ip = $serverData->nameserverFourIpAddress;
            $ns5 = $serverData->nameserverFive;
            $ns5Ip = $serverData->nameserverFiveIpAddress;
        }
        $dedicatedIp = $addonData->serviceProperties->get("Dedicated IP");
        $assignedIps = $addonData->serviceProperties->get("Assigned IPs");
        $paymentMethod = $gatewaysArray[$addonData->paymentGateway];
        $firstPaymentAmount = $addonData->setupFee + $addonData->recurringFee;
        $recurringAmount = $addonData->recurringFee;
        $cycles = new \App\Helpers\Cycles();
        $billingCycle = $cycles->translate($addonData->billingCycle);
        $status = $addonData->status;
        $suspendReason = "";
        if ($status == "Suspended") {
            $suspendReason = \Lang::get("suspendreasonoverdue");
        }
        $status = \Lang::get("clientarea" . strtolower(str_replace(" ", "", $status)));
        $email_merge_fields = array();
        $email_merge_fields["service_order_id"] = $addonData->orderId;
        $email_merge_fields["service_id"] = $addonData->serviceId;
        $email_merge_fields["service_reg_date"] = (new \App\Helpers\Functions())->fromMySQLDate($addonData->registrationDate, 0, true);
        $email_merge_fields["service_product_name"] = $addonName;
        $email_merge_fields["service_product_description"] = $addonDescription;
        $email_merge_fields["service_config_options"] = array();
        $email_merge_fields["service_config_options_html"] = "";
        $email_merge_fields["service_domain"] = $domain;
        $email_merge_fields["service_server_name"] = $serverName;
        $email_merge_fields["service_server_hostname"] = $serverHostname;
        $email_merge_fields["service_server_ip"] = $serverIp;
        $email_merge_fields["service_dedicated_ip"] = $dedicatedIp;
        $email_merge_fields["service_assigned_ips"] = $assignedIps;
        $email_merge_fields["service_ns1"] = $ns1;
        $email_merge_fields["service_ns2"] = $ns2;
        $email_merge_fields["service_ns3"] = $ns3;
        $email_merge_fields["service_ns4"] = $ns4;
        $email_merge_fields["service_ns5"] = $ns5;
        $email_merge_fields["service_ns1_ip"] = $ns1Ip;
        $email_merge_fields["service_ns2_ip"] = $ns2Ip;
        $email_merge_fields["service_ns3_ip"] = $ns3Ip;
        $email_merge_fields["service_ns4_ip"] = $ns4Ip;
        $email_merge_fields["service_ns5_ip"] = $ns5Ip;
        $email_merge_fields["service_payment_method"] = $paymentMethod;
        $email_merge_fields["service_first_payment_amount"] = \App\Helpers\Format::formatCurrency($firstPaymentAmount);
        $email_merge_fields["service_recurring_amount"] = \App\Helpers\Format::formatCurrency($recurringAmount);
        $email_merge_fields["service_billing_cycle"] = $billingCycle;
        $email_merge_fields["service_next_due_date"] = (new \App\Helpers\Functions())->fromMySQLDate($addonData->nextDueDate, 0, true);
        $email_merge_fields["service_status"] = $status;
        $email_merge_fields["service_username"] = $addonData->serviceProperties->get("Username");
        $email_merge_fields["service_password"] = $addonData->serviceProperties->get("Password");
        $email_merge_fields["service_subscription_id"] = "";
        $email_merge_fields["service_suspension_reason"] = $suspendReason;
        $email_merge_fields["service_cancellation_type"] = "";
        $customFields = array();
        if ($addonData->addonId) {
            $customFields = \App\Helpers\Customfield::getCustomFields("addon", $addonData->addonId, $addonId, true, "");
        }
        $email_merge_fields["service_custom_fields"] = array();
        foreach ($customFields as $customField) {
            $customFieldName = preg_replace("/[^0-9a-z]/", "", strtolower($customField["name"]));
            $email_merge_fields["service_custom_field_" . $customFieldName] = $customField["value"];
            $email_merge_fields["service_custom_fields"][] = $customField["value"];
            $email_merge_fields["service_custom_fields_by_name"][] = array("name" => $customField["name"], "value" => $customField["value"]);
        }
        $email_merge_fields["client_id"] = $addonData->clientId;
        $moduleInterface = \App\Module\Server::factoryFromModel($addonData);
        $additionalEmailVariables = $moduleInterface->call("entity_specific_merge_data");
        if (is_array($additionalEmailVariables) && 0 < count($additionalEmailVariables)) {
            foreach ($additionalEmailVariables as $variableName => $variableValue) {
                $email_merge_fields[$variableName] = $variableValue;
            }
        }
        return $email_merge_fields;
    }
}

?>
