@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Tax Configuration</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Sidebar Shortcut -->
                     
                    <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Tax Configuration</h4>
                                    </div>
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
                                    <div class="msg-alert"></div>
                                    <nav>
                                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                            <a class="nav-item nav-link active" id="nav-general-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="true">General Settings</a>
                                            <a class="nav-item nav-link" id="nav-vat-tab" data-toggle="tab" href="#nav-vat" role="tab" aria-controls="nav-vat" aria-selected="false">VAT Settings</a>
                                            <a class="nav-item nav-link" id="nav-tax-rules-tab" data-toggle="tab" href="#nav-tax-rules" role="tab" aria-controls="nav-tax-rules" aria-selected="false">Tax Rules</a>
                                            <a class="nav-item nav-link" id="nav-advanced-tab" data-toggle="tab"  href="#nav-advanced" role="tab" aria-controls="nav-advanced" aria-selected="false">Advanced Settings</a>
                                        </div>
                                    </nav>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                    <form id="formgeneral" action="{{ url(Request::segment(1).'/setup/payments/taxconfiguration/general/store') }}" method="post" enctype="multipart/form-data">
                                        <div class="tab-content" id="nav-tabContent">
                                            {{-- General Settings --}}
                                            <div class="tab-pane fade show active" id="nav-general" role="tabpanel"
                                                aria-labelledby="nav-general-tab">
                                               
                                                <div class="general forms">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Tax Support <br> functionality</small></label>
                                                        <div class="col-sm-12 col-lg-4 pt-2">
                                                            <input type="checkbox" name="taxenabled" {{ ($config['TaxEnabled'] == 'on')?'checked':'' }}  data-toggle="toggle">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Tax Your Tax ID/VAT Number <br>
                                                            <small>The value you enter here will be displayed on
                                                                invoices.</small></label>
                                                        <div class="col-sm-12 col-lg-2 pt-2">
                                                            <input type="text" id="taxCode" name="tax_code" value="{{ $config['TaxCode'] }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Customer Tax
                                                            IDs/VAT Numbers
                                                            <br>
                                                            <small>Enable input and storage in signup and customer
                                                                profiles</small></label>
                                                        <div class="col-sm-12 col-lg-4 pt-2">
                                                            <input type="checkbox" name="tax_id_enabled" id="taxIdEnabled" value="1"  {{ ($config['TaxIDDisabled'] == 1)?'checked':'' }} data-toggle="toggle">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Taxation Type <br>
                                                            <small>Choose how you want tax to be billed</small></label>
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="taxtype" id="exampleRadios1" value="Exclusive" {{ ($config['TaxType'] == 'Exclusive')?'checked':'' }}>
                                                                <label class="form-check-label" for="exampleRadios1">Exclusive Tax - prices are entered without tax </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="taxtype" id="exampleRadios2" value="Inclusive" {{ ($config['TaxType'] == 'Inclusive')?'checked':'' }}>
                                                                <label class="form-check-label" for="exampleRadios2">Inclusive Tax - prices are entered including tax </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Custom Invoice Numbering
                                                            <br>
                                                            <small>Tick to enable Custom Invoice Number Formats for Generated Invoices</small>
                                                        </label>
                                                        <div class="col-sm-12 col-lg-3 pt-2">
                                                            <input type="checkbox" data-toggle="toggle" name="custom_invoice_numbering" id="customNumbering" value="1" {{ ($config['TaxCustomInvoiceNumbering'] == 1)?'checked':'' }}>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Custom Invoice  Numbering Format <br>  <small>The invoice number format - can contain tags which will be auto replaced</small></label>
                                                        <div class="col-sm-12 col-lg-4">
                                                            <input class="form-control"  name="custom_invoice_number_format" value="{{ $config['TaxCustomInvoiceNumberFormat'] }}" id="inputCustomFormat"/>
                                                            <small>Available Tags: {YEAR} {MONTH} {DAY} {NUMBER}</small>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Next Invoice Number <br> <small>The next invoice number that will be assigned</small></label>
                                                        <div class="col-sm-12 col-lg-2 pt-2">
                                                            <input class="form-control" type="number" name="next_custom_invoice_number" value="{{ $config['TaxNextCustomInvoiceNumber'] }}" >
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Auto Reset Numbering <br> periodically</small></label>
                                                        <div class="col-sm-12 col-lg-4 pt-2">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="custom_invoice_number_reset_frequency" id="inlineRadio1" {{ ($config['TaxAutoResetNumbering'] == '')?'checked':'' }}>
                                                                <label class="form-check-label" for="inlineRadio1">Never</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="custom_invoice_number_reset_frequency" id="inlineRadio2" value="monthly" {{ ($config['TaxAutoResetNumbering'] == 'monthly')?'checked':'' }}>
                                                                <label class="form-check-label" for="inlineRadio2">Monthly</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="custom_invoice_number_reset_frequency" id="inlineRadio3" value="annually" {{ ($config['TaxAutoResetNumbering'] == 'annually')?'checked':'' }}>
                                                                <label class="form-check-label" for="inlineRadio3">Annually</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{ csrf_field() }}
                                                
                                                    <button type="submit" class="btn btn-success px-3">Save Changes</button>
                                                    <a href="{{ url(Request::segment(1).'/setup/support/escalationrules') }}" class="btn btn-light px-3 mx-1">Cancel Changes</a>
                                                   
                                                </div>
                                            </div>
                                            {{-- VAT Settings --}}
                                            <div class="tab-pane fade" id="nav-vat" role="tabpanel"
                                                aria-labelledby="nav-vat-tab">
                                                <div class="vat-forms">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">VAT Mode <br> <small>Toggle to enable VAT taxation</small></label>
                                                        <div class="col-sm-12 col-lg-1 pt-2">
                                                            <input type="checkbox"  name="vatenabled" id="vatenabled" value="1"  {{ ($config['TaxVATEnabled'] == 1)?'checked':'' }} data-toggle="toggle">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-4 pt-3">
                                                            <a href="#">Auto Configure VAT Tax Rules</a>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">VAT Number Validation <br> <small>Validate VAT numbers during registration and checkout using the VIES web service</small></label>
                                                        <div class="col-sm-12 col-lg-4 pt-2">
                                                            <input type="checkbox"  data-toggle="toggle" name="eu_tax_validation" id="checkEUTaxValidation" value="1" {{ ($config['TaxEUTaxValidation'] == 1)?'checked':'' }} >
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Auto Tax Exempt <br> <small>Automatically set a client as tax exempt upon successful validation of VAT Number input</small></label>
                                                        <div class="col-sm-12 col-lg-1 pt-3">
                                                            <input type="hidden" name="eu_tax_exempt" value="0">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"  name="eu_tax_exempt" id="taxExempt" value="1"  {{ ($config['TaxEUTaxExempt'] == 1)?'checked':'' }} >
                                                                <label class="custom-control-label"  for="customCheck1"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Your Home Country <br> <small>The country where your business is located</small></label>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <select name="home_country" id="homeCountry" class="form-control">
                                                                @foreach($country as $k=>$v )
                                                                <option value="{{ $k }}" {{ ($config['TaxEUHomeCountry'] == $k)?'selected':'' }}  >{{ $v }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Home Country Exclusion <br> <small>Always charge VAT if the customers billing address is in your home country</small></label>
                                                        <div class="col-sm-12 col-lg-1 pt-3">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" name="home_country_exempt" id="homeCountryExclusion" value="1" {{ ($config['TaxEUHomeCountryNoExempt'] == 1)?'checked':'' }}  >
                                                                <label class="custom-control-label" for="customCheck2"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Proforma  Invoicing/Sequential Paid Invoice Numbering <br>  <small>The invoice number format - can contain tags which will be auto replaceds</small></label>
                                                        <div class="col-sm-12 col-lg-3 pt-2">
                                                            <input type="checkbox"  data-toggle="toggle"  name="sequential_paid_numbering" id="sequentialPaidNumbering" value="1" {{ ($config['SequentialInvoiceNumbering'] == 1)?'checked':'' }} >
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Sequential Invoice Number Format <br> <small>The invoice number format - can contain tags which will  be auto replaced</small></label>
                                                        <div class="col-sm-12 col-lg-4">
                                                            <input class="form-control" name="sequential_paid_format" id="sequentialPaidFormat" value="{NUMBER}" />
                                                            <small>Available Tags: {YEAR} {MONTH} {DAY} {NUMBER}</small>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Next Paid Invoice Number <br> <small>The next invoice number that will be  assigned</small></label>
                                                        <div class="col-sm-12 col-lg-2 pt-2">
                                                            <input class="form-control" type="number"  name="next_paid_invoice_number" id="nextPaidNumber" value="{{ $config['SequentialInvoiceNumberValue'] }}" placeholder="1"/>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-4 col-form-label">Auto Reset Numbering <br> <small>Allows you to automatically reset the invoice number periodically</small></label>
                                                        <div class="col-sm-12 col-lg-4 pt-2">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="paid_invoice_number_reset_frequency" id="inlineRadio1" value="" {{ ($config['TaxAutoResetPaidNumbering'] == '')?'checked':'' }}>
                                                                <label class="form-check-label" for="inlineRadio1">Never</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" id="inlineRadio2"  name="paid_invoice_number_reset_frequency" value="monthly" {{ ($config['TaxAutoResetPaidNumbering'] == 'monthly')?'checked':'' }} >
                                                                <label class="form-check-label" for="inlineRadio2">Monthly</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"  name="paid_invoice_number_reset_frequency" id="inlineRadio3" value="annually" {{ ($config['TaxAutoResetPaidNumbering'] == 'annually')?'checked':'' }} >
                                                                <label class="form-check-label" for="inlineRadio3">Annually</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-4 col-form-label">Set Invoice Date on Payment
                                                        <br>
                                                        <small>Set the invoice date to the current date upon payment</small></label>
                                                    <div class="col-sm-12 col-lg-4">
                                                        <div class="col-sm-12 col-lg-1 pt-3">
                                                            <input type="hidden" name="set_invoice_date" value="0">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" name="set_invoice_date" id="setInvoiceDate" value="1" {{ ($config['TaxSetInvoiceDateOnPayment'] == 1)?'checked':'' }}>
                                                                <label class="custom-control-label" for="customCheck3"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-success px-3">Save Changes</button>
                                                <a href="{{ url(Request::segment(1).'/setup/support/escalationrules') }}" class="btn btn-light px-3 mx-1">Cancel Changes</a>
                                            </div>
                                            {{-- Tax Rules --}}
                                            <div class="tab-pane fade" id="nav-tax-rules" role="tabpanel"
                                                aria-labelledby="nav-tax-rules-tab">
                                                <div class="tax-rules-forms">
                                                    <div class="card border">
                                                        <div class="card-header">
                                                            <h5>Quick Add</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-sm-12 col-lg-4">
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-3 col-form-label">Name</label>
                                                                        <div class="col-sm-12 col-lg-7">
                                                                            <input type="text" class="form-control" placeholder="Tax" id="ruleName" name="name">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-12 col-lg-4">
                                                                    <div class="form-group row">
                                                                        <label class="col-sm-12 col-lg-3 col-form-label">Tax Rate</label>
                                                                        <div class="col-sm-12 col-lg-7">
                                                                            <div class="input-group mb-3">
                                                                                <input type="number" class="form-control"  id="taxRate" name="taxrate" aria-label="Amount (to the nearest dollar)" value="0.00">
                                                                                <div class="input-group-append">
                                                                                    <span class="input-group-text">%</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-12 col-lg-4">
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-3 col-form-label">Level</label>
                                                                        <div class="col-sm-12 col-lg-7">
                                                                            <select id="taxLevel" name="level" class="form-control">
                                                                                <option value="1">Level 1</option>
                                                                                <option value="2">Level 2</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-4 col-form-label">Country<br>
                                                                            <small>The country which the tax rule will be
                                                                                applied</small></label>
                                                                        <div class="col-sm-12 col-lg-8">
                                                                            <div class="row">
                                                                                <div class="col-lg-12">
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input" type="radio" name="countrytype" id="exampleRadios1" value="any" checked>
                                                                                        <label class="form-check-label" for="exampleRadios1"> Apply Rule to All Countries </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-sm-12 col-lg-3 pt-2">
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input"  type="radio" name="countrytype" id="exampleRadios2" value="specific">
                                                                                        <label class="form-check-label" for="exampleRadios2">
                                                                                            Apply Rule to Specific Country
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-sm-12 col-lg-6">
                                                                                    <select class="form-control" name="country" id="country">
                                                                                        @foreach($country as $k=>$v )
                                                                                        <option value="{{ $k }}">{{ $v }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-4 col-form-label">State/Region<br>
                                                                            <small>The state which the tax rule will be
                                                                                applied</small></label>
                                                                        <div class="col-sm-12 col-lg-8">
                                                                            <div class="row">
                                                                                <div class="col-lg-12">
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input" type="radio" name="statetype" id="exampleRadios1" value="any" checked>
                                                                                        <label class="form-check-label" for="exampleRadios1"> Apply Rule to All States </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-sm-12 col-lg-3 pt-2">
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input" type="radio" name="statetype" id="exampleRadios2"  value="specific">
                                                                                        <label class="form-check-label" for="exampleRadios2">  Apply Rule to Specific States  </label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-sm-12 col-lg-6">
                                                                                    <input type="text" name="state" class="form-control" data-selectinlinedropdown="1" id="stateinput">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex mt-3">
                                                                        <button id="addrule"  class="btn btn-success mx-auto px-5">Add Rule</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <nav>
                                                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                                    <a class="nav-item nav-link active" id="nav-lvl-1-tab" data-toggle="tab" href="#nav-lvl-1" role="tab" aria-controls="nav-lvl-1" aria-selected="true">Level
                                                                        1 Rules
                                                                    </a>
                                                                    <a class="nav-item nav-link" id="nav-lvl-2-tab" data-toggle="tab" href="#nav-lvl-2" role="tab" aria-controls="nav-lvl-2" aria-selected="false">
                                                                        Level 2 Rules
                                                                    </a>

                                                                </div>
                                                            </nav>
                                                            <div class="tab-content" id="nav-tabContent">
                                                                <div class="tab-pane fade show active" id="nav-lvl-1"
                                                                    role="tabpanel" aria-labelledby="nav-lvl-1-tab">
                                                                    <div class="table-responsive">
                                                                        <table id="tablelevel1" class="table tabel-borderless">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Name</th>
                                                                                    <th>Country</th>
                                                                                    <th>State/Region</th>
                                                                                    <th>Tax Rate</th>
                                                                                    <th></th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($tax['level1'] as $r)
                                                                                    <tr id="table{{ $r['id'] }}" >
                                                                                        <td>{{ $r['name'] }}</td>
                                                                                        <td>{{ $r['country'] }}</td>
                                                                                        <td>{{ $r['state'] }}</td>
                                                                                        <td>{{ $r['taxrate'] }}</td>
                                                                                        <td>
                                                                                            <button type="button" data-id="{{ $r['id'] }}" data-title="{{ $r['name'] }}" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                <div class="tab-pane fade" id="nav-lvl-2" role="tabpanel"aria-labelledby="nav-lvl-2-tab">
                                                                    <div class="table-responsive">
                                                                        <table id="tablelevel2" class="table tabel-borderless">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Name</th>
                                                                                    <th>Country</th>
                                                                                    <th>State/Region</th>
                                                                                    <th>Tax Rate</th>
                                                                                    <th></th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($tax['level2'] as $r)
                                                                                    <tr id="table{{ $r['id'] }}">
                                                                                        <td>{{ $r['name'] }}</td>
                                                                                        <td>{{ $r['country'] }}</td>
                                                                                        <td>{{ $r['state'] }}</td>
                                                                                        <td>{{ $r['taxrate'] }}</td>
                                                                                        <td>
                                                                                            <button type="button" data-id="{{ $r['id'] }}" data-title="{{ $r['name'] }}" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                                                                                        </td>
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
                                            {{-- Advanced Settings --}}
                                            <div class="tab-pane fade" id="nav-advanced" role="tabpanel"
                                                aria-labelledby="nav-advanced-tab">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-4 col-form-label">Taxed items <br> <small>Choose which products include tax Products & Addons are set per item</small>
                                                            </label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="taxdomains" id="customCheck4" {{ ($config['TaxDomains'] == 'on')?'checked':'' }} >
                                                                    <label class="custom-control-label" for="customCheck4">Domains</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input"  name="taxbillableitems" id="customCheck5" {{ ($config['TaxBillableItems'] == 'on')?'checked':'' }}>
                                                                    <label class="custom-control-label" for="customCheck5">Billable Items</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="taxlatefee" id="customCheck6" {{ ($config['TaxLateFee'] == 'on')?'checked':'' }}>
                                                                    <label class="custom-control-label" for="customCheck6">Late Fees</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="taxcustominvoices" id="customCheck7" {{ ($config['TaxCustomInvoices'] == 'on')?'checked':'' }}>
                                                                    <label class="custom-control-label" for="customCheck7">Custom Invoices</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-4 col-form-label">Calculation Mode <br> <small>Choose how you want tax to be calculated</small></label>
                                                            <div class="col-sm-12 col-lg-6 pt-2">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="taxperlineitem" id="exampleRadios1"  value="1" {{ ($config['TaxPerLineItem'] == 1)?'checked':'' }}>
                                                                    <label class="form-check-label" for="exampleRadios1"> Calculate individually per line item</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="taxperlineitem"   id="exampleRadios2" value="0" {{ ($config['TaxPerLineItem'] == 0)?'checked':'' }}>
                                                                    <label class="form-check-label" for="exampleRadios2"> Calculate based on collective sum of the taxable line items </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-4 col-form-label">Compound Tax <br> <small>Enable level 2 taxes being applied to level 1 taxes</small></label>
                                                            <div class="col-sm-12 col-lg-1 pt-2">
                                                                <input type="checkbox" name="taxl2compound" data-toggle="toggle" id="compoundTax" {{ ($config['TaxL2Compound'] == 'on')?'checked':'' }}>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-4 col-form-label">Deduct Tax Amount <br>
                                                                <small>Deduct calculated tax amount when no tax rules are met</small></label>
                                                            <div class="col-sm-12 col-lg-1 pt-2">
                                                                <input type="checkbox" name="taxinclusivededuct" data-toggle="toggle" id="inclusiveDeduct" {{ ($config['TaxInclusiveDeduct'] == 'on')?'checked':'' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-success px-3">Save Changes</button>
                                                <a href="{{ url(Request::segment(1).'/setup/support/escalationrules') }}" class="btn btn-light px-3 mx-1">Cancel Changes</a>
                                            </div>
                                        </div>
                                        </fomr>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End MAIN CARD -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ Theme::asset('assets/libs/bootstrap-switch-custom/bootstrap4-toggle.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {

            $('select').select2();

            $("#formgeneral").submit(function(){
                $('.msg-alert').html('');
                 $("#formgeneral button[type='submit']").prop('disabled',true);

                $.ajax({
                    type: 'POST',
                    url:  "{{ url(Request::segment(1).'/setup/payments/taxconfiguration/general/store') }}",
                    data: $("#formgeneral").serialize(),
                    dataType: 'json',
                    success: function(data){
                        if(!data.error){
                            $(":input","#formgeneral")
                            .not(":button, :submit, :reset, :hidden")
                            .val("")
                            .removeAttr("checked")
                            .removeAttr("selected");
                            $("#formgeneral button[type='submit']").removeAttr('disabled');
                            Swal.fire(
                                    'Changes Saved Successfully!',
                                    'Your changes have been saved.',
                                    'success'
                                    );
                        }
                        else{
                            $("#formgeneral button[type='submit']").removeAttr('disabled');
                            Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: data.alert,
                                        footer: ''
                                    });
                        }
                    
                    }
                });
                return false;
         });

        
        $('.table').on('click', '.delete', function (){ 
                Swal.fire({
                    title: "Delete",
                    text: "Click ok if you are sure you want to delete this tax rule",
                    icon: "warning",
                    showCancelButton:true,
                    cancelButtonColor: '#d33',
                    buttons: true,
                    dangerMode: true,
                })
                .then((value) => {
                    if(value.isConfirmed){
                        //$('#fd'+$(this).data('id')).submit();
                        var id=$(this).data('id');
                        $.ajax({
                            type: 'POST',
                            url:  "{{ url(Request::segment(1).'/setup/payments/taxconfiguration/destroy') }}",
                            data: {id:id,_method:'DELETE'},
                            headers : {
                                            'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                                        },
                            dataType: 'json',
                            success: function(data){
                                if(!data.error){
                                    $('#table'+id).remove();
                                    $("#formgeneral button[type='submit']").removeAttr('disabled');
                                    Swal.fire(
                                            'Changes Saved Successfully!',
                                            'Your changes have been saved.',
                                            'success'
                                            );
                                }
                                else{
                                    //$("#formgeneral button[type='submit']").removeAttr('disabled');
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


        //addrule
        $( "#addrule" ).click(function() {
            $('.msg-alert').html('');
            $("#addrule").prop('disabled',true);
            $.ajax({
                type: 'POST',
                url:  "{{ url(Request::segment(1).'/setup/payments/taxconfiguration/rule/store') }}",
                data: $("#formgeneral").serialize(),
                dataType: 'json',
                success: function(data){
                    if(!data.error){
                        /* $(":input","#formgeneral")
                        .not(":button, :submit, :reset, :hidden")
                        .val("")
                        .removeAttr("checked")
                        .removeAttr("selected");
                        $("#addrule").removeAttr('disabled'); */
                        $('#ruleName').val();
                        $('#taxRate').val('0.00');
                        $('#taxLevel').val(1);

                        var html=`
                                    <tr id="table`+data.data.id+`">
                                        <td>`+data.data.name+`</td>
                                        <td>`+data.data.country+`</td>
                                        <td>`+data.data.State+`</td>
                                        <td>`+data.data.taxrate+`</td>
                                        <td>
                                            <button type="button" data-id="`+data.data.id+`" data-title="`+data.data.name+`" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                `;

                        $('#tablelevel'+data.data.level+' tbody').append(html);
                        $("#addrule").removeAttr('disabled');
                        Swal.fire(
                                'Changes Saved Successfully!',
                                'Your changes have been saved.',
                                'success'
                                );
                    }
                    else{
                        $("#addrule").removeAttr('disabled');
                        Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.alert,
                                    footer: ''
                                });
                    }
                }
            });
            return false;
        });



        });
    </script>
@endsection
