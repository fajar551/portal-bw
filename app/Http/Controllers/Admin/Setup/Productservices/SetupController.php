<?php

namespace App\Http\Controllers\Admin\Setup\Productservices;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//Models
use App\Models\Currency;
use App\Models\Emailtemplate;
use App\Models\Productgroup;
use App\Models\Product;
use App\Models\AdminRole;
use App\Models\Admin;
//Helpers
use App\Helpers\Gateway;
use App\Helpers\ProductType;
use App\Helpers\Format;
use App\Helpers\Password;
use App\Helpers\Pwd;
use Ramsey\Uuid\Uuid;
use App\Helpers\Cfg;
use DataTables;
use Validator;
use Database;
use API;
use App\Helpers\LogActivity;
use Illuminate\Support\Facades\DB;
class SetupController extends Controller
{
    public function __construct() 
    {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
        $this->adminURL =request()->segment(1).'/';
    }
    public function SignInIntegrations()
    {
        return view('pages.setup.signinintegrations.index');
    }
    public function AppsIntegrations()
    {
        return view('pages.setup.appsintegrations.index');
    }
    public function AutomationSettings() 
    {
        return view('pages.setup.automationsettings.index');
    }
    public function MarketConnect()
    {
        return view('pages.setup.marketconnect.index');
    }
    public function Notifications()
    {
        return view('pages.setup.notifications.index');
    }
    public function StorageSettings()
    {
        return view('pages.setup.storagesettings.index');
    }
    public function StaffManagement_adminusers()
    {
        return view('pages.setup.staffmanagement.administratorusers.index');
    }
    public function StaffManagement_adminusers_form(Request $request)
    {
        $getListRole = AdminRole::all()->pluck('name', 'id')->toArray();
        return view('pages.setup.staffmanagement.administratorusers.add', ['roleList' => $getListRole]);
    }
    public function StaffManagement_adminusers_insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roleid' => 'required|numeric',
            'username' => 'required|string',
            'password' =>  'required|string',
            'password2' => 'required|string',
            'authmodule' => 'nullable',
            'authdata' => 'nullable',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string',
            'signature' => 'nullable',
            'ticketnotification' => 'nullable',
            'password_reset_key' => 'nullable',
            'password_reset_date' => 'nullable',
            'hidden_widgets' => 'nullable',
        ]);
        
        if($validator->fails()) {
            return redirect()->back()->withInput($request->except('password', 'password2'))->withErrors($validator)->with('message', 'Can\'t create new Administrator , please fill forms correctly and try again');
        }
        // route('admin.pages.setup.staffmanagement.administratorusers.addform')
        $uuid = "";
        if (!$uuid) {
            $uuid = Uuid::uuid4();
            $uuid = $uuid->toString();
        }

        if($request->password){
            $password = $request->password;
            $hashPass = new Password();
            $hashedPass = $hashPass->hash($password);
        }

        $passwordHash = (new Password())->hash(\App\Helpers\Sanitize::decode($request->password));

        $newAdmin = new Admin();
        $newAdmin->uuid = $uuid;
        $newAdmin->roleid = $request->roleid;
        $newAdmin->username = $request->username;
        $newAdmin->password = $hashedPass;
        $newAdmin->passwordhash = $passwordHash;
        $newAdmin->firstname = $request->firstname;
        $newAdmin->lastname = $request->lastname;
        $newAdmin->email = $request->email;
        $newAdmin->signature = $request->signature;
        $newAdmin->notes = $request->notes;
        $newAdmin->template = $request->template;
        $newAdmin->language = $request->language;
        $newAdmin->save();
        
        return redirect()->route('admin.pages.setup.staffmanagement.administratorusers.index', ['success' => 'Congratulation, a new Administrator has been created!']);
    }
    public function StaffManagement_2fa()
    {
        return view('pages.setup.staffmanagement.2fa.index');
    }
    public function StaffManagement_apicredentials()
    {
        return view('pages.setup.staffmanagement.manageapicredentials.index');
    }
    public function Payments_currencies()
    {
       $currenciesData = API::post('GetCurrencies');
       return view('pages.setup.payments.currencies.index')->with('currenciesData', $currenciesData->currencies->currency);
    }
    public function Payments_currencies_create(Request $request)
    {
        $validator = Validator::make($request->all(), [   
            'code' => 'required',
            'prefix' => 'required',
            'suffix' => 'nullable',
            'default' => 'nullable'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.pages.setup.payments.currencies.index')->withErrors($validator)->with('message', 'Can\'t create new currency, please try again.');
        }

        $newCurrency = new Currency();
        $newCurrency->code = $request->code;
        $newCurrency->prefix = $request->prefix;
        $newCurrency->format = $request->format;
        $newCurrency->rate = $request->rate;
        $newCurrency->default = 0;
        $newCurrency->save();

        return redirect()->route('admin.pages.setup.payments.currencies.index')->with(['success' => 'A new currency has been created']);
    }
    public function Payments_currencies_edit($id)
    {
        $currencyDataById = Currency::findOrfail($id);
        return view('pages.setup.payments.currencies.edit', [
            'currency' => $currencyDataById
        ]);
    }
    public function Payments_currencies_update(Request $request, $id)
    {
        $data = $request->all();
        $currencyData = Currency::findOrFail($id);
        $currencyData->update($data);
        return redirect()->route('admin.pages.setup.payments.currencies.edit', ['id' => $id])->with(['success' => 'The currency has been updated.']);
    }
    public function Payments_currencies_delete(Request $request, $id)
    {
        $data = $request->ajax();
        $currencyData = Currency::findOrFail($id);
        $currencyData->delete($data);
        return redirect()->route('admin.pages.setup.payments.currencies.index')->with(['success' => 'A currency code has been successfully deleted']);
    }
     public function Payments_paymentgateways()
    {
        $dataPaymentMethods = API::post('GetPaymentMethods');
        // dd($dataPaymentMethods);
        return view('pages.setup.payments.paymentgateways.index');
    }
    public function Payments_taxconfiguration()
    {
        return view('pages.setup.payments.taxconfiguration.index');
    }
    public function Promotions()
    {
        return view('pages.setup.payments.promotions.index');
    }
    public function Promotions_create()
    {
        return view('pages.setup.payments.promotions.add');
    }
    public function Support_ticketdepartments()
    {
        return view('pages.setup.support.supportticketdepartments.index');
    }
    public function Support_ticketdepartments_add()
    {
        return view('pages.setup.support.supportticketdepartments.add');
    }
    public function Support_ticketdepartments_edit()
    {
        return view('pages.setup.support.supportticketdepartments.edit');
    }
    public function Support_ticketstatuses()
    {
        return view('pages.setup.support.ticketstatuses.index');
    }
    public function Support_escalationrules()
    {
        return view('pages.setup.support.escalationrules.index');
    }
    public function Support_escalationrules_add()
    {
        return view('pages.setup.support.escalationrules.add');
    }
    public function Support_spamcontrol()
    {
        return view('pages.setup.support.spamcontrol.index');
    }
    public function ApplicationLinks()
    {
        return view('pages.setup.applicationlinks.index');
    }
    public function OpenIdConnect()
    {
        return view('pages.setup.openidconnect.index');
    }
    public function OpenIdConnect_add()
    {
        return view('pages.setup.openidconnect.add');
    }
    public function EmailTemplates()
    {
        //get data from API by type
        $generalTemplates = API::post('GetEmailTemplates', ['type' => 'general']);
        $productTemplates = API::post('GetEmailTemplates', ['type' => 'product']);
        $invoiceTemplates = API::post('GetEmailTemplates', ['type' => 'invoice']);
        $supportTemplates = API::post('GetEmailTemplates', ['type' => 'support']);
        $notificationTemplates = API::post('GetEmailTemplates', ['type' => 'notification']);
        $domainTemplates = API::post('GetEmailTemplates', ['type' => 'domain']);
        $adminTemplates = API::post('GetEmailTemplates', ['type' => 'admin']);
        $affiliatesTemplates = API::post('GetEmailTemplates', ['type' => 'affiliate']);

        // Email Template Array Response
        $emailGeneralTemplate = $generalTemplates->emailtemplates->emailtemplate;
        $emailProductTemplate = $productTemplates->emailtemplates->emailtemplate;
        $emailInvoiceTemplate = $invoiceTemplates->emailtemplates->emailtemplate;
        $emailSupportTemplate = $supportTemplates->emailtemplates->emailtemplate;
        $emailNotificationTemplate = $notificationTemplates->emailtemplates->emailtemplate;
        $emailDomainTemplate = $domainTemplates->emailtemplates->emailtemplate;
        $emailAdminTemplate = $invoiceTemplates->emailtemplates->emailtemplate;
        $emailAffiliatesTemplate = $affiliatesTemplates->emailtemplates->emailtemplate;

        //pass to view
        return view('pages.setup.emailtemplates.index', [
        'generalTemplates' => $emailGeneralTemplate, 
        'productTemplates' => $emailProductTemplate,
        'invoiceTemplates' => $emailInvoiceTemplate,
        'supportTemplates' => $emailSupportTemplate,
        'notificationTemplates' => $emailNotificationTemplate,
        'domainTemplates' => $emailDomainTemplate,
        'adminTemplates' => $emailAdminTemplate,
        'affiliatesTemplates' => $emailAffiliatesTemplate]);
    }
    public function EmailTemplates_create(Request $request)
    {
        $data = $request->all();
        EmailTemplate::create($data);
        return redirect()->route('admin.pages.setup.emailtemplates.index')->with(['success' => 'A new email template has been created']);
    }
    public function EmailTemplates_edit($id)
    {
        $dataEmailTemplates = API::post('GetEmailTemplates', ['id' => $id]);
        $emailtemplates = $dataEmailTemplates->emailtemplates->emailtemplate;
        return view('pages.setup.emailtemplates.edit', [
            'templates' =>  $emailtemplates
        ]);
    }
    public function EmailTemplates_update(Request $request, $id)
    {
        $data = $request->all();
        $template = EmailTemplate::findOrFail($id);
        $template->update($data);
        return redirect()->route('admin.pages.setup.emailtemplates.edit', ['id' => $id])->with(['success' => 'The email templates has been updated.']);
    }
    public function EmailTemplates_delete(Request $request, $id)
    {
        $data = $request->all();
        // dd($data);
        $template = EmailTemplate::findOrFail($id);
        $template->delete($data);
        return redirect()->route('admin.pages.setup.emailtemplates.index')->with(['success' => 'An email template successfully deleted!']);
    }
    public function AddonsModule()
    {
        return view('pages.setup.addonsmodule.index');
    }
    public function ClientGroups()
    {
        return view('pages.setup.clientgroups.index');
    }
    public function CustomClientFields()
    {
        return view('pages.setup.customclientfields.index');
    }
    public function FraudProtection()
    {
        return view('pages.setup.fraudprotection.index');
    }
    public function Other_orderstatuses()
    {
        return view('pages.setup.other.orderstatuses.index');
    }
    public function Other_securityquestions()
    {
        return view('pages.setup.other.securityquestions.index');
    }
    public function Other_bannedips()
    {
        return view('pages.setup.other.bannedips.index');
    }
    public function Other_bannedemails()
    {
        return view('pages.setup.other.bannedemails.index');
    }
    public function Other_databasebackups()
    {
        return view('pages.setup.other.databasebackups.index');
    }

    public function configurableoptions(){

        return view('pages.setup.prodsservices.configurableoptions.index');
    }

    public function getConfigurableOptions(Request $request){
        $data=\App\Models\Productconfiggroup::select('*');

        return Datatables::of($data)
                ->toJson();
    }

    public function ConfigurableOptionsDestroy(Request $request){
        $id=(int) $request->id;
        //checkPermission("Delete Products/Services");
        $group =\App\Models\Productconfiggroup::find($id);

        $option=\App\Models\Productconfigoption::where('gid',$id)->get();
        foreach($option as $r){
            \App\Models\Productconfigoption::find($r->id)->delete();
            \App\Models\Productconfigoptionssub::where('configid',$r->id)->delete();
            \App\Models\Hostingconfigoption::where('configid',$r->id)->delete();
        }

        \App\Models\Productconfiggroup::find($id)->delete();
        \App\Models\Productconfiglink::where('gid', $id )->delete();


        LogActivity::save("Configurable Option Group Deleted - '" . $group->name . "' - Option Group ID: " . $id);
        return back()->with('success', 'Deleted Option Group successfully');  
    }


    public function ConfigurableOptions_add(){
        // checkPermission("Create New Products/Services");
        $product=\App\Helpers\Product::getProducts();
        $param=[
                    'product' => $product
                ];
        //dd($param);
        return view('pages.setup.prodsservices.configurableoptions.add',$param);
    }

    public function ConfigurableOptions_store(Request $request){
        //dd($request->all());
        $config=new \App\Models\Productconfiggroup();
        $config->name = $request->name;
        $config->description = $request->description;
        $config->save();
        $id=$config->id;
        LogActivity::save("Configurable Option Group Created: '" .  $request->name . "' - Option Group ID: " . $id);

        if(!empty($request->productlinks)){
            foreach($request->productlinks as $pid){
                $link = new \App\Models\Productconfiglink();
                $link->gid = $id;
                $link->pid = $pid;
                $link->save();
            }
        }

        //return back()->with('success', 'Created Option Group successfully'); 
        return redirect(url(request()->segment(1)."/setup/productservices/configurableoptions/edit/{$id}"))->with('success', 'Created Option Group successfully');
    }

    public function ConfigurableOptions_edit($id){
        $id=(int)$id;
        $data=\App\Models\Productconfiggroup::find($id);
        $links=\App\Models\Productconfiglink::where('gid',$id)->select('pid')->get();
        $link=array();
        foreach($links as $r){
            $link[]=$r->pid;
        }

        $product=\App\Helpers\Product::getProducts();
        $configoptions=\App\Models\Productconfigoption::where('gid',$id)->orderBy('id','ASC')->get();
        //dd($configoptions);
        $param=[
                    'data'          => $data,
                    'product'       => $product,
                    'link'          => $link,
                    'configoptions' => $configoptions
                ];
        //dd($param);
        return view('pages.setup.prodsservices.configurableoptions.edit',$param);
    }

    public function ConfigurableOptions_update(Request $request){
        //checkPermission("Edit Products/Services");
        //dd($request->all());
        $id=(int)$request->id;
        $data=\App\Models\Productconfiggroup::find($id);
        $data->name =$request->name;
        $data->description =$request->description;
        $data->save();

        if(isset($request->order)){
            foreach($request->order as $k => $v){
                $update=\App\Models\Productconfigoption::find($k);
                $update->order =$v;
                $update->save();
            }
        }

        if(isset($request->hidden)){
            $hidden=\App\Models\Productconfigoption::find($k);
            $hidden->hidden =$v;
            $hidden->save();
        }

        return back()->with('success', 'Update Option Group successfully'); 

    }

    public function ConfigurableOptionsManageoptions(Request $request){
        // checkPermission("Create New Products/Services");
        $error=true;
        $alert='';
        $data=array();
        $gid=(int)$request->gid;
        $cid=(int)$request->cid;
        $price=$request->price;
        $addoptionname=$request->addoptionname;
        $addhidden=$request->addhidden;
        $configoptionname=$request->configoptionname;

        $addsortorder=$request->addsortorder;
        //dd($request->all());
        $currencies=\App\Models\Currency::all();
        $currenciesarray=array();
        foreach($currencies as $r){
            $currenciesarray[$r->id]=$r->code;
        }
        $totalcurrencies = count($currenciesarray) * 2;
        //checkPermission("Edit Products/Services");
        if (!$cid) {
            $productConfigOption=new \App\Models\Productconfigoption();
            $productConfigOption->gid =$gid;
            $productConfigOption->optionname =$configoptionname ?? '';
            $productConfigOption->save();

            $cid=$productConfigOption->id;

            LogActivity::save("New Configurable Option Created: '" .  $request->optionname . "' - Option Group ID: " . $gid);
        }
        $optionname=null;
        $configOption = \App\Models\Productconfigoption::find($cid);
        $group =\App\Models\Productconfiggroup::find($configOption->gid);
        if (is_null($optionname)) {
            $optionname = array();
        }
        if (is_null($addoptionname)) {
            $addoptionname = array();
        }
        $configoptiontype = $request->configoptiontype;
        if($configoptionname != $configOption->optionname) {
            LogActivity::save("Configurable Option Modified: Name Changed: " . "'" . $configOption->optionname . "' to " . $configoptionname . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        if ($configoptiontype != $configOption->optiontype) {
            LogActivity::save("Configurable Option Modified: Type Changed: " . "'" . $configOption->optiontype . "' to " . $configoptiontype . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }

        $qtyminimum = (int) $request->qtyminimum;
        $qtymaximum = (int) $request->qtymaximum;
        if ($qtyminimum != $configOption->qtyminimum) {
            LogActivity::save("Configurable Option Modified: Qty Minimum Modified: " . "'" . $configOption->qtyminimum . "' to " . $qtyminimum . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        if ($qtymaximum != $configOption->qtymaximum) {
            LogActivity::save("Configurable Option Modified: Qty Maximum Modified: " . "'" . $configOption->qtymaximum . "' to " . $qtymaximum . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        
    
        \App\Models\Productconfigoption::find($cid)->update(["optionname" => $configoptionname, "optiontype" => $configoptiontype, "qtyminimum" => $qtyminimum, "qtymaximum" => $qtymaximum]);
        foreach ($optionname as $key => $value) {
            $subOption =\App\Models\Productconfigoptionssub::find($key);
            \App\Models\Productconfigoptionssub::find($key)->update(["optionname" => $value, "sortorder" => $sortorder[$key], "hidden" => $hidden[$key]]);
            if ($subOption->optionname != $value || $subOption->sortorder != $sortorder[$key] || $subOption->hidden != $hidden[$key]) {
                LogActivity::save("Configurable Option Modified - '" . $configoptionname . "' - Option Modified: '" . $value . "'" . " - Group: " . $group->name . " - Option Group ID: " . $group->id);
            }
        }
        
        if($price){
            $priceChanges = false;
            foreach ($price as $curr_id => $temp_values) {
                if ($priceChanges === false) {
                    $currentPricing =\App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->first();
                    if ($currentPricing->msetupfee != $values[1] || $currentPricing->qsetupfee != $values[2] || $currentPricing->ssetupfee != $values[3] || $currentPricing->asetupfee != $values[4] || $currentPricing->bsetupfee != $values[5] || $currentPricing->tsetupfee != $values[6] || $currentPricing->monthly != $values[7] || $currentPricing->quarterly != $values[8] || $currentPricing->semiannually != $values[9] || $currentPricing->annually != $values[10] || $currentPricing->biennially != $values[11] || $currentPricing->triennially != $values[12]) {
                        $priceChanges = true;
                    }
                }

                \App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->update(["msetupfee" => $values[1], "qsetupfee" => $values[2], "ssetupfee" => $values[3], "asetupfee" => $values[4], "bsetupfee" => $values[5], "tsetupfee" => $values[11], "monthly" => $values[6], "quarterly" => $values[7], "semiannually" => $values[8], "annually" => $values[9], "biennially" => $values[10], "triennially" => $values[12]]);
            }

            if ($priceChanges) {
                LogActivity::save("Configurable Option Pricing Modified: '" . $configoptionname . "'" . " - Group: " . $group->name . " - Option Group ID: " . $group->id);
            }
        }

        if ($addoptionname) {
            
            //new \App\Models\Productconfigoptionssub(["configid" => $cid, "optionname" => $addoptionname, "sortorder" => $addsortorder, "hidden" => $addhidden]);
            $create = new \App\Models\Productconfigoptionssub();
            $create->configid = $cid;
            $create->optionname = $addoptionname;
            $create->sortorder = $addsortorder;
            $create->hidden = $addhidden ?? 0;
            $create->save();

           
            LogActivity::save("Configurable Option Modified - '" . $configoptionname . "' - Option Added: '" . $addoptionname . "'" . " - Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        
        //dd($cid,'cid');
        // $currenciesarray
        $dataRespone=\App\Models\Productconfigoptionssub::where('configid',(int) $cid)->orderBy('sortorder','ASC')->get();
        //dd($dataRespone);
        $x=0;
        $return=array();
        foreach($dataRespone as $r){
            $x++;
            $optionid=$r->id;
            $firstcurrencydone = false;
            //dd($currenciesarray);
            foreach($currenciesarray as $curr_id => $curr_code){
                DB::enableQueryLog();
                $data=\App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->first();
               // dd(DB::getQueryLog());
                if(is_null($data)){
                    
                    $price= new \App\Models\Pricing();
                    $price->type        = 'configoptions';
                    $price->currency    =  $curr_id;
                    $price->relid       =  $optionid;
                    $price->save();
                    $data=\App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->first();
                }
                //dd($data);
                $val[1]     = $data->msetupfee;
                $val[2]     = $data->qsetupfee;
                $val[3]     = $data->ssetupfee;
                $val[4]     = $data->asetupfee;
                $val[5]     = $data->bsetupfee;
                $val[11]    = $data->tsetupfee;
                $val[6]     = $data->monthly;
                $val[7]     = $data->quarterly;
                $val[8]     = $data->semiannually;
                $val[9]     = $data->annually;
                $val[10]    = $data->biennially;
                $val[12]    = $data->triennially;

                $return[$curr_code]=$val;


            }


            $data=[
                        'id'            => $r->id,
                        'optionname'    => $r->optionname,
                        'sortorder'     => $r->sortorder,
                        'hidden'        => $r->hidden,
                        'price'         => $return,
                        'totalcurrencies' => $totalcurrencies
                    ];

        }
        $curren=array();
        foreach($currenciesarray as $curr_id => $curr_code){
            $curren[$curr_code] = $curr_id;
        }

        $productConfigOption=\App\Models\Productconfigoption::find($cid);

        $error=true;
        $alert='';
        $respone=[
                    'error' => false,
                    'alert' => 'Success Manage Data',
                    'data'  => $data,
                    'curren' => $curren,
                    'configoption' => $productConfigOption
                ];

        return response($respone);
    }

    public function poppup(Request $request){
        //dd($id);
        //dd($this->adminURL);
        $cid=(int)$request->input('cid');
        $gid=(int)$request->input('gid');
        $return=array();
      

        
        $configoptions=array();
        if($cid){
            $configoptions=\App\Models\Productconfigoption::find($cid);
           

            $currencies=\App\Models\Currency::all();
            $currenciesarray=array();
            foreach($currencies as $r){
                $currenciesarray[$r->id]=$r->code;
            }
            $dataRespone=\App\Models\Productconfigoptionssub::where('configid',(int) $cid)->orderBy('sortorder','ASC')->get();
            //dd($dataRespone);
             $x=0;
           
            foreach($dataRespone as $r){
                $x++;
                $optionid=$r->id;
                $firstcurrencydone = false;
                //dd($currenciesarray);
                $currency=array();
                foreach($currenciesarray as $curr_id => $curr_code){
                    //DB::enableQueryLog();
                    $data=\App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->first();
                    //dd(DB::getQueryLog());
                    if(is_null($data)){
                        
                        $price= new \App\Models\Pricing();
                        $price->type        = 'configoptions';
                        $price->currency    =  $curr_id;
                        $price->relid       =  $optionid;
                        $price->save();
                        $data=\App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->first();
                    }
                    //dd($data);
                    $val['curr_id']=$curr_id;
                    $val[1]     = $data->msetupfee;
                    $val[2]     = $data->qsetupfee;
                    $val[3]     = $data->ssetupfee;
                    $val[4]     = $data->asetupfee;
                    $val[5]     = $data->bsetupfee;
                    $val[11]    = $data->tsetupfee;
                    $val[6]     = $data->monthly;
                    $val[7]     = $data->quarterly;
                    $val[8]     = $data->semiannually;
                    $val[9]     = $data->annually;
                    $val[10]    = $data->biennially;
                    $val[12]    = $data->triennially;
                   
                    $currency[$curr_code]=$val;
                }

                $return[]=[
                            'id'            => $r->id,
                            'configid'      => $r->configid,
                            'optionname'    => $r->optionname,
                            'sortorder'     => $r->sortorder,
                            'hidden'        => $r->r,
                            'currency'      => $currency
                        ];

            }
        }
        //dd($return);

        $params=[
                    'gid'       => $gid,
                    'cid'       => $cid,
                    'data'      => $configoptions,
                    'config'    => $return
                ];
        //dd($params);
        return view('pages.setup.prodsservices.configurableoptions.popup',$params);
    }

    public function poppupsave(Request $request){
        //dd($request->all());

        
        $data=array();
        $gid=(int)$request->gid;
        $cid=null;
        $price=$request->price;
        $addoptionname=$request->addoptionname;
        $addhidden=$request->addhidden;
        $configoptionname=$request->configoptionname;

        $addsortorder=$request->addsortorder;
        //dd($request->all());

        $optionname=null;


        $currencies=\App\Models\Currency::all();
        $currenciesarray=array();
        foreach($currencies as $r){
            $currenciesarray[$r->id]=$r->code;
        }
        $totalcurrencies = count($currenciesarray) * 2;
        //checkPermission("Edit Products/Services");
        if (!$cid) {
            $productConfigOption=new \App\Models\Productconfigoption();
            $productConfigOption->gid =$gid;
            $productConfigOption->optionname =$configoptionname ?? '';
            $productConfigOption->save();

            $cid=$productConfigOption->id;

            LogActivity::save("New Configurable Option Created: '" .  $request->optionname . "' - Option Group ID: " . $gid);
        }
        
        $configOption = \App\Models\Productconfigoption::find($cid);
        $group =\App\Models\Productconfiggroup::find($configOption->gid);
        if (is_null($optionname)) {
            $optionname = array();
        }
        if (is_null($addoptionname)) {
            $addoptionname = array();
        }
        $configoptiontype = $request->configoptiontype;
        if($configoptionname != $configOption->optionname) {
            LogActivity::save("Configurable Option Modified: Name Changed: " . "'" . $configOption->optionname . "' to " . $configoptionname . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        if ($configoptiontype != $configOption->optiontype) {
            LogActivity::save("Configurable Option Modified: Type Changed: " . "'" . $configOption->optiontype . "' to " . $configoptiontype . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }

        $qtyminimum = (int) $request->qtyminimum;
        $qtymaximum = (int) $request->qtymaximum;
        if ($qtyminimum != $configOption->qtyminimum) {
            LogActivity::save("Configurable Option Modified: Qty Minimum Modified: " . "'" . $configOption->qtyminimum . "' to " . $qtyminimum . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        if ($qtymaximum != $configOption->qtymaximum) {
            LogActivity::save("Configurable Option Modified: Qty Maximum Modified: " . "'" . $configOption->qtymaximum . "' to " . $qtymaximum . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        
    
        \App\Models\Productconfigoption::find($cid)->update(["optionname" => $configoptionname, "optiontype" => $configoptiontype, "qtyminimum" => $qtyminimum, "qtymaximum" => $qtymaximum]);
        foreach ($optionname as $key => $value) {
            $subOption =\App\Models\Productconfigoptionssub::find($key);
            \App\Models\Productconfigoptionssub::find($key)->update(["optionname" => $value, "sortorder" => $sortorder[$key], "hidden" => $hidden[$key]]);
            if ($subOption->optionname != $value || $subOption->sortorder != $sortorder[$key] || $subOption->hidden != $hidden[$key]) {
                LogActivity::save("Configurable Option Modified - '" . $configoptionname . "' - Option Modified: '" . $value . "'" . " - Group: " . $group->name . " - Option Group ID: " . $group->id);
            }
        }
        
        if($price){
            $priceChanges = false;
            foreach ($price as $curr_id => $temp_values) {
                foreach ($temp_values as $optionid => $values) {
                if ($priceChanges === false) {
                    $currentPricing =\App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->first();
                    if ($currentPricing->msetupfee != $values[1] || $currentPricing->qsetupfee != $values[2] || $currentPricing->ssetupfee != $values[3] || $currentPricing->asetupfee != $values[4] || $currentPricing->bsetupfee != $values[5] || $currentPricing->tsetupfee != $values[6] || $currentPricing->monthly != $values[7] || $currentPricing->quarterly != $values[8] || $currentPricing->semiannually != $values[9] || $currentPricing->annually != $values[10] || $currentPricing->biennially != $values[11] || $currentPricing->triennially != $values[12]) {
                        $priceChanges = true;
                    }
                }

                \App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->update(["msetupfee" => $values[1], "qsetupfee" => $values[2], "ssetupfee" => $values[3], "asetupfee" => $values[4], "bsetupfee" => $values[5], "tsetupfee" => $values[11], "monthly" => $values[6], "quarterly" => $values[7], "semiannually" => $values[8], "annually" => $values[9], "biennially" => $values[10], "triennially" => $values[12]]);
                }
            }

            if ($priceChanges) {
                LogActivity::save("Configurable Option Pricing Modified: '" . $configoptionname . "'" . " - Group: " . $group->name . " - Option Group ID: " . $group->id);
            }
        } 
        



        //if ($$request->addoptionname) {
            
            //new \App\Models\Productconfigoptionssub(["configid" => $cid, "optionname" => $addoptionname, "sortorder" => $addsortorder, "hidden" => $addhidden]);
            $create = new \App\Models\Productconfigoptionssub();
            $create->configid = $cid;
            $create->optionname = $request->addoptionname ?? '';
            $create->sortorder = $addsortorder ?? 0 ;
            $create->hidden = $addhidden ?? 0;
            $create->save();

           
            LogActivity::save("Configurable Option Modified - '" . $configoptionname . "' - Option Added: '" . $addoptionname . "'" . " - Group: " . $group->name . " - Option Group ID: " . $group->id);
       // }

        $dataRespone=\App\Models\Productconfigoptionssub::where('configid',(int) $cid)->orderBy('sortorder','ASC')->get();
        //dd($dataRespone);
        $x=0;
        $return=array();
        foreach($dataRespone as $r){
            $x++;
            $optionid=$r->id;
            $firstcurrencydone = false;
            //dd($currenciesarray);
            foreach($currenciesarray as $curr_id => $curr_code){
                //DB::enableQueryLog();
                $data=\App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->first();
               // dd(DB::getQueryLog());
                if(is_null($data)){
                    
                    $price= new \App\Models\Pricing();
                    $price->type        = 'configoptions';
                    $price->currency    =  $curr_id;
                    $price->relid       =  $optionid;
                    $price->save();
                }
            }
            
        }




        return redirect($this->adminURL.'setup/productservices/configurableoptions/poppup?cid='.$cid);

    }



    public function poppupupdate(Request $request){
        //dd($request->all());
        $data=array();
        $gid=(int)$request->gid;
        $cid=(int)$request->cid;
        $price=$request->price;
        $addoptionname=$request->addoptionname;
        $addhidden=$request->addhidden;
        $configoptionname=$request->configoptionname;

        $addsortorder=$request->addsortorder;
        //dd($request->all());

        $optionname=null;


        $currencies=\App\Models\Currency::all();
        $currenciesarray=array();
        foreach($currencies as $r){
            $currenciesarray[$r->id]=$r->code;
        }
        $totalcurrencies = count($currenciesarray) * 2;
        //checkPermission("Edit Products/Services");
        if (!$cid) {
            $productConfigOption=new \App\Models\Productconfigoption();
            $productConfigOption->gid =$gid;
            $productConfigOption->optionname =$configoptionname ?? '';
            $productConfigOption->save();

            $cid=$productConfigOption->id;

            LogActivity::save("New Configurable Option Created: '" .  $request->optionname . "' - Option Group ID: " . $gid);
        }
        
        $configOption = \App\Models\Productconfigoption::find($cid);
        //dd($configOption);
        $group =\App\Models\Productconfiggroup::find($configOption->gid);
        if (is_null($optionname)) {
            $optionname = array();
        }
        if (is_null($addoptionname)) {
            $addoptionname = array();
        }
        $configoptiontype = $request->configoptiontype;
        if($configoptionname != $configOption->optionname) {
            LogActivity::save("Configurable Option Modified: Name Changed: " . "'" . $configOption->optionname . "' to " . $configoptionname . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        if ($configoptiontype != $configOption->optiontype) {
            LogActivity::save("Configurable Option Modified: Type Changed: " . "'" . $configOption->optiontype . "' to " . $configoptiontype . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }

        $qtyminimum = (int) $request->qtyminimum;
        $qtymaximum = (int) $request->qtymaximum;
        if ($qtyminimum != $configOption->qtyminimum) {
            LogActivity::save("Configurable Option Modified: Qty Minimum Modified: " . "'" . $configOption->qtyminimum . "' to " . $qtyminimum . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        if ($qtymaximum != $configOption->qtymaximum) {
            LogActivity::save("Configurable Option Modified: Qty Maximum Modified: " . "'" . $configOption->qtymaximum . "' to " . $qtymaximum . "' - " . "Group: " . $group->name . " - Option Group ID: " . $group->id);
        }
        
    
        \App\Models\Productconfigoption::find($cid)->update(["optionname" => $configoptionname, "optiontype" => $configoptiontype, "qtyminimum" => $qtyminimum, "qtymaximum" => $qtymaximum]);
        foreach ($optionname as $key => $value) {
            $subOption =\App\Models\Productconfigoptionssub::find($key);
            \App\Models\Productconfigoptionssub::find($key)->update(["optionname" => $value, "sortorder" => $sortorder[$key], "hidden" => $hidden[$key]]);
            if ($subOption->optionname != $value || $subOption->sortorder != $sortorder[$key] || $subOption->hidden != $hidden[$key]) {
                LogActivity::save("Configurable Option Modified - '" . $configoptionname . "' - Option Modified: '" . $value . "'" . " - Group: " . $group->name . " - Option Group ID: " . $group->id);
            }
        }
        
        if($price){
            $priceChanges = false;
            foreach ($price as $curr_id => $temp_values) {
                foreach ($temp_values as $optionid => $values) {
                if ($priceChanges === false) {
                    $currentPricing =\App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->first();
                    if ($currentPricing->msetupfee != $values[1] || $currentPricing->qsetupfee != $values[2] || $currentPricing->ssetupfee != $values[3] || $currentPricing->asetupfee != $values[4] || $currentPricing->bsetupfee != $values[5] || $currentPricing->tsetupfee != $values[6] || $currentPricing->monthly != $values[7] || $currentPricing->quarterly != $values[8] || $currentPricing->semiannually != $values[9] || $currentPricing->annually != $values[10] || $currentPricing->biennially != $values[11] || $currentPricing->triennially != $values[12]) {
                        $priceChanges = true;
                    }
                }
                \App\Models\Pricing::where('type','configoptions')->where('currency',$curr_id)->where('relid',$optionid)->update(["msetupfee" => $values[1], "qsetupfee" => $values[2], "ssetupfee" => $values[3], "asetupfee" => $values[4], "bsetupfee" => $values[5], "tsetupfee" => $values[11], "monthly" => $values[6], "quarterly" => $values[7], "semiannually" => $values[8], "annually" => $values[9], "biennially" => $values[10], "triennially" => $values[12]]);
                }
            }

            if ($priceChanges) {
                LogActivity::save("Configurable Option Pricing Modified: '" . $configoptionname . "'" . " - Group: " . $group->name . " - Option Group ID: " . $group->id);
            }
        }

        if ($addoptionname) {
            
            //new \App\Models\Productconfigoptionssub(["configid" => $cid, "optionname" => $addoptionname, "sortorder" => $addsortorder, "hidden" => $addhidden]);
            $create = new \App\Models\Productconfigoptionssub();
            $create->configid = $cid;
            $create->optionname = $addoptionname ?? '';
            $create->sortorder = $addsortorder ?? 0 ;
            $create->hidden = $addhidden ?? 0;
            $create->save();

           
            LogActivity::save("Configurable Option Modified - '" . $configoptionname . "' - Option Added: '" . $addoptionname . "'" . " - Group: " . $group->name . " - Option Group ID: " . $group->id);
        }

        return redirect($this->adminURL.'setup/productservices/configurableoptions/poppup?cid='.$cid);

    }


    public function ConfigurableOptionsManageoptionsDestroy(Request $request){
       
        $id=(int)$request->id;
        $opid=(int)$request->opid;
        $group=\App\Models\Productconfiggroup::find($id);
        $option =\App\Models\Productconfigoption::find($opid);
        
        \App\Models\Productconfigoption::find($opid)->delete();
        \App\Models\Productconfigoptionssub::where('configid', $opid)->delete();
        \App\Models\Hostingconfigoption::where('configid', $opid)->delete();

        logActivity::save("Configurable Option Group Modified - '" . $group->name . "' - Option Removed: '" . $option->optionname . "'" . " - Option Group ID: " . $id);
        
        return json_encode(['error' =>false , 'alert' => 'Success Delete  Configurable Options ' ]);
    }

    public function Duplicategroup(){
        $data=\App\Models\Productconfiggroup::all();
        //dd($data);
        $param=['data' => $data ];
        return view('pages.setup.prodsservices.configurableoptions.duplicategroup',$param);
    }


    public function DuplicategroupStore(Request $request){
        //dd($request->all());
        //checkPermission("Create New Products/Services");
        $id=(int)$request->existinggroupid;

        $oldGroub=\App\Models\Productconfiggroup::find($id);
        $oldGroupName=$oldGroub->name;
        /* $oldGroub=collect($oldGroub)->map(function ($item) {
            //dd($item);
            return $item;            


        }); */
        $newgroupname=$request->newgroupname;

        /* //dd($oldGroub);
        $addstr = "";
        foreach($oldGroub as $key => $value){
            //print_r( $value);
            //if (is_numeric($key)) {
                if ($key == "id") {
                    $value = "";
                }
                if ($key == "name") {
                    $value = $newgroupname;
                }
                $addstr .= "'" . $value. "',";
           // }
        }
        $addstr = substr($addstr, 0, -1);
        dd($addstr); */

        $data1=new \App\Models\Productconfiggroup();
        $data1->name = $newgroupname;
        $data1->description = $oldGroub->description;
        $data1->save();
        $newgroupid =$data1->id;

        $step2=\App\Models\Productconfigoption::where('gid', $id )->get();
        foreach($step2 as $r ){
            $save=new \App\Models\Productconfigoption();
            $save->gid =$newgroupid;
            $save->optionname =$r->optionname;
            $save->optiontype =$r->optiontype;
            $save->qtyminimum =$r->qtyminimum;
            $save->qtymaximum =$r->qtymaximum;
            $save->order =$r->order;
            $save->hidden =$r->hidden;
            $save->save();
            $newoptionid = $save->id;

            $price=\App\Models\Pricing::where('type','configoptions')->where('relid',$newgroupid)->get();
            foreach($price as $ra){
                $p=new \App\Models\Pricing();
                $p->type =$ra->type;
                $p->currency =$ra->currency;
                $p->relid   =$newoptionid;
                $p->msetupfee   =$ra->msetupfee;
                $p->qsetupfee   =$ra->qsetupfee;
                $p->ssetupfee   =$ra->ssetupfee;
                $p->asetupfee   =$ra->asetupfee;
                $p->bsetupfee   =$ra->bsetupfee;
                $p->monthly   =$ra->monthly;
                $p->quarterly   =$ra->quarterly;
                $p->semiannually   =$ra->semiannually;
                $p->annually   =$ra->annually;
                $p->biennially   =$ra->biennially;
                $p->triennially   =$ra->triennially;
                $p->save();
            }


        }

        logActivity::save("Configurable Option Group Duplicated: '" . $oldGroupName . "' to '" . $newgroupname . "' - Option Group ID: " . $newgroupid);
        return back()->with('success', 'Configurable Option Group Duplicated successfully'); 
    }


}

