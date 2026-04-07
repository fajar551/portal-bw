<?php

namespace Modules\Gateways\Permatabankva\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PermatabankvaController extends Controller
{
    public function MetaData()
    {
        return array(
            'DisplayName' => 'Permata Bank VA',
            'APIVersion' => '1.1', // Use API Version 1.1
            'DisableLocalCreditCardInput' => true,
            'TokenisedStorage' => false,
        );
    }

    public function config()
    {
        $configarray = array(
            "FriendlyName" => array("Type" => "System", "Value" => "Permata Bank VA"),
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
            
            $user = \DB::table('fixedva')->where('clientid', $userid)->value('permatabankva');

            return view('permatabankva::index', [
                'params' => $params,
                'nomor' => $user,
            ]);
        
        } catch (\Exception $e) {
            return $e->getCode()."-".$e->getMessage();
        }
    }
}
