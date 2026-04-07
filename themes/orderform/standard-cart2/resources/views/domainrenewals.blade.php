@extends('layouts.clientbase')

@section('page-title')
   Domain Renewals
@endsection

@section('content')
@include('common')
<div class="page-content">
    <div class="container-fluid">
        <div id="order-standard_cart">
            <div class="row">

                <div class="pull-md-right col-md-9">
        
                    <div class="header-lined">
                        <h1>
                            {{isset($totalResults) && $totalResults > 1 ? Lang::get("client.domainrenewals"):Lang::get("client.domainrenewal")}}
                            @if (isset($totalResults) && $totalResults > 5)
                                <div class="pull-right">
                                    <input id="domainRenewalFilter" type="search" class="domain-renewals-filter form-control input-inline-100" placeholder="{{Lang::get('client.searchenterdomain')}}">
                                </div>
                            @endif
                        </h1>
                    </div>
        
                </div>
                <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">
        
                    @include('sidebar-categories')
        
                </div>
        
                <div class="col-md-9 pull-md-right">
        
                    @include('sidebar-categories-collapsed')
        
                    <div class="row">
        
                        <div class="col-md-8">
                            @if ($totalResults < $totalDomainCount)
                                <div class="text-center">
                                    {{Lang::get("client.domainRenewal.showingDomains", ["showing" => $totalResults, "totalCount" => $totalDomainCount])}}
                                    <a id="linkShowAll" href="{routePath('cart-domain-renewals')}">{{Lang::get("client.domainRenewal.showAll")}}</a>
                                </div>
                            @endif
        
                            <div id="domainRenewals" class="domain-renewals">
                                @forelse ($renewalsData as $renewalData)
                                    <div class="domain-renewal" data-domain="{{$renewalData['domain']}}">
                                        <div class="pull-right">
                                            @if (!$renewalData['eligibleForRenewal'])
                                                <span class="label label-info">
                                                    {{Lang::get("client.domainRenewal.unavailable")}}
                                                </span>
                                            @elseif (($renewalData['pastGracePeriod'] && $renewalData['pastRedemptionGracePeriod']))
                                                <span class="label label-info">
                                                    {{Lang::get("client.domainrenewalspastgraceperiod")}}
                                                </span>
                                            @elseif (!$renewalData['beforeRenewLimit'] && $renewalData['daysUntilExpiry'] > 0)
                                                <span class="label label-{{$renewalData['daysUntilExpiry'] > 30?'success':'warning'}}">
                                                    {lang key='' }
                                                    {{Lang::get("client.domainRenewal.expiringIn", ["days"=>$renewalData['daysUntilExpiry']])}}
                                                </span>
                                            @elseif ($renewalData['daysUntilExpiry'] === 0)
                                                <span class="label label-grey">
                                                    {{Lang::get("client.expiresToday")}}
                                                </span>
                                            @elseif ($renewalData['beforeRenewLimit'])
                                                <span class="label label-info">
                                                    {{Lang::get("client.domainRenewal.maximumAdvanceRenewal", ["days"=>$renewalData['beforeRenewLimitDays']])}}
                                                </span>
                                            @else
                                                <span class="label label-danger">
                                                    {{Lang::get("client.domainRenewal.expiredDaysAgo", ["days"=>$renewalData['daysUntilExpiry']*-1])}}
                                                </span>
                                            @endif
                                        </div>
        
                                        <h3>{{$renewalData['domain']}}</h3>
        
                                        <p>{{Lang::get("client.clientareadomainexpirydate")}}: {{$renewalData['expiryDate']->format('j M Y')}} ({{$renewalData['expiryDate']->diffForHumans()}})</p>
        
                                        @if (($renewalData['pastGracePeriod'] && $renewalData['pastRedemptionGracePeriod']))
                                        @else
                                            <form class="form-horizontal">
                                                <div class="form-group row">
                                                    <label for="renewalPricing{{$renewalData['id']}}" class="control-label col-md-5">
                                                        {{Lang::get("client.domainRenewal.availablePeriods")}}
                                                        @if ($renewalData['inGracePeriod'] || $renewalData['inRedemptionGracePeriod'])
                                                            *
                                                        @endif
                                                    </label>
                                                    <div class="col-sm-6">
                                                        <select class="form-control select-renewal-pricing" id="renewalPricing{{$renewalData['id']}}" data-domain-id="{{$renewalData['id']}}">
                                                            @foreach ($renewalData['renewalOptions'] as $renewalOption)
                                                                <option value="{{$renewalOption['period']}}">
                                                                    {{$renewalOption['period']}} {{Lang::get('client.orderyears')}} @ {{$renewalOption['rawRenewalPrice']}}
                                                                    @if ($renewalOption['gracePeriodFee'] && $renewalOption['gracePeriodFee']->toNumeric() != 0.00)
                                                                        + {{$renewalOption['gracePeriodFee']}} {{Lang::get("client.domainRenewal.graceFee")}}
                                                                    @endif
                                                                    @if ($renewalOption['redemptionGracePeriodFee'] && $renewalOption['redemptionGracePeriodFee']->toNumeric() != 0.00)
                                                                        + {{$renewalOption['redemptionGracePeriodFee']}} {{Lang::get("client.domainRenewal.redemptionFee")}}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </form>
                                        @endif
        
                                        <div class="text-right">
                                            @if (!$renewalData['eligibleForRenewal'] || $renewalData['beforeRenewLimit'] || ($renewalData['pastGracePeriod'] && $renewalData['pastRedemptionGracePeriod']))
                                            @else
                                                <button id="renewDomain{{$renewalData['id']}}" class="btn btn-default btn-sm btn-add-renewal-to-cart" data-domain-id="{{$renewalData['id']}}">
                                                    <span class="to-add">
                                                        <i class="fas fa-fw fa-spinner fa-spin"></i>
                                                        {{Lang::get("client.addtocart")}}
                                                    </span>
                                                    <span class="added">{{Lang::get("client.domaincheckeradded")}}</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="no-domains">
                                        {{Lang::get("client.domainRenewal.noDomains")}}
                                    </div>
                                @endforelse
                            </div>
        
                            <div class="text-center">
                                <small>
                                    {if $hasDomainsInGracePeriod}
                                        * {lang key='domainRenewal.graceRenewalPeriodDescription'}
                                    {/if}
                                </small>
                            </div>
                        </div>
        
                        <div class="col-md-4" id="scrollingPanelContainer">
        
                            <div id="orderSummary">
                                <div class="order-summary">
                                    <div class="loader" id="orderSummaryLoader">
                                        <i class="fas fa-fw fa-sync fa-spin"></i>
                                    </div>
                                    <h2>{lang key='ordersummary'}</h2>
                                    <div class="summary-container" id="producttotal"></div>
                                </div>
                                <div class="text-center">
                                    <a id="btnGoToCart" class="btn btn-primary btn-lg" href="{$WEB_ROOT}/cart.php?a=view">
                                        {lang key='viewcart'}
                                        <i class="glyphicon glyphicon-shopping-cart"></i>
                                    </a>
                                </div>
                            </div>
        
                        </div>
                    </div>
                </div>
            </div>
            <form id="removeRenewalForm" method="post" action="{$WEB_ROOT}/cart.php">
                <input type="hidden" name="a" value="remove" />
                <input type="hidden" name="r" value="" id="inputRemoveItemType" />
                <input type="hidden" name="i" value="" id="inputRemoveItemRef" />
                <div class="modal fade modal-remove-item" id="modalRemoveItem" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="{lang key='orderForm.close'}">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title">
                                    <i class="fas fa-times fa-3x"></i>
                                    <span>{lang key='orderForm.removeItem'}</span>
                                </h4>
                            </div>
                            <div class="modal-body">
                                {lang key='cartremoveitemconfirm'}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{lang key='no'}</button>
                                <button type="submit" class="btn btn-primary">{lang key='yes'}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>recalculateRenewalTotals();</script>
@endsection
