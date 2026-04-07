@extends('layouts.base-without-sidebar')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Move</title>
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
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Transfer Ownership</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-lg-12">
                                    <label for="">This tool allows you to move this product/service record to another client</label>
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
                                <div class="col-lg-12">
                                    <div class="card p-3 min-vh-100 bg-white">
                                        <div class="row">
                                            <label class="col-sm-6 col-form-label">Type: {{ ucfirst($type) }}</label>
                                        </div>
                                        <form method="POST" action="{{ route("admin.pages.clients.viewclients.clientmove.transfer") }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="off">
                                            @csrf
                                            <input type="number" name="id" value="{{ $id }}" hidden>
                                            <input type="text" name="type" value="{{ $type }}" hidden>
                                            <div class="rounded border p-3 mb-3">
                                                <div class="row flex-wrap">
                                                    <div class="col-sm-12 col-lg-12">
                                                        @if (isset($domainData))
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-12 col-form-label">Domain:&nbsp;&nbsp; {{ $domainData["domain"] }}</label>
                                                        </div>
                                                        @endif
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-4 col-form-label">Client ID:</label>
                                                            <div class="col-sm-12 col-lg-8">
                                                                <input type="number" name="newuserid" id="newuserid" min="0" step="1" class="form-control @error('newuserid') is-invalid @enderror" value="{{ old('newuserid') }}" placeholder="Enter new client ID" autocomplete="off" required>
                                                                @error('newuserid')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-4 col-form-label">Enter Name, Company or Email to Search Client ID:</label>
                                                            <div class="col-sm-12 col-lg-8 pt-2">
                                                                <select name="search_client" id="search_client" class="form-control select2-limiting" style="width: 100%">
                                                                    
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12 d-flex justify-content-center">
                                                    <button type="submit" class="btn btn-success">Transfer</button> &nbsp;
                                                    <button type="reset" class="btn btn-light" onclick="window.close();">Close</button>
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

    <script>
        // Daterange options
        let dateOption = {
            format: 'dd/mm/yyyy',
            autoclose: true,
            orientation: 'bottom',
            todayBtn: 'linked',
            todayHighlight: true,
            clearBtn: true,
            disableTouchKeyboard: true,
        };

        $(() => {

            @if ($submited)
                @if ($type == "domain")
                    window.opener.location.href = "{!! route('admin.pages.clients.viewclients.clientdomain.index', ['userid' => $newuserid, 'domainid' => $id]) !!}";
                @elseif ($type == "hosting")
                    window.opener.location.href = "{!! route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $newuserid, 'hostingid' => $id]) !!}";
                @endif

                window.close();
            @endif

            // Search Client on merge client modal
            $("#search_client").select2({
                // theme: "classic"
                placeholder: 'Search Client',
                allowClear: true,
                width: 'resolve',
                closeOnSelect: false,
                templateResult: formatState,
                ajax: {
                    url: '{{ route("admin.pages.clients.viewclients.clientsummary.searchClient") }}',
                    type: 'GET',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    delay: 1000, // Wait 1 seconds before triggering the request
                    data: function (params) {
                        // You can add more params here
                        let query = {
                            search: params.term,
                        }

                        return query;
                    }
                },
                cache: true,
                minimumInputLength: 3,
            });

            $('#search_client').on('select2:select', function (e) {
                let data = e.params.data;

                $('#newuserid').val(data.id);
            });
        });

        const formatState = (state) => {
            if (!state.id) return state.text;
            
            // console.log(state);

            let result = $(
                `<strong>${state.data.firstname} ${state.data.lastname} ${state.data.companyname}</strong> #${state.data.id}<br /><span>${state.data.email}</span>`
            );

            return result;
        };
    </script>
@endsection
