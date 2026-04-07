@extends('layouts.clientbase')

@section('page-title')
   View Cart
@endsection

@section('content')
    @if ($checkout)
        @include('checkout')
    @else
        <script>
            // Define state tab index value
            var statesTab = 10;
            var stateNotRequired = true;
        </script>
        @include('common')
        <script type="text/javascript" src="{{Theme::asset('js/StatesDropdown.js')}}"></script>
        <div class="page-content">
            <div class="container-fluid">
                <div id="order-standard_cart">
                    <div class="row">

                        <div class="pull-md-right col-md-9">

                            <div class="header-lined">
                                <h1>{{Lang::get('client.cartreviewcheckout')}}</h1>
                            </div>

                        </div>

                        <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">

                            @include('sidebar-categories')

                        </div>

                        <div class="col-md-9 pull-md-right">

                            @include('sidebar-categories-collapsed')

                            <div class="row">
                                <div class="col-md-8">

                                    @if (isset($promoerrormessage) && $promoerrormessage)
                                        <div class="alert alert-warning text-center" role="alert">
                                            {{$promoerrormessage}}
                                        </div>
                                    @elseif (isset($errormessage) && $errormessage)
                                        <div class="alert alert-danger" role="alert">
                                            <p>{{Lang::get('orderForm.correctErrors')}}:</p>
                                            <ul>
                                                {{$errormessage}}
                                            </ul>
                                        </div>
                                    @elseif ((isset($promotioncode) && $promotioncode) && $rawdiscount == "0.00")
                                        <div class="alert alert-info text-center" role="alert">
                                            {{Lang::get('client.promoappliedbutnodiscount')}}
                                        </div>
                                    @elseif (isset($promoaddedsuccess) && $promoaddedsuccess)
                                        <div class="alert alert-success text-center" role="alert">
                                            {{Lang::get('orderForm.promotionAccepted')}}
                                        </div>
                                    @endif

                                    @if ($bundlewarnings)
                                        <div class="alert alert-warning" role="alert">
                                            <strong>{{Lang::get('client.bundlereqsnotmet')}}</strong><br />
                                            <ul>
                                                @foreach ($bundlewarnings as $warning)
                                                    <li>{{$warning}}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form method="post" action="{{route('cart', ['a' => 'view'])}}">

                                        <div class="view-cart-items-header">
                                            <div class="row">
                                                <div class="{{$showqtyoptions?'col-sm-5':'col-sm-7'}} col-xs-7">
                                                    {{Lang::get('orderForm.productOptions')}}
                                                </div>
                                                @if ($showqtyoptions)
                                                    <div class="col-sm-2 hidden-xs text-center">
                                                        {{Lang::get('orderForm.qty')}}
                                                    </div>
                                                @endif
                                                <div class="col-sm-4 col-xs-5 text-right">
                                                    {{Lang::get('orderForm.priceCycle')}}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="view-cart-items">

                                            @foreach ($products as $num => $product)
                                                <div class="item">
                                                    <div class="row">
                                                        <div class="{{$showqtyoptions?'col-sm-5':'col-sm-7'}}">
                                                            <span class="item-title">
                                                                {{$product['productinfo']['name']}}
                                                                <a href="{{route('cart')}}?a=confproduct&i={{$num}}" class="btn btn-link btn-xs">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                    {{Lang::get('orderForm.edit')}}
                                                                </a>
                                                                <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('p','{{$num}}')">
                                                                        <i class="fas fa-times"></i>
                                                                        {{Lang::get('orderForm.remove')}}
                                                                </button>
                                                            </span>
                                                            <span class="item-group">
                                                                {{$product['productinfo']['groupname']}}
                                                            </span>
                                                            @if ($product['domain'])
                                                                <span class="item-domain">
                                                                    {{$product['domain']}}
                                                                </span>
                                                            @endif
                                                            @if ($product['configoptions'])
                                                                <small>
                                                                    @foreach ($product['configoptions'] as $confnum => $configoption)
                                                                        &nbsp;&raquo; {{$configoption['name']}}:
                                                                        @if ($configoption['type'] == 1 || $configoption['type'] == 2)
                                                                            {!!$configoption['option']!!}
                                                                        @elseif ($configoption['type'] == 3)
                                                                            @if ($configoption['qty'])
                                                                                {!!$configoption['option']!!}
                                                                            @else
                                                                                {{Lang::get('client.no')}}
                                                                            @endif
                                                                        @elseif ($configoption['type'] == 4)
                                                                            {!!$configoption['qty']!!} x {!!$configoption['option']!!}
                                                                        @endif
                                                                        <br />
                                                                    @endforeach
                                                                </small>
                                                            @endif
                                                        </div>
                                                        @if ($showqtyoptions)
                                                            <div class="col-sm-2 item-qty">
                                                                @if ($product['allowqty'])
                                                                    <input type="number" name="qty[{{$num}}]" value="{{$product['qty']}}" class="form-control text-center" />
                                                                    <button type="submit" class="btn btn-xs">
                                                                        {{Lang::get('orderForm.update')}}
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <div class="col-sm-4 item-price">
                                                            <span>{{$product['pricing']['totalTodayExcludingTaxSetup']}}</span>
                                                            <span class="cycle">{{$product['billingcyclefriendly']}}</span>
                                                            @if ($product['pricing']['productonlysetup'])
                                                                {{$product['pricing']['productonlysetup']->toPrefixed()}} {{Lang::get('client.ordersetupfee')}}
                                                            @endif
                                                            @if (isset($product['proratadate']) && $product['proratadate'])
                                                                <br />({{Lang::get('client.orderprorata')}} {{$product['proratadate']}})
                                                            @endif
                                                        </div>
                                                        <div class="col-sm-1 hidden-xs">
                                                            <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('p','{{$num}}')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    @if (isset($adjustment))
                                                        @if (array_key_exists($product['pid'], $adjustment) && $product['pid'] == $adjustment[$product['pid']]['pid'])
                                                            <div class="row">
                                                                <div class="{{$showqtyoptions?'col-sm-5':'col-sm-7'}}">
                                                                    {{$adjustment[$product['pid']]['description']}}
                                                                </div>
                                                                <div class="col-sm-4 item-price">
                                                                    {{$adjustment[$product['pid']]['amount']}}
                                                                </div>
                                                                <div class="col-sm-1 hidden-xs">
                                                                    &nbsp;
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                                @foreach ($product['addons'] as $addonnum => $addon)
                                                    <div class="item">
                                                        <div class="row">
                                                            <div class="col-sm-7">
                                                                <span class="item-title">
                                                                    {{$addon['name']}}
                                                                </span>
                                                                <span class="item-group">
                                                                    {{Lang::get('client.orderaddon')}}
                                                                </span>
                                                                @if ($addon['setup'])
                                                                    <span class="item-setup">
                                                                        {{$addon['setup']}} {{Lang::get('client.ordersetupfee')}}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="col-sm-4 item-price">
                                                                <span>{{$addon['totaltoday']}}</span>
                                                                <span class="cycle">{{$addon['billingcyclefriendly']}}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endforeach

                                            @foreach ($addons as $num => $addon)
                                                <div class="item">
                                                    <div class="row">
                                                        <div class="col-sm-7">
                                                            <span class="item-title">
                                                                {{$addon['name']}}
                                                                <span class="visible-xs-inline">
                                                                    <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('a','{{$num}}')">
                                                                        <i class="fas fa-times"></i>
                                                                        {{Lang::get('orderForm.remove')}}
                                                                    </button>
                                                                </span>
                                                            </span>
                                                            <span class="item-group">
                                                                {{$addon['productname']}}
                                                            </span>
                                                            @if ($addon['domainname'])
                                                                <span class="item-domain">
                                                                    {{$addon['domainname']}}
                                                                </span>
                                                            @endif
                                                            @if ($addon['setup'])
                                                                <span class="item-setup">
                                                                    {{$addon['setup']}} {{Lang::get('client.ordersetupfee')}}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="col-sm-4 item-price">
                                                            <span>{{$addon['pricingtext']}}</span>
                                                            <span class="cycle">{{$addon['billingcyclefriendly']}}</span>
                                                        </div>
                                                        <div class="col-sm-1 hidden-xs">
                                                            <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('a','{{$num}}')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @foreach ($domains as $num => $domain)
                                                <div class="item">
                                                    <div class="row">
                                                        <div class="col-sm-7">
                                                            <span class="item-title">
                                                                @if ($domain['type'] == "register")
                                                                    {{Lang::get('client.orderdomainregistration')}}
                                                                @else
                                                                    {{Lang::get('client.orderdomaintransfer')}}
                                                                @endif
                                                                <a href="{{route('cart')}}?a=confdomains" class="btn btn-link btn-xs">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                    {{Lang::get('orderForm.edit')}}
                                                                </a>
                                                                <span class="visible-xs-inline">
                                                                    <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('d','{{$num}}')">
                                                                        <i class="fas fa-times"></i>
                                                                        {{Lang::get('orderForm.remove')}}
                                                                    </button>
                                                                </span>
                                                            </span>
                                                            @if ($domain['domain'])
                                                                <span class="item-domain">
                                                                    {{$domain['domain']}}
                                                                </span>
                                                            @endif
                                                            @if ($domain['dnsmanagement']) &nbsp;&raquo; {{Lang::get('client.domaindnsmanagement')}}<br /> @endif
                                                            @if ($domain['emailforwarding']) &nbsp;&raquo; {{Lang::get('client.domainemailforwarding')}}<br /> @endif
                                                            @if ($domain['idprotection']) &nbsp;&raquo; {{Lang::get('client.domainidprotection')}}<br /> @endif
                                                        </div>
                                                        <div class="col-sm-4 item-price">
                                                            @if (count($domain['pricing']) == 1 || $domain['type'] == 'transfer')
                                                                <span name="{{$domain['domain']}}Price">{{$domain['price']}}</span>
                                                                <span class="cycle">{{$domain['regperiod']}} {{$domain['yearsLanguage']}}</span>
                                                                <span class="renewal cycle">
                                                                    @if (isset($domain['renewprice']))
                                                                        {{Lang::get('client.domainrenewalprice')}} <span class="renewal-price cycle">{{$domain['renewprice']->toPrefixed()}}{{$domain['shortYearsLanguage']}}
                                                                    @endif
                                                                    </span>
                                                                    {{-- {if isset($domain.renewprice)}{lang key='domainrenewalprice'} <span class="renewal-price cycle">{$domain.renewprice->toPrefixed()}{$domain.shortYearsLanguage}{/if}</span> --}}
                                                                </span>
                                                            @else
                                                                <span name="{{$domain['domain']}}Price">{{$domain['price']}}</span>
                                                                <div class="dropdown">
                                                                    <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="{{$domain['domain']}}Pricing" name="{{$domain['domain']}}Pricing" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                                        {{$domain['regperiod']}} {{$domain['yearsLanguage']}}
                                                                        <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu" aria-labelledby="{{$domain['domain']}}Pricing">
                                                                        @foreach ($domain['pricing'] as $years => $price)
                                                                            <li>
                                                                                <a href="#" onclick="selectDomainPeriodInCart('{{$domain['domain']}}', '{{$price['register']}}', {{$years}}, '{{$years == 1?Lang::get('orderForm.year'):Lang::get('orderForm.years')}}');return false;">
                                                                                    {{$years}}
                                                                                    @if ($years == 1)
                                                                                        {{Lang::get('orderForm.year')}}
                                                                                    @else
                                                                                        {{Lang::get('orderForm.years')}}
                                                                                    @endif
                                                                                    @ {{$price['register']}}
                                                                                    {{-- {if $years == 1}{lang key='orderForm.year'}{else}{lang key='orderForm.years'}{/if} @ {$price.register} --}}
                                                                                </a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                                <span class="renewal cycle">
                                                                    {{Lang::get('client.domainrenewalprice')}} <span class="renewal-price cycle">@if (isset($domain['renewprice'])) {{$domain['renewprice']->toPrefixed()}}{{$domain['shortYearsLanguage']}} @endif</span>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="col-sm-1 hidden-xs">
                                                            <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('d','{{$num}}')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @foreach ($renewals as $num => $domain)
                                                <div class="item">
                                                    <div class="row">
                                                        <div class="col-sm-7">
                                                            <span class="item-title">
                                                                {{Lang::get('client.domainrenewal')}}
                                                            </span>
                                                            <span class="item-domain">
                                                                {{$domain['domain']}}
                                                            </span>
                                                            @if ($domain['dnsmanagement']) &nbsp;&raquo; {{Lang::get('client.domaindnsmanagement')}}<br /> @endif
                                                            @if ($domain['emailforwarding']) &nbsp;&raquo; {{Lang::get('client.domainemailforwarding')}}<br /> @endif
                                                            @if ($domain['idprotection']) &nbsp;&raquo; {{Lang::get('client.domainidprotection')}}<br /> @endif
                                                        </div>
                                                        <div class="col-sm-4 item-price">
                                                            <span>{{$domain['price']}}</span>
                                                            <span class="cycle">{{$domain['regperiod']}} {{Lang::get('client.orderyears')}}</span>
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('r','{{$num}}')">
                                                                <i class="fas fa-times"></i>
                                                                <span class="visible-xs">{{Lang::get('orderForm.remove')}}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @foreach ($upgrades as $num => $upgrade)
                                                <div class="item">
                                                    <div class="row">
                                                        <div class="col-sm-7">
                                                            <span class="item-title">
                                                                {{Lang::get('client.upgrade')}}
                                                            </span>
                                                            <span class="item-group">
                                                                @if ($upgrade->type == 'service')
                                                                    {{$upgrade->originalProduct->productGroup->name}}<br>{{$upgrade->originalProduct->name}} => {{$upgrade->newProduct->name}}
                                                                @elseif ($upgrade->type == 'addon')
                                                                    {{$upgrade->originalAddon->name}} => {{$upgrade->newAddon->name}}
                                                                @endif
                                                            </span>
                                                            <span class="item-domain">
                                                                @if ($upgrade->type == 'service')
                                                                    {{$upgrade->service->domain}}
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="col-sm-4 item-price">
                                                            <span>{{$upgrade->newRecurringAmount}}</span>
                                                            <span class="cycle">{{$upgrade->localisedNewCycle}}</span>
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('u','{{$num}}')">
                                                                <i class="fas fa-times"></i>
                                                                <span class="visible-xs">{{Lang::get('orderForm.remove')}}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @if ($upgrade->totalDaysInCycle > 0)
                                                        <div class="row row-upgrade-credit">
                                                            <div class="col-sm-7">
                                                                <span class="item-group">
                                                                    {{Lang::get('client.upgradeCredit')}}
                                                                </span>
                                                                <div class="upgrade-calc-msg">
                                                                    {{Lang::get('client.upgradeCreditDescription', ['daysRemaining' => $upgrade->daysRemaining, 'totalDays' => $upgrade->totalDaysInCycle])}}
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4 item-price">
                                                                <span>-{{$upgrade->creditAmount}}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach

                                            @if ($cartitems == 0)
                                                <div class="view-cart-empty">
                                                    {{Lang::get('client.cartempty')}}
                                                </div>
                                            @endif

                                        </div>

                                        @if ($cartitems > 0)
                                            <div class="empty-cart">
                                                <button type="button" class="btn btn-link btn-xs" id="btnEmptyCart">
                                                    <i class="fas fa-trash-alt"></i>
                                                    <span>{{Lang::get('client.emptycart')}}</span>
                                                </button>
                                            </div>
                                        @endif

                                    </form>

                                    @foreach ($hookOutput as $output)
                                        <div>
                                            {!!$output!!}
                                        </div>
                                    @endforeach

                                    @foreach ($gatewaysoutput as $gatewayoutput)
                                        <div class="view-cart-gateway-checkout">
                                            {!!$gatewayoutput!!}
                                        </div>
                                    @endforeach

                                    <div class="view-cart-tabs">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="nav-item active"><a class="nav-link active" href="#applyPromo" aria-controls="applyPromo" role="tab" data-toggle="tab">{{Lang::get('orderForm.applyPromoCode')}}</a></li>
                                            @if ($taxenabled && (isset($loggedin) && !$loggedin))
                                                <li class="nav-item" role="presentation"><a class="nav-link" href="#calcTaxes" aria-controls="calcTaxes" role="tab" data-toggle="tab">{{Lang::get('orderForm.estimateTaxes')}}</a></li>
                                            @endif
                                        </ul>
                                        <div class="tab-content">
                                            <div role="tabpanel" class="tab-pane active promo" id="applyPromo">
                                                @if ($promotioncode)
                                                    <div class="view-cart-promotion-code">
                                                        {{$promotioncode}} - {{$promotiondescription}}
                                                    </div>
                                                    <div class="text-center">
                                                        <a href="{{route('cart')}}?a=removepromo" class="btn btn-secondary btn-sm">
                                                            {{Lang::get('orderForm.removePromotionCode')}}
                                                        </a>
                                                    </div>
                                                @else
                                                    <form method="post" action="{{route('cart')}}?a=view">
                                                        <div class="form-group prepend-icon ">
                                                            <label for="cardno" class="field-icon">
                                                                <i class="fas fa-ticket-alt"></i>
                                                            </label>
                                                            <input type="text" name="promocode" id="inputPromotionCode" class="field" placeholder="{{Lang::get('client.orderPromoCodePlaceholder')}}" required="required">
                                                        </div>
                                                        <button type="submit" name="validatepromo" class="btn btn-block btn-primary" value="{{Lang::get('client.orderpromovalidatebutton')}}">
                                                            {{Lang::get('client.orderpromovalidatebutton')}}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            <div role="tabpanel" class="tab-pane" id="calcTaxes">

                                                <form method="post" action="{{route('cart')}}?a=setstateandcountry">
                                                    <div class="form-horizontal">
                                                        <div class="form-group">
                                                            <label for="inputState" class="col-sm-4 control-label">{{Lang::get('orderForm.state')}}</label>
                                                            <div class="col-sm-7">
                                                                <input type="text" name="state" id="inputState" value="{{isset($clientsdetails['state']) ? $clientsdetails['state'] : ""}}" class="form-control" @if (isset($loggedin) && $loggedin) disabled="disabled" @endif/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="inputCountry" class="col-sm-4 control-label">{{Lang::get('orderForm.country')}}</label>
                                                            <div class="col-sm-7">
                                                                <select name="country" id="inputCountry" class="form-control">
                                                                    @foreach ($countries as $countrycode => $countrylabel)
                                                                        <option
                                                                            value="{{$countrycode}}"
                                                                            @if ((!$country && $countrycode == $defaultcountry) || $countrycode == $country)
                                                                                selected
                                                                            @endif>
                                                                            {{$countrylabel}}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group text-center">
                                                            <button type="submit" class="btn">
                                                                {{Lang::get('orderForm.updateTotals')}}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-4" id="scrollingPanelContainer">

                                    <div class="order-summary" id="orderSummary">
                                        <div class="loader" id="orderSummaryLoader" style="display: none;">
                                            <i class="fas fa-fw fa-sync fa-spin"></i>
                                        </div>
                                        <h2>{{Lang::get('client.ordersummary')}}</h2>
                                        <div class="summary-container">

                                            <div class="subtotal clearfix">
                                                <span class="pull-left">{{Lang::get('client.ordersubtotal')}}</span>
                                                <span id="subtotal" class="pull-right">{{$subtotal}}</span>
                                            </div>
                                            @if (isset($adjustment) && isset($adjustmentSubtotal))
                                                <div class="subtotal clearfix">
                                                    <span class="pull-left">{{Lang::get('client.orderprorata')}}</span>
                                                    <span id="subtotal" class="pull-right">{{$adjustmentSubtotal}}</span>
                                                </div>
                                            @endif
                                            @if ($promotioncode || $taxrate || $taxrate2)
                                                <div class="bordered-totals">
                                                    @if ($promotioncode)
                                                        <div class="clearfix">
                                                            <span class="pull-left">{{$promotiondescription}}</span>
                                                            <span id="discount" class="pull-right">{{$discount}}</span>
                                                        </div>
                                                    @endif
                                                    @if ($taxrate)
                                                        <div class="clearfix">
                                                            <span class="pull-left">{{$taxname}} @ {{$taxrate}}%</span>
                                                            <span id="taxTotal1" class="pull-right">{{$taxtotal}}</span>
                                                        </div>
                                                    @endif
                                                    @if ($taxrate2)
                                                        <div class="clearfix">
                                                            <span class="pull-left">{{$taxname2}} @ {{$taxrate2}}%</span>
                                                            <span id="taxTotal2" class="pull-right">{{$taxtotal2}}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="recurring-totals clearfix">
                                                <span class="pull-left">{{Lang::get('orderForm.totals')}}</span>
                                                <span id="recurring" class="pull-right recurring-charges">
                                                    <span id="recurringMonthly" @if (!$totalrecurringmonthly) style="display:none;" @endif>
                                                        <span class="cost">{{$totalrecurringmonthly}}</span> {{Lang::get('client.orderpaymenttermmonthly')}}<br />
                                                    </span>
                                                    <span id="recurringQuarterly" @if (!$totalrecurringquarterly) style="display:none;" @endif >
                                                        <span class="cost">{{$totalrecurringquarterly}}</span> {{Lang::get('client.orderpaymenttermquarterly')}}<br />
                                                    </span>
                                                    <span id="recurringSemiAnnually" @if (!$totalrecurringsemiannually) style="display:none;" @endif>
                                                        <span class="cost">{{$totalrecurringsemiannually}}</span> {{Lang::get('client.orderpaymenttermsemiannually')}}<br />
                                                    </span>
                                                    <span id="recurringAnnually" @if (!$totalrecurringannually) style="display:none;" @endif>
                                                        <span class="cost">{{$totalrecurringannually}}</span> {{Lang::get('client.orderpaymenttermannually')}}<br />
                                                    </span>
                                                    <span id="recurringBiennially" @if (!$totalrecurringbiennially) style="display:none;" @endif>
                                                        <span class="cost">{{$totalrecurringbiennially}}</span> {{Lang::get('client.orderpaymenttermbiennially')}}<br />
                                                    </span>
                                                    <span id="recurringTriennially" @if (!$totalrecurringtriennially) style="display:none;" @endif>
                                                        <span class="cost">{{$totalrecurringtriennially}}</span> {{Lang::get('client.orderpaymenttermtriennially')}}<br />
                                                    </span>
                                                </span>
                                            </div>

                                            <div class="total-due-today total-due-today-padded">
                                                <span id="totalDueToday" class="amt">{{$total}}</span>
                                                <span>{{Lang::get('client.ordertotalduetoday')}}</span>
                                            </div>

                                            <div class="text-right">
                                                <a href="{{route('cart')}}?a=checkout" class="btn btn-success btn-lg btn-checkout {{$cartitems == 0? 'disabled' : ''}}" id="checkout">
                                                    {{Lang::get('orderForm.checkout')}}
                                                    <i class="fas fa-arrow-right"></i>
                                                </a><br />
                                                <a href="{{route('cart')}}" class="btn btn-link btn-continue-shopping" id="continueShopping">
                                                    {{Lang::get('orderForm.continueShopping')}}
                                                </a>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="{{route('cart')}}">
                        <input type="hidden" name="a" value="remove" />
                        <input type="hidden" name="r" value="" id="inputRemoveItemType" />
                        <input type="hidden" name="i" value="" id="inputRemoveItemRef" />
                        <div class="modal fade modal-remove-item" id="modalRemoveItem" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="{{Lang::get('orderForm.close')}}">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        {{-- <h4 class="modal-title">
                                            <i class="fas fa-times fa-3x"></i>
                                            <span>{{Lang::get('orderForm.removeItem')}}</span>
                                        </h4> --}}
                                    </div>
                                    <div class="modal-body px-3 m-0">
                                        <h5>{{Lang::get('client.cartremoveitemconfirm')}}</h4>
                                    </div>
                                    <div class="modal-footer m-0 p-2">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{Lang::get('client.no')}}</button>
                                        <button type="submit" class="btn btn-primary">{{Lang::get('client.yes')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form method="post" action="{{route('cart')}}">
                        <input type="hidden" name="a" value="empty" />
                        <div class="modal fade modal-remove-item" id="modalEmptyCart" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="{{Lang::get('orderForm.close')}}">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title">
                                            <i class="fas fa-trash-alt fa-3x"></i>
                                            <span>{{Lang::get('client.emptycart')}}</span>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        {{Lang::get('client.cartemptyconfirm')}}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{Lang::get('client.no')}}</button>
                                        <button type="submit" class="btn btn-primary">{{Lang::get('client.yes')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
