<?php

namespace App\Http\Controllers\API\System;
use ResponseAPI;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\SystemHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

/**
 * @group System
 * 
 * APIs for managing system
 */
class SystemController extends Controller
{
    
    public function __construct(){
       $this->system = new SystemHelper();  
    }

    
    public function AddBannedIp(Request $request){
        $validator = Validator::make($request->all(), [
            'ip'        => 'required|string',
            'reason'    => 'required|string',
            'days'      => 'required|int'
        ]);

        if ($validator->fails()) {
             return response()->json(['error' => $validator->errors()], 401);
        }
        try {
            $data=$this->system->AddBannedIp($request->all());
            if($data['result'] == 'success' ){
                return ResponseAPI::Success($data);
            }else{
                return ResponseApi::Error($data);
            }
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function DecryptPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password2'        => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        //try {
            $data=$this->system->DecryptPassword($request->password2);
            return ResponseAPI::Success($data);
       // } catch (\Exception $e) {
        //    return ResponseApi::Error(['message' => $e->getMessage()]);
       // }
    }

    public function EncryptPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password2'        => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try {
            $data=$this->system->EncryptPassword($request->password2);
            return ResponseAPI::Success($data);
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetActivityLog(Request $request){
        $params=[
                    'limitstart'         => (int) $request->limitstart,
                    'limitnum'           => (int) $request->limitnum,
                    'userid'             => (int) $request->userid,
                    'date'               => $request->date,
                    'user'               => $request->user,
                    'description'        => $request->description,
                    'ipaddress'          => $request->ipaddress,
                ];
        try{
            //dd($params);
            $data=$this->system->GetActivityLog($params);
            return ResponseAPI::Success($data);

        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function GetAdminDetails(Request $request){
        $params=[
            'roleid'               => (int) $request->roleid,
            'email'                => $request->email,
            'include_disabled'     => $request->include_disabled,
            'signature'             => $request->signature,
            'allowedpermissions'    => $request->allowedpermissions,
            'departments'           => $request->departments,
            'requesttime'           => $request->requesttime,
            'whmcs'                 => (array)$request->whmcs,
        ];
        try{
            
            $data=$this->system->GetAdminDetails($params);
            return ResponseAPI::Success($data);

        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetAdminUsers(Request $request){
        $params=[
            'roleid'               => (int) $request->roleid,
            'email'                => $request->email,
            'include_disabled'     => (bool)$request->include_disabled
        ];
        try{
            
            $data=$this->system->GetAdminUsers($params);
            return ResponseAPI::Success($data);

        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetAutomationLog(Request $request){
        $params=[
            'startdate'   => $request->startdate,
            'enddate'     => $request->enddate,
            'namespace'   => $request->namespace
        ];
       try{
           $data=$this->system->GetAutomationLog($params);
            return ResponseAPI::Success($data);

        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }


    public function GetConfigurationValue(Request $request){
        $params=[
            'setting'   => $request->setting,
        ];
       try{
             $data=$this->system->GetConfigurationValue($params);
            return ResponseAPI::Success($data);

        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetCurrencies(){
       try{
             $data=$this->system->GetCurrencies();
             return ResponseAPI::Success($data);
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetEmailTemplates(Request $request){
        $params=[
                    'type' =>  $request->type,
                    'language' =>  $request->language,
                    'id' => $request->id,
                ];
       try{
             $data=$this->system->GetEmailTemplates($params);
             return ResponseAPI::Success($data);
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetPaymentMethodsOLD(){
        try{
            $data=$this->system->GetPaymentMethods();
            return ResponseAPI::Success($data);
       } catch (\Exception $e) {
           return ResponseApi::Error(['message' => $e->getMessage()]);
       }
    }
    public function GetPaymentMethods(Request $request)
    {
        $paymentmethods = \App\Helpers\Gateway::getGatewaysArray();
        $apiresults = array("result" => "success", "totalresults" => count($paymentmethods));
        foreach ($paymentmethods as $module => $name) {
            $apiresults["paymentmethods"]["paymentmethod"][] = array("module" => $module, "displayname" => $name);
        }
        return ResponseAPI::Success($apiresults);
    }

    public function GetStaffOnline(){
        try{
            $data=$this->system->GetStaffOnline();
            return ResponseAPI::Success($data);
        } catch (\Exception $e) {
           return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function GetStats(Request $request){
        $timeline_days=(int)$request->timeline_days;
        try{
            $data=$this->system->GetStats($timeline_days);
            return ResponseAPI::Success($data);
        } catch (\Exception $e) {
           return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function GetToDoItems(Request $request){
        $params=[
                'limitstart' => (int) $request->limitstart,
                'limitnum' => (int) $request->limitnum,
                'status' =>  $request->status
            ];
        try{
            $data=$this->system->GetToDoItems($params);
            return ResponseAPI::Success($data);
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetToDoItemStatuses(){
        try{
            $data=$this->system->GetToDoItemStatuses();
            return ResponseAPI::Success($data);
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }
    public function LogActivity(Request $request){
        $params=[
                    'clientid' => (int) $request->clientid,
                    'description' => $request->description
                ];
                
             //   dd($params);
        //try{
            $data=$this->system->LogActivity($params);
           // return ResponseAPI::Success($data);
        //} catch (\Exception $e) {
        //    return ResponseApi::Error(['message' => $e->getMessage()]);
       // }

    }


    public function SendAdminEmailOLD(Request $request){
            $params=[
                    'messagename'   => $request->messagename,
                    'custommessage' => $request->custommessage,
                    'customsubject' => $request->customsubject,
                    'type'          => $request->type,
                    'deptid'        => (int) $request->deptid,
                    'mergefields'   => (array) $request->mergefields
                ];
            //dd($params);

            $data=$this->system->SendAdminEmail($params);

    }
    public function SendAdminEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'messagename' => ['nullable', 'string'],
            'custommessage' => ['nullable', 'string'],
            'customsubject' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'deptid' => ['nullable', 'integer'],
            'mergefields' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

        // vars
        $messagename = $request->input('messagename');
        $custommessage = $request->input('custommessage');
        $customsubject = $request->input('customsubject');
        $type = $request->input('type');
        $deptid = $request->input('deptid');
        $mergefields = $request->input('mergefields') ?? [];

        if ($custommessage) {
            \App\Models\Emailtemplate::where("name", "=", "Mass Mail Template")->delete();
            $template = new \App\Models\Emailtemplate();
            $template->type = "admin";
            $template->name = "Custom Admin Temp";
            $template->subject = \App\Helpers\Sanitize::decode($customsubject);
            $template->message = \App\Helpers\Sanitize::decode($custommessage);
            $template->disabled = false;
            $template->plaintext = false;
        } else {
            try {
                $template = \App\Models\Emailtemplate::where("name", "=", $messagename)->where("type", "=", "admin")->firstOrFail();
            } catch (\Exception $e) {
                $apiresults = array("result" => "error", "message" => "Email Template not found");
                return ResponseApi::Error($apiresults);
            }
        }
        if (!in_array($type, array("system", "account", "support"))) {
            $type = "system";
        }
        \App\Helpers\Functions::sendAdminMessage($template, $mergefields, $type, $deptid);
        $apiresults = array("result" => "success");
        return ResponseAPI::Success($apiresults);
    }


    public function SetConfigurationValue(Request $request){
        $validator = Validator::make($request->all(), [
            'setting'   => 'required',
            'value'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try{
            $data=$this->system->SetConfigurationValue($request->all());
            return ResponseAPI::Success($data);
        } catch (\Exception $e) {
                return ResponseApi::Error(['message' => $e->getMessage()]);
        
        }
    }


    public function TriggerNotificationEvent(Request $request){
        $params=[
                    'notification_identifier' => $request->notification_identifier,
                    'title' => $request->title,
                    'message' => $request->message,
                    'url' => $request->url,
                    'status' => $request->status,
                    'attributes' => (array)$request->attributes,
                ];
        dd($params);
    }


    public function UpdateAnnouncement(Request $request){
        $validator = Validator::make($request->all(), [
            'announcementid'   => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $params=[
            'announcementid' => (int) $request->announcementid,
            'title' => $request->title,
            'announcement' => $request->announcement,
            'date' =>  $request->date,
            'status' => $request->status,
            'published' => (bool)$request->published,
        ];
       // dd($params);

       
        try{
            $data=$this->system->UpdateAnnouncement($params);
            return ResponseAPI::Success($data);
        } catch (\Exception $e) {
                return ResponseApi::Error(['message' => $e->getMessage()]);
        
        }

    }

    public function UpdateToDoItem(Request $request){
        $validator = Validator::make($request->all(), [
            'itemid'    => 'required|int',
            'adminid'   => 'required|int',
            'date'      => 'int',
            'duedate'   => 'int',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $params=[
            'notes' => (int) $request->itemid
        ];

        try{
            $data=$this->system->UpdateToDoItem($params);
            return ResponseAPI::Success($data);
        } catch (\Exception $e) {
                return ResponseApi::Error(['message' => $e->getMessage()]);
        
        }
    }

    public function UpdateAdminNotes(Request $request){
        $validator = Validator::make($request->all(), [
            'note'    => 'required|string',
            'adminid'   => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try{
            $note =$request->note;
            $adminid =$request->adminid;

            $admin =\App\Models\Admin::find($adminid);
            if (is_null($admin)) {
                return ResponseApi::Error(['message' => 'You must be authenticated as an admin user to perform this action']);
            }
            $admin->notes = $note;
            $admin->save();

            return ResponseAPI::Success(["result" => "success"]);
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }

    /**
     * SendEmail
     * 
     * Send a client Email Notification.
     * 
     * The following email templates cannot be sent in this way:
     * - Order Confirmation
     * - Password Reset Confirmation
     * - Password Reset Validation
     * - Quote Accepted
     * - Quote Accepted Notification
     * - Quote Delivery with PDF
     * - Clients Only Bounce Message
     * - Replies Only Bounce Message
     * - Affiliate Monthly Referrals Report
     * 
     * What you must provide for the Related ID depends upon the type of email being sent. The available options are:
     * - General Email Type = Client ID (tblclients.id)
     * - Product Email Type = Service ID (tblhosting.id)
     * - Domain Email Type = Domain ID (tbldomains.id)
     * - Invoice Email Type = Invoice ID (tblinvoices.id)
     * - Support Email Type = Ticket ID (tbltickets.id)
     * - Affiliate Email Type = Affiliate ID (tblaffiliates.id)
     * 
     * **Sending Failed**
     * 
     * The generic Sending Failed error response can be caused by one of five possible conditions. They are:
     * - Invalid related ID
     * - Email template contains no body content after processing (typically a Smarty error)
     * - Welcome email requested for product which has none set
     * - Hook aborted the sending
     * - PHPMailer Sending Error - in this failure condition, an activity log entry will be created recording the error message that occurred
     */
    public function SendEmail(Request $request)
    {
        $validCustomEmailTypes = array("general", "product", "domain", "invoice", "support", "affiliate");

        $rules = [
            // The name of the client email template to send
            'messagename' => ['nullable', 'string'],
            // The related id for the type of email template. Eg this should be the client id for a general type email
            'id' => ['nullable', 'integer'],
            // The type of custom email template to send (‘general’, ‘product’, ‘domain’, ‘invoice’, ‘support’, ‘affiliate’)
            'customtype' => ['nullable', 'string'],
            // The HTML message body to send for a custom email
            'custommessage' => ['nullable', 'string'],
            // The subject to send for a custom email
            'customsubject' => ['nullable', 'string'],
            // The custom variables to provide to the email template. Can be used for existing and custom emails.
            'customvars' => ['nullable', 'string'],
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $incomingEmailTplName = $request->input("messagename");
        $incomingRelId = $request->input("id");
        $incomingCustomType = $request->input("customtype");
        $incomingCustomSubject = $request->input("customsubject");
        $incomingCustomMsg = $request->input("custommessage");
        $incomingCustomVars = $request->input("customvars");
        $incomingNonNl2Br = $request->input("nonl2br");

        if (!$incomingEmailTplName && !$incomingCustomType) {
            return ResponseAPI::Error([
                'message' => "You must provide either an existing email template name or a custom message type",
            ]);
        }
        if ($incomingCustomType) {
            if (!in_array($incomingCustomType, $validCustomEmailTypes)) {
                return ResponseAPI::Error([
                    'message' => "Invalid message type provided",
                ]);
            }
            if (!$incomingCustomSubject) {
                return ResponseAPI::Error([
                    'message' => "A subject is required for a custom message",
                ]);
            }
            if (!$incomingCustomMsg) {
                return ResponseAPI::Error([
                    'message' => "A message body is required for a custom message",
                ]);
            }
        }
        if (!$incomingRelId || !is_numeric($incomingRelId)) {
            return ResponseAPI::Error([
                'message' => "A related ID is required",
            ]);
        }
        if ($incomingCustomType) {
            $messageBody = \App\Helpers\Sanitize::decode($incomingCustomMsg);
            if (!$incomingNonNl2Br) {
                $messageBody = nl2br($messageBody);
            }
            \App\Models\Emailtemplate::where("name", "=", "Mass Mail Template")->delete();
            $template = new \App\Models\Emailtemplate();
            $template->type = $incomingCustomType;
            $template->name = "Mass Mail Template";
            $template->subject = $incomingCustomSubject;
            $template->message = $messageBody;
            $template->plaintext = 0;
            $template->disabled = 0;
        } else {
            $template = \App\Models\Emailtemplate::where("name", "=", $incomingEmailTplName)->where("language", "=", "")->first();
            if (is_null($template)) {
                return ResponseAPI::Error([
                    'message' => "Email Template not found",
                ]);
            }
            if ($template->disabled) {
                return ResponseAPI::Error([
                    'message' => "Email Template is disabled",
                ]);
            }
        }
        $customVars = array();
        if ($incomingCustomVars) {
            if (is_array($incomingCustomVars)) {
                $customVars = $incomingCustomVars;
            } else {
                $customVars = (new \App\Helpers\Client())->safe_unserialize(base64_decode($incomingCustomVars));
            }
        }
        $sendingResult = \App\Helpers\Functions::sendMessage($template, $incomingRelId, $customVars);
        if ($sendingResult) {
            return ResponseAPI::Success();
        } else {
            return ResponseAPI::Error([
                'message' => "Sending Failed. Please see documentation.",
            ]);
        }
    }
}
