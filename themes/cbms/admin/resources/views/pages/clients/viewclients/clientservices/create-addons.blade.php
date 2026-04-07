@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Services</title>
@endsection

@section('styles')
    <!-- Date Picker -->
    <link href="{{ Theme::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">
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
                             
                            {{-- Row client select --}}
                            @include('includes.clientsearch')

                            {{-- Tab Nav --}}
                            @include('includes.tabnavclient')
                            <div class="row">
                                <div class="col-lg-12">
                                    @if (isset($invalidClientId))
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">Invalid User ID</h4>
                                                <hr>
                                                <p class="mb-0">Please <a href="{{ route('admin.pages.clients.viewclients.index') }}">Click here</a> to find correct Client ID </p>
                                            </div>
                                        </div>
                                    </div>
                                    @elseif (isset($invalidServiceId))
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">Invalid Product/Service ID</h4>
                                                <hr>
                                                <p>{{ __("admin.servicesnoproductsinfo") }} <a href="{{ route('admin.pages.orders.addneworder.index') }}">{{ __("admin.clickhere") }}</a> {{ __("admin.orderstoplacenew") }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @elseif (isset($invalidClientIdAndServiceId))
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">Invalid Client ID & Service ID</h4>
                                                <hr>
                                                <p>{{ __("admin.servicesnoproductsinfo") }} <a href="{{ route('admin.pages.orders.addneworder.index') }}">{{ __("admin.clickhere") }}</a> {{ __("admin.orderstoplacenew") }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if (isset($clientsdetails))
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3 border">
                                        <div class="row">
                                            <label class="col-sm-6 col-form-label">
                                                <h4>Add New Addon</h4>
                                            </label>
                                        </div>
                                        <form action="{{ route('admin.pages.clients.viewclients.clientservices.storeAddons') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                                            @csrf
                                            <input type="number" name="userid" value="{{ $userid }}" hidden>
                                            <input type="number" name="id" value="{{ $id }}" hidden>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            {{-- <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Order #</label>
                                                                <div class="col-sm-9">
                                                                    <label for="" class="col-form-label">{{ $orderid }} - <a href="{{ route('admin.pages.orders.vieworder.index', ['id' => $id]) }}">View Order</a></label>
                                                                </div>
                                                            </div> --}}
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Parent Product/Service</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="id" id="addonServiceId" style="width: 100%;">
                                                                        @foreach ($servicesarr as $k => $v)
                                                                        @php
                                                                            $color = $colorData = "";
                                                                            list($color, $value) = $v;
                                                                        @endphp
                                                                            <option value="{{ $k }}" @if ($id == $k) selected @endif {{ $color ? "style=\"background-color:$color\"" : "" }} >{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="inputRegDate" class="col-sm-3 col-form-label ">Registration Date</label>
                                                                <div class="col-sm-9">
                                                                    <div class="input-daterange input-group " id="inputRegDate">
                                                                        <input type="text" class="form-control" name="regdate" placeholder="dd/mm/yyyy" value="{{ old('regdate', $regdate) }}" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Predefined Addon</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="addonid" style="width: 100%;">
                                                                        <option value="0">None</option>
                                                                        @foreach ($predefaddons as $key => $addon)
                                                                            <option value="{{ $key }}" @if ($key == old('addonid')) selected @endif >{{ $addon }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Custome Name</label>
                                                                <div class="col-sm-9">
                                                                    <input type="text" class="form-control" name="name" placeholder="Custome Name" value="{{ old('name') }}" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Status</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="productstatus" style="width: 100%;">
                                                                        <option value="">None</option>
                                                                        {!! $productStatusList !!}
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="paymentmethod" class="col-sm-3 col-form-label">Payment Method</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="paymentmethod" id="paymentmethod" style="width: 100%;">
                                                                        <option value="">None</option>
                                                                        @foreach ($paymentmethodlist as $paymentmethod)
                                                                            <option value="{{ $paymentmethod["gateway"] }}" @if($paymentmethod["gateway"] == $gateway) selected @endif>
                                                                                {{ $paymentmethod["value"] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Subscription ID</label>
                                                                <div class="col-sm-9">
                                                                    <input type="number" min="0" name="subscriptionid" value="{{ old('subscriptionid') }}" class="form-control " placeholder="Subscription ID">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Admin Notes</label>
                                                                <div class="col-sm-9">
                                                                    <textarea name="notes" class="form-control" rows="4" placeholder="Add admin notes">{{ old('notes') }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Setup Fee</label>
                                                                <div class="col-sm-9">
                                                                    <input type="number" min="0" step="0.01" name="setupfee" class="form-control " placeholder="Setup Fee" value="{{ old('setupfee', '0.00') }}" >
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Recurring</label>
                                                                <div class="col-sm-5">
                                                                    <input type="number" min="0" step="0.01" name="recurring" class="form-control " placeholder="Recurring" value="{{ old('recurring', '0.00') }}" >
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{-- Note: This field is only appear in add form --}}
                                                                    <div class="custom-control custom-checkbox mt-2">
                                                                        <input type="checkbox" name="defaultpricing" class="custom-control-input" id="defaultpricing" value="1" checked >
                                                                        <label class="custom-control-label" for="defaultpricing"> Use Default</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Billing Cycle</label>
                                                                <div class="col-sm-9">
                                                                    <select name="billingcycle" class="form-control select-inline">
                                                                        {!! $billingcycleList !!}
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="inputNextDueDate" class="col-sm-3 col-form-label">Next Due Date</label>
                                                                <div class="col-sm-9">
                                                                    @if (in_array($billingcycle, array("One Time", "Free Account")))
                                                                        <label for="" class="col-form-label">N/A</label>
                                                                    @else
                                                                        {{-- <input type="text" class="form-control" name="oldnextduedate" placeholder="dd/mm/yyyy" value="{{ old('nextduedate') ?? $nextduedate }}" hidden /> --}}
                                                                        <div class="input-daterange input-group " id="inputNextDueDate">
                                                                            <input type="text" class="form-control" name="nextduedate" placeholder="dd/mm/yyyy" value="{{ old('nextduedate', $nextduedate) }}" />
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="inputTerminationDate" class="col-sm-3 col-form-label ">Termination Date</label>
                                                                <div class="col-sm-9">
                                                                    <div class="input-daterange input-group " id="inputTerminationDate">
                                                                        <input type="text" class="form-control" name="termination_date" placeholder="dd/mm/yyyy" value="{{ strpos($terminationDate, "0000") === false ? $terminationDate : "" }}" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Tax Addon</label>
                                                                <div class="col-sm-9">
                                                                    <div class="custom-control custom-checkbox mt-2">
                                                                        <input type="checkbox" name="tax" class="custom-control-input" id="tax" value="1" @if (old('tax')) checked @endif>
                                                                        <label class="custom-control-label" for="tax"> </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <div class="custom-control custom-checkbox mt-2">
                                                                <input type="checkbox" name="geninvoice" class="custom-control-input" id="geninvoice" value="1" @if (old('geninvoice')) checked @endif>
                                                                <label class="custom-control-label" for="geninvoice"> Generate Invoice after Adding</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <button type="submit" class="btn btn-success px-3 mr-2">Save Changes</button>
                                                            <button type="reset" class="btn btn-secondary">Cancel</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
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

@section('scripts')
    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    @stack('clientsearch')
    
    <script>
        @if (isset($clientsdetails))

        $(() => {
            $('#inputRegDate').datepicker(dateRangeOption);
            $('#inputTerminationDate').datepicker(dateRangeOption);
            $('#inputNextDueDate').datepicker(dateRangeOption);
        });

        @endif
    </script>
@endsection
