@if ($producttotals)
    <span class="product-name">
        @if ($producttotals['allowqty'] && $producttotals['qty'] > 1)
            {{$producttotals['qty']}} x
        @endif
        {{$producttotals['productinfo']['name']}}
    </span>
    <span class="product-group">{{$producttotals['productinfo']['groupname']}}</span>

    <div class="clearfix">
        <span class="pull-left">{{$producttotals['productinfo']['name']}}</span>
        <span class="pull-right">{{$producttotals['pricing']['baseprice']}}</span>
    </div>

    @foreach ($producttotals['configoptions'] as $configoption)
        @if ($configoption)
            <div class="clearfix">
                <span class="pull-left">&nbsp;&raquo; {{$configoption['name']}}: {{$configoption['optionname']}}</span>
                <span class="pull-right">
                    {{$configoption['recurring']}}
                    @if ($configoption['setup'])
                        + {{$configoption['setup']}} {{Lang::get('client.ordersetupfee')}}
                    @endif
                </span>
            </div>
        @endif
    @endforeach

    @foreach ($producttotals['addons'] as $addon)
        <div class="clearfix">
            <span class="pull-left">+ {{$addon['name']}}</span>
            <span class="pull-right">{{$addon['recurring']}}</span>
        </div>
    @endforeach

    @if ($producttotals['pricing']['setup'] || $producttotals['pricing']['recurring'] || $producttotals['pricing']['addons'])
        <div class="summary-totals">
            @if ($producttotals['pricing']['setup'])
                <div class="clearfix">
                    <span class="pull-left">{{Lang::get('client.cartsetupfees')}}:</span>
                    <span class="pull-right">{{$producttotals['pricing']['setup']}}</span>
                </div>
            @endif
            @foreach ($producttotals['pricing']['recurringexcltax'] ?? [] as $cycle => $recurring)
                <div class="clearfix">
                    <span class="pull-left">{{$cycle}}:</span>
                    <span class="pull-right">{{$recurring}}</span>
                </div>
            @endforeach
            @if (isset($producttotals['pricing']['tax1']))
                <div class="clearfix">
                    <span class="pull-left">{{$carttotals['taxname']}} @ {{$carttotals['taxrate']}}%:</span>
                    <span class="pull-right">{{$producttotals['pricing']['tax1']}}</span>
                </div>
            @endif
            @if (isset($producttotals['pricing']['tax2']))
                <div class="clearfix">
                    <span class="pull-left">{{$carttotals['taxname2']}} @ {{$carttotals['taxrate2']}}%:</span>
                    <span class="pull-right">{{$producttotals['pricing']['tax2']}}</span>
                </div>
            @endif
        </div>
    @endif

    <div class="total-due-today">
        <span class="amt">{{$producttotals['pricing']['totaltoday']}}</span>
        <span>{{Lang::get('client.ordertotalduetoday')}}</span>
    </div>
@elseif ($renewals)
    @if ($carttotals['renewals'])
        <span class="product-name">{{Lang::get('client.domainrenewals')}}</span>
        @foreach ($carttotals['renewals'] as $domainId => $renewal)
            <div class="clearfix" id="cartDomainRenewal{{$domainId}}">
                <span class="pull-left">
                    {{$renewal['domain']}} - {{$renewal['regperiod']}} 
                    @if ($renewal['regperiod'] == 1)
                        {{Lang::get('orderForm.year')}}
                    @else
                        {{Lang::get('orderForm.years')}}
                    @endif
                </span>
                <span class="pull-right">
                    {{$renewal['priceBeforeTax']}}
                    <a onclick="removeItem('r','{{$domainId}}'); return false;" href="#" id="linkCartRemoveDomainRenewal{{$domainId}}">
                        <i class="fas fa-fw fa-trash-alt"></i>
                    </a>
                </span>
            </div>
            @if ($renewal['dnsmanagement'])
                <div class="clearfix">
                    <span class="pull-left">+ {{Lang::get('client.domaindnsmanagement')}}</span>
                </div>
            @endif
            @if ($renewal['emailforwarding'])
                <div class="clearfix">
                    <span class="pull-left">+ {{Lang::get('client.domainemailforwarding')}}</span>
                </div>
            @endif
            @if ($renewal['idprotection'])
                <div class="clearfix">
                    <span class="pull-left">+ {{Lang::get('client.domainidprotection')}}</span>
                </div>
            @endif
            @if ($renewal['hasGracePeriodFee'])
                <div class="clearfix">
                    <span class="pull-left">+ {{Lang::get('client.domainRenewal.graceFee')}}</span>
                </div>
            @endif
            @if ($renewal['hasRedemptionGracePeriodFee'])
                <div class="clearfix">
                    <span class="pull-left">+ {{Lang::get('client.domainRenewal.redemptionFee')}}</span>
                </div>
            @endif
        @endforeach
    @endif
    <div class="summary-totals">
        <div class="clearfix">
            <span class="pull-left">{{Lang::get('client.ordersubtotal')}}:</span>
            <span class="pull-right">{{$carttotals['subtotal']}}</span>
        </div>
        @if (($carttotals['taxrate'] && $carttotals['taxtotal']) || ($carttotals['taxrate2'] && $carttotals['taxtotal2']))
            @if ($carttotals['taxrate'])
                <div class="clearfix">
                    <span class="pull-left">{{$carttotals['taxname']}} @ {{$carttotals['taxrate']}}%:</span>
                    <span class="pull-right">{{$carttotals['taxtotal']}}</span>
                </div>
            @endif
            @if ($carttotals['taxrate2'])
                <div class="clearfix">
                    <span class="pull-left">{{$carttotals['taxname2']}} @ {{$carttotals['taxrate2']}}%:</span>
                    <span class="pull-right">{{$carttotals['taxtotal2']}}</span>
                </div>
            @endif
        @endif
    </div>
    <div class="total-due-today">
        <span class="amt">{{$carttotals['total']}}</span>
        <span>{{Lang::get('client.ordertotalduetoday')}}</span>
    </div>
@endif
