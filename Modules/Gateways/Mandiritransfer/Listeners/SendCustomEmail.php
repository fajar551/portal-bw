<?php

namespace Modules\Gateways\Mandiritransfer\Listeners;

use App\Events\InvoiceCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCustomEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param InvoiceCreated $event
     * @return void
     */
    public function handle(InvoiceCreated $event)
    {
        //
        $source = $event->source;
        $user = $event->user; // mixed
        $invoiceid = $event->invoiceid;
        $status = $event->status;

        try {
            $invoice = new \App\Helpers\InvoiceClass($invoiceid);
            $params = $invoice->getGatewayInvoiceParams();

            $emailTemplate = $params["emailTemplate"] ?? "";
            $paymentmethod = $params["paymentmethod"];
            $customfields = $params["clientdetails"]["customfields"];

            // change this
            $gateway = "mandiritransfer";

            // compare gateway
            if (strtolower($paymentmethod) == $gateway) {
                // check email template
                if ($emailTemplate) {
                    // customvars
                    $customvars = array(
                        // "nova" => $this->getNoVa($customfields, $fieldname),
                    );
                    $values["messagename"] = $emailTemplate;
                    $values["id"] = $invoiceid;
                    $values["customvars"] = base64_encode(serialize($customvars));
                    $response = (new \App\Helpers\HelperApi)->localAPI('SendEmail', $values);
                }
            }
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
        }
    }

    private function getNoVa($customfields, $fieldname)
    {
        $value = "";
        foreach ($customfields as $customfield) {
            $cst = \Illuminate\Support\Facades\DB::table("tblcustomfields")->where("id", $customfield['id'])->first();
            if ($cst) {
                if ($cst->fieldname == $fieldname) {
                    $value = $customfield['value'];
                    break;
                }
            }
        }
        return $value;
    }
}