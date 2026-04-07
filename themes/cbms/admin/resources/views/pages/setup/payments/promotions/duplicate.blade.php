@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Duplicate Promotions</title>
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
                                        <h4 class="mb-3">Promotions/Coupons</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
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
                                    <!-- START HERE -->
                                    <form action="{{ url(Request::segment(1).'/setup/payments/promotions/duplicate-store') }}" method="post" enctype="multipart/form-data">
                                    <div class="card p-3">
                                        <h4 class="card-title mb-3">Duplicate Promotion</h4>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Promotion Code</label>
                                            <div class="col-sm-12 col-lg-3">
                                                <input type="text" name="code" value="" id="promocode" class="form-control">
                                            </div>
                                            <div class="col-sm-12 col-lg-3">
                                                <button id="genarate" class="btn px-3 btn-success">Auto Generate Code</button>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Type</label>
                                            <div class="col-sm-12 col-lg-3">
                                                <select name="type"  class="form-control">
                                                    <option value="Percentage" {{ ($data->type == 'Percentage')?'selected':'' }} >Percentage</option>
                                                    <option value="Fixed Amount"  {{ ($data->type == 'Fixed Amount')?'selected':'' }}>Fixed Amount</option>
                                                    <option value="Price Override" {{ ($data->type == 'Price Override')?'selected':'' }}>Price Override</option>
                                                    <option value="Free Setup" {{ ($data->type == 'Free Setup')?'selected':'' }}>Free Setup</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Recurring</label>
                                            <div class="col-sm-12 col-lg-5 d-flex align-items-center">
                                                <div class="custom-control custom-checkbox pt-2">
                                                    <input type="checkbox" name="recurring" class="custom-control-input" onclick="$('input#recurfor').prop('readonly', !$('input#recurfor').prop('readonly'));" value="1"  id="customCheck1" {{ ($data->recurring == 1 )?'checked':'' }} >
                                                    <label class="custom-control-label" for="customCheck1">Enable RecurFor</label>
                                                </div>
                                                <input id="recurfor" name="recurfor" type="text" class="form-control mx-2 w-25" value="{{ $data->recurfor }}">
                                                <label class="pt-2 m-0">Times (0= Unlimited)</label>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Value</label>
                                            <div class="col-sm-12 col-lg-3">
                                                <input type="text" name="pvalue" class="form-control" value="{{ $data->value }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Applies To</label>
                                            <div class="col-sm-12 col-lg-5">
                                                <select name="appliesto[]" id="" class="form-control" multiple>
                                                    @foreach($product as $r )
                                                        <option value="{{ $r['id'] }}"  {{ (in_array($r['id'],explode(',',$data->appliesto)))?'selected':'' }} >{{$r['groupname'] }} - {{ $r['name'] }} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                          
                                            <label class="col-sm-12 col-lg-2 col-form-label">Requires</label>
                                            <div class="col-sm-12 col-lg-5">
                                                <select name="requires[]" id="" class="form-control" multiple>
                                                    @foreach($Addons as $r)
                                                        <option value="A{{ $r->id }}" {{ (in_array('A'.$r->id,explode(',',$data->requires)))?'selected':'' }} >Addon - {{ $r->name }}</option>
                                                    @endforeach
                                                    @foreach($extension as $r)
                                                    <option value="D{{ $r->id }}" {{ (in_array('D'.$r->id,explode(',',$data->requires)))?'selected':'' }}>Domain - {{ $r->extension }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="custom-control custom-checkbox pt-2">
                                                    <input type="checkbox" name="requiresexisting" class="custom-control-input" id="customCheck2" value="1" {{ ($data->requiresexisting == 1)?'checked':'' }} >
                                                    <label class="custom-control-label" for="customCheck2">Also allowexisting products in account to qualify for promotion</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label
                                                class="col-sm-12 col-lg-2 col-form-label d-flex align-items-center text-justify">Billing
                                                Cycles
                                                (No selection = any)</label>
                                            <div class="col-sm-12 col-lg-10">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <h6><strong>Products/Services</strong></h6>
                                                    </div>
                                                    <div class="col-lg-12 col-sm-6 d-lg-flex">
                                                        <div class="custom-control custom-checkbox mr-2">
                                                            <input type="checkbox" class="custom-control-input"  name="cycles[]"  value="One Time" {{ (in_array('One Time',explode(',',$data->cycles)))?'checked':'' }}  id="customCheck3">
                                                            <label class="custom-control-label" for="customCheck3">One Time</label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox mr-2">
                                                            <input type="checkbox" class="custom-control-input" id="customCheck4" name="cycles[]" value="Monthly" {{ (in_array('Monthly',explode(',',$data->cycles)))?'checked':'' }}>
                                                            <label class="custom-control-label" for="customCheck4">Monthly</label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox mr-2">
                                                            <input type="checkbox" class="custom-control-input" id="customCheck5" name="cycles[]" value="Quarterly" {{ (in_array('Quarterly',explode(',',$data->cycles)))?'checked':'' }} >
                                                            <label class="custom-control-label" for="customCheck5">Quarterly</label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox mr-2">
                                                            <input type="checkbox" class="custom-control-input"  id="customCheck6" name="cycles[]" value="Semi-Annually" {{ (in_array('Semi-Annually',explode(',',$data->cycles)))?'checked':'' }} >
                                                            <label class="custom-control-label" for="customCheck6">Semi-Annually</label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox mr-2">
                                                            <input type="checkbox" class="custom-control-input" id="customCheck7" name="cycles[]" value="Annually" {{ (in_array('Annually',explode(',',$data->cycles)))?'checked':'' }}>
                                                            <label class="custom-control-label" for="customCheck7">Annually</label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox mr-2">
                                                            <input type="checkbox" class="custom-control-input" id="customCheck8" name="cycles[]" value="Biennially" {{ (in_array('Biennially',explode(',',$data->cycles)))?'checked':'' }}>
                                                            <label class="custom-control-label" for="customCheck8">Biennially</label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox mr-2">
                                                            <input type="checkbox" class="custom-control-input" id="customCheck9"  name="cycles[]" value="Triennially" {{ (in_array('Triennially',explode(',',$data->cycles)))?'checked':'' }}>
                                                            <label class="custom-control-label" for="customCheck9">Triennially</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <h6><strong>Domains</strong></h6>
                                                    </div>
                                                    <div class="col-lg-12 col-sm-6 d-lg-flex">
                                                        @for($domainyears = 1; $domainyears <= 10; $domainyears++)
                                                        <div class="custom-control custom-checkbox mr-3">
                                                            <input type="checkbox" class="custom-control-input" name="cycles[]" value="{{  $domainyears }}Years" id="domain{{$domainyears}}" {{ (in_array($domainyears.'Years',explode(',',$data->cycles)))?'checked':'' }} >
                                                            <label class="custom-control-label" for="domain{{$domainyears}}">{{ $domainyears }} Year</label>
                                                        </div>
                                                        @endfor
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Start Date</label>
                                            <div class="col-sm-12 col-lg-3">
                                                <input id="inputStartDate" type="date" class="form-control" name="startdate" value="{{ $data->startdate }}"  >
                                                <p>(Leave blank for none)</p>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Expiry Date</label>
                                            <div class="col-sm-12 col-lg-3">
                                                <input id="inputExpirationDate" type="date" name="expirationdate" class="form-control"  value="{{ $data->expirationdate }}">
                                                <p>(Leave blank for none)</p>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Maximum Uses</label>
                                            <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="maxuses" value="{{ $data->maxuses }}" >
                                            </div>
                                            <div class="col-sm-12 col-lg-3 pt-2">
                                                <p>(Enter 0 to allow unlimited uses)</p>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Number Of Uses</label>
                                            <div class="col-sm-12 col-lg-3">
                                                <input type="text" name="" id="" class="form-control" value="0" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Lifetime Promotions</label>
                                            <div class="col-sm-12 col-lg-10 pt-2">
                                                <div class="custom-control custom-checkbox mr-3">
                                                    <input type="checkbox" name="lifetimepromo" class="custom-control-input" id="customCheck20" value="1" {{ ($data->lifetimepromo == 1 )?'checked':'' }} >
                                                    <label class="custom-control-label" for="customCheck20">Discounted
                                                        pricing is applied even on upgrade and downgrade orders in the
                                                        future regardless of settings like max uses, expiry, etc;</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Apply Once</label>
                                            <div class="col-sm-12 col-lg-10 pt-2">
                                                <div class="custom-control custom-checkbox mr-3">
                                                    <input type="checkbox" class="custom-control-input" name="applyonce" id="customCheck21" value="1" {{ ($data->applyonce == 1 )?'checked':'' }} >
                                                    <label class="custom-control-label"  for="customCheck21">Apply only once
                                                        per order (even if multiple items qualify)</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">New Signups</label>
                                            <div class="col-sm-12 col-lg-10 pt-2">
                                                <div class="custom-control custom-checkbox mr-3">
                                                    <input type="checkbox" class="custom-control-input"  name="newsignups" id="customCheck22" value="1" {{ ($data->newsignups == 1 )?'checked':'' }} >
                                                    <label class="custom-control-label" for="customCheck22">Apply to new
                                                        signups only (must have no previous active orders)</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Apply Once / Client </label>
                                            <div class="col-sm-12 col-lg-10 pt-2">
                                                <div class="custom-control custom-checkbox mr-3">
                                                    <input type="checkbox" name="onceperclient" class="custom-control-input" value="1" id="customCheck23"  {{ ($data->onceperclient == 1 )?'checked':'' }}>
                                                    <label class="custom-control-label" for="customCheck23">Apply only once
                                                        per client globally (ie. only one order allowed per promo)</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Existing Client</label>
                                            <div class="col-sm-12 col-lg-10 pt-2">
                                                <div class="custom-control custom-checkbox mr-3">
                                                    <input type="checkbox" class="custom-control-input" name="existingclient" id="customCheck24" value="1"  {{ ($data->existingclient == 1 )?'checked':'' }}>
                                                    <label class="custom-control-label" for="customCheck24">Apply to existing clients only (must have an active order to qualify))</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Upgrades/Downgrades</label>
                                            <div class="col-sm-12 col-lg-10 pt-2">
                                                <div class="custom-control custom-checkbox mr-3">
                                                    <input type="checkbox" class="custom-control-input" value="1" name="upgrades"  {{ ($data->upgrades == 1 )?'checked':'' }} onclick="$('#upgradeoptions').slideToggle()" id="customCheck25">
                                                    <label class="custom-control-label" for="customCheck25">Enable for product upgrades</label>
                                                </div>
                                                <div id="upgradeoptions" class="p-3" style="display: {{ ($data->upgrades == 1)?'block':'none' }};">
                                                    <p><b>Upgrade Promotion Instructions</b></p>
                                                    <p>For all upgrade promotion codes, the 'Applies To' field defines the products that the promotion applies to
                                                    For product upgrades/downgrades, 'Requires' can also be used to restrict the products being upgraded from
                                                    And for configurable option upgrades/downgrades, you can select the specific options to apply the discount to below
                                                    If you set a discount value & enable recurring above, then the upgrade promo will also give a recurring discount to the parent product</p>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Upgrade Type</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="upgradetype" value="product" @if(!empty($upgradeconfig['upgradetype']))  {{ ($upgradeconfig['discounttype'] == 'product')?'checked':'' }} @endif >
                                                                <label class="form-check-label">Products/Services </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="upgradetype" value="configoptions" @if(!empty($upgradeconfig['upgradetype'])) {{ ($upgradeconfig['discounttype'] == 'configoptions' )?'checked':'' }} @endif>
                                                                <label class="form-check-label">Configurable Options</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Upgrade Discount</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="row">
                                                                <div class="col-3">
                                                                    <input type="text" class="form-control" name="upgradevalue" size="10" value="{{ $upgradeconfig['value'] }}">
                                                                </div>
                                                                <div class="col-3">
                                                                    <select name="upgradediscounttype" class="form-control">
                                                                        <option value="Percentage" @if(!empty($upgradeconfig['discounttype'])) {{ ($upgradeconfig['discounttype'] == 'Percentage')?'selected':'' }} @endif >Percentage</option>
                                                                        <option value="Fixed Amount" @if(!empty($upgradeconfig['discounttype'])) {{ ($upgradeconfig['discounttype'] == 'Fixed Amount')?'selected':'' }} @endif >Fixed Amount</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Config Options Upgrades</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <select name="configoptionupgrades[]" class="form-control" multiple>
                                                                @foreach($config as $r)
                                                                    <option value="{{ $r->id }}"   @if(!empty($upgradeconfig['configoptions'])) {{ in_array($r->id,$upgradeconfig['configoptions'] )?'selected':'' }} @endif  >{{ $r->name }} - {{ $r->optionname }} </option>
                                                                @endforeach
                                                               
                                                            </select>
                                                            <label>The options selected above are the ones the discount is applied to when Upgrade Type is set to Configurable Options</label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Admin Notes</label>
                                            <div class="col-sm-12 col-lg-10 pt-2">
                                                <textarea name="notes"  cols="30" rows="5" class="form-control" >{{ $data->notes }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 d-flex justify-content-center">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="id" value="{{ $data->id }}" >
                                            <button type="submit" class="btn btn-success px-3 mx-1">Save Changes</button>
                                            <a href="{{ url(Request::segment(1).'/setup/payments/promotions/') }}" class="btn btn-light px-3 mx-1">Cancel Changes</a>
                                        </div>
                                    </div>
                                    </form>
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
<script type="text/javascript">
    $(document).ready(function () {
       
        $( "#genarate" ).click(function() {
            $('#promocode').val();
            $.ajax({
                type: 'POST',
                url: '{{ url(Request::segment(1).'/setup/payments/promotions/gencode') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(data) {
                    $('#promocode').val(data);
                }
            });
            return false;
        });



    });
</script> 
@endsection