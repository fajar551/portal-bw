<?php

namespace App\Payment\Contracts;

interface PayMethodInterface extends \App\User\Contracts\ContactAwareInterface, PayMethodTypeInterface
{
    public function payment();
    public function isDefaultPayMethod();
    public function setAsDefaultPayMethod();
    public function getDescription();
    public function setDescription($value);
    public function getGateway();
    public function setGateway(\App\Module\Gateway $value);
    public function isUsingInactiveGateway();
    public function getPaymentDescription();
    public function save(array $options);
}

?>
