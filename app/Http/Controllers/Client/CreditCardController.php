<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hexadog\ThemesManager\Facades\ThemesManager;
use DB;

class CreditCardController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        global $_LANG;
        global $params;
        $auth = auth('web')->user();

        $userId = $auth->id;
        $invoiceid = (int) $request->input("invoiceid");
        if (!$invoiceid) {
            return redirect()->route('home');
        }
        $client = \App\User\Client::find($userId);
        if (!$client) {
            return redirect()->route('home');
        }
        $invoice = new \App\Helpers\InvoiceClass($invoiceid);
        if (!$invoice->isAllowed()) {
            return redirect()->route('home');
        }
        $invoiceid = $invoice->getData("invoiceid");
        $invoicenum = $invoice->getData("invoicenum");
        $status = $invoice->getData("status");
        $total = $invoice->getData("total");
        $invoiceModel = \App\Models\Invoice::find($invoiceid);
        if ($status != "Unpaid") {
            return redirect()->route('home');
        }
        $gateways = new \App\Helpers\Gateways();
        $action = $request->input("action");
        $ccinfo = $request->input("ccinfo");
        $cctype = $request->input("cctype");
        $ccDescription = $request->input("ccdescription") ?? "";
        $ccnumber = $request->input("ccnumber");
        $ccExpiryDate = $request->input("ccexpirydate");
        $ccexpirymonth = $ccexpiryyear = $ccstartmonth = $ccstartyear = "";
        if ($ccExpiryDate) {
            $ccExpiryDate = \App\Helpers\Carbon::createFromCcInput($ccExpiryDate);
            $ccexpirymonth = $ccExpiryDate->month;
            $ccexpiryyear = $ccExpiryDate->year;
        }
        $ccStartDate = $request->input("ccstartdate");
        if ($ccStartDate) {
            $ccStartDate = \App\Helpers\Carbon::createFromCcInput($ccStartDate);
            $ccstartmonth = $ccStartDate->month;
            $ccstartyear = $ccStartDate->year;
        }
        $ccissuenum = $request->input("ccissuenum");
        $nostore = $request->input("nostore");
        $cccvv = $request->input("cccvv");
        $cccvv2 = $request->input("cccvv2");
        $firstname = $request->input("firstname");
        $lastname = $request->input("lastname");
        $address1 = $request->input("address1");
        $address2 = $request->input("address2");
        $city = $request->input("city");
        $state = $request->input("state");
        $postcode = $request->input("postcode");
        $country = $request->input("country");
        $phonenumber = \App\Helpers\Application::formatPostedPhoneNumber();
        $userDetailsValidationError = false;
        $params = NULL;
        $errormessage = false;
        $fromorderform = false;
        if (session()->get("cartccdetail")) {
            $cartccdetail = unserialize(base64_decode((new \App\Helpers\Pwd)->decrypt(session()->get("cartccdetail"))));
            session()->forget('cartccdetail');
            list($cctype, $ccnumber, $ccexpirymonth, $ccexpiryyear, $ccstartmonth, $ccstartyear, $ccissuenum, $cccvv, $nostore, $ccinfo) = $cartccdetail;
            $action = "submit";
            if (\App\Helpers\Cc::ccFormatNumbers($ccnumber)) {
                $ccinfo = "new";
            }
            $fromorderform = true;
        }
        $gateway = new \App\Module\Gateway();
        $gateway->load($invoice->getData("paymentmodule"));
        if ($gateway->functionExists("credit_card_input")) {
            if (is_null($params)) {
                $params = \App\Helpers\Cc::getCCVariables($invoiceid);
            }
            // $clientArea->assign("credit_card_input", $gateway->call("credit_card_input", $params));
            $smartyvalues["credit_card_input"] = $gateway->call("credit_card_input", $params);
        }
        if ($action == "submit") {
            DB::beginTransaction();
            try {
                if (!$fromorderform) {
                    // check_token();
                }
                if ($nostore && (!\App\Helpers\Cfg::getValue("CCAllowCustomerDelete") || $gateway->functionExists("storeremote"))) {
                    $nostore = "";
                }
                $payMethod = NULL;
                $billingcid = $request->input("billingcontact");
                if (!$fromorderform) {
                    if ($billingcid == "new") {
                        $errormessage = (new \App\Helpers\Client)->checkDetailsareValid($userId, false, false, false, false);
                    }
                    if ($errormessage) {
                        $userDetailsValidationError = true;
                    }
                    if ($gateway->functionExists("cc_validation")) {
                        $params = array();
                        $params["cardtype"] = $cctype;
                        $params["cardnum"] = \App\Helpers\Cc::ccFormatNumbers($ccnumber);
                        $params["cardexp"] = \App\Helpers\Cc::ccFormatDate(\App\Helpers\Cc::ccFormatNumbers($ccexpirymonth . $ccexpiryyear));
                        $params["cardstart"] = \App\Helpers\Cc::ccFormatDate(\App\Helpers\Cc::ccFormatNumbers($ccstartmonth . $ccstartyear));
                        $params["cardissuenum"] = \App\Helpers\Cc::ccFormatNumbers($ccissuenum);
                        $errormessage = $gateway->call("cc_validation", $params);
                        $params = NULL;
                    } else {
                        if ($ccinfo == "new") {
                            $errormessage .= \App\Helpers\Cc::updateCCDetails("", $cctype, $ccnumber, $cccvv, $ccexpirymonth . $ccexpiryyear, $ccstartmonth . $ccstartyear, $ccissuenum, "", "", $gateway->getLoadedModule());
                        }
                        if ($cccvv2) {
                            $cccvv = $cccvv2;
                        }
                        if (!$cccvv) {
                            $errormessage .= "<li>" . $_LANG["creditcardccvinvalid"];
                        }
                    }
                    if (!$errormessage) {
                        if ($billingcid === "new") {
                            $array = array("userid" => $userId, "firstname" => $firstname, "lastname" => $lastname, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber);
                            $billingcid = \App\Models\Contact::insertGetId($array);
                        }
                        if ($ccinfo == "new") {
                            $errormessage .= \App\Helpers\Cc::updateCCDetails($userId, $cctype, $ccnumber, $cccvv, $ccexpirymonth . $ccexpiryyear, $ccstartmonth . $ccstartyear, $ccissuenum, $nostore, "", $gateway->getLoadedModule(), $payMethod, $ccDescription);
                            if ($payMethod) {
                                $billingContact = $client->contacts->find($billingcid);
                                if ($billingContact) {
                                    $payMethod->contact()->associate($billingContact);
                                    $payMethod->save();
                                }
                            }
                        }
                    }
                }
                if (!$errormessage) {
                    $gatewayName = "";
                    if (!$payMethod && $ccinfo && is_numeric($ccinfo)) {
                        $payMethod = \App\Payment\PayMethod\Model::findForClient($ccinfo, $client->id);
                    }
                    if ($payMethod) {
                        $invoiceModel->payMethod()->associate($payMethod);
                        $invoiceModel->save();
                    } else {
                        $payMethod = $invoiceModel->payMethod;
                    }
                    if ($payMethod) {
                        $gatewayName = $payMethod->gateway_name;
                    }
                    $params = \App\Helpers\Cc::getCCVariables($invoiceid, $gatewayName, $payMethod);
                    if (!$payMethod) {
                        $payMethod = $params["payMethod"];
                    }
                    if ($ccinfo == "new") {
                        $params["cardtype"] = \App\Helpers\Cc::getCardTypeByCardNumber($ccnumber);
                        $params["cardnum"] = \App\Helpers\Cc::ccFormatNumbers($ccnumber);
                        $params["cardexp"] = \App\Helpers\Cc::ccFormatDate(\App\Helpers\Cc::ccFormatNumbers($ccexpirymonth . $ccexpiryyear));
                        $params["cardstart"] = \App\Helpers\Cc::ccFormatDate(\App\Helpers\Cc::ccFormatNumbers($ccstartmonth . $ccstartyear));
                        $params["cardissuenum"] = \App\Helpers\Cc::ccFormatNumbers($ccissuenum);
                        $params["gatewayid"] = \App\Models\Client::where(array("id" => $userId))->value("gatewayid");
                        if ($payMethod && $payMethod->payment instanceof \App\Payment\Contracts\RemoteTokenDetailsInterface) {
                            $params["gatewayid"] = $payMethod->payment->getRemoteToken();
                        }
                        $params["billingcontactid"] = $billingcid;
                    }
                    // if (function_exists($params["paymentmethod"] . "_3dsecure")) {
                    if ($gateway->functionExists("_3dsecure")) {
                        $params["cccvv"] = $cccvv;
                        // $buttoncode = call_user_func($params["paymentmethod"] . "_3dsecure", $params);
                        $buttoncode = $gateway->call("_3dsecure", $params);
                        $buttoncode = str_replace("<form", "<form target=\"3dauth\"", $buttoncode);
                        $smartyvalues["code"] = $buttoncode;
                        $smartyvalues["width"] = "400";
                        $smartyvalues["height"] = "500";
                        if ($buttoncode == "success" || $buttoncode == "declined") {
                            $result = $buttoncode;
                        } else {
                            // $clientArea->setTemplate("3dsecure");
                            // $clientArea->output();
                            // exit;
                            // return view("3dsecure", $smartyvalues);
                            DB::commit();
                            return \App\Helpers\ClientareaFunctions::outputClientArea("3dsecure", true, array(), $smartyvalues);
                        }
                    } else {
                        if ($gateway->isTokenised() && $payMethod->isLocalCreditCard()) {
                            $payment = $payMethod->payment;
                            $newRemotePayMethod = \App\Payment\PayMethod\Adapter\RemoteCreditCard::factoryPayMethod($invoiceModel->client, $invoiceModel->client->billingContact, $payMethod->getDescription());
                            $newRemotePayMethod->setGateway($gateway);
                            \App\Helpers\Cc::updateCCDetails($userId, $payment->getCardType(), $payment->getCardNumber(), $cccvv, $payment->getExpiryDate()->toCreditCard(), $payment->getStartDate(), $payment->getIssueNumber(), "", "", $invoiceModel->paymentGateway, $newRemotePayMethod);
                            $payMethod->delete();
                            $payMethod = $newRemotePayMethod;
                            $invoiceModel->payMethod()->associate($payMethod);
                            $invoiceModel->save();
                            $params = \App\Helpers\Cc::getCCVariables($invoiceid, $invoiceModel->paymentGateway, $payMethod);
                        }
                        $result = \App\Helpers\Cc::captureCCPayment($invoiceid, $cccvv, true, $payMethod);
                    }
                    if ($params["paymentmethod"] == "offlinecc") {
                        \App\Helpers\Functions::sendAdminNotification("account", "Offline Credit Card Payment Submitted", "<p>An offline credit card payment has just been submitted.  Details are below:</p><p>Client ID: " . $userId . "<br />Invoice ID: " . $invoiceid . "</p>");
                        // redir("id=" . $invoiceid . "&offlinepaid=true", "viewinvoice.php");
                        // return redirect()->to("viewinvoice.php?id=$invoiceid&offlinepaid=true");
                        $url = route("pages.services.mydomains.viewinvoiceweb", $invoiceid)."?paymentsuccess=true";
                        DB::commit();
                        return redirect()->to($url);
                    }
                    if ($result == "success") {
                        // redir("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
                        // return redirect()->to("viewinvoice.php?id=$invoiceid&paymentsuccess=true");
                        $url = route("pages.services.mydomains.viewinvoiceweb", $invoiceid)."?paymentsuccess=true";
                        DB::commit();
                        return redirect()->to($url);
                    } else {
                        $errormessage = "<li>" . $_LANG["creditcarddeclined"];
                        $action = "";
                        if ($ccinfo == "new" && $payMethod instanceof \App\Payment\PayMethod\Model) {
                            $payMethod->delete();
                        }
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                // DEBUG: remove dd
                // dd($e);
                DB::rollback();
                $errormessage = "<li>" .$e->getMessage();
            }
        }
        $billingContactId = NULL;
        if ($invoiceModel && $invoiceModel->payMethod && $invoiceModel->payMethod->getContactId()) {
            $billingContactId = $invoiceModel->payMethod->getContactId();
        }
        if (!$billingContactId) {
            $billingContactId = "billing";
        }
        $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($userId, $billingContactId);
        $cardtype = $clientsdetails["cctype"];
        $cardlastfour = $clientsdetails["cclastfour"];
        if (!$errormessage && $fromorderform) {
            $firstname = $clientsdetails["firstname"];
            $lastname = $clientsdetails["lastname"];
            $email = $clientsdetails["email"];
            $address1 = $clientsdetails["address1"];
            $address2 = $clientsdetails["address2"];
            $city = $clientsdetails["city"];
            $state = $clientsdetails["state"];
            $postcode = $clientsdetails["postcode"];
            $country = $clientsdetails["country"];
            $phonenumber = $clientsdetails["telephoneNumber"];
        }
        $invoiceData = $invoice->getOutput();
        $existingClientCards = array();
        $gatewayCards = $client->payMethods->creditCards()->validateGateways()->sortByExpiryDate()->filter(function (\App\Payment\Contracts\PayMethodInterface $payMethod) use($gateway) {
            if ($payMethod->getType() === \App\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_LOCAL && !in_array($gateway->getWorkflowType(), array(\App\Module\Gateway::WORKFLOW_ASSISTED, \App\Module\Gateway::WORKFLOW_REMOTE))) {
                return true;
            }
            $payMethodGateway = $payMethod->getGateway();
            return $payMethodGateway && $payMethodGateway->getLoadedModule() === $gateway->getLoadedModule();
        });
        $billingContacts = array(array("id" => 0, "firstname" => $client->firstName, "lastname" => $client->lastName, "companyname" => $client->companyName, "email" => $client->email, "address1" => $client->address1, "address2" => $client->address2, "city" => $client->city, "state" => $client->state, "postcode" => $client->postcode, "country" => $client->country, "countryname" => $client->countryName, "phonenumber" => $client->phoneNumber));
        foreach ($client->contacts as $contact) {
            $billingContacts[$contact->id] = array("id" => $contact->id, "firstname" => $contact->firstName, "lastname" => $contact->lastName, "companyname" => $contact->companyName, "email" => $contact->email, "address1" => $contact->address1, "address2" => $contact->address2, "city" => $contact->city, "state" => $contact->state, "postcode" => $contact->postcode, "country" => $contact->country, "countryname" => $contact->countryName, "phonenumber" => $contact->phoneNumber);
        }
        $defaultCardKey = NULL;
        $lowestOrder = NULL;
        foreach ($gatewayCards as $key => $creditCardMethod) {
            if (is_null($lowestOrder) || $lowestOrder < $creditCardMethod->order_preference) {
                $lowestOrder = $creditCardMethod->order_preference;
                $defaultCardKey = $key;
            }
            $existingClientCards[$key] = \App\Helpers\Cc::getPayMethodCardDetails($creditCardMethod);
        }
        $existingCard = array("cardtype" => NULL, "cardlastfour" => NULL, "cardnum" => \Lang::get("client.nocarddetails"), "fullcardnum" => NULL, "expdate" => "", "startdate" => "", "issuenumber" => NULL, "gatewayid" => NULL, "billingcontactid" => NULL);
        if (!empty($existingClientCards)) {
            $existingCard = $existingClientCards[$defaultCardKey];
        }
        $countryObject = new \App\Helpers\Country();
        if (!$ccinfo) {
            if ($invoiceModel->payMethod && $invoiceModel->payMethod->gateway_name == $gateway->getLoadedModule()) {
                $ccinfo = $invoiceModel->payMethod->id;
            } else {
                if (isset($existingCard["payMethod"]) && $existingCard["payMethod"]) {
                    $ccinfo = $existingCard["payMethod"]->id;
                } else {
                    if ($gatewayCards->count()) {
                        $ccinfo = $gatewayCards->first()->id;
                    } else {
                        $ccinfo = "new";
                    }
                }
            }
        }
        $smartyvalues = array(
            "firstname" => $firstname,
            "lastname" => $lastname,
            "address1" => $address1,
            "address2" => $address2,
            "city" => $city,
            "state" => $state,
            "postcode" => $postcode,
            "country" => $country,
            "countryname" => $countryObject->getName($country),
            "countriesdropdown" => \App\Helpers\ClientHelper::getCountriesDropDown($country),
            "phonenumber" => $phonenumber,
            "cardOnFile" => 0 < strlen($existingCard["cardlastfour"]),
            "addingNewCard" => $ccinfo == "new" || 0 >= strlen($existingCard["cardlastfour"]),
            "ccinfo" => $ccinfo,
            "cardtype" => $existingCard["cardtype"],
            "cardnum" => $existingCard["cardlastfour"],
            "existingCardType" => $existingCard["cardtype"],
            "existingCardLastFour" => $existingCard["cardlastfour"],
            "existingCardExpiryDate" => $existingCard["expdate"],
            "existingCardStartDate" => $existingCard["startdate"],
            "existingCardIssueNum" => $existingCard["issuenumber"],
            "defaultBillingContact" => $billingContacts[$client->billingContactId],
            "billingContacts" => $billingContacts,
            "existingCards" => $existingClientCards,
            "cctype" => $cctype,
            "ccdescription" => $ccDescription,
            "ccnumber" => $ccnumber,
            "ccexpirymonth" => $ccexpirymonth,
            "ccexpiryyear" => ($ccexpiryyear == "" ? 0 : $ccexpiryyear) < 2000 ? ($ccexpiryyear == "" ? 0 : $ccexpiryyear) + 2000 : $ccexpiryyear,
            "ccstartmonth" => $ccstartmonth,
            "ccstartyear" => ($ccstartyear == "" ? 0 : $ccstartyear) < 2000 ? ($ccstartyear == "" ? 0 : $ccstartyear) + 2000 : $ccstartyear,
            "ccissuenum" => $ccissuenum,
            "cccvv" => $cccvv,
            "errormessage" => $errormessage,
            "invoiceid" => $invoiceid,
            "invoicenum" => $invoicenum,
            "total" => $invoiceData["total"],
            "balance" => $invoiceData["balance"],
            "showccissuestart" => \App\Helpers\Cfg::getValue("ShowCCIssueStart"),
            "shownostore" => \App\Helpers\Cfg::getValue("CCAllowCustomerDelete") && !$gateway->functionExists("storeremote"),
            "allowClientsToRemoveCards" => \App\Helpers\Cfg::getValue("CCAllowCustomerDelete") && !$gateway->functionExists("storeremote"),
            "invoice" => $invoiceData,
            "invoiceitems" => $invoice->getLineItems(),
            "userDetailsValidationError" => $userDetailsValidationError,
            "billingcontact" => $billingcid ?? "",
        );
        $smartyvalues["months"] = $gateways->getCCDateMonths();
        $smartyvalues["startyears"] = $gateways->getCCStartDateYears();
        $smartyvalues["years"] = $gateways->getCCExpiryDateYears();
        $smartyvalues["expiryyears"] = $smartyvalues["years"];
        if (is_null($params)) {
            $params = \App\Helpers\Cc::getCCVariables($invoiceid);
        }
        $smartyvalues["remotecode"] = "";
        // if (function_exists($params["paymentmethod"] . "_remoteinput")) {
        if ($gateway->functionExists("remoteinput")) {
            // $smartyvalues["remotecode"] = true;
            $output = $gateway->call("remoteinput", $params);
            $output = str_replace("<form", "<form target=\"ccframe\"", $output);
            $smartyvalues["remotecode"] = $output;
        }

        // additional
        $smartyvalues['client'] = $client;
        $smartyvalues['servedOverSsl'] = $request->secure();
        // dd($smartyvalues);
        // ThemesManager::set(\App\Helpers\ThemeManager::orderformThemeVendor() . "/" . \App\Helpers\ThemeManager::orderformThemeDefault());
        return \App\Helpers\ClientareaFunctions::outputClientArea("creditcard", true, array("ClientAreaPageCreditCardCheckout"), $smartyvalues);
    }
}
