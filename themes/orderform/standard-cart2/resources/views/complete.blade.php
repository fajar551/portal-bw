@extends('layouts.clientbase')

@section('page-title')
   Shopping Cart
@endsection

@section('content')
@include('common')
<div class="page-content">
    <div class="container-fluid">
        
        <div id="order-standard_cart">

            <div class="row">

                <div class="pull-md-right col-md-9">

                    <div class="header-lined">
                        <h1>{{Lang::get('client.orderconfirmation')}}</h1>
                    </div>

                </div>

                <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">

                    @include('sidebar-categories')

                </div>

                <div class="col-md-12 pull-md-right">

                    @include('sidebar-categories-collapsed')

                    <p class="text-center">{{Lang::get('client.orderreceived')}}</p>

                    <div class="row justify-content-center">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            <div class="alert alert-info order-confirmation">
                                {{Lang::get('client.ordernumberis')}} <span>{{$ordernumber}}</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-center">{{Lang::get('client.orderfinalinstructions')}}</p>

                    @if ($invoiceid && !$ispaid)
                        <div class="alert alert-warning text-center">
                            {!!Lang::get('client.ordercompletebutnotpaid')!!}
                            <br /><br />
                            <a href="viewinvoice.php?id={{$invoiceid}}" target="_blank" class="alert-link">
                                {!!Lang::get('client.invoicenumber')!!}{{$invoiceid}}
                            </a>
                        </div>
                    @endif

                    @if (isset($addons_html))
                        @foreach ($addons_html as $addon_html)
                            <div class="order-confirmation-addon-output">
                                {!!$addon_html!!}
                            </div>
                        @endforeach
                    @endif

                    @if (isset($ispaid) && $ispaid)
                        <!-- Enter any HTML code which should be displayed when a user has completed checkout here -->
                        <!-- Common uses of this include conversion and affiliate tracking scripts -->
                    @endif

                    <div class="text-center">
                        <a href="{{route('home')}}" class="btn btn-success">
                            {{Lang::get('orderForm.continueToClientArea')}}
                            &nbsp;<i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
