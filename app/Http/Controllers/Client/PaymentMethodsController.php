<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentMethodsController extends Controller
{
    //
    public function remoteInput(Request $request)
    {
        $gatewayModule = $request->input("gateway");
        $invoiceId = $request->input("invoice_id");
        if (!$gatewayModule || !$invoiceId) {
            return response()->json(array("warning" => \Lang::get("client.invoiceserror")));
        }
        $gateway = new \App\Module\Gateway();
        if (!$gateway->load($gatewayModule)) {
            return response()->json(array("warning" => \Lang::get("client.invoiceserror")));
        }
        if (!$gateway->functionExists("remoteinput")) {
            return response()->json(array("warning" => "Invalid Request"));
        }
        $params = \App\Helpers\Cc::getCCVariables($invoiceId, $gatewayModule);
        $output = $gateway->call("remoteinput", $params);
        $output = str_replace("<form", "<form target=\"ccframe\"", $output);
        return response()->json(array("output" => $output));
    }
}
