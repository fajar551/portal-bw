<?php

namespace App\Payment\Contracts;

interface CreditCardDetailsInterface
{
    public function getCardNumber();
    public function setCardNumber($value);
    public function getCardCvv();
    public function setCardCvv($value);
    public function getLastFour();
    public function setLastFour($value);
    public function getMaskedCardNumber();
    public function getExpiryDate();
    public function setExpiryDate(\App\Helpers\Carbon $value);
    public function getCardType();
    public function setCardType($value);
    public function getStartDate();
    public function setStartDate(\App\Helpers\Carbon $value);
    public function getIssueNumber();
    public function setIssueNumber($value);
}

?>
