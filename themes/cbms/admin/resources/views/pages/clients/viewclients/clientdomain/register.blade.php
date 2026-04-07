@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Domain</title>
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

                            @if (isset($invalidClientId))
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">No Data Found!</h4>
                                                <hr>
                                                <p class="mb-0">Invalid client ID. Please access this page with correct ID</p>
                                                {{-- <p>No domains found for this user. <a href="{{ url('admin/orders/add') }}">Click here</a> to place a new order..</p> --}}
                                                {{-- <p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if (isset($invalidDomainId))
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">No Data Found!</h4>
                                                <hr>
                                                <p>No domains found for this user. <a href="{{ route('admin.pages.orders.addneworder.index', ['userid' => $userid]) }}">Click here</a> to place a new order..</p>
                                                <p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if (isset($clientsdetails))
                            
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3 border">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card-title mb-3 mt-3">
                                                    <h4 class="">Register/Transfer Domain</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <form action="{{ route('admin.pages.clients.viewclients.clientdomain.saveRegister') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                                            @csrf
                                            <input type="number" name="userid" value="{{ $userid }}" hidden>
                                            <input type="number" name="domainid" value="{{ $id }}" hidden>
                                            <input type="text" name="action" value="{{ $action }}" hidden>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Registrar</label>
                                                        <div class="col-sm-9">
                                                            <label for="" class="col-form-label">{{ $registrar }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Requested Action</label>
                                                        <div class="col-sm-9">
                                                            <label for="" class="col-form-label">{{ ucfirst($action) }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Domain</label>
                                                        <div class="col-sm-9">
                                                            <label for="" class="col-form-label">{{ $domain }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Registration Period</label>
                                                        <div class="col-sm-9">
                                                            <label for="" class="col-form-label">{{ $registrationperiod }} {{ __("admin.domainsyears") }}</label>
                                                        </div>
                                                    </div>

                                                    @for ($i = 1; $i <= 5; $i++) 
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Nameserver {{ $i }}</label>
                                                        <div class="col-sm-4">
                                                            <input type="text" name="ns{{$i}}" id="ns{{$i}}" class="form-control" value="{{ $nsvals[$i] ?? "" }}" placeholder="Nameserver {{ $i }}" autocomplete="off">
                                                        </div>
                                                        @if ($i == 1)
                                                        <div class="col-sm-5">
                                                            <label for="#" class="col-form-label ">{{ $autonsdesc }}</label>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    @endfor

                                                    @if ($action == "transfer") 
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">EPP Code</label>
                                                        <div class="col-sm-4">
                                                            <input type="text" name="transfersecret" id="transfersecret" class="form-control" value="{{ $transfersecret }}" placeholder="EPP Code" autocomplete="off">
                                                        </div>
                                                        <div class="col-sm-5">
                                                            <label for="#" class="col-form-label ">{{ __("admin.domainsifreq") }}</label>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label ">Send Confirmation Email</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="sendregisterconfirm" class="form-check-input" id="sendregisterconfirm" value="1">
                                                                <label class="form-check-label" for="sendregisterconfirm">Tick this box to Send Registration Confirmation Email on Successful Completion</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            @php $replace = $action == "register" ? __("admin.domainsactionreg") : __("admin.domainstransfer"); @endphp
                                                            <label for="">{{ str_replace("%s", $replace, __("admin.domainsactionquestion")) }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <button type="submit" class="btn btn-success px-3 mr-2">Yes</button>
                                                            <button type="button" class="btn btn-secondary" onClick="window.location='{{ route('admin.pages.clients.viewclients.clientdomain.index', ['userid' => $userid, 'domainid' => $id]) }}'">No</button>
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

    @stack('clientsearch')
    
    <script>
        // Daterange options
        let dateRangeOption = {
            format: 'dd/mm/yyyy',
            autoclose: true,
            orientation: 'bottom',
            todayBtn: 'linked',
            todayHighlight: true,
            clearBtn: true,
            disableTouchKeyboard: true,
        };

        // Swal toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            timerProgressBar: true,
            timer: 6000,
        });

        $(() => {
           
        });

    </script>
@endsection
