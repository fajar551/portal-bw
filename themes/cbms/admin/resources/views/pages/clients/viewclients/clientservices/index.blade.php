@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Services</title>
@endsection

@section('styles')
    <!-- Date Picker -->
    <link href="{{ Theme::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="client-summary-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Client Profile</h4>
                                    </div>
                                </div>
                            </div>
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
                            
                            {{-- Row client select --}}
                            @include('includes.clientsearch')

                            {{-- Tab Nav --}}
                            @include('includes.tabnavclient')
                            <div class="row">
                                <div class="col-lg-12">
                                    @if (isset($invalidClientId))
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">Invalid User ID</h4>
                                                <hr>
                                                <p class="mb-0">Please <a href="{{ route('admin.pages.clients.viewclients.index') }}">Click here</a> to find correct Client ID </p>
                                            </div>
                                        </div>
                                    </div>
                                    @elseif (isset($invalidServiceId))
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">Invalid Product/Service ID</h4>
                                                <hr>
                                                <p>{{ __("admin.servicesnoproductsinfo") }} <a href="{{ route('admin.pages.orders.addneworder.index') }}">{{ __("admin.clickhere") }}</a> {{ __("admin.orderstoplacenew") }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @elseif (isset($invalidClientIdAndServiceId))
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">Invalid Client ID & Service ID</h4>
                                                <hr>
                                                <p>{{ __("admin.servicesnoproductsinfo") }} <a href="{{ route('admin.pages.orders.addneworder.index') }}">{{ __("admin.clickhere") }}</a> {{ __("admin.orderstoplacenew") }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if (isset($clientsdetails))
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <form action="{{ route('admin.pages.clients.viewclients.clientservices.filterService') }}" method="GET" enctype="multipart/form-data" onsubmit="return false;">
                                                    @csrf
                                                    <input type="number" name="userid" value="{{ $userid }}" hidden>
                                                    <div class="form-group row">
                                                        {{-- <label for="productselect" class="col-sm-3 col-form-label ">Domains: </label> --}}
                                                        <div class="col-sm-9">
                                                            <select name="productselect" onChange="submit()" class="select2-search-disable form-control" style="width: 100%;">
                                                                @foreach ($productselect as $k => $v)
                                                                @php
                                                                    $color = $colorData = "";
                                                                    list($color, $value) = $v;
                                                                @endphp
                                                                    <option value="{{ $k }}" @if ($itemToSelect == $k) selected @endif {{ $color ? "style=\"background-color:$color\"" : "" }} >{{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-lg-12 d-flex flex-row-reverse mb-2">
                                                        <a href="javascript:void(0)" type="button" id="btn-move" class="btn btn-sm btn-primary mr-2 align-items-center d-flex" onclick="window.open('{{ route('admin.pages.clients.viewclients.clientmove.index', ['type' => 'hosting', 'id' => $id]) }}','movewindow','width=800,height=400,top=100,left=100');return false">
                                                            <i class="fas fa-random mr-2"></i> Move Product/Service
                                                        </a>&nbsp;&nbsp;
                                                        <a href="javascript:void(0)" type="button" id="btn-updown" onclick="modalUpgrade();" class="btn btn-sm btn-primary mr-2 align-items-center d-flex">
                                                            <i class="fas fa-arrow-circle-up mr-2"></i>  Upgrade/Downgrade
                                                        </a>&nbsp;&nbsp;
                                                        <a href="javascript:void(0);" class="btn btn-sm btn-outline align-items-center d-flex">
                                                            {!! $sslStatusToggle !!}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3 border">
                                        <form action="{{ route('admin.pages.clients.viewclients.clientservices.update') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                                            @csrf
                                            <input type="number" name="userid" value="{{ $userid }}" hidden>
                                            <input type="number" name="id" value="{{ $id }}" hidden>
                                            <input type="number" name="aid" value="{{ $aid }}" hidden>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Order #</label>
                                                                <div class="col-sm-9">
                                                                    <label for="" class="col-form-label">{{ $orderid }} - <a href="{{ route('admin.pages.orders.vieworder.index', ['id' => $orderid]) }}">View Order</a></label>
                                                                </div>
                                                            </div>
                                                            {{-- <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Order Type</label>
                                                                <div class="col-sm-9">
                                                                    <label for="" class="col-form-label">{{ "N/A" }}</label>
                                                                </div>
                                                            </div> --}}
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Product/Service</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="packageid" id="productDropDown" style="width: 100%;">
                                                                        {!! $productList !!}
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Server</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="server" id="serverDropDown" style="width: 100%;">
                                                                        <option value="">None</option>
                                                                        @foreach ($serversarr as $k => $v)
                                                                            <option value="{{ $k }}" @if($k == $server) selected @endif>{{ $v }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">{{ $producttype == "server" ? "Hostname" : "Domain" }}</label>
                                                                <div class="col-sm-9">
                                                                    <div class="form-inline">
                                                                        <div class="input-group">
                                                                            <input type="text" name="domain" class="form-control" placeholder="{{ $producttype == "server" ? "Hostname" : "Domain" }}" value="{{ $domain }}" autocomplete="off">
                                                                            <div class="input-group-append">
                                                                            <div class="btn-group" role="group">
                                                                                <button id="btnGroupVerticalDrop1" type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                    <i class="mdi mdi-chevron-down"></i>
                                                                                </button>
                                                                                <div class="dropdown-menu" aria-labelledby="btnGroupVerticalDrop1" style="">
                                                                                    <a class="dropdown-item" href="http://www.{{ $domain ?? "" }}" target="_blank">www</a>
                                                                                    {{-- TODO: whois --}}
                                                                                    <a class="dropdown-item" href="whois.php/?domain={{ $domain ?? "" }}" target="_blank">whois</a>
                                                                                    <a class="dropdown-item" href="http://www.intodns.com/{{ $domain ?? "" }}" target="_blank">intoDNS</a>
                                                                                </div>
                                                                            </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Dedicated IP</label>
                                                                <div class="col-sm-9">
                                                                    <input type="text" name="dedicatedip" id="dedicatedip" class="form-control " id="inputDedicatedip" placeholder="Dedicated IP" value="{{ $dedicatedip }}" autocomplete="off">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                @php
                                                                    // TODO: Check this action should call in Custom action or in modeop for singlesignon action?  
                                                                    $usernameOutput = "<input type=\"text\" name=\"username\" id=\"username\" class=\"form-control\" id=\"inputUsername\" placeholder=\"Input Username\" value=\"$username\" autocomplete=\"off\">";
                                                                    
                                                                    if ($moduleInterface->functionExists("ServiceSingleSignOn")) {
                                                                        $btnLabel = $moduleInterface->getMetaDataValue("ServiceSingleSignOnLabel");
                                                                        $usernameOutput = "<div class=\"\">" . $usernameOutput;
                                                                        $usernameOutput .= sprintf(" <button type=\"button\" data-act=\"%s\" data-label=\"ServiceSingleSignOn\" onclick=\"modCommand('ServiceSingleSignOn', this);\" class=\"btn btn-sm btn-primary mt-2\">%s</button>", "singlesignon", $btnLabel ? $btnLabel : __("admin.ssoservicelogin"));
                                                                        $usernameOutput .= "</div>";
                                                                    } else if ($moduleInterface->functionExists("LoginLink")) {
                                                                        $usernameOutput .= " " . $moduleInterface->call("LoginLink");   
                                                                    }
                                                                @endphp
                                                                <label for="#" class="col-sm-3 col-form-label ">Username</label>
                                                                <div class="col-sm-9">
                                                                    {{-- <input type="text" name="username" id="username" class="form-control " id="inputUsername" placeholder="Input Username" value="" autocomplete="off"> --}}
                                                                    {!! $usernameOutput !!}
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Password</label>
                                                                <div class="col-sm-9">
                                                                    <input type="text" name="password" id="password" class="form-control " id="inputPassword" placeholder="Input Password" value="{{ $password }}" autocomplete="off">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Status</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="domainstatus" id="domainstatus" style="width: 100%;">
                                                                        {!! $domainstatusList !!}
                                                                    </select>
                                                                    {{ $statusExtra }}
                                                                </div>
                                                            </div>
                                                            @if ($producttype == "server")
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Assigned IPs</label>
                                                                <div class="col-sm-9">
                                                                    <textarea type="textarea" name="assignedips" class="form-control" id="assignedips" rows="3" placeholder="Assigned IPs">{{ $assignedips }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Nameserver 1</label>
                                                                <div class="col-sm-9">
                                                                    <input type="text" class="form-control" name="ns1" placeholder="Nameserver 1" value="{{ $ns1 }}" autocomplete="off" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Nameserver 2</label>
                                                                <div class="col-sm-9">
                                                                    <input type="text" class="form-control" name="ns2" placeholder="Nameserver 2" value="{{ $ns2 }}" autocomplete="off" />
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <div class="form-group row">
                                                                <label for="inputRegDate" class="col-sm-3 col-form-label ">Registration Date</label>
                                                                <div class="col-sm-9">
                                                                    <div class="input-daterange input-group " id="inputRegDate">
                                                                        <input type="text" class="form-control" name="regdate" placeholder="dd/mm/yyyy" value="{{ $regdate }}" autocomplete="off" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">First Payment Amount</label>
                                                                <div class="col-sm-9">
                                                                    <input type="number" min="0" step="0.01" name="firstpaymentamount" id="inputFirstpaymentamount" class="form-control " placeholder="First Payment Amount" value="{{ $firstpaymentamount }}" autocomplete="off">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label">Recurring Amount	</label>
                                                                <div class="col-sm-9">
                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <input type="number" name="amount" id="inputAmount" min="0" step="0.01" value="{{ $amount }}" placeholder="Recurring Amount" class="form-control ml-2" autocomplete="off">
                                                                        </div>
                                                                        <div class="col-sm-6">
                                                                            <div class="form-check">
                                                                                <input type="checkbox" name="autorecalcrecurringprice" class="form-check-input " id="autorecalcrecurringprice" value="1" @if ($autorecalcdefault) checked @endif>
                                                                                <label class="form-check-label" for="autorecalcrecurringprice">Auto Recalculate on Save</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="inputNextDueDate" class="col-sm-3 col-form-label">Next Due Date</label>
                                                                <div class="col-sm-9">
                                                                    @if (in_array($billingcycle, array("One Time", "Free Account")))
                                                                        <label for="" class="col-form-label">N/A</label>
                                                                    @else
                                                                        <input type="text" class="form-control" name="oldnextduedate" placeholder="dd/mm/yyyy" value="{{ $nextduedate }}" autocomplete="off" hidden />
                                                                        <div class="input-daterange input-group " id="inputNextDueDate">
                                                                            <input type="text" class="form-control" name="nextduedate" placeholder="dd/mm/yyyy" value="{{ $nextduedate }}" autocomplete="off" />
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="inputTerminationDate" class="col-sm-3 col-form-label ">Termination Date</label>
                                                                <div class="col-sm-9">
                                                                    <div class="input-daterange input-group " id="inputTerminationDate">
                                                                        <input type="text" class="form-control" name="termination_date" placeholder="dd/mm/yyyy" value="{{ strpos($terminationDate, "0000") === false ? $terminationDate : "" }}" autocomplete="off" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="inputBillingcycle" class="col-sm-3 col-form-label ">Billing Cycle</label>
                                                                <div class="col-sm-9">
                                                                    <select name="billingcycle" id="inputBillingcycle" class="form-control select-inline">
                                                                        {!! $billingcycleList !!}
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="paymentmethod" class="col-sm-3 col-form-label">Payment Method</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="paymentmethod" id="paymentmethod" style="width: 100%;">
                                                                        <option value="">None</option>
                                                                        @foreach ($paymentmethodlist as $paymentmethod)
                                                                            <option value="{{ $paymentmethod["gateway"] }}" @if($paymentmethod["gateway"] == $gateway) selected @endif>
                                                                                {{ $paymentmethod["value"] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="mt-2">
                                                                        <a href="{{ route('admin.pages.clients.viewclients.clientinvoices.index', ['userid' => $userid, 'serviceid' => $id]) }}" >View Invoices</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label  ">Promotion Code</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="promoid" id="promoid" style="width: 100%;">
                                                                        <option value="0">None</option>
                                                                        @foreach ($promoarr as $key => $value)
                                                                            <option value="{{ $key }}" @if ($key == $promoid) selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <label for="">(Change will not affect price) {{ $recurCountString  }}</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if (in_array($oldpackageid, [45, 46, 47]))
                                                    <div class="d-none text-right w-100">
                                                        <a class="btn btn-primary" href="/admin/run_cron_invoice?userid={{$userid}}"> Generate Invoice Prorate Now <i class="fa fa-info-circle" title="Invoice akan di generate tiap hari jam 2 malam. Klik button ini jika ingin generate sekarang"></i></a>
                                                    </div> 
                                                @else
                                                    
                                                @endif

                                                <div class="col-lg-12">
                                                    @php
                                                        if ($configoptions) {
                                                            foreach ($configoptions as $configoption) {
                                                                $optionid = $configoption["id"];
                                                                $optionhidden = $configoption["hidden"];
                                                                $optionname = $optionhidden ? $configoption["optionname"] . " <i>(" . __("admin.hidden") . ")</i>" : $configoption["optionname"];
                                                                $optiontype = $configoption["optiontype"];
                                                                $selectedvalue = $configoption["selectedvalue"];
                                                                $selectedqty = $configoption["selectedqty"];

                                                                if ($optiontype == "1") {
                                                                    $inputcode = "<select name=\"configoption[" . $optionid . "]\" class=\"select2-search-disable form-control\">";
                                                                    foreach ($configoption["options"] as $option) {
                                                                        $inputcode .= "<option value=\"" . $option["id"] . "\"";
                                                                        if ($option["hidden"]) {
                                                                            $inputcode .= " style='color:#ccc;'";
                                                                        }

                                                                        if ($selectedvalue == $option["id"]) {
                                                                            $inputcode .= " selected";
                                                                        }
                                                                        
                                                                        $inputcode .= ">" . $option["name"] . "</option>";
                                                                    }

                                                                    $inputcode .= "</select>";
                                                                } else if ($optiontype == "2") {
                                                                    $inputcode = "";
                                                                    foreach ($configoption["options"] as $key => $option) {
                                                                        $inputcode = '<div class="form-check form-check-inline mt-2">
                                                                            <input type="radio" name="configoption["' .$optionid .'"]" id="configoption' .$key .'" class="form-check-input" value="' .$option["id"] .'"' .($selectedvalue == $option["id"] ? "checked" : "") .'>
                                                                            <label class="form-check-label" for="configoption' .$key .'">' .($option["hidden"] ? '<span style="color:#ccc;">' .$option["name"] .'</span>' : $option["name"]) .'</label>
                                                                        </div>';

                                                                        // $inputcode .= "<label class=\"radio-inline\"><input type=\"radio\" name=\"configoption[" . $optionid . "]\" value=\"" . $option["id"] . "\"";
                                                                        // if ($selectedvalue == $option["id"]) {
                                                                        //     $inputcode .= " checked";
                                                                        // }
                                                                        
                                                                        // if ($option["hidden"]) {
                                                                        //     $inputcode .= "> <span style='color:#ccc;'>" . $option["name"] . "</span></label><br />";
                                                                        // } else {
                                                                        //     $inputcode .= "> " . $option["name"] . "</label><br />";
                                                                        // }
                                                                    }
                                                                } else if ($optiontype == "3") {
                                                                    $inputcode = "<div class=\"form-check mt-2\">
                                                                                    <input type=\"checkbox\" name=\"configoption[" . $optionid . "]\" class=\"form-check-input\" id=\"configoption{$optionid}\" value=\"1\" " .($selectedqty ? "checked" : "") .">
                                                                                    <label class=\"form-check-label\" for=\"configoption{$optionid}\">" .($configoption["options"][0]["name"]) ."</label>
                                                                                </div>";
                                                            
                                                                    // $inputcode = "<label class=\"checkbox-inline\"><input type=\"checkbox\" name=\"configoption[" . $optionid . "]\" value=\"1\"";
                                                                    // if ($selectedqty) {
                                                                    //     $inputcode .= " checked";
                                                                    // }
                                                                    // $inputcode .= "> " . $configoption["options"][0]["name"] . "</label>";
                                                                } else if ($optiontype == "4") {
                                                                    $inputcode = "<input type=\"text\" name=\"configoption[" . $optionid . "]\" value=\"" . $selectedqty . "\" class=\"form-control \"> x " . $configoption["options"][0]["name"];
                                                                }

                                                                echo '<div class="form-group row">
                                                                        <label for="#" class="col-sm-2 col-form-label">' .$optionname .'</label>
                                                                        <div class="col-sm-10">' .$inputcode .'</div>
                                                                    </div>';
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                    
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Module Commands</label>
                                                        <div class="col-sm-10">
                                                        @if ($module) 
                                                            @if ($moduleInterface->functionExists("CreateAccount"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Create');"> Create </button>
                                                            @endif
                                                            
                                                            @if ($moduleInterface->functionExists("Renew"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Renew');"> Renew </button>
                                                            @endif
                                                            
                                                            @if ($moduleInterface->functionExists("SuspendAccount"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Suspend');"> Suspend </button>
                                                            @endif

                                                            @if ($moduleInterface->functionExists("UnsuspendAccount"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Unsuspend');"> Unsuspend </button>
                                                            @endif

                                                            @if ($moduleInterface->functionExists("TerminateAccount"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Terminate');"> Terminate </button>
                                                            @endif

                                                            @if ($moduleInterface->functionExists("ChangePackage"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('ChangePackage');"> {{ $moduleInterface->getMetaDataValue("ChangePackageLabel") ?:  "Change Package" }} </button>
                                                            @endif

                                                            @if ($moduleInterface->functionExists("ChangePassword"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('ChangePassword');"> Change Password </button>
                                                            @endif
                                                            
                                                            @if ($moduleInterface->isApplicationLinkingEnabled() && $moduleInterface->isApplicationLinkSupported())
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('ManageAppLinks');"> Manage App Links </button>
                                                            @endif
                                                            
                                                            @if ($moduleInterface->functionExists("AdminCustomButtonArray"))
                                                            <br>
                                                            {!! implode(" ", $modulebtns) !!}
                                                            @endif

                                                            {{-- TODO: --}}

                                                            {{-- function buildCustomModuleButtons($modulebtns, $adminbuttonarray)
                                                            {
                                                                global $frm;
                                                                global $id;
                                                                global $userid;
                                                                global $aid;
                                                                
                                                                if ($adminbuttonarray) {
                                                                    foreach ($adminbuttonarray as $displayLabel => $options) {
                                                                        if (is_array($options)) {
                                                                            $href = isset($options["href"]) ? $options["href"] : "?userid=" . $userid . "&id=" . $id;
                                                                            if ($aid) {
                                                                                $href .= "&aid=" . $aid;
                                                                            }

                                                                            if (isset($options["customModuleAction"]) && $options["customModuleAction"]) {
                                                                                $href .= "&modop=custom&ac=" . $options["customModuleAction"] . "&token=" . generate_token("plain");
                                                                            }

                                                                            $submitLabel = isset($options["submitLabel"]) ? $options["submitLabel"] : "";
                                                                            $submitId = isset($options["submitId"]) ? $options["submitId"] : "";
                                                                            $modalClass = isset($options["modalClass"]) ? $options["modalClass"] : "";
                                                                            $modalSize = isset($options["modalSize"]) ? $options["modalSize"] : "";
                                                                            $disabled = isset($options["disabled"]) && $options["disabled"] ? " disabled=\"disabled\"" : "";
                                                                            if ($disabled && isset($options["disabledTooltip"]) && $options["disabledTooltip"]) {
                                                                                $disabled .= " data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $options["disabledTooltip"] . "\"";
                                                                            }

                                                                            if (isset($options["modal"]) && $options["modal"] === true) {
                                                                                $modulebtns[] = "<a href=\"" . $href . "\" class=\"btn btn-default open-modal\" data-modal-title=\"" . $options["modalTitle"] . "\" data-modal-size=\"" . $modalSize . "\" data-modal-class=\"" . $modalClass . "\"" . $disabled . ($submitLabel ? " data-btn-submit-label=\"" . $submitLabel . "\" data-btn-submit-id=\"" . $submitId . "\"" : "") . ">" . $displayLabel . "</a>";
                                                                            } else {
                                                                                $modulebtns[] = "<a href=\"" . $href . "\" class=\"btn btn-default" . $options["class"] . "\">" . $displayLabel . "</a>";
                                                                            }
                                                                        } else {
                                                                            $modulebtns[] = $frm->button($displayLabel, "runModuleCommand('custom','" . $options . "')");
                                                                        }
                                                                    }
                                                                }

                                                                return $modulebtns;
                                                            } --}}
                                                        @endif
                                                        </div>
                                                    </div>

                                                    @if ($module) 
                                                        @php
                                                            if ($moduleInterface->functionExists("AdminServicesTabFields")) {
                                                                if ($adminServicesTabFieldsSaveErrors = session()->get("adminServicesTabFieldsSaveErrors")) {
                                                                    session()->forget("adminServicesTabFieldsSaveErrors");
                                                                    echo '<div class="form-group row">
                                                                            <label for="#" class="col-sm-2 col-form-label">' . __("admin.error") .'</label>
                                                                            <div class="col-sm-10">' .$adminServicesTabFieldsSaveErrors .'</div>
                                                                        </div>'; 
                                                                }
                                                
                                                                $fieldsArray = $moduleInterface->call("AdminServicesTabFields", $moduleParams);
                                                                if ($fieldsArray && is_array($fieldsArray)) {
                                                                    foreach ($fieldsArray as $fieldName => $fieldValue) {
                                                                        echo '<div class="form-group row">
                                                                            <label for="#" class="col-sm-2 col-form-label">' .$fieldName .'</label>
                                                                            <div class="col-sm-10">' .$fieldValue .'</div>
                                                                        </div>'; 
                                                                    }
                                                                }
                                                            }

                                                            // TODO:
                                                            // if (WHMCS\UsageBilling\MetricUsageSettings::isCollectionEnable() && $serviceModel->getMetricProvider()) {
                                                            //     $helper = new WHMCS\UsageBilling\Service\ViewHelper();
                                                            //     $table = $helper->serverTenantUsageTable($serviceModel->metrics());
                                                            //     $html = "<div id=\"containerStats\">\n    " . $table . "\n</div>\n<div class=\"text-right\">\n    <button type=\"button\" id=\"btnRefreshStats\" class=\"btn btn-xs btn-default\">\n        <i class=\"fas fa-sync\"></i>\n        Refresh Now\n    </button>\n</div>";
                                                            //     $tbl->add("Metric Statistics", $html, 1);
                                                            //     $jQueryCode .= "\njQuery('#btnRefreshStats').on('click', function (e) {\n   e.preventDefault();\n   var \$btnTarget = \$(this);\n   \$btnTarget.find('i').addClass('fa-spin');\n   WHMCS.http.jqClient.jsonPost({\n        url: \"clientsservices.php\",\n        data: {\n            action: 'refreshStats',\n            userid: '" . $userid . "',\n            id: '" . $id . "'\n        },\n        success: function(data) {\n            if (data.success) {\n                jQuery(\"#containerStats\").html(data.body);\n            }\n        },\n        always: function() {\n            \$btnTarget.find('i').removeClass('fa-spin');\n        }\n    });\n});";
                                                            // }
                                                        @endphp
                                                    @endif
                                                    
                                                    @php
                                                        $hookret = \App\Helpers\Hooks::run_hook("AdminClientServicesTabFields", ["id" => $id]);
                                                        foreach ($hookret as $hookdat) {
                                                            foreach ($hookdat as $k => $v) {
                                                                // $tbl->add($k, $v, 1);
                                                                echo '<div class="form-group row">
                                                                            <label for="#" class="col-sm-2 col-form-label">' .$k .'</label>
                                                                            <div class="col-sm-10">' .$v .'</div>
                                                                        </div>'; 
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                    {{-- TODO: This field maybe from hookret above  
                                                        <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Next Invoice Date</label>
                                                        <div class="col-sm-10">
                                                            <label for="" class="col-form-label">{{ "N/A" }} </label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">No Invoice Unpaid</label>
                                                        <div class="col-sm-10">
                                                            <label for="" class="col-form-label">{{ "N/A" }} </label>
                                                        </div>
                                                    </div> --}}
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Addons</label>
                                                        <div class="col-sm-10">
                                                            <table id="dtDomainReminders" class="table table-bordered">
                                                                <thead>
                                                                    <tr class="text-white table-head-primary-color">
                                                                        <th scope="col">Reg Date</th>
                                                                        <th scope="col">Name</th>
                                                                        <th scope="col">Pricing</th>
                                                                        <th scope="col">Status</th>
                                                                        <th scope="col">Next Due Date</th>
                                                                        <th scope="col">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse ($addons as $vals)
                                                                        <tr>
                                                                            <td class="text-center">{{ $vals["regdate"] }}</td>
                                                                            <td class="text-center">{{ $vals["name"] }}</td>
                                                                            <td class="text-center">{{ $vals["pricing"] }}</td>
                                                                            <td class="text-center">{{ $vals["status"] }}</td>
                                                                            <td class="text-center">{{ $vals["nextduedate"] }}</td>
                                                                            <td class="text-center">
                                                                                <a href="{{ route('admin.pages.clients.viewclients.clientservices.editAddon', ['userid' => $userid, 'id' => $id, 'aid' => $vals["id"]]) }}" type="button" class="btn btn-xs text-primary p-1 act-edit" title="Edit"><i class="fa fa-edit"></i></a>
                                                                                <button type="button" class="btn btn-xs text-danger p-1 act-delete" onclick="modCommand('DeleteAddons', this);" data-serviceid="{{ $id }}" data-aid="{{ $vals["id"] }}" title="Delete"><i class="fa fa-trash"></i></button>
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="6"><p>No record found</p></td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                            <a href="{{ route('admin.pages.clients.viewclients.clientservices.createAddon', ['userid' => $userid, 'id' => $id]) }}" class="col-form-label">
                                                                <i class="fas fa-plus mr-1"></i> {{ "Add New Addon" }} 
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @foreach ($customfields as $customfield)
                                                        <div class="form-group row">
                                                            <label for="#" class="col-sm-2 col-form-label">{!! $customfield["name"] !!}</label>
                                                            <div class="col-sm-10">
                                                                {!! $customfield["input"] !!}
                                                                @if (in_array($customfield["type"], ['tickbox']))
                                                                    <br>
                                                                @endif
                                                                <small>{{ $customfield["description"] }}</small>
                                                            </div> 
                                                        </div>
                                                    @endforeach
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Subscription ID</label>
                                                        <div class="col-sm-10">
                                                            <input type="number" min="0" name="subscriptionid" id="inputSubscriptionid" value="{{ $subscriptionid }}" class="form-control " placeholder="Subscription ID" autocomplete="off">
                                                            {!! $cancelSubscription !!}
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Override Auto-Suspend</label>
                                                        <div class="col-sm-3">
                                                            <div class="form-check mt-2">
                                                                <input type="checkbox" name="overideautosuspend" class="form-check-input " id="overideautosuspend" value="1" @if ($overideautosuspend) checked @endif>
                                                                <label class="form-check-label" for="overideautosuspend"> Do not suspend until</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-7">
                                                            <div class="input-daterange input-group " id="inputOveridesuspenduntil">
                                                                <input type="text" name="overidesuspenduntil" id="overidesuspenduntil" value="{{ $suspendValue }}" class="form-control " placeholder="Override Auto-Suspend" autocomplete="off">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Auto-Terminate End of Cycle</label>
                                                        <div class="col-sm-3">
                                                            <div class="form-check mt-2">
                                                                <input type="checkbox" name="autoterminateendcycle" class="form-check-input " id="autoterminateendcycle" value="1" @if ($autoterminateendcycle) checked @endif>
                                                                <label class="form-check-label" for="autoterminateendcycle">Reason</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-7">
                                                            <input type="text" name="autoterminatereason" id="inputAutoterminatereason" value="{{ $autoterminatereason }}" class="form-control " placeholder="Auto-Terminate End of Cycle" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Admin Notes</label>
                                                        <div class="col-sm-10">
                                                            <textarea name="notes" class="form-control" id="notes" rows="4" placeholder="Add admin notes">{{ $notes }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <button type="submit" class="btn btn-success px-3 mr-2">Save Changes</button>
                                                            <button type="reset" class="btn btn-secondary">Cancel Changes</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <button type="button" class="btn btn-outline-danger px-3 mr-2" onclick="modCommand('Delete');">Delete</button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (isset($clientsdetails))   
    <div class="modal fade bd-example-modal-lg" id="upgrade-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="#" method="POST" enctype="multipart/form-data" id="form-upgrade" onsubmit="modalUpgrade('order'); return false;">
                @csrf
                <input type="number" name="userid" value="{{ $userid }}" hidden>
                <input type="number" name="id" value="{{ $id }}" hidden>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Upgrade/Downgrade</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modalAjaxBody">

                        {{-- <p>Related Product/Service: Value Hybrid Performance - VHP 1 cPanel (cobaccdulu.my.id)</p>
                        <div class="form-group row">
                            <label class="col-sm-12 col-lg-4 col-form-label">Send Mail</label>
                            <div class="col-sm-12 col-lg-8">
                                <div class="d-flex align-items-center py-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="noemails" id="noemails1" value="false" required>
                                        <label class="form-check-label" for="noemails1">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="noemails" id="noemails2" value="true" required>
                                        <label class="form-check-label" for="noemails2">No</label>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                    </div>
                    <div class="modal-footer">
                        <img class="ml-2" src="{{ Theme::asset('img/loading.gif') }}" id="moduleSettingsLoader" alt="loading" hidden>
                        <button type="submit" class="btn btn-primary" id="btn-create-order">Create Order</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    @stack('clientsearch')
    
    @if (isset($clientsdetails))
    <script>
        $(() => {
            $('#inputRegDate').datepicker(dateRangeOption);
            $('#inputTerminationDate').datepicker(dateRangeOption);
            $('#inputNextDueDate').datepicker(dateRangeOption);
            $('#inputOveridesuspenduntil').datepicker(dateRangeOption);

            $('.ssl-state.ssl-sync').each(function () {
                let userid = $(this).attr('data-user-id');
                let domain = $(this).attr('data-domain');

                sslCheck({ userid, domain, details: false });
            });
        });
  
        const modCommand = (action, element = null) => {
            let url = "{!! route('admin.pages.clients.viewclients.clientservices.modCommand') !!}";
            let title = "";
            let message = "";
            let additionalOptions = {};
            let payloads = {
                userid: "{{ $userid }}",
                id: "{{ $id }}",
            };

            @if ($addonModule)
                payloads.aid = "{{ $aid }}";
            @endif

            switch (action) {
                @if ($module || $addonModule)
                case "Create":
                    payloads.modop = "create";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.servicescreatesure') }}";
                    break;
                case "Renew":
                    payloads.modop = "renew";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.servicesrenewSure') }}";
                    break;
                case "Suspend":
                    payloads.modop = "suspend";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = `
                        <label for="#" class="col-sm-12 col-form-label">{{ __('admin.servicessuspendsure') }}</label>
                        <div class="form-group row">
                            <label for="#" class="col-sm-4 col-form-label text-left">{{ __('admin.servicessuspensionreason') }}</label>
                            <div class="col-sm-8 text-left">
                                <input type="text" name="suspreason" id="suspreason" class="swal2-input form-control" placeholder="Input Suspension Reason (If Any)">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="#" class="col-sm-4 col-form-label "></label>
                            <div class="col-sm-8 text-left">
                                <div class="form-check">
                                    <input type="checkbox" name="suspemail" id="suspemail" class="form-check-input" value="1">
                                    <label class="form-check-label" for="suspemail">{{ __('admin.servicessuspendsendemail') }}</label>
                                </div>
                            </div>
                        </div>`;
                    break;
                case "Unsuspend":
                    payloads.modop = "unsuspend";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = `
                        <label for="#" class="col-sm-12 col-form-label">{{ __('admin.servicesunsuspendsure') }}</label>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" name="unsuspended_email" id="unsuspended_email" class="form-check-input" value="true">
                                    <label class="form-check-label" for="unsuspended_email">{{ __('admin.automationsendAutoUnsuspendEmail') }}</label>
                                </div>
                            </div>
                        </div>`;
                    break;
                case "Terminate":
                    payloads.modop = "terminate";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = `<label for="#" class="col-sm-12 col-form-label">{{ __('admin.servicesterminatesure') }}</label>`;

                    @if ($moduleInterface)
                        @if ($moduleInterface->getLoadedModule() == "Cpanel")
                            let keep = `{{ __('admin.serviceskeepDnsZone') }} (<a href="https://docs.whmcs.com/CPanel/WHM#Keep_DNS_Zone_on_Termination" class="autoLinked" target="_blank">{{ __('admin.learnMore') }}</a>)`;
                            message += `
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input type="checkbox" name="keep_zone" id="inputKeepCPanelDnsZone" class="form-check-input" value="true">
                                            <label class="form-check-label" for="inputKeepCPanelDnsZone">${keep}</label>
                                        </div>
                                    </div>
                                </div>`;
                        @endif
                    @endif
                    break;
                case "ChangePackage":
                    payloads.modop = "changepackage";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.serviceschgpacksure') }}";
                    break;
                case "ChangePassword":
                    payloads.modop = "changepw";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.serviceschangepwsure') }}";
                    break;
                case "ManageAppLinks":
                    // SKIP and Noted!
                    payloads.modop = "manageapplinks";
                    title = "{{ __('admin.domainsresendNotification') }}";
                    message = "{{ __('admin.domainsresendNotificationQuestion') }}";
                    break;
                case "Custom":
                    payloads.modop = "custom";
                    payloads.ac = $(element).attr('data-act');
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.servicescustomsure') }}".replace(":act", $(element).attr('data-label'));
                    break;
                case "ServiceSingleSignOn":
                    payloads.modop = "singlesignon";
                    payloads.server = $("#serverDropDown").val();
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.servicescustomsure') }}".replace(":act", $(element).attr('data-label'));
                    break;
                @endif
                case "SubscriptionInfo":
                    payloads.modop = "cancelsubscription";
                    title = "{{ __('admin.servicescancelSubscription') }}";
                    message = "{{ __('admin.servicescancelSubscriptionSure') }}";
                    break;
                case "CancelSubscription":
                    payloads.modop = "cancelsubscription";
                    title = "{{ __('admin.servicescancelSubscription') }}";
                    message = "{{ __('admin.servicescancelSubscriptionSure') }}";
                    break;
                case "Delete":
                    payloads.modop = "delete";
                    title = "{{ __('admin.servicesdeleteproduct') }}";
                    message = "{{ __('admin.servicesproddeletesure') }}";
                    break;
                case "DeleteAddons":
                    payloads.modop = "deleteaddons";
                    payloads.aid = $(element).attr('data-aid');
                    title = "{{ __('admin.servicesdeleteproduct') }}";
                    message = "{{ __('admin.addonsareYouSureDelete') }}";
                    break;
                default:
                    Toast.fire({icon: 'warning', title: 'N/A!',});
                    return;
            }

            Swal.fire({
                ...additionalOptions,
                title: title,
                html: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText:  "OK",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async (data) => { 
                    if (action == "Suspend") {
                        payloads.suspreason = $('#suspreason').val();
                        payloads.suspemail = $("#suspemail").is(':checked') ? true : false;
                    }

                    if (action == "Unsuspend") {
                        payloads.unsuspended_email = $("#unsuspended_email").is(':checked') ? true : false;
                    }

                    if (action == "Terminate") {
                        payloads.keep_zone = $("#inputKeepCPanelDnsZone").is(':checked') ? true : false;
                    }

                    options.method = "POST";
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
                    const { result, message, modresult = null } = response.value;

                    Toast.fire({ icon: result, title: message, });
                    if (result == 'error') {
                        return false;
                    }

                    if (action == 'ServiceSingleSignOn') {
                        if (modresult.redirectTo) {
                            setTimeout(function(){
                                window.location.href = modresult.redirectTo;
                            }, 3000);

                            return true;
                        }
                    }

                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                    
                    /*
                    if (action == 'Delete') {
                        setTimeout(function(){
                            window.location.href = "{!! route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $userid]) !!}"
                        } , 2000);   
                    }

                    if (action == 'DeleteAddons') {
                        setTimeout(function(){
                            window.location.href = "{!! route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $userid, 'id' => $id]) !!}"
                        } , 2000);   
                    }
                    */
                }
            }).catch(swal.noop);
        }            

        const sslCheck = async (payloads = {}) => {
            const url = "{!! route('admin.pages.clients.viewclients.clientdomain.sslCheckAdminArea') !!}";
            options.method = "POST";
            options.body = JSON.stringify(payloads);

            const response = await cbmsPost(url, options);
            if (response) {    
                const { result, message, data } = response;

                if (result == 'error') {
                    // Toast.fire({ icon: 'error', title: message, });
                    console.log(message);
                    return false;
                }

                $('.ssl-state.ssl-sync').each(function () {
                    let self = $(this);
                    self.replaceWith(`<img src="${data.image}" data-toggle="tooltip" title="${data.tooltip}" class="${data.class}" style="width:25px;">`);
                    $('[data-toggle="tooltip"]').tooltip();
                });

                return true;
            }

            console.log("Failed to fetch data. Response: " +response);
            return false;
        }

        const modalUpgrade = async (action = "show") => {
            let payloads = { 
                action: false, 
                id: "{{ $id }}",
                promoid: $("#promocode").val(),
            };

            if(action == "show") {
                $('#upgrade-modal').modal({ show: true, backdrop: 'static' });
            }

            if(action == "show-productform") {
                payloads.type = "product";
            }

            if(action == "show-configoptionsform") {
                payloads.type = "configoptions";
            }

            if(action == "getcycles") {
                payloads.action = "getcycles";
                payloads.pid = $("#newpid option:selected").val();
            }

            if(action == "calcsummary") {
                payloads.action = "calcsummary";
                payloads.formdata = $('#form-upgrade').serializeJSON();
            }

            if(action == "order") {
                payloads.action = "order";
                payloads.formdata = $('#form-upgrade').serializeJSON();
            }

            const url = "{!! route('admin.pages.clients.viewclients.clientservices.clientUpgrade') !!}";
            options.method = "POST";
            options.body = JSON.stringify(payloads);

            // $("#modalAjaxBody").html(""); 
            $("#moduleSettingsLoader").removeAttr("hidden");
            $("#btn-create-order").attr({ "disabled": true }).text("Loading...");
            $("#div-ordersresult").html("");

            const response = await cbmsPost(url, options);
            
            $("#moduleSettingsLoader").attr({ "hidden": true});
            $("#btn-create-order").attr({ "disabled": false }).text("Create Order");
            if (response) {    
                const { result, message, data } = response;

                $(`#${data.element}`).html(data.body);
                if(action == "getcycles") {
                    calctotals();
                }
    
                return true;
            }

            console.log("Failed to fetch data. Response: " +response);

        }

        const calctotals = async () => {
            modalUpgrade('calcsummary');
        }

    </script>
    @endif
@endsection
