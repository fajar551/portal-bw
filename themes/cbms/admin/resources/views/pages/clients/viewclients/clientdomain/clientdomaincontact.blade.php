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
                                                    <h4 class="">Modify Domain Contact Details</h4>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if ($regError)
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="alert alert-warning">
                                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                                    <strong>{!! $regError !!}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <form action="{{ route('admin.pages.clients.viewclients.clientdomain.savedomaincontact') }}" method="POST" id="frmDomainContactModification" enctype="multipart/form-data" autocomplete="off">
                                            @csrf
                                            <input type="number" name="userid" value="{{ $userid }}" hidden>
                                            <input type="number" name="domainid" value="{{ $id }}" hidden>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group row mt-2">
                                                        <label for="#" class="col-sm-2 col-form-label text-right">Registrar: </label>
                                                        <div class="col-sm-10 bg-light">
                                                            <label for="" class="col-form-label">{{ $registrar }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-2 col-form-label text-right">Domain:</label>
                                                        <div class="col-sm-10 bg-light">
                                                            <label for="" class="col-form-label">{{ $domain }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @foreach ($contactdetails as $contactdetail => $values)
                                            <div class="form-group row">
                                                {{-- <label for="#" class="col-sm-3 col-form-label text-right">Reg Result:</label> --}}
                                                <div class="col-sm-12 bg-light">
                                                    <label for="" class="col-form-label">{{ $contactdetail }}</label>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <div class="form-check mt-3">
                                                        <input class="form-check-input" type="radio" name="wc[{{$contactdetail}}]" id="{{$contactdetail}}1" value="contact" onclick="usedefaultwhois(id)">
                                                        <label class="form-check-label" for="{{$contactdetail}}1">Use Existing Contact</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-9">
                                                    <div class="row bg-light py-2">
                                                        <label for="#" class="col-sm-2 col-form-label">Choose Contact </label>
                                                        <div class="col-sm-10 ">
                                                            <select name="sel[{{$contactdetail}}]" id="{{$contactdetail}}3" class="{{$contactdetail}}defaultwhois form-control" onclick="usedefaultwhois(id)">
                                                                <option value="u{{$userid}}">Account Owner's Details</option>
                                                                @foreach ($contactsarray as $subcontactsarray)
                                                                <option value="c{{$subcontactsarray["id"]}}">{{$subcontactsarray["name"]}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <div class="form-check mt-3">
                                                        <input class="form-check-input" type="radio" name="wc[{{$contactdetail}}]" id="{{$contactdetail}}2" value="custom" onclick="usecustomwhois(id)" checked="checked">
                                                        <label class="form-check-label" for="{{$contactdetail}}2">Use Following Details</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-9" id="{{$contactdetail}}defaultwhoiscontainer">
                                                    @foreach ($values as $name => $value)
                                                    <div class="row bg-light py-2">
                                                        <label for="#" class="col-sm-2 col-form-label mt-2">{{ $name }}</label>
                                                        <div class="col-sm-10 ">
                                                            @php
                                                                $textFieldInput = true;
                                                                if ($name == "Country") {
                                                                    if (!$value) {
                                                                        $value = \App\Helpers\cfg::get("DefaultCountry");
                                                                        $countries = $country->getCountryNameArray();
                                                                        $textFieldInput = false;
                                                                    } else if ($country->isValidCountryCode($value)) {
                                                                        $countries = $country->getCountryNameArray();
                                                                        $textFieldInput = false;
                                                                    } else if ($country->isValidCountryName($value)) {
                                                                        $countries = $country->getCountryNamesOnly();
                                                                        $textFieldInput = false;
                                                                    } else {
                                                                        $textFieldInput = true;
                                                                    }

                                                                    if (!$textFieldInput) {
                                                                        echo "<select name=\"contactdetails[" . $contactdetail . "][" . $name . "]\" class=\"" . $contactdetail . "customwhois form-control mt-2\">";
                                                                        foreach ($countries as $k => $v) {
                                                                            echo "<option value=\"" . $k . "\"" . ($k == $value ? " selected" : "") . ">" . $v . "</option>";
                                                                        }
                                                                        echo "</select>";
                                                                    }
                                                                }

                                                                if ($textFieldInput) {
                                                                    $additionalData = "";
                                                                    $classes = array($contactdetail . "customwhois", "form-control", "input-300", "mt-2");
                                                                    if (array_key_exists($contactdetail, $irtpFields) && in_array($name, $irtpFields[$contactdetail])) {
                                                                        $additionalData = "data-original-value=\"" . $value . "\"";
                                                                        $classes[] = "irtp-field";
                                                                    }

                                                                    $type = "type=\"text\"";
                                                                    $fieldName = "name=\"contactdetails[" . $contactdetail . "][" . $name . "]\"";
                                                                    $value = "value=\"" . \App\Helpers\Sanitize::encode($value) . "\"";
                                                                    $class = "class=\"" . implode(" ", $classes) . "\"";

                                                                    echo "<input " . $type . " " . $fieldName . " " . $value . " " . $class . " " . $additionalData . ">";
                                                                }
                                                            @endphp 
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endforeach

                                            @if ($domainInformation && $domainInformation->isIrtpEnabled()) 
                                                <div class="form-group row">
                                                    {{-- <label for="#" class="col-sm-3 col-form-label text-right">irtpOptOut</label> --}}
                                                    <div class="col-sm-12 bg-light">
                                                        <input id="irtpOptOut" type="hidden" name="irtpOptOut" value="0">
                                                        <input id="irtpOptOutReason" type="hidden" name="irtpOptOutReason" value="">
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <button type="submit" class="btn btn-success px-3 mr-2">Save Changes</button>
                                                            <button type="button" class="btn btn-secondary" onClick="window.location='{{ route('admin.pages.clients.viewclients.clientdomain.index', ['userid' => $userid, 'domainid' => $id]) }}'">Go Back</button>
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

    @if ($domainInformation && $domainInformation->isIrtpEnabled()) 
    <!--  Modal IRTP -->
    <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modalIRTPConfirmation" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="myLargeModalLabel">{{ __("admin.domainsimportantReminder") }} <img class="ml-2" src="{{ Theme::asset('img/loading.gif') }}" id="prev-loader" alt="loading" hidden><br>
                        <p id="text-loading">{{ __("admin.domainsirtpNotice") }} </p>  
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" >
                    <div class="card p-3">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label for="#" class="col-sm-3 col-form-label "></label>
                                    <div class="col-sm-9">
                                        <div class="form-check">
                                            <input type="checkbox" name="modalIrtpOptOut" class="form-check-input " id="modalIrtpOptOut" value="1">
                                            <label class="form-check-label" for="modalIrtpOptOut">{{  __("admin.domainsoptOut") }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="#" class="col-sm-3 col-form-label ">{{ __("admin.domainsoptOutReason") }}</label>
                                    <div class="col-sm-9 py-2">
                                        <input type="text" name="modalReason" id="modalReason" class="form-control" value="" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary waves-effect" onclick="irtpSubmit();return false;">Submit</button>
                    <button type="button" class="btn btn-light waves-effect" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    @stack('clientsearch')
    
    <script>
        // Swal toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            timerProgressBar: true,
            timer: 6000,
        });

        let allowSubmit = 0;

        $(() => {
            $('#frmDomainContactModification').on('submit', function(){
                if (!allowSubmit) {
                    let changed = false;
                    $('.irtp-field').each(function() {
                        let value = $(this).val();
                        let originalValue = $(this).attr('data-original-value');

                        if (value !== originalValue) {
                            changed = true;
                        }
                    });

                    if (changed) {
                        $('#modalIRTPConfirmation').modal({ show:true, backdrop: "static"});
                        return false;
                    }
                }

                return true;
            });
        });

        const usedefaultwhois = (id) => {
            $("." + id.substr(0, id.length - 1) + "customwhois").attr("disabled", true);
            $("." + id.substr(0, id.length - 1) + "defaultwhois").attr("disabled", false);
            $('#' + id.substr(0, id.length - 1) + '1').attr({checked: true});
        }

        const usecustomwhois = (id) => {
            $("." + id.substr(0, id.length - 1) + "customwhois").attr("disabled", false);
            $("." + id.substr(0, id.length - 1) + "defaultwhois").attr("disabled", true);
            $('#' + id.substr(0, id.length - 1) + '2').attr({checked: true});
        }

        const irtpSubmit = () => {
            allowSubmit = true;

            let optOut = 0;
            let optOutCheckbox = $('#modalIrtpOptOut');
            let optOutReason = $('#modalReason');
            let formOptOut = $('#irtpOptOut');
            let formOptOutReason = $('#irtpOptOutReason');
            
            if (optOutCheckbox.is(':checked')) {
                optOut = 1;
            }

            formOptOut.val(optOut);
            formOptOutReason.val(optOutReason.val());
            $('#frmDomainContactModification').submit();
        }
    </script>
@endsection
