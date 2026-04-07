<?php

namespace App\Http\Controllers\Admin\Client;

use App\Helpers\Cfg;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\Client as HelpersClient;
use App\Helpers\ClientHelper;
use App\Helpers\Customfield;
use App\Helpers\Gateway;
use App\Helpers\Hooks;
use App\Helpers\LogActivity;
use App\Helpers\Password;
use App\Helpers\Pwd;

// Models
use App\Models\AdminSecurityQuestion;
use App\Models\Client;
use App\Models\Clientgroup;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\Note;


// Traits
use App\Traits\DatatableFilter;

class ClientProfileController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }
    
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.index")
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> ') .__('admin.clientsinvalidclientid'));
        }
        
        $userid = $request->userid;
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);

        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["emailPref"] = json_decode($clientsdetails["email_preferences"]);
        $templatevars["clientsdetails"]["securityqans"] = (new Pwd())->decrypt($clientsdetails["securityqans"]);
        $templatevars["userid"] = $userid;

        // Clientdetail custom data
        $client = $clientsdetails["model"];
        $templatevars["marketingEmailsOptIn"] = $client->isOptedInToMarketingEmails();
        $templatevars["password"] = __("admin.fieldsentertochange");
        $templatevars["questions"] = (new HelpersClient())->getSecurityQuestions();
        $templatevars["countries"] = (new HelpersClient())->getCountries();
        $templatevars["languages"] = (new HelpersClient())->getAvailableLanguages();
        $templatevars["paymentmethodlist"] = (new Gateway($request))->paymentMethodsList();

        // BIlling Contact
        // $result = select_query("tblcontacts", "", array("userid" => $userid), "firstname` ASC,`lastname", "ASC");
        // Note: From decode version why use $userid as filter? 
        $billingContacts = Contact::where("userid", $userid)->orderBy("firstname", "ASC")->get();
        $templatevars["billingcontacts"] = $billingContacts;

        // Currency
        // $result = select_query("tblcurrencies", "id,code", "", "code", "ASC");
        $currencies = Currency::select("id", "code")->orderBy("code", "ASC")->get();
        $templatevars["currencies"] = $currencies;

        // Client Group
        $templatevars["clientgroups"] = ClientHelper::getClientGroups();

        /*
        TODO: remoteAuth
        $remoteAuth = new WHMCS\Authentication\Remote\RemoteAuth();
        foreach ($client->remoteAccountLinks()->get() as $remoteAccountLink) {
            $provider = $remoteAuth->getProviderByName($remoteAccountLink->provider);
            $remoteAccountLinks[$remoteAccountLink->id] = $provider->parseMetadata($remoteAccountLink->metadata);
        }
        */

        // Custom Field
        $customfields = Customfield::getCustomFields("client", "", $userid, "on", "");
        $templatevars["customfields"] = $customfields;
        $templatevars["notesCount"] = Note::where('userid', $userid)->count();

        // dd($templatevars);

        return view('pages.clients.viewclients.clientprofile.index', $templatevars);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'firstname' => "required|string",
            'lastname' => "nullable|string",
            'companyname' => "nullable|string",
            "email" => "required|string|email|unique:App\Models\Client,email,".$request->userid,
            "password" => "nullable",
            "securityqid" => $request->securityqid != 0 ? "required_with:securityqans|string" : "nullable|numeric",
            "securityqans" => $request->securityqid != 0 ? "required_with:securityqid|string" : "nullable|string",
            "tax_id" => "nullable|string",
            "address1" => "required|string",
            "address2" => "nullable|string",
            "city" => "required|string",
            "state" => "required|string",
            "postcode" => "nullable|numeric",
            "country" => "required|string|max:2",
            "phonenumber" => "required|string|max:14|min:8",
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
        
        $userid = $request->userid;

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientprofile.index", ["userid" => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }
        
        $client = Client::findOrFail($userid);

        // Check the email preferences format
        $emailPreferences = [];
        if ($request->has("email_preferences")) {
            $emailPreferences = $request->email_preferences;

            try {
                $client->validateEmailPreferences($emailPreferences);
            } catch (\App\Exceptions\Validation\Required $e) {
                return redirect()
                    ->route("admin.pages.clients.viewclients.clientprofile.index", ["userid" => $userid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('admin.emailPreferencesoneRequired') ." " .__($e->getMessage()));

            } catch (Exception $e) {
                throw $e->getMessage();
            }
        }

        $defaultPref = $client->getEmailPreferencesDefault();
        $emailPreferences = array_merge($defaultPref, $emailPreferences);

        $oldclientsdetails = (new HelpersClient())->DataClientsDetails($userid);

        $emailWasUpdated = false;
        if ($request->email != $oldclientsdetails["email"]) {
            $emailWasUpdated = true;
        }

        $uuid = "";
        if (empty($oldclientsdetails["uuid"])) {
            $uuid = \Ramsey\Uuid\Uuid::uuid4();
            $uuid = $uuid->toString();
        } else {
            $uuid = $oldclientsdetails["uuid"];
        }

        // TODO: formatPostedPhoneNumber
        // $request->phonenumber = App::formatPostedPhoneNumber();

        $array = [
            "uuid" => $uuid,
            "firstname" => $request->firstname,
            "lastname" => $request->lastname,
            "companyname" => $request->companyname,
            "email" => $request->email,
            "address1" => $request->address1,
            "address2" => $request->address2,
            "city" => $request->city,
            "state" => $request->state,
            "postcode" => $request->postcode,
            "country" => $request->country,
            "phonenumber" => $request->phonenumber,
            "tax_id" => $request->tax_id,
            "currency" => $request->currency,
            "notes" => $request->notes,
            "status" => $request->clientstatus,
            "taxexempt" => (bool) $request->taxexempt,
            "latefeeoveride" => (bool) $request->latefeeoveride,
            "overideduenotices" => (bool) $request->overideduenotices,
            "separateinvoices" => (bool) $request->separateinvoices,
            "disableautocc" => (bool) $request->disableautocc,
            "overrideautoclose" => (bool) $request->overrideautoclose,
            "language" => $request->language,
            "billingcid" => $request->billingcid,
            "securityqid" => $request->securityqid,
            "securityqans" => $request->securityqid != "0" ? (new Pwd())->encrypt($request->securityqans) : "",
            "groupid" => $request->groupid,
            "email_preferences" => $emailPreferences ? json_encode($emailPreferences, JSON_NUMERIC_CHECK) : null,
            "allow_sso" => (bool)  $request->allow_sso,
        ];

        if (!$request->twofaenabled) {
            $array["authmodule"] = "";
            $array["authdata"] = "";
        }

        $emailUpdated = "";
        if ($emailWasUpdated) {
            $array["email_verified"] = 0;
            if (Cfg::get("EnableEmailVerification")) {
                // $queryString .= "emailUpdated=true&";
                $emailUpdated = "<a href=\"#\" id=\"hrefEmailVerificationSendNew\">" .__("admin.generalemailVerificationSendNew") . "</a>";
            }
        }

        if (Cfg::get("DisableClientEmailPreferences")) {
            $array["email_preferences"] = Client::$emailPreferencesDefaults ? json_encode(Client::$emailPreferencesDefaults) : null;
        }

        $changedpw = false;
        if ($request->password) {
            $hasher = new Password();
            $array["password"] = $hasher->hash($request->password);
            $changedpw = true;

            Hooks::run_hook("ClientChangePassword", ["userid" => $userid, "password" => $request->password]);
        }
        
        foreach ($array as $key => $value) {
            $client->{$key} = $value;
        }
        $client->save();

        // TODO: Check for Custom Fields
        $customfields = Customfield::getCustomFields("client", "", $userid, "on", "");
        $customfieldInput = $request->customfield;
        foreach ($customfields as $v) {
            if ($customfieldInput) {
                $k = $v["id"];
                $customfieldsarray[$k] = $customfieldInput[$k] ?? null;
            }
        }

        $updatefieldsarray = [
            "firstname" => "First Name", 
            "lastname" => "Last Name", 
            "companyname" => "Company Name", 
            "email" => "Email Address", 
            "address1" => "Address 1", 
            "address2" => "Address 2", 
            "city" => "City", 
            "state" => "State", 
            "postcode" => "Postcode", 
            "country" => "Country", 
            "phonenumber" => "Phone Number", 
            "tax_id" => "Tax ID", 
            "securityqid" => "Security Question", 
            "billingcid" => "Billing Contact", 
            "groupid" => "Client Group", 
            "language" => "Language", 
            "currency" => "Currency", 
            "status" => "Status"
        ];

        $updatedtickboxarray = [
            "latefeeoveride" => "Late Fees Override", 
            "overideduenotices" => "Overdue Notices", 
            "taxexempt" => "Tax Exempt", 
            "separateinvoices" => "Separate Invoices", 
            "disableautocc" => "Disable CC Processing", 
            "overrideautoclose" => "Auto Close"
        ];

        $changelist = [];
        foreach ($updatefieldsarray as $field => $displayname) {
            if ( array_key_exists($field, $oldclientsdetails) ) {
                $oldvalue = $oldclientsdetails[$field];
                $newvalue = $array[$field];

                if ($field == "phonenumber" && $newvalue) {
                    $newvalue = $request->phonenumber;  // str_replace(array(" ", "-"), "", App::formatPostedPhoneNumber());
                    $oldvalue = $oldclientsdetails["phonenumberformatted"];
                }

                if ($newvalue != $oldvalue) {
                    $log = true;

                    if ($field == "groupid") {
                        $oldvalue = $oldvalue ? Clientgroup::select("groupname")->where("id", $oldvalue)->value("groupname") : __("admin.none");
                        $newvalue = $newvalue ? Clientgroup::select("groupname")->where("id", $newvalue)->value("groupname") : __("admin.none");
                    } else if ($field == "currency") {
                        $oldvalue = Currency::select("code")->where("id", $oldvalue)->value("code");
                        $newvalue = Currency::select("code")->where("id", $newvalue)->value("code");
                    } else if ($field == "securityqid") {
                        $oldvalue = (new Pwd())->decrypt(AdminSecurityQuestion::select("question")->where("id", $oldvalue)->value("question"));
                        $newvalue = (new Pwd())->decrypt(AdminSecurityQuestion::select("question")->where("id", $newvalue)->value("question"));
                        
                        if ($oldvalue == $newvalue) $log = false;
                    }

                    if ($log) {
                        $changelist[] = "$displayname: '$oldvalue' to '$newvalue'";
                    }
                }

                if ($field == "securityqid" && $request->securityqans && ($request->securityqans != (new Pwd())->decrypt($oldclientsdetails["securityqans"]))) {
                    $changelist[] = "Security Question Answer Changed";
                }
            }
        }

        foreach ($updatedtickboxarray as $field => $displayname) {
            if ( array_key_exists($field, $oldclientsdetails) ) {
                if ($field == "overideduenotices") {
                    $oldfield = $oldclientsdetails[$field] ? "Disabled" : "Enabled";
                    $newfield = $array[$field] ? "Disabled" : "Enabled";
                } else {
                    $oldfield = $oldclientsdetails[$field] ? "Enabled" : "Disabled";
                    $newfield = $array[$field] ? "Enabled" : "Disabled";
                }

                if ($oldfield != $newfield) {
                    $changelist[] = "$displayname: '$oldfield' to '$newfield'";
                }
            }
        }

        $marketing_emails_opt_in = (int) $request->marketing_emails_opt_in;

        if ($client->isOptedInToMarketingEmails() && !$marketing_emails_opt_in) {
            $client->marketingEmailOptOut();
            $changelist[] = "Opted Out of Marketing Emails";
        } else if (!$client->isOptedInToMarketingEmails() && $marketing_emails_opt_in) {
            $client->marketingEmailOptIn();
            $changelist[] = "Opted In to Marketing Emails";
        }

        $oldEmailPref = json_decode($oldclientsdetails["email_preferences"]);
        if ($oldEmailPref) {
            unset($oldclientsdetails["email_preferences"]);
    
            foreach ($oldEmailPref as $key => $value) {
                $oldclientsdetails["email_preferences"][$key] = $value;
            }
        }
        
        if (!Cfg::get("DisableClientEmailPreferences")) {
            $emailPreferencesChanges = [];
            unset($array["email_preferences"]);

            if ($emailPreferences) {
                foreach ($emailPreferences as $type => $value) {
                    try {
                        $array["email_preferences"][$type] = (int) $value;
                        if ((int) $oldclientsdetails["email_preferences"][$type] != (int) $value) {
                            $suffixText = "Disabled";
                            if ($value) {
                                $suffixText = "Enabled";
                            }
                            
                            $emailPreferencesChanges[] = ucfirst($type) . " Emails " . $suffixText;
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
                
            if (0 < count($emailPreferencesChanges)) {
                $changelist[] = "Email Preferences Updated: " . implode(", ", $emailPreferencesChanges);
            }
        }
        
        $gatewayclass = new \App\Module\Gateway();
        $paymentmethod = $request->paymentmethod ?? $gatewayclass->getFirstAvailableGateway();
        (new HelpersClient())->clientChangeDefaultGateway($userid, $paymentmethod);
        if ($oldclientsdetails["defaultgateway"] != $paymentmethod) {
            $changelist[] = "Default Payment Method: '{$oldclientsdetails["defaultgateway"]}' to '$paymentmethod'";
        }

        if ($changedpw) {
            $changelist[] = "Password Changed";
        }

        $twofaenabled = (bool) $request->twofaenabled;
        if (!$twofaenabled && $oldclientsdetails["twofaenabled"] == true) {
            $changelist[] = "Disabled Two-Factor Authentication";
        }

        // TODO: Check for Custom Fields
        // $customfields = $request->customfield;
        foreach ($customfields as $customfield) {
            $fieldid = $customfield["id"];
            if (isset($customfieldsarray)) {
                if ($customfield["rawvalue"] != $customfieldsarray[$fieldid]) {
                    $changelist[] = "Custom Field {$customfield["name"]} : '{$customfield["rawvalue"]}' to '{$customfieldsarray[$fieldid]}'";
                }
            }
        }
        
        if (isset($customfieldsarray)) {
            Customfield::SaveCustomFields($userid, $customfieldsarray, "client", true);
        }

        if (!count($changelist)) {
            $changelist[] = "No Changes";
        }

        $changeLog = implode(", ", $changelist);
        LogActivity::Save("Client Profile Modified - $changeLog - User ID: $userid", $userid);
        Hooks::run_hook("AdminClientProfileTabFieldsSave", $request->all());

        if (Cfg::get("TaxEUTaxValidation")) {
            $taxExempt = \App\Helpers\Vat::setTaxExempt($client);
            $client->save();

            if ($taxExempt != $array["taxexempt"]) {
                $array["taxexempt"] = $taxExempt;
            }
        }

        Hooks::run_hook("ClientEdit", array_merge([
            "userid" => $userid, 
            "isOptedInToMarketingEmails" => $client->isOptedInToMarketingEmails(), 
            "olddata" => $oldclientsdetails], 
            $array
        ));

        return redirect()
                ->route('admin.pages.clients.viewclients.clientprofile.index', ['userid' => $userid])
                ->with('type', 'success')
                ->with('message', __("<b>Well Done!</b> The data has been successfully updated.<br>Changelog: $changeLog")  );

    }

}
