<?php

namespace App\Payment\PayMethod\Adapter;

class RemoteBankAccount extends BankAccountModel implements \App\Payment\Contracts\RemoteTokenDetailsInterface
{
    use \App\Payment\PayMethod\Traits\RemoteBankAccountDetailsTrait;
    public function getName()
    {
        return $this->bank_name;
    }
    public function setName($value)
    {
        $this->bank_name = $value;
        return $this;
    }
    public function getRemoteToken()
    {
        $remoteToken = $this->getSensitiveProperty("remoteToken");
        if (is_array($remoteToken)) {
            $remoteToken = json_encode($remoteToken);
        }
        if (!is_string($remoteToken)) {
            $remoteToken = (string) $remoteToken;
        }
        return $remoteToken;
    }
    public function setRemoteToken($value)
    {
        $this->setSensitiveProperty("remoteToken", $value);
        return $this;
    }
    protected function getGatewayParamsForRemoteCall(\App\Module\Gateway $gateway)
    {
        $params = $gateway->loadSettings();
        if (!$params) {
            throw new \App\Exceptions\Module\InvalidConfiguration("No Gateway Settings Found");
        }
        $params["companyname"] = \App\Helpers\Cfg::getValue("CompanyName");
        $params["systemurl"] = config('app.url');
        $params["langpaynow"] = \Lang::get("invoicespaynow");
        return $params;
    }
    protected function getBillingContactParamsForRemoteCall(\App\User\Contracts\UserInterface $client, \App\User\Contracts\ContactInterface $contact)
    {
        $legacyClient = new \App\Helpers\ClientClass($client->id);
        $clientsDetails = $legacyClient->getDetails($contact->id);
        $clientsDetails["state"] = $clientsDetails["statecode"];
        return array("clientdetails" => $clientsDetails);
    }
    protected function storeRemote($action)
    {
        $payMethod = $this->payMethod;
        $gateway = $payMethod->getGateway();
        if (!$gateway) {
            throw new \RuntimeException("No valid gateway for PayMethod ID " . $this->payMethod->id);
        }
        $gatewayParams = $this->getGatewayParamsForRemoteCall($gateway);
        $billingParams = $this->getBillingContactParamsForRemoteCall($payMethod->client, $payMethod->contact);
        $params = array_merge($gatewayParams, $billingParams);
        $params["action"] = $action;
        $params["payMethod"] = $payMethod;
        $params["gatewayid"] = $this->getRemoteToken();
        $params["remoteStorageToken"] = $this->getRemoteToken();
        $gatewayCallResult = $gateway->call("storeremote", $params);
        if (is_array($gatewayCallResult["rawdata"])) {
            $debugData = array_merge(array("UserID" => $params["clientdetails"]["userid"]), $gatewayCallResult["rawdata"]);
        } else {
            $debugData = "UserID => " . $params["clientdetails"]["userid"] . "\n" . $gatewayCallResult["rawdata"];
        }
        if ($gatewayCallResult["status"] === "success") {
            \App\Helpers\Gateway::logTransaction($gateway->getLoadedModule(), $debugData, "Remote Storage Success");
            if (is_array($gatewayCallResult) && array_key_exists("gatewayid", $gatewayCallResult) && !array_key_exists("remoteToken", $gatewayCallResult)) {
                $gatewayCallResult["remoteToken"] = $gatewayCallResult["gatewayid"];
            }
            if (isset($gatewayCallResult["remoteToken"])) {
                $this->setRemoteToken($gatewayCallResult["remoteToken"]);
            } else {
                if ($action === "create") {
                    \App\Helpers\Gateway::logTransaction($gateway->getLoadedModule(), $debugData, "Remote Storage \"create\" action did NOT provide token");
                    throw new \RuntimeException("Remote Storage Failed");
                }
            }
            $this->save();
            return $this;
        }
        \App\Helpers\Gateway::logTransaction($gateway->getLoadedModule(), $debugData, "Remote Storage Failed");
        throw new \RuntimeException("Remote Storage Failed");
    }
    public function createRemote()
    {
        return $this->storeRemote("create");
    }
    public function updateRemote()
    {
        return $this->storeRemote("update");
    }
    public function deleteRemote()
    {
        return $this->storeRemote("delete");
    }
    public function validateRequiredValuesPreSave()
    {
        return $this;
    }
}

?>
