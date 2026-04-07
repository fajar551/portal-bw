{{-- NOTE: tema bersifat fleksibel --}}
{{-- tidak tergantung dengan tema parent --}}

{{-- parent: one --}}
@extends('layouts.clientbase')

@section('page-title')
   Shopping Cart
@endsection

@section('content')
@include('common')
<script>
    var _localLang = {
        'addToCart': "{{Lang::get('orderForm.addToCart')}}",
        'addedToCartRemove': "{{Lang::get('orderForm.addedToCartRemove')}}"
    }
</script>
<div class="page-content">
    <div class="container-fluid">
        <div id="order-standard_cart">
            <div class="row">
                <div class="pull-md-right col-md-9">

                    <div class="header-lined">
                        <h1>Configure </h1>
                    </div>

                </div>

                <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">

                    @include('sidebar-categories')

                </div>

                <div class="col-md-9 pull-md-right">

                    @include('sidebar-categories-collapsed')

                    <form id="frmConfigureProduct">
                        <input type="hidden" name="configure" value="true" />
                        <input type="hidden" name="i" value="{{$i}}" />

                        <div class="row">
                            <div class="col-md-8">

                                <p>{{Lang::get('orderForm.configureDesiredOptions')}}</p>

                                <div class="product-info">
                                    <p class="product-title">{{$productinfo['name']}}</p>
                                    <p>{!!$productinfo['description']!!}</p>
                                </div>

                                <div class="alert alert-danger hidden" role="alert" id="containerProductValidationErrors">
                                    <p>Mohon memperbaiki error berikut sebelum dapat melanjutkan:</p>
                                    <ul id="containerProductValidationErrorsList"></ul>
                                </div>

                                @if (isset($pricing['type']) && $pricing['type'] == "recurring")
                                    <div class="field-container">
                                        <div class="form-group">
                                            <label for="inputBillingcycle">{{Lang::get('client.cartchoosecycle')}}</label>
                                            <select name="billingcycle" id="inputBillingcycle" class="form-control select-inline"
                                                @if ($configurableoptions)
                                                    onchange="updateConfigurableOptions({{$i}}, this.value);"
                                                @else
                                                    onchange="recalctotals();"
                                                @endif
                                            >
                                                @if (isset($pricing['monthly']))
                                                    <option value="monthly" {{$billingcycle == "monthly" ? 'selected': '' }}>
                                                        {{$pricing['monthly']}}
                                                    </option>
                                                @endif
                                                @if (isset($pricing['quarterly']))
                                                    <option value="quarterly" {{$billingcycle == "quarterly" ? 'selected': '' }}>
                                                        {{$pricing['quarterly']}}
                                                    </option>
                                                @endif
                                                @if (isset($pricing['semiannually']))
                                                    <option value="semiannually" {{$billingcycle == "semiannually" ? 'selected': '' }}>
                                                        {{$pricing['semiannually']}}
                                                    </option>
                                                @endif
                                                @if (isset($pricing['annually']))
                                                    <option value="annually" {{$billingcycle == "annually" ? 'selected': '' }}>
                                                        {{$pricing['annually']}}
                                                    </option>
                                                @endif
                                                @if (isset($pricing['biennially']))
                                                    <option value="biennially" {{$billingcycle == "biennially" ? 'selected': '' }}>
                                                        {{$pricing['biennially']}}
                                                    </option>
                                                @endif
                                                @if (isset($pricing['triennially']))
                                                    <option value="triennially" {{$billingcycle == "triennially" ? 'selected': '' }}>
                                                        {{$pricing['triennially']}}
                                                    </option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                @if ($productinfo['type'] == "server")
                                    <div class="sub-heading">
                                        <span>{{Lang::get('client.cartconfigserver')}}</span>
                                    </div>
                                    <div class="field-container">

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="inputHostname">{{Lang::get('client.serverhostname')}}</label>
                                                    <input type="text" name="hostname" class="form-control" id="inputHostname" value="{{$server['hostname']}}" placeholder="servername.yourdomain.com">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="inputRootpw">{{Lang::get('client.serverrootpw')}}</label>
                                                    <input type="password" name="rootpw" class="form-control" id="inputRootpw" value="{{$server['rootpw']}}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="inputNs1prefix">{{Lang::get('client.serverns1prefix')}}</label>
                                                    <input type="text" name="ns1prefix" class="form-control" id="inputNs1prefix" value="{{$server['ns1prefix']}}" placeholder="ns1">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="inputNs2prefix">{{Lang::get('client.serverns2prefix')}}</label>
                                                    <input type="text" name="ns2prefix" class="form-control" id="inputNs2prefix" value="{{$server['ns2prefix']}}" placeholder="ns2">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                @endif

                                @if ($configurableoptions)
                                    <div class="sub-heading">
                                        <span>{{Lang::get('client.orderconfigpackage')}}</span>
                                    </div>
                                    <div class="product-configurable-options" id="productConfigurableOptions">
                                        <div class="row">
                                            @foreach ($configurableoptions as $num => $configoption)
                                                @if ($configoption['optiontype'] == 1)
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="inputConfigOption{{$configoption['id']}}">{{$configoption['optionname']}}</label>
                                                            <select name="configoption[{{$configoption['id']}}]" id="inputConfigOption{{$configoption['id']}}" class="form-control">
                                                                @foreach ($configoption['options'] as $options)
                                                                    <option value="{{$options['id']}}"
                                                                    @if ($configoption['selectedvalue'] == $options['id'])
                                                                    selected="selected"
                                                                    @endif
                                                                    >
                                                                        {{$options['name']}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                @elseif ($configoption['optiontype'] == 2)
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="inputConfigOption{{$configoption['id']}}">{{$configoption['optionname']}}</label>
                                                            @foreach ($configoption['options'] as $options)
                                                                <br />
                                                                <label>
                                                                    <input type="radio" name="configoption[{{$configoption['id']}}]" value="{{$options['id']}}"
                                                                    @if ($configoption['selectedvalue'] == $options['id'])
                                                                        checked="checked"
                                                                    @endif
                                                                    />
                                                                    @if ($options['name'])
                                                                        {{$options['name']}}
                                                                    @else
                                                                        {{Lang::get('client.enable')}}
                                                                    @endif
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @elseif ($configoption['optiontype'] == 3)
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="inputConfigOption{{$configoption['id']}}">{{$configoption['optionname']}}</label>
                                                            <br />
                                                            <label>
                                                                <input type="checkbox" name="configoption[{{$configoption['id']}}]" id="inputConfigOption{{$configoption['id']}}" value="1"
                                                                @if ($configoption['selectedqty'])
                                                                    checked
                                                                @endif
                                                                />
                                                                @if ($configoption['options'][0]['name'])
                                                                    {{$configoption['options'][0]['name']}}
                                                                @else
                                                                    {{Lang::get('client.enable')}}
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </div>
                                                @elseif ($configoption['optiontype'] == 4)
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label for="inputConfigOption{{$configoption['id']}}">{{$configoption['optionname']}}</label>
                                                            @php
                                                                $rangesliderincluded = $rangesliderincluded ?? false;
                                                            @endphp

                                                            @if ($configoption['qtymaximum'])
                                                                @if (!$rangesliderincluded)
                                                                    <script src="{{Theme::asset('js/ion.rangeSlider.min.js')}}"></script>
                                                                    <link rel="stylesheet" href="{{Theme::asset('css/ion.rangeSlider.css')}}">
                                                                    <link rel="stylesheet" href="{{Theme::asset('css/ion.rangeSlider.skinModern.css')}}">
                                                                    @php
                                                                        $rangesliderincluded = true;
                                                                    @endphp
                                                                @endif
                                                                <input
                                                                    type="text"
                                                                    name="configoption[{{$configoption['id']}}]"
                                                                    @if ($configoption['selectedqty'])
                                                                        value="{{$configoption['selectedqty']}}"
                                                                    @else
                                                                        value="{{$configoption['qtyminimum']}}"
                                                                    @endif
                                                                    id="inputConfigOption{{$configoption['id']}}"
                                                                    class="form-control" />
                                                                <script>
                                                                    var sliderTimeoutId = null;
                                                                    var sliderRangeDifference = "{{$configoption['qtymaximum']}}" - "{{$configoption['qtyminimum']}}";
                                                                    // The largest size that looks nice on most screens.
                                                                    var sliderStepThreshold = 25;
                                                                    // Check if there are too many to display individually.
                                                                    var setLargerMarkers = sliderRangeDifference > sliderStepThreshold;

                                                                    $("#inputConfigOption{{$configoption['id']}}").ionRangeSlider({
                                                                        min: "{{$configoption['qtyminimum']}}",
                                                                        max: "{{$configoption['qtymaximum']}}",
                                                                        grid: true,
                                                                        grid_snap: setLargerMarkers ? false : true,
                                                                        onChange: function() {
                                                                            if (sliderTimeoutId) {
                                                                                clearTimeout(sliderTimeoutId);
                                                                            }

                                                                            sliderTimeoutId = setTimeout(function() {
                                                                                sliderTimeoutId = null;
                                                                                recalctotals();
                                                                            }, 250);
                                                                        }
                                                                    });
                                                                </script>
                                                            @else
                                                                <div>
                                                                    <input
                                                                        type="number"
                                                                        name="configoption[{{$configoption['id']}}]"
                                                                        @if ($configoption['selectedqty'])
                                                                            value="{{$configoption['selectedqty']}}"
                                                                        @else
                                                                            value="{{$configoption['qtyminimum']}}"
                                                                        @endif
                                                                        id="inputConfigOption{{$configoption['id']}}" min="{{$configoption['qtyminimum']}}"
                                                                        onchange="recalctotals()"
                                                                        onkeyup="recalctotals()"
                                                                        class="form-control form-control-qty"
                                                                    />
                                                                    <span class="form-control-static form-control-static-inline">
                                                                        x {{$configoption['options'][0]['name']}}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($num % 2 != 0)
                                                    </div>
                                                    <div class="row">
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($customfields)
                                    <div class="sub-heading">
                                        <span>Informasi Tambahan Yang Diperlukan</span>
                                    </div>
                                    <div class="field-container">
                                        @foreach ($customfields as $customfield)
                                            <div class="form-group">
                                                <label for="customfield{{$customfield['id']}}">{{$customfield['name']}}</label>
                                                {!!$customfield['input']!!}
                                                @if ($customfield['description'])
                                                    <span class="field-help-text">
                                                        {!!$customfield['description']!!}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($addons || count($addonsPromoOutput) > 0)
                                    <div class="sub-heading">
                                        <span>{{Lang::get('client.cartavailableaddons')}}</span>
                                    </div>
                                    @foreach ($addonsPromoOutput as $output)
                                        <div>
                                            {!!$output!!}
                                        </div>
                                    @endforeach
                                    <div class="row addon-products">
                                        @foreach ($addons as $addon)
                                            <div class="col-sm-{{count($addons) > 1?'6':'12'}}">
                                                <div class="card panel-default panel-addon {{$addon['status']? 'panel-addon-selected' : ''}}">
                                                    <div class="card-body">
                                                        <label>
                                                            <input type="checkbox" name="addons[{{$addon['id']}}]" {{$addon['status']? 'checked':''}} />
                                                            {{$addon['name']}}
                                                        </label><br />
                                                        {{$addon['description']}}
                                                    </div>
                                                    @if ($addon['customfields'])
                                                        <div class="card-body text-left py-0 panel-addon-customfields {{$addon['status']? '':'d-none'}}">
                                                            @foreach ($addon['customfields'] as $customfield)
                                                                <div class="form-group">
                                                                    <label for="acustomfield{{$customfield['id']}}">{{$customfield['name']}}</label>
                                                                    {!!$customfield['input']!!}
                                                                    @if ($customfield['description'])
                                                                        <span class="field-help-text">
                                                                            {!!$customfield['description']!!}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <div class="panel-price">
                                                        {{$addon['pricing']}}
                                                    </div>
                                                    <div class="panel-add">
                                                        <i class="fas fa-plus"></i>
                                                        {{Lang::get('client.addtocart')}}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="alert alert-warning info-text-sm">
                                    <i class="fas fa-question-circle"></i>
                                    {!!Lang::get('orderForm.haveQuestionsContact')!!} <a href="contact.php" target="_blank" class="alert-link">{!!Lang::get('orderForm.haveQuestionsClickHere')!!}</a>
                                </div>

                            </div>
                            <div class="col-md-4" id="scrollingPanelContainer">

                                <div id="orderSummary">
                                    <div class="order-summary">
                                        <div class="loader" id="orderSummaryLoader">
                                            <i class="fas fa-fw fa-sync fa-spin"></i>
                                        </div>
                                        <h2>{{Lang::get('client.ordersummary')}}</h2>
                                        <div class="summary-container" id="producttotal"></div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" id="btnCompleteProductConfig" class="btn btn-primary btn-lg">
                                            {{Lang::get('client.continue')}}
                                            <i class="fas fa-arrow-circle-right"></i>
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
<script>recalctotals();</script>
@endsection
