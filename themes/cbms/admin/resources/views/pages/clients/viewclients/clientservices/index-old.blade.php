@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Services</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">

                    <div class="col-xl-12">
                        <div class="client-summary-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Client Profile</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    @if (session('message'))
                                        <div class="alert alert-{{ session('type') }}">
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                            <strong>{!! session('message') !!}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <form>
                                        <div class="form-group">
                                            <select name="profile_name" id="select-prof-name" class="form-control">
                                                @if (isset($clientsdetails))
                                                <option>{{ $clientsdetails["fullname"] }} - #{{ $clientsdetails["userid"] }}</option>
                                                @else
                                                <option>{{ __("None") }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            {{-- Tab Nav --}}
                            @include('includes.tabnavclient')

                            @if (isset($invalidId))
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">No Data Found!</h4>
                                                <hr>
                                                <p class="mb-0">No data found for this user. Please access this page with correct ID</p>
                                                {{-- <p>No domains found for this user. <a href="{{ url('admin/orders/add') }}">Click here</a> to place a new order..</p> --}}
                                                {{-- <p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if (!isset($invalidId))
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                Lorem ipsum
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
