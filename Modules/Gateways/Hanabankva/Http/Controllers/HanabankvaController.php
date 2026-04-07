<?php

namespace Modules\Gateways\Hanabankva\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HanabankvaController extends Controller
{
    public function MetaData()
    {
        return array(
            'DisplayName' => 'HANA Bank VA',
            'APIVersion' => '1.1', // Use API Version 1.1
            'DisableLocalCreditCardInput' => true,
            'TokenisedStorage' => false,
        );
    }

    public function config()
    {
        $configarray = array(
            "FriendlyName" => array("Type" => "System", "Value" => "HANA Bank VA"),
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
            
            $user = \DB::table('fixedva')->where('clientid', $userid)->value('hanabankva');

            return view('hanabankva::index', [
                'params' => $params,
                'nomor' => $user,
            ]);
        
        } catch (\Exception $e) {
            return $e->getCode()."-".$e->getMessage();
        }
    }
}
