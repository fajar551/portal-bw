<?php

namespace App\Payment\Contracts;

interface BankAccountDetailsInterface
{
    public function getRoutingNumber();
    public function setRoutingNumber($value);
    public function getAccountNumber();
    public function setAccountNumber($value);
    public function getBankName();
    public function setBankName($value);
    public function getAccountType();
    public function setAccountType($value);
    public function getAccountHolderName();
    public function setAccountHolderName($value);
}

?>
