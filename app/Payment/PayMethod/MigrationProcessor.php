<?php

namespace App\Payment\PayMethod;

use DB;

class MigrationProcessor
{
    private function getEncryptedDataFields()
    {
        return array("cardtype", "cardlastfour", "cardnum", "startdate", "expdate", "issuenumber", "bankcode", "bankacct");
    }
    private function getLegacyClientPaymentData(\App\User\Client $client)
    {
        $ccHash = md5(\Config::get("portal")['hash']['cc_encryption_hash'] . $client->id);
        $columns = array_map(function ($fieldName) use($ccHash) {
            return DB::connection()->raw(sprintf("AES_DECRYPT(`%s`, '%s') as `%s`", $fieldName, $ccHash, $fieldName));
        }, $this->getEncryptedDataFields());
        $columns = array_merge($columns, array("bankname", "banktype", "cardtype as cardtyperaw", "cardlastfour as cardlastfourraw"));
        $legacyPaymentData = (array) DB::table("tblclients")->where("id", $client->id)->select($columns)->first();
        if (empty($legacyPaymentData["cardtype"]) && !empty($legacyPaymentData["cardtyperaw"])) {
            $legacyPaymentData["cardtype"] = $legacyPaymentData["cardtyperaw"];
        }
        if (empty($legacyPaymentData["cardlastfour"]) && !empty($legacyPaymentData["cardlastfourraw"])) {
            $legacyPaymentData["cardlastfour"] = $legacyPaymentData["cardlastfourraw"];
        }
        unset($legacyPaymentData["cardtyperaw"]);
        unset($legacyPaymentData["cardlastfourraw"]);
        return $legacyPaymentData;
    }
    private function getBillingContact(\App\User\Client $client)
    {
        if ($client->billingContact) {
            return $client->billingContact;
        }
        return $client;
    }
    private function migrateLocalCreditCardDetails(\App\User\Client $client, array $paymentData)
    {
        $payMethod = Adapter\CreditCard::factoryPayMethod($client, $this->getBillingContact($client), "");
        $payment = $payMethod->payment;
        $payment->setCardNumber($paymentData["cardnum"]);
        if ($paymentData["cardtype"]) {
            $payment->setCardType($paymentData["cardtype"]);
        }
        if ($paymentData["startdate"]) {
            $payment->setStartDate(\App\Helpers\Carbon::createFromCcInput($paymentData["startdate"]));
        }
        if ($paymentData["expdate"]) {
            $payment->setExpiryDate(\App\Helpers\Carbon::createFromCcInput($paymentData["expdate"]));
        }
        if ($paymentData["issuenumber"]) {
            $payment->setIssueNumber($paymentData["issuenumber"]);
        }
        $payment->validateRequiredValuesPreSave()->save();
    }
    private function findGatewayForClient(\App\User\Client $client, callable $callback)
    {
        $gatewayInterface = new \App\Module\Gateway();
        $activeCcGateways = DB::table("tblpaymentgateways")->where("setting", "type")->where("value", "CC")->pluck("gateway");
        $tokenisedPaymentInvoices = DB::table("tblinvoices")->where("tblinvoices.userid", $client->id)->whereIn("paymentmethod", $activeCcGateways)->orderBy("id", "DESC")->distinct()->pluck("paymentmethod");
        $gateways = array_unique(array_merge($tokenisedPaymentInvoices, $activeCcGateways));
        if ($client->defaultPaymentGateway && $gatewayInterface->isActiveGateway($client->defaultPaymentGateway) && !in_array($client->defaultPaymentGateway, $gateways)) {
            $gateways[] = $client->defaultPaymentGateway;
        }
        foreach ($gateways as $gatewayName) {
            if ($gatewayInterface->load($gatewayName) && $callback($gatewayInterface)) {
                return $gatewayInterface;
            }
        }
        return null;
    }
    private function migrateRemoteCreditCardDetails(\App\User\Client $client, array $paymentData)
    {
        $remoteCreditCardGateway = $this->findGatewayForClient($client, function (\App\Module\Gateway $gateway) {
            return $gateway->isTokenised();
        });
        if (!$remoteCreditCardGateway) {
            throw new \Exception("Client's remote credit card gateway could not be determined. Client ID: " . $client->id);
        }
        $payMethod = Adapter\RemoteCreditCard::factoryPayMethod($client, $this->getBillingContact($client), "");
        $payMethod->setGateway($remoteCreditCardGateway)->save();
        $payment = $payMethod->payment;
        $payment->setRemoteToken($client->paymentGatewayToken);
        if ($paymentData["cardlastfour"]) {
            $payment->setLastFour($paymentData["cardlastfour"]);
        }
        if ($paymentData["cardtype"]) {
            $payment->setCardType($paymentData["cardtype"]);
        } else {
            $payment->setCardType("Card");
        }
        if ($paymentData["startdate"]) {
            $payment->setStartDate(\App\Helpers\Carbon::createFromCcInput($paymentData["startdate"]));
        }
        if ($paymentData["expdate"]) {
            $payment->setExpiryDate(\App\Helpers\Carbon::createFromCcInput($paymentData["expdate"]));
        }
        if ($paymentData["issuenumber"]) {
            $payment->setIssueNumber($paymentData["issuenumber"]);
        }
        $payment->validateRequiredValuesPreSave()->save();
    }
    private function migrateBankDetails(\App\User\Client $client, array $paymentData)
    {
        $payMethod = Adapter\BankAccount::factoryPayMethod($client, $this->getBillingContact($client), "Default Bank Account");
        $payment = $payMethod->payment;
        $payment->setAccountType($paymentData["banktype"])->setAccountHolderName($client->firstName . " " . $client->lastName)->setBankName($paymentData["bankname"])->setRoutingNumber($paymentData["bankcode"])->setAccountNumber($paymentData["bankacct"])->validateRequiredValuesPreSave()->save();
    }
    private function migrateNonCardPaymentToken(\App\User\Client $client)
    {
        $remoteNonCardGateway = $this->findGatewayForClient($client, function (\App\Module\Gateway $gateway) {
            return $gateway->getWorkflowType() === \App\Module\Gateway::WORKFLOW_NOLOCALCARDINPUT;
        });
        if (!$remoteNonCardGateway) {
            throw new \Exception("Client's remote non-card gateway could not be determined. Client ID: " . $client->id);
        }
        $payMethod = Adapter\RemoteBankAccount::factoryPayMethod($client, $this->getBillingContact($client), $remoteNonCardGateway->getDisplayName());
        $payMethod->setGateway($remoteNonCardGateway)->save();
        $payment = $payMethod->payment;
        $payment->setRemoteToken($client->paymentGatewayToken)->setName($remoteNonCardGateway->getDisplayName())->validateRequiredValuesPreSave()->save();
    }
    public function migrateForClient(\App\User\Client $client)
    {
        $legacyPaymentData = $this->getLegacyClientPaymentData($client);
        if ($client->needsCardDetailsMigrated()) {
            if ($legacyPaymentData["cardnum"] && preg_match("/^[\\d]+\$/", $legacyPaymentData["cardnum"])) {
                $this->migrateLocalCreditCardDetails($client, $legacyPaymentData);
            } else {
                $this->migrateRemoteCreditCardDetails($client, $legacyPaymentData);
                $client->markPaymentTokenMigrated();
            }
            $client->markCardDetailsAsMigrated();
        }
        if ($client->needsBankDetailsMigrated()) {
            $this->migrateBankDetails($client, $legacyPaymentData);
            $client->markBankDetailsAsMigrated();
        }
        if ($client->needsNonCardPaymentTokenMigrated()) {
            $this->migrateNonCardPaymentToken($client);
            $client->markPaymentTokenMigrated();
        }
    }
}

?>
