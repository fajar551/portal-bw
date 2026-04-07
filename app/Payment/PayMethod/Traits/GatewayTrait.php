<?php

namespace App\Payment\PayMethod\Traits;

trait GatewayTrait
{
    public function loadGateway($gatewayName)
    {
        $gateway = new \App\Module\Gateway();
        if ($gateway->load($gatewayName)) {
            return $gateway;
        }
        return null;
    }
}

?>
