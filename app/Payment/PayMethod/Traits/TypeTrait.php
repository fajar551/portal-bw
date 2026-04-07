<?php

namespace App\Payment\PayMethod\Traits;

trait TypeTrait
{
    public function getType($instance = NULL)
    {
        if (!$instance) {
            $instance = $this;
        }
        $thisType = substr(strrchr(get_class($instance), "\\"), 1);
        $types = $this->getSupportedPayMethodTypes();
        if (in_array($thisType, $types)) {
            return $thisType;
        }
        foreach ($types as $type) {
            if ($thisType instanceof $type) {
                return $type;
            }
        }
        throw new \RuntimeException("Indeterminate type " . get_class($instance));
    }
    public function getTypeDescription($instance = NULL)
    {
        $type = $this->getType($instance);
        switch ($type) {
            case \App\Payment\Contracts\PayMethodTypeInterface::TYPE_BANK_ACCOUNT:
                $description = "Bank Account";
                break;
            case \App\Payment\Contracts\PayMethodTypeInterface::TYPE_REMOTE_BANK_ACCOUNT:
                $description = "Payment Account";
                break;
            case \App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_LOCAL:
            default:
                $description = "Credit Card";
        }
        return $description;
    }
    public function isManageable()
    {
        $type = $this->getType();
        if ($type == \App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_REMOTE_UNMANAGED) {
            return false;
        }
        return true;
    }
    public function isLocalCreditCard()
    {
        return $this->getType() == \App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_LOCAL;
    }
    public function isRemoteCreditCard()
    {
        return $this->getType() == \App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_REMOTE_MANAGED;
    }
    public function isCreditCard()
    {
        return $this->isLocalCreditCard() || $this->isRemoteCreditCard();
    }
    public function isBankAccount()
    {
        return $this->getType() == \App\Payment\Contracts\PayMethodTypeInterface::TYPE_BANK_ACCOUNT;
    }
    public function isRemoteBankAccount()
    {
        return $this->getType() == \App\Payment\Contracts\PayMethodTypeInterface::TYPE_REMOTE_BANK_ACCOUNT;
    }
    public function getSupportedPayMethodTypes()
    {
        return array(\App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_LOCAL, \App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_REMOTE_MANAGED, \App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_REMOTE_UNMANAGED, \App\Payment\Contracts\PayMethodTypeInterface::TYPE_BANK_ACCOUNT, \App\Payment\Contracts\PayMethodTypeInterface::TYPE_REMOTE_BANK_ACCOUNT);
    }
}

?>
