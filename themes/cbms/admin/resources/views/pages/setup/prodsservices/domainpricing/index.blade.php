@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Domain Pricing</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <!-- Sidebar Shortcut -->
                    {{-- <div class="col-xl-1">
                        <div class="card sc-sidecard">
                            <div class="card-body">
                                <section class="shortcuts">
                                    <div class="card-title">
                                        <h6>Shortcuts</h6>
                                    </div>
                                    <ul>
                                        <li><a href="#">Add New Client</a></li>
                                        <li><a href="#">Add New Order</a></li>
                                        <li><a href="#">Create New Quote</a></li>
                                        <li><a href="#">Create New To-Do  </a></li>
                                        <li><a href="#">Open New Ticket</a></li>
                                    </ul>
                                </section>

                                <section class="system-information">
                                    <div class="card-title">
                                        <h6>System Information</h6>
                                    </div>
                                    <ul>
                                        <li>Registered To: PT Qwords Company International</li>
                                        <li>License Type: Development</li>
                                        <li>License Expires: Never</li>
                                        <li>Version: 7.10.3</li>
                                    </ul>
                                </section>
                                <section class="adv-search">
                                    <div class="card-title">
                                        <h6>Advanced Search</h6>
                                    </div>
                                    <form>
                                        <div class="form-group">
                                            <select class="form-control" placeholder="Clients">
                                                <option value="clients">Clients</option>
                                                <option value="orders">Orders</option>
                                                <option value="services">Services</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <select class="form-control" placeholder="Clients Name">
                                                <option value="clients_id">Clients ID</option>
                                                <option value="company">Company</option>
                                                <option value="email">Email Address</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="input1" placeholder="Type here...">
                                        </div>
                                        <div>
                                            <button type="submit"
                                                class="btn btn-primary btn-block d-flex align-items-center justify-content-center"><i
                                                    class="ri-search-line mr-2"></i>Search</button>
                                        </div>
                                    </form>
                                </section>
                                <section class="who-online">
                                    <div class="card-title">
                                        <h6>Staff Online</h6>
                                    </div>
                                    <p>Admin</p>
                                </section>
                            </div>
                        </div>
                    </div> --}}
                    <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Domain Pricing</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p>This is where you configure the TLDs that you want to allow clients to register or transfer to you. As well as pricing, you can set which addons are offered with each TLD, if an EPP code is required for transfers, and whether registration should be automated and if so, with which registrar.</p>
                                        <div class="row bg-light p-3 mx-1 justify-content-lg-around d-none">
                                            <div class="col-lg-2 pt-2 my-1 text-center"><strong>Spotlight TLDs<i
                                                        class="ri-lightbulb-line ml-2"></i></strong>
                                            </div>
                                            <div class="col-lg-1 my-1 p-0"><input type="text" class="form-control"></div>
                                            <div class="col-lg-1 my-1 p-0"><input type="text" class="form-control"></div>
                                            <div class="col-lg-1 my-1 p-0"><input type="text" class="form-control"></div>
                                            <div class="col-lg-1 my-1 p-0"><input type="text" class="form-control"></div>
                                            <div class="col-lg-1 my-1 p-0"><input type="text" class="form-control"></div>
                                            <div class="col-lg-1 my-1 p-0"><input type="text" class="form-control"></div>
                                            <div class="col-lg-1 my-1 p-0"><input type="text" class="form-control"></div>
                                            <div class="col-lg-1 my-1 p-0"><input type="text" class="form-control"></div>

                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12" id="accordion">
                                                <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                                    <button type="button" class="btn btn-light" data-toggle="collapse" data-target="#collapse1" aria-expanded="false">Lookup Provider</button>
                                                    <button type="button" class="btn btn-light" data-toggle="collapse" data-target="#collapse2" aria-expanded="false">Premium Domains</button>
                                                    <button type="button" class="btn btn-light" data-toggle="collapse" data-target="#collapse3" aria-expanded="false">Bulk Management</button>
                                                    <button type="button" class="btn btn-light" data-toggle="collapse" data-target="#collapse4" aria-expanded="false">Domain Addons</button>
                                                </div>
                                                <div id="collapse1" class="collapse" data-parent="#accordion">
                                                    <div class="card w-25 mb-0">
                                                        <div class="card-header bg-primary">
                                                            <h5 class="mb-0 text-white">Lookup Provider</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="text-center selected-provider">
                                                                {!!$lookupRegistrar!!}
                                                            </div>
                                                            <div class="row">
                                                                <div class="col">
                                                                    <a id="changeLookupProvider" class="btn btn-sm btn-light btn-block open-modal" href="{{route('apiconsumer.admin.setup.lookup-provider')}}" data-modal-title="Choose Lookup Provider" onclick="return false;" data-modal-size="modal-lg">
                                                                        Change
                                                                    </a>
                                                                </div>
                                                                <div class="col">
                                                                    <a id="configureLookupProvider" class="btn btn-sm btn-light btn-block open-modal" href="{{route('apiconsumer.admin.setup.domainlookup.index', 'action=configure')}}" data-modal-title="Configure Lookup Provider" data-btn-submit-id="btnSaveLookupConfiguration" data-btn-submit-label="Save" onclick="return false;" data-modal-size="modal-lg">
                                                                        Configure
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="collapse2" class="collapse" data-parent="#accordion">
                                                    <div class="card w-25 mb-0">
                                                        <div class="card-header bg-primary">
                                                            <h5 class="mb-0 text-white">Premium Domain</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-around mt-2 premium-domain-option">
                                                                <input
                                                                    class="premium-toggle-switch"
                                                                    name="premiumDomains"
                                                                    type="checkbox" data-toggle="toggle"
                                                                    data-onstyle="success" data-size="sm"
                                                                    @if (\App\Helpers\Cfg::getValue("PremiumDomains"))
                                                                        checked
                                                                    @endif
                                                                >
                                                                <a 
                                                                    href="{{route('apiconsumer.admin.setup.premium-levels')}}"
                                                                    class="btn btn-light btn-sm open-modal {{\App\Helpers\Cfg::getValue("PremiumDomains") ? '' : 'disabled'}}"
                                                                    id="linkConfigurePremiumMarkup"
                                                                    data-modal-title="Configure Premium Domain Levels"
                                                                    data-btn-submit-id="btnSavePremium"
                                                                    data-btn-submit-label="Save"
                                                                >Configure</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="collapse3" class="collapse" data-parent="#accordion">
                                                    <div class="card w-25 mb-0">
                                                        <div class="card-header bg-primary">
                                                            <h5 class="mb-0 text-white">Bulk Management</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="text-center" style="background-color: #252B3B; color: #FFF">{{Lang::get('admin.pricing')}}</div>
                                                            <div class="text-center m-2">1 {{Lang::get('admin.domainsyear')}}</div>
                                                            <div class="row">
                                                                <div class="col-lg-4">
                                                                    <label class="col-form-label">{{Lang::get('admin.domainsregister')}}</label>
                                                                </div>
                                                                <div class="col-lg-8">
                                                                    <div class="input-group input-group-sm mb-3">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text">{{$defaultCurrency->prefix}}</span>
                                                                        </div>
                                                                        <input type="number" step="any" id="inputOneYearRegistrationBulk" min="-1" class="form-control" aria-label="Rupiah" placeholder="0.00">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text">{{$defaultCurrency->suffix}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-lg-4">
                                                                    <label class="col-form-label">{{Lang::get('admin.domainstransfer')}}</label>
                                                                </div>
                                                                <div class="col-lg-8">
                                                                    <div class="input-group input-group-sm mb-3">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text">{{$defaultCurrency->prefix}}</span>
                                                                        </div>
                                                                        <input type="number" step="any" id="inputOneYearTransferBulk" min="-1" class="form-control" aria-label="Rupiah" placeholder="0.00">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text">{{$defaultCurrency->suffix}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-lg-4">
                                                                    <label class="col-form-label">{{Lang::get('admin.domainsrenewal')}}</label>
                                                                </div>
                                                                <div class="col-lg-8">
                                                                    <div class="input-group input-group-sm mb-3">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text">{{$defaultCurrency->prefix}}</span>
                                                                        </div>
                                                                        <input type="number" step="any" id="inputOneYearRenewBulk" min="-1" class="form-control" aria-label="Rupiah" placeholder="0.00">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text">{{$defaultCurrency->suffix}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="inputCopyPricingBulk" value="1">
                                                                        <label class="custom-control-label font-size-12" for="inputCopyPricingBulk">
                                                                            {{Lang::get('admin.domainsbulkYearsDescription')}}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-lg-12 my-2">
                                                                    <div class="text-center" style="background-color: #252B3B; color: #FFF">
                                                                        {{Lang::get('admin.domainsgracePeriod')}}
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label class="col-form-label">{{Lang::get('admin.domainsduration')}}</label>
                                                                </div>
                                                                <div class="col-lg-8">
                                                                    <div class="input-group input-group-sm mb-3">
                                                                        <input type="number" min="0" id="inputGraceDurationBulk" class="form-control" aria-label="days" aria-describedby="basic-addon2">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text" id="basic-addon2">{{Lang::get('admin.calendardays')}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label class="col-form-label">{{Lang::get('admin.domainsfee')}}</label>
                                                                </div>
                                                                <div class="col-lg-8">
                                                                    <div class="input-group input-group-sm mb-3">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text">{{$defaultCurrency->prefix}}</span>
                                                                        </div>
                                                                        <input type="number" step="any" id="inputGraceFeeBulk" min="-1" class="form-control" aria-label="Rupiah" placeholder="0.00">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text">{{$defaultCurrency->suffix}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-lg-12 my-2">
                                                                    <div class="text-center" style="background-color: #252B3B; color: #FFF">
                                                                        {{Lang::get('admin.domainsredemptionPeriod')}}
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label class="col-form-label">{{Lang::get('admin.domainsduration')}}</label>
                                                                </div>
                                                                <div class="col-lg-8">
                                                                    <div class="input-group input-group-sm mb-3">
                                                                        <input type="number" min="0" id="inputRedemptionDurationBulk" class="form-control" aria-label="days" aria-describedby="basic-addon2">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text" id="basic-addon2">{{Lang::get('admin.calendardays')}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label class="col-form-label">{{Lang::get('admin.domainsfee')}}</label>
                                                                </div>
                                                                <div class="col-lg-8">
                                                                    <div class="input-group input-group-sm mb-3">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text">{{$defaultCurrency->prefix}}</span>
                                                                        </div>
                                                                        <input type="number" step="any" id="inputRedemptionFeeBulk" min="-1" class="form-control" aria-label="Rupiah" placeholder="0.00">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text">{{$defaultCurrency->suffix}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-md-12 text-center">
                                                                    <button type="button" id="btnBulkManagementSave" class="btn btn-success btn-sm">{{Lang::get('admin.savechanges')}}</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="collapse4" class="collapse" data-parent="#accordion">
                                                    <div class="card w-25 mb-0">
                                                        <div class="card-header bg-primary">
                                                            <h5 class="mb-0 text-white">Domain Addons</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <form id="saveaddons" action="" method="post">
                                                                @csrf
                                                                <input type="hidden" name="action" value="saveaddons">
                                                                @foreach ($domainAddons as $type => $domainAddonData)
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <div class="text-center mb-2 domain-addon-title" style="background-color: #252B3B; color: #FFF">
                                                                                {{Lang::get("admin.domains" . strtolower($type))}}
                                                                            </div>
                                                                        </div>
                                                                        @foreach ($domainAddonData as $currencyId => $priceInfo)
                                                                            <div class="col-lg-6 col-sm-12 mb-2 text-center">
                                                                                <label class="col-form-label">
                                                                                    <strong>{{$currencies[$currencyId]}}</strong>
                                                                                </label>
                                                                            </div>
                                                                            <div class="col-lg-6 col-sm-12 mb-2">
                                                                                <input type="text" name="currency[{{$currencyId}}][{{$priceInfo['field']}}]" value="{{$priceInfo["price"]}}" class="form-control" placeholder="0.00">
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endforeach
                                                                <div class="row">
                                                                    <div class="col-lg-12 text-center">
                                                                        <button class="btn btn-sm btn-success">
                                                                            {{Lang::get("admin.savechanges")}}
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row my-2">
                                            <div class="col-lg-12 col-sm-12">
                                                <div class="borderx rounded">
                                                    <form id="form-pricing" action="" method="post">
                                                        @csrf
                                                        <div class="table-responsivex">
                                                            <table class="table table-bordered table-hover w-100 display" id="domainpricing">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center" width="50">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="custom-control-input"
                                                                                    id="checkAllTld">
                                                                                <label class="custom-control-label"
                                                                                    for="checkAllTld"></label>
                                                                            </div>
                                                                        </th>
                                                                        <th width="250" class="text-center" style="width:250px;">TLD</th>
                                                                        <th width="100"></th>
                                                                        <th>DNS Management</th>
                                                                        <th>Email Forwarding</th>
                                                                        <th>ID Protection</th>
                                                                        <th>EPP Code</th>
                                                                        <th width="200">Auto Registration</th>
                                                                        <th witdh="50"></th>
                                                                        <th witdh="50"></th>
                                                                        <th witdh="50"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($extensions as $domainExtension)
                                                                        @php
                                                                            $id = $domainExtension->id;
                                                                            $extension = $domainExtension->extension;
                                                                            $autoreg = $domainExtension->autoRegistrationRegistrar;
                                                                            $dnsmanagement = $domainExtension->supportsDnsManagement;
                                                                            $emailforwarding = $domainExtension->supportsEmailForwarding;
                                                                            $idprotection = $domainExtension->supportsIdProtection;
                                                                            $eppcode = $domainExtension->requiresEppCode;
                                                                            $order = $domainExtension->order;
                                                                            $group = $domainExtension->group;
                                                                            $customGracePeriod = $domainExtension->getRawAttribute("grace_period");
                                                                            $defaultGracePeriod = $domainExtension->defaultGracePeriod;
                                                                            $gracePeriodFee = 0 <= $domainExtension->getRawAttribute("grace_period_fee") ? \App\Helpers\Functions::format_as_currency($domainExtension->getRawAttribute("grace_period_fee")) : -1;
                                                                            $customRedemptionGracePeriod = $domainExtension->getRawAttribute("redemption_grace_period");
                                                                            $defaultRedemptionGracePeriod = $domainExtension->defaultRedemptionGracePeriod;
                                                                            $redemptionGracePeriodFee = 0 <= $domainExtension->getRawAttribute("redemption_grace_period_fee") ? \App\Helpers\Functions::format_as_currency($domainExtension->getRawAttribute("redemption_grace_period_fee")) : -1;
                                                                            $groupInfo = \App\Helpers\ViewHelper::getDomainGroupLabel($group);
                                                                        @endphp
                                                                        <tr id="dp-{{$id}}" data-tld-id="{{$id}}" class="domain-pricing-row">
                                                                            <td class="text-center align-middle">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input
                                                                                        data-tld="{{$extension}}"
                                                                                        name="tldId[]"
                                                                                        value="{{$id}}"
                                                                                        type="checkbox"
                                                                                        class="custom-control-input"
                                                                                        id="{{$id}}">
                                                                                    <label class="custom-control-label"
                                                                                        for="{{$id}}"></label>
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                <div class="input-group">
                                                                                    <input type="text" class="form-control tld" name="tld[{{$id}}]" value="{{$extension}}">
                                                                                    <div class="input-group-append">
                                                                                    <input type="hidden" name="tldGroup[{{$id}}]" value="{{$group}}">
                                                                                    <div class="selected-tld-group-container">
                                                                                        <div class="position-absolute selected-tld-group" style="right: 43%; margin-top: 6px; z-index: 10;">
                                                                                            {!!$groupInfo!!}
                                                                                        </div>
                                                                                    </div>
                                                                                    <button class="btn btn-info" type="button" disabled>
                                                                                        <i class="ri-lightbulb-line"></i>
                                                                                    </button>
                                                                                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                        <i class="ri-arrow-down-s-fill"></i>
                                                                                    </button>
                                                                                    <ul class="dropdown-menu p-0">
                                                                                        <li>
                                                                                            <div class="btn-group tld-group" role="group" aria-label="Basic example">
                                                                                                <button type="button" class="btn btn-light">
                                                                                                    <span data-group="none" class="badge badge-secondary">NONE</span>
                                                                                                </button>
                                                                                                <button type="button" class="btn btn-light">
                                                                                                    <span data-group="hot" class="badge badge-danger">HOT</span>
                                                                                                </button>
                                                                                                <button type="button" class="btn btn-light">
                                                                                                    <span data-group="new" class="badge badge-success">NEW</span>
                                                                                                </button>
                                                                                                <button type="button" class="btn btn-light">
                                                                                                    <span data-group="sale" class="badge badge-warning">SALE</span>
                                                                                                </button>
                                                                                            </div>
                                                                                        </li>
                                                                                    </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                <a href="?id={{$id}}" target="_blank" class="btn btn-light btn-sm">Pricing</a>
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input
                                                                                        data-type="dns"
                                                                                        data-tld="{{$extension}}"
                                                                                        name="dns[{{$id}}]"
                                                                                        type="checkbox"
                                                                                        class="custom-control-input input-option" 
                                                                                        @if ($dnsmanagement)
                                                                                            checked
                                                                                        @endif
                                                                                        id="dns{{$id}}"
                                                                                    >
                                                                                    <label class="custom-control-label"
                                                                                        for="dns{{$id}}"></label>
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input
                                                                                        data-type="email"
                                                                                        data-tld="{{$extension}}"
                                                                                        name="email[{{$id}}]"
                                                                                        type="checkbox"
                                                                                        class="custom-control-input input-option" 
                                                                                        @if ($emailforwarding)
                                                                                            checked
                                                                                        @endif
                                                                                        id="email{{$id}}"
                                                                                    >
                                                                                    <label class="custom-control-label"
                                                                                        for="email{{$id}}"></label>
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input
                                                                                        data-type="idprot"
                                                                                        data-tld="{{$extension}}"
                                                                                        name="idprot[{{$id}}]"
                                                                                        type="checkbox"
                                                                                        class="custom-control-input input-option" 
                                                                                        @if ($idprotection)
                                                                                            checked
                                                                                        @endif
                                                                                        id="idprot{{$id}}"
                                                                                    >
                                                                                    <label class="custom-control-label"
                                                                                        for="idprot{{$id}}"></label>
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input
                                                                                        data-type="eppcode"
                                                                                        data-tld="{{$extension}}"
                                                                                        name="eppcode[{{$id}}]"
                                                                                        type="checkbox"
                                                                                        class="custom-control-input input-option" 
                                                                                        @if ($eppcode)
                                                                                            checked
                                                                                        @endif
                                                                                        id="eppcode{{$id}}"
                                                                                    >
                                                                                    <label class="custom-control-label"
                                                                                        for="eppcode{{$id}}"></label>
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                {!!(new \App\Module\Registrar)->getRegistrarsDropdownMenu($autoreg, "autoreg[" . $id . "]")!!}
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                <h4>
                                                                                    <a href="javascript:;" class="btn-shower tld-settings" data-tld-id="{{$id}}" onclick="return false;">
                                                                                        <i class="ri-settings-3-fill"></i>
                                                                                    </a>
                                                                                </h4>
                                                                            </td>
                                                                            <td class="text-center align-middle sortcol" style="cursor: move">
                                                                                <h4>
                                                                                    <i class="ri-drag-move-2-fill"></i>
                                                                                </h4>
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                <h4>
                                                                                    <a href="javascript:;" onclick="deleteTld({{$id}})" class="text-danger">
                                                                                        <i class="ri-close-circle-fill"></i>
                                                                                    </a>
                                                                                </h4>
                                                                            </td>
                                                                        </tr>
                                                                        <tr id="dpe-{{$id}}" class="domain-extra nodrop d-none" style="display: none">
                                                                            <td class="text-center" colspan="11">
                                                                                <table class="table w-50 mx-auto">
                                                                                    <tr>
                                                                                        <th></th>
                                                                                        <th class="bg-primary text-white">Grace Period</th>
                                                                                        <th class="bg-primary text-white">Redemption Period</th>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="align-middle">
                                                                                            Duration
                                                                                        </td>
                                                                                        <td class="align-middle">
                                                                                            <div class="input-group">
                                                                                                <input name="grace[{{$id}}]" min="0" value="{{0 <= $customGracePeriod ? $customGracePeriod : ""}}" type="number" class="form-control" placeholder="{{$defaultGracePeriod}} (Default)">
                                                                                                <div class="input-group-append">
                                                                                                <span class="input-group-text" id="basic-addon2">Days</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                        <td class="align-middle">
                                                                                            <div class="input-group">
                                                                                                <input name="redemption[{{$id}}]" min="0" value="{{0 <= $customRedemptionGracePeriod ? $customRedemptionGracePeriod : ""}}" type="number" class="form-control" placeholder="{{$defaultRedemptionGracePeriod}} (Default)">
                                                                                                <div class="input-group-append">
                                                                                                <span class="input-group-text" id="basic-addon2">Days</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="align-middle">
                                                                                            Fee
                                                                                        </td>
                                                                                        <td class="align-middle">
                                                                                            <div class="input-group">
                                                                                                <div class="input-group-prepend">
                                                                                                    <span class="input-group-text" id="basic-addon2">{{$defaultCurrency->prefix}}</span>
                                                                                                </div>
                                                                                                <input name="grace_fee[{{$id}}]" min="-1" step="any" value="{{0 <= $gracePeriodFee ? $gracePeriodFee : ""}}" type="number" class="form-control" placeholder="0.00">
                                                                                                <div class="input-group-append">
                                                                                                    <span class="input-group-text" id="basic-addon2">{{$defaultCurrency->suffix}}</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                        <td class="align-middle">
                                                                                            <div class="input-group">
                                                                                                <div class="input-group-prepend">
                                                                                                    <span class="input-group-text" id="basic-addon2">{{$defaultCurrency->prefix}}</span>
                                                                                                </div>
                                                                                                <input name="redemption_grace_fee[{{$id}}]" min="-1" step="any" value="{{0 <= $redemptionGracePeriodFee ? $redemptionGracePeriodFee : ""}}" type="number" class="form-control" placeholder="0.00">
                                                                                                <div class="input-group-append">
                                                                                                    <span class="input-group-text" id="basic-addon2">{{$defaultCurrency->suffix}}</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                                @if (count($extensions))
                                                                <tfoot>
                                                                    <tr>
                                                                        <td colspan="10" class="text-center">
                                                                            <button class="btn btn-primary">Save Change</button>
                                                                            <a type="button" class="btn btn-light open-modal" href="{{route('apiconsumer.admin.setup.showduplicatetld')}}" data-btn-submit-label="Submit" data-modal-title="Duplicate TLD" data-btn-submit-id="btnDuplicateTld">
                                                                                Duplicate TLD
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </tfoot>
                                                                @endif
                                                            </table>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="border rounded p-4 bg-light">
                                                    <div class="alert alert-danger" id="newTldAlertError" style="display: none"></div>
                                                    <div class="alert alert-success" id="newTldAlertSuccess" style="display: none"></div>
                                                    <form method="POST" action="" id="form-newtld">
                                                        @csrf
                                                        <div class="form-group row">
                                                          <label class="col-sm-2 col-form-label">TLD</label>
                                                          <div class="col-sm-10">
                                                            <input type="text" name="tld" class="form-control mb-3" id="" placeholder="Add TLD (eg. com)" required>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="label" id="none" value="none" checked>
                                                                <label class="form-check-label" for="none">
                                                                    <span class="badge badge-pill badge-secondary">NONE</span>
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="label" id="hot" value="hot">
                                                                <label class="form-check-label" for="hot">
                                                                    <span class="badge badge-pill badge-danger">HOT</span>
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="label" id="new" value="new">
                                                                <label class="form-check-label" for="new">
                                                                    <span class="badge badge-pill badge-success">NEW</span>
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="label" id="sale" value="sale">
                                                                <label class="form-check-label" for="sale">
                                                                    <span class="badge badge-pill badge-warning">SALE</span>
                                                                </label>
                                                            </div>
                                                          </div>
                                                        </div>
                                                        <div class="form-group row align-items-center">
                                                            <label class="col-sm-2 col-form-label">DNS Management</label>
                                                            <div class="col-sm-10">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="dns" id="dns">
                                                                    <label class="form-check-label" for="dns">
                                                                      Check to Enable
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row align-items-center">
                                                            <label class="col-sm-2 col-form-label">Email Forwarding</label>
                                                            <div class="col-sm-10">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="email" id="email">
                                                                    <label class="form-check-label" for="email">
                                                                      Check to Enable
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row align-items-center">
                                                            <label class="col-sm-2 col-form-label">ID Protection</label>
                                                            <div class="col-sm-10">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="idprot" id="idprot">
                                                                    <label class="form-check-label" for="idprot">
                                                                      Check to Enable
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row align-items-center">
                                                            <label class="col-sm-2 col-form-label">EPP Code</label>
                                                            <div class="col-sm-10">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="eppcode" id="eppcode">
                                                                    <label class="form-check-label" for="eppcode">
                                                                      Check to Enable
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row align-items-center">
                                                            <label class="col-sm-2 col-form-label">Auto Registration</label>
                                                            <div class="col-sm-10">
                                                                {!!(new \App\Module\Registrar)->getRegistrarsDropdownMenu2(old("auto_registration"), "auto_registration")!!}
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                          <label class="col-sm-2 col-form-label"></label>
                                                          <div class="col-sm-10">
                                                            <button type="submit" class="btn btn-primary">Add</button>
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
                <!-- End MAIN CARD -->
            </div>
        </div>
    </div>
    @include('includes.modal-ajax')
@endsection

@section('scripts')
    <style>
        .lookup-provider {
            border: 3px solid #e2e7e9;
        }
        .lookup-provider.active {
            border-color: #369;
        }
    </style>
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
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/bootstrap-switch-custom/bootstrap4-toggle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/TableDnD/0.9.1/jquery.tablednd.js" integrity="sha256-d3rtug+Hg1GZPB7Y/yTcRixO/wlI78+2m08tosoRn7A=" crossorigin="anonymous"></script>
    <script src="{{ Theme::asset('js/notify.min.js') }}"></script>
    <script src="{{ Theme::asset('js/AjaxModal.js') }}"></script>
    <script>
        $(function() {
            var $formsaveaddons = $("form#saveaddons");
            $formsaveaddons.on("submit", function(e) {
                e.preventDefault();
                var data = $(this).serialize();
                $.ajax({
                    url: route('apiconsumer.admin.setup.saveaddons'),
                    type: 'POST',
                    data: data,
                    success: function (res) {
                        console.log(res)
                        if (res.result == 'success') {
                            $.notify(res.message, "success");
                            
                        } else {
                            $.notify(res.message, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        var e = JSON.parse(xhr.responseText);
                        $.notify(e.message, "error");
                    },
                });
            });

            $(".premium-toggle-switch").on("change", function(e) {
                var state = this.checked;
                $.ajax({
                    url: route('apiconsumer.admin.setup.togglePremiumDomain'),
                    type: 'POST',
                    data: {enable: state == true ? 1 : 0},
                    success: function (res) {
                        console.log(res)
                        if (res.result == 'success') {
                            // $.notify(res.message, "success");
                            if (state) {
                                $('.premium-domain-option').find('a').removeClass('disabled');
                            } else {
                                $('.premium-domain-option').find('a').addClass('disabled');
                            }
                        } else {
                            $.notify(res.message, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        var e = JSON.parse(xhr.responseText);
                        $.notify(e.message, "error");
                    },
                });
            });

            $("#checkAllTld").click(function (event) {
                $(event.target).parents(".table").find("input[name='tldId[]']").prop("checked", this.checked);
            });

            // bulk
            $(document).on('click', '#btnBulkManagementSave', function (event) {
                event.preventDefault();
                var selectedItems = $("input[name='tldId[]']"),
                    self = $(this),
                    oneYearRegistration = $('#inputOneYearRegistrationBulk').val(),
                    oneYearRenew = $('#inputOneYearRenewBulk').val(),
                    oneYearTransfer = $('#inputOneYearTransferBulk').val(),
                    graceDuration = $('#inputGraceDurationBulk').val(),
                    graceFee = $('#inputGraceFeeBulk').val(),
                    redemptionDuration = $('#inputRedemptionDurationBulk').val(),
                    redemptionFee = $('#inputRedemptionFeeBulk').val();

                if (selectedItems.filter(':checked').length === 0
                    || (
                            (!oneYearRegistration && oneYearRegistration !== 0)
                        && (!oneYearRenew && oneYearRenew !== 0)
                        && (!oneYearTransfer && oneYearTransfer !== 0)
                        && (!graceDuration && graceDuration !== 0)
                        && (!graceFee && graceFee !== 0)
                        && (!redemptionDuration && redemptionDuration !== 0)
                        && (!redemptionFee && redemptionFee !== 0)
                    )
                ) {
                    // swal({
                    //     title: '{{$errorSwal["title"]}}',
                    //     html: true,
                    //     text: '{{$errorSwal["text"]}}',
                    //     type: 'error',
                    //     confirmButtonText: '{{$errorSwal["confirmButtonText"]}}'
                    // });
                    $.notify('{{$errorSwal["text"]}}', "error");
                } else {
                    var selectedTlds = [],
                    validResponse = false;
                    $("input[name='tldId[]']:checked").each(function() {
                        selectedTlds.push(parseInt($(this).val()));
                    });
                    if (confirm('{{$massUpdateSwal["text"]}}')) {
                        self.prop('disabled', true).addClass('disabled');
                        $.ajax({
                            url: route('apiconsumer.admin.setup.mass-configuration-tld'),
                            type: 'POST',
                            data: {
                                _token: '{{csrf_token()}}',
                                tldIds: selectedTlds,
                                pricing: {
                                    register: oneYearRegistration,
                                    renew: oneYearRenew,
                                    transfer: oneYearTransfer,
                                    copyToYears: $('#inputCopyPricingBulk').prop('checked'),
                                    grace: {
                                        duration: graceDuration,
                                        fee: graceFee
                                    },
                                    redemption: {
                                        duration: redemptionDuration,
                                        fee: redemptionFee
                                    }
                                }
                            },
                            success: function (res) {
                                // console.log(res)
                                if (res.result == 'success') {
                                    validResponse = true;
                                    $.notify(res.message, "success");
                                    setTimeout(() => {
                                        location.reload();
                                    }, 1000);
                                } else {
                                    validResponse = false;
                                    $.notify(res.message, "error");
                                }
                            },
                            error: function(xhr, status, error) {
                                var e = JSON.parse(xhr.responseText);
                                $.notify(e.message, "error");
                            },
                        }).always(function() {
                            if (!validResponse) {
                                self.prop('disabled', false).removeClass('disabled');
                                $.notify("{{Lang::get('admin.unexpectedError')}}", "error");
                            }
                        });
                    }
                    return false;

                    // swal(
                    //     {
                    //         title: '" . $massUpdateSwal["title"] . "',
                    //         html: true,
                    //         text: '" . $massUpdateSwal["text"] . "',
                    //         type: 'warning',
                    //         showCancelButton: true,
                    //         confirmButtonText: '" . $massUpdateSwal["confirmButtonText"] . "',
                    //         cancelButtonText: '" . $massUpdateSwal["cancelButtonText"] . "'
                    //     },
                    //     function() {
                    //             self.prop('disabled', true).addClass('disabled');
                    //         WHMCS.http.jqClient.post(
                    //                 '" . $massUpdateUrl . "',
                    //             {
                    //                     token: csrfToken,
                    //                 tldIds: selectedTlds,
                    //                 pricing: {
                    //                         register: oneYearRegistration,
                    //                     renew: oneYearRenew,
                    //                     transfer: oneYearTransfer,
                    //                     copyToYears: $('#inputCopyPricingBulk').prop('checked'),
                    //                     grace: {
                    //                             duration: graceDuration,
                    //                         fee: graceFee
                    //                     },
                    //                     redemption: {
                    //                             duration: redemptionDuration,
                    //                         fee: redemptionFee
                    //                     }
                    //                 }
                    //             }
                    //         ).done(function(data) {
                    //                 if (data.success === true) {
                    //                     validResponse = true;
                    //                 window.location.replace('configdomains.php?success=true');
                    //             }
                    //         }).always(function() {
                    //                 if (!validResponse) {
                    //                     self.prop('disabled', false).removeClass('disabled');
                    //                 " . $massUpdateErrorGrowl . "
                    //             }
                    //         });
                    //     }
                    // );
                }

            });

            var $formPricing = $("form#form-pricing");
            $formPricing.on("submit", function(e) {
                e.preventDefault();
                var data = $(this).serialize();
                $.each($('form#form-pricing input[type=checkbox].input-option')
                    .filter(function(idx){
                        return $(this).prop('checked') === false
                    }),
                    function(idx, el){
                        // attach matched element names to the formData with a chosen value.
                        var emptyVal = "";
                        data += '&' + $(el).attr('name') + '=' + emptyVal;
                    }
                );
                // console.log(data);
                $.ajax({
                    url: route('apiconsumer.admin.setup.saveTld'),
                    type: 'POST',
                    data: data,
                    success: function (res) {
                        // console.log(res)
                        if (res.result == 'success') {
                            $.notify(res.message, "success");
                        } else {
                            $.notify(res.message, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        var e = JSON.parse(xhr.responseText);
                        $.notify(e.message, "error");
                    },
                });
            });

            $('.tld-group button').on('click', function(e) {
                e.preventDefault();
                var tldId = $(this).parent().parent().parent().parent().parent().parent().parent().data('tld-id'),
                    group = $(this).find('span').attr('data-group'),
                    spanHtml = $(this).html();
                if (group != 'none') {
                    $('#dp-' + tldId).first('td').find('div.selected-tld-group').html(spanHtml);
                } else {
                    $('#dp-' + tldId).first('td').find('div.selected-tld-group').html('');
                }
                $('input[name="tldGroup[' + tldId + ']"]').val(group);
            });

            $("#domainpricing").tableDnD({
                dragHandle: ".sortcol",
                onDrop: function(table, row) {
                    var thisRow = $("#"+row.id),
                        tldId = thisRow.data('tld-id'),
                        tldIds = [];
                    $(".domain-pricing-row").each(function(index) {
                        var thisId = $(this).data("tld-id");
                        if (typeof thisId !== "undefined") {
                            tldIds.push(thisId);
                            var currentRow = $("#dp-"+thisId),
                                extraRow = $("#dpe-"+thisId),
                                clonedRow = extraRow.clone();
                            
                            extraRow.remove();
                            currentRow.after(clonedRow);
                        }
                    });
                    $.ajax({
                        url: route('apiconsumer.admin.setup.saveorderTld'),
                        type: 'POST',
                        data: {pricing: tldIds},
                        success: function (res) {
                            console.log(res)
                            if (res.result == 'success') {
                                $.notify(res.message, "success");
                            } else {
                                $.notify(res.message, "error");
                            }
                        },
                        error: function(xhr, status, error) {
                            var e = JSON.parse(xhr.responseText);
                            $.notify(e.message, "error");
                        },
                    });
                },
            });

            $(".tld-settings").on("click", function(e) {
                var tldId = $(this).data("tld-id"),
                tableRow = $("#dpe-" + tldId),
                isHidden = tableRow.hasClass("d-none");
                if (isHidden) {
                    tableRow.hide().removeClass("d-none").fadeIn("slow");
                } else {
                    tableRow.fadeOut("slow").addClass("d-none");
                }
            });

            // $(".btn-shower").click(function() {
            //     $(this).closest('tr').nextUntil("tr:has(.btn-shower)").toggle("fast", function() {});
            // });

            var $formNewTld = $("form#form-newtld"),
                $alertError = $("#newTldAlertError"),
                $alertSuccess = $("#newTldAlertSuccess")

            $formNewTld.on("submit", function(e) {
                e.preventDefault();
                $.ajax({
                    url: route('apiconsumer.admin.setup.addnewTld'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        console.log(res);
                        if (res.result == 'success') {
                            $alertSuccess.text(res.message).show();
                            $alertError.hide();
                            location.reload();
                        } else {
                            $alertSuccess.hide();
                            $alertError.text(res.message).show();
                        }
                    },
                    error: function(xhr) {
                        console.log('Error');
                    },
                });
            });
        });
        function deleteTld(id) {
            if (confirm("Are you sure you want to delete this domain extension from the pricing list?")) {
                $.ajax({
                    url: route('apiconsumer.admin.setup.deleteTld'),
                    type: 'POST',
                    data: {id: id},
                    success: function(res) {
                        console.log(res);
                        if (res.result == 'success') {
                            alert(res.message);
                            location.reload();
                        } else {
                            alert(res.message);
                            return false;
                        }
                    },
                    error: function(xhr) {
                        alert('Error. Contact your administrator');
                        console.log('Error');
                    },
                });
            } else {
                return false;
            }
        }
    </script>
@endsection
