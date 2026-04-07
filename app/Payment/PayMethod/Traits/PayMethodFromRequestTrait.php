<?php

namespace App\Payment\PayMethod\Traits;

use Illuminate\Http\Request;

trait PayMethodFromRequestTrait
{
    private static function getClient(Request $request)
    {
        $clientId = (int) $request->input("userId");
        $client = \App\Models\Client::find($clientId);
        if (!$client) {
            throw new \RuntimeException("Missing client data");
        }
        return $client;
    }
    public static function getBillingContact(Request $request, \App\Models\Client $client)
    {
        $billingContactId = $request->input("billingContactId");
        if ($billingContactId === "client") {
            $billingContact = $client;
        } else {
            $billingContact = $client->contacts()->where("id", $billingContactId)->first();
        }
        if (!$billingContact) {
            throw new \RuntimeException("Invalid billing contact id");
        }
        return $billingContact;
    }
    public static function factoryFromRequest(Request $request)
    {
        $post = $request;
        $client = self::getClient($request);
        $billingContact = self::getBillingContact($request, $client);
        $description = $post->input("description", "");
        $type = $post->input("payMethodType");
        if (in_array($type, array(\App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_LOCAL, \App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_REMOTE_MANAGED))) {
            $payment = self::getCardPayment($request, $client, $billingContact, $description);
        } else {
            if (in_array($type, array(\App\Payment\Contracts\PayMethodTypeInterface::TYPE_BANK_ACCOUNT, \App\Payment\Contracts\PayMethodTypeInterface::TYPE_REMOTE_BANK_ACCOUNT))) {
                $payment = self::getBankPayment($request, $client, $billingContact, $description);
            } else {
                throw new \RuntimeException("Invalid pay method type");
            }
        }
        return $payment;
    }
    private static function getBankPayment(Request $request, \App\User\Contracts\UserInterface $client, \App\User\Contracts\ContactInterface $billingContact, $description = "")
    {
        $post = $request;
        $gateway = null;
        $existingPayMethod = $post->input("payMethodId", 0);
        if ($existingPayMethod) {
            $payMethod = \App\Payment\PayMethod\Model::find($existingPayMethod);
            if (!$payMethod || $payMethod->client->id !== $client->id) {
                throw new \RuntimeException("Pay method ID is not associated with client ID");
            }
            $gateway = $payMethod->getGateway();
        } else {
            $storage = $post->input("storage", $post->input("storageGateway", "local"));
            $class = "App\\Payment\\PayMethod\\Adapter\\BankAccount";
            if ($storage === "local") {
                $resolver = new \App\Helpers\Gateways();
                if (!$resolver->isLocalBankAccountGatewayAvailable()) {
                    throw new \RuntimeException("No compatible gateways are active.");
                }
            } else {
                $gateways = (new \App\Module\Gateway())->getAvailableGatewayInstances(true);
                if (array_key_exists($storage, $gateways)) {
                    $gateway = $gateways[$storage];
                    $class = "App\\Payment\\PayMethod\\Adapter\\RemoteBankAccount";
                } else {
                    throw new \RuntimeException("Selected gateway is unavailable.");
                }
            }
            $payMethod = $class::factoryPayMethod($client, $billingContact, $description);
        }
        if ($gateway) {
            $payMethod->setGateway($gateway);
            $payMethod->save();
        }
        $payment = $payMethod->payment;
        if ($payMethod->getType() === \App\Payment\Contracts\PayMethodTypeInterface::TYPE_BANK_ACCOUNT) {
            $payment->setAccountType($post->input("bankaccttype"));
            $payment->setBankName($post->input("bankname"));
            $payment->setAccountHolderName($post->input("bankacctholdername"));
            $payment->setRoutingNumber($post->input("bankroutingnum"));
            $payment->setAccountNumber($post->input("bankacctnum"));
        }
        return $payment;
    }
    private static function getCardPayment(Request $request, \App\User\Contracts\UserInterface $client, \App\User\Contracts\ContactInterface $billingContact, $description = "")
    {
        $post = $request;
        $gateway = null;
        $existingPayMethod = $post->input("payMethodId", 0);
        if ($existingPayMethod) {
            $payMethod = \App\Payment\PayMethod\Model::find($existingPayMethod);
            if (!$payMethod || $payMethod->client->id !== $client->id) {
                throw new \RuntimeException("Pay method ID is not associated with client ID");
            }
            $gateway = $payMethod->getGateway();
        } else {
            $storage = $post->input("storage", $post->input("storageGateway", "local"));
            $class = "App\\Payment\\PayMethod\\Adapter\\CreditCard";
            if ($storage === "local") {
                $resolver = new \App\Module\Gateway();
                if (!$resolver->isLocalCreditCardStorageEnabled(false)) {
                    throw new \RuntimeException("No compatible gateways are active.");
                }
            } else {
                $gateways = (new \App\Module\Gateway())->getAvailableGatewayInstances(true);
                if (array_key_exists($storage, $gateways)) {
                    $gateway = $gateways[$storage];
                    $class = "App\\Payment\\PayMethod\\Adapter\\RemoteCreditCard";
                } else {
                    throw new \RuntimeException("Selected gateway is unavailable.");
                }
            }
            $payMethod = $class::factoryPayMethod($client, $billingContact, $description);
        }
        if ($gateway) {
            $payMethod->setGateway($gateway);
            $payMethod->save();
        }
        $expiry = null;
        if ($post->has("ccexpirydate")) {
            try {
                $expiry = \App\Helpers\Carbon::createFromCcInput($post->input("ccexpirydate"));
            } catch (\Exception $e) {
            }
        }
        $payment = $payMethod->payment;
        if (!$existingPayMethod) {
            $cardNumber = $post->input("ccnumber", "");
            $cardCvv = $post->input("cardcvv", "");
            $payment->setCardNumber($cardNumber);
            $payment->setCardCvv($cardCvv);
            if (!$expiry) {
                $expiry = \App\Helpers\Carbon::fromCreditCard($post->input("ccexpirymonth", "01") . "/" . $post->input("ccexpiryyear", "28"));
            }
        } else {
            if (!$expiry) {
                $defaultMonth = $payment->getExpiryDate() ? $payment->getExpiryDate()->format("m") : "01";
                $defaultYear = $payment->getExpiryDate() ? $payment->getExpiryDate()->format("m") : "28";
                $expiry = \App\Helpers\Carbon::fromCreditCard($post->input("ccexpirymonth", $defaultMonth) . $post->input("ccexpiryyear", $defaultYear));
            }
        }
        if ($expiry) {
            $payment->setExpiryDate($expiry);
        }
        $startDate = null;
        $ccStartMonth = $post->input("ccstartmonth");
        $ccStartYear = $post->input("ccstartyear");
        $ccStartDate = $post->input("ccstartdate");
        try {
            if ($ccStartDate) {
                $startDate = \App\Helpers\Carbon::createFromCcInput($ccStartDate);
            } else {
                if ($ccStartMonth && $ccStartYear) {
                    $startDate = \App\Helpers\Carbon::createFromCcInput($ccStartMonth . $ccStartYear);
                }
            }
        } catch (\Exception $e) {
        }
        if ($startDate) {
            $payment->setStartDate($startDate);
        }
        $issueNumber = $post->input("ccissuenum");
        if ($issueNumber) {
            $payment->setIssueNumber($issueNumber);
        }
        return $payment;
    }
}

?>
