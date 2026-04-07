@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Manage Orders</title>
@endsection

@section('styles')
    <!-- Date Picker -->
    <link href="{{ Theme::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <!-- <div class="row">
                        <div class="col-12 p-3">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">Dashboard</h4>
                            </div>
                        </div>
                    </div> -->
                <!-- end page title -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h4>Manage Orders</h4>
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-12">
                                                @if (session('message'))
                                                    <div class="alert alert-{{ session('type') }}">
                                                        <button type="button" class="close" data-dismiss="alert">×</button>
                                                        <strong>{!! session('message') !!}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @if($verifyEmailAddressEnabled && !$isEmailAddressVerified)
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="alert alert-warning" role="alert">
                                                    <div class="row d-flex align-items-center">
                                                        <div class="col-lg-8 col-sm-12">
                                                            <p class="m-0">
                                                                <img class="ml-0" src="{{ Theme::asset('img/loading.gif') }}" id="moduleSettingsLoader" alt="loading" hidden>
                                                                <i class="ri-error-warning-fill mr-2"></i>
                                                                {{ __("admin.emailAddressNotVerified") }}
                                                            </p>
                                                        </div>
                                                        <div class="col-lg-4 col-sm-12">
                                                            <button class="btn btn-success px-3 mr-auto float-lg-right btn-block" id="btnResendVerificationEmail" onclick="resendVerificationEmail(this);">
                                                                {{ __("admin.resendEmail") }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card p-3">
                                                    <form action="#" enctype="multipart/form-data" id="form-view-order" autocomplete="off">
                                                        @csrf
                                                        <input type="number" name="id" value="{{ $id }}" hidden>
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Date: </label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        {{ $date }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Order #: </label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        {{ "$ordernum (ID: $id)" }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Client: </label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        {!! $client !!}
                                                                        @if ($isEmailAddressVerified) 
                                                                        <span class="badge badge-success">{{__("admin.clientsemailVerified")}}</span>
                                                                        @else
                                                                        <span class="badge badge-danger">{{ __("admin.clientsemailUnverified") }}</span>
                                                                        @endif
                                                                        <p>{!! $address !!}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Promotion Code:</label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        @php
                                                                            if ($promocode) {
                                                                                if (strpos($promotype, "Percentage")) {
                                                                                    echo $promocode . " - " . $promovalue . "% " . str_replace("Percentage", "", $promotype);
                                                                                } else {
                                                                                    echo $promocode . " - " . \App\Helpers\format::formatCurrency($promovalue) . " " . str_replace("Fixed Amount", "", $promotype);
                                                                                }
                                                                                echo "<br />";
                                                                            }
                                                                            
                                                                            if (is_array($orderdata)) {
                                                                                if (array_key_exists("bundleids", $orderdata) && is_array($orderdata["bundleids"])) {
                                                                                    foreach ($orderdata["bundleids"] as $bid) {
                                                                                        $bundlename = \App\Model\Bundle::find($bid);
                                                                                        if (!$bundlename) {
                                                                                            $bundlename = "Bundle Has Been Deleted";
                                                                                        } else {
                                                                                            $bundlename = $bundlename->name;
                                                                                        }
                                                                                        echo "Bundle ID " . $bid . " - " . $bundlename . "<br />";
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                if (!$promocode) {
                                                                                    echo "None";
                                                                                }
                                                                            }
                                                                        @endphp
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Notes:</label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        <textarea name="notes" id="notes" rows="4" class="form-control">{{ $notes }}</textarea>
                                                                        <br>
                                                                        <button type="button" class="btn btn-sm btn-primary" id="btn-update-notes" onclick="addNotes();">{{ __("Update/Save Notes") }}</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Payment Method:</label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        <p>{{ $paymentmethod }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Amount:</label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        <p>{{ $amount }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Invoice #:</label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        @if ($invoiceid) 
                                                                            <a href="{{ route('admin.pages.billing.invoices.edit', ['id' => $invoiceid]) }}">{{ $invoiceid }}</a>
                                                                        @else 
                                                                            {{ __("admin.ordersnoInvoice") }}
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Status:</label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        {!! $statusoptions !!}
                                                                        {{-- <select class="form-control" name="orderstatus" id="orderstatus">
                                                                            <option value="Any">Any</option>
                                                                            @foreach ($orderStatus as $data)
                                                                                <option value="{{ $data->title }}" style="color:{{ $data->color }}">{{ ( __("admin.status" .strtolower($data->title)) ? __("admin.status" .strtolower($data->title)) : $data->title) }}</option>
                                                                            @endforeach
                                                                        </select> --}}
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">IP Address:</label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        {!! $ipaddressField !!}
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label text-right">Affiliate:</label>
                                                                    <div class="col-sm-9 pt-2">
                                                                        {!! $affiliateField !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {{-- <div class="col-lg-12 text-center">
                                                                <button type="submit" class="btn btn-success px-3">Save Changes</button>
                                                                <button type="reset" class="btn btn-light px-3">Cancel Changes</button>
                                                            </div> --}}
                                                        </div>
                                                    </form>
                                                </div>
                                                <hr>
                                            </div>

                                            <div class="col-lg-12">
                                                <h4>Order Items</h4>
                                                <div class="card p-3">
                                                    <form method="post" action="{{ route("admin.pages.clients.domainregistrations.whois") }}" target="_blank" id="frmWhois">
                                                        @csrf
                                                        <input type="hidden" name="domain" id="frmWhoisDomain" value="" />
                                                    </form>
                                                    
                                                    <form action="#" method="POST" enctype="multipart/form-data" id="form-order-items" autocomplete="off">
                                                        @csrf
                                                        <input type="hidden" name="activate" value="true">
                                                        <div class="table-responsive">
                                                            <table id="dt-orders" class="table table-bordered dt-responsive nowrap w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">Item</th>
                                                                        <th class="text-center">Description</th>
                                                                        <th class="text-center">Billing Cycle</th>
                                                                        <th class="text-center">Amount</th>
                                                                        <th class="text-center">Status</th>
                                                                        <th class="text-center">Payment Status</th>
                                                                        <th class="text-center">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($services as $numericIndex => $service)
                                                                        @php
                                                                            if (0 < strlen($service->subscriptionId)) {
                                                                                $orderHasASubscription = true;
                                                                            }

                                                                            $hostingid = $service->id;
                                                                            $domain = $service->domain;
                                                                            $billingcycle = $service->billingcycle;
                                                                            $hostingstatus = $service->status;
                                                                            $firstpaymentamount = \App\Helpers\Format::formatCurrency($service->firstpaymentamount);
                                                                            $recurringamount = $service->amount;
                                                                            $packageid = $service->packageid;
                                                                            $server = $service->server;
                                                                            $regdate = $service->regdate;
                                                                            $nextduedate = $service->nextduedate;
                                                                            $serverusername = $service->username;
                                                                            $serverpassword = (new \App\Helpers\Pwd())->decrypt($service->password);
                                                                            $groupname = $service->product->productGroup->name;
                                                                            $productname = $service->product->name;
                                                                            $producttype = $service->product->type;
                                                                            $welcomeemail = $service->product->welcomeEmailTemplateId;
                                                                            $autosetup = $service->product->autoSetup;
                                                                            $servertype = $service->product->module;
                                                                            $serverInterface = \App\Module\Server::factoryFromModel($service);

                                                                            if ($serverInterface->getMetaDataValue("AutoGenerateUsernameAndPassword") !== false && $hostingstatus === \App\Models\Hosting::STATUS_PENDING) {
                                                                                if (!$serverusername) {
                                                                                    $serverusername = (new \App\Module\Server())->createServerUsername($domain);
                                                                                }
                                                                                if (!$serverpassword) {
                                                                                    $serverpassword = $serverInterface->generateRandomPasswordForModule();
                                                                                }
                                                                                if ($serverusername != $service->username || $serverpassword != (new \App\Helpers\Pwd())->decrypt($service->password)) {
                                                                                    $service->username = $serverusername;
                                                                                    $service->password = (new \App\Helpers\Pwd())->encrypt($serverpassword);
                                                                                    $service->save();
                                                                                }
                                                                            }

                                                                            if ($domain && $producttype != "other") {
                                                                                $domain .= "<br />
                                                                                    (<a href=\"http://$domain\" target=\"_blank\" style=\"color:#cc0000\">www</a> 
                                                                                    <a href=\"javascript:void(0);\" onclick=\"\$('#frmWhoisDomain').val('" . addslashes($domain) . "');\$('#frmWhois').submit();return false\">" . __("admin.domainswhois") 
                                                                                    ."</a> <a href=\"http://www.intodns.com/$domain\" target=\"_blank\" style=\"color:#006633\">intoDNS</a>)";
                                                                            }

                                                                            $route = route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $userid, 'id' => $hostingid]);
                                                                            $itemField = "<a href=\"$route\">";

                                                                            if ($producttype == "hostingaccount") {
                                                                                $itemField .= __("admin.orderssharedhosting");
                                                                            } else if ($producttype == "reselleraccount") {
                                                                                $itemField .= __("admin.ordersresellerhosting");
                                                                            } else if ($producttype == "server") {
                                                                                $itemField .= __("admin.ordersserver");
                                                                            } else if ($producttype == "other") {
                                                                                $itemField .= __("admin.ordersother");
                                                                            }

                                                                            $itemField .= "</a>";

                                                                            echo "<tr>
                                                                                    <td class=\"text-center\"><b>" . $itemField . "</b></td>
                                                                                    <td>" . $groupname . " - " . $productname . "<br>" . $domain . "</td>
                                                                                    <td>" . ($billingcycle ? __("admin.billingcycles" .str_replace(array("-", "account", " "), "", strtolower($billingcycle))) : "-") . "</td>
                                                                                    <!--<td>" . $firstpaymentamount . "</td>-->
                                                                                    <td>" . \App\Helpers\Format::formatCurrency($subtotal) . "</p>
                                                                                    <td>" . __("admin.status" .strtolower($hostingstatus)) . "</td>
                                                                                    <td><b>" . $paymentstatus . "</b></td>
                                                                                    <td class=\"text-center\"><button type=\"button\" class=\"btn btn-light btn-shower\">Detail</button></td>
                                                                                </tr>";
                                                                            
                                                                            if ($showpending && $hostingstatus == "Pending") {
                                                                                echo "<tr class=\"child\" style=\"display: none;\">
                                                                                    <td style=\"background-color:#FFFFFF;text-align:left;\">
                                                                                    <td style=\"background-color:#EFF2F9;text-align:left;\" colspan=\"5\">";
                                                                                        if ($servertype) {
                                                                                            echo __("admin.fieldsusername") . ": <input type=\"text\" name=\"vars[products][" . $hostingid . "][username]\" value=\"" . $serverusername . "\" class=\"form-control input-inline input-150\"> " 
                                                                                            . __("admin.fieldspassword") . ": <input type=\"text\" name=\"vars[products][" . $hostingid . "][password]\" value=\"" . $serverpassword . "\" class=\"form-control input-inline input-150\"> " 
                                                                                            . __("admin.fieldsserver") . ": <select name=\"vars[products][" . $hostingid . "][server]\" class=\"select2-search-disable form-control select-inline\"> "
                                                                                            .  "<option value=\"\">" . __("admin.none") . "</option>";
                                                                                            
                                                                                            if (!in_array($servertype, $serverList)) {
                                                                                                $serverList[$servertype] = array();
                                                                                                $servers = \App\Models\Server::enabled()->ofModule($servertype)->get();
                                                                                                if (0 < $servers->count()) {
                                                                                                    $serverList[$servertype] = $servers;
                                                                                                }
                                                                                            }
                                                                                            
                                                                                            foreach ($serverList[$servertype] as $listedServer) {
                                                                                                $selectedServer = $listedServer->id == $server ? " selected" : "";
                                                                                                $serverName = $listedServer->name;
                                                                                                if ($listedServer->disabled) {
                                                                                                    $serverName .= " (" . __("admin.emailtplsdisabled") . ")";
                                                                                                }
                                                                                                echo "<option value=\"" . $listedServer->id . "\"" . $selectedServer . ">\n    " . $serverName . " (" . $listedServer->activeAccountsCount . "/" . $listedServer->maxAccounts . ")</option>";
                                                                                            }

                                                                                            echo "</select>";
                                                                                            echo "<label class=\"checkbox-inline mt-3\">
                                                                                                <input type=\"checkbox\" name=\"vars[products][" . $hostingid . "][runcreate]\" id=\"serviceRunModuleCreate" . $numericIndex . "\"";
                                                                                                if ($hostingstatus == "Pending" && $autosetup) {
                                                                                                    echo " checked";
                                                                                                }
                                                                                            echo "> " . __("admin.ordersrunmodule") . "</label> &nbsp;&nbsp;";
                                                                                        }

                                                                                        echo " <label class=\"checkbox-inline mt-3\">
                                                                                            <input type=\"checkbox\" name=\"vars[products][" . $hostingid . "][sendwelcome]\"";
                                                                                        if ($hostingstatus == "Pending" && $welcomeemail) {
                                                                                            echo " checked";
                                                                                        }

                                                                                        echo "> " . __("admin.orderssendwelcome") . "</label>
                                                                                    </td>
                                                                                </tr>";
                                                                            }
                                                                        @endphp

                                                                    @endforeach 

                                                                    @foreach ($hostingAddons as $numericIndex => $hostingAddon)
                                                                        @php
                                                                            $aId = $hostingAddon->id;
                                                                            $hostingId = $hostingAddon->serviceId;
                                                                            $addonId = $hostingAddon->addonId;
                                                                            $name = $hostingAddon->name;
                                                                            $domain = $hostingAddon->serviceProperties->get("Domain");
                                                                            if (!$domain) {
                                                                                $domain = $hostingAddon->service->domain;
                                                                            }
                                                                            if (!$name && $hostingAddon->addonId) {
                                                                                $name = $hostingAddon->productAddon->name;
                                                                            }
                                                                            $billingCycle = $hostingAddon->billingCycle;
                                                                            $addonAmount = $hostingAddon->setupFee + $hostingAddon->recurringFee;
                                                                            $addonStatus = $hostingAddon->status;
                                                                            $regDate = $hostingAddon->registrationDate;
                                                                            $nextDueDate = $hostingAddon->nextDueDate;
                                                                            $addonAmount = \App\Helpers\Format::formatCurrency($addonAmount);
                                                                            $serverType = "";
                                                                            if ($hostingAddon->addonId) {
                                                                                $serverType = $hostingAddon->productAddon->module;
                                                                            }
                                                                            $cleanedCycleName = "billingcycles" . str_replace(array("-", "account", " "), "", strtolower($billingCycle));
                                                                            $cleanedStatus = "status" . strtolower($addonStatus);
                                                                            if (!array_key_exists($cleanedCycleName, $lang)) {
                                                                                $lang[$cleanedCycleName] = __("admin.$cleanedCycleName");
                                                                            }
                                                                            if (!array_key_exists($cleanedStatus, $lang)) {
                                                                                $lang[$cleanedStatus] = __("admin.$cleanedStatus");
                                                                            }

                                                                            $route = route('admin.pages.clients.viewclients.clientservices.editAddon', ['userid' => $userid, 'id' => $hostingId, "aid" => $aId]);
                                                                            echo "<tr>
                                                                                    <td align=\"center\"> <a href=\"$route\"><b>" . $lang["ordersaddon"] . "</b></a></td>
                                                                                    <td>" . $name . " - " . $domain . "</td>
                                                                                    <td>" . $lang[$cleanedCycleName] . "</td>
                                                                                    <td>" . $addonAmount . "</td>
                                                                                    <td>" . $lang[$cleanedStatus] . "</td>
                                                                                    <td>" . $paymentstatus . "</td>
                                                                                    <td class=\"text-center\"><button type=\"button\" class=\"btn btn-light btn-shower\">Detail</button></td>
                                                                                </tr>";
                                                                            
                                                                            if ($addonStatus == "Pending") {
                                                                                $serverOutput = "-";
                                                                                if ($serverType) {
                                                                                    $addonUsername = $addonPassword = "";
                                                                                    $serverInterface = \App\Module\Server::factoryFromModel($hostingAddon);
                                                                                    if ($serverInterface->getMetaDataValue("AutoGenerateUsernameAndPassword") !== false) {
                                                                                        $addonUsername = $hostingAddon->serviceProperties->get("Username");
                                                                                        $addonPassword = $hostingAddon->serviceProperties->get("Password");
                                                                                        if (!$serverusername) {
                                                                                            $addonUsername = (new \App\Module\Server())->createServerUsername($domain);
                                                                                        }
                                                                                        if (!$addonPassword) {
                                                                                            $addonPassword = $serverInterface->generateRandomPasswordForModule();
                                                                                        }
                                                                                        if ($addonUsername != $hostingAddon->serviceProperties->get("Username") || $addonPassword != $hostingAddon->serviceProperties->get("Password")) {
                                                                                            $hostingAddon->serviceProperties->save(array("Username" => $addonUsername, "Password" => $addonPassword));
                                                                                        }
                                                                                    }
                                                                                    if (!in_array($serverType, $serverList)) {
                                                                                        $serverList[$serverType] = array();
                                                                                        $servers = \App\Models\Server::enabled()->ofModule($serverType)->get();
                                                                                        if (0 < $servers->count()) {
                                                                                            $serverList[$serverType] = $servers;
                                                                                        }
                                                                                    }
                                                                                    $serverListOutput = "";
                                                                                    foreach ($serverList[$serverType] as $listedServer) {
                                                                                        $selectedServer = $listedServer->id == $hostingAddon->serverId ? " selected" : "";
                                                                                        $serverName = $listedServer->name;
                                                                                        if ($listedServer->disabled) {
                                                                                            $serverName .= " (" . __("admin.emailtplsdisabled") . ")";
                                                                                        }
                                                                                        $serverListOutput = "<option value=\"" . $listedServer->id . "\"" . $selectedServer . ">\n    " . $serverName . " (" . $listedServer->activeAccountsCount . "/" . $listedServer->maxAccounts . ")\n</option>";
                                                                                    }
                                                                                    $runCreatedChecked = "";
                                                                                    if ($hostingAddon->productAddon->autoActivate) {
                                                                                        $runCreatedChecked = " checked=\"checked\"";
                                                                                    }
                                                                                    $serverOutput = (string) $lang["fieldsusername"] . ": <input type=\"text\" name=\"vars[addons][" . $aId . "][username]\" value=\"" . $addonUsername . "\" class=\"form-control input-inline input-150\">" 
                                                                                    . $lang["fieldspassword"] . ": <input type=\"text\" name=\"vars[addons][" . $aId . "][password]\" value=\"" . $addonPassword . "\" class=\"form-control input-inline input-150\">" 
                                                                                    . $lang["fieldsserver"] . ": <select name=\"vars[addons][" . $aId . "][server]\" class=\"select2-search-disable form-control select-inline\">
                                                                                        <option value=\"\">" . $lang["none"] . "</option>
                                                                                        " . $serverListOutput . "
                                                                                        </select>
                                                                                        <label class=\"checkbox-inline mt-3\">
                                                                                            <input type=\"checkbox\" name=\"vars[addons][" . $aId . "][runcreate]\" id=\"addonRunModuleCreate" . $numericIndex . "\"" . $runCreatedChecked . "> " . $lang["ordersrunmodule"] . "
                                                                                        </label>&nbsp;&nbsp;";
                                                                                }

                                                                                $welcomeEmailCheckbox = "";
                                                                                if ($hostingAddon->productAddon && $hostingAddon->productAddon->welcomeEmailTemplateId) {
                                                                                    $welcomeEmailCheckbox = " <label class=\"checkbox-inline mt-3\"><input type=\"checkbox\" name=\"vars[addons][" . $aId . "][sendwelcome]\" checked=\"checked\"> " . $lang["orderssendwelcome"] . "</label>";
                                                                                }

                                                                                echo "<tr class=\"child\" style=\"display: none;\">
                                                                                        <td style=\"background-color:#FFFFFF;text-align:left;\">
                                                                                        <td style=\"background-color:#EFF2F9;text-align:left;\" colspan=\"5\">" . $serverOutput . " " . $welcomeEmailCheckbox . "  </td>
                                                                                    </tr>";
                                                                            }
                                                                        @endphp
                                                                    @endforeach

                                                                    @foreach ($domains as $data)
                                                                        @php
                                                                            if (0 < strlen($data["subscriptionid"])) {
                                                                                $orderHasASubscription = true;
                                                                            }
                                                                            $domainid = $data["id"];
                                                                            $type = $data["type"];
                                                                            $domain = $data["domain"];
                                                                            $registrationperiod = $data["registrationperiod"];
                                                                            $status = $data["status"];
                                                                            $regdate = $data["registrationdate"];
                                                                            $nextduedate = $data["nextduedate"];
                                                                            $domainamount = \App\Helpers\format::formatCurrency($data["firstpaymentamount"]);
                                                                            $domainregistrar = $data["registrar"];
                                                                            $dnsmanagement = $data["dnsmanagement"];
                                                                            $emailforwarding = $data["emailforwarding"];
                                                                            $idprotection = $data["idprotection"];
                                                                            $type = __("admin.domains" .strtolower($type));

                                                                            $route = route('admin.pages.clients.viewclients.clientdomain.index', ['userid' => $userid, 'domainid' => $domainid]);

                                                                            echo "<tr>
                                                                                    <td align=\"center\"><a href=\"$route\"><b>" . __("admin.fieldsdomain") . "</b></a></td>
                                                                                    <td>" . $type . " - " . $domain . "<br>";
                                                                            if ($contactid) {
                                                                                $contact = \App\Models\Contact::select("firstname", "lastname")->find($contactid);
                                                                                if ($contact) {
                                                                                    $routeContact = route('admin.pages.clients.viewclients.clientcontacts.update', ['userid' => $userid, 'contactid' => $contactid]);
                                                                                    $contact = $contact->toArray();
                                                                                    echo __("admin.domainsregistrant") . ": <a href=\"$routeContact\">" . $data["firstname"] . " " . $data["lastname"] . " (" . $contactid . ")</a><br>";
                                                                                }
                                                                            }
                                                                            if ($dnsmanagement) {
                                                                                echo " + " . __("admin.domainsdnsmanagement") . "<br>";
                                                                            }
                                                                            if ($emailforwarding) {
                                                                                echo " + " . __("admin.domainsemailforwarding") . "<br>";
                                                                            }
                                                                            if ($idprotection) {
                                                                                echo " + " . __("admin.domainsidprotection") . "<br>";
                                                                            }
                                                                            if (isset($transfersecret[$domain])) {
                                                                                echo __("admin.domainseppcode") . ": " . \App\Helpers\Sanitize::makeSafeForOutput($transfersecret[$domain]);
                                                                            }
                                                                            $regperiods = 1 < $registrationperiod ? "s" : "";
                                                                            echo "</td>";
                                                                            
                                                                            echo "<td>" . $registrationperiod . " " . __("admin.domainsyear" . $regperiods) . "</td>
                                                                                <td>" . $domainamount . "</td>
                                                                                <td>" . ($status ? __("admin.status" .strtolower(str_replace(" ", "", $status))) : "-") . "</td>
                                                                                <td><b>" . $paymentstatus . "</td>
                                                                                <td class=\"text-center\"><button type=\"button\" class=\"btn btn-light btn-shower\">Detail</button></td>
                                                                            </tr>";
                                                                            if ($showpending && $status == "Pending") {
                                                                                echo "<tr class=\"child\" style=\"display: none;\">"
                                                                                        ."<td style=\"background-color:#FFFFFF;text-align:left;\">"
                                                                                        ."<td style=\"background-color:#EFF2F9;text-align:left;\" colspan=\"5\">" . __("admin.fieldsregistrar") . ": " . (new \App\Module\Registrar())->getRegistrarsDropdownMenu("", "vars[domains][" . $domainid . "][registrar]", "select2-search-disable") 
                                                                                            . " <label class=\"checkbox-inline mt-3\"> <input type=\"checkbox\" name=\"vars[domains][" . $domainid . "][sendregistrar]\" checked> " . __("admin.orderssendtoregistrar") . "</label> &nbsp;&nbsp;"
                                                                                            . " <label class=\"checkbox-inline mt-3\"> <input type=\"checkbox\" name=\"vars[domains][" . $domainid . "][sendemail]\" checked> " . __("admin.orderssendconfirmation") . " </label>&nbsp;&nbsp;"
                                                                                        ."</td>"
                                                                                    ."</tr>";
                                                                            }
                                                                        @endphp
                                                                    @endforeach

                                                                    @php
                                                                        if ($renewals) {
                                                                            $renewals = explode(",", $renewals);
                                                                            foreach ($renewals as $renewal) {
                                                                                $renewal = explode("=", $renewal);
                                                                                list($domainid, $registrationperiod) = $renewal;
                                                                                $result = \App\Models\Domain::find($domainid);
                                                                                $data = $result->toArray();

                                                                                $domainid = $data["id"];
                                                                                $type = $data["type"];
                                                                                $domain = $data["domain"];
                                                                                $registrar = $data["registrar"];
                                                                                $status = $data["status"];
                                                                                $regdate = $data["registrationdate"];
                                                                                $nextduedate = $data["nextduedate"];
                                                                                $domainamount = \App\Helpers\format::formatCurrency($data["recurringamount"]);
                                                                                $domainregistrar = $data["registrar"];
                                                                                $dnsmanagement = $data["dnsmanagement"];
                                                                                $emailforwarding = $data["emailforwarding"];
                                                                                $idprotection = $data["idprotection"];

                                                                                $route = route('admin.pages.clients.viewclients.clientdomain.index', ['userid' => $userid, 'domainid' => $domainid]);

                                                                                echo "<tr>
                                                                                        <td><a href=\"$route\"><b>" . __("admin.fieldsdomain") . "</b></a></td>
                                                                                        <td>" . __("admin.domainsrenewal") . " - " . $domain . "<br>";
                                                                                        if ($dnsmanagement) {
                                                                                            echo " + " . __("admin.domainsdnsmanagement") . "<br>";
                                                                                        }
                                                                                        if ($emailforwarding) {
                                                                                            echo " + " . __("admin.domainsemailforwarding") . "<br>";
                                                                                        }
                                                                                        if ($idprotection) {
                                                                                            echo " + " . __("admin.domainsidprotection") . "<br>";
                                                                                        }
                                                                                        $regperiods = 1 < $registrationperiod ? "s" : "";
                                                                                        echo "</td>
                                                                                                <td>" . $registrationperiod . " " . __("admin.domainsyear" . $regperiods) . "</td>
                                                                                                <td>" . $domainamount . "</td><td>" . __("admin.status" .strtolower($status)) . "</td>
                                                                                                <td><b>" . $paymentstatus . "</td>
                                                                                                <td class=\"text-center\"><button type=\"button\" class=\"btn btn-light btn-shower\">Detail</button></td>
                                                                                        </tr>";
                                                                                        if ($showpending) {
                                                                                            $checkstatus = $registrar && !$CONFIG["AutoRenewDomainsonPayment"] ? " checked" : " disabled";
                                                                                            echo "<tr class=\"child\" style=\"display: none;\">
                                                                                                    <td style=\"background-color:#FFFFFF;text-align:left;\">
                                                                                                    <td style=\"background-color:#EFF2F9;text-align:left;\" colspan=\"5\">
                                                                                                        <label class=\"checkbox-inline mt-3\"> <input type=\"checkbox\" name=\"vars[renewals][" . $domainid . "][sendregistrar]\"" . $checkstatus . " /> Send to Registrar</label> &nbsp;&nbsp;
                                                                                                        <label class=\"checkbox-inline mt-3\"> <input type=\"checkbox\" name=\"vars[renewals][" . $domainid . "][sendemail]\"" . $checkstatus . " /> Send Confirmation Email</label>
                                                                                                    </td>
                                                                                                </tr>";
                                                                                        }
                                                                            }
                                                                        }
                                                                        
                                                                        if (substr($promovalue, 0, 2) == "DR") {
                                                                            $domainid = substr($promovalue, 2);
                                                                            $result = \App\Models\Domain::find($domainid);
                                                                            $data = $result->toArray();

                                                                            $domainid = $data["id"];
                                                                            $type = $data["type"];
                                                                            $domain = $data["domain"];
                                                                            $registrar = $data["registrar"];
                                                                            $registrationperiod = $data["registrationperiod"];
                                                                            $status = $data["status"];
                                                                            $regdate = $data["registrationdate"];
                                                                            $nextduedate = $data["nextduedate"];
                                                                            $domainamount = \App\Helpers\format::formatCurrency($data["firstpaymentamount"]);
                                                                            $domainregistrar = $data["registrar"];
                                                                            $dnsmanagement = $data["dnsmanagement"];
                                                                            $emailforwarding = $data["emailforwarding"];
                                                                            $idprotection = $data["idprotection"];

                                                                            $route = route('admin.pages.clients.viewclients.clientdomain.index', ['userid' => $userid, 'domainid' => $domainid]);

                                                                            echo "<tr>
                                                                                    <td><a href=\"$route\"><b>" . __("admin.fieldsdomain") . "</b></a></td>
                                                                                    <td>" . __("admin.domainsrenewal") . " - " . $domain . "<br>";
                                                                            if ($dnsmanagement) {
                                                                                echo " + " . __("admin.domainsdnsmanagement") . "<br>";
                                                                            }
                                                                            if ($emailforwarding) {
                                                                                echo " + " . __("admin.domainsemailforwarding") . "<br>";
                                                                            }
                                                                            if ($idprotection) {
                                                                                echo " + " . __("admin.domainsidprotection") . "<br>";
                                                                            }
                                                                            $regperiods = 1 < $registrationperiod ? "s" : "";
                                                                            echo "</td>";
                                                                            echo "  <td>" . $registrationperiod . " " . __("admin.domainsyear" . $regperiods) . "</td>
                                                                                    <td>" . $domainamount . "</td>
                                                                                    <td>" . ($status ? __("admin.status" .strtolower($status)) : "-") . "</td>
                                                                                    <td><b>" . $paymentstatus . "</td>
                                                                                    <td class=\"text-center\"><button type=\"button\" class=\"btn btn-light btn-shower\">Detail</button></td>
                                                                                </tr>";

                                                                            if ($showpending) {
                                                                                echo "<tr class=\"child\" style=\"display: none;\">
                                                                                        <td style=\"background-color:#FFFFFF;text-align:left;\">
                                                                                        <td style=\"background-color:#EFF2F9;text-align:left;\" colspan=\"5\">
                                                                                            <label class=\"checkbox-inline mt-3\"><input type=\"checkbox\" name=\"vars[domains][" . $domainid . "][sendregistrar]\"";
                                                                                        if ($registrar && !$CONFIG["AutoRenewDomainsonPayment"]) {
                                                                                            echo " checked";
                                                                                        } else {
                                                                                            echo " disabled";
                                                                                        }
                                                                                        echo "> Send to Registrar</label> &nbsp;&nbsp;";
                                                                                        echo "<label class=\"checkbox-inline mt-3\"><input type=\"checkbox\" name=\"vars[domains][" . $domainid . "][sendemail]\"";
                                                                                        if ($registrar) {
                                                                                            echo " checked";
                                                                                        } else {
                                                                                            echo " disabled";
                                                                                        }
                                                                                        echo "> Send Confirmation Email</label>
                                                                                        </td>
                                                                                    </tr>";
                                                                            }
                                                                        }
                                                                    @endphp

                                                                    @foreach ($upgrades as $upgrade)
                                                                        @php
                                                                            if (!$upgrade->userid) {
                                                                                $up = \App\Models\Upgrade::find($upgrade->id);
                                                                                $up->userid = $userid;
                                                                                $upgrade->userid = $userid;
                                                                                $up->save();
                                                                            }

                                                                            if ($upgrade->type == "package") {
                                                                                $newValue = explode(",", $upgrade->newValue);
                                                                                list($upgrade->newValue, $upgrade->newCycle) = $newValue;
                                                                                $upgradeType = "Product Upgrade";
                                                                                $description = $upgrade->originalProduct->productGroup->name . " - " . $upgrade->originalProduct->name . " => " . $upgrade->newProduct->name;
                                                                                if ($upgrade->service->domain) {
                                                                                    $description .= "<br>" . $upgrade->service->domain;
                                                                                }

                                                                                $manageLink = route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $upgrade->userId, 'id' => $upgrade->relid]);
                                                                            } else if ($upgrade->type == "configoptions") {
                                                                                $upgradeType = "Options Upgrade";
                                                                                
                                                                                // $result2 = select_query("{$pfx}hosting", "{$pfx}products.name AS productname,domain,{$pfx}hosting.userid", array("{$pfx}hosting.id" => $upgrade->relid), "", "", "", "{$pfx}products ON {$pfx}products.id={$pfx}hosting.packageid");
                                                                                $result2 = \App\Models\Hosting::selectRaw("{$pfx}products.name AS productname,domain,{$pfx}hosting.userid")
                                                                                                ->where("{$pfx}hosting.id", $upgrade->relid)
                                                                                                ->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                                                                                                ->first();

                                                                                $data = $result2 ? $result2->toArray() : [];

                                                                                $productname = @$data["productname"];
                                                                                $domain = @$data["domain"];
                                                                                $userId = @$data["userid"];
                                                                                if (!$upgrade->userid) {
                                                                                    $upgrade->userid = $userId;
                                                                                    $upgrade->save();
                                                                                }
                                                                                $tempvalue = explode("=>", $upgrade->originalValue);
                                                                                list($configid, $oldoptionid) = $tempvalue;
                                                                                
                                                                                // $result2 = select_query("{$pfx}productconfigoptions", "", array("id" => $configid));
                                                                                $result2 = \App\Models\Hostingconfigoption::find($configid);
                                                                                $data = $result2 ? $result2->toArray() : [];

                                                                                $configname = @$data["optionname"];
                                                                                if (strpos($configname, "|") !== false) {
                                                                                    $configname = explode("|", $configname);
                                                                                    $configname = $configname[1];
                                                                                }
                                                                                $optiontype = @$data["optiontype"];
                                                                                $oldoptionname = "";
                                                                                $newoptionname = "";
                                                                                if ($optiontype == 1 || $optiontype == 2) {
                                                                                    // $result2 = select_query("{$pfx}productconfigoptionssub", "", array("id" => $oldoptionid));
                                                                                    $result2 = \App\Models\Productconfigoptionssub::find($oldoptionid);
                                                                                    $data = $result2 ? $result2->toArray() : [];

                                                                                    $oldoptionname = @$data["optionname"];
                                                                                    if (strpos($oldoptionname, "|") !== false) {
                                                                                        $oldoptionname = explode("|", $oldoptionname);
                                                                                        $oldoptionname = $oldoptionname[1];
                                                                                    }

                                                                                    // $result2 = select_query("{$pfx}productconfigoptionssub", "", array("id" => $upgrade->newValue));
                                                                                    $result2 = \App\Models\Productconfigoptionssub::find($upgrade->newValue);
                                                                                    $data = $result2 ? $result2->toArray() : [];

                                                                                    $newoptionname = @$data["optionname"];
                                                                                    if (strpos($newoptionname, "|") !== false) {
                                                                                        $newoptionname = explode("|", $newoptionname);
                                                                                        $newoptionname = $newoptionname[1];
                                                                                    }
                                                                                } else if ($optiontype == 3) {
                                                                                    if ($oldoptionid) {
                                                                                        $oldoptionname = "Yes";
                                                                                        $newoptionname = "No";
                                                                                    } else {
                                                                                        $oldoptionname = "No";
                                                                                        $newoptionname = "Yes";
                                                                                    }
                                                                                } else if ($optiontype == 4) {
                                                                                    // $result2 = select_query("{$pfx}productconfigoptionssub", "", array("configid" => $configid));
                                                                                    $result2 = \App\Models\Productconfigoptionssub::where("configid", $configid)->first();
                                                                                    $data = $result2 ? $result2->toArray() : [];

                                                                                    $optionname = @$data["optionname"];
                                                                                    if (strpos($optionname, "|") !== false) {
                                                                                        $optionname = explode("|", $optionname);
                                                                                        $optionname = $optionname[1];
                                                                                    }
                                                                                    $oldoptionname = $oldoptionid;
                                                                                    $newoptionname = $upgrade->newValue . " x " . $optionname;
                                                                                }
                                                                                $description = $productname . " - " . $domain . "<br>" . $configname . ": " . $oldoptionname . " => " . $newoptionname;
                                                                                
                                                                                $route = route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $upgrade->userId, 'id' => $upgrade->relid]);
                                                                                $manageLink = $route;
                                                                            } else if ($upgrade->type == "service") {
                                                                                $upgradeType = "Product Upgrade";
                                                                                $description = $upgrade->originalProduct->productGroup->name . " - " . $upgrade->originalProduct->name . " => " . $upgrade->newProduct->name;
                                                                                if ($upgrade->service->domain) {
                                                                                    $description .= "<br>" . $upgrade->service->domain;
                                                                                }

                                                                                $route = route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $upgrade->userId, 'id' => $upgrade->relid]);
                                                                                $manageLink = $route;
                                                                            } else if ($upgrade->type == "addon") {
                                                                                $upgradeType = "Addon Upgrade";
                                                                                $description = $upgrade->originalAddon->name . " => " . $upgrade->newAddon->name;

                                                                                $hostingId =  \App\Models\Hostingaddon::find($upgrade->relid)->serviceId;
                                                                                
                                                                                $route = route('admin.pages.clients.viewclients.clientservices.editAddon', ['userid' => $upgrade->userId, 'id' => $hostingId, "aid" => $upgrade->relid]);
                                                                                $manageLink = $route;
                                                                            }

                                                                            echo "<tr>
                                                                                    <td align=\"center\"><a href=\"" . $manageLink . "\"><b>" . $upgradeType . "</b></a></td>
                                                                                    <td><a href=\"" . $manageLink . "\">" . $description . "</a><br>" . (in_array($upgrade->type, array("service", "addon")) ? "<small>New Recurring Amount: " . \App\Helpers\Format::formatCurrency($upgrade->newRecurringAmount) . " - Credit Amount: " . \App\Helpers\Format::formatCurrency($upgrade->creditAmount) . "<br>" . "Calculation based on " . $upgrade->daysRemaining . " unused days of " . $upgrade->totalDaysInCycle . " totals days in the current billing cycle.</small></td>" : "") . "
                                                                                    <td>" . ($upgrade->newCycle ? __("admin.billingcycles" .(new \App\Helpers\Cycles())->getNormalisedBillingCycle($upgrade->newCycle)) : "-") . "</td>
                                                                                    <td>" . \App\Helpers\Format::formatCurrency($upgrade->upgradeAmount) . "</td>
                                                                                    <td>" . __("admin.status" .strtolower($upgrade->status)) . "</td>
                                                                                    <td><b>" . $paymentstatus . "</b></td>
                                                                                    <td>N/A</td>
                                                                                </tr>";
                                                                        @endphp
                                                                    @endforeach
                                                                    {{-- @empty
                                                                        <tr>
                                                                            <td colspan="7"><p>No record found</p></td>
                                                                        </tr>
                                                                    @endforelse --}}
                                                                </tbody>
                                                            </table>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <!--<p class="text-right text-bold">{{ "Total Due: $amount" }}</p>-->
                                                                    <p class="text-right text-bold">{{ "Total Due: " . \App\Helpers\Format::formatCurrency($total) }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mt-3">
                                                            <label for="custom-field" class="col-sm-2 col-form-label mt-2">Actions: </label>
                                                            <div class="col-sm-10">
                                                                <button type="button" class="btn btn-success px-3 mt-2" @if (!$showpending) disabled @else onclick="actionCommand('activate')" @endif>Accept Order</button>
                                                                <button type="button" class="btn btn-light px-3 mt-2" @if ($orderstatus == "Cancelled") disabled @else onclick="actionCommand('cancel')" @endif>Cancel Order</button>
                                                                <button type="button" class="btn btn-light px-3 mt-2" @if (!$invoiceid || $invoicestatus == "Refunded") disabled @else onclick="actionCommand('cancelrefund')" @endif>Cancel & Refund</button>
                                                                <button type="button" class="btn btn-light px-3 mt-2" @if ($orderstatus == "Fraud") disabled @else onclick="actionCommand('fraud')" @endif>Set as Fraud</button>
                                                                <button type="button" class="btn btn-light px-3 mt-2" onclick="actionCommand('pending')">Set Back to Pending</button>
                                                                <button type="button" class="btn btn-danger px-3 mt-2" id="btn-delete" onclick="actionCommand('ajaxCanOrderBeDeleted')">Delete Order</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Required datatable js -->
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    
    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}

    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    <script>
        $(() => {
            $(".btn-shower").click(function() {
                $(this).closest('tr').nextUntil("tr:has(.btn-shower)").toggle("slow", function() {});
            });
        });

        const resendVerificationEmail = async () => {
            const url = "{!! route('admin.pages.clients.viewclients.clientsummary.resendVerificationEmail') !!}";
            const payloads = {
                userid: "{{ $userid }}",
            };

            options.body = JSON.stringify(payloads);

            $("#btnResendVerificationEmail").attr({ "disabled": true });
            $("#moduleSettingsLoader").removeAttr("hidden");

            const response = await cbmsPost(url, options);
            
            // $("#btnResendVerificationEmail").attr({ "disabled": false });
            $("#moduleSettingsLoader").attr({ "hidden": true});

            if (response) {    
                const { result, message, data = null } = response;

                if (result == "error" || !data) {
                    Toast.fire({ icon: result, title: message, });
                    return false;
                }

                $("#btnResendVerificationEmail").text(data.body);
    
                return true;
            }

            console.log(`resendVerificationEmail: Failed to fetch data. Response: ${response}`);
        }

        // TODO: showaffassign (popup modal)
        const showaffassign = (id) => {
            Toast.fire({ icon: "warning", title: "N/A", });
        }

        const addNotes = async () => {
            const url = "{!! route('admin.pages.orders.vieworder.updateNotes') !!}";
            const formData = $('#form-view-order').serializeJSON();
            const payloads = {
                ...formData,
            }

            options.body = JSON.stringify(payloads);

            $("#btn-update-notes").attr({ "disabled": true }).text("Loading...");

            const response = await cbmsPost(url, options);
            
            $("#btn-update-notes").attr({ "disabled": false }).text("Update/Save Notes");

            if (response) {    
                const { result, message, data } = response;

                if (result == 'error') {
                    Toast.fire({ icon: 'error', title: message, });
                    return false;
                }

                $("#notes").html(data.notes);

                Toast.fire({ icon: 'success', title: message, });
                return true;
            }

            console.log(`addNotes: Failed to fetch data. Response: ${response}`);
        }

        const actionCommand = (action, params = {}) => {
            let url = "{!! route('admin.pages.orders.vieworder.actionCommand') !!}";
            let id = "{{ $id }}";
            let message = "";
            let payloads = {
                id,
                action,
            }

            switch (action) {
                case "delete":
                    url = "{!! route('admin.pages.orders.listallorders.actionCommand') !!}";
                    message = "Are you sure you want to delete this order? This will delete all related products/services & invoice.";
                    break;
                case "ajaxCanOrderBeDeleted":
                    ajaxCanOrderBeDeleted(id);
                    return;
                case "ajaxChangeOrderStatus":
                    message = "Are you sure you want to change the order status?";
                    payloads.status = $("#ajaxchangeorderstatus").val();
                    break;
                case "activate":
                    message = "Are you sure you want to approve this orders?";
                    payloads.formData = $('#form-order-items').serializeJSON();
                    break;
                case "cancel":
                    message = "Are you sure you want to cancel this order? This will also run module termination for any active products/services.";
                    @if ($orderHasASubscription)
                    message = `
                        <label for="#" class="col-sm-12 col-form-label">Are you sure you want to cancel this order? This will also run module termination for any active products/services.</label>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" name="cancelsub" id="cancelsub1" class="form-check-input" value="true">
                                    <label class="form-check-label" for="cancelsub1">{{ __('Also Cancel Subscription') }}</label>
                                </div>
                            </div>
                        </div>`;
                    @endif
                    break;
                case "cancelrefund":
                    message = "Are you sure you want to cancel & refund this order? This will also run module termination for any active products/services.";
                    break;
                case "fraud":
                    message = "Are you sure you want to cancel this order? This will also run module termination for any active products/services.";
                    @if ($orderHasASubscription)
                    message = `
                        <label for="#" class="col-sm-12 col-form-label">Are you sure you want to cancel this order? This will also run module termination for any active products/services.</label>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" name="cancelsub" id="cancelsub2" class="form-check-input" value="true">
                                    <label class="form-check-label" for="cancelsub2">{{ __('Also Cancel Subscription') }}</label>
                                </div>
                            </div>
                        </div>`;
                    @endif
                    break;
                case "pending":
                    message = "Are you sure you want to set this order back to Pending?";
                    break;
                case "pending":
                    message = "Are you sure you want to set this order back to Pending?";
                    break;
                default:
                    return;
            }

            Swal.fire({
                title: "Are you sure?",
                html: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText:  "OK",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async (data) => { 
                    if (action == "cancel") {
                        payloads.cancelsub = $("#cancelsub1").is(':checked') ? true : false;
                    }

                    if (action == "fraud") {
                        payloads.cancelsub = $("#cancelsub2").is(':checked') ? true : false;
                    }

                    options.body = JSON.stringify(payloads);

                    const response = await cbmsPost(url, options);
                    if (!response) {
                        const error = "An error occured.";
                        return Swal.showValidationMessage(`Request failed: ${error}`);
                    }

                    return response;
                },
            }).then((response) => {
                if (response.value) {
                    const { result, message } = response.value;

                    Toast.fire({ icon: result, title: message, });

                    if (result == 'error') {
                        return false;
                    }

                    if (action == "delete") {
                        setTimeout(function(){
                            window.location.href = "{!! route('admin.pages.orders.listallorders.index') !!}"
                        } , 2000);

                        return true;
                    }

                    setTimeout(function() {
                        location.reload();
                    }, 2000);

                    return true;
                }
            }).catch(swal.noop);
        }

        const ajaxCanOrderBeDeleted = async (id) => {
            const url = "{!! route('admin.pages.orders.vieworder.actionCommand') !!}";
            const payloads = {
                id,
                action: "ajaxCanOrderBeDeleted",
            }

            options.body = JSON.stringify(payloads);

            $("#btn-delete").attr({ "disabled": true }).text("Checking...");

            const response = await cbmsPost(url, options);

            $("#btn-delete").attr({ "disabled": false }).text("Delete Order");

            if (response) {    
                const { result, message, data } = response;

                if (result == 'error') {
                    Toast.fire({ icon: 'error', title: message, });
                    return false;
                }

                if (!data.candelete) {
                    Toast.fire({ icon: 'warning', title: "The order status must be in Cancelled or Fraud to be deleted", });
                    return false;
                }

                actionCommand("delete");
                
                return true;
            }

            console.log(`ajaxCanOrderBeDeleted: Failed to fetch data. Response: ${response}`);
        }

    </script>
@endsection
