@extends('layouts.clientbase')

@section('page-title')
   Shopping Cart - Error!
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
                            {{Lang::get('client.thereisaproblem')}}
                        </h1>
                    </div>
        
                </div>
        
                <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">
        
                    @include('sidebar-categories')
        
                </div>
        
                <div class="col-md-9 pull-md-right">
        
                    @include('sidebar-categories-collapsed')
        
                    <div class="alert alert-danger error-heading">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{$errortitle}}
                    </div>
        
                    <div class="row">
                        <div class="col-sm-8 col-sm-offset-2">
        
                            <p class="margin-bottom">{{$errormsg}}</p>
        
                            <div class="text-center">
                                <a href="javascript:history.go(-1)" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i>&nbsp;
                                    {{Lang::get('client.problemgoback')}}
                                </a>
                            </div>
        
                        </div>
                    </div>
        
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
