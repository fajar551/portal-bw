<?php
namespace App\Helpers;

// Import Model Class here

use App\Events\PreRegistrar;
use App\Exceptions\Fatal;
use App\Models\Client as ClientModel;
use App\Models\Hosting;
use App\Models\Cancelrequest;
use App\Models\Pricing;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use App\Models\Domain;
use App\Models\Hostingaddon;

use App\Helpers\Database;
use App\Helpers\Cfg;
use App\Helpers\AdminFunctions;
use App\Helpers\Hooks;
use App\Helpers\Gateway;
use App\Helpers\Vat;
use App\Helpers\Password;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Registrar;
use App\Module\Registrar as ModuleRegistrar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Ramsey\Uuid\Uuid;

class Client
{
	protected $request;
    protected $password;
    protected $prefix;
    
    public function __construct()
	{
		$this->password = new Password();
        $this->prefix = Database::prefix();
	}
	
	public function safe_serialize($data) 
{
    if (function_exists("mb_internal_encoding") && (int) ini_get("mbstring.func_overload") & 2) {
        $mbIntEnc = mb_internal_encoding();
        mb_internal_encoding("ASCII");
    }

    try {
        if (is_null($data)) {
            $out = '';
        } else if (is_array($data) || is_object($data)) {
            // Serialize array/object data
            $out = serialize($data);
            
            // Validate serialized data
            if ($out === false) {
                throw new \Exception("Failed to serialize data");
            }
            
            // Verify data can be unserialized
            $test = @unserialize($out);
            if ($test === false) {
                throw new \Exception("Serialized data failed validation");
            }
        } else {
            // Convert scalar values to string
            $out = (string) $data;
        }
    } catch (\Exception $e) {
        LogActivity::Save("Serialization Error: " . $e->getMessage());
        $out = '';
    }

    // Restore original encoding if changed
    if (isset($mbIntEnc)) {
        mb_internal_encoding($mbIntEnc);
    }

    return $out;
}

    public function AddClient2(
        $firstname,
        $lastname,
        $companyname,
        $email,
        $address1,
        $address2,
        $city,
        $state,
        $postcode,
        $country,
        $phonenumber,
        $password,
        $securityqid = 0,
        $securityqans = "",
        $sendemail = true,
        $additionalData = [],
        $uuid = "",
        $isAdmin = false,
        $marketingOptIn = 0
    ) {
        // Get client IP
        $remote_ip = request()->ip(); // or $_SERVER['SERVER_ADDR'] ?
        $verifyEmailAddress = Cfg::get("EnableEmailVerification");
    
        if (!$country) {
            $country = Cfg::get("DefaultCountry");
        }
    
        if (!$uuid) {
            $uuid = Uuid::uuid4()->toString();
        }
    
        $fullhost = gethostbyaddr($remote_ip);
        $currency = is_array(session("currency")) ? session("currency") : (new AdminFunctions())->getCurrency("", session("currency"));
    
        $hasher = new Pwd();
        $passwordHash = (new Password())->hash(\App\Helpers\Sanitize::decode($password));
    
        $client = new ClientModel();
    
        // Check the email preferences format
        // TODO: need improvement move to additionalData block
        $emailPreferences = [];
        if (!empty($additionalData["email_preferences"])) {
            $emailPreferences = $additionalData["email_preferences"];
    
            try {
                $client->validateEmailPreferences($emailPreferences);
            } catch (\App\Exceptions\Validation\Required $e) {
                return ["result" => "error", "message" => __('admin.emailPreferencesoneRequired') . " " . __($e->getMessage())];
            } catch (\Exception $e) {
                return ["result" => "error", "message" => __($e->getMessage())];
            }
        }
    
        $defaultPref = $client->getEmailPreferencesDefault();
        $emailPreferences = array_merge($defaultPref, $emailPreferences);
    
        $client->fill([
            'uuid' => $uuid,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'companyname' => $companyname,
            'email' => $email,
            'address1' => $address1,
            'address2' => $address2,
            'city' => $city,
            'state' => $state,
            'postcode' => $postcode,
            'country' => $country,
            'phonenumber' => $phonenumber,
            'password' => $passwordHash,
            'lastlogin' => \Carbon\Carbon::now(),
            'securityqid' => $securityqid,
            'securityqans' => $hasher->encrypt($securityqans),
            'ip' => $remote_ip,
            'host' => $fullhost,
            'status' => "Active",
            'datecreated' => \Carbon\Carbon::now(),
            'language' => session("Language") ?? "",
            'currency' => $currency["id"],
            'email_verified' => 0,
            'email_preferences' => $emailPreferences ? json_encode($emailPreferences, JSON_NUMERIC_CHECK) : null,
        ]);
        $client->save();
    
        $uid = $client->id;
    
        \App\Helpers\LogActivity::Save("Created Client $firstname $lastname - User ID: $uid", $uid);
    
        if (!empty($additionalData)) {
            $legacyBooleanColumns = [
                "taxexempt",
                "latefeeoveride",
                "overideduenotices",
                "separateinvoices",
                "disableautocc",
                "emailoptout",
                "overrideautoclose"
            ];
    
            foreach ($legacyBooleanColumns as $column) {
                if (isset($additionalData[$column])) {
                    $additionalData[$column] = (bool) $additionalData[$column];
                }
            }
    
            if (!empty($additionalData["credit"]) && $additionalData["credit"] <= 0) {
                unset($additionalData["credit"]);
            }
    
            $tableData = $additionalData;
            if (isset($tableData["customfields"])) {
                unset($tableData["customfields"]);
            }
    
            if (Vat::isTaxIdDisabled() || !Vat::isUsingNativeField()) {
                unset($tableData["tax_id"]);
            }
    
            // TODO: Need to know what the value inside $tableData
            // dd($tableData);
            $client = ClientModel::find($uid);
            foreach ($tableData as $key => $value) {
                $client->{$key} = $value;
            }
            $client->save();
    
            if (!empty($tableData["credit"])) {
                $credit = new \App\Models\Credit();
                $credit->clientid = $uid;
                $credit->date = \Carbon\Carbon::now()->format("Y-m-d");
                $credit->description = "Opening Credit Balance";
                $credit->amount = $tableData["credit"];
                $credit->save();
            }
        }
    
        // TODO: Apakah bisa menggunakan if (auth()->guard('admin')->check() ) ?
        if (defined("ADMINAREA")) {
            $isAdmin = true;
        }
    
        $customFields = request()->get("customfield");
        if (empty($customFields) && !empty($additionalData["customfields"])) {
            $customFields = $additionalData["customfields"];
        }
    
        \App\Helpers\Customfield::SaveCustomFields($uid, $customFields, "client", $isAdmin);
    
        $client = ClientModel::find($uid);
    
        if (!is_null($marketingOptIn)) {
            if ($marketingOptIn) {
                $client->marketingEmailOptIn($remote_ip, false);
            } else {
                $client->marketingEmailOptOut($remote_ip, false);
            }
        }
    
        // TODO: sendEmailAddressVerification
        if ($verifyEmailAddress) {
            if (!is_null($client)) {
                // TODO: $client->sendEmailAddressVerification();
            }
        } else {
            if ($sendemail) {
                \App\Helpers\Functions::sendMessage("Client Signup Email", $uid, ["client_password" => $password]);
            }
        }
    
        // TODO: session set
        if (defined("CLIENTAREA")) {
            // $_SESSION["uid"] = $uid;
            // $_SESSION["upw"] = WHMCS\Authentication\Client::generateClientLoginHash($uid, NULL, $passwordHash);
            // $_SESSION["tkval"] = genRandomVal();
    
            Hooks::run_hook("ClientLogin", ["userid" => $uid, "contactid" => 0]);
        }
    
        if (Cfg::get("TaxEUTaxValidation")) {
            $taxExempt = Vat::setTaxExempt($client);
            $client->save();
            if ($taxExempt != $additionalData["taxexempt"]) {
                $additionalData["taxexempt"] = $taxExempt;
            }
        }
    
        if (!defined("APICALL")) {
            Hooks::run_hook("ClientAdd", array_merge([
                "userid" => $uid,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "companyname" => $companyname,
                "email" => $email,
                "address1" => $address1,
                "address2" => $address2,
                "city" => $city,
                "state" => $state,
                "postcode" => $postcode,
                "country" => $country,
                "phonenumber" => $phonenumber,
                "password" => $password
            ], $additionalData, ["customfields" => $customFields]));
        }
    
        return ["result" => "success", "clientid" => $uid];
    }
    
    public function AddClient(array $params)
    {
        $AdminFunctions = new \App\Helpers\AdminFunctions();
        $currency = $AdminFunctions->getCurrency();
    
        $params = [
            'owner_user_id' => (int) @$params['owner_user_id'],
            'firstname' => @$params['firstname'],
            'lastname' => @$params['lastname'],
            'companyname' => @$params['companyname'] ?? '',
            'email' => @$params['email'],
            'address1' => @$params['address1'],
            'address2' => @$params['address2'] ?? '',
            'city' => @$params['city'],
            'state' => @$params['state'],
            'postcode' => @$params['postcode'] ?? '',
            'country' => @$params['country'],
            'phonenumber' => @$params['phonenumber'],
            'tax_id' => @$params['tax_id'],
            'password2' => @$params['password2'],
            'securityqid' => (int) @$params['securityqid'],
            'securityqans' => @$params['securityqans'],
            'currency' => @$params['currency'] ?? $currency['id'],
            'groupid' => (int) @$params['groupid'],
            'customfields' => @$params['customfields'],
            'language' => @$params['language'],
            'clientip' => Request::ip(),
            'notes' => @$params['notes'],
            'marketingoptin' => (bool) @$params['notes'],
            'noemail' => (bool) @$params['noemail'],
            'skipvalidation' => (int) (bool) @$params['skipvalidation'],
        ];
        extract($params);
    
        $remote_ip = $clientip;
        $errorMessage = [];
        $verifyEmailAddress = \App\Helpers\Cfg::get('EnableEmailVerification');
    
        if (!$country) {
            $country = \App\Helpers\Cfg::get("DefaultCountry");
        }
    
        $uuid = Uuid::uuid4()->toString($clientip);
        $password_hash = $this->password->hash(\App\Helpers\Sanitize::decode($password2));
        $fullhost = gethostbyaddr($clientip);
        $customFieldsErrors = [];
    
        if (!empty($customfields)) {
            $customfields = $this->safe_unserialize(base64_decode($customfields));
            $validate = new \App\Helpers\Validate();
            $validate->validateCustomFields("client", "", false, $customfields);
            $customFieldsErrors = $validate->getErrors();
            // dd($customFieldsErrors);
        } else {
            $fetchedCustomClientFields = $this->getCustomFields("client", null, null);
            if (is_array($fetchedCustomClientFields)) {
                foreach ($fetchedCustomClientFields as $fetchedCustomClientField) {
                    if ($fetchedCustomClientField["required"] == "*") {
                        $customFieldsErrors[] = "You did not provide required custom field value for " . $fetchedCustomClientField["name"];
                    }
                }
            }
        }
    
        if (count($customFieldsErrors) > 0 && !$skipvalidation) {
            if ($errorMessage) {
                $errorMessage = explode("<li>", $errorMessage);
                $error = $errorMessage[1];
                $error = strip_tags($error);
            } else {
                $error = implode(", ", $customFieldsErrors);
            }
            return ["result" => "error", "message" => $error];
        } else {
            if ($errorMessage) {
                $emailErrLang = 'A user already exists with that email address';
                foreach (explode("<li>", $errorMessage) as $error) {
                    $error = strip_tags($error);
                    if (stripos($emailErrLang, $error) !== false) {
                        return ["result" => "error", "message" => $error];
                    }
                }
            }
        }
    
        session()->put("currency", $currency);
        $sendEmail = !$noemail;
        $clientData = $params;
        unset($clientData['customfields'], $clientData['skipvalidation']);
        $now = \Carbon\Carbon::now();
    
        $array = [
            "uuid" => $uuid,
            "firstname" => $firstname,
            "lastname" => $lastname,
            "companyname" => $companyname,
            "email" => $email,
            "address1" => $address1,
            "address2" => $address2,
            "city" => $city,
            "state" => $state,
            "postcode" => $postcode,
            "country" => $country,
            "phonenumber" => $phonenumber,
            "password" => $password_hash,
            "lastlogin" => $now,
            "securityqid" => $securityqid,
            "securityqans" => encrypt($securityqans),
            "ip" => $remote_ip,
            "host" => $fullhost,
            "status" => "Active",
            "datecreated" => "now()",
            "language" => '',
            "currency" => $currency,
            "email_verified" => 0
        ];
    
        $saveClient = DB::table("{$this->prefix}clients")->insert($array);
        $clientId = DB::getPdo()->lastInsertId();
        LogActivity::Save("Created Client " . $firstname . " " . $lastname . " - User ID: " . $uuid, $uuid);
    
        if (!empty($customfields)) {
            $this->saveCustomFields($clientId, $customfields, "client");
        }
    
        return ["result" => "success", "clientid" => $clientId];
    }
    public function UpdateClient(array $params)
    {
        $params = [
            'clientid' => (int) $params['clientid'],
            'clientemail' => $params['clientemail'] ?? '',
            'firstname' => $params['firstname'] ?? '',
            'lastname' => $params['lastname'] ?? '',
            'companyname' => $params['companyname'] ?? '',
            'email' => $params['email'] ?? '',
            'address1' => $params['address1'] ?? '',
            'address2' => $params['address2'] ?? '',
            'city' => $params['city'] ?? '',
            'state' => $params['state'] ?? '',
            'postcode' => $params['postcode'] ?? '',
            'country' => $params['country'] ?? '',
            'phonenumber' => $params['phonenumber'] ?? '',
            'tax_id' => $params['tax_id'] ?? '',
            'language' => $params['language'] ?? '',
            'clientip' => Request::ip(),
            'notes' => $params['notes'] ?? '',
            'status' => $params['status'] ?? '',
            'paymentmethod' => $params['paymentmethod'] ?? '',
            'email_preferences' => [
                'general' => (int) (bool) ($params['email_preferences']['general'] ?? ''),
                'product' => (int) (bool) ($params['email_preferences']['product'] ?? ''),
                'domain' => (int) (bool) ($params['email_preferences']['domain'] ?? ''),
                'invoice' => (int) (bool) ($params['email_preferences']['invoice'] ?? ''),
                'support' => (int) (bool) ($params['email_preferences']['support'] ?? ''),
                'affiliate' => (int) (bool) ($params['email_preferences']['affiliate'] ?? ''),
            ],
            'marketingoptin' => (int) (bool) ($params['marketingoptin'] ?? ''),
            'clearcreditcard' => (int) (bool) ($params['clearcreditcard'] ?? ''),
            'skipvalidation' => (int) (bool) ($params['skipvalidation'] ?? ''),
            'latefeeoveride' => (int) (bool) ($params['latefeeoveride'] ?? ''),
            'overideduenotices' => (int) (bool) ($params['overideduenotices'] ?? ''),
            'taxexempt' => (int) (bool) ($params['taxexempt'] ?? ''),
            'separateinvoices' => (int) (bool) ($params['separateinvoices'] ?? ''),
        ];
    
        $statusInDb = ['Active', 'Inactive', 'Closed'];
        if (!in_array($params['status'], $statusInDb)) {
            $params['status'] = '';
        }
    
        extract($params);
    
        $getDataClient = $clientemail
            ? \App\Models\Client::where('email', $clientemail)->first()
            : \App\Models\Client::find($clientid);
    
        if (is_null($getDataClient)) {
            return ["result" => "error", "message" => "Client ID Not Found"];
        }
    
        $clientid = $getDataClient->id;
        $clientData = \App\Models\Client::find($clientid);
        $fieldsarray = [
            "firstname", "lastname", "companyname", "email", "address1", "address2", "city", "state", "postcode", "country",
            "phonenumber", "credit", "taxexempt", "notes", "status", "language", "currency", "groupid", "taxexempt",
            "latefeeoveride", "overideduenotices", "billingcid", "separateinvoices", "disableautocc", "datecreated",
            "securityqid", "lastlogin", "ip", "host"
        ];
        $fliedUpdate = [];
    
        foreach ($fieldsarray as $flied) {
            if (!empty($params[$flied])) {
                $fliedUpdate[$flied] = $params[$flied];
            }
        }
    
        if (!empty($fliedUpdate)) {
            $fliedUpdate['ip'] = $params['clientip'];
            $fliedUpdate['lastlogin'] = $params['clientip'];
            $fliedUpdate['updated_at'] = \Carbon\Carbon::now();
    
            foreach ($fliedUpdate as $k => $v) {
                $clientData->$k = $v;
            }
            $clientData->save();
            // $changes = implode(", ", $changeList);
            \App\Helpers\LogActivity::Save("Client Profile Modified - " . $changes . " - User ID: " . $clientid, $clientid);
        }
    
        return ["result" => "success", "clientid" => $clientid];
    }


    public function saveCustomFields($relid, $customfields, $type = "", $isAdmin = false)
    {
        if (is_array($customfields)) {
            foreach ($customfields as $id => $value) {
                if (is_null($value)) {
                    $value = "";
                }
    
                if (!is_int($id) && !empty($id)) {
                    $stmt = DB::table("{$this->prefix}customfields")
                        ->where("{$this->prefix}customfields.fieldname", "=", $id);
    
                    if ($type) {
                        $stmt->where("{$this->prefix}customfields.type", "=", $type);
                    }
    
                    if ($type == "product") {
                        $stmt->join("{$this->prefix}products", "{$this->prefix}products.id", "=", "{$this->prefix}customfields.relid")
                            ->join("{$this->prefix}hosting", "{$this->prefix}hosting.packageid", "=", "{$this->prefix}products.id")
                            ->where("{$this->prefix}hosting.id", "=", $relid);
                    }
    
                    $fieldIds = $stmt->get(["{$this->prefix}customfields.id"]);
                    if (count($fieldIds) != 1) {
                        continue;
                    }
                    $id = $fieldIds[0]->id;
                }
    
                $tblcustomfields = \App\Models\Customfield::where('id', $id);
                if ($type) {
                    $tblcustomfields->where('type', $type);
                }
                if (!$isAdmin) {
                    $tblcustomfields->where('adminonly', '');
                }
                if (!$tblcustomfields->first()) {
                    continue;
                }
    
                $fieldsavehooks = event(new \App\Events\CustomFieldSave($id, $relid, $value));
                if (count($fieldsavehooks) > 0) {
                    $fieldsavehookslast = array_pop($fieldsavehooks);
                    if (array_key_exists("value", $fieldsavehookslast)) {
                        $value = $fieldsavehookslast["value"];
                    }
                }
    
                $customFieldValue = \App\Models\Customfieldsvalue::firstOrNew(["fieldid" => $id, "relid" => $relid]);
                $customFieldValue->value = $value;
                $customFieldValue->save();
            }
        }
    }

 public function safe_unserialize($str)
{
    if (function_exists("mb_internal_encoding") && (int) ini_get("mbstring.func_overload") & 2) {
        $mbIntEnc = mb_internal_encoding();
        mb_internal_encoding("ASCII");
    }
    
    try {
        $out = $this->_safe_unserialize($str);
    } catch (Exception $e) {
        LogActivity::Save($e->getMessage());
        return NULL;
    }
    
    if (isset($mbIntEnc)) {
        mb_internal_encoding($mbIntEnc);
    }
    
    return $out;
}
    
    public function _safe_unserialize($str)
    {
        if ($this->getSerializeInputMaxLength() < strlen($str)) {
            throw new \Exception(sprintf("Failed to unserialize input string. %s exceeds maximum of %s", strlen($str), $this->getSerializeInputMaxLength()));
        }
    
        if (empty($str) || !is_string($str)) {
            return false;
        }
    
        $stack = [];
        $expected = [];
        $arrayMaxLength = $this->getSerializeArrayMaxLength();
        $arrayMaxDepth = $this->getSerializeArrayDepth();
        $state = 0;
        $list = [];
        $key = '';

        while ($state != 1) {
            $type = $str[0] ?? "";
    
            if ($type == "}") {
                $str = substr($str, 1);
            } else {
                if ($type == "N" && $str[1] == ";") {
                    $value = null;
                    $str = substr($str, 2);
                } elseif ($type == "b" && preg_match("/^b:([01]);/", $str, $matches)) {
                    $value = $matches[1] == "1";
                    $str = substr($str, 4);
                } elseif ($type == "i" && preg_match("/^i:(-?[0-9]+);(.*)/s", $str, $matches)) {
                    $value = (int) $matches[1];
                    $str = $matches[2];
                } elseif ($type == "d" && preg_match("/^d:(-?[0-9]+\\.?[0-9]*(E[+-][0-9]+)?);(.*)/s", $str, $matches)) {
                    $value = (double) $matches[1];
                    $str = $matches[3];
                } elseif ($type == "s" && preg_match("/^s:([0-9]+):\"(.*)/s", $str, $matches) && substr($matches[2], (int) $matches[1], 2) == "\";") {
                    $value = substr($matches[2], 0, (int) $matches[1]);
                    $str = substr($matches[2], (int) $matches[1] + 2);
                } elseif ($type == "a" && preg_match("/^a:([0-9]+):{(.*)/s", $str, $matches)) {
                    if ($arrayMaxLength < $matches[1]) {
                        throw new \Exception(sprintf("Failed to unserialize array content. %s exceeds maximum array length %s", $matches[1], $arrayMaxLength));
                    }
                    $expectedLength = (int) $matches[1];
                    $str = $matches[2];
                } else {
                    return false;
                }
            }
    
            switch ($state) {
                case 3:
                    if ($type == "a") {
                        if ($arrayMaxDepth <= count($stack)) {
                            throw new \Exception(sprintf("Failed to unserialize array content. Maximum array depth exceeds %s", count($stack), $arrayMaxDepth));
                        }
                        
                        $stack[] =& $list;
                        $list[$key] = [];
                        $list =& $list[$key];
                        $expected[] = $expectedLength;
                        $state = 2;
                        break;
                    }
                    if ($type != "}") {
                        $list[$key] = $value;
                        $state = 2;
                        break;
                    }
                    return false;
                case 2:
                    if ($type == "}") {
                        if (count($list) < end($expected)) {
                            return false;
                        }
                        unset($list);
                        $list =& $stack[count($stack) - 1];
                        array_pop($stack);
                        array_pop($expected);
                        if (count($expected) == 0) {
                            $state = 1;
                        }
                        break;
                    }
                    if ($type == "i" || $type == "s") {
                        if ($arrayMaxLength <= count($list)) {
                            throw new \Exception(sprintf("Failed to unserialize array content. %s exceeds maximum array length %s", count($list), $arrayMaxLength));
                        }
                        if (end($expected) <= count($list)) {
                            return false;
                        }
                        $key = $value;
                        $state = 3;
                        break;
                    }
                    return false;
                case 0:
                    if ($type == "a") {
                        if ($arrayMaxDepth <= count($stack)) {
                            throw new \Exception(sprintf("Failed to unserialize array content. Maximum array depth exceeds %s", count($stack), $arrayMaxDepth));
                        }
                        $data = [];
                        $list =& $data;
                        $expected[] = $expectedLength;
                        $state = 2;
                        break;
                    }
                    if ($type != "}") {
                        $data = $value;
                        $state = 1;
                        break;
                    }
                    return false;
            }
        }
    
        if (!empty($str)) {
            return false;
        }
    
        return $data;
    }
    
    public function getSerializeArrayMaxLength()
    {
        $default = 256;
        $userPreference = config('portal.config.serialize_array_max_length');
        if (!is_numeric($userPreference)) {
            return $default;
        }
        return $userPreference;
    }

	public function getSerializeArrayDepth()
    {
        $default = 5;
        $userPreference = config('portal.config.serialize_array_max_depth');
        if (!is_numeric($userPreference)) {
            return $default;
        }
        return $userPreference;
    }

	public function getSerializeInputMaxLength()
    {
        $default = 16384;
        $userPreference = config('portal.config.serialize_input_max_length');
        if (!is_numeric($userPreference)) {
            return $default;
        }
        return $userPreference;
    }


    public function AddContact(array $params)
    {
        $params = [
            'clientid' => (int) $params['clientid'],
            'firstname' => @$params['firstname'],
            'lastname' => @$params['lastname'],
            'companyname' => @$params['companyname'],
            'email' => @$params['email'] ?? '',
            'address1' => @$params['address1'] ?? '',
            'address2' => @$params['address2'] ?? '',
            'city' => @$params['city'] ?? '',
            'state' => @$params['state'] ?? '',
            'postcode' => @$params['postcode'] ?? '',
            'country' => @$params['country'] ?? '',
            'phonenumber' => @$params['phonenumber'] ?? '',
            'tax_id' => @$params['tax_id'] ?? '',
            'subaccount' => @$params['subaccount'] ?? '',
            'permissions' => @$params['permissions'] ?? '',
            'password2' => @$params['password2'] ?? '',
            'email_preferences' => @$params['email_preferences'],
        ];
        extract($params);
    
        $getClient = \App\Models\Client::find($clientid);
    
        if (is_null($getClient)) {
            return ["result" => "error", "message" => "Client ID Not Found"];
        } else {
            $permissions = $permissions ? explode(",", $permissions) : [];
            if ($password2 || count($permissions)) {
                $cekClient = \App\Models\Client::where('email', $email)->count();
                $cekContact = \App\Models\Contact::where('email', $email)->where('subaccount', 1)->count();
                if ($cekClient || $cekContact) {
                    return ["result" => "error", "message" => "Duplicate Email Address"];
                }
            }
    
            $generalemails = $email_preferences['general'] ?? 0;
            $productemails = $email_preferences['product'] ?? 0;
            $domainemails = $email_preferences['invoice'] ?? 0;
            $invoiceemails = $email_preferences['support'] ?? 0;
            $supportemails = $email_preferences['affiliate'] ?? 0;
    
            $strPermission = implode(",", $permissions);
            $contact = new \App\Models\Contact;
            $contact->userid = $clientid;
            $contact->firstname = $firstname;
            $contact->lastname = $lastname;
            $contact->companyname = $companyname;
            $contact->email = $email;
            $contact->address1 = $address1;
            $contact->address2 = $address2;
            $contact->city = $city;
            $contact->state = $state;
            $contact->postcode = $postcode;
            $contact->country = $country;
            $contact->phonenumber = $phonenumber;
            // $contact->tax_id = $tax_id;
    
            if ($generalemails) {
                $contact->generalemails = "1";
            }
            if ($productemails) {
                $contact->productemails = "1";
            }
            if ($domainemails) {
                $contact->domainemails = "1";
            }
            if ($invoiceemails) {
                $contact->invoiceemails = "1";
            }
            if ($supportemails) {
                $contact->supportemails = "1";
            }
            if ($subaccount) {
                $contact->subaccount = $subaccount;
            }
            if ($permissions) {
                $contact->permissions = $strPermission;
            }
    
            $password2 = \App\Helpers\Sanitize::decode($password2);
            $hash = new \App\Helpers\Password();
            $password = $hash->hash($password2);
            $contact->password = $password;
            $contact->save();
            $contactid = $contact->id;
            $contactemail = $contact->email;
    
            \App\Helpers\Hooks::run_hook("ContactAdd", array_merge($params, ["contactid" => $contactid, "password" => $password]));
            LogActivity::Save("Added Contact - User ID: " . $clientid . " - Contact ID: " . $contactid, $clientid);
    
            return ["result" => "success", "contactid" => $contactid, 'email' => $contactemail];
        }
    }

    
    public function AddContact2(array $params)
    {
        $params = [
            "userid" => @$params["userid"] ?? null,
            "firstname" => @$params["firstname"] ?? null,
            "lastname" => @$params["lastname"] ?? null,
            "companyname" => @$params["companyname"] ?? null,
            "email" => @$params["email"] ?? null,
            "address1" => @$params["address1"] ?? null,
            "address2" => @$params["address2"] ?? "",
            "city" => @$params["city"] ?? null,
            "state" => @$params["state"] ?? null,
            "postcode" => @$params["postcode"] ?? "",
            "country" => @$params["country"] ?? null,
            "phonenumber" => @$params["phonenumber"] ?? null,
            "password" => @$params["password"] ?? null,
            "permissions" => @$params["permissions"] ?? "",
            "generalemails" => @$params["generalemails"] ?? null,
            "productemails" => @$params["productemails"] ?? null,
            "domainemails" => @$params["domainemails"] ?? null,
            "invoiceemails" => @$params["invoiceemails"] ?? null,
            "supportemails" => @$params["supportemails"] ?? null,
            "affiliateemails" => @$params["affiliateemails"] ?? null,
            "taxId" => @$params["taxt_id"] ?? "",
            "subaccount" => @$params["subaccount"] ?? null,
            "contactid" => @$params["contactid"] ?? null,
            "email_preferences" => @$params["email_preferences"] ?? null,
        ];
        extract($params);
    
        // Needed when update
        // if ($subaccount) {
        //     $subaccount = "1";

        //     $data = \App\Models\Client::where("email", $email)->count();
        //     $data2 = \App\Models\Contact::where("email", $email)->where("id", "!=", $contactid)->count();

        //     if ($data + $data2) {
        //         return ["result" => "error", "message" => __('admin.clientsduplicateemailexp')];
        //     }
        // } else {
        //     $subaccount = "0";
        // }

        // $contact = NULL;
        // $contact = \App\Models\Contact::find($contactid);

        // if ($contact) {
        //     if (0 < $contactid) {
        //         try {
        //             $contact->validateEmailPreferences($email_preferences);
        //         } catch (\App\Exceptions\Validation\Required $e) {
        //             return ["result" => "error", "message" => __('admin.emailPreferencesoneRequired') ." " .__($e->getMessage())];
        //         } catch (\Exception $e) {
        //             return ["result" => "error", "message" => "Invalid Contact ID"];
        //         }
        //     }
        // }

        $taxId = "";
        if (\App\Helpers\Vat::isTaxIdEnabled()) {
            $taxId = request()->get(\App\Helpers\Vat::getFieldName(true));
        }
    
        if (!$country) {
            $country = Cfg::get("DefaultCountry");
        }
    
        if ($permissions) {
            $permissions = implode(",", $permissions);
        }
    
        $contact = new \App\Models\Contact();
    
        $subaccount = $password ? "1" : "0";
    
        $hasher = new \App\Helpers\Password();
        $password = $hasher->hash(\App\Helpers\Sanitize::decode($password));
    
        $emailPreferences = [];
        if ($email_preferences) {
            try {
                $contact->validateEmailPreferences($email_preferences);
            } catch (\App\Exceptions\Validation\Required $e) {
                return ["result" => "error", "message" => __('admin.emailPreferencesoneRequired') . " " . __($e->getMessage())];
            } catch (\Exception $e) {
                return ["result" => "error", "message" => "Invalid Contact ID"];
            }
    
            $emailPreferences = $email_preferences;
        }
    
        $defaultPref = $contact->getEmailPreferencesDefault();
        $emailPreferences = array_merge($defaultPref, $emailPreferences);
    
        $array = [
            "userid" => $userid,
            "firstname" => $firstname,
            "lastname" => $lastname,
            "companyname" => $companyname,
            "email" => $email,
            "address1" => $address1,
            "address2" => $address2,
            "city" => $city,
            "state" => $state,
            "postcode" => $postcode,
            "country" => $country,
            "phonenumber" => $phonenumber,
            "tax_id" => $taxId ?? "",
            "subaccount" => $subaccount,
            "password" => $password,
            "permissions" => $permissions,
            "generalemails" => (int) $emailPreferences["general"],
            "productemails" => (int) $emailPreferences["product"],
            "domainemails" => (int) $emailPreferences["domain"],
            "invoiceemails" => (int) $emailPreferences["invoice"],
            "supportemails" => (int) $emailPreferences["support"],
            "affiliateemails" => (int) $emailPreferences["affiliate"],
        ];
    
        foreach ($array as $key => $value) {
            $contact->{$key} = $value;
        }
    
        $contact->save();
        $contactid = $contact->id;
    
        Hooks::run_hook("ContactAdd", array_merge($array, ["contactid" => $contactid, "password" => $password]));
        LogActivity::Save("Added Contact - User ID: $userid - Contact ID: $contactid", $userid);
    
        return ["result" => "success", "contactid" => $contactid];
    }
    
    public function CloseClient(Int $clientid)
    {
        $client = \App\Models\Client::find($clientid);
        if (is_null($client)) {
            return ["result" => "error", "message" => "Client ID Not Found"];
        } else {
            $client->status = 'Closed';
            $client->save();
    
            \App\Models\Hosting::where('userid', $clientid)
                ->whereIn('domainstatus', ['Pending', 'Active'])
                ->update([
                    'domainstatus' => 'Cancelled',
                    'termination_date' => date("Y-m-d")
                ]);
    
            \App\Models\Hosting::where('userid', $clientid)
                ->where('domainstatus', 'Suspended')
                ->update([
                    'domainstatus' => 'Terminated',
                    'termination_date' => date("Y-m-d")
                ]);
    
            $dataHosting = \App\Models\Hosting::where('userid', $clientid)->pluck('id');
            foreach ($dataHosting as $v) {
                \App\Models\Hostingaddon::where('hostingid', $v)
                    ->whereIn('status', ['Pending', 'Active'])
                    ->update([
                        'status' => 'Cancelled',
                        'termination_date' => date("Y-m-d")
                    ]);
    
                \App\Models\Hostingaddon::where('hostingid', $v)
                    ->where('status', 'Suspended')
                    ->update([
                        'status' => 'Terminated',
                        'termination_date' => date("Y-m-d")
                    ]);
            }
    
            \App\Models\Domain::where('userid', $clientid)
                ->whereIn('status', ['Pending', 'Active', 'Pending-Transfer'])
                ->update(['status' => 'Cancelled']);
    
            \App\Models\Billableitem::where('userid', $clientid)->update(['invoiceaction' => 0]);
            LogActivity::Save("Client Status changed to Closed - User ID: " . $clientid, $clientid);
            \App\Helpers\Hooks::run_hook("ClientClose", ["userid" => $clientid]);
            return ["result" => "success", "clientid" => $clientid];
        }
    }
    
    public function DeleteClient(Int $userID)
    {
        $client = \App\Models\Client::find($userID);
    
        if (is_null($client)) {
            return ["result" => "error", "message" => "Client ID Not Found"];
        } else {
            try {
                $this->deleteEntireClient($userID);
                $client->delete();
            } catch (\Exception $e) {
                return ["result" => "error", "message" => "Client Delete Failed: " . $e->getMessage()];
            }
        }
    
        return ["result" => "success", "clientid" => $userID];
    }
    
    private function deleteEntireClient(Int $userID)
    {
        \App\Helpers\Hooks::run_hook("PreDeleteClient", ["userid" => $userID]);
    
        \App\Models\Contact::where('userid', $userID)->delete();
        $tblhostingIds = \App\Models\Hostingaddon::where('userid', $userID)->pluck("id");
        if (!empty($tblhostingIds)) {
            \App\Models\Hostingconfigoption::whereIn("relid", $tblhostingIds)->delete();
        }
        $tblcustomfields = \App\Models\Customfield::where('type', 'client')->pluck("id");
        if (!empty($tblcustomfields)) {
            \App\Models\Customfieldsvalue::where('relid', $userID)->whereIn('fieldid', $tblcustomfields)->delete();
        }
    
        $resut = \App\Models\Customfield::where('type', 'product')->select('id', 'relid')->get();
        foreach ($resut as $data) {
            $customfieldid = $data->id;
            $customfieldpid = $data->relid;
            $hostingID = \App\Models\Hosting::where('userid', $userID)->where('packageid', $customfieldpid)->pluck("id");
            if (!empty($hostingID)) {
                \App\Models\Customfieldsvalue::where('fieldid', $customfieldid)->whereIn('relid', $hostingID)->delete();
            }
        }
    
        $addonCustomFields = \App\Models\Customfield::where('type', 'addon')->select('id', 'relid')->get();
        foreach ($addonCustomFields as $addonCustomField) {
            $customFieldId = $addonCustomField->id;
            $customFieldAddonId = $addonCustomField->relid;
            $hostingAddons = \App\Models\Hostingaddon::where("userid", $userID)->where("addonid", $customFieldAddonId)->pluck("id");
            foreach ($hostingAddons as $v) {
                \App\Models\Customfieldsvalue::where('fieldid', $customFieldId)->where("relid", $v)->delete();
            }
        }
    
        $hostingid = \App\Models\Hosting::where('userid', $userID)->pluck("id");
        \App\Models\Hostingaddon::whereIn('hostingid', $hostingid)->delete();
        \App\Models\Order::where('userid', $userID)->delete();
        \App\Models\Hosting::where('userid', $userID)->delete();
        \App\Models\Domain::where('userid', $userID)->delete();
        \App\Models\Email::where('userid', $userID)->delete();
        \App\Models\Invoice::where('userid', $userID)->delete();
        \App\Models\Invoiceitem::where('userid', $userID)->delete();
    
        $tickets = \App\Models\Ticket::where("userid", $userID)->pluck("id");
        foreach ($tickets as $v) {
            try {
                $this->deleteTicket($v);
            } catch (\Exception $e) {
                \App\Models\Ticketreply::where('tid', $v)->delete();
                \App\Models\Tickettag::where('ticketid', $v)->delete();
                \App\Models\Ticketnote::where('ticketid', $v)->delete();
                \App\Models\Ticketlog::where('tid', $v)->delete();
                \App\Models\Ticket::where('id', $v)->delete();
            }
        }
        \App\Models\Affiliate::where('clientid', $userID)->delete();
        \App\Models\Note::where('userid', $userID)->delete();
        \App\Models\Credit::where('clientid', $userID)->delete();
        \App\Models\ActivityLog::where('userid', $userID)->delete();
        \App\Models\Sslorder::where('userid', $userID)->delete();
        \App\Models\AuthnAccountLink::where('client_id', $userID)->delete();
    
        LogActivity::Save("Client Deleted - ID: " . $userID);
        return true;
    }
    
    private function deleteTicket($ticketid, $replyid = 0)
    {
        $ticketid = (int) $ticketid;
        $replyid = (int) $replyid;
        $attachments = [];
    
        if (0 < $replyid) {
            $attachments = \App\Models\Ticketreply::where('id', $replyid)->pluck("attachment");
        } else {
            $attachments = \App\Models\Ticketreply::where('tid', $ticketid)->pluck("attachment");
        }
    
        if (!$replyid) {
            $data = \App\Models\Ticket::where('id', $ticketid)->select('did', 'attachment')->first();
            $deptid = $data->did;
            $attachments[] = $data->attachment;
        }
    
        foreach ($attachments as $attachment) {
            if ($attachment) {
                $attachment = explode("|", $attachment);
                foreach ($attachment as $filename) {
                    //try {
                    //    Storage::ticketAttachments()->deleteAllowNotPresent($filename);
                    //} catch (Exception $e) {
                    //    throw new Exception\Fatal("Could not delete file: " . htmlentities($e->getMessage()));
                    //}
                }
            }
        }
    
        if (!$replyid) {
            $customfields = $this->getCustomFields("support", $deptid, $ticketid, true);
            foreach ($customfields as $field) {
                \App\Models\Customfieldsvalue::where('fieldid', $field["id"])->where('relid', $ticketid)->delete();
            }
            \App\Models\Tickettag::where('ticketid', $ticketid)->delete();
            \App\Models\Ticketnote::where('ticketid', $ticketid)->delete();
            \App\Models\Ticketlog::where('tid', $ticketid)->delete();
            \App\Models\Ticketreply::where('tid', $ticketid)->delete();
            \App\Models\Ticket::where('id', $ticketid)->delete();
            $auth = Auth::user();
            $adminId = $auth->id;
            \App\Helpers\LogActivity::Save("Deleted Ticket - Ticket ID: " . $ticketid);
            \App\Helpers\Hooks::run_hook("TicketDelete", ["ticketId" => $ticketid, "adminId" => $adminId]);
        } else {
            $auth = Auth::user();
            $adminId = $auth->id;
            \App\Models\Ticketreply::where('id', $replyid)->delete();
            \App\Helpers\LogActivity::Save("Deleted Ticket Reply - ID: " . $replyid);
            \App\Helpers\Hooks::run_hook("TicketDeleteReply", ["ticketId" => $ticketid, "replyId" => $replyid, "adminId" => $adminId]);
        }
    }

    public function getSecurityQuestions($questionid = "")
    {
        $query = $questionid 
            ? \App\Models\AdminSecurityQuestion::where("question", $questionid)->get() 
            : \App\Models\AdminSecurityQuestion::get();
    
        $results = [];
        $pwd = new \App\Helpers\Pwd();
    
        foreach ($query as $data) {
            $results[] = [
                "id" => $data["id"], 
                "question" => $pwd->decrypt($data["question"])
            ];
        }
    
        return $results;
    }
    
    public function getCountries()
    {
        $countries = [];
        $availableCountries = (new Country())->getCountryNameArray();
    
        foreach ($availableCountries as $key => $value) {
            $countries[] = ["id" => $key, "name" => $value];
        }
    
        return $countries;
    }
    
    public function getAvailableLanguages()
    {
        $langPath = App::langPath();
        $files = File::allFiles($langPath);
        $lang = [];
    
        foreach ($files as $file) {
            $filename = $file->getFilename();
            $ext = $file->getExtension();
            $locale = str_replace("$langPath/", "", $file->getPath());
    
            $filename = str_replace(".$ext", "", $filename);
            $languages = Lang::get($filename, [], $locale);
    
            if (is_array($languages) && array_key_exists("langname", $languages) && array_key_exists("langkey", $languages)) {
                $lang[$languages["langkey"]] = [
                    "key" => $locale,
                    "name" => $languages["langname"],
                ];
            }
        }
    
        return $lang;
    }
    
    public function getLanguages()
    {
        return [
            ["id" => "arabic", "name" => "Arabic"],
            ["id" => "azerbaijani", "name" => "Azerbaijani"],
            ["id" => "catalan", "name" => "Catalan"],
            ["id" => "chinese", "name" => "Chinese"],
            ["id" => "croatian", "name" => "Croatian"],
            ["id" => "czech", "name" => "Czech"],
            ["id" => "danish", "name" => "Danish"],
            ["id" => "dutch", "name" => "Dutch"],
            ["id" => "english", "name" => "English"],
            ["id" => "estonian", "name" => "Estonian"],
            ["id" => "farsi", "name" => "Farsi"],
            ["id" => "french", "name" => "French"],
            ["id" => "german", "name" => "German"],
            ["id" => "hebrew", "name" => "Hebrew"],
            ["id" => "hungarian", "name" => "Hungarian"],
            ["id" => "indonesia", "name" => "Indonesia"],
            ["id" => "italian", "name" => "Italian"],
            ["id" => "macedonian", "name" => "Macedonian"],
            ["id" => "norwegian", "name" => "Norwegian"],
            ["id" => "portuguese-br", "name" => "Portuguese-br"],
            ["id" => "portuguese-pt", "name" => "Portuguese-pt"],
            ["id" => "romanian", "name" => "Romanian"],
            ["id" => "russian", "name" => "Russian"],
            ["id" => "spanish", "name" => "Spanish"],
            ["id" => "swedish", "name" => "Swedish"],
            ["id" => "turkish", "name" => "Turkish"],
            ["id" => "ukranian", "name" => "Ukranian"],
        ];
    }
    
    public function getCustomFields($type, $relid, $relid2, $admin = "", $order = "", $ordervalues = "", $hidepw = "")
    {
        global $_LANG;
        $customfields = [];
        $relid = $relid ?? 0;
        $relid2 = $relid2 ?? 0;
        static $customFieldCache = null;
    
        if (!$customFieldCache) {
            $customFieldCache = [];
        }
    
        if (isset($customFieldCache[$type][$relid])) {
            $customFieldsData = $customFieldCache[$type][$relid];
        } else {
            $customFieldsData = \App\Models\Customfield::where("type", $type)->where("relid", $relid)->get();
            $customFieldCache[$type][$relid] = $customFieldsData;
        }
    
        if (!$admin) {
            $customFieldsData = $customFieldsData->where("adminonly", "");
        }
        if ($order) {
            $customFieldsData = $customFieldsData->where("showorder", "on");
        }
    
        foreach ($customFieldsData->toArray() as $data) {
            $id = $data["id"];
            $fieldname = $admin ? $data["fieldname"] : \App\Models\Customfield::getFieldName($id, $data["fieldname"]);
            if (strpos($fieldname, "|")) {
                $fieldname = explode("|", $fieldname);
                $fieldname = trim($fieldname[1]);
            }
    
            $fieldtype = $data["fieldtype"];
            $description = $admin ? $data["description"] : \App\Models\Customfield::getDescription($id, $data["description"]);
            $fieldoptions = $data["fieldoptions"];
            $required = $data["required"];
            $adminonly = $data["adminonly"];
            $customfieldval = is_array($ordervalues) && array_key_exists($id, $ordervalues) ? $ordervalues[$id] : "";
            $input = "";
    
            if ($relid2) {
                $customFieldValue = \App\Models\Customfieldsvalue::firstOrNew(["fieldid" => $id, "relid" => $relid2]);
                if ($customFieldValue->exists) {
                    $customfieldval = $customFieldValue->value;
                }
    
                $fieldloadhooks = \App\Helpers\Hooks::run_hook("CustomFieldLoad", ["fieldid" => $id, "relid" => $relid2, "value" => $customfieldval]);
                if (count($fieldloadhooks) > 0) {
                    $fieldloadhookslast = array_pop($fieldloadhooks);
                    if (array_key_exists("value", $fieldloadhookslast)) {
                        $customfieldval = $fieldloadhookslast["value"];
                    }
                }
            }
    
            $rawvalue = $customfieldval;
            $customfieldval = \App\Helpers\Sanitize::makeSafeForOutput($customfieldval);
            if ($required == "on") {
                $required = "*";
            }
    
            switch ($fieldtype) {
                case "text":
                case "password":
                    if ($admin) {
                        $input = "<input type=\"text\" name=\"customfield[$id]\" id=\"customfield$id\" value=\"$customfieldval\" size=\"30\" class=\"form-control\" />";
                    }
                break;
                    
                case "link":
                    $webaddr = trim($customfieldval);
                    if (substr($webaddr, 0, 4) == "www.") {
                        $webaddr = "http://" . $webaddr;
                    }
                    $input = "<input type=\"text\" name=\"customfield[$id]\" id=\"customfield$id\" value=\"$customfieldval\" size=\"40\" class=\"form-control\" /> " . ($customfieldval ? "<a href=\"$webaddr\" target=\"_blank\">www</a>" : "");
                    $customfieldval = "<a href=\"$webaddr\" target=\"_blank\">$customfieldval</a>";
                break;

                case "password":
                    $input = "<input type=\"password\" name=\"customfield[$id]\" id=\"customfield$id\" value=\"$customfieldval\" size=\"30\" class=\"form-control\" />";
                    if ($hidepw) {
                        $pwlen = strlen($customfieldval);
                        $customfieldval = str_repeat("*", $pwlen);
                    }
                break;

                case "textarea":
                    $input = "<textarea name=\"customfield[$id]\" id=\"customfield$id\" rows=\"3\" class=\"form-control\">$customfieldval</textarea>";
                break;

                case "dropdown":
                    $input = "<select name=\"customfield[$id]\" id=\"customfield$id\" class=\"form-control\">";
                    if (!$required) {
                        $input .= "<option value=\"\">" . $_LANG["none"] . "</option>";
                    }
                    $fieldoptions = explode(",", $fieldoptions);
                    foreach ($fieldoptions as $optionvalue) {
                        $input .= "<option value=\"$optionvalue\"";
                        if ($customfieldval == $optionvalue) {
                            $input .= " selected";
                        }
                        if (strpos($optionvalue, "|")) {
                            $optionvalue = explode("|", $optionvalue);
                            $optionvalue = trim($optionvalue[1]);
                        }
                        $input .= ">$optionvalue</option>";
                    }
                    $input .= "</select>";
                break;

                case "tickbox":
                    $input = "<input type=\"checkbox\" name=\"customfield[$id]\" id=\"customfield$id\"";
                    if ($customfieldval == "on") {
                        $input .= " checked";
                    }
                    $input .= " />";
                break;
            }
    
            if ($fieldtype != "link" && strpos($customfieldval, "|")) {
                $customfieldval = explode("|", $customfieldval);
                $customfieldval = trim($customfieldval[1]);
            }
    
            $customfields[] = [
                "id" => $id,
                "textid" => preg_replace("/[^0-9a-z]/i", "", strtolower($fieldname)),
                "name" => $fieldname,
                "description" => $description,
                "type" => $fieldtype,
                "input" => $input,
                "value" => $customfieldval,
                "rawvalue" => $rawvalue,
                "required" => $required,
                "adminonly" => $adminonly
            ];
        }
    
        return $customfields;
    }

    public function DeleteContact(Int $contactid)
    {
        $contact = \App\Models\Contact::find($contactid);
    
        if (!$contact) {
            return [
                "result" => "error",
                "message" => "Contact ID Not Found"
            ];
        } else {
            $userid = $contact->userid;
            $name = "{$contact->firstname} {$contact->lastname}";
            $email = $contact->email;
    
            \App\Models\Client::where('billingcid', $contactid)
                ->where("id", $userid)
                ->update(['billingcid' => ""]);
    
            \App\Models\Order::where('contactid', $contactid)
                ->update(['contactid' => 0]);
    
            \App\Models\AuthnAccountLink::where('client_id', $userid)
                ->where('contact_id', $contactid)
                ->delete();
    
            \App\Helpers\Hooks::run_hook("ContactDelete", ["userid" => $userid, "contactid" => $contactid]);
            \App\Helpers\LogActivity::Save("Deleted Contact - User ID: $userid - Contact ID: $contactid - Contact Name: $name - Contact Email: $email", $userid);
    
            $contact->delete();
    
            return [
                "result" => "success",
                "message" => $contactid
            ];
        }
    }
    
    public function GetClients(array $params)
    {
        extract($params);
        $limitstart = $limitstart ?? 0;
        $limitnum = $limitnum ?? 25;
        $sorting = in_array($sorting, ["ASC", "DESC"]) ? $sorting : 'ASC';
        $status = in_array($status, ["Active", "Inactive", "Closed"]) ? $status : '';
    
        $getClient = \App\Models\Client::select('id', 'firstname', 'lastname', 'companyname', 'email', 'groupid', 'datecreated', 'status')
            ->orderBy('lastname', $sorting)
            ->orderBy('firstname', $sorting)
            ->orderBy('companyname', $sorting)
            ->skip($limitstart)
            ->take($limitnum);
    
        if (!empty(trim($search))) {
            $getClient->where(function ($r) use ($search) {
                $r->where('email', 'LIKE', "%{$search}%")
                    ->orWhere('firstname', 'LIKE', "%{$search}%")
                    ->orWhere('lastname', 'LIKE', "%{$search}%")
                    ->orWhere('companyname', 'LIKE', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(firstname, ' ', lastname) LIKE '" . $search . "%'"));
            });
        }
    
        $data = $getClient->get();
        $count = $getClient->count();
    
        return [
            "result" => "success",
            "totalresults" => $count,
            "startnumber" => $limitstart,
            "numreturned" => count($data),
            "clients" => [
                'client' => $data
            ]
        ];
    }
    
    public function GetClientPassword(Int $userid, $email = '')
    {
        $getClient = $userid
            ? \App\Models\Client::find($userid)->first('password')
            : \App\Models\Client::where('email', $email)->first('password');
    
        if (is_null($getClient)) {
            return ["result" => "error", "message" => "Client ID Not Found"];
        } else {
            return ["result" => "success", "password" => $getClient->password];
        }
    }
    
    public function GetClientGroups()
    {
        $count = \App\Models\Clientgroup::count();
        $data = \App\Models\Clientgroup::all();
    
        return [
            "result" => "success",
            "totalresults" => $count,
            "groups" => ["group" => $data]
        ];
    }
    
    public function GetClientsDetails(array $params)
    {
        extract($params);
    
        if ($clientid) {
            $getClient = \App\Models\Client::where('id', $clientid);
        } elseif ($email) {
            $getClient = \App\Models\Client::where('email', $email);
        } else {
            return ["result" => "error", "message" => "Either clientid Or email Is Required"];
        }
    
        if ($getClient->count() === 0) {
            return ["result" => "error", "message" => "Client Not Found"];
        } else {
            $clientid = $getClient->first('id')->id;
            $clientsdetails = $this->DataClientsDetails($clientid);
            unset($clientsdetails["model"]);
            $currency = \App\Models\Currency::find($clientsdetails["currency"])->code;
            $clientsdetails["currency_code"] = $currency;
            $apiresults = array_merge(["result" => "success"], $clientsdetails);
    
            if ($clientsdetails["cctype"]) {
                $apiresults["warning"] = "Credit Card related parameters are now deprecated and have been removed. Use GetPayMethods instead.";
            }
    
            unset($clientsdetails["cctype"], $clientsdetails["cclastfour"], $clientsdetails["gatewayid"]);
            $clientHelpers = new \App\Helpers\ClientHelper();
            $apiresults["stats"] = $clientHelpers->getClientsStats($clientid);
    
            return $apiresults;
        }
    }

    public function DataClientsDetails($userid = "", $contactid = "")
    {
        if (!$userid) {
            $userid = session("uid");
        }
    
        $client = \App\Models\Client::find($userid);
        $countries = new \App\Helpers\Country();
    
        if (is_null($client)) {
            return false;
        }
    
        $details = [];
        $details["userid"] = $client->id;
        $details["id"] = $details["userid"];
    
        $billingContact = false;
    
        if ($contactid === "billing") {
            $contactid = $client->billingContactId;
            $billingContact = true;
        } else {
            $contactid = (int)$contactid;
        }
    
        $contact = null;
    
        if ($contactid > 0) {
            try {
                $contact = \App\Models\Contact::findOrFail($contactid);
    
                $details["firstname"] = $contact->firstname;
                $details["lastname"] = $contact->lastname;
                $details["companyname"] = $contact->companyname;
                $details["email"] = $contact->email;
                $details["address1"] = $contact->address1;
                $details["address2"] = $contact->address2;
                $details["city"] = $contact->city;
                $details["fullstate"] = $contact->state;
                $details["state"] = $details["fullstate"];
                $details["postcode"] = $contact->postcode;
                $details["countrycode"] = $contact->country;
                $details["country"] = $details["countrycode"];
                $details["phonenumber"] = $contact->phoneNumber;
                $details["tax_id"] = $contact->taxId ?: $client->taxId;
                $details["password"] = $contact->passwordHash;
                $details["email_preferences"] = $contact->getEmailPreferences();
                $details["domainemails"] = $contact->receivesDomainEmails;
                $details["generalemails"] = $contact->receivesGeneralEmails;
                $details["invoiceemails"] = $contact->receivesInvoiceEmails;
                $details["productemails"] = $contact->receivesProductEmails;
                $details["supportemails"] = $contact->receivesSupportEmails;
                $details["affiliateemails"] = $contact->receivesAffiliateEmails;
                $details["model"] = $contact;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                if ($billingContact) {
                    $client->billingContactId = 0;
                    $client->save();
                }
            }
        }
    
        if (is_null($contact)) {
            $details["uuid"] = $client->uuid;
            $details["firstname"] = $client->firstname;
            $details["lastname"] = $client->lastname;
            $details["companyname"] = $client->companyname;
            $details["email"] = $client->email;
            $details["address1"] = $client->address1;
            $details["address2"] = $client->address2;
            $details["city"] = $client->city;
            $details["fullstate"] = $client->state;
            $details["state"] = $details["fullstate"];
            $details["postcode"] = $client->postcode;
            $details["countrycode"] = $client->country;
            $details["country"] = $details["countrycode"];
            $details["phonenumber"] = $client->phoneNumber;
            $details["tax_id"] = $client->taxId;
            $details["password"] = $client->passwordHash;
            $details["email_preferences"] = $client->getEmailPreferences();
            $details["model"] = $client;
        }
    
        $details["fullname"] = $details["firstname"] . " " . $details["lastname"];
    
        if (!@$details["uuid"]) {
            $uuid = \Ramsey\Uuid\Uuid::uuid4();
            $details["uuid"] = $uuid->toString();
        }
    
        if ($details["country"] === "GB") {
            $postcode = strtoupper(preg_replace("/[^A-Z0-9]/", "", $details["postcode"]));
            $details["postcode"] = strlen($postcode) === 5 ? substr($postcode, 0, 2) . " " . substr($postcode, 2) :
                                  (strlen($postcode) === 6 ? substr($postcode, 0, 3) . " " . substr($postcode, 3) :
                                  (strlen($postcode) === 7 ? substr($postcode, 0, 4) . " " . substr($postcode, 4) : $details["postcode"]));
        }
    
        $ClientHelper = new \App\Helpers\ClientHelper();
    
        $details["statecode"] = $ClientHelper->convertStateToCode($details["state"], $details["country"]);
    
        $details["countryname"] = $countries->getName($details["countrycode"]);
    
        $details = $ClientHelper->formatPhoneNumber($details);
    
        $defaultPayMethod = $ClientHelper->getClientDefaultCardDetails($client->id);
    
        $details["billingcid"] = $client->billingContactId;
        $details["notes"] = $client->notes;
        $details["twofaenabled"] = (bool)$client->twoFactorAuthModule;
        $details["currency"] = $client->currency;
        $details["defaultgateway"] = $client->defaultPaymentGateway;
        $details["cctype"] = $defaultPayMethod["cardtype"];
        $details["cclastfour"] = $defaultPayMethod["cardlastfour"];
        $details["gatewayid"] = $defaultPayMethod["gatewayid"];
        $details["securityqid"] = $client->securityQuestionId;
        $details["securityqans"] = $client->securityQuestionAnswer;
        $details["groupid"] = $client->groupId;
        $details["status"] = $client->status;
        $details["credit"] = $client->credit;
        $details["taxexempt"] = $client->taxExempt;
        $details["latefeeoveride"] = $client->overrideLateFee;
        $details["overideduenotices"] = $client->overrideOverdueNotices;
        $details["separateinvoices"] = $client->separateInvoices;
        $details["disableautocc"] = $client->disableAutomaticCreditCardProcessing;
        $details["emailoptout"] = $client->emailOptOut;
        $details["marketing_emails_opt_in"] = $client->marketingEmailsOptIn;
        $details["overrideautoclose"] = $client->overrideAutoClose;
        $details["allowSingleSignOn"] = $client->allowSso;
        $details["language"] = $client->language;
        $details["isOptedInToMarketingEmails"] = $client->isOptedInToMarketingEmails();
    
        $lastlogin = $client->lastlogin;
        $details["lastlogin"] = $lastlogin === "1970-01-01 00:00:00" 
            ? "No Login Logged" 
            : "Date: " . $this->fromMySQLDate($lastlogin, "time") . "<br>IP Address: " . $client->lastLoginIp . "<br>Host: " . $client->lastLoginHostname;
    
        $customfields = $this->getCustomFields("client", "", $client->id, true);
        foreach ($customfields as $i => $value) {
            $details["customfields" . ($i + 1)] = $value["value"];
            $details["customfields"][] = ["id" => $value["id"], "value" => $value["value"]];
        }
    
        return $details;
    }

    
    public function fromMySQLDate($date, $time = false, $client = false, $zerodateval = false)
    {
        // Handle specific zero date format
        if (substr($date, 0, 11) == "-0001-11-30") {
            $date = "0000-00-00";
        }

        // Convert Carbon instance to string and check for zero timestamp
        if ($date instanceof Carbon) {
            $date = (string) $date;
            if ((string) $date === (string) Carbon::createFromTimestamp(0, "UTC")) {
                $date = "0000-00-00";
            }
        }

        // Handle zero date
        $isZeroDate = substr($date, 0, 10) == "0000-00-00";
        if ($isZeroDate) {
            if ($zerodateval) {
                return $zerodateval;
            }
            $dateFormat = Carbon::now();
            $dateFormat = $this->getAdminDateFormat($dateFormat);
            return str_replace(["d", "m", "Y", "H:i"], ["00", "00", "0000", ($time ? "00:00" : "")], $dateFormat);
        }

        // Parse date and handle exceptions
        try {
            $date = Carbon::parse($date);
        } catch (\Exception $e) {
            throw new Fatal("Invalid date format provided: " . $date);
        }

        // Format date based on client and time flags
        if ($client && $time) {
            return $date->format($this->getAdminDateFormat());
        }
        if ($client) {
            return Carbon::parse($date)->toClientDateFormat();
        }
        if ($time) {
            return $date->format($this->getAdminDateFormat(true));
        }

        return $date->format($this->getAdminDateFormat(false));
    }

    public function getAdminDateFormat($withTime = false)
    {
        $dateFormat = Cfg::get('DateFormat') ?: "DD/MM/YYYY";
        $dateFormat = str_replace(["DD", "MM", "YYYY"], ["d", "m", "Y"], $dateFormat);
        if ($withTime) {
            $dateFormat .= " H:i";
        }
        return $dateFormat;
    }

    public function toAdminDateTimeFormat()
    {
        return $this->getAdminDateFormat(true);
    }

    public function toClientDateFormat()
    {
        // /$results = run_hook("FormatDateForClientAreaOutput", array("date" => $this));
        // event(new FormatDateForClientAreaOutput(array("date" => $this)));
        //  foreach ($results as $result) {
        //     if ($result && is_string($result)) {
        //         return $result;
        //    }
        // }        
        // event(new FormatDateForClientAreaOutput(array("date" => $this)));
        return $this->getClientDateFormat(false);
    }

    public function getClientDateFormat($withTime = false)
    {
        $clientDateFormat = Cfg::get('ClientDateFormat');
        switch ($clientDateFormat) {
            case "full":
                $dateFormat = "jS F Y";
                break;
            case "shortmonth":
                $dateFormat = "jS M Y";
                break;
            case "fullday":
                $dateFormat = "l, F jS, Y";
                break;
            default:
                $dateFormat = $this->getAdminDateFormat();
        }
        if ($withTime) {
            $dateFormat .= " (H:i)";
        }
        return $dateFormat;
    }

    public function parseDateRangeValue($value, $withTime = false)
    {
        $format = $this->getAdminDateFormat($withTime);

        // Use client date format if not admin
        if (!auth()->guard("admin")->check()) {
            $format = $this->getClientDateFormat($withTime);
        }

        $value = explode(" - ", $value);
        $firstDate = Carbon::createFromFormat($format, $value[0]);
        if (!$withTime) {
            $firstDate->startOfDay();
        }

        $secondDate = !empty($value[1]) ? Carbon::createFromFormat($format, $value[1]) : $firstDate->copy();
        if (!$withTime) {
            $secondDate->endOfDay();
        }

        return [
            $firstDate,
            $secondDate,
            "from" => $firstDate,
            "to" => $secondDate
        ];
    }

    public function GetClientsAddons(array $params)
    {
        extract($params);

        $query = DB::table($this->prefix . "hostingaddons")
            ->distinct()
            ->join($this->prefix . "hosting", $this->prefix . "hosting.id", "=", $this->prefix . "hostingaddons.hostingid")
            ->join($this->prefix . "addons", $this->prefix . "addons.id", "=", $this->prefix . "hostingaddons.addonid", "LEFT");

        if (!empty($serviceid)) {
            if (is_numeric($serviceid)) {
                $query->where($this->prefix . "hostingaddons.hostingid", "=", $serviceid);
            } else {
                $serviceids = array_map("trim", explode(",", $serviceid));
                $query->whereIn($this->prefix . "hostingaddons.hostingid", $serviceids);
            }
        }

        if (!empty($clientid)) {
            $query->where($this->prefix . "hosting.userid", "=", $clientid);
        }

        if (!empty($addonid)) {
            $query->where($this->prefix . "hostingaddons.addonid", "=", $addonid);
        }

        $query->orderBy($this->prefix . "hostingaddons.id", "ASC");

        $result = $query->get([
            $this->prefix . "hostingaddons.*",
            $this->prefix . "hosting.userid",
            $this->prefix . "addons.name AS addon_name"
        ]);

        $apiresults = [
            "result" => "success",
            "serviceid" => $serviceid,
            "clientid" => $clientid,
            "totalresults" => count($result)
        ];

        foreach ($result as $data) {
            $addonarray = [
                "id" => $data->id,
                "userid" => $data->userid,
                "orderid" => $data->orderid,
                "serviceid" => $data->hostingid,
                "addonid" => $data->addonid,
                "name" => $data->name ?: $data->addon_name,
                "setupfee" => $data->setupfee,
                "recurring" => $data->recurring,
                "billingcycle" => $data->billingcycle,
                "tax" => $data->tax,
                "status" => $data->status,
                "regdate" => $data->regdate,
                "nextduedate" => $data->nextduedate,
                "nextinvoicedate" => $data->nextinvoicedate,
                "paymentmethod" => $data->paymentmethod,
                "notes" => $data->notes
            ];
            $apiresults["addons"]["addon"][] = $addonarray;
        }

        return $apiresults;
    }
    
    // GetClientsDomains params
    //     'limitstart' => (int) $request->limitstart,
    //     'limitnum' => (int) $request->limitnum,
    //     'clientid' => (int) $request->clientid,
    //     'domainid' => (int) $request->domainid,
    //     'domain' => $request->domain,
    public function GetClientsDomains_2024(array $params)
    {
        // Extract parameters
        extract($params);

        // Initialize query
        $getDomain = Domain::orderBy('id', 'ASC');

        // Filter by client ID
        if (!empty($clientid)) {
            $getDomain->where('userid', $clientid);
        }

        // Filter by domain ID
        if (!empty($domainid)) {
            $getDomain->where('id', $domainid);
        }

        // Filter by domain name
        if (!empty($domain)) {
            $getDomain->where('domain', $domain);
        }

        // Count total results
        $count = $getDomain->count();

        // Apply pagination
        $getDomain->skip($limitstart)->take($limitnum);

        // Select fields with payment method name
        $getDomain->select(DB::raw($this->prefix . 'domains.*, (SELECT ' . $this->prefix . 'paymentgateways.value FROM ' . $this->prefix . 'paymentgateways WHERE ' . $this->prefix . 'paymentgateways.gateway=' . $this->prefix . 'domains.paymentmethod AND ' . $this->prefix . 'paymentgateways.setting=\'name\' LIMIT 1) AS paymentmethodname'));

        // Execute query
        $datas = $getDomain->get();

        // Prepare API results
        $apiresults = [
            "result" => "success",
            "clientid" => $clientid,
            "domainid" => $domainid,
            "totalresults" => $count,
            "startnumber" => $limitstart,
            "numreturned" => count($datas)
        ];

        // Check if there are results
        if (!$apiresults) {
            $apiresults["domains"] = "";
        } else {
            $registrarClass = new ModuleRegistrar();

            // Process each domain
            foreach ($datas as $data) {
                $id = $data["id"];
                $userid = $data["userid"];
                $orderid = $data["orderid"];
                $type = $data["type"];
                $registrationdate = $data["registrationdate"];
                $domain = $data["domain"];
                $firstpaymentamount = $data["firstpaymentamount"];
                $recurringamount = $data["recurringamount"];
                $registrar = $data["registrar"];
                $registrationperiod = $data["registrationperiod"];
                $expirydate = $data["expirydate"];
                $nextduedate = $data["nextduedate"];
                $status = $data["status"];
                $subscriptionid = $data["subscriptionid"];
                $promoid = $data["promoid"];
                $additionalnotes = $data["additionalnotes"];
                $paymentmethod = $data["paymentmethod"];
                $paymentmethodname = $data["paymentmethodname"];
                $dnsmanagement = $data["dnsmanagement"];
                $emailforwarding = $data["emailforwarding"];
                $idprotection = $data["idprotection"];
                $donotrenew = $data["donotrenew"];
                $nameservers = [];

                // Get domain parts
                $domainparts = explode(".", $domain, 2);
                $params = [
                    "domainid" => $id,
                    "sld" => $domainparts[0],
                    "tld" => $domainparts[1],
                    "regperiod" => $registrationperiod,
                    "registrar" => $registrar
                ];

                // Get nameservers
                $nameservers = $registrarClass->RegGetNameservers($params);
                $nameservers["nameservers"] = true;

                // Merge domain data with nameservers
                $apiresults["domains"]["domain"][] = array_merge([
                    "id" => $id,
                    "userid" => $userid,
                    "orderid" => $orderid,
                    "regtype" => $type,
                    "domainname" => $domain,
                    "registrar" => $registrar,
                    "regperiod" => $registrationperiod,
                    "firstpaymentamount" => $firstpaymentamount,
                    "recurringamount" => $recurringamount,
                    "paymentmethod" => $paymentmethod,
                    "paymentmethodname" => $paymentmethodname,
                    "regdate" => $registrationdate,
                    "expirydate" => $expirydate,
                    "nextduedate" => $nextduedate,
                    "status" => $status,
                    "subscriptionid" => $subscriptionid,
                    "promoid" => $promoid,
                    "dnsmanagement" => $dnsmanagement,
                    "emailforwarding" => $emailforwarding,
                    "idprotection" => $idprotection,
                    "donotrenew" => $donotrenew,
                    "notes" => $additionalnotes
                ], $nameservers);
            }
        }

        // Return API results
        return $apiresults;
    }

    public function GetClientsDomains(array $params)
    {
        // Extract parameters
        extract($params);
 
        // Initialize query
        $getDomain = Domain::orderBy('id', 'ASC');

        // Filter by client ID
        if (!empty($clientid)) {
            $getDomain->where('userid', $clientid);
        }

        // Filter by domain ID
        if (!empty($domainid)) {
            $getDomain->where('id', $domainid);
        }

        // Filter by domain name
        if (!empty($domain)) {
            $getDomain->where('domain', $domain);
        }

        // Count total results
        $count = $getDomain->count();

        // Apply pagination
        $getDomain->skip($limitstart)->take($limitnum);

        // Select fields with payment method name using COLLATE
        $getDomain->select(DB::raw(
            $this->prefix . 'domains.*, 
            (SELECT ' . $this->prefix . 'paymentgateways.value 
            FROM ' . $this->prefix . 'paymentgateways 
            WHERE ' . $this->prefix . 'paymentgateways.gateway COLLATE utf8mb3_unicode_ci = ' . $this->prefix . 'domains.paymentmethod COLLATE utf8mb3_unicode_ci 
            AND ' . $this->prefix . 'paymentgateways.setting=\'name\' LIMIT 1) AS paymentmethodname'
        ));

        // Execute query
        $datas = $getDomain->get();

        // Prepare API results
        $apiresults = [
            "result" => "success",
            "clientid" => $clientid,
            "domainid" => $domainid,
            "totalresults" => $count,
            "startnumber" => $limitstart,
            "numreturned" => count($datas)
        ];

        // Check if there are results
        if (!$apiresults) {
            $apiresults["domains"] = "";
        } else {
            $registrarClass = new ModuleRegistrar();

            // Process each domain
            foreach ($datas as $data) {
                $id = $data["id"];
                $userid = $data["userid"];
                $orderid = $data["orderid"];
                $type = $data["type"];
                $registrationdate = $data["registrationdate"];
                $domain = $data["domain"];
                $firstpaymentamount = $data["firstpaymentamount"];
                $recurringamount = $data["recurringamount"];
                $registrar = $data["registrar"];
                $registrationperiod = $data["registrationperiod"];
                $expirydate = $data["expirydate"];
                $nextduedate = $data["nextduedate"];
                $status = $data["status"];
                $subscriptionid = $data["subscriptionid"];
                $promoid = $data["promoid"];
                $additionalnotes = $data["additionalnotes"];
                $paymentmethod = $data["paymentmethod"];
                $paymentmethodname = $data["paymentmethodname"];
                $dnsmanagement = $data["dnsmanagement"];
                $emailforwarding = $data["emailforwarding"];
                $idprotection = $data["idprotection"];
                $donotrenew = $data["donotrenew"];
                $nameservers = [];

                // Get domain parts
                $domainparts = explode(".", $domain, 2);
                $params = [
                    "domainid" => $id,
                    "sld" => $domainparts[0],
                    "tld" => $domainparts[1],
                    "regperiod" => $registrationperiod,
                    "registrar" => $registrar
                ];

                // Get nameservers
                $nameservers = $registrarClass->RegGetNameservers($params);
                $nameservers["nameservers"] = true;

                // Merge domain data with nameservers
                $apiresults["domains"]["domain"][] = array_merge([
                    "id" => $id,
                    "userid" => $userid,
                    "orderid" => $orderid,
                    "regtype" => $type,
                    "domainname" => $domain,
                    "registrar" => $registrar,
                    "regperiod" => $registrationperiod,
                    "firstpaymentamount" => $firstpaymentamount,
                    "recurringamount" => $recurringamount,
                    "paymentmethod" => $paymentmethod,
                    "paymentmethodname" => $paymentmethodname,
                    "regdate" => $registrationdate,
                    "expirydate" => $expirydate,
                    "nextduedate" => $nextduedate,
                    "status" => $status,
                    "subscriptionid" => $subscriptionid,
                    "promoid" => $promoid,
                    "dnsmanagement" => $dnsmanagement,
                    "emailforwarding" => $emailforwarding,
                    "idprotection" => $idprotection,
                    "donotrenew" => $donotrenew,
                    "notes" => $additionalnotes
                ], $nameservers);
            }
        }

        // Return API results
        return $apiresults;
    }
    
    public function RegGetNameservers($params){
        return $this->regcallfunction($params, "GetNameservers");
    }

    public function regcallfunction($params, $function, $noarr = false){
        $registrar = $params["registrar"];
        event(new PreRegistrar($params));
        $functionExists = $functionSuccessful = false;
    }

    // Params for GetContacts array
    // 'limitstart'  => (int) $request->limitstart,
    // 'limitnum'    => (int) $request->limitnum,
    // 'userid'      => (int) $request->userid,
    // 'firstname'   => $request->firstname,
    // 'lastname'    => $request->lastname,
    // 'companyname' => $request->companyname,
    // 'email'       => $request->email,
    // 'address1'    => $request->address1,
    // 'address2'    => $request->address2,
    // 'city'        => $request->city,
    // 'state'       => $request->state,
    // 'postcode'    => $request->postcode,
    // 'country'     => $request->country,
    // 'phonenumber' => $request->phonenumber,
    public function GetContacts(array $params)
    {
        extract($params);

        $limitstart = $limitstart ?? 0;
        $limitnum = $limitnum ?? 25;

        unset($params['limitstart'], $params['limitnum']);

        DB::enableQueryLog();

        $getContact = Contact::orderBy('id', 'ASC');

        foreach ($params as $key => $value) {
            if (!empty($value) || $value !== null) {
                $getContact->where($key, $value);
            }
        }

        $count = $getContact->count();

        $getContact->skip($limitstart)->take($limitnum);

        $getContact->select([
            'id', 'userid', 'firstname', 'lastname', 'email', 'address1', 'address2', 
            'city', 'state', 'postcode', 'country', 'phonenumber', 'domainemails', 
            'generalemails', 'invoiceemails', 'productemails', 'supportemails', 
            'affiliateemails', 'created_at', 'updated_at'
        ]);

        $data = $getContact->get();

        return [
            "result" => "success",
            "totalresults" => $count,
            "startnumber" => $limitstart,
            "numreturned" => count($data),
            "contacts" => ['contact' => $data]
        ];
    }

    // GetClientsProducts params array
    // 'limitstart'  => (int) $request->limitstart,
    // 'limitnum'    => (int) $request->limitnum,
    // 'clientid'    => (int) $request->clientid,
    // 'serviceid'   => (int) $request->serviceid,
    // 'pid'         => (int) $request->pid,
    // 'domain'      => $request->domain,
    // 'username2'   => $request->username2
    public function GetClientsProducts(array $params)
    {
        extract($params);
        unset($params['limitstart'], $params['limitnum']);

        $prefix = $this->prefix;
        $tblhosting = $prefix . 'hosting';
        $tblproducts = $prefix . 'products';
        $tblproductgroups = $prefix . 'productgroups';
        $tblservers = "{$prefix}servers";
        $tblpaymentgateways = "{$prefix}paymentgateways";

        $getProduct = DB::table($tblhosting)
            ->join($tblproducts, "{$tblhosting}.packageid", '=', "{$tblproducts}.id")
            ->join($tblproductgroups, "{$tblproducts}.gid", '=', "{$tblproductgroups}.id");

        if ($clientid) {
            $getProduct->where("{$tblhosting}.userid", $clientid);
        }
        if ($serviceid) {
            $getProduct->where("{$tblhosting}.id", $serviceid);
        }
        if ($pid) {
            $getProduct->where("{$tblhosting}.packageid", $pid);
        }
        if ($domain) {
            $getProduct->where("{$tblhosting}.domain", $domain);
        }
        if ($username2) {
            $getProduct->where("{$tblhosting}.username", $username2);
        }

        $limitstart = (int) $limitstart;
        $limitnum = (int) $limitnum ?: 999999;
        $totalresults = $getProduct->count();

        $getData = DB::table($tblhosting)
            ->selectRaw("{$tblhosting}.*, {$tblproductgroups}.name as group_name, {$tblproductgroups}.id AS group_id, {$tblproducts}.name, 
                (SELECT CONCAT(name, '|', ipaddress, '|', hostname) FROM {$tblservers} WHERE {$tblservers}.id = {$tblhosting}.server) AS serverdetails, 
                (SELECT {$tblpaymentgateways}.value FROM {$tblpaymentgateways} WHERE {$tblpaymentgateways}.gateway = {$tblhosting}.paymentmethod AND {$tblpaymentgateways}.setting = 'name' LIMIT 1) AS paymentmethodname")
            ->join($tblproducts, "{$tblhosting}.packageid", '=', "{$tblproducts}.id")
            ->join($tblproductgroups, "{$tblproducts}.gid", '=', "{$tblproductgroups}.id");

        if ($clientid) {
            $getData->where("{$tblhosting}.userid", $clientid);
        }
        if ($serviceid) {
            $getData->where("{$tblhosting}.id", $serviceid);
        }
        if ($pid) {
            $getData->where("{$tblhosting}.packageid", $pid);
        }
        if ($domain) {
            $getData->where("{$tblhosting}.domain", $domain);
        }
        if ($username2) {
            $getData->where("{$tblhosting}.username", $username2);
        }

        $getData = $getData->get();

        $pwd = new Pwd();
        $apiresults = [];

        foreach ($getData as $d) {
            $id = $d->id;
            $userid = $d->userid;
            $orderid = $d->orderid;
            $pid = $d->packageid;
            $name = $d->name ?: Product::find($pid, ['name'])->name;
            $language = ClientModel::find($userid, ['language'])->language ?? \App\Helpers\Cfg::get("Language");
            $translatedName = Product::find($d->packageid, ['name'])->name;
            $groupname = $d->group_name;
            $translatedGroupName = Product::find($d->group_id, ['name'])->name;
            $server = $d->server;
            $regdate = $d->regdate;
            $domain = $d->domain;
            $paymentmethod = $d->paymentmethod;
            $paymentmethodname = $d->paymentmethodname;
            $firstpaymentamount = $d->firstpaymentamount;
            $recurringamount = $d->amount;
            $billingcycle = $d->billingcycle;
            $nextduedate = $d->nextduedate;
            $domainstatus = $d->domainstatus;
            $username = $d->username;
            $password = $pwd->decrypt($d->password);
            $notes = $d->notes;
            $subscriptionid = $d->subscriptionid;
            $promoid = $d->promoid;
            $ipaddress = $d->serverdetails;
            $overideautosuspend = $d->overideautosuspend;
            $overidesuspenduntil = $d->overidesuspenduntil;
            $ns1 = $d->ns1;
            $ns2 = $d->ns2;
            $dedicatedip = $d->dedicatedip;
            $assignedips = $d->assignedips;
            $diskusage = $d->diskusage;
            $disklimit = $d->disklimit;
            $bwusage = $d->bwusage;
            $bwlimit = $d->bwlimit;
            $lastupdate = $d->lastupdate;
            $serverdetails = explode("|", $d->serverdetails);

            $customfieldsdata = [];
            $customfields = $this->getCustomFields("product", $pid, $id, "on", "");
            foreach ($customfields as $customfield) {
                $customfieldsdata[] = [
                    "id" => $customfield["id"],
                    "name" => $customfield["name"],
                    "translated_name" => $customfield["name"],
                    "value" => $customfield["value"]
                ];
            }

            $configoptionsdata = [];
            $adminHelpers = new AdminFunctions();
            $currency = $adminHelpers->getCurrency($userid);
            $configoptions = $this->getCartConfigOptions($pid, "", $billingcycle, $id, "", true);

            foreach ($configoptions as $configoption) {
                switch ($configoption["optiontype"]) {
                    case 1:
                        $type = "dropdown";
                        break;
                    case 2:
                        $type = "radio";
                        break;
                    case 3:
                        $type = "yesno";
                        break;
                    case 4:
                        $type = "quantity";
                        break;
                    default:
                        $type = "";
                }
                $value = ($configoption["optiontype"] == 3 || $configoption["optiontype"] == 4) ? $configoption["selectedqty"] : $configoption["selectedoption"];
                $configoptionsdata[] = [
                    "id" => $configoption["id"],
                    "option" => $configoption["optionname"],
                    "type" => $type,
                    "value" => $value
                ];
            }

            $apiresults["products"]["product"][] = [
                "id" => $id,
                "clientid" => $userid,
                "orderid" => $orderid,
                "pid" => $pid,
                "regdate" => $regdate,
                "name" => $name,
                "translated_name" => $translatedName,
                "groupname" => $groupname,
                "translated_groupname" => $translatedGroupName,
                "domain" => $domain,
                "dedicatedip" => $dedicatedip,
                "serverid" => $server,
                "servername" => $serverdetails[0],
                "serverip" => $serverdetails[1],
                "serverhostname" => $serverdetails[2],
                "suspensionreason" => $d->suspendreason,
                "firstpaymentamount" => $firstpaymentamount,
                "recurringamount" => $recurringamount,
                "paymentmethod" => $paymentmethod,
                "paymentmethodname" => $paymentmethodname,
                "billingcycle" => $billingcycle,
                "nextduedate" => $nextduedate,
                "status" => $domainstatus,
                "username" => $username,
                "password" => $password,
                "subscriptionid" => $subscriptionid,
                "promoid" => $promoid,
                "overideautosuspend" => $overideautosuspend,
                "overidesuspenduntil" => $overidesuspenduntil,
                "ns1" => $ns1,
                "ns2" => $ns2,
                "dedicatedip" => $dedicatedip,
                "assignedips" => $assignedips,
                "notes" => $notes,
                "diskusage" => $diskusage,
                "disklimit" => $disklimit,
                "bwusage" => $bwusage,
                "bwlimit" => $bwlimit,
                "lastupdate" => $lastupdate,
                "customfields" => ["customfield" => $customfieldsdata],
                "configoptions" => ["configoption" => $configoptionsdata]
            ];
        }

        return $apiresults;
    }
    
    public function getCartConfigOptions($pid, $values, $cycle, $accountid = "", $orderform = "", $showHiddenOverride = false)
    {
        $adminHelpers = new \App\Helpers\AdminFunctions();
        $currency = $adminHelpers->getCurrency();
        $configoptions = [];
        $cycle = strtolower(str_replace(['-', ' '], '', $cycle));
        if ($cycle == "onetime") {
            $cycle = "monthly";
        }
        $showhidden = $showHiddenOverride || (Auth::id() && (defined("ADMINAREA") || defined("APICALL")));
        $cyclemonths = \App\Helpers\Invoice::getBillingCycleMonths($cycle);
    
        if ($accountid) {
            $values = $options = [];
            $accountid = (int)$accountid;
            $configOptionsResult = DB::table("{$this->prefix}productconfigoptionssub")
                ->join("{$this->prefix}productconfigoptions", "{$this->prefix}productconfigoptionssub.configid", "=", "{$this->prefix}productconfigoptions.id")
                ->join("{$this->prefix}productconfiglinks", "{$this->prefix}productconfigoptions.gid", "=", "{$this->prefix}productconfiglinks.gid")
                ->join("{$this->prefix}hosting", "{$this->prefix}productconfiglinks.pid", "=", "{$this->prefix}hosting.packageid")
                ->where("{$this->prefix}hosting.id", $accountid)
                ->whereIn("{$this->prefix}productconfigoptions.optiontype", [3, 4])
                ->groupBy("{$this->prefix}productconfigoptionssub.configid")
                ->orderBy("{$this->prefix}productconfigoptionssub.sortorder", "ASC")
                ->orderBy("id", "ASC")
                ->select("{$this->prefix}productconfigoptionssub.id", "{$this->prefix}productconfigoptionssub.configid")
                ->get();
    
            foreach ($configOptionsResult as $configOptionsData) {
                $options[$configOptionsData->id] = $configOptionsData->configid;
            }
    
            if (count($options)) {
                foreach ($options as $subID => $configOptionID) {
                    $isOptionSaved = (bool)\App\Models\Hostingconfigoption::where('configid', $configOptionID)
                        ->where('relid', $accountid)
                        ->select('configid')
                        ->first();
    
                    if (!$isOptionSaved) {
                        $hostingconfigoptions = new \App\Models\Hostingconfigoption();
                        $hostingconfigoptions->relid = $accountid;
                        $hostingconfigoptions->configid = $configOptionID;
                        $hostingconfigoptions->optionid = $subID;
                        $hostingconfigoptions->qty = 0;
                        $hostingconfigoptions->save();
                    }
                }
            }
    
            $result = \App\Models\Hostingconfigoption::where('relid', $accountid)->get();
            foreach ($result as $data) {
                $configid = $data->configid;
                $result2 = \App\Models\Productconfigoption::where('id', $configid)->first();
                $optiontype = $result2->optiontype;
                $configoptionvalue = ($optiontype == 3 || $optiontype == 4) ? $data->qty : $data->optionid;
                $values[$configid] = $configoptionvalue;
            }
        }
    
        $result2 = DB::table("{$this->prefix}productconfigoptions")
            ->join("{$this->prefix}productconfiglinks", "{$this->prefix}productconfigoptions.gid", "=", "{$this->prefix}productconfiglinks.gid")
            ->where('pid', $pid);
    
        if (!$showhidden) {
            $result2->where('hidden', 0);
        }
    
        $result2->select("{$this->prefix}productconfigoptions.*")
            ->orderBy("{$this->prefix}productconfigoptions.order", "ASC")
            ->orderBy("{$this->prefix}productconfigoptions.id", "ASC");
    
        $result2 = $result2->get();
    
        foreach ($result2 as $data2) {
            $optionid = $data2->id;
            $optionname = $data2->optionname;
            $optiontype = $data2->optiontype;
            $optionhidden = $data2->hidden;
            $qtyminimum = $data2->qtyminimum;
            $qtymaximum = $data2->qtymaximum;
    
            if (strpos($optionname, "|")) {
                $optionname = explode("|", $optionname);
                $optionname = trim($optionname[1]);
            }
    
            $options = [];
            $selname = $selectedoption = $selsetup = $selrecurring = "";
            $selectedqty = 0;
            $foundPreselectedValue = false;
            $selvalue = isset($values[$optionid]) ? $values[$optionid] : "";
    
            if ($optiontype == "3") {
                $data3 = \App\Models\Productconfigoptionssub::where('configid', $optionid)->first();
                $opid = $data3->id;
                $ophidden = $data3->hidden;
                $opname = $data3->optionname;
    
                if (strpos($opname, "|")) {
                    $opname = explode("|", $opname);
                    $opname = trim($opname[1]);
                }
    
                $opnameonly = $opname;
                $data = \App\Models\Pricing::where('type', 'configoptions')
                    ->where('currency', $currency["id"])
                    ->where('relid', $opid)
                    ->first();
    
                $subcycle = substr($cycle, 0, 1) . "setupfee";
                $setup = isset($data->$cycle) ? $data3->$subcycle : 0;
                $price = $fullprice = isset($data->$cycle) ? $data->$cycle : 0;
    
                if ($orderform) {
                    $price = $price / $cyclemonths;
                }
    
                if ($price > 0) {
                    $opname .= " " . \App\Helpers\Format::Currency($price);
                }
    
                $setupvalue = $setup > 0 ? " + " . \App\Helpers\Format::Currency($setup) . " Setup Fee" : "";
                $options[] = ["id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price];
    
                if (!$selvalue) {
                    $selvalue = 0;
                }
    
                $selectedqty = $selvalue;
                $selvalue = $opid;
                $selname = 'No';
    
                if ($selectedqty) {
                    $selname = 'Yes';
                    $selectedoption = $opname;
                    $selsetup = $setup;
                    $selrecurring = $fullprice;
                }
            } elseif ($optiontype == "4") {
                $data3 = \App\Models\Productconfigoptionssub::where('configid', $optionid)->first();
                $opid = $data3->id;
                $ophidden = $data3->hidden;
                $opname = $data3->optionname;
    
                if (strpos($opname, "|")) {
                    $opname = explode("|", $opname);
                    $opname = trim($opname[1]);
                }
    
                $opnameonly = $opname;
                $data = \App\Models\Pricing::where('type', 'configoptions')
                    ->where('currency', $currency["id"])
                    ->where('relid', $opid)
                    ->first();
    
                $subcycle = substr($cycle, 0, 1) . "setupfee";
                $setup = $data3->$subcycle;
                $price = $fullprice = $data->$cycle;
    
                if ($orderform) {
                    $price = $price / $cyclemonths;
                }
    
                if ($price > 0) {
                    $opname .= " " . \App\Helpers\Format::Currency($price);
                }
    
                $setupvalue = $setup > 0 ? " + " . \App\Helpers\Format::Currency($setup) . " Setup Fee" : "";
                $options[] = ["id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price];
    
                if (!is_numeric($selvalue) || $selvalue < 0) {
                    $selvalue = $qtyminimum;
                }
    
                if ($qtyminimum > 0 && $selvalue < $qtyminimum) {
                    $selvalue = $qtyminimum;
                }
    
                if ($qtymaximum > 0 && $qtymaximum < $selvalue) {
                    $selvalue = $qtymaximum;
                }
    
                $selectedqty = $selvalue;
                $selvalue = $opid;
                $selname = $selectedqty;
                $selectedoption = $opname;
                $selsetup = $setup * $selectedqty;
                $selrecurring = $fullprice * $selectedqty;

                // $rawName = $required = $opname = $data2->optionname;
                // if (strpos($opname, "|")) {
                //     $opnameArr = explode("|", $opname);
                //     $opname = trim($opnameArr[1]);
                //     $required = trim($opnameArr[0]);
                //     if (defined("APICALL")) {
                //         $setupvalue = "";
                //     }
                // }
                // $opnameonly = $opname; 
            } else {
                $result3 = DB::table("{$this->prefix}productconfigoptionssub")
                    ->join("{$this->prefix}pricing", "{$this->prefix}productconfigoptionssub.id", "=", "{$this->prefix}pricing.relid")
                    ->select("{$this->prefix}pricing.*", "{$this->prefix}productconfigoptionssub.*")
                    ->where("tblproductconfigoptionssub.configid", $optionid)
                    ->where("tblpricing.type", "configoptions")
                    ->where("tblpricing.currency", $currency["id"])
                    ->orderBy("tblproductconfigoptionssub.sortorder", "ASC")
                    ->orderBy("tblproductconfigoptionssub.id", "ASC")
                    ->get();
    
                foreach ($result3 as $data3) {
                    $opid = $data3->id;
                    $ophidden = $data3->hidden;
                    $subcycle = substr($cycle, 0, 1) . "setupfee";
                    $setup = $data3->$subcycle;
                    $price = $fullprice = $data3->$cycle;
    
                    if ($orderform) {
                        $price = $price / $cyclemonths;
                    }
    
                    $setupvalue = $setup > 0 ? " + " . \App\Helpers\Format::Currency($setup) . " Setup Fee" : "";
                    $rawName = $required = $opname = $data3->optionname;
    
                    if (strpos($opname, "|")) {
                        $opnameArr = explode("|", $opname);
                        $opname = trim($opnameArr[1]);
                        $required = trim($opnameArr[0]);
    
                        if (defined("APICALL")) {
                            $setupvalue = "";
                        }
                    }
    
                    $opnameonly = $opname;
    
                    if ($price > 0 && !defined("APICALL")) {
                        $opname .= " " . \App\Helpers\Format::Currency($price);
                    }
    
                    if ($showhidden || !$ophidden || $opid == $selvalue) {
                        $options[] = [
                            "id" => $opid,
                            "name" => $opname . $setupvalue,
                            "rawName" => $rawName,
                            "required" => $required,
                            "nameonly" => $opnameonly,
                            "nameandprice" => $opname,
                            "setup" => $setup,
                            "fullprice" => $fullprice,
                            "recurring" => $price,
                            "hidden" => $ophidden
                        ];
                    }
    
                    if ($opid == $selvalue || (!$selvalue && !$ophidden)) {
                        $selname = $opnameonly;
                        $selectedoption = $opname;
                        $selsetup = $setup;
                        $selrecurring = $fullprice;
                        $selvalue = $opid;
                        $foundPreselectedValue = true;
                    }
                }
    
                if (!$foundPreselectedValue && count($options) > 0) {
                    $selname = $options[0]["nameonly"];
                    $selectedoption = $options[0]["nameandprice"];
                    $selsetup = $options[0]["setup"];
                    $selrecurring = $options[0]["fullprice"];
                    $selvalue = $options[0]["id"];
                }
            }
    
            $configoptions[] = [
                "id" => $optionid,
                "hidden" => $optionhidden,
                "optionname" => $optionname,
                "optiontype" => $optiontype,
                "selectedvalue" => $selvalue,
                "selectedqty" => $selectedqty,
                "selectedname" => $selname,
                "selectedoption" => $selectedoption,
                "selectedsetup" => $selsetup,
                "selectedrecurring" => $selrecurring,
                "qtyminimum" => $qtyminimum,
                "qtymaximum" => $qtymaximum,
                "options" => $options
            ];
        }
    
        return $configoptions;
    }

    public function GetEmails(array $params)
    {
        extract($params);
    
        $getClients = \App\Models\Client::find($clientid);
        if (is_null($getClients)) {
            return [
                "status" => "error",
                "message" => "Client ID Not Found"
            ];
        }
    
        $limitstart = $limitstart ?? 0;
        $limitnum = $limitnum ?? 25;
    
        $getEmail = \App\Models\Email::orderBy('id', 'DESC');
    
        if (!empty($date)) {
            $getEmail->whereDate('date', $date);
        }
    
        if (!empty($subject)) {
            $getEmail->where('subject', 'LIKE', '%' . $subject . '%');
        }
    
        $totalresults = $getEmail->count();
        $getEmail->skip($limitstart)->take($limitnum);
        $data = $getEmail->get();
    
        return [
            "result" => "success",
            "totalresults" => $totalresults,
            "startnumber" => $limitstart,
            "numreturned" => count($data),
            "emails" => ['email' => $data]
        ];
    }
    
    public function UpdateContact(array $params)
    {
        extract($params);
    
        $contact = \App\Models\Contact::find($contactid);
        if (!$contact) {
            return [
                "result" => "error",
                "message" => "Contact ID Not Found"
            ];
        }
    
        $oldContact = $contact->toArray();
    
        $contact->firstname = $firstname;
        $contact->lastname = $lastname;
        $contact->companyname = $companyname;
        $contact->email = $email;
        $contact->address1 = $address1;
        $contact->address2 = $address2;
        $contact->city = $city;
        $contact->state = $state;
        $contact->postcode = $postcode;
        $contact->country = $country;
        $contact->phonenumber = $phonenumber;
        $contact->tax_id = $tax_id;
        $contact->generalemails = $email_preferences['general'] ?? 0;
        $contact->productemails = $email_preferences['product'] ?? 0;
        $contact->domainemails = $email_preferences['domain'] ?? 0;
        $contact->invoiceemails = $email_preferences['invoice'] ?? 0;
        $contact->supportemails = $email_preferences['support'] ?? 0;
        $contact->permissions = implode(",", $permissions);
    
        if (!empty($password2)) {
            $hash = new \App\Helpers\Password();
            $password = $hash->hash(\App\Helpers\Sanitize::decode($password2));
            $contact->password = $password;
    
            Hooks::run_hook("ContactChangePassword", [
                "userid" => $userid,
                "contactid" => $contactid,
                "password" => $password
            ]);
        }
    
        $subaccount = $subaccount ?? 0;
        $contact->subaccount = $subaccount;
    
        if (!$subaccount) {
            \App\Models\AuthnAccountLink::where("contact_id", $contactid)
                ->where("client_id", $userid)
                ->delete();
        }
    
        $contact->save();
        $newContact = $contact->toArray();
    
        LogActivity::save("Contact Modified - User ID: $userid - Contact ID: $contactid", $userid);
        Hooks::run_hook("ContactEdit", array_merge([
            "userid" => $userid,
            "contactid" => $contactid,
            "olddata" => $oldContact
        ], $newContact));
    
        return [
            "result" => "success",
            "contactid" => $contactid,
        ];
    }

    public function GetCancelledPackages(array $params)
    {
        extract($params);
    
        $limitstart = (int)($limitstart ?? 0);
        $limitnum = (int)($limitnum ?? 25);
    
        $totalresults = \App\Models\Cancelrequest::count();
        $result2 = \App\Models\Cancelrequest::skip($limitstart)->take($limitnum)->get();
    
        return [
            "result" => "success",
            "totalresults" => $totalresults,
            "startnumber" => $limitstart,
            "numreturned" => count($result2),
            "packages" => ['package' => $result2]
        ];
    }
    
    public function CreateCancellationRequest($userid, $serviceid, $reason, $type)
    {
        $existing = Cancelrequest::where("relid", $serviceid)->count();
    
        if ($existing == 0) {
            if (!in_array($type, ["Immediate", "End of Billing Period"])) {
                $type = "End of Billing Period";
            }
    
            $cancelrequest = new Cancelrequest();
            $cancelrequest->date = \Carbon\Carbon::now();
            $cancelrequest->relid = $serviceid;
            $cancelrequest->reason = $reason;
            $cancelrequest->type = $type;
            $cancelrequest->save();
    
            $logMessage = $type == "End of Billing Period" 
                ? "Automatic Cancellation Requested for End of Current Cycle - Service ID: $serviceid" 
                : "Automatic Cancellation Requested Immediately - Service ID: $serviceid";
            LogActivity::Save($logMessage, $userid);
    
            $data = Hosting::select("{$this->prefix}hosting.id", "domain", "freedomain", "subscriptionid", "packageid", "type")
                ->where("{$this->prefix}hosting.id", $serviceid)
                ->join("{$this->prefix}products", "{$this->prefix}products.id", "=", "{$this->prefix}hosting.packageid")
                ->first();
    
            if ($data) {
                $domain = $data->domain;
                $freedomain = $data->freedomain;
                $subscriptionId = $data->subscriptionid;
    
                if ($freedomain && $domain) {
                    $domainData = Domain::select("id", "status", "recurringamount", "registrationperiod", "dnsmanagement", "emailforwarding", "idprotection")
                        ->where("userid", $userid)
                        ->where("domain", $domain)
                        ->orderBy("status", "ASC")
                        ->first();
    
                    if ($domainData) {
                        $domainid = $domainData->id;
                        $recurringamount = $domainData->recurringamount;
                        $regperiod = $domainData->registrationperiod;
                        $dnsmanagement = $domainData->dnsmanagement;
                        $emailforwarding = $domainData->emailforwarding;
                        $idprotection = $domainData->idprotection;
    
                        if ($recurringamount <= 0) {
                            $currency = (new AdminFunctions())->getCurrency($userid);
                            $pricingData = Pricing::select("msetupfee", "qsetupfee", "ssetupfee")
                                ->where("type", "domainaddons")
                                ->where("currency", $currency["id"])
                                ->where("relid", 0)
                                ->first();
    
                            if ($pricingData) {
                                $domaindnsmanagementprice = $pricingData->msetupfee * $regperiod;
                                $domainemailforwardingprice = $pricingData->qsetupfee * $regperiod;
                                $domainidprotectionprice = $pricingData->ssetupfee * $regperiod;
                                $domainparts = explode(".", $domain, 2);
    
                                $temppricelist = \App\Helpers\Domain::GetTLDPriceList("." . $domainparts[1], "", true, $userid);
                                $recurringamount = $temppricelist[$regperiod]["renew"] ?? null;
    
                                if ($dnsmanagement && $recurringamount) {
                                    $recurringamount += $domaindnsmanagementprice;
                                }
                                if ($emailforwarding && $recurringamount) {
                                    $recurringamount += $domainemailforwardingprice;
                                }
                                if ($idprotection && $recurringamount) {
                                    $recurringamount += $domainidprotectionprice;
                                }
    
                                if ($recurringamount) {
                                    $domainUpdate = Domain::find($domainid);
                                    $domainUpdate->recurringamount = $recurringamount;
                                    $domainUpdate->save();
                                }
                            }
                        }
                    }
                }
            }
    
            Hooks::run_hook("CancellationRequest", ["userid" => $userid, "relid" => $serviceid, "reason" => $reason, "type" => $type]);
    
            if (Cfg::get("CancelInvoiceOnCancellation")) {
                $this->CancelUnpaidInvoicebyProductID($serviceid, $userid);
            }
    
            if (Cfg::get("AutoCancelSubscriptions") && $subscriptionId) {
                try {
                    Gateway::CancelSubscriptionForService($serviceid, $userid);
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
    
            return "success";
        }
    
        return "Existing Cancellation Request Exists";
    }

    public function CancelUnpaidInvoicebyProductID($serviceid, $userid = "")
    {
        $userid = (int)$userid;
        $serviceid = (int)$serviceid;
    
        if (!$userid) {
            $hosting = Hosting::select('id', 'userid')->find($serviceid);
            $userid = (int)$hosting->userid;
        }
    
        $addons = Hostingaddon::select("id", "hostingid")
            ->where("hostingid", $serviceid)
            ->get();
    
        $addonIds = $addons->pluck('id')->toArray();
    
        $result = Invoiceitem::select("type", "relid", "status", "{$this->prefix}invoices.userid", "{$this->prefix}invoiceitems.id", "{$this->prefix}invoiceitems.invoiceid")
            ->where("type", "Hosting")
            ->where("relid", $serviceid)
            ->where("status", "Unpaid")
            ->where("{$this->prefix}invoices.userid", $userid)
            ->join("{$this->prefix}invoices", "{$this->prefix}invoices.id", "=", "{$this->prefix}invoiceitems.invoiceid")
            ->get()
            ->toArray();
    
        foreach ($result as $data) {
            $itemid = $data["id"];
            $invoiceid = $data["invoiceid"];
    
            $itemcount = Invoiceitem::where("invoiceid", $invoiceid)->count();
    
            if ($itemcount > 1 && $itemcount <= 4) {
                $itemcount -= Invoiceitem::where("invoiceid", $invoiceid)
                    ->where("type", "PromoHosting")
                    ->where("relid", $serviceid)
                    ->count();
    
                $itemcount -= Invoiceitem::where("invoiceid", $invoiceid)
                    ->where("type", "GroupDiscount")
                    ->count();
    
                $itemcount -= Invoiceitem::where("invoiceid", $invoiceid)
                    ->where("type", "LateFee")
                    ->count();
    
                if ($addonIds) {
                    $itemcount -= Invoiceitem::where("invoiceid", $invoiceid)
                        ->where("type", "Addon")
                        ->whereIn("relid", $addonIds)
                        ->count();
                }
            }
    
            if ($itemcount == 1) {
                $invoice = Invoice::find($invoiceid);
                $invoice->status = "Cancelled";
                $invoice->save();
    
                LogActivity::Save("Cancelled Outstanding Product Renewal Invoice - Invoice ID: $invoiceid - Service ID: $serviceid", $userid);
    
                Hooks::run_hook("InvoiceCancelled", ["invoiceid" => $invoiceid]);
            } else {
                Invoiceitem::find($itemid)->delete();
                Invoiceitem::where("invoiceid", $invoiceid)
                    ->where("type", "PromoHosting")
                    ->where("relid", $serviceid)
                    ->delete();
                Invoiceitem::where("invoiceid", $invoiceid)
                    ->where("type", "GroupDiscount")
                    ->delete();
    
                \App\Helpers\Invoice::UpdateInvoiceTotal($invoiceid);
    
                LogActivity::Save("Removed Outstanding Product Renewal Invoice Line Item - Invoice ID: $invoiceid - Service ID: $serviceid", $userid);
            }
        }
    
        if ($addonIds) {
            $invoiceItems = Invoiceitem::select("type", "status", "{$this->prefix}invoices.userid", "{$this->prefix}invoiceitems.id", "{$this->prefix}invoiceitems.relid", "{$this->prefix}invoiceitems.invoiceid")
                ->where("type", "Addon")
                ->where("status", "Unpaid")
                ->where("{$this->prefix}invoices.userid", $userid)
                ->whereIn("relid", $addonIds)
                ->join("{$this->prefix}invoices", "{$this->prefix}invoices.id", "=", "{$this->prefix}invoiceitems.invoiceid")
                ->get();
    
            foreach ($invoiceItems as $invoiceItem) {
                $itemCount = Invoiceitem::where("invoiceid", $invoiceItem->invoiceid)->count();
    
                if ($itemCount > 1 && $itemCount <= 3) {
                    $itemCount -= Invoiceitem::where("invoiceid", $invoiceItem->invoiceid)
                        ->where("type", "GroupDiscount")
                        ->count();
                    $itemCount -= Invoiceitem::where("invoiceid", $invoiceItem->invoiceid)
                        ->where("type", "LateFee")
                        ->count();
                }
    
                if ($itemCount == 1) {
                    $inv = Invoice::find($invoiceItem->invoiceid);
                    $inv->status = "Cancelled";
                    $inv->save();
    
                    LogActivity::Save("Cancelled Outstanding Product Addon Invoice - Invoice ID: {$invoiceItem->invoiceid} - Service Addon ID: {$invoiceItem->relid}", $userid);
    
                    Hooks::run_hook("InvoiceCancelled", ["invoiceid" => $invoiceItem->invoiceid]);
                } else {
                    Invoiceitem::find($invoiceItem->id)->delete();
                    Invoiceitem::where("invoiceid", $invoiceItem->invoiceid)
                        ->where("type", "GroupDiscount")
                        ->delete();
    
                    \App\Helpers\Invoice::UpdateInvoiceTotal($invoiceItem->invoiceid);
    
                    LogActivity::Save("Removed Outstanding Product Renewal Invoice Line Item - Invoice ID: {$invoiceItem->invoiceid} - Service ID: {$invoiceItem->relid}", $userid);
                }
            }
        }
    
        return true;
    }
    
    public static function getClientDefaultBankDetails($userId, $mode = "allowLegacy", &$foundPayMethodRef = false)
    {
        $bankDetails = [
            "bankname" => null,
            "banktype" => null,
            "bankacct" => null,
            "bankcode" => null
        ];
    
        $client = \App\User\Client::find($userId);
        if (!$client) {
            return $bankDetails;
        }
    
        if (!in_array($mode, ["forceLegacy", "forcePayMethod", "allowLegacy"])) {
            $mode = "allowLegacy";
        }
    
        if ($mode == "forceLegacy") {
            return self::getClientsBankDetails($userId);
        }
    
        if ($mode == "allowLegacy" && $client->needsBankDetailsMigrated()) {
            return self::getClientsBankDetails($userId);
        }
    
        $payMethods = $client->payMethods->bankAccounts();
        $gateway = new \App\Module\Gateway();
        $payMethod = null;
    
        foreach ($payMethods as $tryPayMethod) {
            if (!$tryPayMethod->isUsingInactiveGateway()) {
                $payMethod = $tryPayMethod;
                break;
            }
        }
    
        if ($payMethod) {
            $payment = $payMethod->payment;
            if ($payment) {
                $bankDetails["paymethodid"] = $payMethod->id;
                $bankDetails["bankname"] = $payment->getBankName();
                $bankDetails["banktype"] = $payment->getAccountType();
                $bankDetails["bankcode"] = $payment->getRoutingNumber();
                $bankDetails["bankacct"] = $payment->getAccountNumber();
            }
            if ($foundPayMethodRef !== false) {
                $foundPayMethodRef = $payMethod;
            }
        }
    
        return $bankDetails;
    }
    
    public static function getClientsBankDetails($userId)
    {
        static $users = null;
        if (!is_array($users)) {
            $users = [];
        }
    
        if (!array_key_exists($userId, $users)) {
            $ccHash = Config::get("portal")["hash"]["cc_encryption_hash"];
            $aesHash = md5($ccHash . $userId);
            $clientInfo = DB::table("tblclients")
                ->where("id", $userId)
                ->first([
                    "bankname",
                    "banktype",
                    DB::raw("AES_DECRYPT(bankcode, '" . $aesHash . "') as bankcode"),
                    DB::raw("AES_DECRYPT(bankacct, '" . $aesHash . "') as bankacct")
                ]);
            $users[$userId] = (array)$clientInfo;
        }
    
        return $users[$userId];
    }
    
    public function clientChangeDefaultGateway($userid, $paymentmethod)
    {
        $defaultgateway = ClientModel::where("id", $userid)->value("defaultgateway");
    
        if (auth()->user()->id && !$paymentmethod && $defaultgateway) {
            ClientModel::where("id", $userid)->update(["defaultgateway" => ""]);
        }
    
        if ($paymentmethod && $paymentmethod != $defaultgateway) {
            if ($paymentmethod == "none") {
                ClientModel::where("id", $userid)->update(["defaultgateway" => ""]);
            }
    
            $paymentmethod = \App\Models\Paymentgateway::where("gateway", $paymentmethod)->value("gateway");
            if (!$paymentmethod) {
                return false;
            }
    
            ClientModel::where("id", $userid)->update(["defaultgateway" => $paymentmethod]);
            Hosting::where("userid", $userid)->update(["paymentmethod" => $paymentmethod]);
            Hostingaddon::whereRaw("hostingid IN (SELECT id FROM {$this->prefix}hosting WHERE userid={$userid})")
                ->update(["paymentmethod" => $paymentmethod]);
            Domain::where("userid", $userid)->update(["paymentmethod" => $paymentmethod]);
            Invoice::where("userid", $userid)->where("status", "Unpaid")->update(["paymentmethod" => $paymentmethod]);
        }
    }
    
    public function getClientStatus($key = null)
    {
        $status = ["Active", "Inactive", "Closed"];
    
        if (is_null($key)) {
            return $status;
        }
    
        return in_array($key, $status) ? $status[array_search($key, $status)] : false;
    }
    
    public function AddCredit($actionType = "add", $clientid, $description, $amount, $date, $adminid)
    {
        $amount = (double)$amount ?? 0;
        $date = $date ?? \Carbon\Carbon::now()->format('Y-m-d');
        $type = $actionType ?? 'add';
    
        $client = \App\Models\Client::find($clientid);
    
        if ($type === "remove" && $client->credit < $amount) {
            return [
                'result' => 'error',
                'message' => 'Insufficient Credit Balance',
            ];
        }
    
        if (!$adminid) {
            $auth = Auth::guard('admin')->user();
            $adminid = $auth ? $auth->id : 0;
        }
    
        $relativeChange = $type === "remove" ? -$amount : $amount;
    
        $credit = new \App\Models\Credit();
        $credit->clientid = $clientid;
        $credit->admin_id = $adminid;
        $credit->date = (new \App\Helpers\SystemHelper())->toMySQLDate($date);
        $credit->description = $description;
        $credit->amount = $relativeChange;
        $credit->save();
    
        $client->credit += $relativeChange;
        $client->save();
        $client = $client->fresh();
    
        $message = ($type === "remove" ? "Removed" : "Added") . " Credit - User ID: " . $clientid . " - Amount: " . \App\Helpers\Format::Price($amount);
    
        LogActivity::Save($message, $clientid);
    
        return [
            'result' => 'success',
            'message' => $message,
        ];
    }

    function checkDetailsareValid($uid = "", $signup = false, $checkemail = true, $captcha = true, $checkcustomfields = true)
    {
        $validate = new \App\Helpers\Validate();
        $validate->setOptionalFields(\App\Helpers\Cfg::get("ClientsProfileOptionalFields"));
    
        if (!$signup) {
            $ClientsProfileUneditableFields = \App\Helpers\Cfg::get("ClientsProfileUneditableFields");
            if (\App\Helpers\Application::isApiRequest()) {
                $ClientsProfileUneditableFields = preg_replace("/email,?/i", "", $ClientsProfileUneditableFields);
            }
            $validate->setOptionalFields($ClientsProfileUneditableFields);
        }
    
        $validate->validate("required", "firstname", "client.clientareaerrorfirstname");
        $validate->validate("required", "lastname", "client.clientareaerrorlastname");
    
        if (($signup || $checkemail) && 
            $validate->validate("required", "email", "client.clientareaerroremail") && 
            $validate->validate("email", "email", "client.clientareaerroremailinvalid") && 
            $validate->validate("banneddomain", "email", "client.clientareaerrorbannedemail")) {
            $validate->validate("uniqueemail", "email", "client.ordererroruserexists", [$uid, ""]);
        }
    
        $validate->validate("required", "address1", "client.clientareaerroraddress1");
        $validate->validate("required", "city", "client.clientareaerrorcity");
        $validate->validate("required", "state", "client.clientareaerrorstate");
        $validate->validate("required", "postcode", "client.clientareaerrorpostcode");
        $validate->validate("postcode", "postcode", "client.clientareaerrorpostcode2");
    
        if ($validate->validate("required", "phonenumber", "client.clientareaerrorphonenumber")) {
            $validate->validate("phone", "phonenumber", "client.clientareaerrorphonenumber2");
        }
    
        $validate->validate("country", "country", "client.clientareaerrorcountry");
    
        if (\App\Helpers\Vat::isTaxIdEnabled() && array_key_exists(Request::input("country"), \App\Helpers\Vat::EU_COUNTRIES)) {
            $validate->validate("tax_code?", \App\Helpers\Vat::getFieldName(), [
                "key" => "tax.errorInvalid", 
                "replacements" => ["taxLabel" => Lang::get(\App\Helpers\Vat::getLabel())]
            ]);
        }
    
        if ($signup && 
            $validate->validate("required", "password", "client.ordererrorpassword") && 
            $validate->validate("pwstrength", "password", "client.pwstrengthfail") && 
            $validate->validate("required", "password2", "client.clientareaerrorpasswordconfirm")) {
            $validate->validate("match_value", "password", "client.clientareaerrorpasswordnotmatch", "password2");
        }
    
        if ($checkcustomfields) {
            $validate->validateCustomFields("client", "", $signup);
        }
    
        if ($signup) {
            $securityquestions = $this->getSecurityQuestions();
            if ($securityquestions) {
                $validate->validate("required", "securityqans", "client.securityanswerrequired");
            }
            if ($captcha) {
                // $captchaCheck = new WHMCS\Utility\Captcha();
                // $captchaCheck->validateAppropriateCaptcha(WHMCS\Utility\Captcha::FORM_REGISTRATION, $validate);
            }
            if (\App\Helpers\Cfg::get("EnableTOSAccept")) {
                $validate->validate("required", "accepttos", "client.ordererroraccepttos");
            }
        }
    
        \App\Helpers\Hooks::run_validate_hook($validate, "ClientDetailsValidation", $_POST);
        return $validate->getHTMLErrorOutput();
    }
    
    function checkContactDetails($cid = "", $reqpw = false, $prefix = "")
    {
        return $this->validatecontactdetails($cid, $reqpw, $prefix)->getHTMLErrorOutput();
    }
    
    function validateContactDetails($cid = "", $reqpw = false, $prefix = "")
    {
        $subaccount = Request::input("subaccount");
        $validate = new \App\Helpers\Validate();
        $validate->setOptionalFields(\App\Helpers\Cfg::get("ClientsProfileOptionalFields"));
    
        $validate->validate("required", $prefix . "firstname", "client.clientareaerrorfirstname");
        $validate->validate("required", $prefix . "lastname", "client.clientareaerrorlastname");
    
        if ($validate->validate("required", $prefix . "email", "client.clientareaerroremail") &&
            $validate->validate("email", $prefix . "email", "client.clientareaerroremailinvalid") &&
            $validate->validate("banneddomain", $prefix . "email", "client.clientareaerrorbannedemail") &&
            $subaccount) {
            $validate->validate("uniqueemail", $prefix . "email", "client.ordererroruserexists", ["", $cid]);
        }
    
        $validate->validate("required", $prefix . "address1", "client.clientareaerroraddress1");
        $validate->validate("required", $prefix . "city", "client.clientareaerrorcity");
        $validate->validate("required", $prefix . "state", "client.clientareaerrorstate");
        $validate->validate("required", $prefix . "postcode", "client.clientareaerrorpostcode");
        $validate->validate("postcode", $prefix . "postcode", "client.clientareaerrorpostcode2");
    
        if ($validate->validate("required", $prefix . "phonenumber", "client.clientareaerrorphonenumber")) {
            $validate->validate("phone", $prefix . "phonenumber", "client.clientareaerrorphonenumber2");
        }
    
        $validate->validate("country", $prefix . "country", "client.clientareaerrorcountry");
    
        if ($subaccount && $reqpw &&
            $validate->validate("required", "password", "client.ordererrorpassword") &&
            $validate->validate("pwstrength", "password", "client.pwstrengthfail") &&
            $validate->validate("required", "password2", "client.clientareaerrorpasswordconfirm")) {
            $validate->validate("match_value", "password", "client.clientareaerrorpasswordnotmatch", "password2");
        }
    
        if (\App\Helpers\Vat::isTaxIdEnabled() &&
            array_key_exists(Request::input($prefix . "country"), \App\Helpers\Vat::EU_COUNTRIES)) {
            $validate->validate("tax_code?", $prefix . "tax_id", [
                "key" => "tax.errorInvalid",
                "replacements" => ["taxLabel" => Lang::get(\App\Helpers\Vat::getLabel())]
            ]);
        }
    
        \App\Helpers\Hooks::run_validate_hook($validate, "ContactDetailsValidation", $_POST);
    
        return $validate;
    }
}
