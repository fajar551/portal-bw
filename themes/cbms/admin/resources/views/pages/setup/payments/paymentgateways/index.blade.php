@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Payment Gateways</title>
@endsection
@push('styles')
    <link href="{{ Theme::asset('css/app.css') }}" type="text/css" rel="stylesheet" />
@endpush
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                @php
                    // $i = 0;
                    // $currenciesarray[$i] = $result->toArray();
                    $currenciesarray = $result->toArray();
                @endphp
                {{-- @if ($currenciesarray[$i])
                    @php
                        $i++;
                    @endphp
                @else
                    @php
                        array_pop($currenciesarray);
                    @endphp
                @endif --}}
                
                <div class="row">
                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Payment Gateways</h4>
                                    </div>
                                    
                                    {{-- featured --}}
                                    {{-- <div class="alert alert-primary" role="alert">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Pembayaran Ototmatis</strong><br/>
                                                Aktifkan pembayaran otomatis Anda sekarang juga!
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <a href="{{route('admin.submission.payment.index')}}" class="btn btn-primary">Aktifkan Sekarang</a>
                                            </div>
                                        </div>
                                    </div> --}}

                                    @if ($message = Session::get('success'))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert"
                                                id="success-alert">
                                                <h5>Successfully Updated!</h5>
                                                <small>{{ $message }}</small>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                    @endif
                                    @if ($message = Session::get('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                            id="danger-alert">
                                            <h5>Error:</h5>
                                            <small>{{ $message }}</small>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    <nav>
                                        <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                                            {{-- <li class="nav-item d-none" role="presentation">
                                                <a class="nav-link" id="nav-featured-gateways-tab" data-toggle="tab"
                                                    href="#nav-featured-gateways" role="tab"
                                                    aria-controls="nav-featured-gateways" aria-selected="true">Featured
                                                    Payment Gateways</a>
                                            </li> --}}
                                            <li class="nav-item">
                                                <a class="nav-link {{Request::get('activated') || Request::get('updated') || Request::get('error') || Request::get('deactivated') || Request::get('sortchange') ? '' : 'active'}}" id="nav-all-gateways-tab" data-toggle="tab"
                                                    href="#nav-all-gateways" role="tab" aria-controls="nav-all-gateways"
                                                    aria-selected="false">All Payment Gateways</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link {{Request::get('activated') || Request::get('updated') || Request::get('error') || Request::get('deactivated') || Request::get('sortchange') ? 'active' : ''}}" id="nav-manage-gateways-tab" data-toggle="tab"
                                                    href="#nav-manage-gateways" role="tab"
                                                    aria-controls="nav-manage-gateways" aria-selected="false">Manage Payment
                                                    Gateways</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="tab-content" id="nav-tabContent">
                                            <div class="tab-pane fade {{Request::get('activated') || Request::get('updated') || Request::get('error') || Request::get('deactivated') || Request::get('sortchange') ? '' : 'show active'}}" id="nav-all-gateways" role="tabpanel"
                                                aria-labelledby="nav-all-gateways-tab">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <p>Click on a payment gateway below to activate and begin using it.
                                                            Already active payment gateways will appear in green.</p>
                                                    </div>
                                                </div>
                                                <div class="container">
                                                    <div class="row">
                                                        @foreach ($AllGateways as $modulename)
                                                            @php
                                                                $displayName = $GatewayConfig[$modulename]["FriendlyName"]["Value"];
                                                                $isActive = in_array($modulename, $ActiveGateways);
                                                                $btnDisabled = $isActive ? " disabled" : "";
                                                            @endphp
                                                            <div class="col-lg-3">
                                                                <form action="{{route('admin.pages.setup.payments.paymentgateways.activate')}}" method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="gateway" value="{{$modulename}}">
                                                                    <button
                                                                        class="btn btn-{{$isActive ? 'success' : 'light'}} btn-sm py-2 mb-2 btn-block" {{$btnDisabled}}>{{$displayName}}</button>
                                                                    </form>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12 text-center">
                                                        <p class="p-3 m-0">Can't find the payment gateway you're looking
                                                            for? Take a look at
                                                            our <a href="#">Marketplace</a> for gateways with third party
                                                            modules.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade {{Request::get('activated') || Request::get('updated') || Request::get('error') || Request::get('deactivated') || Request::get('sortchange') ? 'show active' : ''}}" id="nav-manage-gateways" role="tabpanel"
                                                aria-labelledby="nav-manage-gateways-tab">
                                               <div class="row">
                                                    @php
                                                        $count = 1;
                                                        $newgateways = "";
                                                    @endphp
                                                    @foreach ($result3 as $data)
                                                        @php
                                                            $module = $data["gateway"];
                                                            $order = $data["order"];
                                                            $isModuleDisabled = false;
                                                            if (isset($GatewayConfig[$module])) {
                                                                $modName = $GatewayConfig[$module]["FriendlyName"]["Value"];
                                                            } else {
                                                                $modName = $module;
                                                                $isModuleDisabled = true;
                                                            }
                                                        @endphp
                                                        <div class="col-lg-12 mb-5 mt-2">
                                                            <a name="{{$module}}"></a>
                                                            <h5>
                                                                {{$loop->iteration}}. {{$modName}}
                                                                @php
                                                                    // $friedlyname = isset($GatewayConfig[$module]) ? $GatewayConfig[$module]["FriendlyName"]["Value"] : "";
                                                                    $friedlyname = $modName;
                                                                @endphp
                                                                @if ($numgateways != "1")
                                                                    <a href="#" onclick="deactivateGW('{{$module}}', '{{$friedlyname}}');return false;" class="text-danger">(Deactivate)</a>
                                                                @endif
                                                                @if (!$loop->first)
                                                                    <a href="{{route('admin.pages.setup.payments.paymentgateways.moveup', ['order' => $order])}}" class="btn btn-sm btn-success rounded-lg">up</a>
                                                                @endif
                                                                @if (!$loop->last)
                                                                    <a href="{{route('admin.pages.setup.payments.paymentgateways.movedown', ['order' => $order])}}" class="btn btn-sm btn-success rounded-lg">down</a>
                                                                @endif
                                                            </h5>
                                                            @if (Request::get('activated') == $module)
                                                                <div class="alert alert-warning mt-3" role="alert">
                                                                    <h4 class="alert-heading">Success</h4>
                                                                    <p>{{\Lang::get('admin.gatewaysactivatesuccess')}}</p>
                                                                </div>
                                                            @endif
                                                            @if (Request::get('error') == $module)
                                                                <div class="alert alert-danger" role="alert">
                                                                    <h4 class="alert-heading">Error Configuration</h4>
                                                                    <p>{{\Lang::get('admin.gatewayschangesUnsaved')}}</p>
                                                                </div>
                                                            @endif
                                                            @if (Request::get('updated') == $module)
                                                                <div class="alert alert-warning" role="alert">
                                                                    <h4 class="alert-heading">Success</h4>
                                                                    <p>{{\Lang::get('admin.gatewayssavesuccess')}}</p>
                                                                </div>
                                                            @endif
                                                            @if ($isModuleDisabled === true)
                                                                <div class="alert alert-warning" role="alert">
                                                                    {{\Lang::get('admin.gatewaysmoduleunavailable')}}
                                                                </div>
                                                            @else
                                                                <form action="{{route('admin.pages.setup.payments.paymentgateways.save')}}" method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="module" value="{{$module}}">
                                                                    <div class="border rounded px-3 pb-0">
                                                                        <div class="form-group row mb-0">
                                                                            <label class="col-sm-12 col-lg-2 col-form-label">Show on
                                                                                Order Form</label>
                                                                            <div class="col-sm-12 col-lg-10 pt-2 bg-light pb-3">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox" class="custom-control-input"
                                                                                        id="customCheck{{$loop->iteration}}" name="field[visible]" {{isset($GatewayValues[$module]) && $GatewayValues[$module]["visible"] ? 'checked' : ''}}>
                                                                                    <label class="custom-control-label"
                                                                                        for="customCheck{{$loop->iteration}}"></label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row mb-0">
                                                                            <label class="col-sm-12 col-lg-2 col-form-label">Display
                                                                                Name
                                                                            </label>
                                                                            <div class="col-sm-12 col-lg-10 bg-light pb-3">
                                                                                <input name="field[name]" value="{{isset($GatewayValues[$module]) ? htmlspecialchars($GatewayValues[$module]["name"]) : ''}}" type="text" class="form-control input-inline input-300"
                                                                                    placeholder="Mail in Payment">
                                                                            </div>
                                                                        </div>
                                                                        @foreach ($GatewayConfig[$module] as $confname => $values)
                                                                            @if ($values["Type"] != "System")
                                                                                @php
                                                                                    $values["Name"] = "field[" . $confname . "]";
                                                                                    $values["Value"] = null;
                                                                                    if (isset($GatewayValues[$module][$confname])) {
                                                                                        $values["Value"] = $GatewayValues[$module][$confname];
                                                                                    }
                                                                                    if (isset($passedParams[$module][$confname])) {
                                                                                        $values["Value"] = $passedParams[$module][$confname];
                                                                                    }
                                                                                @endphp
                                                                                <div class="form-group row mb-0">
                                                                                    <label class="col-sm-12 col-lg-2 col-form-label">{{$values["FriendlyName"]}}</label>
                                                                                    <div class="col-sm-12 col-lg-10 text-left bg-light pb-3">
                                                                                        {!!\App\Helpers\Module::moduleConfigFieldOutput($values)!!}
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                        @if (1 < count($currenciesarray) && (isset($noConversion[$module]) && !$noConversion[$module]))
                                                                            <div class="form-group row mb-0">
                                                                                <label class="col-sm-12 col-lg-2 col-form-label">Convert To For Processing</label>
                                                                                <div class="col-sm-12 col-lg-10 text-left bg-light pb-3">
                                                                                    <select name="field[convertto]" class="form-control select-inline">
                                                                                        <option value="">None</option>
                                                                                        @foreach ($currenciesarray as $currencydata)
                                                                                            <option value="{{$currencydata["id"]}}" {{isset($GatewayValues[$module]["convertto"]) && $currencydata["id"] == $GatewayValues[$module]["convertto"] ? 'selected' : ''}}>{{$currencydata["code"]}}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                        @if (array_key_exists("UsageNotes", $GatewayConfig[$module]) && $GatewayConfig[$module]["UsageNotes"]["Value"])
                                                                            <div class="form-group row mb-0">
                                                                                <label class="col-sm-12 col-lg-2 col-form-label"></label>
                                                                                <div class="col-sm-12 col-lg-10 text-left bg-light pb-3">
                                                                                    <div class="alert alert-info" role="alert">
                                                                                        {!!$GatewayConfig[$module]["UsageNotes"]["Value"]!!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                        <div class="form-group row mb-0">
                                                                            <label for=""
                                                                                class="col-sm-12 col-lg-2 col-form-label"></label>
                                                                            <div class="col-sm-12 col-lg-10 bg-light pb-3">
                                                                                <button class="btn btn-success">Save
                                                                                    Changes</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            @endif
                                                        </div>
                                                        @php
                                                            $newgateways .= "<option value=\"" . $module . "\">" . $GatewayConfig[$module]["FriendlyName"]["Value"] . "</option>";
                                                        @endphp
                                                    @endforeach
                                                    @if (count($ActiveGateways) < 1)
                                                        <div class="col-md-12 text-center">
                                                            <div class="alert alert-danger" role="alert">
                                                                <strong>{{\Lang::get('admin.gatewaysnoGatewaysActive')}}</strong>
                                                                {{\Lang::get('admin.gatewaysactivateGatewayFirst')}}
                                                            </div>
                                                        </div>
                                                    @endif
                                               </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End MAIN CARD -->
            </div>
        </div>
    </div>
    </div>
    <div class="modal fade" id="modalDeactivateGateway" tabindex="-1" role="dialog" aria-labelledby="modalDeactivateGatewayLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="modalDeactivateGatewayLabel">Deactivate Module</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{route('admin.pages.setup.payments.paymentgateways.deactivate')}}" id="frmDeactivateGateway">
                        @csrf
                        <input type="hidden" name="gateway" value="" id="inputDeactivateGatewayName">
                        <input type="hidden" name="friendlygateway" value="" id="inputFriendlyGatewayName">
                        To deactivate this gateway module, you must first choose an alternative for any products & invoices currently assigned to it to be switched to.
                        <div class="text-center">
                            <select name="newgateway" class="form-control select-inline" id="inputNewGateway">
                                {{$newgateways}}
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="$('#frmDeactivateGateway').submit()">Deactivate</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function deactivateGW(module,friendlyname) {
            var gatewayOptions = "{!!addslashes($newgateways)!!}";
            $("#inputDeactivateGatewayName").val(module);
            $("#inputFriendlyGatewayName").val(friendlyname);
            $("#inputNewGateway").html(gatewayOptions);
            $("#inputNewGateway option[value="+module+"]").remove();
            $("#modalDeactivateGateway").modal('show');
        }
    </script>
@endsection
