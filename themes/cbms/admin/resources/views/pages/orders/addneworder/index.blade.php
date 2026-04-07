@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Add New Order</title>
@endsection

@section('styles')
    <style>
        /* --------------------------------------------------------------
        ORDER SUMMARY
        -------------------------------------------------------------- */
        .ordersummarytitle {
            font-family:Arial;
            font-size:20px;
            text-align:center;
        }
        .ordersummaryleftcol {
            width:60%;
            min-width:600px;
        }
        #ordersummary {
            margin: 20px 0 10px 0;
            padding: 0;
            border: 1px solid #B4B4B4;
            -moz-border-radius: 6px;
            -webkit-border-radius: 6px;
            -o-border-radius: 6px;
            border-radius: 6px;
            -moz-box-shadow: 0px 0px 20px #000;
            -webkit-box-shadow: 0px 0px 20px #ccc;
            -o-box-shadow: 0px 0px 20px #ccc;
            box-shadow: 0px 0px 20px #ccc;
        }
        #ordersummary table {
            width: 100%;
        }
        #ordersummary tr td {
            padding: 3px 10px;
        }
        #ordersummary tr.item td {
            padding: 10px 10px 12px 10px;
            border-bottom: 1px dashed #B4B4B4;
        }
        #ordersummary div.itemtitle {
            font-size: 16px;
        }
        .itemtitle {
            font-size: 16px;
        }
        #ordersummary div.itempricing {
            text-align: right;
            font-size: 20px;
        }
        #ordersummary td.alnright {
            text-align: right;
        }
        #ordersummary tr.subtotal td {
            background-color: #FFFFDF;
            font-size: 14px;
            padding: 4px 10px;
        }
        #ordersummary tr.promo td {
            background-color: #FFE1E1;
            font-size: 12px;
            padding: 4px 10px;
        }
        #ordersummary tr.tax td {
            background-color: #E6F7FF;
            font-size: 12px;
            padding: 4px 10px;
        }
        #ordersummary tr.total td {
            background-color: #E7FFDA;
            font-size: 26px;
            padding: 6px 10px;
        }
        #ordersummary tr.recurring td {
            background-color: #FFE1E1;
            font-size: 12px;
            padding: 4px 10px;
        }

        #ordersummary .apply-credit-container {
            margin: 15px 0;
            padding: 15px;
            background-color: #f2f2f2;
        }
        #ordersummary .apply-credit-container span {
            font-weight: bold;
        }
        #ordersummary .apply-credit-container .radio {
            padding-left: 20px;
            padding-right: 20px;
            font-weight: normal;
        }
        #createPromoCode {
            pointer-events: all !important;
        }
    </style>
    
@endsection
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                     
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="card-title mb-3">
                                <h4>Add New Order</h4>
                            </div>
                            @if (session('message'))
                                <div class="alert alert-{{ session('type') }}">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    <strong>{!! session('message') !!}</strong>
                                </div>
                            @endif
                            <form action="" method="post" enctype="multipart/form-data" autocomplete="off" id="orderfrm" onsubmit="submitOrder(); return false;">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="card border p-3">
                                            {{-- <form action="/" method="POST" enctype="multipart/form-data" autocomplete="off" id="form-submit-order">
                                                @csrf --}}
                                                <div class="form-group row">
                                                    <label for="clientName" class="col-sm-3 col-form-label">Client</label>
                                                    <div class="col-sm-9">
                                                        <select name="userid" id="search_client" onchange="setClientSession(this);" class="form-control select2-limiting" style="width: 100%" required>
                                                            @if (isset($client) && $client)
                                                                <option value="{{ $client->id }}" selected="selected"><strong>{{ "$client->firstname $client->lastname $client->companyname" }}</strong> #{{ $client->id }}<br /> <span>{{ $client->email }}</span></option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="paymentmethod" class="col-sm-3 col-form-label">Payment Method</label>
                                                    <div class="col-sm-9">
                                                        <select class="select2-search-disable form-control" name="paymentmethod" id="payment-method" style="width: 100%;" required>
                                                            @foreach($gateway as $k => $v)
                                                                <option value="{{ $k }}">{{ $v }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="promodd" class="col-sm-3 col-form-label">Promotion Code</label>
                                                    <div class="col-sm-6">
                                                        <select class="select2-limiting form-control" name="promocode" id="promodd" onchange="updatesummary()" style="width: 100%;">
                                                            <option value="">None</option>
                                                            <optgroup label="Active Promotions">
                                                                {!! $activePromo !!}
                                                            </optgroup>
                                                            <optgroup label="Expired Promotions">
                                                                {!! $inactivePromo !!}
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <button type="button" class="btn btn-primary btn-sm btn-block mt-1" id="createPromoCode" data-toggle="modal" data-target="#modalCreatePromo" data-backdrop="static">Create Custom Promo</button>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="orderstatus" class="col-sm-3 col-form-label">Order Status</label>
                                                    <div class="col-sm-3">
                                                        <select class="select2-search-disable form-control" name="orderstatus" id="orderstatus" style="width: 100%;" required>
                                                            <option value="Pending">Pending</option>
                                                            <option value="Active">Active</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="orderStatus" class="col-sm-3 col-form-label"></label>
                                                    <div class="col-sm-9">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="adminorderconf" id="adminorderconf" value="1" checked>
                                                            <label class="form-check-label" for="adminorderconf">Order Confirmation</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="admingenerateinvoice" id="admingenerateinvoice" value="1" checked>
                                                            <label class="form-check-label" for="admingenerateinvoice">Generate Invoice</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="adminsendinvoice" id="adminsendinvoice" value="1" checked>
                                                            <label class="form-check-label" for="adminsendinvoice">Send Email</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            {{-- </form> --}}
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12" id="products">
                                                <div class="product" id="ord0">
                                                    <h4 class="card-title my-3"><strong>Product/Service</strong></h4>
                                                    <div class="card border mb-2 p-3">
                                                        <div class="form-group row">
                                                            <label for="pid0" class="col-sm-3 col-form-label">Product/Service</label>
                                                            <div class="col-sm-9">
                                                                <select name="pid[]" id="pid0" class=" form-control" onchange="loadproductoptions(this)" style="width: 100%;" required>
                                                                    {!! $products !!}
                                                                </select>
                                                                <div class="text-info mt-2 prod-loader" id="prod-loader0" hidden> 
                                                                    Loading...
                                                                    <img class="ml-0" src="{{ Theme::asset('img/loading.gif') }}" id="prod-loader0" alt="loading" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="domain0" class="col-sm-3 col-form-label">Domain</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="domain[]" id="domain0" data-index="0" onkeyup="handleProductDomainInput(this)" class="form-control input-reg-domain">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="billingcycle0" class="col-sm-3 col-form-label">Billing Cycle</label>
                                                            <div class="col-sm-3">
                                                                <select class=" form-control" name="billingcycle[]" id="billingcycle0" onchange="updatesummary();loadproductoptions(jQuery('#pid' + this.id.substring(12))[0]);return false;" style="width: 100%;" required>
                                                                    {!! $cycles !!}
                                                                </select>
                                                            </div>
                                                        </div>
    
                                                        <div class="addonsrow" id="addonsrow0" style="display:none;">
                                                            {{-- TODO Addoonsrow --}}
                                                        </div>
    
                                                        <div class="form-group row">
                                                            <label for="qty0" class="col-sm-3 col-form-label">Quantity</label>
                                                            <div class="col-sm-3">
                                                                <input type="number" min="1" step="1" name="qty[]" value="1" id="qty0" onkeyup="updatesummary()" class="form-control input-reg-qty" required>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row align-items-center">
                                                            <label for="priceoverride0" class="col-sm-3 col-form-label">Price Override</label>
                                                            <div class="col-sm-3">
                                                                <input type="text" name="priceoverride[]" id="priceoverride0" onkeyup="updatesummary();" class="form-control input-reg-priceoveride">
                                                            </div>
                                                            <small>(Only enter to manually override default product pricing)</small>
                                                        </div>

                                                        <div class="card border mb-2 p-3 productconfigoptions" id="productconfigoptions0" style="display: none;">
                                                            {{-- Dynamic Input Field --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 py-0">
                                                <button type="button" class="btn btn-outline-success btn-block addproduct" id="addproduct" onclick="addProduct();">
                                                    <i class="fa fa-plus-circle mr-2" aria-hidden="true"></i>
                                                    Add Another Product
                                                </button>
                                            </div>
                                            <hr>
                                            <div class="col-lg-12 mt-3" id="domains">
                                                <h4 class="card-title my-3"><strong>Domain Registration</strong></h4>
                                                <div class="card border mb-2 p-3 tbl-domain-config domain" id="ord-domain0" domain-counter="0">
                                                    <div class="form-group row align-items-center mt-2 p-0">
                                                        <label for="regType" class="col-sm-3 col-form-label">Registration Type</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input domain-reg-action" type="radio" name="regaction[0]" id="inputDomainRegActionNone0" data-index="0" value="" onclick="loaddomainoptions(this);updatesummary()" checked>
                                                                <label class="form-check-label" for="inputDomainRegActionNone0">None</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input domain-reg-action" type="radio" name="regaction[0]" id="inputDomainRegActionRegister0" data-index="0" value="register" onclick="loaddomainoptions(this);updatesummary()">
                                                                <label class="form-check-label" for="inputDomainRegActionRegister0">Registration</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input domain-reg-action" type="radio" name="regaction[0]" id="inputDomainRegActionTransfer0" data-index="0" value="transfer" onclick="loaddomainoptions(this);updatesummary()">
                                                                <label class="form-check-label" for="inputDomainRegActionTransfer0">Transfer</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="domain-input-container0">
                                                        <div class="form-group row">
                                                            <label for="inputDomainRegDomain0" class="col-sm-3 col-form-label">Domain</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="regdomain[]" id="inputDomainRegDomain0" data-index="0" data-manual-input="0" onkeyup="$(this).prop('data-manual-input', 1); handleDomainRegInput(this);" class="form-control input-reg-domain">
                                                                <small class="text-warning required-field-indication" id="spanRequiredFields0" style="display: none;">* Indicates a required field.</small>
                                                                <small class="text-danger invalid-tld" id="spanInvalidTld0" style="display: none;">TLD/Extension not configured for sale. Please check your input before continuing.</small>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="inputDomainRegPeriod0" class="col-sm-3 col-form-label">Registration Period</label>
                                                            <div class="col-sm-9">
                                                                <select class="form-control" name="regperiod[]" id="inputDomainRegPeriod0" onchange="updatesummary()" style="width: 100%;">
                                                                    @php
                                                                        $regperiods = $regperiodss = "";
                                                                        for ($regperiod = 1; $regperiod <= 10; $regperiod++) {
                                                                            $regperiods .= "<option value=\"" . $regperiod . "\">" . $regperiod . " " . __("admin.domainsyear" . $regperiodss) . "</option>";
                                                                            $regperiodss = "s";
                                                                        }
                                                                    @endphp
                                                                    {!! $regperiods !!}
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row" id="inputDomainRegEppCode0">
                                                            <label for="inputDomainRegEppCode0" class="col-sm-3 col-form-label">EPP Code</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="eppcode[]" id="inputDomainRegEppCode0" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row align-items-center mt-2 p-0">
                                                            <label for="#" class="col-sm-3 col-form-label">Domain Addons</label>
                                                            <div class="col-sm-9">
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input domain-reg-dnsmanagement" type="checkbox" name="dnsmanagement[0]" id="inputDomainRegDnsManagement0" onclick="updatesummary()">
                                                                    <label class="form-check-label" for="inputDomainRegDnsManagement0">DNS Management </label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input domain-reg-emailforwarding" type="checkbox" name="emailforwarding[0]" id="inputDomainRegEmailForwarding0" onclick="updatesummary()">
                                                                    <label class="form-check-label" for="inputDomainRegEmailForwarding0">Email Forwarding </label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input domain-reg-idprotection" type="checkbox" name="idprotection[0]" id="inputDomainRegIdProtection0" onclick="updatesummary()">
                                                                    <label class="form-check-label" for="inputDomainRegIdProtection0">ID Protection</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row align-items-center">
                                                            <label for="inputDomainRegPriceOverride0" class="col-sm-3 col-form-label">Registration Price Override</label>
                                                            <div class="col-sm-3">
                                                                <input type="text" name="domainpriceoverride[0]" id="inputDomainRegPriceOverride0" data-manual-input="0" oninput="updatesummary();" class="form-control domain-reg-priceoverride">
                                                            </div>
                                                            <small>(Only enter to manually override default pricing)</small>
                                                        </div>
                                                        <div class="form-group row align-items-center">
                                                            <label for="inputDomainRenewPriceOverride0" class="col-sm-3 col-form-label">Renewal Price Override</label>
                                                            <div class="col-sm-3">
                                                                <input type="text" name="domainrenewoverride[0]" id="inputDomainRenewPriceOverride0" data-manual-input="0" oninput="updatesummary();" class="form-control domain-reg-renewoverride">
                                                            </div>
                                                            <small>(Only enter to manually override default pricing)</small>
                                                        </div>

                                                        <div class="domain-addt-fields" id="domain-addt-fields0">

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <button type="button" class="btn btn-outline-success btn-block" id="adddomain" onclick="addDomain();">
                                                    <i class="fa fa-plus-circle mr-2" aria-hidden="true"></i>
                                                    Add Another Domain
                                                </button>
                                            </div>
                                            <div class="col-lg-12 mt-3" id="domainContactContainer" style="display:none;">
                                                <h4 class="card-title my-3"><strong>Domain Registration Contact</strong></h4>
                                                <label class="">Defines the name and address information to use for all domain registrations within this order. If you need to add a new contact, <a href="#" id="linkAddContact">create the contact first</a> and then begin the order again.</label>
                                                <div class="card border mb-0 p-3">
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label">Choose Contact</label>
                                                        <div class="col-sm-9">
                                                            <select class="select2-search-disable form-control" name="contactid" id="inputContactID" style="width: 100%;">

                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card border p-3" id="div-ordersumm">
                                                    <h4 class="card-title mb-3 ordersummarytitle">
                                                        Order Summary
                                                        <img class="ml-0" src="{{ Theme::asset('img/loading.gif') }}" id="loaderToggle" alt="loading" hidden>
                                                    </h4>
                                                    <div class="card border">
                                                        <div class="card-body border-bottom">
                                                            <h4 class="text-center bg-white itemtitle">No Items Selected</h4>
                                                        </div>
                                                        {{-- <div class="card-Body">
                                                            <div class="tabel-responsive">
                                                                <table class="table table-borderless table-sm">
                                                                    <tbody>
                                                                        <tr class="table-warning font-size-18">
                                                                            <td>Sub Total</td>
                                                                            <td class="text-right">Rp. 0.00</td>
                                                                        </tr>
                                                                        <tr class="table-info font-size-14">
                                                                            <td>PPn @ 10%</td>
                                                                            <td class="text-right">Rp. 0.00</td>
                                                                        </tr>
                                                                        <tr class="table-success font-size-24">
                                                                            <td>Total</td>
                                                                            <td class="text-right">Rp. 0.00</td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div> --}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <button type="submit" class="btn btn-success btn-block p-3 font-size-18" id="btn-submit-order">
                                                    Place Order
                                                    <img class="ml-2" src="{{ Theme::asset('img/loading.gif') }}" id="submit-promo-loader" alt="loading" hidden>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--  Modal Merge Client -->
    <div class="modal fade bs-example-modal-md" tabindex="-1" role="dialog" id="modalCreatePromo" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form action="" method="POST" enctype="multipart/form-data" id="form-create-promo" autocomplete="off" onsubmit="createPromo(); return false;">
                    <input type="hidden" name="action" value="createpromo">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0" id="myLargeModalLabel">Create Custom Promo<br>
                            <p></p>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card p-3">
                            <div class="form-group row">
                                <label class="col-sm-12 col-lg-3 col-form-label">Promotion Code</label>
                                <div class="col-sm-12 col-lg-9">
                                    <input type="text" name="code" id="promocode" class="form-control" placeholder="Promotion Code" required />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-lg-3 col-form-label">Type</label>
                                <div class="col-sm-12 col-lg-9">
                                    <select name="type" id="ptype" class="form-control select2-search-disable" style="width: 100%" required>
                                        <option value="Percentage">Percentage</option>
                                        <option value="Fixed Amount">Fixed Amount</option>
                                        <option value="Price Override">Price Override</option>
                                        <option value="Free Setup">Free Setup</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-lg-3 col-form-label">Value</label>
                                <div class="col-sm-12 col-lg-9">
                                    <input type="number" name="pvalue" id="pvalue" min="0" placeholder="Promotion Value" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-lg-3 col-form-label">Recuring</label>
                                <div class="col-sm-12 col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-5 d-flex align-items-center ">
                                            <div class="form-check form-check-inline ">
                                                <input class="form-check-input" type="checkbox" name="recurring" id="recurring" value="1">
                                                <label class="form-check-label" for="recurring" style="font-size: 12px">Enable - Recur For</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-7 d-flex align-items-center ">
                                            <div class="form-check form-check-inline">
                                                <input type="number" name="recurfor" id="recurfor" min="0" step="1" class="form-control" value="0">&nbsp;&nbsp;
                                                <label class="form-check-label" for="recurfor" style="font-size: 12px"> Times (0 = Unlimited)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p>* Promotional Discounts created "on the fly" here apply to all items in an order</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <img class="ml-2" src="{{ Theme::asset('img/loading.gif') }}" id="create-promo-loader" alt="loading" hidden>
                        <button type="button" class="btn btn-light waves-effect" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary waves-effect waves-light" id="btn-create-promo" >Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <!-- Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/helpers/select2-utils.js') }}"></script>

    <script type="text/javascript">

        let summaryUpdateTimeoutId = 0;
        let domainUpdateTimeoutId = 0;
        const maxClonedField = 10;
        const actionURL = "{!! route('admin.pages.orders.addneworder.actionCommand') !!}";

        // Product/Service form cloned
        let prodtemplate = $("#products .product:first");
        let productsCount = 0;

        // Domain form cloned
        let domaintemplate = $("#domains .domain:first");
        let domainConfigCount = 0;

        const searchEl = $("#search_client");
        const searchURL = "{!! route('admin.pages.clients.viewclients.clientsummary.searchClient') !!}";

        $(() => {
            searchClient(searchEl, searchURL);
            updatesummary();
            loaddomainoptions($(`#inputDomainRegActionNone${domainConfigCount}`));
        });

        const submitOrder = async (params = {}) => {
            disableFormElements(true); // Disable form elements
            if (!$("#search_client").val()) {
                Toast.fire({ icon: 'warning', title: "Please select or search the client to make cart calculation!", });
                disableFormElements(false); // Re-enable form elements
                return false;
            }
            
            const url = actionURL;
            const formData = $('#orderfrm').serializeJSON();
            const payloads = { 
                ...formData,
                ...params,
                action: "submitorder", 
                calconly: false,
            };

            options.body = JSON.stringify(payloads);

            $("#submit-promo-loader").removeAttr("hidden");
            $("#btn-submit-order").attr({ "disabled": true }).text("Loading...");

            const response = await cbmsPost(url, options);
            
            $("#submit-promo-loader").attr({ "hidden": true});
            $("#btn-submit-order").attr({ "disabled": false }).text("Place Order");

            if (response) {    
                const { result, message, data = null } = response;

                if (result == 'error') {
                    if (data !== null && data.containInvalid) {
                        Swal.fire({
                            title: data.title,
                            html: data.message,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Submit Order",
                        }).then((response) => {
                            if (response.isConfirmed) {
                                submitOrder({ forceSubmit: true });
                            }
                        }).catch(swal.noop);

                        disableFormElements(false); // Re-enable form elements
                        return false;
                    }

                    Toast.fire({ icon: 'error', title: message, });
                    disableFormElements(false); // Re-enable form elements
                    return false;
                }

                Toast.fire({ icon: 'success', title: message, });

                window.location.href = data.redirect;

                disableFormElements(false); // Re-enable form elements
                return true;
            }

            console.log(`submitOrder: Failed to fetch data. Response: ${response}`);
            disableFormElements(false); // Re-enable form elements
        }

        const disableFormElements = (disable) => {
            $('#orderfrm :input').prop('disabled', disable);
        }

        const updatesummary = async () => {
            if (!$("#search_client").val()) {
                // Toast.fire({ icon: 'warning', title: "Please select or search the client to make cart calcluation!", });
                return false;
            }

            if (summaryUpdateTimeoutId) {
                clearTimeout(summaryUpdateTimeoutId);
                summaryUpdateTimeoutId = 0;
            }

            summaryUpdateTimeoutId = setTimeout(async function() {
                const url = actionURL;
                const formData = $('#orderfrm').serializeJSON();
                const payloads = {
                    ...formData,
                    action: "submitorder",
                    calconly: true,
                }
                options.body = JSON.stringify(payloads);

                $("#loaderToggle").removeAttr("hidden");
                
                const response = await cbmsPost(url, options);

                $("#loaderToggle").attr({ "hidden": true});
                if (response) {    
                    const { result, message, data } = response;

                    if (result == 'error') {
                        Toast.fire({ icon: 'error', title: message, });
                        return false;
                    }

                    $("#div-ordersumm").html(data.body);

                    return true;
                }

                console.log(`updatesummary: Failed to fetch data. Response:  ${response}`);
            }, 500);
        }

        const setClientSession = async (el) => {
            disableFormElements(true); // Disable form elements
            const url = actionURL;
            const payloads = {
                userid: $(el).val(),
                action: "setclientsession",
            }
            options.body = JSON.stringify(payloads);

            const response = await cbmsPost(url, options);

            if (response) {
                // Handle response
            }

            loadDomainContactOptions();
            disableFormElements(false); // Re-enable form elements
            console.log(`setClientSession: ${response}`);
        } 

        const loadproductoptions = async (piddd) => {
            let ord = piddd.id.substring(3);
            let pid = piddd.value;
            let billingcycle = $("#billingcycle" + ord).val();

            $("#productconfigoptions" +ord).html("");
            $("#productconfigoptions" +ord).hide();
            $("#addonsrow" +ord).hide();
            if (pid == 0) {
                $("#productconfigoptions" +ord).html("");
                $("#productconfigoptions" +ord).hide();
                $("#addonsrow" +ord).hide();
                updatesummary();
            } else {
                const url = actionURL;
                const payloads = { 
                    action: "getconfigoptions", 
                    pid: pid, 
                    cycle: billingcycle, 
                    orderid: ord, 
                };

                options.body = JSON.stringify(payloads);

                $("#prod-loader"+ord).removeAttr("hidden");
                
                const response = await cbmsPost(url, options);
                
                $("#prod-loader"+ord).attr({ "hidden": true});
                
                if (response) {    
                    const { result, message, data } = response;

                    if (result == 'error') {
                        Toast.fire({ icon: 'error', title: message, });
                        return false;
                    }

                    if (data.addons) {
                        $("#addonsrow"+ord).show();
                        $("#addonsrow"+ord).html(data.addons);
                    } else {
                        $("#addonsrow"+ord).hide();
                        $("#addonsrow"+ord).html("");
                    }

                    if (data.options) {
                        $("#productconfigoptions"+ord).html(data.options);
                        $("#productconfigoptions"+ord).show();
                    }

                    updatesummary();

                    return true;
                }

                console.log(`loadproductoptions: Failed to fetch data. Response: ${response}`);
            }
        }

        const createPromo = async () => {
            const url = actionURL;
            const formData = $('#form-create-promo').serializeJSON();
            const payloads = { 
                ...formData,
                action: "createpromo", 
            };

            options.body = JSON.stringify(payloads);

            $("#create-promo-loader").removeAttr("hidden");
            $("#btn-create-promo").attr({ "disabled": true }).text("Loading...");

            const response = await cbmsPost(url, options);
            
            $("#create-promo-loader").attr({ "hidden": true});
            $("#btn-create-promo").attr({ "disabled": false }).text("Save changes");

            if (response) {    
                const { result, message, data } = response;

                if (result == 'error') {
                    Toast.fire({ icon: 'error', title: message, });
                    return false;
                }

                Toast.fire({ icon: 'success', title: message, });
                
                $('#form-create-promo')[0].reset();
                $('#ptype').val("Percentage").trigger('change');
                $("#modalCreatePromo").modal("hide");

                $("#promodd").append(data.option);
                $("#promodd").val(data.selected);

                return true;
            }

            console.log(`createPromo: Failed to fetch data. Response: ${response}`);
        }

        const loadDomainContactOptions = async () => {
            let hasDomainReg = false;
            $(".domain-reg-action").filter(":checked").each(function() {
                if (this.value == "register" || this.value == "transfer") {
                    hasDomainReg = true;
                }
            });

            if (!hasDomainReg) {
                $("#inputContactID").empty();
                $("#domainContactContainer").hide();
                return false;
            }

            const url = actionURL;
            const payloads = { 
                action: "getcontacts", 
                userid: $("#search_client").val(),
            };

            options.body = JSON.stringify(payloads);

            const response = await cbmsPost(url, options);

            if (response) {    
                const { result, message, data } = response;

                if (result == 'error') {
                    Toast.fire({ icon: 'error', title: message, });
                    return false;
                }

                let numberOfElements = data.contacts.length;
                if (numberOfElements === 0) {
                    $("#domainContactContainer").hide();
                } else {
                    $("#inputContactID").empty();
                    $("#inputContactID").append("<option value=\"0\">Use Primary Profile</option>");
                    $.each(data.contacts, function(key, value) {
                        $("#inputContactID").append("<option value=\"" + key + "\">" + value + "</option>");
                    });

                    $("#domainContactContainer").show();
                }
                
                return true;
            }

            console.log(`loadDomainContactOptions: Failed to fetch data. Response: ${response}`);
        }

        const loaddomainoptions = (el) => {
            let index = $(el).attr('data-index');

            if ($(`#inputDomainRegActionNone${index}`).is(':checked')) {
                $(`#domain-input-container${index}`).fadeOut(500).hide();
            } else if ($(`#inputDomainRegActionRegister${index}`).is(':checked')) {
                $(`#domain-input-container${index}`).fadeIn(500).show();
                $(`#inputDomainRegEppCode${index}`).fadeOut(500).hide();
            } else if ($(`#inputDomainRegActionTransfer${index}`).is(':checked')) {
                $(`#domain-input-container${index}`).fadeIn(500).show();
                $(`#inputDomainRegEppCode${index}`).fadeIn(500).show();
            }

            // loaddomfields(domainRef);
            loadDomainContactOptions();
        }

        const handleProductDomainInput = (currentDomain) => {
            // TODO! inputDomainRegDomain0
            let index = $(currentDomain).attr('data-index');
            let inputDomain = $(currentDomain).val();
            let domainEntries = $(`#inputDomainRegDomain${index}:visible`);

            if ($(domainEntries).length == 1) {
                if (!$(domainEntries).prop("data-manual-input") || ($(domainEntries).val().trim() == "")) {
                    $(domainEntries).val(inputDomain);
                }

                handleDomainRegInput(domainEntries);
            }

            if (domainContainsAPeriod(inputDomain)) {
                updatesummary();
            }
        }

        const handleDomainRegInput = (currentDomain) => {
            let inputDomain = $(currentDomain).val();

            if (domainUpdateTimeoutId) {
                clearTimeout(domainUpdateTimeoutId);
                domainUpdateTimeoutId = 0;
            }

            if (domainContainsAPeriod(inputDomain)) {
                domainUpdateTimeoutId = setTimeout(function() {
                    loaddomfields(currentDomain);
                }, 500);

                // updatesummary();
            }
        }

        const domainContainsAPeriod = (domain) => {
            if (domain.indexOf(".") > -1 ) {
                return true;
            } else {
                return false;
            }
        }

        const loaddomfields = async (domainRef) => {            
            let index = $(domainRef).attr('data-index');
            let domainName = $("#inputDomainRegDomain" +index).val();
            let domainCounter = index;

            if (domainName.length >= 5 && domainContainsAPeriod(domainName)) {
                const url = actionURL;
                const payloads = { 
                    action: "getdomainaddlfields", 
                    domain: domainName, 
                    domainnum: domainCounter, 
                };

                options.body = JSON.stringify(payloads);

                const response = await cbmsPost(url, options);

                if (response) {    
                    const { result, message, data } = response;

                    if (result == 'error') {
                        Toast.fire({ icon: 'error', title: message, });
                        return false;
                    }

                    $("#domain-addt-fields" +index).html("");
                    if (data.additionalFields) {
                        $("#domain-addt-fields" +index).html(data.additionalFields);
                        $("#spanRequiredFields" +index).show().fadeIn(500);
                    } else {
                        $("#spanRequiredFields" +index).hide().fadeOut();
                    }

                    if (data.invalidTld) {
                        $("#spanInvalidTld" +index).show().fadeIn(500);
                    } else {
                        $("#spanInvalidTld" +index).hide().fadeOut(500);
                    }

                    return true;
                }

                console.log(`loaddomfields: Failed to fetch data. Response: ${response}`);
            }
        }

        const addProduct = () => {
            // Avoid to much cloned form
            if (productsCount > maxClonedField) {
                return;
            }
            
            productsCount++;
            prodtemplate
                .clone()
                .attr("id", "ord" + productsCount)
                .find(".input-reg-domain").val("").end()
                .find(".input-reg-qty").val("1").end()
                .find(".input-reg-priceoveride").val("").end()
                .find(".addonsrow").html("").end()
                .find(".productconfigoptions").html("").hide().end()
                .find("*").each(function() {
                    updateClonedElement(this, productsCount);
                }).end()
                .appendTo("#products");
            
            return true;
        }

        const addDomain = () => {
            // Avoid to much cloned form
            if (domainConfigCount > maxClonedField) {
                return;
            }

            domainConfigCount++;
            domaintemplate
                .clone()
                .attr("id", "ord-domain" + domainConfigCount)
                .attr("domain-counter", domainConfigCount)
                .find(".domain-reg-action").attr("name", "regaction[" + domainConfigCount + "]").end()
                .find(".required-field-indication").hide().end()
                .find(".invalid-tld").hide().end()
                .find(".domain-reg-dnsmanagement").attr("name", "dnsmanagement[" + domainConfigCount + "]").end()
                .find(".domain-reg-emailforwarding").attr("name", "emailforwarding[" + domainConfigCount + "]").end()
                .find(".domain-reg-idprotection").attr("name", "idprotection[" + domainConfigCount + "]").end()
                .find(".domain-reg-priceoverride").attr("name", "domainpriceoverride[" + domainConfigCount + "]").end()
                .find(".domain-reg-renewoverride").attr("name", "domainrenewoverride[" + domainConfigCount + "]").end()
                .find(".domain-addt-fields").html("").end()
                .find(".input-reg-domain").val("").end()
                .find("input:checkbox").removeAttr("checked").end()
                .find("input:radio").prop("checked", false).end()
                .find("input:radio:first").click().end()
                .find("*").each(function() {
                    updateClonedElement(this, domainConfigCount);
                }).end()
                .appendTo("#domains");

            loaddomainoptions($(`#inputDomainRegActionNone${domainConfigCount}`));

            return true;
        }

        const updateClonedElement = (el, counter = 0) => {
            // Update label attribut
            if ($(el).is("label") && $(el).attr('for')) {
                let attrFor =  $(el).attr("for");
                let oldLabelFor = attrFor.substring(0, attrFor.length - 1);
                let newLabelFor = oldLabelFor + counter;

                $(el).attr("for", newLabelFor);
            }

            // Update input attribut
            if ($(el).attr('id')) {
                let attrId =  $(el).attr("id");
                let oldId = attrId.substring(0, attrId.length - 1);
                let newId = oldId + counter;

                $(el).attr("id", newId)
            }

            // Update data attribut
            if ($(el).attr('data-index')) {
                $(el).attr('data-index', counter);
            }
        }

        $('#btn-submit-order').on('click', function(event) {
            event.preventDefault(); 

            const url = actionURL; 

            const formData = $('#orderfrm').serializeJSON();
            console.log('Serialized Form Data:', formData);

            const payloads = {
                ...formData,
                action: "submitorder",
                calconly: false,
                forceSubmit: true,
            };

            const token = $('meta[name="csrf-token"]').attr('content');

            $("#submit-promo-loader").removeAttr("hidden");
            $("#btn-submit-order").attr({ "disabled": true }).text("Loading...");

            $.ajax({
                url: url,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                contentType: 'application/json',
                data: JSON.stringify(payloads),
                success: function(data) {
                    console.log('Response Data:', data); 

                    if (data.result === 'success') {
                        Toast.fire({
                            icon: 'success',
                            title: data.message,
                            text: 'Redirecting...'
                        });
                        window.location.href = data.data.redirect;
                        $("#btn-submit-order").attr({ "disabled": true }).text("Loading...");
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message
                        });
                        console.error('Order submission failed:', data.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error:',
                        text: errorThrown
                    });
                    console.error('Error:', textStatus, errorThrown);
                },
                complete: function() {
                    $("#submit-promo-loader").attr({ "hidden": true });
                    $("#btn-submit-order").attr({ "disabled": false }).text("Place Order");
                }
            });
        });

    </script> 
@endsection
