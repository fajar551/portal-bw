<?php

namespace Modules\Gateways\Banktransfer\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BanktransferController extends Controller
{
    /**
     * Define module related meta data.
     *
     * Values returned here are used to determine module related capabilities and
     * settings.
     *
     * @return array
     */
    function MetaData()
    {
        return array(
            'DisplayName' => 'Bank Transfer',
            'APIVersion' => '1.1', // Use API Version 1.1
            'DisableLocalCreditCardInput' => true,
            'TokenisedStorage' => false,
        );
    }

    /**
     * Define gateway configuration options.
     *
     * The fields you define here determine the configuration options that are
     * presented to administrator users when activating and configuring your
     * payment gateway module for use.
     *
     * Supported field types include:
     * * text
     * * password
     * * yesno
     * * dropdown
     * * radio
     * * textarea
     *
     * Examples of each field type and their possible configuration parameters are
     * provided in the sample function below.
     *
     * @return array
     */
    function config()
    {
        $configarray = array(
            "FriendlyName" => array("Type" => "System", "Value" => "Bank Transfer"), 
            "instructions" => array("FriendlyName" => "Bank Transfer Instructions", "Type" => "textarea", "Rows" => "5", "Default" => "Bank Name:\nPayee Name:\nSort Code:\nAccount Number:",
            "Description" => "The instructions you want displaying to customers who choose this payment method - the invoice number will be shown underneath the text entered above"),
            'emailTemplate' => array(
                'FriendlyName' => 'Email template',
                'Type' => 'dropdown',
                'Options' => $this->getEmailTemplates(),
                'Description' => 'Choose one',
            ),
        );
        return $configarray;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function link($params = [])
    {
        $code = '<p>' . nl2br(@$params['instructions']) . '<br />' . __('client.invoicerefnum') . ': ' . $params['invoiceid'] . '</p>';
        return $code;
    }

    private function getEmailTemplates()
    {
        $data = [];
        $data[""] = "-- None --";
        $emailTemplates = \Illuminate\Support\Facades\DB::table("tblemailtemplates")->where(["type" => "invoice", "custom" => "1"])->get();
        foreach ($emailTemplates as $emailTemplate) {
            $data[$emailTemplate->name] = $emailTemplate->name;
        }

        return $data;
    }
}
