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
                        <h1>{{Lang::get('client.cartdomainsconfig')}}</h1>
                    </div>
        
                </div>
        
                <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">
        
                    @include('sidebar-categories')
        
                </div>
        
                <div class="col-md-9 pull-md-right">
        
                    @include('sidebar-categories-collapsed')
        
                    <form method="post" action="{{route('cart')}}?a=confdomains" id="frmConfigureDomains">
                        <input type="hidden" name="update" value="true" />
        
                        <p>{{Lang::get('orderForm.reviewDomainAndAddons')}}</p>
        
                        @if (isset($errormessage) && $errormessage)
                            <div class="alert alert-danger" role="alert">
                                <p>{{Lang::get('orderForm.correctErrors')}}:</p>
                                <ul>
                                    {!!$errormessage!!}
                                </ul>
                            </div>
                        @endif
        
                        @foreach ($domains as $num => $domain)
                            <div class="sub-heading">
                                <span>{{$domain['domain']}}</span>
                            </div>
        
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>{{Lang::get('client.orderregperiod')}}</label>
                                        <br />
                                        {{$domain['regperiod']}} {{Lang::get('client.orderyears')}}
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>{{Lang::get('client.hosting')}}</label>
                                        <br />
                                        @if ($domain['hosting'])
                                            <span style="color:#009900;">[{{Lang::get('client.cartdomainshashosting')}}]</span>
                                        @else
                                            <a href="cart.php" style="color:#cc0000;">[{{Lang::get('client.cartdomainsnohosting')}}]</a>
                                        @endif
                                    </div>
                                </div>
                                @if ($domain['eppenabled'])
                                    <div class="col-sm-12">
                                        <div class="form-group prepend-icon">
                                            <input type="text" name="epp[{{$num}}]" id="inputEppcode{{$num}}" value="{{$domain['eppvalue']}}" class="field" placeholder="{{Lang::get('client.domaineppcode')}}" />
                                            <label for="inputEppcode{{$num}}" class="field-icon">
                                                <i class="fas fa-lock"></i>
                                            </label>
                                            <span class="field-help-text">
                                                {{Lang::get('client.domaineppcodedesc')}}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
        
                            @if ($domain['dnsmanagement'] || $domain['emailforwarding'] || $domain['idprotection'])
                                <div class="row addon-products">
            
                                    @if ($domain['dnsmanagement'])
                                        @php
                                            $numAddons = $domain['addonsCount'];
                                            $cls = 12 / $numAddons;
                                        @endphp
                                        {{-- <div class="col-sm-{math equation="12 / numAddons" numAddons=$domain.addonsCount}"> --}}
                                        <div class="col-sm-{{$cls}}">
                                            <div class="panel panel-default panel-addon {{$domain['dnsmanagementselected']? 'panel-addon-selected':''}}">
                                                <div class="panel-body">
                                                    <label>
                                                        <input type="checkbox" name="dnsmanagement[{{$num}}]" {{$domain['dnsmanagementselected']? 'checked':''}} />
                                                        {{Lang::get('client.domaindnsmanagement')}}
                                                    </label><br />
                                                    {{Lang::get('client.domainaddonsdnsmanagementinfo')}}
                                                </div>
                                                <div class="panel-price">
                                                    {{$domain['dnsmanagementprice']}} / {{$domain['regperiod']}} {{Lang::get('client.orderyears')}}
                                                </div>
                                                <div class="panel-add">
                                                    <i class="fas fa-plus"></i>
                                                    {{Lang::get('orderForm.addToCart')}}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
        
                                    @if ($domain['idprotection'])
                                        @php
                                            $numAddons = $domain['addonsCount'];
                                            $cls = 12 / $numAddons;
                                        @endphp
                                        {{-- <div class="col-sm-{math equation="12 / numAddons" numAddons=$domain.addonsCount}"> --}}
                                        <div class="col-sm-{{$cls}}">
                                            <div class="panel panel-default panel-addon {{$domain['idprotectionselected']? 'panel-addon-selected':''}}">
                                                <div class="panel-body">
                                                    <label>
                                                        <input type="checkbox" name="idprotection[{{$num}}]" {{$domain['idprotectionselected']? 'checked':''}} />
                                                        {{Lang::get('client.domainidprotection')}}
                                                        </label><br />
                                                    {{Lang::get('client.domainaddonsidprotectioninfo')}}
                                                </div>
                                                <div class="panel-price">
                                                    {{$domain['idprotectionprice']}} / {{$domain['regperiod']}} {{Lang::get('client.orderyears')}}
                                                </div>
                                                <div class="panel-add">
                                                    <i class="fas fa-plus"></i>
                                                    {{Lang::get('orderForm.addToCart')}}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
        
                                    @if ($domain['emailforwarding'])
                                        @php
                                            $numAddons = $domain['addonsCount'];
                                            $cls = 12 / $numAddons;
                                        @endphp
                                        <div class="col-sm-{{$cls}}">
                                            <div class="panel panel-default panel-addon {{$domain['emailforwardingselected']? 'panel-addon-selected':''}}">
                                                <div class="panel-body">
                                                    <label>
                                                        <input type="checkbox" name="emailforwarding[{{$num}}]" {{$domain['emailforwardingselected']? 'checked':''}} />
                                                        {{Lang::get('client.domainemailforwarding')}}
                                                    </label><br />
                                                    {{Lang::get('client.domainaddonsemailforwardinginfo')}}
                                                </div>
                                                <div class="panel-price">
                                                    {{$domain['emailforwardingprice']}} / {{$domain['regperiod']}} {{Lang::get('client.orderyears')}}
                                                </div>
                                                <div class="panel-add">
                                                    <i class="fas fa-plus"></i>
                                                    {{Lang::get('orderForm.addToCart')}}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
        
                                </div>
                            @endif
                            @foreach ($domain['fields'] as $domainfieldname => $domainfield)
                                <div class="row">
                                    <div class="col-sm-4">{{$domainfieldname}}:</div>
                                    <div class="col-sm-8">{!!$domainfield!!}</div>
                                </div>
                            @endforeach
                        @endforeach
                        
                        @if ($atleastonenohosting)
                            <div class="sub-heading">
                                <span>{{Lang::get('client.domainnameservers')}}</span>
                            </div>
        
                            <p>{{Lang::get('client.cartnameserversdesc')}}</p>
        
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="inputNs1">{{Lang::get('client.domainnameserver1')}}</label>
                                        <input type="text" class="form-control" id="inputNs1" name="domainns1" value="{{$domainns1}}" />
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="inputNs2">{{Lang::get('client.domainnameserver2')}}</label>
                                        <input type="text" class="form-control" id="inputNs2" name="domainns2" value="{{$domainns2}}" />
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="inputNs3">{{Lang::get('client.domainnameserver3')}}</label>
                                        <input type="text" class="form-control" id="inputNs3" name="domainns3" value="{{$domainns3}}" />
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="inputNs1">{{Lang::get('client.domainnameserver4')}}</label>
                                        <input type="text" class="form-control" id="inputNs4" name="domainns4" value="{{$domainns4}}" />
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="inputNs5">{{Lang::get('client.domainnameserver5')}}</label>
                                        <input type="text" class="form-control" id="inputNs5" name="domainns5" value="{{$domainns5}}" />
                                    </div>
                                </div>
                            </div>
                        @endif
        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                {{Lang::get('client.continue')}}
                                &nbsp;<i class="fas fa-arrow-circle-right"></i>
                            </button>
                        </div>
        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
