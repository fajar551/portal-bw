@extends('layouts.clientbase')

@section('title')
   
@endsection

@section('page-title')
   Upgrade Summary
@endsection

@section('content')
    <div class="page-content bg-white" id="service-detailsx">
        <div class="container-fluid">
            @if ($promoerror)
                @include('includes.alert', [
                    'type' => 'error',
                    'msg' => $promoerror,
                    'textcenter' => true,
                ])
            @endif

            @if ($promorecurring)
                @include('includes.alert', [
                    'type' => 'info',
                    'msg' => sprintf(Lang::get('client.recurringpromodesc'), $promorecurring),
                    'textcenter' => true,
                ])
            @endif

            <div class="alert alert-block alert-info text-center">
                {{Lang::get('client.upgradecurrentconfig')}}: <strong>{{$groupname}} - {{$productname}}</strong>@if ($domain) ({{$domain}}) @endif
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="60%">{{Lang::get('client.orderdesc')}}</th>
                        <th width="40%" class="text-center">{{Lang::get('client.orderprice')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($upgrades as $upgrade)
                        @if ($type == "package")
                            <tr>
                                <td><input type="hidden" name="pid" value="{{$upgrade['newproductid']}}" /><input type="hidden" name="billingcycle" value="{{$upgrade['newproductbillingcycle']}}" />{{$upgrade['oldproductname']}} => {{$upgrade['newproductname']}}</td>
                                <td class="text-center">{{$upgrade['price']}}</td>
                            </tr>
                        @elseif ($type == "configoptions")
                            <tr>
                                <td>{{$upgrade['configname']}}: {{$upgrade['originalvalue']}} => {{$upgrade['newvalue']}}</td>
                                <td class="text-center">{{$upgrade['price']}}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="masspay-total">
                        <td class="text-right">{{Lang::get('client.ordersubtotal')}}:</td>
                        <td class="text-center">{{$subtotal}}</td>
                    </tr>
                    @if ($promodesc)
                        <tr class="masspay-total">
                            <td class="text-right">{{$promodesc}}:</td>
                            <td class="text-center">{{$discount}}</td>
                        </tr>
                    @endif
                    @if (isset($taxrate))
                        <tr class="masspay-total">
                            <td class="text-right">{{$taxname}} @ {{$taxrate}}%:</td>
                            <td class="text-center">{{$tax}}</td>
                        </tr>
                    @endif
                    @if (isset($taxrate2))
                        <tr class="masspay-total">
                            <td class="text-right">{{$taxname2}} @ {{$taxrate2}}%:</td>
                            <td class="text-center">{{$tax2}}</td>
                        </tr>
                    @endif
                    <tr class="masspay-total">
                        <td class="text-right">{{Lang::get('client.ordertotalduetoday')}}:</td>
                        <td class="text-center">{{$total}}</td>
                    </tr>
                </tbody>
            </table>

            @if ($type == "package")
                @include('includes.alert', [
                    'type' => 'warning',
                    'msg' => Lang::get('client.upgradeproductlogic').' ('.$upgrade['daysuntilrenewal'].' '.Lang::get('client.days').')',
                    'textcenter' => true,
                ])
            @endif
            {{-- {if $type eq "package"}
                {include file="$template/includes/alert.tpl" type="" msg=$LANG.upgradeproductlogic|cat:' ('|cat:$upgrade.daysuntilrenewal|cat:' '|cat:$LANG.days|cat:')' textcenter=true}
            {/if} --}}

            <div class="row">
                <div class="col-sm-6">

                    <form method="post" action="" role="form">
                        @csrf
                        <input type="hidden" name="step" value="2" />
                        <input type="hidden" name="type" value="{{$type}}" />
                        <input type="hidden" name="id" value="{{$id}}" />
                        @if ($type == "package")
                            <input type="hidden" name="pid" value="{{$upgrades[0]['newproductid']}}" />
                            <input type="hidden" name="billingcycle" value="{{$upgrades[0]['newproductbillingcycle']}}" />
                        @endif
                        <h2>{{Lang::get('client.orderpromotioncode')}}</h2>
                        @foreach ($configoptions as $cid => $value)
                            <input type="hidden" name="configoption[{{$cid}}]" value="{{$value}}" />
                        @endforeach
                        <div class="input-group">
                            <input class="form-control" type="text" name="promocode" placeholder="{{Lang::get('client.orderpromotioncode')}}" width="40"
                                @if ($promocode)
                                    value="{{$promocode}} - {{$promodesc}}"
                                    disabled="disabled"
                                @endif>
                            @if ($promocode)
                                <span class="input-group-btn">
                                    <input type="submit" name="removepromo" value="{{Lang::get('client.orderdontusepromo')}}"
                                        class="btn btn-danger" />
                                </span>
                            @else
                                <span class="input-group-btn">
                                    <input type="submit" value="{{Lang::get('client.orderpromovalidatebutton')}}" class="btn btn-success" />
                                </span>
                            @endif
                        </div>
                    </form>

                </div>
                <div class="col-sm-6">

                    <form method="post" action="">
                        @csrf
                        <input type="hidden" name="step" value="3" />
                        <input type="hidden" name="type" value="{{$type}}" />
                        <input type="hidden" name="id" value="{{$id}}" />
                        @if ($type == "package")
                            <input type="hidden" name="pid" value="{{$upgrades[0]['newproductid']}}" />
                            <input type="hidden" name="billingcycle" value="{{$upgrades[0]['newproductbillingcycle']}}" />
                        @endif
                        @foreach ($configoptions as $cid => $value)
                            <input type="hidden" name="configoption[{{$cid}}]" value="{{$value}}" />
                        @endforeach
                        @if ($promocode)
                            <input type="hidden" name="promocode" value="{{$promocode}}">
                        @endif
                        <h2>{{Lang::get('client.orderpaymentmethod')}}</h2>
                        <div class="form-group">
                            <select name="paymentmethod" id="inputPaymentMethod" class="form-control">
                                @if ($allowgatewayselection)
                                    <option value="none">{{Lang::get('client.paymentmethoddefault')}}</option>
                                @endif
                                @foreach ($gateways as $gateway)
                                    <option value="{{$gateway['sysname']}}"
                                    @if ($gateway['sysname'] == $selectedgateway)
                                        selected="selected"
                                    @endif>{{$gateway['name']}}</option>
                                @endforeach
                            </select>
                        </div>

                </div>
            </div>

            <div class="form-group text-center">
                <input type="submit" value="{{Lang::get('client.ordercontinuebutton')}}" class="btn btn-primary" />
            </div>

            </form>

        </div>
    </div>
@endsection
