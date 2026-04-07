<?php

namespace App\Payment\PayMethod\Traits;

trait PayMethodFactoryTrait
{
    public static function factoryPayMethod(\App\User\Contracts\UserInterface $client, \App\User\Contracts\ContactInterface $billingContact = NULL, $description = "")
    {
        $payment = new static();
        $payment->save();
        return $payment->newPayMethod($client, $billingContact, $description);
    }
    public function newPayMethod(\App\User\Contracts\UserInterface $client, \App\User\Contracts\ContactInterface $billingContact = NULL, $description = "")
    {
        $payMethod = new \App\Payment\PayMethod\Model();
        $payMethod->description = $description;
        $payMethod->order_preference = \App\Payment\PayMethod\Model::totalPayMethodsOnFile($client);
        if (!$billingContact) {
            $billingContact = $client->defaultBillingContact;
        }
        $payMethod->save();
        $payMethod->contact()->associate($billingContact);
        $payMethod->client()->associate($client);
        $payMethod->payment()->associate($this);
        $this->pay_method_id = $payMethod->id;
        $payMethod->push();
        return $payMethod;
    }
}

?>
