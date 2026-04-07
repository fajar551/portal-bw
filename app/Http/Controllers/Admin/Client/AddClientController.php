<?php

namespace App\Http\Controllers\Admin\Client;

use App\Events\ClientAreaRegister;
use App\Http\Controllers\Controller;

use App\Helpers\Client as HelpersClient;
use App\Helpers\ClientHelper;
use App\Helpers\Country;
use App\Helpers\Customfield;
use App\Helpers\Database;
use App\Helpers\Gateway;
use App\Hooks\Registervanewclient;
use App\Models\Contact;
use App\Models\Currency;
use App\Module\Gateway as ModuleGateway;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Traits\DatatableFilter;

class AddClientController extends Controller
{
    use DatatableFilter;
    protected $prefix;
    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = Database::prefix();
    }

    public function index(Request $request)
    {
        $clientHelper = new HelpersClient();
        $gatewayclass = new ModuleGateway();
        $customfields = Customfield::getCustomFields("client", "", $userid = null, "on", "");

        $templatevars["customfields"] = $customfields;
        $templatevars["questions"] = $clientHelper->getSecurityQuestions();
        $templatevars["countries"] = (new Country())->getCountryNameArray();
        $templatevars["languages"] = $clientHelper->getAvailableLanguages();
        $templatevars["clientstatus"] = $clientHelper->getClientStatus();
        $templatevars["gateway"] = $gatewayclass->getFirstAvailableGateway();
        $templatevars["gateways"] = Gateway::GetGatewaysArray();
        $templatevars["paymentmethodlist"] = (new Gateway($request))->paymentMethodsList();
        $templatevars["billingcontacts"] = Contact::orderBy("firstname", "ASC")->get();
        $templatevars["currencies"] = Currency::select("id", "code")->orderBy("code", "ASC")->get();
        $templatevars["clientgroups"] = ClientHelper::getClientGroups();

        // dd($templatevars);

        return view('pages.clients.addnewclient.index', $templatevars);
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        // try {
            $validator = Validator::make($request->all(), [
                'firstname' => "required|string",
                'lastname' => "nullable|string",
                'companyname' => "nullable|string",
                "email" => "required|string|email|unique:App\Models\Client,email|unique:App\Models\Contact,email",
                "password" => "required",
                "securityqid" => $request->securityqid != 0 ? "required_with:securityqans|string" : "nullable|numeric",
                "securityqans" => $request->securityqid != 0 ? "required_with:securityqid|string" : "nullable|string",
                "tax_id" => "nullable|string",
                "address1" => "required|string",
                "address2" => "nullable|string",
                "city" => "required|string",
                "state" => "required|string",
                "postcode" => "required|numeric",
                "country" => "required|string|max:2",
                "phonenumber" => "nullable|string|max:14|min:8",
                "paymentmethod" => "nullable|string",
                "billingcid" => "nullable|numeric",
                "language" => "required|string",
                "clientstatus" => "required|in:Active,Inactive,Closed",
                "currency" => "required|exists:\App\Models\Currency,id",
                "groupid" => $request->groupid > 0 ? "required|exists:\App\Models\Clientgroup,id" : "",
                "latefeeoveride" => $request->latefeeoveride ? "required|in:0,1" : "nullable",
                "overideduenotices" => $request->overideduenotices ? "required|in:0,1" : "nullable",
                "taxexempt" => $request->taxexempt ? "required|in:0,1" : "nullable",
                "separateinvoices" => $request->separateinvoices ? "required|in:0,1" : "nullable",
                "disableautocc" => $request->disableautocc ? "required|in:0,1" : "nullable",
                "marketing_emails_opt_in" => $request->marketing_emails_opt_in ? "required|in:0,1" : "nullable",
                "overrideautoclose" => $request->overrideautoclose ? "required|in:0,1" : "nullable",
                "allow_sso" => $request->allow_sso ? "required|in:0,1" : "nullable",
                "notes" => "nullable|string",
                "twofaenabled" => $request->twofaenabled ? "required|in:0,1" : "nullable",
                "email_preferences" => "nullable|array",
                "email_preferences.*" => $request->email_preferences ? "required|numeric|in:0,1" : "",
            ]);
    
            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
            }
    
            $gatewayclass = new ModuleGateway();
            $customfields = Customfield::getCustomFields("client", "", $userid = null, "on", "");
            $customfieldInput = $request->customfield;
            $customfieldsarray = array_map(function ($v) use ($customfieldInput) {
                return $customfieldInput[$v["id"]] ?? null;
            }, $customfields);
    
            extract($request->all());
    
            $additionalData = [
                "notes" => $notes,
                "status" => $clientstatus,
                "taxexempt" => $taxexempt ?? null,
                "latefeeoveride" => $latefeeoveride ?? 0,
                "overideduenotices" => $overideduenotices ?? 0,
                "language" => $language,
                "billingcid" => $billingcid,
                "lastlogin" => "00000000000000",
                "groupid" => $groupid,
                "separateinvoices" => $separateinvoices ?? 0,
                "disableautocc" => $disableautocc ?? 0,
                "defaultgateway" => $paymentmethod ?? $gatewayclass->getFirstAvailableGateway(),
                "emailoptout" => isset($marketing_emails_opt_in) ? !$marketing_emails_opt_in : 0,
                "overrideautoclose" => $overrideautoclose ?? 0,
                "allow_sso" => $allow_sso ?? 0,
                "credit" => (double) $credit ?? null,
                "tax_id" => $tax_id,
                "email_preferences" => $email_preferences ?? null,
            ];
    
            $phonenumber = \App\Helpers\Application::formatPostedPhoneNumber();
    
            $response = (new HelpersClient())->AddClient2(
                $firstname,
                $lastname,
                $companyname ?? "",
                $email,
                $address1,
                $address2 ?? "-",
                $city,
                $state,
                $postcode,
                $country,
                $phonenumber ?? "",
                $password,
                $securityqid ?? 0,
                $securityqans,
                $sendemail ?? false,
                $additionalData,
                "",
                true,
                $marketing_emails_opt_in ?? 0
            );
            \Log::info('AddClient2 response:', $response);

    
            if ($response["result"] == "error") {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Something went wrong, please try again later.<br>Message: ' . $response["message"]));
            }

            Customfield::SaveCustomFields($response["clientid"], $customfieldsarray, "client", true);
            DB::commit();

            $hookRegisterNewClient = new Registervanewclient();
            $request = new \Illuminate\Http\Request(['userid' => $response["clientid"]]);
            $result = $hookRegisterNewClient->handle($request);  

            if ($result) {
                return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $response["clientid"]])
                    ->with('type', 'success')
                    ->with('message', __('<b>Well Done!</b> The client has been created successfully.'));
            } else {
                DB::rollback();
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Something went wrong, please try again later.<br>Message: ' . $result));
            }

        // } catch (\Exception $e) {
        //     DB::rollback();
        //     return redirect()
        //         ->back()
        //         ->withInput()
        //         ->with('type', 'danger')
        //         ->with('message', __('<b>Oh No!</b> Something went wrong, please try again later.<br>Message: ' . $e->getMessage()));
        // }
    }    
}
