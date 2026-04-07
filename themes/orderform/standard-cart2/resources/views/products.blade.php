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
                        <h1 class="header-pretitle d-md-block font-weight-bold">
                            @if ($productGroup['headline'])
                                {{$productGroup['headline']}}
                            @else
                                {{{$productGroup['name']}}}
                            @endif
                        </h1>
                        @if ($productGroup['tagline'])
                            <p>{{$productGroup['tagline']}}</p>
                        @endif
                    </div>
                    @if (isset($errormessage) && $errormessage)
                        <div class="alert alert-danger">
                            {{$errormessage}}
                        </div>
                    @endif
                </div>

                <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">
                    @include('sidebar-categories')
                </div>

                <div class="col-md-9 pull-md-right mb-4">

                    @include('sidebar-categories-collapsed')
        
                    @foreach ($hookAboveProductsOutput as $output)
                        <div>
                            {!!$output!!}
                        </div>
                    @endforeach
        
                    <div class="products" id="products">
                        <div class="row row-eq-height">
                            @foreach ($products as $key => $product)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="product clearfix" id="product{{$loop->iteration}}">
                                                <header>
                                                    <span id="product{{$loop->iteration}}-name">{{$product['name']}}</span>
                                                    @if ($product['qty'])
                                                        <span class="qty">
                                                            {{$product['qty']}} {{Lang::get('client.orderavailable')}}
                                                        </span>
                                                    @endif
                                                </header>
                                                <div class="product-desc">
                                                    @if ($product['featuresdesc'])
                                                        <p id="product{{$loop->iteration}}-description">
                                                            {!!$product['featuresdesc']!!}
                                                        </p>
                                                    @endif
                                                    <ul>
                                                        @foreach ($product['features'] as $feature => $value)
                                                            <li id="product{{$loop->iteration}}-feature{{$loop->iteration}}">
                                                                <span class="feature-value">{{$value}}</span>
                                                                {{$feature}}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <footer>
                                                    <div class="product-pricing" id="product{{$loop->iteration}}-price">
                                                        @if ($product['bid'])
                                                            {{Lang::get('client.bundledeal')}}<br />
                                                            @if ($product['displayprice'])
                                                                <span class="price">{{$product['displayprice']}}</span>
                                                            @endif
                                                        @else
                                                            @if ($product['pricing']['hasconfigoptions'])
                                                                {{Lang::get('client.startingfrom')}}<br/>
                                                            @endif
                                                            <span class="price">{{$product['pricing']['minprice']['price']}}</span>
                                                            <br />
                                                            <span class="text-muted font-weight-normal">@if ($product['pricing']['minprice']['cycle'] == "monthly")
                                                                {{Lang::get('client.orderpaymenttermmonthly')}}
                                                            @elseif ($product['pricing']['minprice']['cycle'] == "quarterly")
                                                                {{Lang::get('client.orderpaymenttermquarterly')}}
                                                            @elseif ($product['pricing']['minprice']['cycle'] == "semiannually")
                                                                {{Lang::get('client.orderpaymenttermsemiannually')}}
                                                            @elseif ($product['pricing']['minprice']['cycle'] == "annually")
                                                                {{Lang::get('client.orderpaymenttermannually')}}
                                                            @elseif ($product['pricing']['minprice']['cycle'] == "biennially")
                                                                {{Lang::get('client.orderpaymenttermbiennially')}}
                                                            @elseif ($product['pricing']['minprice']['cycle'] == "triennially")
                                                                {{Lang::get('client.orderpaymenttermtriennially')}}
                                                            @endif
                                                            <br>
                                                            @if ($product['pricing']['minprice']['setupFee'])
                                                                <small>{{$product['pricing']['minprice']['setupFee']->toPrefixed()}} {{Lang::get('client.ordersetupfee')}}</small>
                                                            @endif
                                                        @endif</span>
                                                    </div>
                                                    <a href="{{route('cart')}}?a=add&{{$product['bid']?"bid={$product['bid']}" : "pid={$product['pid']}"}}" class="btn btn-success btn-sm" id="product{{$loop->iteration}}-order-button">
                                                        <!--<i class="fas fa-shopping-cart"></i>-->
                                                        {{Lang::get('client.ordernowbutton')}}
                                                    </a>
                                                </footer>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($loop->iteration % 2 == 0)
                                    </div>
                                    <div class="row row-eq-height">
                                @endif
                            @endforeach
                        </div>
                    </div>
        
                    @foreach ($hookBelowProductsOutput as $output)
                        <div>
                            {!!$output!!}
                        </div>
                    @endforeach
        
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
