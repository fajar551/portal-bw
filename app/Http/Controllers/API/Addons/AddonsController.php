<?php

namespace App\Http\Controllers\API\Addons;
use ResponseAPI;
use App\Helpers\Addons;
use App\Http\Controllers\Controller;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddonsController extends Controller
{
    public function __construct()
    {
        
    }
    
    //
    public function index(){
        $params=[
                    'id' => 2,
                    'status' => 'Active',
                    'terminationDate' => '2021-05-20',
                    'addonid' => 2,
                    'name' => 'Boost Power 2aaa',
                    'setupfee' => 0.00,
                    'recurring' => 100.00,
                    'billingcycle' => 'Monthly',
                    'nextduedate' => '2021-05-20',
                    /* 'terminationDate' => '2021-05-30', */
                    'notes' => 'heheh',
                    'autorecalc' => true,
                    
                ];
        $addons = new Addons();
        $data= $addons->UpdateClientAddon($params);

        return ResponseAPI::Success([
            'addons' => $data,
        ]);
    }

    public function UpdateClientAddon(Request $request){
        $validator = Validator::make($request->all(), [
                                'id'   => 'required|integer'
                    ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $addons = new Addons();
        $updateAddon= $addons->UpdateClientAddon($request->all());
        if($updateAddon['result'] == 'success' ){
            return ResponseAPI::Success($updateAddon);
        }else{
            return ResponseApi::Error($updateAddon);
        }
    }



}
