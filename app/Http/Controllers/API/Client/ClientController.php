<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseAPI;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Client;

class ClientController extends Controller
{
    public function __construct(){
        $this->client = new Client();
    }

    public function AddClient(Request $request){
        $validator = Validator::make($request->all(), [
            'firstname'         => 'required|string',
            'lastname'          => 'required|string',
            'email'             => 'required|email|unique:'.\Database::prefix().'clients,email',
            'address1'          => 'required|string',
            'city'              => 'required|string',
            'state'             => 'required|string',
            'country'           => 'required|string|max:2',
            'phonenumber'       => 'required|string|max:14|min:8',
        ]);

        if ($validator->fails()) {
             return response()->json(['error' => $validator->errors()], 401);
        }
       try{
            $data=$this->client->AddClient($request->all());
            return ResponseAPI::Success($data);

       } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function UpdateClient(Request $request){
        $validator = Validator::make($request->all(), [
            'clientid'         => 'required|integer'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }
        try{
            $data=$this->client->UpdateClient($request->all());
            return ResponseAPI::Success($data);

        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }



    public function AddContact(Request $request){
        $validator = Validator::make($request->all(), [
            'clientid'         => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try{
            $data=$this->client->AddContact($request->all());
            return ResponseAPI::Success($data);

        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function CloseClient(Request $request){
        $validator = Validator::make($request->all(), [
            'clientid'         => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try{
            $data=$this->client->CloseClient($request->clientid);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }

    public function DeleteClient(Request $request){
        $validator = Validator::make($request->all(), [
            'clientid'         => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try{
            $data=$this->client->DeleteClient($request->clientid);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function DeleteContact(Request $request){
        $validator = Validator::make($request->all(), [
            'contactid'         => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try{
            $data=$this->client->DeleteContact($request->contactid);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }



    public function GetClients(Request $request){
        $params=[
                  'limitstart'  => (int)  $request->limitstart,
                  'limitnum'    => (int)  $request->limitnum,
                  'sorting'     =>   $request->sorting,
                  'status'      =>   $request->status,
                  'search'      =>   $request->search,

                ];
        try{
            $data=$this->client->GetClients($params);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function GetClientPassword(Request $request){
        $params=[
            'userid'  => (int)  $request->userid,
            'email'   =>   $request->email
          ];
        try{
            $data=$this->client->GetClientPassword($params['userid'],$params['email']);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }



    public function GetClientGroups(){
        try{
            $data=$this->client->GetClientGroups();
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }

    public function GetClientsDetails(Request $request){
        $params=[
            'clientid'  => (int)  $request->clientid,
            'email'   =>   $request->email,
            'stats'   =>  (bool) $request->stats
          ];
        try{
            $data=$this->client->GetClientsDetails($params);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
          return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetClientsAddons(Request $request){
        $params=[
            'serviceid'  => $request->serviceid,
            'clientid'   => (int)$request->clientid,
            'addonid'   =>  (int) $request->addonid
          ];
        try{
            $data=$this->client->GetClientsAddons($params);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetClientsDomains(Request $request){
        $params=[
            'limitstart'  => (int)$request->limitstart,
            'limitnum'   => (int)$request->limitnum,
            'clientid'   =>  (int) $request->clientid,
            'domainid'   =>  (int) $request->domainid,
            'domain'   =>  $request->domain
          ];
        try{
            $data=$this->client->GetClientsDomains($params);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            dd($e);
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function GetContacts(Request $request){
        $params=[
            'limitstart'  => (int)$request->limitstart,
            'limitnum'    => (int)$request->limitnum,
            'userid'      =>  (int) $request->userid,
            'firstname'   => $request->firstname,
            'lastname'    =>  $request->lastname,
            'companyname' =>  $request->companyname,
            'email'       =>  $request->email,
            'address1'    =>  $request->address1,
            'address2'    =>  $request->address2,
            'city'        =>  $request->city,
            'state'       =>  $request->state,
            'postcode'    =>  $request->postcode,
            'country'     =>  $request->country,
            'phonenumber'    =>  $request->phonenumber,

          ];


        try{
            $data=$this->client->GetContacts($params);
           return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetClientsProducts(Request $request){
        //dd($request->all());
        $params=[
            'limitstart'  => (int)$request->limitstart,
            'limitnum'    => (int)$request->limitnum,
            'clientid'    => (int) $request->clientid,
            'serviceid'   => (int) $request->serviceid,
            'pid'         => (int)$request->pid,
            'domain'      => $request->domain,
            'username2'   =>  $request->username2
          ];
       try{
            $data=$this->client->GetClientsProducts($params);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
           return ResponseApi::Error(['message' => $e->getMessage()]);
       }

    }


    public function GetEmails(Request $request){
        $params=[
            'clientid'    => (int) $request->clientid,
            'limitstart'  => (int)$request->limitstart,
            'limitnum'    => (int)$request->limitnum,
            'date'        => $request->date,
            'subject'     => $request->subject
          ];
        try{
            $data=$this->client->GetEmails($params);
           return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }

    public function UpdateContact(Request $request){
        $params=[
			'contactid'     => (int) $request->contactid,
			'firstname'    => $request->firstname,
			'lastname'     => $request->lastname,
			'companyname'  => $request->companyname,
			'email'        => $request->email,
			'address1'     => $request->address1,
			'address2'     => $request->address2,
			'city'         => $request->city,
			'state'        => $request->state,
			'postcode'     => $request->postcode,
			'country'      => $request->country,
			'phonenumber'  => $request->phonenumber,
			'email_preferences' => (array) $request->email_preferences,
            'password2'      => $request->password2,
		];
        try{
            $data=$this->client->UpdateContact($params);
           return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function GetCancelledPackages(Request $request){
        $params=[
                    'limitstart'   => (int) $request->limitstart,
                    'limitnum'     => (int) $request->limitnum
                ];
        try{
            $data=$this->client->GetCancelledPackages($params);
            return ResponseAPI::Success($data);
        }catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }



}
