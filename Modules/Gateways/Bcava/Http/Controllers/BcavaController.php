<?php

namespace Modules\Gateways\Bcava\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BcavaController extends Controller
{
    public function MetaData()
    {
        return array(
            'DisplayName' => 'BCA VA',
            'APIVersion' => '1.1', // Use API Version 1.1
            'DisableLocalCreditCardInput' => true,
            'TokenisedStorage' => false,
        );
    }

    public function config()
    {
        $configarray = array(
            "FriendlyName" => array("Type" => "System", "Value" => "BCA VA"),
            'clientId' => array(
                'FriendlyName' => 'Client id notification',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Client id for recieve email notification',
            ),
        );
        return $configarray;
    }

    public function link($params)
    {
        try {
            $clientdetails = $params['clientdetails'];
            $userid = $clientdetails['userid'];
            
            $user = \DB::table('fixedva')->where('clientid', $userid)->value('bcava');

            return view('bcava::index', [
                'params' => $params,
                'nomor' => $user,
            ]);
        
        } catch (\Exception $e) {
            return $e->getCode()."-".$e->getMessage();
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
                $values["customsubject"] = "Ada Pembayaran Masuk ke BCA VA Fleksibel, Untuk No Invoice: ".$invoiceId.", Txid:".$transactionId;
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
            $result = \App\Helpers\HelperApi::post('AddInvoicePayment', $postData);
                        \Log::debug("===result AddInvoicePayment");
                        \Log::debug($result);
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
}
