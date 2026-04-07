<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use App\Helpers\LogActivity;
use App\Helpers\Cfg;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Auth;

class ClientClass
{
    // Client class
    protected $userid = "";
    protected $clientModel = NULL;
    public function __construct($user)
    {
        if ($user instanceof \App\Models\Client) {
            $this->clientModel = $user;
            $this->setID($user->id);
        } else {
            $this->setID($user);
            $this->clientModel = \App\Models\Client::find($this->getID());
        }
        return $this;
    }

    // Client class
    public function getClientModel()
    {
        return $this->clientModel;
    }
    public function setID($userid)
    {
        $this->userid = (int) $userid;
    }
    public function getID()
    {
        return $this->userid;
    }
    public function getUneditableClientProfileFields()
    {
        global $whmcs;
        return explode(",", \App\Helpers\Cfg::get("ClientsProfileUneditableFields"));
    }
    public function isEditableField($field)
    {
        $uneditablefields = defined("CLIENTAREA") ? $this->getUneditableClientProfileFields() : array();
        return !in_array($field, $uneditablefields) ? true : false;
    }
    public static function formatPhoneNumber($details)
    {
        $phone = trim($details["phonenumber"]);
        $phonePrefix = "";
        if (substr($phone, 0, 1) == "+") {
            $phoneParts = explode(".", ltrim($phone, "+"), 2);
            if (count($phoneParts) == 2) {
                list($phonePrefix, $phoneNumber) = $phoneParts;
            } else {
                $phoneNumber = $phoneParts[0];
            }
        } else {
            $phoneNumber = $phone;
        }
        $phonePrefix = preg_replace("/[^0-9]/", "", $phonePrefix);
        $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumber);
        $countries = new \App\Helpers\Country();
        if (!$phonePrefix) {
            $phonePrefix = $countries->getCallingCode($details["countrycode"]);
        }
        $trimmedPhoneNumber = $phoneNumber;
        if ($phonePrefix != $countries->getCallingCode("IT")) {
            $trimmedPhoneNumber = ltrim($trimmedPhoneNumber, "0");
        }
        $fullyFormattedPhoneNumber = $phonePrefix ? "+" . $phonePrefix . "." . $trimmedPhoneNumber : $phoneNumber;
        $details["phonenumber"] = $phoneNumber;
        $details["phonecc"] = $phonePrefix;
        $details["phonenumberformatted"] = $phoneNumber ? $fullyFormattedPhoneNumber : $phoneNumber;
        $details["telephoneNumber"] = Cfg::getValue("PhoneNumberDropdown") ? $details["phonenumberformatted"] : $phone;
        return $details;
    }
    public function getDetails($contactid = "")
    {
        if (is_null($this->clientModel)) {
            return false;
        }
        $countries = new \App\Helpers\Country();
        $details = array();
        $details["userid"] = $this->clientModel->id;
        $details["id"] = $details["userid"];
        $billingContact = false;
        if ($contactid == "billing") {
            $contactid = $this->clientModel->billingContactId;
            $billingContact = true;
        } else {
            $contactid = (int) $contactid;
        }
        $contact = null;
        if (0 < $contactid) {
            try {
                $contact = $this->clientModel->contacts()->whereId($contactid)->firstOrFail();
                $details["firstname"] = $contact->firstName;
                $details["lastname"] = $contact->lastName;
                $details["companyname"] = $contact->companyName;
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
                $details["tax_id"] = $contact->taxId;
                if (empty($details["tax_id"])) {
                    $details["tax_id"] = $this->clientModel->taxId;
                }
                $details["password"] = $contact->passwordHash;
                $details["domainemails"] = $contact->receivesDomainEmails;
                $details["generalemails"] = $contact->receivesGeneralEmails;
                $details["invoiceemails"] = $contact->receivesInvoiceEmails;
                $details["productemails"] = $contact->receivesProductEmails;
                $details["supportemails"] = $contact->receivesSupportEmails;
                $details["affiliateemails"] = $contact->receivesAffiliateEmails;
                $details["model"] = $contact;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                if ($billingContact) {
                    $this->clientModel->billingcid = 0;
                    $this->clientModel->save();
                }
            }
        }
        if (is_null($contact)) {
            $details["uuid"] = $this->clientModel->uuid;
            $details["firstname"] = $this->clientModel->firstname;
            $details["lastname"] = $this->clientModel->lastname;
            $details["companyname"] = $this->clientModel->companyName;
            $details["email"] = $this->clientModel->email;
            $details["address1"] = $this->clientModel->address1;
            $details["address2"] = $this->clientModel->address2;
            $details["city"] = $this->clientModel->city;
            $details["fullstate"] = $this->clientModel->state;
            $details["state"] = $details["fullstate"];
            $details["postcode"] = $this->clientModel->postcode;
            $details["countrycode"] = $this->clientModel->country;
            $details["country"] = $details["countrycode"];
            $details["phonenumber"] = $this->clientModel->phoneNumber;
            $details["tax_id"] = $this->clientModel->taxId;
            $details["password"] = $this->clientModel->passwordHash;
            $details["model"] = $this->clientModel;
        }
        $details["fullname"] = $details["firstname"] . " " . $details["lastname"];
        if (!isset($details["uuid"])) {
            $uuid = (string) Str::uuid();
            $details["uuid"] = $uuid;
        }
        if ($details["country"] == "GB") {
            $postcode = $origpostcode = $details["postcode"];
            $postcode = strtoupper($postcode);
            $postcode = preg_replace("/[^A-Z0-9]/", "", $postcode);
            if (strlen($postcode) == 5) {
                $postcode = substr($postcode, 0, 2) . " " . substr($postcode, 2);
            } else {
                if (strlen($postcode) == 6) {
                    $postcode = substr($postcode, 0, 3) . " " . substr($postcode, 3);
                } else {
                    if (strlen($postcode) == 7) {
                        $postcode = substr($postcode, 0, 4) . " " . substr($postcode, 4);
                    } else {
                        $postcode = $origpostcode;
                    }
                }
            }
            $postcode = trim($postcode);
            $details["postcode"] = $postcode;
        }
        $details["statecode"] = (new \App\Helpers\ClientHelper())->convertStateToCode($details["state"], $details["country"]);
        $details["countryname"] = $countries->getName($details["countrycode"]);
        $details = self::formatPhoneNumber($details);
        $defaultPayMethod = (new \App\Helpers\ClientHelper())->getClientDefaultCardDetails($this->userid);
        $details["billingcid"] = $this->clientModel->billingContactId;
        $details["notes"] = $this->clientModel->notes;
        $details["twofaenabled"] = $this->clientModel->twoFactorAuthModule ? true : false;
        $details["currency"] = $this->clientModel->currencyId;
        $details["defaultgateway"] = $this->clientModel->defaultPaymentGateway;
        $details["cctype"] = $defaultPayMethod["cardtype"];
        $details["cclastfour"] = $defaultPayMethod["cardlastfour"];
        $details["gatewayid"] = $defaultPayMethod["gatewayid"];
        $details["securityqid"] = $this->clientModel->securityQuestionId;
        $details["securityqans"] = $this->clientModel->securityQuestionAnswer;
        $details["groupid"] = $this->clientModel->groupId;
        $details["status"] = $this->clientModel->status;
        $details["credit"] = $this->clientModel->credit;
        $details["taxexempt"] = $this->clientModel->taxExempt;
        $details["latefeeoveride"] = $this->clientModel->overrideLateFee;
        $details["overideduenotices"] = $this->clientModel->overrideOverdueNotices;
        $details["separateinvoices"] = $this->clientModel->separateInvoices;
        $details["disableautocc"] = $this->clientModel->disableAutomaticCreditCardProcessing;
        $details["emailoptout"] = $this->clientModel->emailOptOut;
        $details["marketing_emails_opt_in"] = $this->clientModel->marketingEmailsOptIn;
        $details["overrideautoclose"] = $this->clientModel->overrideAutoClose;
        $details["allowSingleSignOn"] = $this->clientModel->allowSso;
        $details["language"] = $this->clientModel->language;
        $details["isOptedInToMarketingEmails"] = $this->clientModel->isOptedInToMarketingEmails();
        $lastlogin = $this->clientModel->lastLoginDate ? $this->clientModel->lastLoginDate->format("Y-m-d H:i:s") : "1970-01-01 00:00:00";
        $details["lastlogin"] = $lastlogin == "1970-01-01 00:00:00" ? "No Login Logged" : "Date: " . (new \App\Helpers\Functions())->fromMySQLDate($lastlogin, "time") . "<br>IP Address: " . $this->clientModel->lastLoginIp . "<br>Host: " . $this->clientModel->lastLoginHostname;
        $customfields = \App\Helpers\Customfield::getCustomFields("client", "", $this->clientModel->id, true);
        foreach ($customfields as $i => $value) {
            $details["customfields" . ($i + 1)] = $value["value"];
            $details["customfields"][] = array("id" => $value["id"], "value" => $value["value"]);
        }
        return $details;
    }
    public function getCurrency()
    {
        return (new \App\Helpers\AdminFunctions())->getCurrency($this->getID());
    }
    public function getContactsWithAddresses()
    {
        $where = array();
        $where[] = ["userid", '=', $this->userid];
        $where[] = ["address1", '!=', ''];
        return $this->getContactsData($where);
    }
    public function getContacts()
    {
        $where = array();
        $where[] = ["userid", "=", $this->userid];
        return $this->getContactsData($where);
    }
    private function getContactsData($where)
    {
        $contactsarray = array();
        $result = \App\Models\Contact::selectRaw("id,firstname,lastname,email")->where($where)->orderBy("firstname", "ASC")->orderBy("lastname", "ASC")->get();
        foreach ($result->toArray() as $data) {
            $contactsarray[] = array("id" => $data["id"], "name" => $data["firstname"] . " " . $data["lastname"], "email" => $data["email"]);
        }
        return $contactsarray;
    }
}
