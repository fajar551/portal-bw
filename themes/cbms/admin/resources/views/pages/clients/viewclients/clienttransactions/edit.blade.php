@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Transaction</title>
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
                        <div class="view-client-wrapper">
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

                            @include('includes.tabnavclient')
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3 min-vh-100 bg-white">
                                        <div class="row">
                                            <label class="col-sm-6 col-form-label">Edit Transaction</label>
                                        </div>
                                        <form method="POST" action="{{ route("admin.pages.clients.viewclients.clienttransactions.update") }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                                            @csrf
                                            <input type="number" name="userid" value="{{ $clientsdetails["userid"] }}" hidden>
                                            <input type="number" name="id" value="{{ $transaction->id }}" hidden>
                                            <div class="rounded border p-3 mb-3">
                                                <div class="row flex-wrap">
                                                    <div class="col-lg-6">
                                                        <div class="form-group row">
                                                            <label for="date" class="col-sm-3 col-form-label my-1">Date</label>
                                                            <div class="col-sm-9">
                                                                <div class="input-date input-group">
                                                                    <input type="text" class="form-control @error('date') is-invalid @enderror" name="date" id="date" value="{{ old('date') ?? $transaction->date }}" placeholder="Date (dd/mm/yyyy)" required autocomplete="off"/>
                                                                </div>
                                                                @error('date')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="description" class="col-sm-3 col-form-label">Description</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" id="description" value="{{ old("description") ?? $transaction->description }}" placeholder="Description" autocomplete="off">
                                                                @error('description')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="transid" class="col-sm-3 col-form-label">Transaction ID</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="transid" class="form-control @error('transid') is-invalid @enderror" id="transid" value="{{ old("transid") ?? $transaction->transid }}" placeholder="Transaction ID" autocomplete="off">
                                                                @error('transid')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="invoiceid" class="col-sm-3 col-form-label">Invoice ID</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="invoiceid" class="form-control @error('invoiceid') is-invalid @enderror" id="invoiceid" value="{{ old("invoiceid") ?? $transaction->invoiceid }}" placeholder="Invoice ID" autocomplete="off">
                                                                @error('invoiceid')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="paymentmethod" class="col-sm-3 col-form-label my-1">Payment Method</label>
                                                            <div class="col-sm-9">
                                                                <select class="select2-search-disable form-control @error('paymentmethod') is-invalid @enderror" name="paymentmethod" id="paymentmethod" style="width: 100%;">
                                                                    <option value="">None</option>
                                                                    @foreach ($paymentmethodlist as $paymentmethod)
                                                                        <option value="{{ $paymentmethod["gateway"] }}" 
                                                                        @if(old('paymentmethod')) 
                                                                            @if($paymentmethod["gateway"] == old('paymentmethod')) selected @endif
                                                                        @else 
                                                                            @if($paymentmethod["gateway"] == $transaction->gateway) selected @endif
                                                                        @endif>
                                                                            {{ $paymentmethod["value"] }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('paymentmethod')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group row">
                                                            <label for="amountin" class="col-sm-3 col-form-label">Amount In</label>
                                                            <div class="col-sm-9">
                                                                <input type="number" min="0" step="0.01" name="amountin" class="form-control @error('amountin') is-invalid @enderror" id="amountin" value="{{ old("amountin") ?? $transaction->amountin }}" placeholder="Amount IN" autocomplete="off">
                                                                @error('amountin')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="fees" class="col-sm-3 col-form-label">Fees</label>
                                                            <div class="col-sm-9">
                                                                <input type="number" min="0" step="0.01" name="fees" class="form-control @error('fees') is-invalid @enderror" id="fees" value="{{ old("fees") ?? $transaction->fees }}" placeholder="Fees" autocomplete="off">
                                                                @error('fees')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="amountout" class="col-sm-3 col-form-label">Amount Out</label>
                                                            <div class="col-sm-9">
                                                                <input type="number" min="0" step="0.01" name="amountout" class="form-control @error('amountout') is-invalid @enderror" id="amountout" value="{{ old("amountout") ?? $transaction->amountout }}" placeholder="Amount OUT" autocomplete="off">
                                                                @error('amountout')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12 d-flex justify-content-center">
                                                    <button type="submit" class="btn btn-success px-3 mr-2">Save Changes</button>
                                                    <button type="reset" class="btn btn-light">Reset Changes</button>
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

    @stack('clientsearch')
    
    <script>
        $(() => {
            $('#date').datepicker(dateOption);
        });
    </script>
@endsection
