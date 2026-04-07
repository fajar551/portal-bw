<?php

namespace Modules\Gateways\Ccxendit\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CcxenditController extends Controller
{
    function MetaData()
    {
        return array(
            'DisplayName' => 'Credit Card Xendit',
            'APIVersion' => '1.1', // Use API Version 1.1
            'DisableLocalCreditCardInput' => true,
            'TokenisedStorage' => false,
        );
    }

    function config()
    {
        return [
            'FriendlyName' => array(
                'Type' => 'System',
                'Value' => 'Credit Card Xendit',
            ),
            "UsageNotes" => array(
                "Type" => "System",
                "Value" => "Lihat dan kelola secret keys Anda di <a href=\"https://dashboard.xendit.co/settings/developers#api-keys\">Dasboard Xendit</a>. <i>Public keys hanya digunakan untuk tokenisasi informasi kartu pada sisi client.</i>",
            ),
            'secretKeyTest' => array(
                'FriendlyName' => 'Secret Key Test',
                'Type' => 'password',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter secret key here',
            ),
            'secretKeyLive' => array(
                'FriendlyName' => 'Secret Key Live',
                'Type' => 'password',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter secret key here',
            ),
            'tokenCallbackTest' => array(
                'FriendlyName' => 'Token verifikasi callback test',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter your token here',
            ),
            'tokenCallbackLive' => array(
                'FriendlyName' => 'Token verifikasi callback live',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter your token here',
            ),
            'publicKeyTest' => array(
                'FriendlyName' => 'Public keys test',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter your key here',
            ),
            'publicKeyLive' => array(
                'FriendlyName' => 'Public keys live',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter your key here',
            ),
            'clientId' => array(
                'FriendlyName' => 'Client id notification',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Client id for recieve email notification',
            ),
            'testMode' => array(
                'FriendlyName' => 'Test Mode',
                'Type' => 'yesno',
                'Description' => 'Tick to enable test mode',
            ),
            'instructions' => array(
                'FriendlyName' => 'Payment instructions',
                'Type' => 'textarea',
                'Rows' => '5',
                'Description' => 'The instructions you want displaying to customers who choose this payment method',
                'Default' => $this->getDefaultInstructions(),
            ),
            'emailTemplate' => array(
                'FriendlyName' => 'Email template',
                'Type' => 'dropdown',
                'Options' => $this->getEmailTemplates(),
                'Description' => 'Choose one',
            ),
        ];
    }

    public function link($params)
    {
        try {
            $testMode = $params["testMode"];
            $secretKey = $testMode ? $params["secretKeyTest"] : $params["secretKeyLive"];
            $publicKey = $testMode ? $params["publicKeyTest"] : $params["publicKeyLive"];
            $clientdetails = $params['clientdetails'];
            $userid = $clientdetails['userid'];
            $invoiceid = $params['invoiceid'];
            $name = trim($clientdetails['firstname'].' '.$clientdetails['lastname']);
            $view = "ccxendit::index";

            return view($view, [
                "url" => url("ccxendit/charge"),
                "url_update" => url("ccxendit"),
                'invoiceid' => $invoiceid,
                'langpaynow' => $params["langpaynow"],
                'public_key' => $publicKey,
                'amount' => $params['amount'],
                
                'amountToCharge' => (new \App\Helpers\Pwd())->encrypt($params['amount']),
                'apikeyToCharge' => (new \App\Helpers\Pwd())->encrypt($secretKey),
                'useridToCharge' => (new \App\Helpers\Pwd())->encrypt((string)$userid),
                'invoiceidToCharge' => (new \App\Helpers\Pwd())->encrypt((string)$invoiceid),
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function call($url = '', $token = '', array $postfield = [])
    {
        $postfield = json_encode($postfield);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postfield,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic '.base64_encode("$token:"),
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function charge(Request $request)
    {
        try {
            $amount = (new \App\Helpers\Pwd())->decrypt($request->input('amount'));
            $apikey = (new \App\Helpers\Pwd())->decrypt($request->input('apikey'));
            $userid = (new \App\Helpers\Pwd())->decrypt($request->input('userid'));
            $invoiceid = (new \App\Helpers\Pwd())->decrypt($request->input('invoiceid'));

            $tokencc = $request->input('tokencc');
            $authentication_id = $request->input('authentication_id');
            $card_cvn = $request->input('card_cvn');
            
            $paramsData = [
                'token_id' => $tokencc,
                'external_id' => $invoiceid . '-' . time(),
                'authentication_id' => $authentication_id,
                'amount' => $amount,
                'card_cvn' => $card_cvn,
                'capture' => true,
            ];
            
            $createCharge = $this->call("https://api.xendit.co/credit_card_charges", $apikey, $paramsData);
            \Log::debug("===CC charge====");
            \Log::debug(json_decode(json_encode($createCharge), true));

            // paid invoice
            if ($createCharge->status == "CAPTURED") {
                $this->paidInvoice($invoiceid, json_decode(json_encode($createCharge), true));
            }

            return response()->json([
                "result" => "success",
                'message' => 'Charge payment succesful',
            ]);
        } catch (\Xendit\Exceptions\ApiException $e) {
            return response()->json([
                "result" => "error",
                'message' => $e->getErrorCode()."-".$e->getMessage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "result" => "error",
                'message' => $e->getCode()."-".$e->getMessage(),
            ]);
        }
    }

    public function paidInvoice($invoiceid, $callback)
    {
        $activeinvoice = false;
        try {
            $invoice = new \App\Helpers\InvoiceClass($invoiceid);
            $activeinvoice = true;
        } catch (\Exception $e) {
            $activeinvoice = false;
        }

        if ($activeinvoice) {
            $params = $invoice->getGatewayInvoiceParams();
            $clientIdNotification = $params["clientId"];
            $clientdetails = $params['clientdetails'];
            $userid = $clientdetails['userid'];
            $invoiceId = $invoiceid;
            $gateway = $params['paymentmethod'];
            
            $transactionId = $callback['external_id'];
            $amount = $callback['capture_amount'];
            $status = $callback['status'];

            $payment_num_rows_transaction_count = \App\Models\Account::where(['userid' => $userid, 'transid' => $transactionId])->count();
            if ($payment_num_rows_transaction_count <= 0) {
                \App\Helpers\Gateway::logTransaction($gateway, $callback, $status);
                $command = "SendEmail";
                $adminuser = "billing";
                $values["customtype"] = "general";
                $values["customsubject"] = "Ada Pembayaran Masuk ke CC Xendit Relabs, Untuk No Invoice: ".$invoiceId.", Txid:".$transactionId;
                $values["custommessage"] = "Silakan di cek di https://dashboard.xendit.co/</nowiki>";
                $values["id"]            = $clientIdNotification;
                $results = (new \App\Helpers\HelperApi)->localAPI($command, $values, $adminuser);
                
                \App\Helpers\Invoice::addInvoicePayment(
                    $invoiceId,
                    $transactionId,
                    $amount,
                    '',//Payment Fee
                    $gateway
                );
            }
        }
    }

    public function index(Request $request)
    {
        $postData = array(
            'invoiceid' => $request->input('id'),
        );
        $response = (new \App\Helpers\HelperApi)->localAPI('GetInvoice', $postData);
        if ($response['result'] == 'success') {
            if($response['status'] == 'Paid'){
                return response()->json(['status' => true], 200);
            }else{
                return response()->json(['status' => false], 200);
            }
        } else {
            return response()->json(['status' => false], 200);
        }
    }

    private function getDefaultInstructions()
    {
        return '
        under construction
        '; 
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
