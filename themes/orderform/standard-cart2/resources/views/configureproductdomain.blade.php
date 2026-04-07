@extends('layouts.clientbase')

@section('page-title')
   Shopping Cart 
@endsection

@section('content')
@include('common')

<style>
#domainregister .input-group .form-control,
#domainregister .input-group .form-select {
    border-radius: 0; 
    width: auto; 
}

#domainregister .input-group .form-select {
    margin-left: 4px; 
    border-left: none; 
}

#domainregister .input-group-text {
    border-right: none; 
}

#domainregister .btn {
    margin-left: 5px; 
}

#domaintransfer .input-group .form-control,
#domaintransfer .input-group .form-select {
    border-radius: 0; 
    width: auto; 
}

#domaintransfer .input-group .form-select {
    margin-left: 4px; 
    border-left: none; 
}

#domaintransfer .input-group-text {
    border-right: none; 
}

#domaintransfer .btn {
    margin-left: 5px; 
}


</style>

<div class="page-content">
    <div class="container-fluid">
        <div id="order-standard_cart">
            <div class="row">
                <div class="pull-md-right col-md-9">

                    <div class="header-lined">
                        <h1>Register your domain here...</h1>
                    </div>
        
                </div>

                <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">
                    @include('sidebar-categories')
                </div>

                <div class="col-md-9 pull-md-right">
                    @include('sidebar-categories-collapsed')
                    <form id="frmProductDomain">
                        @csrf
                        <input type="hidden" id="frmProductDomainPid" value="{{$pid}}" />
                        <div class="domain-selection-options">
                            @if ($incartdomains)
                                <div class="option">
                                    <label>
                                        <input type="radio" name="domainoption" value="incart" id="selincart" />{{Lang::get('client.cartproductdomainuseincart')}}
                                    </label>
                                    <div class="domain-input-group clearfix" id="domainincart">
                                        <div class="row">
                                            <div class="col-sm-8 col-sm-offset-1 col-md-6 col-md-offset-2">
                                                <div class="domains-row">
                                                    <select id="incartsld" name="incartdomain" class="form-control">
                                                        @foreach ($incartdomains as $incartdomain)
                                                            <option value="{{$incartdomain}}">{{$incartdomain}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    {{Lang::get('orderForm.use')}}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($registerdomainenabled)
                            <div class="option">
                                <label>
                                    <input type="radio" name="domainoption" value="register" id="selregister" {{$domainoption == "register" ? 'checked' : '' }} />
                                    {{sprintf(Lang::get('client.cartregisterdomainchoice'), $companyname ?? "")}}
                                </label>
                                <div class="domain-input-group clearfix mt-3" id="domainregister">
                                    <div class="row g-2 align-items-center">
                                        <!-- Input Group -->
                                        <div class="col-auto">
                                            <div class="input-group">
                                                <span class="input-group-text">www.</span>
                                                <input type="text" id="registersld" value="{{$sld}}" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{{Lang::get('orderForm.enterDomain')}}" />
                                                <select id="registertld" class="form-control">
                                                    @foreach ($registertlds as $listtld)
                                                        <option value="{{$listtld}}" 
                                                        @if ($listtld == $tld)
                                                            selected="selected"
                                                        @endif>{{$listtld}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                            
                                        <!-- Button -->
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-primary">
                                                {{Lang::get('orderForm.check')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @endif
                            @if ($transferdomainenabled)
                            <div class="option">
                                <label>
                                    <input type="radio" name="domainoption" value="transfer" id="seltransfer" {{$domainoption == "transfer" ? 'checked' : '' }} />
                                    {{sprintf(Lang::get('client.carttransferdomainchoice'), $companyname ?? "")}}
                                </label>
                                <div class="domain-input-group clearfix mt-3" id="domaintransfer">
                                    <div class="row align-items-center g-2">
                                        <!-- Input Group -->
                                        <div class="col-auto">
                                            <div class="input-group">
                                                <span class="input-group-text">www.</span>
                                                <input type="text" id="transfersld" value="{{$sld}}" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{{Lang::get('orderForm.enterDomain')}}" />
                                                <select id="transfertld" class="form-control">
                                                    @foreach ($transfertlds as $listtld)
                                                        <option value="{{$listtld}}" 
                                                        @if ($listtld == $tld)
                                                            selected="selected"
                                                        @endif>{{$listtld}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                            
                                        <!-- Button -->
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-primary">
                                                {{Lang::get('orderForm.transfer')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @endif

                            @if ($owndomainenabled)
                                <div class="option">
                                    <label>
                                        <input type="radio" name="domainoption" value="owndomain" id="selowndomain" {{$domainoption == "owndomain" ? 'checked' : ''}} />{{sprintf(Lang::get('client.cartexistingdomainchoice'), $companyname ?? "")}}
                                    </label>
                                    <div class="domain-input-group clearfix" id="domainowndomain">
                                        <div class="row">
                                            <div class="col-sm-9">
                                                <div class="row domains-row">
                                                    <div class="col-xs-2 text-right">
                                                        <p class="form-control-static">www.</p>
                                                    </div>
                                                    <div class="col-xs-7">
                                                        <input type="text" id="owndomainsld" value="{{$sld}}" placeholder="{{Lang::get('client.yourdomainplaceholder')}}" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{{Lang::get('orderForm.enterDomain')}}" />
                                                    </div>
                                                    <div class="col-xs-3">
                                                        <input type="text" id="owndomaintld" value="{{substr($tld, 1)}}" placeholder="{{Lang::get('client.yourtldplaceholder')}}" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{{Lang::get('orderForm.required')}}" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <button type="submit" class="btn btn-primary btn-block" id="useOwnDomain">
                                                    {{Lang::get('orderForm.use')}}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($subdomains)
                                <div class="option">
                                    <label>
                                        <input type="radio" name="domainoption" value="subdomain" id="selsubdomain" {{$domainoption == "subdomain"? 'checked':''}} />{{sprintf(Lang::get('client.cartsubdomainchoice'), $companyname ?? "")}}
                                    </label>
                                    <div class="domain-input-group clearfix" id="domainsubdomain">
                                        <div class="row">
                                            <div class="col-sm-9">
                                                <div class="row domains-row">
                                                    <div class="col-xs-2 text-right">
                                                        <p class="form-control-static">http://</p>
                                                    </div>
                                                    <div class="col-xs-5">
                                                        <input type="text" id="subdomainsld" value="{{$sld}}" placeholder="yourname" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{{Lang::get('orderForm.enterDomain')}}" />
                                                    </div>
                                                    <div class="col-xs-5">
                                                        <select id="subdomaintld" class="form-control">
                                                            @foreach ($subdomains as $subid => $subdomain)
                                                                <option value="{{$subid}}">{{$subdomain}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    {{Lang::get('orderForm.check')}}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if ($freedomaintlds)
                            <p>* <em>{{Lang::get('client.orderfreedomainregistration')}} {{Lang::get('client.orderfreedomainappliesto')}}: {{$freedomaintlds}}</em></p>
                        @endif
                    </form>
                    <div class="clearfix"></div>
                    <form method="post" action="{{route('cart')}}?a=add&pid={{$pid}}&domainselect=1" id="frmProductDomainSelections">
                    @csrf
                        <div id="DomainSearchResults" class="hidden">
        
                            <div id="searchDomainInfo">
                                <p id="primaryLookupSearching" class="domain-lookup-loader domain-lookup-primary-loader domain-searching domain-checker-result-headline">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span class="domain-lookup-register-loader">{{Lang::get('orderForm.checkingAvailability')}}...</span>
                                    <span class="domain-lookup-transfer-loader">{{Lang::get('orderForm.verifyingTransferEligibility')}}...</span>
                                    <span class="domain-lookup-other-loader">{{Lang::get('orderForm.verifyingDomain')}}...</span>
                                </p>
                                <div id="primaryLookupResult" class="domain-lookup-result domain-lookup-primary-results hidden">
                                    <div class="domain-unavailable domain-checker-unavailable headline">{!!Lang::get('orderForm.domainIsUnavailable')!!}</div>
                                    <div class="domain-available domain-checker-available headline">{!!Lang::get('client.domainavailable1')!!} <strong></strong> {{Lang::get('client.domainavailable2')}}</div>
                                    <div class="btn btn-primary domain-contact-support headline">{!!Lang::get('client.domainContactUs')!!}</div>
                                    <div class="transfer-eligible">
                                        <p class="domain-checker-available headline">{!!Lang::get('orderForm.transferEligible')!!}</p>
                                        <p>{!!Lang::get('orderForm.transferUnlockBeforeContinuing')!!}</p>
                                    </div>
                                    <div class="transfer-not-eligible">
                                        <p class="domain-checker-unavailable headline">{!!Lang::get('orderForm.transferNotEligible')!!}</p>
                                        <p>{!!Lang::get('orderForm.transferNotRegistered')!!}</p>
                                        <p>{!!Lang::get('orderForm.trasnferRecentlyRegistered')!!}</p>
                                        <p>{!!Lang::get('orderForm.transferAlternativelyRegister')!!}</p>
                                    </div>
                                    <div class="domain-invalid">
                                        <p class="domain-checker-unavailable headline">{!!Lang::get('orderForm.domainInvalid')!!}</p>
                                        <p>
                                            {!!Lang::get('orderForm.domainLetterOrNumber')!!}<span class="domain-length-restrictions">{!!Lang::get('orderForm.domainLengthRequirements')!!}</span><br />
                                            {!!Lang::get('orderForm.domainInvalidCheckEntry')!!}
                                        </p>
                                    </div>
                                    <div class="domain-price">
                                        <span class="register-price-label">{!!Lang::get('orderForm.domainPriceRegisterLabel')!!}</span>
                                        <span class="transfer-price-label hidden">{!!Lang::get('orderForm.domainPriceTransferLabel')!!}</span>
                                        <span class="price"></span>
                                    </div>
                                    <p class="domain-error domain-checker-unavailable headline"></p>
                                    <input type="hidden" id="resultDomainOption" name="domainoption" />
                                    <input type="hidden" id="resultDomain" name="domains[]" />
                                    <input type="hidden" id="resultDomainPricingTerm" />
                                </div>
                            </div>
                            
                            @if ($registerdomainenabled)
                            {{-- dikomen dulu rekomendasinya --}}
                                {{--@if ($spotlightTlds)
                                    <div id="spotlightTlds" class="spotlight-tlds clearfix hidden">
                                        <div class="spotlight-tlds-container">
                                            @foreach ($spotlightTlds as $key => $data)
                                                <div class="spotlight-tld-container spotlight-tld-container-{{count($spotlightTlds)}}">
                                                    <div id="spotlight{{$data['tldNoDots']}}" class="spotlight-tld">
                                                        @if ($data['group'])
                                                            <div class="spotlight-tld-{{$data['group']}}">{{$data['groupDisplayName']}}</div>
                                                        @endif
                                                        {{$data['tld']}}
                                                        <span class="domain-lookup-loader domain-lookup-spotlight-loader">
                                                            <i class="fas fa-spinner fa-spin"></i>
                                                        </span>
                                                        <div class="domain-lookup-result">
                                                            <button type="button" class="btn unavailable hidden" disabled="disabled">
                                                                {{Lang::get('client.domainunavailable')}}
                                                            </button>
                                                            <button type="button" class="btn invalid hidden" disabled="disabled">
                                                                {{Lang::get('client.domainunavailable')}}
                                                            </button>
                                                            <span class="available price hidden">{{$data['register']}}</span>
                                                            <button type="button" class="btn hidden btn-add-to-cart product-domain" data-whois="0" data-domain="">
                                                                <span class="to-add">{{Lang::get('orderForm.add')}}</span>
                                                                <span class="added">{{Lang::get('client.domaincheckeradded')}}</span>
                                                                <span class="unavailable">{{Lang::get('client.domaincheckertaken')}}</span>
                                                            </button>
                                                            <button type="button" class="btn btn-primary domain-contact-support hidden">Contact Support to Purchase</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif--}}

                                <div class="suggested-domains hidden">
                                    <div class="panel-heading">
                                        {{Lang::get('orderForm.suggestedDomains')}}
                                    </div>
                                    <div id="suggestionsLoader" class="panel-body domain-lookup-loader domain-lookup-suggestions-loader">
                                        <i class="fas fa-spinner fa-spin"></i> {{Lang::get('orderForm.generatingSuggestions')}}
                                    </div>
                                    <ul id="domainSuggestions" class="domain-lookup-result list-group hidden">
                                        <li class="domain-suggestion list-group-item hidden">
                                            <span class="domain"></span><span class="extension"></span>
                                            <button type="button" class="btn btn-add-to-cart product-domain" data-whois="1" data-domain="">
                                                <span class="to-add">{{Lang::get('client.addtocart')}}</span>
                                                <span class="added">{{Lang::get('client.domaincheckeradded')}}</span>
                                                <span class="unavailable">{{Lang::get('client.domaincheckertaken')}}</span>
                                            </button>
                                            <button type="button" class="btn btn-primary domain-contact-support hidden">Contact Support to Purchase</button>
                                            <span class="price"></span>
                                            <span class="promo hidden"></span>
                                        </li>
                                    </ul>
                                    <div class="panel-footer more-suggestions hidden text-center">
                                        <a id="moreSuggestions" href="#" onclick="loadMoreSuggestions();return false;">{{Lang::get('client.domainsmoresuggestions')}}</a>
                                        <span id="noMoreSuggestions" class="no-more small hidden">{{Lang::get('client.domaincheckernomoresuggestions')}}</span>
                                    </div>
                                    <div class="text-center text-muted domain-suggestions-warning">
                                        <p>{{Lang::get('client.domainssuggestionswarnings')}}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
        
                        <div class="text-center">
                            <button id="btnDomainContinue" type="submit" class="btn btn-primary btn-lg hidden" disabled="disabled">
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
