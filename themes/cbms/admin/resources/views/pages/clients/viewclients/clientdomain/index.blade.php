@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Domain</title>
@endsection

@section('styles')
    <!-- Date Picker -->
    <link href="{{ Theme::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')
    <style>
    #domainList {
        max-height: 300px;
        overflow-y: auto;
    }
    .custom-modal-width {
        max-width: 60% !important; /* Atur lebar sesuai kebutuhan */
    }
    </style>
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

                            @if (isset($invalidClientId))
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">No Data Found!</h4>
                                                <hr>
                                                <p class="mb-0">Invalid client ID. Please access this page with correct ID</p>
                                                {{-- <p>No domains found for this user. <a href="{{ url('admin/orders/add') }}">Click here</a> to place a new order..</p> --}}
                                                {{-- <p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if (isset($invalidDomainId))
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">No Data Found!</h4>
                                                <hr>
                                                <p>No domains found for this user. <a href="{{ route('admin.pages.orders.addneworder.index', ['userid' => $userid ?? '']) }}">Click here</a> to place a new order..</p>
                                                <p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if (isset($clientsdetails))
                            <!--{{ auth()->user()->id }}-->

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <form action="{{ route('admin.pages.clients.viewclients.clientdomain.filterDomain') }}" method="GET" enctype="multipart/form-data" onsubmit="return false;">
                                                    @csrf
                                                    <input type="number" name="userid" value="{{ $clientsdetails["userid"] }}" hidden>
                                                    <div class="form-group row">
                                                        <label for="domainid" class="col-sm-3 col-form-label ">Domains: </label>
                                                        <div class="col-sm-9">
                                                            <select name="domainid" onChange="submit()" class="select2-search-disable form-control" style="width: 100%;">
                                                                @foreach ($domainList as $data)
                                                                    <option value="{{ $data->id }}" @if ($id == $data->id) selected @endif 
                                                                    @if ($data->status == "Pending")
                                                                       style="background-color:#ffffcc;"
                                                                    @elseif (in_array($data->status, ["Expired", "Cancelled", "Fraud", "Transferred Away"]))
                                                                        style="background-color:#ff9999;"
                                                                    @endif
                                                                    >{{ $data->domain }}</option>
                                                                @endforeach

                                                                {{-- <option value="131839" @if ($id == "131839") selected @endif>ujiproduk.com</option>
                                                                <option value="131840" @if ($id == "131840") selected @endif>TutoriaLvFx.com</option>
                                                                <option value="131841" @if ($id == "131841") selected @endif>todayis.live</option>
                                                                <option value="131842" @if ($id == "131842") selected @endif>Sepatudaebak.com</option> --}}
                                                            </select>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-lg-12 d-flex flex-row-reverse mb-2">
                                                        <a href="javascript:void(0)" type="button" id="btn-search" class="btn btn-sm btn-primary mr-2 align-items-center d-flex" onclick="window.open('{{ route('admin.pages.clients.viewclients.clientmove.index', ['type' => 'domain', 'id' => $id]) }}','movewindow','width=800,height=400,top=100,left=100');return false">
                                                            <i class="fas fa-random mr-2"></i> Move Product/Service
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
                                        <form method="post" action="{{ route("admin.pages.clients.domainregistrations.whois") }}" target="_blank" id="frmWhois">
                                            @csrf
                                            <input type="hidden" name="domain" value="{{ $domain }}" />
                                        </form>
                                        <form action="{{ route('admin.pages.clients.viewclients.clientdomain.savedomain') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                                            @csrf
                                            <input type="number" name="userid" value="{{ $userid }}" hidden>
                                            <input type="number" name="domainid" value="{{ $id }}" hidden>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Order #</label>
                                                        <div class="col-sm-9">
                                                            <label for="" class="col-form-label">{{ $orderid }} - <a href="{{ route('admin.pages.orders.vieworder.index', ['id' => $id]) }}">View Order</a></label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Order Type</label>
                                                        <div class="col-sm-9">
                                                            <label for="" class="col-form-label">{{ $ordertype }}</label>
                                                            @if ($isPremium)
                                                            <span class="badge badge-danger">{{__("admin.domainspremiumDomain")}}</span>    
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Domain</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-inline">
                                                                <div class="input-group">
                                                                    <input type="text" name="domain" class="form-control" placeholder="Domain Name" value="{{ $domain }}" autocomplete="off">
                                                                    <div class="input-group-append">
                                                                    <div class="btn-group" role="group">
                                                                        <button id="btnGroupVerticalDrop1" type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                            <i class="mdi mdi-chevron-down"></i>
                                                                        </button>
                                                                        <div class="dropdown-menu" aria-labelledby="btnGroupVerticalDrop1" style="">
                                                                            <a class="dropdown-item" href="http://www.{{ $domain }}" target="_blank">www</a>
                                                                            <a class="dropdown-item" onclick="$('#frmWhois').submit(); return false;">whois</a>
                                                                            <a class="dropdown-item" href="http://www.intodns.com/{{ $domain }}" target="_blank">intoDNS</a>
                                                                        </div>
                                                                    </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="invstatus" class="col-sm-3 col-form-label  ">Registrar</label>
                                                        <div class="col-sm-9">
                                                            <select class="select2-search-disable form-control" name="registrar" id="registrarsDropDown" style="width: 100%;">
                                                                <option value="">None</option>
                                                                @foreach ($registrars as $reg)
                                                                    <option value="{{ $reg->getLowerName() }}" @if (strtolower($reg->getLowerName()) == strtolower($current_registrar)) selected @endif>{{ ucwords($reg->getLowerName()) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">First Payment Amount</label>
                                                        <div class="col-sm-9">
                                                            <input type="number" min="0" step="0.01" name="firstpaymentamount" id="firstpaymentamount" class="form-control " placeholder="First Payment Amount" value="{{ $firstpaymentamount }}" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label">Recurring Amount	</label>
                                                        <div class="col-sm-9">
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <input type="number" name="recurringamount" id="recurringamount" min="0" step="0.01" value="{{ $recurringamount }}" placeholder="Recurring Amount" class="form-control ml-2" autocomplete="off">
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <div class="form-check">
                                                                        <input type="checkbox" name="autorecalc" class="form-check-input " id="autorecalc" value="1">
                                                                        <label class="form-check-label" for="autorecalc">Auto Recalculate on Save</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label  ">Promotion Code</label>
                                                        <div class="col-sm-9">
                                                            <select class="select2-search-disable form-control" name="promoid" id="promoid" style="width: 100%;">
                                                                <option value="0">None</option>
                                                                @php
                                                                    foreach ($promotionList as $key => $data) {
                                                                        $promo_id = $data["id"];
                                                                        $promo_code = $data["code"];
                                                                        $promo_type = $data["type"];
                                                                        $promo_recurring = $data["recurring"];
                                                                        $promo_value = $data["value"];

                                                                        if ($promo_type == "Percentage") {
                                                                            $promo_value .= "%";
                                                                        } else {
                                                                            $promo_value = \App\Helpers\Format::formatCurrency($promo_value);
                                                                        }

                                                                        if ($promo_type == "Free Setup") {
                                                                            $promo_value = __("admin.promosfreesetup");
                                                                        }
                                                                        
                                                                        $promo_recurring = $promo_recurring ? __("admin.statusrecurring") : __("admin.statusonetime");
                                                                        if ($promo_type == "Price Override") {
                                                                            $promo_recurring = __("admin.promospriceoverride");
                                                                        }

                                                                        if ($promo_type == "Free Setup") {
                                                                            $promo_recurring = "";
                                                                        }

                                                                        echo "<option value=\"" . $promo_id . "\"";
                                                                        if ($promo_id == $promoid) {
                                                                            echo " selected";
                                                                        }
                                                                        echo ">" ."$promo_code - $promo_value $promo_recurring" ."</option>";
                                                                    }    
                                                                @endphp

                                                                @php
                                                                    // TODO:
                                                                    // $subscriptionData = "";
                                                                    // if ($subscriptionid) {
                                                                    //     $gateway = new WHMCS\Module\Gateway();
                                                                    //     $gateway->load($paymentmethod);
                                                                    //     $manageSubButtons = array();
                                                                    //     if ($gateway->functionExists("get_subscription_info")) {
                                                                    //         $route = routePathWithQuery("admin-domains-subscription-info", array($id), array("token" => generate_token("plain")));
                                                                    //         $title = AdminLang::trans("subscription.info");
                                                                    //         $manageSubButtons[] = "<a href=\"" . $route . "\" class=\"btn btn-default open-modal\" " . "data-modal-title=\"" . $title . "\">" . AdminLang::trans("global.getSubscriptionInfo") . "</a>";
                                                                    //     }

                                                                    //     if ($gateway->functionExists("cancelSubscription")) {
                                                                    //         $manageSubButtons[] = "<button type=\"button\" class=\"btn btn-default\"" . " onclick=\"jQuery('#modalCancelSubscription').modal('show');\"" . " id=\"btnCancel_Subscription\" style=\"margin-left:-3px;\">" . AdminLang::trans("services.cancelSubscription") . "</button>";
                                                                    //     }

                                                                    //     if (0 < count($manageSubButtons)) {
                                                                    //         $buttons = implode("", $manageSubButtons);
                                                                    //         $subscriptionData = "<span class=\"input-group-btn\" style=\"display:block;\">" . $buttons . "</span>";
                                                                    //     }
                                                                    // }

                                                                    // $subscriptionClass = "input-300";
                                                                    // if ($subscriptionData) {
                                                                    //     $subscriptionClass = "input-group";
                                                                    // }
                                                                @endphp
                                                            </select>
                                                            <label for="">(Change will not affect price)</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Subscription ID</label>
                                                        <div class="col-sm-9">
                                                            <input type="number" min="0" name="subscriptionid" id="subscriptionid" value="{{ $subscriptionid }}" class="form-control " placeholder="Subscription ID" autocomplete="off">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Registration Period</label>
                                                        <div class="col-sm-6">
                                                            <input type="number" min="0" step="1" name="regperiod" class="form-control " id="regperiod" value="{{ $registrationperiod }}">
                                                            {{-- TODO --}}
                                                            {{-- if ($isPremium) {
                                                                $extraData = WHMCS\Domain\Extra::whereDomainId($domain_data["id"])->pluck("value", "name");
                                                                $renewalCost = convertCurrency($extraData["registrarRenewalCostPrice"], $extraData["registrarCurrency"], $currency["id"]);
                                                                $premiumLabel = " <span class=\"label label-danger\">" . AdminLang::trans("domains.premiumDomain") . "</span>";
                                                                $registrationPeriodInput = "<div data-toggle=\"tooltip\" data-placement=\"left\" data-trigger=\"hover\" title=\"" . AdminLang::trans("domains.periodPremiumDomains") . "\">" . $registrationPeriodInput . " disabled\" disabled=\"disabled" . $registrationPeriodInputEnd . "</div>";
                                                                $renewalCostInfo = "<span class=\"badge\">" . AdminLang::trans("domains.premiumRenewalCost") . ": " . formatCurrency((double) $renewalCost, true)->toPrefixed() . "</span>";
                                                            } else {
                                                                $registrationPeriodInput .= $registrationPeriodInputEnd;
                                                            } --}}
                                                        </div>
                                                        
                                                        <label for="#" class="col-sm-3 col-form-label">Years</label>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="inputRegDate" class="col-sm-3 col-form-label ">Registration Date</label>
                                                        <div class="col-sm-9">
                                                            <div class="input-daterange input-group " id="inputRegDate">
                                                                <input type="text" class="form-control" name="regdate" placeholder="dd/mm/yyyy" value="{{ $regdate }}" autocomplete="off" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="inputExpiryDate" class="col-sm-3 col-form-label ">Expiry Date</label>
                                                        <div class="col-sm-9">
                                                            <div class="input-daterange input-group " id="inputExpiryDate">
                                                                <input type="text" class="form-control" name="expirydate" placeholder="dd/mm/yyyy" value="{{ $expirydate }}" autocomplete="off" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="inputNextDueDate" class="col-sm-3 col-form-label">Next Due Date</label>
                                                        <div class="col-sm-9">
                                                            <div class="input-daterange input-group " id="inputNextDueDate">
                                                                <input type="text" class="form-control" name="nextduedate" placeholder="dd/mm/yyyy" value="{{ $nextduedate }}" autocomplete="off" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="inputNextInvoiceDate" class="col-sm-3 col-form-label">Next Invoice Date</label>
                                                        <div class="col-sm-9">
                                                            <div class="input-daterange input-group " id="inputNextInvoiceDate">
                                                                <input type="text" class="form-control" name="nextinvoicedate" placeholder="dd/mm/yyyy" value="{{ $nextinvoicedate }}" autocomplete="off" />
                                                            </div>
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
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="domainstatus" class="col-sm-3 col-form-label">Status</label>
                                                        <div class="col-sm-9">
                                                            <select class="select2-search-disable form-control" name="domainstatus" id="domainstatus" style="width: 100%;">
                                                                {!! $statuses !!}
                                                                {{-- <option value="Pending">Pending</option>
                                                                <option value="Pending Registration">Pending Registration</option>
                                                                <option value="Pending Transfer">Pending Transfer</option>
                                                                <option value="Active" selected="selected">Active</option>
                                                                <option value="Grace">Grace Period (Expired)</option>
                                                                <option value="Redemption">Redemption Period (Expired)</option>
                                                                <option value="Expired">Expired</option>
                                                                <option value="Transferred Away">Transferred Away</option>
                                                                <option value="Cancelled">Cancelled</option>
                                                                <option value="Fraud">Fraud</option> --}}
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    @if ($domainregistraractions) 
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Registrar Commands</label>
                                                        <div class="col-sm-10">
                                                            @if ($domainsHelper->hasFunction("RegisterDomain"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onClick="window.location='{{ route('admin.pages.clients.viewclients.clientdomain.register', ['userid' => $userid, 'domainid' => $id, 'action' => 'register']) }}'"> Register </button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("RegisterOpenProvider"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" id="registerOpenProviderBtn"> Register OpenProvider </button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("RenewDomainOpenProvider"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" id="renewDomainBtn">Renew Domain</button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("TransferDomain"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onClick="window.location='{{ route('admin.pages.clients.viewclients.clientdomain.register', ['userid' => $userid, 'domainid' => $id, 'action' => 'transfer']) }}'"> Transfer </button>
                                                            @endif
                                                            
                                                            @if ($domainsHelper->hasFunction("RenewDomain"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" onclick="regCommand('Renew');"> Renew </button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("TransferDomainOpenProvider"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" id="transferDomainBtn">Transfer Domain</button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("ModifyNameServer"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" id="modifyNameServerBtn">Modify Name Server</button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("LockDomainOpenprovider"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="lockDomain('{{ $sld }}', '{{ $tld }}')">Lock Domain</button>                                                         
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("UnlockDomainOpenprovider"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onclick="unlockDomain('{{ $sld }}', '{{ $tld }}')">Unlock Domain</button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("getDomainList"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" data-target="#domainListModal">Lihat Daftar Domain</button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("getContactList"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" data-target="#contactListModal">Lihat Daftar Kontak</button>
                                                            @endif
                                                           
                                                            

                                                            @if ($domainsHelper->hasFunction("GetContactDetails"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" onClick="window.location='{{ route('admin.pages.clients.viewclients.clientdomain.clientdomaincontact', ['userid' => $userid, 'domainid' => $id]) }}'"> Modify Contact Details </button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("GetDomainDetails"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" data-target="#getDomainDetail" onclick="getDomainDetail();"> Modify Nameservers</button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("GetRegistrarLock"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" onclick="regCommand('Lock');"> Registrar Lock</button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("GetRegistrarUnlock"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" onclick="regCommand('Unlock');"> Registrar Unlock</button>
                                                            @endif
                                                            
                                                            @if ($domainsHelper->hasFunction("GetEPPCode"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" data-target="#getEPPModal"> Get EPP Code</button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("RequestDelete"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" data-target="#modalRequestDelete" onclick="regCommand('RequestDelete');"> Request Delete </button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("ReleaseDomain"))
                                                            <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" data-target="#modalReleaseDomain" onclick="regCommand('ReleaseDomain');"> Release Domain </button>
                                                            @endif

                                                            @if ($domainsHelper->hasFunction("IDProtectToggle"))
                                                            @php
                                                                $buttonValue = __("admin.domainsenableIdProtection");
                                                                if ($idprotection) {
                                                                    $buttonValue = __("admin.domainsdisableIdProtection");
                                                                }
                                                            @endphp
                                                            <button type="button" class="btn btn-light my-1 mx-1" data-toggle="modal" data-target="#modalIdProtectToggle" onclick="regCommand('IdProtectToggle');"> {{ $buttonValue }} </button>
                                                            @endif
                                                            
                                                            {{-- TODO --}}
                                                            {{-- @if ($showResendIRTPVerificationEmail && $domainsHelper->hasFunction("ResendIRTPVerificationEmail")) 
                                                                echo "    <input type=\"button\" value=\"";
                                                                echo AdminLang::trans("domains.resendNotification");
                                                                echo "\" class=\"button btn btn-default\" data-toggle=\"modal\" data-target=\"#modalResendIRTPVerificationEmail\">\n";
                                                            @endif --}}
                                                            {{-- @if ($domains->moduleCall("AdminCustomButtonArray")) 
                                                                $adminbuttonarray = $domains->getModuleReturn();
                                                                foreach ($adminbuttonarray as $key => $value) {
                                                                    echo " <input type=\"button\" value=\"";
                                                                    echo $key;
                                                                    echo "\" class=\"button btn btn-default\" onClick=\"window.location='";
                                                                    echo $whmcs->getPhpSelf();
                                                                    echo "?userid=";
                                                                    echo $userid;
                                                                    echo "&id=";
                                                                    echo $id;
                                                                    echo "&regaction=custom&ac=";
                                                                    echo $value . $token;
                                                                    echo "'\">";
                                                                }
                                                            @endif --}}
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Management Tools</label>
                                                        <div class="col-sm-10">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="dnsmanagement" class="form-check-input" id="dnsmanagement" {{ $dnsmanagement ? "checked" : "" }} value="1">
                                                                <label class="form-check-label" for="dnsmanagement">DNS Management</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="emailforwarding" class="form-check-input" id="emailforwarding" {{ $emailforwarding ? "checked" : "" }} value="1">
                                                                <label class="form-check-label" for="emailforwarding">Email Forwarding</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="idprotection" class="form-check-input" id="idprotection" {{ $idprotection ? "checked" : "" }} 
                                                                @if ($domainsHelper->hasFunction("IDProtectToggle"))
                                                                onclick="$('#modalIdProtectToggle').modal('show');" 
                                                                @endif 
                                                                value="1">
                                                                <label class="form-check-label" for="idprotection">ID Protection</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="donotrenew" class="form-check-input" id="donotrenew" {{ $donotrenew ? "checked" : "" }} value="1">
                                                                <label class="form-check-label" for="donotrenew">Disable Auto Renew</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if ($registrar) 
                                                        @php
                                                        $mod = new \App\Module\Registrar();
                                                        $mod->setLoadedModule($registrar);
                                                        @endphp
                                                        @if ($mod->functionExists("IDProtectToggle"))
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-2 col-form-label "></label>
                                                                <div class="col-sm-10">
                                                                    <label for="#" class="col-form-label ">{{ __("admin.domainsidprotectioncontrolna") }}</label>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Domain Reminder History</label>
                                                        <div class="col-sm-10">
                                                            <table id="dtDomainReminders" class="table table-bordered">
                                                                <thead>
                                                                    <tr class="text-white table-head-primary-color">
                                                                        <th scope="col">Date</th>
                                                                        <th scope="col">Reminder</th>
                                                                        <th scope="col">To</th>
                                                                        <th scope="col">Sent</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse ($obtainEmailReminders as $reminderMail)
                                                                    <tr>
                                                                        <td><p>{{ (new \App\Helpers\Functions())->fromMySQLDate($reminderMail["date"]) }}</p></td>
                                                                        <td><p>{{ __("admin.domains" . $reminderEmails[$reminderMail["type"]] . "Reminder") }}</p></td>
                                                                        <td><p>{{ $reminderMail["recipients"] }}</p></td>
                                                                        <td>
                                                                            <p>
                                                                                @php
                                                                                    $sent = sprintf(__("admin.domainsbeforeExpiry"), $reminderMail["days_before_expiry"]);
                                                                                    if ($reminderMail["days_before_expiry"] < 0) {
                                                                                        $sent = sprintf(__("admin.domainsafterExpiry"), $reminderMail["days_before_expiry"] * -1);
                                                                                    }
                                                                                    echo $sent;
                                                                                @endphp
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                    @empty
                                                                    <tr>
                                                                        <td colspan="3"><p>No record found</p></td>
                                                                    </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    @foreach ($additflds as $fieldLabel => $inputHTML)
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">{!! $fieldLabel !!}</label>
                                                        <div class="col-sm-10">
                                                            {!! $inputHTML !!}
                                                        </div>
                                                    </div>
                                                    @endforeach

                                                    {{-- <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Next Invoice Date</label>
                                                        <div class="col-sm-10">
                                                            <label for="" class="col-form-label ">2022-03-24</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">No Invoice Unpaid</label>
                                                        <div class="col-sm-10">
                                                            <label for="" class="col-form-label ">Tidak ada Invoice Unpaid</label>
                                                        </div>
                                                    </div> --}}

                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label ">Admin Notes</label>
                                                        <div class="col-sm-10">
                                                            <textarea name="additionalnotes" class="form-control" id="additionalnotes" rows="4" placeholder="Add admin notes">{{ $additionalnotes }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <button type="submit" class="btn btn-success px-3 mr-2">Save Changes</button>
                                                            <button type="reset" class="btn btn-secondary">Cancel Changes</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <button type="button" class="btn btn-outline-danger px-3 mr-2" onclick="regCommand('Delete');">Delete</button>
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

<div class="modal fade" id="registerOpenProviderModal" tabindex="-1" role="dialog" aria-labelledby="registerOpenProviderModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registerOpenProviderModalLabel">Register Domain</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="domainName" placeholder="Enter domain name" class="form-control mb-2">
        <input type="text" id="domainExtension" placeholder="Enter domain extension" class="form-control mb-2">
        <input type="text" id="nameServerName" placeholder="Enter name server" class="form-control mb-2">
        <input type="text" id="nameServerIp" placeholder="Enter name server IP" class="form-control mb-2">
        <input type="text" id="nameServerIp6" placeholder="Enter name server IP6" class="form-control mb-2">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="submitDomain">Submit</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="renewDomainModal" tabindex="-1" role="dialog" aria-labelledby="renewDomainModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="renewDomainModalLabel">Renew Domain</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="renewDomainName" placeholder="Enter domain name" class="form-control mb-2">
        <input type="text" id="renewDomainExtension" placeholder="Enter domain extension" class="form-control mb-2">
        <input type="number" id="renewDomainId" placeholder="Enter domain ID" class="form-control mb-2">
        <input type="number" id="renewPeriod" placeholder="Enter renewal period" class="form-control mb-2" min="1">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="submitRenewal">Submit</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="transferDomainModal" tabindex="-1" role="dialog" aria-labelledby="transferDomainModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="transferDomainModalLabel">Transfer Domain</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="transferDomainName" placeholder="Enter domain name" class="form-control mb-2">
        <input type="text" id="transferDomainExtension" placeholder="Enter domain extension" class="form-control mb-2">
        <input type="text" id="transferAuthCode" placeholder="Enter auth code" class="form-control mb-2">
        <input type="text" id="transferAdminHandle" placeholder="Enter admin handle" class="form-control mb-2">
        <input type="text" id="transferBillingHandle" placeholder="Enter billing handle" class="form-control mb-2">
        <input type="text" id="transferOwnerHandle" placeholder="Enter owner handle" class="form-control mb-2">
        <input type="text" id="transferTechHandle" placeholder="Enter tech handle" class="form-control mb-2">
        <input type="text" id="transferNameServerName" placeholder="Enter name server" class="form-control mb-2">
        <input type="text" id="transferNameServerIp" placeholder="Enter name server IP" class="form-control mb-2">
        <input type="text" id="transferNameServerIp6" placeholder="Enter name server IP6" class="form-control mb-2">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="submitTransfer">Submit</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modifyNameServerModal" tabindex="-1" role="dialog" aria-labelledby="modifyNameServerModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifyNameServerModalLabel">Modify Name Server</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="modifyNameServerName" placeholder="Enter name server" class="form-control mb-2">
        <input type="text" id="modifyNameServerIp" placeholder="Enter name server IP" class="form-control mb-2">
        <input type="text" id="modifyNameServerIp6" placeholder="Enter name server IP6" class="form-control mb-2">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="submitModifyNameServer">Submit</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="getEPPModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
 <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Request EPP Code</h5>
      </div>
      <div class="modal-body">
        <div id="alertContainer"></div>
        <span id="eppTitleRequest" style="display:block">Are you sure you want to send the EPP Code request to the registrar?</span>
      <div class="input-group mb-3"  id="eppCodeContainer" hidden>
        <input type="text" class="form-control" id="eppCodeForm" type="text" readonly>
        <div class="input-group-append">
            <button class="btn btn-outline-secondary px-3 mr-2" type="button" id="copyEPPCodeBtn" >Copy</button>
        </div>
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="confirmEPPRequest">OK</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal list domain openprovider --}}
<div class="modal fade" id="domainListModal" tabindex="-1" role="dialog" aria-labelledby="domainListModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="domainListModalLabel">Daftar Domain</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
                <input type="text" id="searchDomain" class="form-control mb-3" placeholder="Cari domain...">
                <div class="row">
                    <div class="col-md-6">
                        <ul id="domainListLeft" style="max-height: 300px; overflow-y: auto;">
                            <!-- Daftar domain kiri akan dimuat di sini -->
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul id="domainListRight" style="max-height: 300px; overflow-y: auto;">
                            <!-- Daftar domain kanan akan dimuat di sini -->
                        </ul>
                    </div>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination items will be added here -->
                    </ul>
                </nav>
            </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          </div>
      </div>
  </div>
</div>

{{-- Modal list contact openprovider --}}
<div class="modal fade" id="contactListModal" tabindex="-1" role="dialog" aria-labelledby="contactListModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-width" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="contactListModalLabel">Daftar Kontak</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
            <input type="text" id="searchContact" class="form-control mb-3" placeholder="Cari kontak...">
            <div class="table-responsive mb-4" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>API Access</th>
                            <th>Last Login</th>
                            <th>Last API Call</th>
                        </tr>
                    </thead>
                    <tbody id="contactTableBody">
                        <!-- Daftar kontak akan dimuat di sini -->
                    </tbody>
                </table>
            </div>
            <nav aria-label="Page navigation mt-2">
                <ul class="pagination justify-content-center" id="contactPagination">
                    <!-- Pagination items will be added here -->
                </ul>
            </nav>
        </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="getDomainDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
 <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Request Modify Nameservers</h5>
      </div>
      <div class="modal-body">
        <div id="alertNameserversContainer"></div>
        <div id="loader" class="text-center" style="display:none;">
            <div class="spinner-border spinner-border-lg text-success" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <div id="nameserverForm">
            <div class="form-group row">
                <label for="#" class="col-sm-3 col-form-label ">Nameserver 1</label>
                <div class="col-sm-9">
                        <input type="text" name="ns1" id="ns1" class="form-control">
                </div>
            </div> 
            <div class="form-group row">
                <label for="#" class="col-sm-3 col-form-label ">Nameserver 2</label>
                <div class="col-sm-9">
                        <input type="text" name="ns2" id="ns2" class="form-control">
                </div>
            </div> 
            <div class="form-group row">
                <label for="#" class="col-sm-3 col-form-label ">Nameserver 3</label>
                <div class="col-sm-9">
                        <input type="text" name="ns3" id="ns3" class="form-control">
                </div>
            </div> 
            <div class="form-group row">
                <label for="#" class="col-sm-3 col-form-label ">Nameserver 4</label>
                <div class="col-sm-9">
                        <input type="text" name="ns4" id="ns4" class="form-control">
                </div>
            </div> 
            <div class="form-group row">
                <label for="#" class="col-sm-3 col-form-label ">Nameserver 5</label>
                <div class="col-sm-9">
                        <input type="text" name="ns5" id="ns5" class="form-control">
                </div>
            </div>  
        </div>   
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="updateNameservers">Update</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeBtn">Close</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    @stack('clientsearch')

    <script>
        $(() => {
            $('#inputRegDate').datepicker(dateRangeOption);
            $('#inputExpiryDate').datepicker(dateRangeOption);
            $('#inputNextDueDate').datepicker(dateRangeOption);
            $('#inputNextInvoiceDate').datepicker(dateRangeOption);


            @if (isset($clientsdetails))
                $('.ssl-state.ssl-sync').each(function () {
                    let userid = $(this).attr('data-user-id');
                    let domain = $(this).attr('data-domain');

                    sslCheck({ userid, domain, details: false });
                });
            @endif
        });

        $(document).ready(function() {
    $('#registerOpenProviderBtn').on('click', function() {
        $('#registerOpenProviderModal').modal('show');
    });

    $('#submitDomain').on('click', function() {
        const domainName = $('#domainName').val();
        const domainExtension = $('#domainExtension').val();
        const nameServerName = $('#nameServerName').val();
        const nameServerIp = $('#nameServerIp').val();
        const nameServerIp6 = $('#nameServerIp6').val();

        fetch('{{ route('admin.pages.clients.viewclients.clientdomain.registerOpenprovider') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                domain: {
                    name: domainName,
                    extension: domainExtension
                },
                name_servers: [
                    {
                        name: nameServerName,
                        ip: nameServerIp,
                        ip6: nameServerIp6,
                        seq_nr: 1
                    }
                ]
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            alert('Domain registered successfully!');
            $('#registerOpenProviderModal').modal('hide');
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Failed to register domain.');
        });
    });
});

$(document).ready(function() {
    $('#renewDomainBtn').on('click', function() {
        $('#renewDomainModal').modal('show');
    });

    $('#submitRenewal').on('click', function() {
        const domainName = $('#renewDomainName').val();
        const domainExtension = $('#renewDomainExtension').val();
        const domainId = $('#renewDomainId').val();
        const period = $('#renewPeriod').val();

        fetch('{{ route('admin.pages.clients.viewclients.clientdomain.renewDomain') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                domain: {
                    name: domainName,
                    extension: domainExtension
                },
                id: parseInt(domainId),
                period: parseInt(period)
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            alert('Domain renewed successfully!');
            $('#renewDomainModal').modal('hide');
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Failed to renew domain.');
        });
    });
});

$(document).ready(function() {
    $('#transferDomainBtn').on('click', function() {
        $('#transferDomainModal').modal('show');
    });

    $('#submitTransfer').on('click', function() {
        const domainName = $('#transferDomainName').val();
        const domainExtension = $('#transferDomainExtension').val();
        const authCode = $('#transferAuthCode').val();
        const adminHandle = $('#transferAdminHandle').val();
        const billingHandle = $('#transferBillingHandle').val();
        const ownerHandle = $('#transferOwnerHandle').val();
        const techHandle = $('#transferTechHandle').val();
        const nameServerName = $('#transferNameServerName').val();
        const nameServerIp = $('#transferNameServerIp').val();
        const nameServerIp6 = $('#transferNameServerIp6').val();

        fetch('{{ route('admin.pages.clients.viewclients.clientdomain.transferDomain') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                domain: {
                    name: domainName,
                    extension: domainExtension
                },
                auth_code: authCode,
                admin_handle: adminHandle,
                billing_handle: billingHandle,
                owner_handle: ownerHandle,
                tech_handle: techHandle,
                name_servers: [
                    {
                        name: nameServerName,
                        ip: nameServerIp,
                        ip6: nameServerIp6,
                        seq_nr: 1
                    }
                ]
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            alert('Domain transferred successfully!');
            $('#transferDomainModal').modal('hide');
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Failed to transfer domain.');
        });
    });
});

$(document).ready(function() {
    $('#modifyNameServerBtn').on('click', function() {
        $('#modifyNameServerModal').modal('show');
    });

    $('#submitModifyNameServer').on('click', function() {
        const nameServerName = $('#modifyNameServerName').val();
        const nameServerIp = $('#modifyNameServerIp').val();
        const nameServerIp6 = $('#modifyNameServerIp6').val();

        fetch(`{{ route('admin.pages.clients.viewclients.clientdomain.modifyNameServer', ['name' => '']) }}/${nameServerName}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ip: nameServerIp,
                ip6: nameServerIp6
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            alert('Name server modified successfully!');
            $('#modifyNameServerModal').modal('hide');
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Failed to modify name server.');
        });
    });
});

        $(document).ready(function() {
    let contacts = []; 
    let contactCurrentPage = 1;
    const contactItemsPerPage = 10;

    $('#contactListModal').on('show.bs.modal', function (e) {
        $.ajax({
            url: '{{ route("admin.pages.clients.viewclients.clientdomain.getContactList") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    contacts = response.data;
                    renderContactList();
                } else {
                    alert('Gagal mendapatkan daftar kontak: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat memuat daftar kontak.');
            }
        });
    });

    $('#searchContact').on('input', function() {
        contactCurrentPage = 1;
        renderContactList();
    });

    function renderContactList() {
        const searchQuery = $('#searchContact').val().toLowerCase();
        const filteredContacts = contacts.filter(contact => 
            (contact.name.full_name + ' ' + contact.email).toLowerCase().includes(searchQuery)
        );

        const totalPages = Math.ceil(filteredContacts.length / contactItemsPerPage);
        const start = (contactCurrentPage - 1) * contactItemsPerPage;
        const end = start + contactItemsPerPage;
        const paginatedContacts = filteredContacts.slice(start, end);

        $('#contactTableBody').empty();

        paginatedContacts.forEach((item, index) => {
            const rowNumber = start + index + 1;
            const row = `
                <tr>
                    <td>${rowNumber}</td>
                    <td><a href="{{ route('admin.pages.clients.viewclients.clientdomain.detailContactOpenprovider', '') }}/${item.id}" target="_blank">${item.name.full_name}</a></td>
                    <td>${item.email}</td>
                    <td>${item.role}</td>
                    <td>${item.api_access_enabled ? 'Enabled' : 'Disabled'}</td>
                    <td>${item.last_login_at}</td>
                    <td>${item.last_api_call_at}</td>
                </tr>
            `;
            $('#contactTableBody').append(row);
        });

        renderContactPagination(totalPages);
    }

    function renderContactPagination(totalPages) {
        $('#contactPagination').empty();
        for (let i = 1; i <= totalPages; i++) {
            $('#contactPagination').append(`
                <li class="page-item ${i === contactCurrentPage ? 'active' : ''}">
                    <a class="page-link" href="#">${i}</a>
                </li>
            `);
        }

        $('.page-link').on('click', function(e) {
            e.preventDefault();
            contactCurrentPage = parseInt($(this).text());
            renderContactList();
        });
    }
});

        $(document).ready(function() {
            let domains = []; 
            let currentPage = 1;
            const itemsPerPage = 10;

            $('#domainListModal').on('show.bs.modal', function (e) {
                $.ajax({
                    url: '{{ route("admin.pages.clients.viewclients.clientdomain.getDomainList") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            domains = response.data;
                            renderDomainList();
                        } else {
                            alert('Gagal mendapatkan daftar domain: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat memuat daftar domain.');
                    }
                });
            });

            $('#searchDomain').on('input', function() {
                currentPage = 1;
                renderDomainList();
            });

            function renderDomainList() {
                const searchQuery = $('#searchDomain').val().toLowerCase();
                const filteredDomains = domains.filter(domain => 
                    (domain.domain.name + '.' + domain.domain.extension).toLowerCase().includes(searchQuery)
                );

                const totalPages = Math.ceil(filteredDomains.length / (itemsPerPage * 2));
                const start = (currentPage - 1) * itemsPerPage * 2;
                const end = start + itemsPerPage * 2;
                const paginatedDomains = filteredDomains.slice(start, end);

                $('#domainListLeft').empty();
                $('#domainListRight').empty();

                paginatedDomains.forEach((item, index) => {
                    const listItem = '<li>' + item.domain.name + '.' + item.domain.extension + '</li>';
                    if (index < itemsPerPage) {
                        $('#domainListLeft').append(listItem);
                    } else {
                        $('#domainListRight').append(listItem);
                    }
                });

                renderPagination(totalPages);
            }

            function renderPagination(totalPages) {
                $('#pagination').empty();
                for (let i = 1; i <= totalPages; i++) {
                    $('#pagination').append(`
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#">${i}</a>
                        </li>
                    `);
                }

                $('.page-link').on('click', function(e) {
                    e.preventDefault();
                    currentPage = parseInt($(this).text());
                    renderDomainList();
                });
            }
        });

        function showConfirmationPopup(title, message, onConfirm) {
            Swal.fire({
                title: title,
                html: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "OK",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: onConfirm
            }).then((result) => {
                if (result.isConfirmed) {
                    Toast.fire({ icon: 'success', title: 'Action confirmed!' });
                }
            }).catch(Swal.noop);
        }

       async function lockDomain(sld, tld) {
            console.log('SLD:', sld, 'TLD:', tld); 

            showConfirmationPopup(
                "Lock Domain",
                "Are you sure you want to lock this domain?",
                async () => {
                    const params = { sld, tld };

                    const options = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify(params)
                    };

                    try {
                        const url = `{{ route('admin.pages.clients.viewclients.clientdomain.lockDomainOpenprovider', ['sld' => '__SLD__', 'tld' => '__TLD__']) }}`;
                        const response = await fetch(url.replace('__SLD__', sld).replace('__TLD__', tld), options);
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            const data = await response.json();
                            if (!response.ok) {
                                throw new Error(data.message || 'Something went wrong');
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message || 'Domain berhasil dikunci',
                            });
                        } else {
                            const text = await response.text();
                            console.error('Unexpected response:', text);
                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan',
                                text: 'Unexpected response from server',
                            });
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan',
                            text: 'Error: ' + error.message,
                        });
                    }
                }
            );
        }

        async function unlockDomain(sld, tld) {
            console.log('SLD:', sld, 'TLD:', tld); 

            showConfirmationPopup(
                "Unlock Domain",
                "Are you sure you want to unlock this domain?",
                async () => {
                    const params = { sld, tld };

                    const options = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify(params)
                    };

                    try {
                        const url = `{{ route('admin.pages.clients.viewclients.clientdomain.unlockDomainOpenprovider', ['sld' => '__SLD__', 'tld' => '__TLD__']) }}`;
                        const response = await fetch(url.replace('__SLD__', sld).replace('__TLD__', tld), options);
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            const data = await response.json();
                            if (!response.ok) {
                                throw new Error(data.message || 'Something went wrong');
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message || 'Domain berhasil dibuka kuncinya',
                            });
                        } else {
                            const text = await response.text();
                            console.error('Unexpected response:', text);
                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan',
                                text: 'Unexpected response from server',
                            });
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan',
                            text: 'Error: ' + error.message,
                        });
                    }
                }
            );
        }

        @if (isset($clientsdetails))
        const regCommand = (action) => {
            let title = "";
            let message = "";
            let url = "{!! route('admin.pages.clients.viewclients.clientdomain.regCommand') !!}";
            let additionalOptions = {};
            let params = {
                userid: "{{ $userid }}",
                id: "{{ $id }}",
            };

            switch (action) {
                case "Renew":
                    title = "{{ __('admin.domainsrenewdomain') }}";
                    message = "{{ __('admin.domainsrenewdomainq') }}";
                    params.regaction = "renew";
                    break;
                case "Lock":
                    title = "{{ __('admin.domainsreglock') }}";
                    message = "{{ __('admin.domainsreglockq') }}";
                    params.regaction = "lock";
                    break;
                case "Unlock":
                    title = "{{ __('admin.domainsregunlock') }}";
                    message = "{{ __('admin.domainsregunlockq') }}";
                    params.regaction = "unlock";
                    break;
                case "LockOpenprovider":
                    title = "Lock Domain";
                    message = "Are you sure you want to lock this domain?";
                    params.regaction = "lockopenprovider";
                    break;
                case "UnlockOpenprovider":
                    title = "Unlock Domain";
                    message = "Are you sure you want to unlock this domain?";
                    params.regaction = "unlockopenprovider";
                    break;
                case "GetEPP":
                    title = "{{ __('admin.domainsrequestepp') }}";
                    message = "{{ __('admin.domainsrequesteppq') }}";
                    params.regaction = "eppcode";
                    break;
                case "RequestDelete":
                    title = "{{ __('admin.domainsrequestdel') }}";
                    message = "{{ __('admin.domainsrequestdelq') }}";
                    params.regaction = "reqdelete";
                    break;
                case "Delete":
                    title = "{{ __('admin.domainsdelete') }}";
                    message = "{{ __('admin.domainsdeleteq') }}";
                    params.regaction = "delete";
                    break;
                case "ReleaseDomain":
                    title = "{{ __('admin.domainsreleasedomain') }}";
                    message = "{{ __('admin.domainsreleasedomainq') }}";
                    params.regaction = "release";
                    additionalOptions = {
                        input: 'text',
                        inputAttributes: {
                            autocapitalize: 'off',
                            placeholder: "{{ __('admin.domainstransfertag') }}",
                        },
                        inputValidator: (value) => { if (!value) return 'The tag is required!'; },
                    }

                    break;
                case "CancelSubscription":
                    title = "{{ __('admin.servicescancelSubscription') }}";
                    message = "{{ __('admin.servicescancelSubscriptionSure') }}";
                    params.regaction = "renew";
                    break;
                case "ResendIRTPVerificationEmail":
                    title = "{{ __('admin.domainsresendNotification') }}";
                    message = "{{ __('admin.domainsresendNotificationQuestion') }}";
                    params.regaction = "resendirtpemail";
                    break;
                case "IdProtectToggle":
                    title = "{{ $idprotection ? __('admin.domainsdisableIdProtection') : __('admin.domainsenableIdProtection') }}";
                    message = "{{ $idprotection ? __('admin.domainsdisableIdProtectionQuestion') : __('admin.domainsenableIdProtectionQuestion') }}";
                    params.regaction = "idtoggle";
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
                    if(data !== true) params.transtag = data;

                    options.body = JSON.stringify(params);
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

                    if (action == 'Delete') {
                        setTimeout(function(){
                            window.location.href = "{{ route('admin.pages.clients.viewclients.clientdomain.index', ['userid' => $userid]) }}"
                        } , 3000);   
                    }
                }
            }).catch(swal.noop);
        };

        const sslCheck = async (payloads = {}) => {
            const url = "{!! route('admin.pages.clients.viewclients.clientdomain.sslCheckAdminArea') !!}";
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
        };

        document.getElementById('confirmEPPRequest').addEventListener('click', async function() {

            let $confirmButton = $('#confirmEPPRequest'); 
            let originalButtonText = $confirmButton.text();
            $confirmButton.attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

            let url = "{!! route('admin.pages.clients.viewclients.clientdomain.regCommand') !!}"; // Laravel route
            let params = {
                userid: "{{ $userid }}", 
                id: "{{ $id }}",       
                regaction: "eppcodePopup" 
            };

            let options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': "{{ csrf_token() }}" 
                },
                body: JSON.stringify(params)
            };

            document.getElementById('alertContainer').innerHTML = '';

            try {
                const response = await fetch(url, options); 
                if (!response || !response.ok) {
                    document.getElementById('alertContainer').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            Request failed. Please try again.
                        </div>`;
                }

                const result = await response.json();

                if (response.status === 200 && result.data) {
                    // Populate the eppCode field with result.data and show the input group
                    $('#eppCodeContainer').removeAttr('hidden');
                    $('#eppCodeForm').val(result.data);
                    document.getElementById('eppTitleRequest').style.display = 'none';
                    // Show success alert
                    document.getElementById('alertContainer').innerHTML = `
                        <div class="alert alert-success" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            EPP Code retrieved successfully!
                        </div>
                    `;
                } else {
                    // On failure, show an error alert inside the modal
                    document.getElementById('alertContainer').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Failed to retrieve EPP Code. Please try again.
                        </div>
                    `;
                }                            
            } catch (error) {
                document.getElementById('alertContainer').innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                        An error occurred: ${error.message}
                    </div>
                `;
            } finally {
                $confirmButton.attr('disabled', false).text(originalButtonText);
            }
        });

        $('#copyEPPCodeBtn').on('click', function() {
            let eppCode = $('#eppCodeForm').val(); // Get the value of the input field

            if (navigator.clipboard && window.isSecureContext) {
                // Modern way of copying to clipboard
                navigator.clipboard.writeText(eppCode).then(() => {
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            } else {
                // Fallback for older browsers
                let input = document.createElement('input');
                input.value = eppCode;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);

                alert('EPP Code copied to clipboard');
            }
        });

        async function getDomainDetail() {

            document.getElementById('loader').style.display = 'block';
            document.getElementById('nameserverForm').style.display = 'none';
            disableButtons();
          
            let url = "{!! route('admin.pages.clients.viewclients.clientdomain.regCommand') !!}";
            let params = {
                userid: "{{ $userid }}",
                id: "{{ $id }}",
                regaction: "getDomainDetail"
            };

            let options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify(params)
            };

            try {
                // Make the request using fetch
                const response = await fetch(url, options);

                // Check if response is okay
                if (!response.ok) {
                    document.getElementById('alertNameserversContainer').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            Request failed. Please try again.
                        </div>`;
                }

                // Parse the JSON response
                const result = await response.json();

                // Handle successful response
                if (response.status === 200 && result.data) {
                    let nameservers = result.data['nameserver'].split(','); // Split string by commas
                    document.getElementById('ns1').value = nameservers[0] || ''; 
                    document.getElementById('ns2').value = nameservers[1] || ''; 
                    document.getElementById('ns3').value = nameservers[2] || '';
                    document.getElementById('ns4').value = nameservers[3] || ''; 
                    document.getElementById('ns5').value = nameservers[4] || '';
                } else {
                    document.getElementById('alertNameserversContainer').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Failed to retrieve Domain Nameservers. Please try again.
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('alertNameserversContainer').innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                        An error occurred: ${error.message}
                    </div>
                `;
            } finally {
                document.getElementById('loader').style.display = 'none';
                document.getElementById('nameserverForm').style.display = 'block';
                enableButtons();
            }
        }

        // Function to disable buttons
        function disableButtons() {
            document.getElementById('updateNameservers').disabled = true;
            document.getElementById('closeBtn').disabled = true;
        };

        // Function to enable buttons
        function enableButtons() {
            document.getElementById('updateNameservers').disabled = false;
            document.getElementById('closeBtn').disabled = false;
        };

        document.getElementById('updateNameservers').addEventListener('click', async function() {

            let $confirmButton = $('#updateNameservers'); 
            let originalButtonText = $confirmButton.text();
            $confirmButton.attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            document.getElementById('closeBtn').disabled = true;


            let url = "{!! route('admin.pages.clients.viewclients.clientdomain.updatenameservers') !!}";
            let params = {
                userid: "{{ $userid }}", 
                id: "{{ $id }}",       
                regaction: "updateNameServer",
                ns1: $('#ns1').val(),
                ns2: $('#ns2').val(),
                ns3: $('#ns3').val(),
                ns4: $('#ns4').val(),
                ns5: $('#ns5').val()
            };

            let options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': "{{ csrf_token() }}" 
                },
                body: JSON.stringify(params)
            };

            document.getElementById('alertNameserversContainer').innerHTML = '';

            try {
                const response = await fetch(url, options); 
                if (!response || !response.ok) {
                    document.getElementById('alertNameserversContainer').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            Request failed. Please try again.
                        </div>`;
                }

                const result = await response.json();

                if (result.code === 200 && result.data) {

                    Toast.fire({ icon: 'success', title: result.data });
                    $('#getDomainDetail').modal('hide');

                } else {
                    document.getElementById('alertNameserversContainer').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Failed to update Nameservers. Please try again.
                        </div>
                    `;
                }  
                
                if (result.errors) {
                    let errorMessages = result.errors.map(error => `<li>${error}</li>`).join('');
                    document.getElementById('alertNameserversContainer').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <ul>${errorMessages}</ul>
                        </div>
                    `;
                }                          
            } catch (error) {
                document.getElementById('alertNameserversContainer').innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                        An error occurred: ${error.message}
                    </div>
                `;
            } finally {
                $confirmButton.attr('disabled', false).text(originalButtonText);
                document.getElementById('closeBtn').disabled = false;
            }
        });

        @endif
    </script>
@endsection
