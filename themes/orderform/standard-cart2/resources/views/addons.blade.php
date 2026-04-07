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
                            <h1>{{Lang::get('client.cartproductaddons')}}</h1>
                        </div>
            
                    </div>
            
                    <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">
            
                        @include('sidebar-categories')
            
                    </div>
            
                    <div class="col-md-9 pull-md-right">
            
                        @include('sidebar-categories-collapsed')
            
                        @if (count($addons) == 0)
                            <div id="noAddons" class="alert alert-warning text-center" role="alert">
                                {!!Lang::get('client.cartproductaddonsnone')!!}
                            </div>
                            <p class="text-center">
                                <a href="{{url('/')}}" class="btn btn-success">
                                    <i class="fas fa-arrow-circle-left"></i>
                                    {{Lang::get('orderForm.returnToClientArea')}}
                                </a>
                            </p>
                        @endif
            
                        <div class="products">
                            <div class="row row-eq-height">
                                @foreach ($addons as $num => $addon)
                                    <div class="col-md-6">
                                        <div class="product clearfix" id="product{{$num}}">
                                            <form method="post" action="{{route('cart')}}?a=add" class="form-inline">
                                                @csrf
                                                <input type="hidden" name="aid" value="{{$addon['id']}}" />
                                                <header class="w-100">
                                                    <span>{{$addon['name']}}</span>
                                                    {{-- @if ($product['qty'])
                                                        <span class="qty">
                                                            {{$product['qty']}} {{Lang::get('client.orderavailable')}}
                                                        </span>
                                                    @endif --}}
                                                </header>
                                                <div class="product-desc">
                                                    <p>{{$addon['description']}}</p>
                                                    <div class="form-group">
                                                        <select name="productid" id="inputProductId{{$num}}" class="field">
                                                            @foreach ($addon['productids'] as $product)
                                                                <option value="{{$product['id']}}">
                                                                    {{$product['product']}}
                                                                    @if ($product['domain'])
                                                                        - {{$product['domain']}}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <footer>
                                                    <div class="product-pricing">
                                                        @if ($addon['free'])
                                                            {{Lang::get('client.orderfree')}}
                                                        @else
                                                            <span class="price">{{$addon['recurringamount']}} {{$addon['billingcycle']}}</span>
                                                            @if ($addon['setupfee'])
                                                                <br />+ {{$addon['setupfee']}} {{Lang::get('client.ordersetupfee')}}
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-shopping-cart"></i>
                                                        {{Lang::get('client.ordernowbutton')}}
                                                    </button>
                                                </footer>
                                            </form>
                                        </div>
                                    </div>
                                    @if ($num % 2 != 0)
                                        </div>
                                        <div class="row row-eq-height">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
