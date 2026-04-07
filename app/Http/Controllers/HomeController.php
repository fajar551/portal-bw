<?php

namespace App\Http\Controllers;

use App\Helpers\Cfg;
use App\Helpers\Client;
use App\Helpers\ClientClass;
use App\Helpers\Format as HelpersFormat;
use App\Helpers\Gateway;
use App\Helpers\ResponseAPI;
use Illuminate\Http\Request;
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\Ticketstatus;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\Invoiceitem;
use Auth;
use Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        global $_LANG;

        $auth = Auth::user();
        if (!$auth) {
            return redirect('/login');
        }
        $userid = $auth->id;
        
        $userCart = \Cart::session($userid);

        //Get Currencies
        $currenciesData = Currency::all();

        //Get Services by logged in user
        $getServices = Hosting::selectRaw("tblhosting.id, tblhosting.domain, tblproducts.name, tblhosting.domainstatus, tblhosting.amount")->where("userid", $userid)->orderBy("domain", "ASC")->join("tblproducts", "tblhosting.packageid", "tblproducts.id")->get();

        //Get Domain by logged in user
        $getDomain = Domain::where("userid", $userid)->orderBy("id", "DESC")->get();

        //Get Support Ticket by logged in user
        $getTicket = Ticket::where("userid", $userid)->orderBy("id", "DESC")->get();
        $getTicketStatus = Ticketstatus::all();
        $ticketDetails = $getTicket->split(6)->toArray();

        //Get Invoice by logged in user
        $getInvoice = Invoice::where("userid", $userid)->orderBy("id", "DESC")->get();

        //Get Contact by logged in user
        $getContacts = Contact::where("userid", $userid)->orderBy("id", "DESC")->get();


        //Formatted User Credit
        $userCurrency = $auth->currency;
        $currency = \App\Helpers\Format::getCurrency($userid, $userCurrency);
        $formattedBalance = new \App\Helpers\FormatterPrice($auth->credit, $currency);

        // return view('pages.dashboard.index', [
        //     'user' => $auth,
        //     'getServices' => $getServices,
        //     'getDomain' => $getDomain,
        //     'getTicket' => $getTicket,
        //     'getInvoice' => $getInvoice,
        //     'contacts' => $getContacts,
        //     'ticketDetails' => $ticketDetails ? $ticketDetails[0] : [],
        //     'getTicketStatus' => $getTicketStatus,
        //     'userCredit' => $formattedBalance
        // ], $_LANG);
        $smartyvalues = [
            'user' => $auth,
            'getServices' => $getServices,
            'getDomain' => $getDomain,
            'getTicket' => $getTicket,
            'getInvoice' => $getInvoice,
            'contacts' => $getContacts,
            'ticketDetails' => $ticketDetails ? $ticketDetails[0] : [],
            'getTicketStatus' => $getTicketStatus,
            'userCredit' => $formattedBalance
        ];
        return \App\Helpers\ClientareaFunctions::outputClientArea("pages.dashboard.index", true, array(), $smartyvalues);
    }

    public function AddDepositFundsOLD(Request $request)
    {
        $addFundsMaxBal = Cfg::getValue('AddFundsMaximumBalance');
        $addFundsMax = Cfg::getValue('AddFundsMaximum');
        $addFundsMin = Cfg::getValue('AddFundsMinimum');

        $params['maxbal'] = $addFundsMaxBal;
        $params['fundmax'] = $addFundsMax;
        $params['fundmin'] = $addFundsMin;
        $gateways = Gateway::GetGatewaysArray();
        $paymentMethodDropdown = (new Gateway($request))->paymentMethodsSelection("- " . __("admin.clientsummarysetPaymentMethod") . " -");

        return view('pages.dashboard.addfunds', ['gateways' => $gateways, 'paymentMethodDropdown' => $paymentMethodDropdown], $params);
    }
    public function AddDepositFunds(Request $request)
    {
        $legacyClient = new \App\Helpers\ClientClass(Auth::guard('web')->user());
        \App\Helpers\ClientHelper::checkContactPermission("invoices");
        // $ca->setDisplayTitle(\Lang::get("client.addfunds"));
        // $ca->setTagLine(\Lang::get("client.addfundsintro"));
        $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails();
        $addfundsmaxbal = \App\Helpers\Format::convertCurrency(\App\Helpers\Cfg::getValue("AddFundsMaximumBalance"), 1, $clientsdetails["currency"]);
        $addfundsmax = \App\Helpers\Format::convertCurrency(\App\Helpers\Cfg::getValue("AddFundsMaximum"), 1, $clientsdetails["currency"]);
        $addfundsmin = \App\Helpers\Format::convertCurrency(\App\Helpers\Cfg::getValue("AddFundsMinimum"), 1, $clientsdetails["currency"]);
        $result = \App\Models\Order::where(array("userid" => $legacyClient->getID(), "status" => "Active"))->count();
        $data = $result;
        $numactiveorders = $data;
        $amount = 0;
        $smartyvalues["addfundsdisabled"] = false;
        $smartyvalues["notallowed"] = false;
        $smartyvalues["errormessage"] = "";
        if (!\App\Helpers\Cfg::getValue("AddFundsRequireOrder")) {
            $numactiveorders = 1;
        }
        if (!\App\Helpers\Cfg::getValue("AddFundsEnabled")) {
            $smartyvalues["addfundsdisabled"] = true;
        } else {
            if (!$numactiveorders) {
                $smartyvalues["notallowed"] = true;
            } else {
                $amount = $request->input("amount");
                $paymentmethod = $request->input("paymentmethod");
                if ($amount) {
                    $errormessage = "";
                    $totalcredit = $clientsdetails["credit"] + $amount;
                    if ($addfundsmaxbal < $totalcredit) {
                        $errormessage = \Lang::get("client.addfundsmaximumbalanceerror") . " " . \App\Helpers\Format::formatCurrency($addfundsmaxbal);
                    }
                    if ($addfundsmax < $amount) {
                        $errormessage = \Lang::get("client.addfundsmaximumerror") . " " . \App\Helpers\Format::formatCurrency($addfundsmax);
                    }
                    if ($amount < $addfundsmin) {
                        $errormessage = \Lang::get("client.addfundsminimumerror") . " " . \App\Helpers\Format::formatCurrency($addfundsmin);
                    }
                    if ($errormessage) {
                        $smartyvalues["errormessage"] = $errormessage;
                    } else {
                        $paymentmethods = \App\Helpers\Gateway::getGatewaysArray();
                        if (!array_key_exists($paymentmethod, $paymentmethods)) {
                            $paymentmethod = \App\Helpers\Gateway::getClientsPaymentMethod($legacyClient->getID());
                        }
                        $paymentmethod = \App\Helpers\Gateways::makeSafeName($paymentmethod);
                        if (!$paymentmethod) {
                            exit("Unexpected payment method value. Exiting.");
                        }
                        $invoiceid = \App\Helpers\ProcessInvoices::createInvoices($legacyClient->getID());
                        \App\Models\Invoiceitem::insert(array("userid" => $legacyClient->getID(), "type" => "AddFunds", "relid" => "", "description" => \Lang::get("client.addfunds"), "amount" => $amount, "taxed" => "0", "duedate" => \Carbon\Carbon::now(), "paymentmethod" => $paymentmethod));
                        $invoiceid = \App\Helpers\ProcessInvoices::createInvoices($legacyClient->getID(), "", true);
                        $result = \App\Models\Paymentgateway::where(array("gateway" => $paymentmethod, "setting" => "type"));
                        $data = $result;
                        $gatewaytype = $data->value("value");
                        if ($gatewaytype == "CC" || $gatewaytype == "OfflineCC") {
                            // TODO: this
                            // if (!\App\Helpers\Functions::isValidforPath($paymentmethod)) {
                            //     exit("Invalid Payment Gateway Name");
                            // }
                            // $gatewaypath = ROOTDIR . "/modules/gateways/" . $paymentmethod . ".php";
                            // if (file_exists($gatewaypath)) {
                            //     require_once $gatewaypath;
                            // }
                            // if (!function_exists($paymentmethod . "_link")) {
                            //     redir("invoiceid=" . $invoiceid, "creditcard.php");
                            // }
                        }
                        return redirect()->route('pages.services.mydomains.viewinvoiceweb', $invoiceid);
                        // $invoice = new \App\Helpers\InvoiceClass($invoiceid);
                        // $paymentbutton = $invoice->getPaymentLink();
                        // $templatefile = "forwardpage";
                        // $smartyvalues["message"] =  \Lang::get("client.forwardingtogateway");
                        // $smartyvalues["code"] =  $paymentbutton;
                        // $smartyvalues["invoiceid"] =  $invoiceid;
                        // // exit;
                        // // $this->setTheme($orderFormTemplateName);
                        // return view($templatefile, $smartyvalues); // in parent theme
                    }
                } else {
                    $amount = $addfundsmin;
                }
            }
        }
        // $ca->setTemplate("clientareaaddfunds");
        $smartyvalues["minimumamount"] = \App\Helpers\Format::formatCurrency($addfundsmin);
        $smartyvalues["maximumamount"] = \App\Helpers\Format::formatCurrency($addfundsmax);
        $smartyvalues["maximumbalance"] = \App\Helpers\Format::formatCurrency($addfundsmaxbal);
        $smartyvalues["amount"] = \App\Helpers\Functions::format_as_currency($amount);
        $gatewayslist = \App\Helpers\Gateway::showPaymentGatewaysList(array(), $legacyClient->getID());
        $smartyvalues["gateways"] = $gatewayslist;
        // $ca->addOutputHookFunction("ClientAreaPageAddFunds");
        return view("pages.dashboard.addfunds", $smartyvalues); // in parent theme
    }

    public function GenerateInvoice(Request $request)
    {
        $auth = Auth::guard('web')->user();
        $userid = $auth->id;
        $amount = $request->amount;
        $paymentmethod = $request->paymentmethod;

        $rules = [
            'amount' => 'required',
            'paymentmethod' => 'required'
        ];
        $messages = [
            'amount.required' => 'Fill in the amount of money!',
            'paymentmethod.required' => 'Choose payment gateway first.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with(['error' => $messages]);
        }
        $user = new ClientClass($userid);
        $addFundsMaxBal = Cfg::getValue('AddFundsMaximumBalance');
        $addFundsMax = Cfg::getValue('AddFundsMaximum');
        $addFundsMin = Cfg::getValue('AddFundsMinimum');
        if ($amount) {
            $totalCredit = $auth->credit + $amount;
            $errorMsg = "";
            if ($addFundsMaxBal < $totalCredit) {
                $errorMsg .= __('client.addfundsmaximumbalanceerror') . " " . HelpersFormat::formatCurrency($addFundsMaxBal);
            }
            if ($addFundsMax < $amount) {
                $errorMsg .= __('client.addfundsmaximumerror') . " " . HelpersFormat::formatCurrency($addFundsMax);
            }
            if ($amount < $addFundsMin) {
                $errorMsg .= __('client.addfundsminimumerror') . " " . HelpersFormat::formatCurrency($addFundsMin);
            }

            if ($errorMsg) {
                return redirect()->back()->with(['error_funds' => $errorMsg]);
            } else {
                global $_LANG;
                $paymentmethods = Gateway::GetGatewaysArray();
                if (!array_key_exists($paymentmethod, $paymentmethods)) {
                    $paymentmethod = Gateway::getClientsPaymentMethod($userid);
                }
                if (!$paymentmethod) {
                    return redirect()->back()->with(['error_payment' => 'Unexpected payment method value. Exiting.']);
                }
                // dd($paymentmethod);
                $invoiceId = \App\Helpers\ProcessInvoices::createInvoices($userid);
                $InvoiceItem = new Invoiceitem();
                $InvoiceItem->userid = $userid;
                $InvoiceItem->invoiceid = $invoiceId;
                $InvoiceItem->type = "AddFunds";
                $InvoiceItem->relid = "";
                $InvoiceItem->description = $_LANG["addfunds"] ?? "";
                $InvoiceItem->amount = $amount;
                $InvoiceItem->taxed = "0";
                $InvoiceItem->duedate = now();
                $InvoiceItem->paymentmethod = $paymentmethod;
                $InvoiceItem->save();
                $invoiceId = \App\Helpers\ProcessInvoices::createInvoices($userid, "", true);

                return redirect()->route('pages.services.mydomains.viewinvoiceweb', $invoiceId);
            }
        }
    }
    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');
        // return view('home');
    }

    public function EmailNotes()
    {
        return view('pages.profile.emailnotes.index');
    }

    public function UploadAccountTerms()
    {
        return view('pages.profile.uploadaccountterms.index');
    }

    public function EditAccountDetails()
    {
        return view('pages.profile.editaccountdetails.index');
    }

    public function ContactSub()
    {
        $auth = Auth::user();
        $client = new Client();
        $getAllPermission = Contact::$allPermissions;

        $countries = $client->getCountries();
        return view('pages.profile.contactsub.index', ['countries' => $countries, 'allPermission' => $getAllPermission, 'auth' => $auth]);
    }

    public function ContactSub_dtJson()
    {
        $auth = Auth::user();
        $client = new Client();

        // ? Get Contact thorugh query eloquent
        $queryContact = Contact::where('userid', $auth->id)->orderBy('id', 'desc')->get();

        return datatables()->of($queryContact)->editColumn('name', function ($row) {
            $fullname = "$row->firstname " . "$row->lastname";
            return $fullname;
        })->editColumn('companyName', function ($row) {
            $companyName = $row->companyname;
            return $companyName ? $companyName : "None";
        })->editColumn('email', function ($row) {
            return $row->email;
        })->editColumn('phonenumber', function ($row) {
            return $row->phonenumber;
        })->editColumn('actions', function ($row) {
            $route = route('pages.profile.contactsub.details', $row->id);
            $action = "";
            $action .= "<a href=\"{$route}\" class=\"btn btn-sm btn-outline-success\">Details</a>";
            return $action;
        })
            ->rawColumns(['actions', 'companyName'])
            ->addIndexColumn()
            ->toJson();
    }

    public function ContactSub_CreateNew(Request $request)
    {
        $auth = Auth::user();
        $userid = $auth->id;


        $rules = [
            'firstname'  => 'required',
            'lastname'   => 'required',
            'companyname'   => 'required',
            'email'   => 'required',
            'phonenumber'   => 'required',
            'address1'   => 'required',
            'address2'   => 'nullable',
            'city'   => 'required',
            'state'   => 'required',
            'postcode'   => 'required|numeric',
            'country'   => 'required',
        ];
        $messages = [
            'firstname.required'    => 'Name is required.',
            'lastname.required'    => 'Last Name is required.',
            'companyname.required'    => 'Company is Name required.',
            'email.required'    => 'Email is required.',
            'phonenumber.required'    => 'Phone is required.',
            'address1.required'    => 'Address is required.',
            // 'address2.required'    => 'Address is required.',
            'city.required'    => 'City is required.',
            'state.required'    => 'State is required.',
            'postcode.required'    => 'Postal is Code required.',
            'country.required'    => 'Country is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with(['error_add_contact' => $messages]);
        }


        if ($request->permissions) {
            $strPerms = implode(",", $request->permissions);
        }

        $subaccount = (int)$request->subaccount;
        $params['clientid'] = $userid;
        $params['firstname'] = $request->firstname;
        $params['lastname'] = $request->lastname;
        $params['companyname'] = $request->companyname;
        $params['email'] = $request->email;
        $params['address1'] = $request->address1;
        $params['address2'] = $request->address2 ?? '';
        $params['city'] = $request->city;
        $params['state'] = $request->state;
        $params['postcode'] = $request->postcode;
        $params['country'] = $request->country;
        $params['phonenumber'] = $request->phonenumber;
        $params['subaccount'] = $subaccount;
        $params['password'] = $request->password;
        $params["password2"] = $request->password;
        $params['permissions'] = $strPerms ?? array();
        $params['email_preferences'] = $request->email_preferences ?? "";
        $params['tax_id'] = $request->tax_id ?? "";

        if ($subaccount == 0) {
            $params['password'] = $params['permissions'] = "";
        }

        $response = (new Client())->AddContact($params);
        // dd($response);
        $successMsg = "New contact with email <u>" . $response['email'] . "</u> has been created";
        return redirect()->route('pages.profile.contactsub.index')->with('success', $successMsg);
    }

    public function ContactSub_Details($id)
    {
        $client = new Client();
        $queryContact = Contact::where('id', $id)->orderBy('id', 'desc')->get();

        foreach ($queryContact as $row) {
            $data = $row;
        }
        $contactActivePermission = explode(",", $data['permissions']);
        // dd($data);
        $getAllPermission = Contact::$allPermissions;
        $countries = $client->getCountries();
        return view('pages.profile.contactsub.details', ['contact_data' => $data, 'countries' => $countries, 'allPermission' => $getAllPermission, 'contactPermissionActive' => $contactActivePermission]);
    }

    public function ContactSub_Update(Request $request, $id)
    {
        $auth = Auth::user();
        $userid = $auth->id;


        $rules = [
            'firstname'  => 'required',
            'lastname'   => 'required',
            'companyname'   => 'required',
            'email'   => 'required',
            'phonenumber'   => 'required',
            'address1'   => 'required',
            'city'   => 'required',
            'state'   => 'required',
            'postcode'   => 'required|numeric',
            'country'   => 'required',
        ];
        $messages = [
            'firstname.required'    => 'Name is required.',
            'lastname.required'    => 'Last Name is required.',
            'companyname.required'    => 'Company is Name required.',
            'email.required'    => 'Email is required.',
            'phonenumber.required'    => 'Phone is required.',
            'address1.required'    => 'Address is required.',
            'city.required'    => 'City is required.',
            'state.required'    => 'State is required.',
            'postcode.required'    => 'Postal is Code required.',
            'country.required'    => 'Country is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with(['error_add_contact' => $messages]);
        }

        $params = $request->all();
        if (!$request->permissions) {
            $params['permissions'] = array();
        }
        $params['contactid'] = $id;
        $params["password2"] = $request->password;
        $params["userid"] = $userid;
        // dd($params);
        $response = (new Client())->UpdateContact($params);
        return redirect()->back()->with('success', "This contact #" . $response['contactid'] . " has been updated!");
    }

    public function ContactSub_Delete(Request $request)
    {
        $id = $request->id;
        $response = (new Client())->DeleteContact($id);
        if ($response["result"] == "error") {
            return ResponseAPI::Error([
                'message' => $response["message"],
            ]);
        }

        return ResponseAPI::Success([
            'message' => "The data successfully deleted!",
        ]);
    }

    public function UpdatePassword()
    {
        return view('pages.profile.changepassword.index');
    }
}
