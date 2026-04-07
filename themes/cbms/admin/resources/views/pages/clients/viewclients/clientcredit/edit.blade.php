@extends('layouts.base-without-sidebar')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Credit</title>
@endsection

@section('styles')
    <!-- Date Picker -->
    <link href="{{ Theme::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')
    <div class="">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-1">
                                    <div class=" ">
                                        <a href="{{ route("admin.pages.clients.viewclients.clientcredit.index", ["userid" => $clientsdetails["userid"] ]) }}">
                                            <button class="btn btn-light btn-rounded btn-sm align-items-center d-flex">
                                                <i class="fa fa-arrow-left mr-2"></i> Back
                                            </button>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-10">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Credit Management</h4>
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
                                <div class="col-sm-6 col-lg-8">
                                    <label for="">Client: {{ $clientsdetails["fullname"] }} (Balance: {{ $creditbalance }})</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3 min-vh-100 bg-white">
                                        <div class="row">
                                            <label class="col-sm-6 col-form-label">Edit Credit</label>
                                        </div>
                                        <form method="POST" action="{{ route("admin.pages.clients.viewclients.clientcredit.update") }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="off">
                                            @csrf
                                            <input type="number" name="id" value="{{ $credit->id }}" hidden>
                                            <input type="number" name="userid" value="{{ $clientsdetails["userid"] }}" hidden>
                                            <input type="text" name="type" value="edit" hidden>
                                            <div class="rounded border p-3 mb-3">
                                                <div class="row flex-wrap">
                                                    <div class="col-sm-12 col-lg-12">
                                                        <div class="form-group row">
                                                            <label for="date" class="col-sm-3 col-form-label my-1">Date</label>
                                                            <div class="col-sm-9">
                                                                <div class="input-date input-group">
                                                                    <input type="text" class="form-control @error('date') is-invalid @enderror" name="date" id="date" value="{{ old('date') ?? $credit->date }}" placeholder="Date (dd/mm/yyyy)" required autocomplete="off"/>
                                                                </div>
                                                                @error('date')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="description" class="col-sm-3 col-form-label">Description</label>
                                                            <div class="col-sm-9">
                                                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="description" cols="75" rows="4" placeholder="Description" autocomplete="off" required>{{ old("description") ?? $credit->description }}</textarea>
                                                                {{-- <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" id="description" value="{{ old("description") }}" placeholder="Description" autocomplete="off"> --}}
                                                                @error('description')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="amount" class="col-sm-3 col-form-label">Amount</label>
                                                            <div class="col-sm-9">
                                                                <input type="number" min="0" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" id="amount" value="{{ $credit->amount ?? "0.00" }}" placeholder="Amount" readonly disabled autocomplete="off">
                                                                <div class="text-warning" >{{ __("admin.clientsummarycannotEditAmount") }}</div>
                                                                <div class="text-warning" >({{ __("admin.clientsummaryuseButtonsToAffectAmount") }})</div>
                                                                @error('amount')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12 d-flex justify-content-center">
                                                    <button type="submit" class="btn btn-success">Save Changes</button>
                                                    {{-- <button type="reset" class="btn btn-light">Reset Changes</button> --}}
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts') 
    <!-- JQuery Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Bootstrap default validation -->
    <script src="{{ Theme::asset('assets/js/pages/form-validation.init.js') }}"></script>

    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>
    
    <script>
        $(() => {
            $('#date').datepicker(dateOption);
        });
    </script>
@endsection
