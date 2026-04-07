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
                                                <h4>Edit Addon</h4>
                                            </label>
                                        </div>
                                        <form action="{{ route('admin.pages.clients.viewclients.clientservices.updateAddons') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                                            @csrf
                                            <input type="number" name="userid" value="{{ $userid }}" hidden>
                                            <input type="number" name="id" value="{{ $id }}" hidden>
                                            <input type="number" name="aid" value="{{ $aid }}" hidden>
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
                                                                            <option value="{{ $key }}" @if ($key == old('addonid', $addonid)) selected @endif >{{ $addon }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Custome Name</label>
                                                                <div class="col-sm-9">
                                                                    <input type="text" class="form-control" name="name" placeholder="Custome Name" value="{{ old('name', $customname) }}" />
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
                                                                            <option value="{{ $paymentmethod["gateway"] }}" @if(old('paymentmethod', $paymentmethod["gateway"]) == $gateway) selected @endif>
                                                                                {{ $paymentmethod["value"] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            @if ($serversArray)
                                                            <div class="form-group row">
                                                                <label for="paymentmethod" class="col-sm-3 col-form-label">Server</label>
                                                                <div class="col-sm-9">
                                                                    <select class="select2-search-disable form-control" name="server" id="server" style="width: 100%;">
                                                                        <option value="">None</option>
                                                                        @foreach ($serversArray as $k => $v)
                                                                            <option value="{{ $k }}" @if ($server == $k) selected @endif >{{ $v }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            @endif
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Subscription ID</label>
                                                                <div class="col-sm-9">
                                                                    <input type="number" min="0" name="subscriptionid" value="{{ old('subscriptionid', $subscriptionid) }}" class="form-control " placeholder="Subscription ID">
                                                                    {!! $cancelSubscription !!}
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Admin Notes</label>
                                                                <div class="col-sm-9">
                                                                    <textarea name="notes" class="form-control" rows="4" placeholder="Add admin notes">{{ old('notes', $notes) }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Setup Fee</label>
                                                                <div class="col-sm-9">
                                                                    <input type="number" min="0" step="0.01" name="setupfee" class="form-control " placeholder="Setup Fee" value="{{ old('setupfee', $setupfee) }}" >
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Recurring</label>
                                                                <div class="col-sm-9">
                                                                    <input type="number" min="0" step="0.01" name="recurring" class="form-control " placeholder="Recurring" value="{{ old('recurring', $recurring) }}" >
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
                                                                            <input type="text" class="form-control" name="nextduedate" placeholder="dd/mm/yyyy" value="{{ old('nextduedate', strpos($nextduedate, "0000") === false ? $nextduedate : "") }}" />
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="inputTerminationDate" class="col-sm-3 col-form-label ">Termination Date</label>
                                                                <div class="col-sm-9">
                                                                    <div class="input-daterange input-group " id="inputTerminationDate">
                                                                        <input type="text" class="form-control" name="termination_date" placeholder="dd/mm/yyyy" value="{{ old('termination_date', strpos($terminationDate, "0000") === false ? $terminationDate : "") }}" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="#" class="col-sm-3 col-form-label ">Tax Addon</label>
                                                                <div class="col-sm-9">
                                                                    <div class="custom-control custom-checkbox mt-2">
                                                                        <input type="checkbox" name="tax" class="custom-control-input" id="tax" value="1" @if (old('tax', $tax)) checked @endif>
                                                                        <label class="custom-control-label" for="tax"></label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <div class="custom-control custom-checkbox mt-2">
                                                                <input type="checkbox" name="geninvoice" class="custom-control-input" id="geninvoice" value="1" @if (old('geninvoice')) checked @endif>
                                                                <label class="custom-control-label" for="geninvoice"> Generate Invoice after Adding</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> --}}
                                                <div class="col-lg-12">
                                                    @if ($moduleInterface) 
                                                        <div class="form-group row">
                                                            <label for="#" class="col-sm-2 col-form-label ">Module Commands</label>
                                                            <div class="col-sm-10">
                                                                @if ($moduleInterface->functionExists("CreateAccount"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Create');"> Create </button>
                                                                @endif
                                                                
                                                                @if ($moduleInterface->functionExists("Renew"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Renew');"> Renew </button>
                                                                @endif
                                                                
                                                                @if ($moduleInterface->functionExists("SuspendAccount"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Suspend');"> Suspend </button>
                                                                @endif

                                                                @if ($moduleInterface->functionExists("UnsuspendAccount"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Unsuspend');"> Unsuspend </button>
                                                                @endif

                                                                @if ($moduleInterface->functionExists("TerminateAccount"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('Terminate');"> Terminate </button>
                                                                @endif

                                                                @if ($moduleInterface->functionExists("ChangePackage"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('ChangePackage');"> {{ $moduleInterface->getMetaDataValue("ChangePackageLabel") ?:  "Change Package" }} </button>
                                                                @endif

                                                                @if ($moduleInterface->functionExists("ChangePassword"))
                                                                <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('ChangePassword');"> Change Password </button>
                                                                @endif
                                                                
                                                                @if ($moduleInterface->functionExists("ServiceSingleSignOn"))
                                                                    @php
                                                                        $btnLabel = $moduleInterface->getMetaDataValue("ServiceSingleSignOnLabel");
                                                                        if (!$btnLabel) {
                                                                            $btnLabel = __("admin.ssoservicelogin");
                                                                        }
                                                                    @endphp
                                                                <button type="button" class="btn btn-light my-1 mx-1" data-label="ServiceSingleSignOn"  onclick="modCommand('ServiceSingleSignOn', this);">{!! $btnLabel !!}</button>
                                                                @endif
                                                                
                                                                @if ($moduleInterface->isApplicationLinkingEnabled() && $moduleInterface->isApplicationLinkSupported())
                                                                <button type="button" class="btn btn-light my-1 mx-1" onclick="modCommand('ManageAppLinks');"> Manage App Links </button>
                                                                @endif
                                                                
                                                                @if ($moduleButtons)
                                                                <br>
                                                                {!! implode(" ", $moduleButtons) !!}
                                                                @endif
                                                            </div>
                                                        </div>

                                                        @if ($moduleInterface->functionExists("AdminServicesTabFields")) 
                                                            @php
                                                            if (!$moduleParams) {
                                                                $moduleParams = $moduleInterface->buildParams();
                                                            }

                                                            $fieldsArray = $moduleInterface->call("AdminServicesTabFields", $moduleParams);
                                                            if ($adminServicesTabFieldsSaveErrors = session()->get("adminServicesTabFieldsSaveErrors")) {
                                                                session()->forget("adminServicesTabFieldsSaveErrors");
                                                                echo '<div class="form-group row">
                                                                            <label for="#" class="col-sm-2 col-form-label">' . __("admin.error") .'</label>
                                                                            <div class="col-sm-10">' .$adminServicesTabFieldsSaveErrors .'</div>
                                                                        </div>'; 
                                                            }

                                                            if ($fieldsArray && is_array($fieldsArray)) {
                                                                foreach ($fieldsArray as $fieldName => $fieldValue) {
                                                                    echo '<div class="form-group row">
                                                                            <label for="#" class="col-sm-2 col-form-label">' .$fieldName .'</label>
                                                                            <div class="col-sm-10">' .$fieldValue .'</div>
                                                                        </div>'; 
                                                                }
                                                            }
                                                            @endphp
                                                        @endif
                                                    @endif

                                                    @foreach ($customFields as $customfield)
                                                        <div class="form-group row">
                                                            <label for="#" class="col-sm-2 col-form-label">{!! $customfield["name"] !!}</label>
                                                            <div class="col-sm-10">{!! $customfield["input"] !!}</div>
                                                        </div>
                                                    @endforeach
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
    
    @if (isset($clientsdetails))
    <script>

        $(() => {
            $('#inputRegDate').datepicker(dateRangeOption);
            $('#inputTerminationDate').datepicker(dateRangeOption);
            $('#inputNextDueDate').datepicker(dateRangeOption);
        });

        const modCommand = (action, element = null) => {
            let url = "{!! route('admin.pages.clients.viewclients.clientservices.modCommand') !!}";
            let title = "";
            let message = "";
            let additionalOptions = {};
            let payloads = {
                userid: "{{ $userid }}",
                id: "{{ $id }}",
            };

            @if ($addonModule)
                payloads.aid = "{{ $aid }}";
            @endif

            switch (action) {
                @if ($addonModule)
                case "Create":
                    payloads.modop = "create";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.servicescreatesure') }}";
                    break;
                case "Renew":
                    payloads.modop = "renew";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.servicesrenewSure') }}";
                    break;
                case "Suspend":
                    payloads.modop = "suspend";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = `
                        <label for="#" class="col-sm-12 col-form-label">{{ __('admin.servicessuspendsure') }}</label>
                        <div class="form-group row">
                            <label for="#" class="col-sm-4 col-form-label text-left">{{ __('admin.servicessuspensionreason') }}</label>
                            <div class="col-sm-8 text-left">
                                <input type="text" name="suspreason" id="suspreason" class="swal2-input form-control" placeholder="Input Suspension Reason (If Any)">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="#" class="col-sm-4 col-form-label "></label>
                            <div class="col-sm-8 text-left">
                                <div class="form-check">
                                    <input type="checkbox" name="suspemail" id="suspemail" class="form-check-input" value="1">
                                    <label class="form-check-label" for="suspemail">{{ __('admin.servicessuspendsendemail') }}</label>
                                </div>
                            </div>
                        </div>`;
                    break;
                case "Unsuspend":
                    payloads.modop = "unsuspend";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = `
                        <label for="#" class="col-sm-12 col-form-label">{{ __('admin.servicesunsuspendsure') }}</label>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" name="unsuspended_email" id="unsuspended_email" class="form-check-input" value="true">
                                    <label class="form-check-label" for="unsuspended_email">{{ __('admin.automationsendAutoUnsuspendEmail') }}</label>
                                </div>
                            </div>
                        </div>`;
                    break;
                case "Terminate":
                    payloads.modop = "terminate";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = `<label for="#" class="col-sm-12 col-form-label">{{ __('admin.servicesterminatesure') }}</label>`;

                    @if ($moduleInterface)
                        @if ($moduleInterface->getLoadedModule() == "Cpanel")
                            let keep = `{{ __('admin.serviceskeepDnsZone') }} (<a href="https://docs.whmcs.com/CPanel/WHM#Keep_DNS_Zone_on_Termination" class="autoLinked" target="_blank">{{ __('admin.learnMore') }}</a>)`;
                            message += `
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input type="checkbox" name="keep_zone" id="inputKeepCPanelDnsZone" class="form-check-input" value="true">
                                            <label class="form-check-label" for="inputKeepCPanelDnsZone">${keep}</label>
                                        </div>
                                    </div>
                                </div>`;
                        @endif
                    @endif
                    break;
                case "ChangePackage":
                    payloads.modop = "changepackage";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.serviceschgpacksure') }}";
                    break;
                case "ChangePassword":
                    payloads.modop = "changepw";
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.serviceschangepwsure') }}";
                    break;
                case "ManageAppLinks":
                    // SKIP and Noted!
                    payloads.modop = "manageapplinks";
                    title = "{{ __('admin.domainsresendNotification') }}";
                    message = "{{ __('admin.domainsresendNotificationQuestion') }}";
                    break;
                case "Custom":
                    payloads.modop = "custom";
                    payloads.ac = $(element).attr('data-act');
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.servicescustomsure') }}".replace(":act", $(element).attr('data-label'));
                    break;
                case "ServiceSingleSignOn":
                    payloads.modop = "singlesignon";
                    payloads.server = $("#server").val();
                    title = "{{ __('admin.servicesconfirmcommand') }}";
                    message = "{{ __('admin.servicescustomsure') }}".replace(":act", $(element).attr('data-label'));
                    break;
                @endif
                case "InfoSubscription":
                    payloads.modop = "infosubscription";
                    payloads.sub = "addons";
                    title = "{{ __('admin.servicescancelSubscription') }}";
                    message = "{{ __('admin.servicescancelSubscriptionSure') }}";
                    return false;
                    break;
                case "CancelSubscription":
                    payloads.modop = "cancelsubscription";
                    payloads.sub = "addons";
                    title = "{{ __('admin.servicescancelSubscription') }}";
                    message = "{{ __('admin.servicescancelSubscriptionSure') }}";
                    break;
                case "Delete":
                    payloads.modop = "delete";
                    title = "{{ __('admin.servicesdeleteproduct') }}";
                    message = "{{ __('admin.servicesproddeletesure') }}";
                    break;
                case "DeleteAddons":
                    payloads.modop = "deleteaddons";
                    payloads.aid = $(element).attr('data-aid');
                    title = "{{ __('admin.servicesdeleteproduct') }}";
                    message = "{{ __('admin.addonsareYouSureDelete') }}";
                    break;
                default:
                    Toast.fire({icon: 'warning', title: 'N/A!',});
                    return;
            }

            Swal.fire({
                ...additionalOptions,
                title: title,
                html: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText:  "OK",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async (data) => { 
                    if (action == "Suspend") {
                        payloads.suspreason = $('#suspreason').val();
                        payloads.suspemail = $("#suspemail").is(':checked') ? true : false;
                    }

                    if (action == "Unsuspend") {
                        payloads.unsuspended_email = $("#unsuspended_email").is(':checked') ? true : false;
                    }

                    if (action == "Terminate") {
                        payloads.keep_zone = $("#inputKeepCPanelDnsZone").is(':checked') ? true : false;
                    }

                    options.method = "POST";
                    options.body = JSON.stringify(payloads);

                    const response = await cbmsPost(url, options);
                    if (!response) {
                        const error = "An error occured.";
                        return Swal.showValidationMessage(`Request failed: ${error}`);
                    }

                    return response;
                },
            }).then((response) => {
                if (response.value) {
                    const { result, message, modresult = null } = response.value;

                    Toast.fire({ icon: result, title: message, });
                    if (result == 'error') {
                        return false;
                    }

                    if (action == 'ServiceSingleSignOn') {
                        if (modresult.redirectTo) {
                            setTimeout(function(){
                                window.location.href = modresult.redirectTo;
                            }, 3000);

                            return true;
                        }
                    }

                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                    
                    /*
                    if (action == 'Delete') {
                        setTimeout(function(){
                            window.location.href = "{!! route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $userid]) !!}"
                        } , 2000);   
                    }

                    if (action == 'DeleteAddons') {
                        setTimeout(function(){
                            window.location.href = "{!! route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $userid, 'id' => $id]) !!}"
                        } , 2000);   
                    }
                    */
                }
            }).catch(swal.noop);
        } 

    </script>
    @endif
@endsection
