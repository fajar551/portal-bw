@extends('layouts.clientbase')

@section('page-title')
   Shopping Cart Add Domain
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
                                @if ($domain == "register")
                                    {{Lang::get('client.registerdomain')}}
                                @elseif ($domain == "transfer")
                                    {{Lang::get('client.transferdomain')}}
                                @endif
                            </h1>
                        </div>
            
                    </div>
            
                    <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">
            
                        @include('sidebar-categories')
            
                    </div>
            
                    <div class="col-md-9 pull-md-right">
            
                        @include('sidebar-categories-collapsed')
            
                        @if ($domain == 'register')
                            <p>{{Lang::get('orderForm.findNewDomain')}}</p>
                        @else
                            <p>{{Lang::get('orderForm.transferExistingDomain')}}</p>
                        @endif
                        
                        <form method="post" action="{{route('cart')}}" id="frmDomainSearch">
                            <input type="hidden" name="a" value="domainoptions" />
                            <input type="hidden" name="checktype" value="{{$domain}}" />
                            <input type="hidden" name="ajax" value="1" />
            
                            <div class="row domain-add-domain">
                                <div class="col-sm-8 col-xs-12 col-sm-offset-1">
                                    <div class="row domains-row">
                                        <div class="col-xs-9">
                                            <div class="input-group">
                                                <span class="input-group-addon">{{Lang::get('orderForm.www')}}</span>
                                                <input type="text" name="sld" value="{{$sld}}" id="inputDomain" class="form-control" autocapitalize="none" />
                                            </div>
                                        </div>
                                        <div class="col-xs-3 mt-4">
                                            <select name="tld" class="form-control">
                                                @if ($domain == 'register')
                                                    @foreach ($registertlds as $listtld)
                                                        <option 
                                                            value="{{$listtld}}"
                                                            @if ($listtld == $tld)
                                                                selected="selected"
                                                            @endif>
                                                            {{$listtld}}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach ($transfertlds as $listtld)
                                                        <option 
                                                            value="{{$listtld}}"
                                                            @if ($listtld == $tld)
                                                                selected="selected"
                                                            @endif>
                                                            {{$listtld}}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2 col-xs-12">
                                    <button type="submit" class="btn btn-primary btn-block" id="btnCheckAvailability">
                                        @if ($domain == "register")
                                            {{Lang::get('orderForm.check')}}
                                        @else
                                            {{Lang::get('client.domainstransfer')}}
                                        @endif
                                    </button>
                                </div>
                            </div>
            
                        </form>
            
                        <div class="domain-loading-spinner" id="domainLoadingSpinner">
                            <i class="fas fa-3x fa-spinner fa-spin"></i>
                        </div>
            
                        <form method="post" action="{{route('cart')}}?a=add&domain={{$domain}}">
                            <div class="domain-search-results" id="domainSearchResults"></div>
                        </form>
            
                    </div>
                </div>
            </div>
            @if ($availabilityresults)
                <script>
                    jQuery(document).ready(function() {
                        jQuery('#btnCheckAvailability').click();
                    });
                </script>
            @endif
        </div>
    </div>
@endsection
