<?php

namespace App\Payment\PayMethod\Adapter;

class CreditCard extends CreditCardModel
{
    use \App\Payment\PayMethod\Traits\CreditCardDetailsTrait {
        getRawSensitiveData as ccGetRawSensitiveData;
    }
    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (!(new \App\Module\Gateway())->isLocalCreditCardStorageEnabled(!defined("ADMINAREA")  || !\App\Helpers\Application::isAdminAreaRequest())) {
                $model->setLastFour("");
                $model->setCardType("");
                $model->expiry_date = "";
            }
            $sensitiveData = $model->getSensitiveData();
            $name = $model->getSensitiveDataAttributeName();
            $model->{$name} = $sensitiveData;
        });
    }
    protected function getRawSensitiveData()
    {
        if (!(new \App\Helpers\Gateways)->isLocalCreditCardStorageEnabled(!defined("ADMINAREA") || !\App\Helpers\Application::isAdminAreaRequest())) {
            return null;
        }
        return $this->ccGetRawSensitiveData();
    }
    public function getDisplayName()
    {
        return implode("-", array($this->card_type, $this->last_four));
    }
}

?>
