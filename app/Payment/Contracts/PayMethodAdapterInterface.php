<?php

namespace App\Payment\Contracts;

interface PayMethodAdapterInterface extends \App\User\Contracts\ContactAwareInterface, PayMethodTypeInterface, SensitiveDataInterface
{
    public function payMethod();
    public static function factoryPayMethod(\App\User\Contracts\UserInterface $client, \App\User\Contracts\ContactInterface $billingContact, $description);
    public function getDisplayName();
}

?>
