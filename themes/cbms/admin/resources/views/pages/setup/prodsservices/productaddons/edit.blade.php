@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Edit Addon</title>
@endsection

@push('styles')
    <link href="{{ Theme::asset('css/app.css') }}" type="text/css" rel="stylesheet" />
@endpush

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <!-- Sidebar Shortcut -->

                    <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        @if(Session::has('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                                @php
                                    Session::forget('success');
                                @endphp
                            </div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        <form action="{{ route('admin.productaddons.update') }}" method="post" enctype="multipart/form-data">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Product Addons - Edit Addon</h4>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <nav>
                                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                            <a class="nav-link nav-item active" id="nav-details-tab" data-toggle="tab"  href="#nav-details" role="tab" aria-controls="nav-details" aria-selected="true">Details</a>
                                            <a class="nav-link nav-item" id="nav-pricing-tab" data-toggle="tab"  href="#nav-pricing" role="tab" aria-controls="nav-pricing" aria-selected="false">Pricing</a>
                                            <a class="nav-link nav-item" id="nav-module-tab" data-toggle="tab" href="#nav-module" role="tab" aria-controls="nav-module" aria-selected="false">Module Settings</a>
                                            <a class="nav-link nav-item" id="nav-custom-fields-tab" data-toggle="tab"  href="#nav-custom-fields" role="tab" aria-controls="nav-custom-fields"  aria-selected="false">Custom Fields</a>
                                            <a class="nav-link nav-item" id="nav-applicable-prods-tab" data-toggle="tab" href="#nav-applicable-prods" role="tab" aria-controls="nav-applicable-prods" aria-selected="false">Applicable Products</a>
                                            <a class="nav-link nav-item" id="nav-assoc-download-tab" data-toggle="tab" href="#nav-assoc-download" role="tab" aria-controls="nav-assoc-download" aria-selected="false">Associated Download</a>
                                        </div>
                                    </nav>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="tab-content" id="nav-tabContent">
                                            <div class="tab-pane fade show active" id="nav-details" role="tabpanel"
                                                aria-labelledby="nav-details-tab">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Name</label>
                                                            <div class="col-sm-12 col-lg-4">
                                                                <input type="text" name="name" value="{{ $data->name }}" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-12 col-lg-2 col-form-label">Description</div>
                                                            <div class="col-sm-12 col-lg-5">
                                                                <textarea name="description"id="" cols="30" rows="5" class="form-control">{{ $data->description }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-12 col-lg-2 col-form-label">Tax Addon</div>
                                                            <div class="col-sm-12 col-lg-5 pt-2">
                                                                <input type="hidden" name="tax" value="0">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="tax" id="customCheck1" value="1" {{ ($data->tax ==1)?'checked':'' }} >
                                                                    <label class="custom-control-label"  for="customCheck1">Charge tax on this addon</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-12 col-lg-2 col-form-label">Show on Order
                                                            </div>
                                                            <div class="col-sm-12 col-lg-5 pt-2">
                                                                <input type="hidden" name="showorder" value="0">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="showorder" value="1" id="customCheck2" {{ ($data->showorder ==1)?'checked':'' }}>
                                                                    <label class="custom-control-label" for="customCheck2">Show addon during initial product order process</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-12 col-lg-2 col-form-label">Suspend Parent Product</div>
                                                            <div class="col-sm-12 col-lg-5 pt-2">
                                                              <input type="hidden" name="suspendproduct" value="0">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="suspendproduct" value="1" id="customCheck3" {{ ($data->suspendproduct ==1)?'checked':'' }}>
                                                                    <label class="custom-control-label" for="customCheck3">Tick to suspend the parent product as well when instances of this addon are overdue</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-12 col-lg-2 col-form-label">Welcome Email
                                                            </div>
                                                            <div class="col-sm-12 col-lg-5">
                                                                <select name="welcomeemail" id="" class="form-control">
                                                                    <option value="0">None</option>
                                                                    @foreach($emailtemplate as $r)
                                                                        <option value="{{ $r->id }}" {{ ($r->id == $data->welcomeemail )?'selected':'' }} >{{ $r->name }}</option>
                                                                    @endforeach

                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-12 col-lg-2 col-form-label">Addon Weighting
                                                            </div>
                                                            <div class="col-sm-12 col-lg-2">
                                                                <input type="text" name="weight" class="form-control" value="{{ $data->weight }}" placeholder="0">
                                                            </div>
                                                            <div class="col-sm-12 col-lg-4 pt-2">
                                                                <p>Enter a number here to override the default alphabetical
                                                                    display order</p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-12 col-lg-2 col-form-label">Hidden</div>
                                                            <div class="col-sm-12 col-lg-5 pt-2">
                                                                <input type="hidden" name="hidden" value="0">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="hidden" value="1" id="customCheck4"  {{ ($data->hidden ==1)?'checked':'' }}>
                                                                    <label class="custom-control-label"
                                                                        for="customCheck4">Enable to hide this addon from
                                                                        the client area order forms</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-12 col-lg-2 col-form-label">Retired</div>
                                                            <div class="col-sm-12 col-lg-8 pt-2">
                                                                <input type="hidden" name="retired" value="0">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="retired" value="1" id="customCheck5" {{ ($data->retired ==1)?'checked':'' }}>
                                                                    <label class="custom-control-label" for="customCheck5">A
                                                                        retired addon will no longer appear within the admin
                                                                        area (note you will still be able to view and manage
                                                                        existing purchases)</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="nav-pricing" role="tabpanel"
                                                aria-labelledby="nav-pricing-tab">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group row d-flex justify-content-center">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Payment Type</label>
                                                            <div class="col-sm-12 col-lg-3 pt-2">
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio" name="billingcycle" id="inlineRadio1" value="" {{ ($data->billingcycle == 'free')?'checked':'' }} >
                                                                    <label class="form-check-label" for="inlineRadio1">Free</label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio" name="billingcycle" id="inlineRadio2"   value="onetime" {{ ($data->billingcycle == 'onetime')?'checked':'' }}>
                                                                    <label class="form-check-label" for="inlineRadio2">One Time</label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio" name="billingcycle" id="inlineRadio3" value="recurring"  {{ ($data->billingcycle == 'recurring')?'checked':'' }}>
                                                                    <label class="form-check-label" for="inlineRadio3">Recurring</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="onetime" class="collapse {{ ($data->billingcycle == 'onetime')?'show':'' }}">

                                                            <div class="row justify-content-center">
                                                                <div class="col-lg-6">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-bordered">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Currency</th>
                                                                                    <th> </th>
                                                                                    <th>One Time/Monthly</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            @foreach($onetime as $r)

                                                                                <tr id="ontimeprice{{ $r->id }}" >
                                                                                    <td rowspan="3" width="100" style="vertical-align: middle;" ><strong>{{ $r->code }}</strong></td>
                                                                                    <td width="100">Setup Fee</td>
                                                                                    <td width="120" class='p-1'>
                                                                                        <input type="text" name="currency[onetime][{{ $r->id }}][msetupfee]" value="{{ @$price['onetime'][$r->id]->msetupfee ?? '0.00' }}"  style="display:{{ ($data->billingcycle == 'onetime')?'block':'none' }}" id="setup_{{ $r->code }}_monthlys" class="form-control collapse">
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td width="100"> Price</td>
                                                                                    <td width="120" class='p-1'>
                                                                                        <input type="text" name="currency[onetime][{{ $r->id }}][monthly]"   value="{{ @$price['onetime'][$r->id]->monthly  ?? '0.00'  }}"style="display:{{ ($data->billingcycle == 'onetime')?'block':'none' }}" id="pricing_{{ $r->code }}_monthlys" class="form-control collapse">
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td width="100">Enable</td>
                                                                                    <td class="text-center">
                                                                                        <div class="custom-control custom-checkbox">
                                                                                            <input type="checkbox" class="custom-control-input onlyOneTime pricingtgl" data-id="{{ $r->id }}" currency="{{ $r->code }}" cycle="monthlys" id="onlyOneTime{{ $r->id }}" value="{{ (@(int)$price['onetime'][$r->id]->monthly != '')?'true':'false'}}" {{ (@(int)$price['onetime'][$r->id ]->monthly != '')?'checked':'' }}>
                                                                                            <label class="custom-control-label"  for="onlyOneTime{{ $r->id }}"></label>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="recurring" class="collapse {{ ($data->billingcycle == 'recurring')?'show':'' }}">
                                                            <div class="row d-flex justify-content-center">
                                                                <div class="col-lg-12">
                                                                    <div class="table-responsive mb-3">
                                                                        <table class="table table-bordered">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th style="width: 80px">Currency</th>
                                                                                    <th> </th>
                                                                                    <th>One Time/Monthly</th>
                                                                                    <th>Quarterly</th>
                                                                                    <th>Semi-Annually</th>
                                                                                    <th>Annually</th>
                                                                                    <th>Biennially</th>
                                                                                    <th>Triennially</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($currencies as $r)
                                                                                    <tr>
                                                                                        <td rowspan="3"  class="text-center font-weight-bold" width="100">{{ $r->code }} </td>
                                                                                        <td width="100">Setup fee</td>
                                                                                        @foreach($cycles as $d )

                                                                                        <td width="100">
                                                                                            <input type="text" name="currency[recurring][{{ $r->id }}][{{$setup[$loop->index] }}]" id="setup_{{ $r->code }}_{{$d}}" value="{{$price['recurring'][$r->id]->{$setup[$loop->index]} ?? 0.00}}" style="display: @if(!empty(@$price['recurring'][$r->id]->{$setup[$loop->index]}))block @else none @endif;" class="form-control  text-center">
                                                                                        </td>
                                                                                        @endforeach
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td width="100">Price</td>
                                                                                        @foreach($cycles as $d )
                                                                                        <td><input type="text" name="currency[recurring][{{ $r->id }}][{{ $d }}]" id="pricing_{{ $r->code }}_{{ $d }}" size="10" value="{{ $price['recurring'][$r->id]->{$d} ?? 0.00  }}" style="display: @if(!empty(@$price['recurring'][$r->id]->{$d})) block @else none @endif ;"  class="form-control text-center"></td>
                                                                                        @endforeach
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td width="100">Enable</td>
                                                                                        @foreach($cycles as $d )
                                                                                            <td><input type="checkbox" class="pricingtgl" currency="{{ $r->code }}" cycle="{{ $d }}" @if(!empty(@$price['recurring'][$r->id]->{$d}) || !empty(@$price['recurring'][$r->id]->{$setup[$loop->index]})) checked @endif ></td>
                                                                                        @endforeach
                                                                                    </tr>

                                                                                @endforeach

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="nav-module" role="tabpanel"
                                                aria-labelledby="nav-module-tab">
                                                <div class="table-responsive" id="addonModuleSettings">
                                                    <table class="form table border">
                                                        <tr>
                                                            <td class="text-right" width="10%">Product Type</td>
                                                            <td class="text-left bg-light" width="25%">
                                                                <select name="type" class="form-control">
                                                                    <option value="hostingaccount" {{ ($data->type == 'hostingaccount' )?'selected':'' }}>Shared Hosting</option>
                                                                    <option value="reselleraccount" {{ ($data->type == 'reselleraccount' )?'selected':'' }}>Reseller Hosting</option>
                                                                    <option value="server" {{ ($data->type == 'server' )?'selected':'' }}>Server/VPS</option>
                                                                    <option value="other"  {{ ($data->type == 'other' )?'selected':'' }}>Other</option>
                                                                </select>
                                                            </td>
                                                            <td class="text-right" width="10%">Module Name</td>
                                                            <td class="text-left bg-light" width="25%">
                                                                <div class="d-flex align-items-center">
                                                                    <select name="servertype" id="inputModule" class="form-control w-50" onchange="fetchModuleSettings('0', 'simple', 'configaddons')">
                                                                        <option value="">None</option>
                                                                        @foreach ($serverModules as $moduleName => $displayName)
                                                                            <option value="{{$moduleName}}" {{ ($data->module == $moduleName )?'selected':'' }}>{{$displayName}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <img class="ml-2" src="{{Theme::asset('img/loading.gif')}}" id="moduleSettingsLoader" alt="loading" style="display: none">
                                                                </div>
                                                            </td>
                                                            <td class="text-right" width="15%">Server Group</td>
                                                            <td class="text-left bg-light">
                                                                <select name="servergroup" id="inputServerGroup" class="form-control" onchange="fetchModuleSettings('0', 'simple', 'configaddons')">
                                                                    <option value="0" data-server-types="">None</option>
                                                                    @foreach($server as $r)
                                                                        <option value="{{ $r->id }}" data-server-types="{{$r->server_types}}"  {{ ($data->server_group_id ==  $r->id )?'selected':'' }}>{{ $r->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <div id="serverReturnedError" class="alert alert-warning my-4 d-none">
                                                        <span id="serverReturnedErrorText"></span>
                                                    </div>

                                                    <table class="table form module-settings border" id="tblModuleSettings">
                                                        <tr id="noModuleSelectedRow">
                                                            <td class="border-top-0 text-center">
                                                                <div class="no-module-selected">
                                                                    Choose a module to load configuration settings
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <table id="tblAddonAutomationSettings" class="table mb-0 module-settings-automation border">
                                                        <tr>
                                                            <td width="20" class="align-middle">
                                                                <input type="radio" name="autosetup" value="order" id="order" {{ ($data->autoactivate == 'order')?'checked':'' }}>
                                                            </td>
                                                            <td class="bg-light align-middle">
                                                                <label for="order" class="mb-0 cursor-pointer">Automatically setup the product as soon as an order is placed</label>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="align-middle">
                                                                <input type="radio" name="autosetup" value="payment" id="payment" {{ ($data->autoactivate == 'payment')?'checked':'' }}>
                                                            </td>
                                                            <td class="bg-light align-middle">
                                                                <label for="payment" class="mb-0 cursor-pointer">Automatically setup the product as soon as the first payment is received</label>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="align-middle">
                                                                <input type="radio" name="autosetup" value="on" id="autosetup_on" {{ ($data->autoactivate == 'on')?'checked':'' }}>
                                                            </td>
                                                            <td class="bg-light align-middle">
                                                                <label for="autosetup_on" class="mb-0 cursor-pointer">Automatically setup the product when you manually accept a pending order</label>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="align-middle">
                                                                <input type="radio" name="autosetup" value="" id="autosetup_no" {{ ($data->autoactivate == '')?'checked':'' }}>
                                                            </td>
                                                            <td class="bg-light align-middle">
                                                                <label for="autosetup_no" class="mb-0 cursor-pointer">Automatically setup the product when you manually accept a pending order</label>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="nav-custom-fields" role="tabpanel"
                                                aria-labelledby="nav-custom-fields-tab">

                                                @foreach($customField as $r)
                                                <div class="card" id="customfields{{ $r->id }}">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="row">
                                                                    <div class="col-sm-12 col-lg-6">
                                                                        <div class="form-group row">
                                                                            <label for=""
                                                                                class="col-sm-12 col-lg-4 col-form-label">Field Name</label>
                                                                            <div class="col-sm-12 col-lg-8">
                                                                                <input type="text" name="old[{{ $r->id }}][addFieldName]" value="{{ $r->fieldname }}" class="form-control">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-12 col-lg-6">
                                                                        <div class="form-group row justify-content-end">
                                                                            <label for=""
                                                                                class="col-sm-12 col-lg-3 col-form-label text-lg-right">Display  Order</label>
                                                                            <div class="col-sm-12 col-lg-3">
                                                                                <input type="text" name="old[{{ $r->id }}][addSortOrder]" value="{{ (int)$r->showorder }}" class="form-control">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Field Type </label>
                                                                    <div class="col-sm-12 col-lg-3">
                                                                        <select name="old[{{ $r->id }}][addFieldType]"  class="form-control">
                                                                            <option value="text" {{ ($r->fieldtype =='text')?'selected':'' }} >Text Box</option>
                                                                            <option value="link" {{ ($r->fieldtype =='link')?'selected':'' }}  >Link/URL</option>
                                                                            <option value="password" {{ ($r->fieldtype =='password')?'selected':'' }}>Password</option>
                                                                            <option value="dropdown" {{ ($r->fieldtype =='dropdown')?'selected':'' }}>Drop Down</option>
                                                                            <option value="tickbox" {{ ($r->fieldtype =='tickbox')?'selected':'' }}>Tick Box</option>
                                                                            <option value="textarea" {{ ($r->fieldtype =='textarea')?'selected':'' }}>Text Area</option>
                                                                            <option value="image" {{ ($r->fieldtype =='image')?'selected':'' }}>Image</option>
                                                                            <option value="hidden" {{ ($r->fieldtype =='hidden')?'selected':'' }}>Disabled</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Description</label>
                                                                    <div class="col-sm-12 col-lg-5">
                                                                        <input type="text" name="old[{{ $r->id }}][addFieldDescription]"  value="{{ $r->description }}" class="form-control">
                                                                    </div>
                                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                                        <p>The explanation to show users</p>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Validation</label>
                                                                    <div class="col-sm-12 col-lg-5">
                                                                        <input type="text" name="old[{{ $r->id }}][addFieldExpression]" value="{{ $r->regexpr }}" class="form-control">
                                                                    </div>
                                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                                        <p>Regular Expression Validation String</p>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                                        Select Options
                                                                    </label>
                                                                    <div class="col-sm-12 col-lg-5">
                                                                        <input type="text" name="old[{{ $r->id }}][addFieldOptions]" value="{{ $r->fieldoptions }}" class="form-control">
                                                                        <div class="row pt-3">
                                                                            <div class="col-3">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox" name="old[{{ $r->id }}][addFieldAdmin]" class="custom-control-input" {{ ($r->adminonly == 'on')?'checked':'' }} id="selectOptions1{{ $r->id }}">
                                                                                    <label class="custom-control-label" for="selectOptions1{{ $r->id }}">Admin Only</label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-3">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox" class="custom-control-input" name="old[{{ $r->id }}][addFieldRequired]" id="selectOptions2{{ $r->id }}"  {{ ($r->required == 'on')?'checked':'' }}>
                                                                                    <label class="custom-control-label" for="selectOptions2{{ $r->id }}">Required Field</label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-3">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox" class="custom-control-input" name="old[{{ $r->id }}][addFieldShowOrder]" id="selectOptions4{{ $r->id }}"  {{ ($r->showorder == 'on')?'checked':'' }}>
                                                                                    <label class="custom-control-label" for="selectOptions4{{ $r->id }}">Show on Order</label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-3">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox" class="custom-control-input" name="old[{{ $r->id }}][addFieldShowInvoice]"  {{ ($r->showinvoice == 'on')?'checked':'' }} id="selectOptions3{{ $r->id }}">
                                                                                    <label class="custom-control-label" for="selectOptions3{{ $r->id }}">Show on Invoice</label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                                        <p> For Dropdowns Only - Comma Seperated List</p>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="box-delete  float-right">
                                                            <button data-id="{{ $r->id }}" data-name="{{ $r->fieldname }}" data-addonid="{{ $data->id }}" type="button" class="btn btn-danger btn-sm deletecustom" >Delete Field</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach

                                                <h4 class="card-title">Add New Custom Field</h4>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col-sm-12 col-lg-6">
                                                                <div class="form-group row">
                                                                    <label for=""
                                                                        class="col-sm-12 col-lg-4 col-form-label">Field Name</label>
                                                                    <div class="col-sm-12 col-lg-8">
                                                                        <input type="text" name="addFieldName" class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <div class="form-group row justify-content-end">
                                                                    <label for=""
                                                                        class="col-sm-12 col-lg-3 col-form-label text-lg-right">Display  Order</label>
                                                                    <div class="col-sm-12 col-lg-3">
                                                                        <input type="text" name="addSortOrder"  class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Field Type </label>
                                                            <div class="col-sm-12 col-lg-3">
                                                                <select name="addFieldType"  class="form-control">
                                                                    <option value="text">Text Box</option>
                                                                    <option value="link">Link/URL</option>
                                                                    <option value="password">Password</option>
                                                                    <option value="dropdown">Drop Down</option>
                                                                    <option value="tickbox">Tick Box</option>
                                                                    <option value="textarea">Text Area</option>
                                                                    <option value="image">Image</option>
                                                                    <option value="hidden">Disabled</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Description</label>
                                                            <div class="col-sm-12 col-lg-5">
                                                                <input type="text" name="addFieldDescription" class="form-control">
                                                            </div>
                                                            <div class="col-sm-12 col-lg-5 pt-2">
                                                                <p>The explanation to show users</p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Validation</label>
                                                            <div class="col-sm-12 col-lg-5">
                                                                <input type="text" name="addFieldExpression" class="form-control">
                                                            </div>
                                                            <div class="col-sm-12 col-lg-5 pt-2">
                                                                <p>Regular Expression Validation String</p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">
                                                                Select Options
                                                            </label>
                                                            <div class="col-sm-12 col-lg-5">
                                                                <input type="text" name="addFieldOptions" class="form-control">
                                                                <div class="row">
                                                                    <div class="col-3">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" name="addFieldOptions" name="addFieldAdmin" class="custom-control-input" id="selectOptions1">
                                                                            <label class="custom-control-label" for="selectOptions1">Admin Only</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" class="custom-control-input" name="addFieldRequired" id="selectOptions2">
                                                                            <label class="custom-control-label" for="selectOptions2">Required Field</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" class="custom-control-input" name="addFieldShowOrder"  id="selectOptions4">
                                                                            <label class="custom-control-label" for="selectOptions4">Show on Order</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" class="custom-control-input" name="addFieldShowInvoice"  id="selectOptions3">
                                                                            <label class="custom-control-label" for="selectOptions3">Show on Invoice</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-12 col-lg-5 pt-2">
                                                                <p> For Dropdowns Only - Comma Seperated List</p>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                            <div class="tab-pane fade" id="nav-applicable-prods" role="tabpanel"
                                                aria-labelledby="nav-applicable-prods-tab">
                                                <div class="container">
                                                <select name="packages[]" id="bootstrap-duallistbox-nonselected-list_packages" class="form-control" size="10" multiple="multiple">
                                                    @foreach($product as $r)
                                                    <option value="{{ $r['id'] }}" @if(!empty($data->packages)) {{ (in_array($r['id'],$data->packages))?'selected':'' }}   @endif>{{ $r['groupname'] }} - {{ $r['name'] }}</option>
                                                    @endforeach
                                                </select>

                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="nav-assoc-download" role="tabpanel"
                                                aria-labelledby="nav-assoc-download-tab">
                                                <div class="container">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <h4 class="card-title">
                                                                Available Downloads
                                                            </h4>
                                                            <small>Showing all 0</small>
                                                            <input type="text" class="form-control"
                                                                placeholder="Filter Downloads">
                                                            <div class="mt-2">
                                                                <button class="btn btn-block btn-light">>></button
                                                                    title="Move All">
                                                                <select name="" id="" class="form-control" size="10"
                                                                    multiple="multiple">
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <h4 class="card-title">
                                                                Selected Downloads
                                                            </h4>
                                                            <small>Showing all 0</small>
                                                            <input type="text" class="form-control"
                                                                placeholder="Filter Downloads">
                                                            <div class="mt-2">
                                                                <button class="btn btn-block btn-light"></button title="Remove All">
                                                                        <select name="" id="" class="form-control" size="10"
                                                                            multiple="multiple">
                                                                        </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-lg-12 text-center">
                                                 {{ csrf_field() }}
                                                 @method('PUT')
                                                <input type="hidden" name="id" value="{{ $data->id }}" >
                                                <button type="submit" class="btn btn-success px-3 mx-1">Save Changes</button>
                                                <a href="{{ route('admin.productaddons.index') }}" class="btn btn-light px-3 mx-1">Cancel Changes</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form>
                    </div>
                    <!-- End MAIN CARD -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ Theme::asset('assets/js/accordion-radio.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/check-reveal.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/bootstrap-duallistbox/js/jquery.bootstrap-duallistbox.min.js') }}"></script>
    <script src="{{ Theme::asset('js/notify.min.js') }}"></script>
    <script src="{{ Theme::asset('js/module-settings.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            /* $(".onlyOneTime").change(function() {
                console.log($(this).data('id'),'idnya');
                if (this.checked) {
                    $("#onlyOneTime1").val("true");
                    $("#OnlyOneTimeSetupIDR").collapse("show");
                    $("#OnlyOneTimePriceIDR").collapse("show");
                } else {
                    $("#onlyOneTime1").val("false");
                    $("#OnlyOneTimeSetupIDR").collapse("hide");
                    $("#OnlyOneTimePriceIDR").collapse("hide");
                }
            }); */
            $(".pricingtgl").click(function() {
                var cycle = $(this).attr("cycle");
                var currency = $(this).attr("currency");

                if ($(this).is(":checked")) {
                    $("#pricing_" + currency + "_" + cycle).val("0.00").show();
                    $("#setup_" + currency + "_" + cycle).show();
                } else {
                    $("#pricing_" + currency + "_" + cycle).val("-1.00").hide();
                    $("#setup_" + currency + "_" + cycle).hide();
                }
            });
            var demo1 = $('#bootstrap-duallistbox-nonselected-list_packages').bootstrapDualListbox();


            $('input[type=radio][name=billingcycle]').on('change', function() {
                //console.log($(this).val());
                if($(this).val() === 'recurring'){
                    $('#recurring').addClass('show');
                    $('#onetime').removeClass('show');
                }else if($(this).val() === 'onetime'){
                    $('#onetime').addClass('show');
                    $('#recurring').removeClass('show');
                }else{
                    $('#recurring').removeClass('show');
                    $('#onetime').removeClass('show');
                }

                return false;
            });


            $( ".deletecustom" ).click(function() {
            // e.preventDefault();
                Swal.fire({
                        title: "Warning..!",
                        text: "Are you sure you want to delete this field and ALL DATA associated with it?",
                        icon: "warning",
                        showCancelButton:true,
                        cancelButtonColor: '#d33',
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((value) => {
                        if(value.isConfirmed){
                           var id=$(this).data('id');


                            $.ajax({
                                    type: 'POST',
                                    url:  "{{ url(Request::segment(1).'/setup/productservices/productaddons/custom_flields_destroy') }}",
                                    data: {id:id,_token:'{{ csrf_token() }}',_method:'DELETE'},
                                    dataType: 'json',
                                    success: function(data){
                                        if(!data.error){
                                            $("#customfields"+data.id).remove();
                                            Swal.fire(
                                                    'Successfully',
                                                    'The Custom Fields has now been Delete',
                                                    'success'
                                                    );


                                        }
                                        else{

                                            Swal.fire({
                                                        icon: 'error',
                                                        title: 'Oops...',
                                                        text: data.alert,
                                                        footer: ''
                                                    });
                                        }

                                    }
                                });

                        }else{
                            return false;
                        }
                });
                return false;
            });



            loadModule();
        });

        var loadModule=function(){
            fetchModuleSettings('0', 'simple', 'configaddons');
        };



    </script>
@endsection
