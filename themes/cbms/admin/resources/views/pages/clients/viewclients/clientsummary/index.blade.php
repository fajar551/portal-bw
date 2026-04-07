@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Client Summary</title>
@endsection

@section('styles')
    <!-- Sweetalert2 -->
    <link href="{{ Theme::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Select2 -->
    {{-- <link href="{{ Theme::asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" /> --}}

    <!-- Date Picker -->
    <link href="{{ Theme::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">
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

                            {{-- Tab Nav Profile --}}
                            @include('includes.tabnavclient')

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3 min-vh-100 bg-white">
                                        <div class="row">
                                            <div class="col-lg-4 col-md-12">
                                                <h4>#{{ $clientsdetails['userid'] }} - {{ $clientsdetails['fullname'] }}
                                                </h4>
                                            </div>
                                            <div class="col-lg-8 col-md-12">
                                                <div class="alert alert-info" role="alert">
                                                    <div class="row">
                                                        <div class="col-lg-12 col-sm-3">
                                                            <div class="row justify-content-lg-between">
                                                                <p>Exempt from Tax:
                                                                    <button class="btn btn-xs font-weight-bold mr-3"
                                                                        id="taxstatus" onclick="csajaxtoggle('taxstatus');">
                                                                        <strong
                                                                            class="text-{{ $clientsdetails['taxstatus'] == 'Yes' ? 'success' : 'danger' }}">
                                                                            <u>{{ $clientsdetails['taxstatus'] }}</u>
                                                                        </strong>
                                                                    </button>
                                                                </p>
                                                                <p>Auto CC Processing:
                                                                    <button class="btn btn-xs font-weight-bold mr-3"
                                                                        id="autocc" onclick="csajaxtoggle('autocc');">
                                                                        <strong
                                                                            class="text-{{ $clientsdetails['autocc'] == 'Yes' ? 'success' : 'danger' }}">
                                                                            <u>{{ $clientsdetails['autocc'] }}</u>
                                                                        </strong>
                                                                    </button>
                                                                </p>
                                                                <p>Send Overdue Reminders:
                                                                    <button class="btn btn-xs font-weight-bold mr-3"
                                                                        id="overduenotices"
                                                                        onclick="csajaxtoggle('overduenotices');">
                                                                        <strong
                                                                            class="text-{{ $clientsdetails['overduenotices'] == 'Yes' ? 'success' : 'danger' }}">
                                                                            <u>{{ $clientsdetails['overduenotices'] }}</u>
                                                                        </strong>
                                                                    </button>
                                                                </p>
                                                                <p>Apply Late Fees:
                                                                    <button class="btn btn-xs font-weight-bold mr-3"
                                                                        id="latefees" onclick="csajaxtoggle('latefees');">
                                                                        <strong
                                                                            class="text-{{ $clientsdetails['latefees'] == 'Yes' ? 'success' : 'danger' }}">
                                                                            <u>{{ $clientsdetails['latefees'] }}</u>
                                                                        </strong>
                                                                    </button>
                                                                </p>
                                                                <img class="ml-2"
                                                                    src="{{ Theme::asset('img/loading.gif') }}"
                                                                    id="moduleSettingsLoader" alt="loading" hidden>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($emailVerificationEnabled && $emailVerificationPending)
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="alert alert-warning" role="alert">
                                                        <div class="row d-flex align-items-center">
                                                            <div class="col-lg-8 col-sm-12">
                                                                <p class="m-0">
                                                                    <i class="ri-error-warning-fill mr-2"></i>
                                                                    {{ __('admin.emailAddressNotVerified') }}
                                                                </p>
                                                            </div>
                                                            <div class="col-lg-4 col-sm-12">
                                                                <button
                                                                    class="btn btn-success px-3 mr-auto float-lg-right btn-block"
                                                                    id="btnResendVerificationEmail"
                                                                    onclick="resendVerificationEmail(this);">{{ __('admin.resendEmail') }}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <section class="card-section">
                                            <div class="row">
                                                <div class="col-lg-3 col-md-12">
                                                    <div class="card p-3 border ">
                                                        <h4 class="card-title text-center mb-3">
                                                            {{ __('admin.clientsummaryinfoheading') }}</h4>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-striped">
                                                                <tbody>
                                                                    <tr>
                                                                        <td>First Name</td>
                                                                        <td>{{ $clientsdetails['firstname'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Last Name</td>
                                                                        <td>{{ $clientsdetails['lastname'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Company Name</td>
                                                                        <td>{{ $clientsdetails['companyname'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Email Address</td>
                                                                        <td>{{ $clientsdetails['email'] }}
                                                                            <h6>
                                                                                @if ($emailVerificationEnabled && $emailVerified)
                                                                                    <span
                                                                                        class="badge badge-success">{{ __('admin.clientsemailVerified') }}</span>
                                                                                @else
                                                                                    <span
                                                                                        class="badge badge-danger">{{ __('admin.clientsemailUnverified') }}</span>
                                                                                @endif
                                                                            </h6>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Address 1</td>
                                                                        <td>{{ $clientsdetails['address1'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Address 2</td>
                                                                        <td>{{ $clientsdetails['address2'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>City</td>
                                                                        <td>{{ $clientsdetails['city'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>State/Region</td>
                                                                        <td>{{ $clientsdetails['state'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Postcode</td>
                                                                        <td>{{ $clientsdetails['postcode'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Country</td>
                                                                        <td>{{ $clientsdetails['country'] }} -
                                                                            {{ $clientsdetails['countrylong'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Phone Number</td>
                                                                        <td>{{ $clientsdetails['phonenumber'] }}</td>
                                                                    </tr>
                                                                    @if ($showTaxIdField)
                                                                        <tr>
                                                                            <td>Tax ID</td>
                                                                            <td>{{ $clientsdetails['tax_id'] }}</td>
                                                                        </tr>
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="row">
                                                            <form action="{{ route('password.email') }}" method="POST"
                                                                id="form-resetpw" style="display: none">
                                                                @csrf
                                                                <input type="email" name="email"
                                                                    value="{{ $clientsdetails['email'] }}" hidden>
                                                            </form>

                                                            <div class="col-lg-12 action-btn text-center">
                                                                {{-- <li><a id="summary-reset-password" href="clientssummary.php?userid={$clientsdetails.userid}&resetpw=true&token={$csrfToken}"><img src="images/icons/resetpw.png" border="0" align="absmiddle" /> {$_ADMINLANG.clients.resetsendpassword}</a></li>
                                                                <li><a id="summary-login-as-client" href="../dologin.php?username={$clientsdetails.email|urlencode}&language={$adminLanguage}"><img src="images/icons/clientlogin.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.loginasclient}</a></li> --}}
                                                                <a href="{{ route('admin.pages.clients.viewclients.clientsummary.loginAsClient', ['userid' => $userid]) }}"
                                                                    target="blank"
                                                                    class="btn btn-success btn-sm btn-block"><i
                                                                        class="ri-login-box-line mr-1"></i>{{ __('admin.clientsummaryloginasclient') }}</a>
                                                                <a href="javascript:void(0);"
                                                                    id="btn-resetpw"><u>{{ __('admin.clientsresetsendpassword') }}</u></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="card p-3 border rounded">
                                                                <h4 class="card-title text-center mb-3">
                                                                    {{ __('admin.clientsummarycontactsheading') }}
                                                                </h4>
                                                                @forelse ($contacts as $contact)
                                                                    <div class="col-lg-12 action-btn text-center">
                                                                        {{-- <tr class="{cycle values=",altrow"}"><td align="center"><a href="clientscontacts.php?userid={$clientsdetails.userid}&contactid={$contact.id}">{$contact.firstname} {$contact.lastname}</a> - {$contact.email}</td></tr> --}}
                                                                        @php
                                                                            $route = route(
                                                                                'admin.pages.clients.viewclients.clientcontacts.index',
                                                                                [
                                                                                    'userid' =>
                                                                                        $clientsdetails['userid'],
                                                                                    'contactid' => $contact['id'],
                                                                                ],
                                                                            );
                                                                        @endphp

                                                                        <a
                                                                            href="{{ $route }}"><u>{{ $contact['fullname'] }}</u></a>
                                                                        - <span>{{ $contact['email'] }}</span>
                                                                    </div>
                                                                @empty
                                                                    <div class="border text-center">
                                                                        <small
                                                                            class="mb-5">{{ __('admin.clientsummarynocontacts') }}</small>
                                                                    </div>
                                                                @endforelse

                                                                <a href="{{ route('admin.pages.clients.viewclients.clientcontacts.index', ['userid' => $userid]) }}"
                                                                    class="btn-success btn-sm btn-block text-center mt-2">
                                                                    <i
                                                                        class="ri-add-line mr-2"></i>{{ __('admin.clientsaddcontact') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="card p-3 border rounded">
                                                                <h4 class="card-title text-center mb-3">
                                                                    Payment Methods
                                                                </h4>
                                                                <div class="border text-center">
                                                                    <small class="mb-5">No Payment Methods</small>
                                                                </div>
                                                                <a href="#"
                                                                    class="btn-success btn-sm btn-block text-center mt-2">
                                                                    <i class="ri-add-line mr-2"></i>Add Credit Card
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-12">
                                                    <div class="card p-3 border">
                                                        <h4 class="card-title text-center mb-3">
                                                            {{ __('admin.clientsummarybillingheading') }}
                                                        </h4>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-striped">
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Paid</td>
                                                                        <td>{{ $stats['numpaidinvoices'] }}
                                                                            ({{ $stats['paidinvoicesamount'] }})</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Draft</td>
                                                                        <td>{{ $stats['numDraftInvoices'] }}
                                                                            ({{ $stats['draftInvoicesBalance'] }})</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Unpaid/Due</td>
                                                                        <td>{{ $stats['numdueinvoices'] }}
                                                                            ({{ $stats['dueinvoicesbalance'] }})</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Cancelled</td>
                                                                        <td>{{ $stats['numcancelledinvoices'] }}
                                                                            ({{ $stats['cancelledinvoicesamount'] }})</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Refunded</td>
                                                                        <td>{{ $stats['numrefundedinvoices'] }}
                                                                            ({{ $stats['refundedinvoicesamount'] }})</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Collections</td>
                                                                        <td>{{ $stats['numcollectionsinvoices'] }}
                                                                            ({{ $stats['collectionsinvoicesamount'] }})
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Income</strong></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Gross Revenue</td>
                                                                        <td>{{ $stats['grossRevenue'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Client Expenses</td>
                                                                        <td>{{ $stats['expenses'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Net Income</td>
                                                                        <td><strong>{{ $stats['income'] }}</strong></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Credit Balance</td>
                                                                        <td>{{ $stats['creditbalance'] }}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <!-- Accordion option -->
                                                        <button type="button" class="btn btn-outline-info"
                                                            data-toggle="collapse"
                                                            data-target="#collapseOptions">Options</button>
                                                        <div class="collapse" id="collapseOptions">
                                                            <div class="card card-body mb-0">
                                                                <div class="row ">
                                                                    <div class="col-12 d-flex flex-column">
                                                                        <a href="{{ route('admin.pages.clients.viewclients.clientinvoices.create', ['userid' => $userid]) }}"
                                                                            class="btn-link">Create Invoice</a>
                                                                        <a href="javascript:void(0);" class="btn-link"
                                                                            data-toggle="modal" data-target="#funds-modal"
                                                                            data-backdrop="static">Create Add Funds
                                                                            Invoice</a>
                                                                        <a href="javascript:void(0);" class="btn-link"
                                                                            data-toggle="modal"
                                                                            data-target="#dueinvoices-modal"
                                                                            data-backdrop="static">Generate Due Invoice</a>
                                                                        {{-- <a href="{{ url('admin/clients/clientbillableitems') }}" class="btn-link">Add Billable Item</a> --}}
                                                                        <a href="javascript:void(0);" class="btn-link"
                                                                            onClick="window.open('{{ route('admin.pages.clients.viewclients.clientcredit.index', ['userid' => $clientsdetails['userid']]) }}', '', 'width=800,height=350,scrollbars=yes'); return false">Manage
                                                                            Credit</a>
                                                                        {{-- <a href="{{ url('admin/billing/quotes/add') }}" class="btn-link">Create New Quote</a> --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="card p-3 border">
                                                                <h4 class="card-title text-center mb-3">
                                                                    {{ __('admin.clientsummaryotherinfoheading') }}</h4>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-striped">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td width='100'>Status</td>
                                                                                <td>{{ $clientsdetails['status'] }}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td width='100'>Client Group</td>
                                                                                <td>{{ $clientgroup['name'] }}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td width='100'>Signup Date</td>
                                                                                <td>{{ $signupdate }}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td width='100'>Client For</td>
                                                                                <td>{{ $clientfor }}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Last Login</td>
                                                                                <td>{!! $lastlogin !!}</td>
                                                                            </tr>
                                                                            @if ($emailVerificationEnabled)
                                                                                <tr>
                                                                                    <td width='100'>Email Verified</td>
                                                                                    <td>
                                                                                        @if ($emailVerified)
                                                                                            {{ __('admin.yes') }}
                                                                                        @else
                                                                                            {{ __('admin.no') }}
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-12">
                                                    <div class="card p-3 border">
                                                        <h4 class="card-title text-center mb-3">
                                                            {{ __('admin.servicestitle') }}</h4>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-striped">
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Shared Hosting</td>
                                                                        <td>{{ $stats['productsnumactivehosting'] }}
                                                                            ({{ $stats['productsnumhosting'] }} Total)</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Reseller Hosting</td>
                                                                        <td>{{ $stats['productsnumactivereseller'] }}
                                                                            ({{ $stats['productsnumreseller'] }} Total)
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>VPS/Server</td>
                                                                        <td>{{ $stats['productsnumactiveservers'] }}
                                                                            ({{ $stats['productsnumservers'] }} Total)</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Product/Service</td>
                                                                        <td>{{ $stats['productsnumactiveother'] }}
                                                                            ({{ $stats['productsnumother'] }} Total)</td>
                                                                    </tr>
                                                                    <!--<tr>-->
                                                                    <!--    <td>Domains</td>-->
                                                                    <!--    <td>{{ $stats['numactivedomains'] }} ({{ $stats['numdomains'] }} Total)</td>-->
                                                                    <!--</tr>-->
                                                                    <tr>
                                                                        <td>Accepted Quotes</td>
                                                                        <td>{{ $stats['numacceptedquotes'] }}
                                                                            ({{ $stats['numquotes'] }} Total)</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Support Tickets</td>
                                                                        <td>{{ $stats['numactivetickets'] }}
                                                                            ({{ $stats['numtickets'] }} Total)</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Affiliate Signups</td>
                                                                        <td>{{ $stats['numaffiliatesignups'] }}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-12 text-center">
                                                                <a href="{{ route('admin.pages.orders.addneworder.index', ['clientid' => $clientsdetails['userid']]) }}"
                                                                    class="btn btn-success btn-sm btn-block"><i
                                                                        class="ri-add-line mr-2"></i> Add New Order</a>
                                                                <a href="{{ route('admin.pages.orders.listallorders.index', ['clientid' => $clientsdetails['userid']]) }}"
                                                                    class="btn-link mt-5"><u>View Orders</u></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="card p-3 border">
                                                                <h4 class="card-title text-center mb-3">
                                                                    Files
                                                                </h4>
                                                                <form
                                                                    action="{{ route('admin.pages.clients.viewclients.clientsummary.deleteFile') }}"
                                                                    method="POST" enctype="multipart/form-data"
                                                                    id="form-delete-file">
                                                                    @csrf
                                                                    <input type="number" name="clientid"
                                                                        value="{{ $clientsdetails['userid'] }}" hidden>
                                                                    <input type="number" name="id" id="file-id"
                                                                        hidden>
                                                                </form>
                                                                @forelse ($files as $file)
                                                                    <div class="border text-center">
                                                                        <small class="mb-5">
                                                                            {{ $file['title'] }}
                                                                            @if ($file['adminonly'])
                                                                                ({{ __('admin.clientsummaryfileadminonly') }})
                                                                            @endif
                                                                            -
                                                                            <a href="{{ route('admin.pages.clients.viewclients.clientsummary.downloadFile', ['userid' => $clientsdetails['userid'], 'id' => $file['id']]) }}"
                                                                                class="btn btn-sm text-primary p-1"
                                                                                title="Click to Download">
                                                                                <i class="fa fa-download"></i>
                                                                            </a>
                                                                            <button
                                                                                class="btn btn-sm text-danger act-delete-file p-1"
                                                                                title="Delete"
                                                                                data-id="{{ $file['id'] }}">
                                                                                <i class="fa fa-trash"></i>
                                                                            </button>
                                                                        </small>
                                                                    </div>
                                                                @empty
                                                                    <div class="border text-center">
                                                                        <small class="mb-5">No Files Uploaded</small>
                                                                    </div>
                                                                @endforelse
                                                                <a href="javascript:void(0)"
                                                                    class="btn-success btn-sm btn-block text-center mt-2"
                                                                    data-toggle="collapse" data-target="#collapse-file">
                                                                    <i class="ri-add-line mr-2"></i>Add File
                                                                </a>
                                                                <div class="collapse" id="collapse-file">
                                                                    <div class="card card-body mb-0">
                                                                        <div class="row ">
                                                                            <div class="col-12 d-flex flex-column">
                                                                                <form
                                                                                    action="{{ route('admin.pages.clients.viewclients.clientsummary.uploadFile') }}"
                                                                                    method="POST"
                                                                                    enctype="multipart/form-data">
                                                                                    @csrf
                                                                                    <input type="number" name="clientid"
                                                                                        value="{{ $clientsdetails['userid'] }}"
                                                                                        hidden>
                                                                                    <div class="row">
                                                                                        <div class="col-lg-12">
                                                                                            <div class="form-group row">
                                                                                                {{-- <label class="col-sm-12 col-md-12 col-lg-3 col-form-label">
                                                                                                    Title *
                                                                                                </label> --}}
                                                                                                <div
                                                                                                    class="col-sm-12 col-md-12 col-lg-12">
                                                                                                    <label
                                                                                                        class="col-form-label">
                                                                                                        Title *
                                                                                                    </label>
                                                                                                    <input type="text"
                                                                                                        name="title"
                                                                                                        class="form-control"
                                                                                                        placeholder="File title"
                                                                                                        value="{{ old('title') }}"
                                                                                                        required
                                                                                                        autocomplete="off">
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="form-group row">
                                                                                                {{-- <label class="col-sm-12 col-md-12 col-lg-3 col-form-label">
                                                                                                    File *
                                                                                                </label> --}}
                                                                                                <div
                                                                                                    class="col-sm-12 col-md-12 col-lg-9">
                                                                                                    <label
                                                                                                        class="col-form-label">
                                                                                                        File *
                                                                                                    </label>
                                                                                                    <input type="file"
                                                                                                        name="upload_file"
                                                                                                        class="form-control-file"
                                                                                                        id="upload_file"
                                                                                                        required>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="form-group row">
                                                                                                {{-- <label class="col-sm-12 col-md-12 col-lg-3 col-form-label">
                                                                                                   &nbsp;
                                                                                                </label> --}}
                                                                                                <div
                                                                                                    class="col-sm-12 col-md-12 col-lg-9">
                                                                                                    <div
                                                                                                        class="custom-control custom-checkbox">
                                                                                                        <input
                                                                                                            type="checkbox"
                                                                                                            name="adminonly"
                                                                                                            id="adminonly"
                                                                                                            class="custom-control-input"
                                                                                                            value="1"
                                                                                                            {{ old('adminonly') ? 'checked' : '' }}
                                                                                                            placeholder="Admin only">
                                                                                                        <label
                                                                                                            class="custom-control-label"
                                                                                                            for="adminonly">Admin
                                                                                                            Only</label>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-12 text-center">
                                                                                            <button type="submit"
                                                                                                class="btn btn-sm btn-success px-3">Submit</button>
                                                                                            {{-- <button type="reset" class="btn btn-light px-3">Reset</button> --}}
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
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="card p-3 border">
                                                                <h4 class="card-title text-center mb-3">
                                                                    {{ __('admin.clientsummaryemailsheading') }}
                                                                </h4>
                                                                @forelse ($lastfivemail as $email)
                                                                    <div class="border text-center">
                                                                        @php
                                                                            $route = route(
                                                                                'admin.pages.clients.viewclients.clientsummary.clientemails.index',
                                                                                [
                                                                                    'displaymessage' => true,
                                                                                    'id' => $email['id'],
                                                                                ],
                                                                            );
                                                                        @endphp
                                                                        <small class="mb-5">{{ $email['date'] }} - <a
                                                                                href="#"
                                                                                onClick="window.open('{{ $route }}', '', 'width=650, height=400, scrollbars=yes'); return false">{{ $email['subject'] }}</a></small>
                                                                    </div>
                                                                @empty
                                                                    <div class="border text-center">
                                                                        <small
                                                                            class="mb-5">{{ __('admin.clientsummarynoemails') }}</small>
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-12">
                                                    <div class="card p-3 border">
                                                        <h4 class="card-title text-center">
                                                            {{ __('admin.clientsummaryactionsheading') }}</h4>
                                                        <div class="other-action-sec">
                                                            <div class="row">
                                                                <div class="col-lg-12 d-flex flex-column">
                                                                    <a href="{{ route('admin.pages.clients.viewclients.reportstatement.index', ['userid' => $clientsdetails['userid']]) }}"
                                                                        title="{{ __('admin.clientsummaryaccountstatement') }}">{{ __('admin.clientsummaryaccountstatement') }}</a>
                                                                    <a href="{{ route('admin.pages.support.opennewtickets.index', ['action' => 'open', 'userid' => $userid]) }}"
                                                                        title="{{ __('admin.clientsummarynewticket') }}">{{ __('admin.clientsummarynewticket') }}</a>
                                                                    <a href="{{ url("admin/support/supporttickets?userid=$userid") }}"
                                                                        title="{{ __('admin.clientsummaryviewtickets') }}">{{ __('admin.clientsummaryviewtickets') }}</a>

                                                                    {{-- TODO Fix this redirect to route: admin.pages.support.supporttickets.index --}}
                                                                    {{-- <a href="{{ route("admin.pages.support.opennewtickets.index", ["view" => "any", "userid" => $clientsdetails["userid"] ]) }}" title="{{ __("admin.clientsummaryviewtickets") }}">{{ __("admin.clientsummaryviewtickets") }}</a> --}}

                                                                    @if ($affiliateid)
                                                                        <a href="{{ route('admin.pages.clients.manageaffiliates.edit', ['id' => $affiliateid]) }}"
                                                                            title="{{ __('admin.clientsummaryviewaffiliate') }}">{{ __('admin.clientsummaryviewaffiliate') }}</a>
                                                                    @else
                                                                        <a href="javascript:void(0);"
                                                                            onClick="affiliateActivate();"
                                                                            title="{{ __('admin.clientsummaryactivateaffiliate') }}">{{ __('admin.clientsummaryactivateaffiliate') }}</a>
                                                                    @endif
                                                                    <a href="javascript:void(0);" onClick="mergeClient()"
                                                                        title="{{ __('admin.clientsummarymergeclients') }}">{{ __('admin.clientsummarymergeclients') }}</a>
                                                                    <a href="javascript:void(0);" onClick="closeClient();"
                                                                        title="{{ __('admin.clientsummarycloseclient') }}">{{ __('admin.clientsummarycloseclient') }}</a>
                                                                    <a href="javascript:void(0);"
                                                                        onClick="deleteClient();"
                                                                        title="{{ __('admin.clientsummarydeleteclient') }}">{{ __('admin.clientsummarydeleteclient') }}</a>
                                                                    {{-- <a href="javascript:void(0);" onClick="alert('Not implemented yet')" title="{{ __("admin.clientsummaryexport") }}">{{ __("admin.clientsummaryexport") }}</a> --}}
                                                                </div>
                                                                <form action="" method="POST"
                                                                    enctype="multipart/form-data" id="form-other-action"
                                                                    style="display: none">
                                                                    @csrf
                                                                    <input type="number" name="clientid"
                                                                        value="{{ $clientsdetails['userid'] }}" hidden>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="card border p-3">
                                                                <h4 class="card-title text-center mb-3">
                                                                    {{ __('admin.clientsummarysendemailheading') }}</h4>
                                                                {{-- TODO: <form action="clientsemails.php?userid={$clientsdetails.userid}&action=send&type=general" method="post"> --}}
                                                                <form action="{!! route('admin.pages.clients.massmail.sendmessage') !!}" method="POST"
                                                                    autocomplete="off" id="form-sendmessage">
                                                                    @csrf
                                                                    <input type="text" name="type" value="general"
                                                                        required hidden>
                                                                    <input type="hidden" name="id"
                                                                        value="{{ $clientsdetails['userid'] }}">
                                                                    <div class="form-group">
                                                                        <select name="messageID" id="messageID"
                                                                            class="form-control"
                                                                            onchange="changeAction(this);">
                                                                            <option value="0">
                                                                                {{ __('admin.newmessage') }}</option>
                                                                            @foreach ($messageslist as $template)
                                                                                <option value="{{ $template->id }}"
                                                                                    {!! $template->custom ? "style=\"background-color:#efefef\"" : '' !!}>
                                                                                    {{ $template->name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <button type="submit"
                                                                        class="btn btn-success btn-sm btn-block">Go</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="card border p-3">
                                                                <h4 class="card-title text-center mb-3">
                                                                    {{ __('admin.fieldsadminnotes') }}
                                                                </h4>
                                                                <form
                                                                    action="{{ route('admin.pages.clients.viewclients.clientsummary.saveNotes') }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="id"
                                                                        value="{{ $clientsdetails['userid'] }}">
                                                                    <div class="form-group">
                                                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Write Your Messages">{{ $clientsdetails['notes'] }}</textarea>
                                                                    </div>
                                                                    <button type="submit"
                                                                        class="btn btn-success btn-sm btn-block">Submit</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <!-- Status Filter -->
                                        <section class="status-filter">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="card p-3 border">
                                                        <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                            <div class="card mb-1 shadow-none">
                                                                <a href="#collapseOne" class="text-dark"
                                                                    data-toggle="collapse" aria-expanded="true"
                                                                    aria-controls="collapseOne">
                                                                    <div class="card-header" id="headingOne">
                                                                        <h6 class="m-0">Status Filter
                                                                            <i
                                                                                class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                        </h6>
                                                                    </div>
                                                                </a>
                                                                <div id="collapseOne" class="collapse hide"
                                                                    aria-labelledby="headingOne" data-parent="#accordion">
                                                                    <div class="card-body p-0 mt-3">
                                                                        <form action="" method="POST"
                                                                            id="form-filters"
                                                                            enctype="multipart/form-data"
                                                                            onsubmit="return filterTable(this)">
                                                                            @csrf
                                                                            <input type="number" name="userid"
                                                                                value="{{ $clientsdetails['userid'] }}"
                                                                                hidden>
                                                                            <div class="row">
                                                                                <div class="col-lg-12">
                                                                                    <div class="form-group row">
                                                                                        <div class="col-lg-10">
                                                                                            <select name="status_filters[]"
                                                                                                id="status_filters"
                                                                                                class="form-control select2-limiting"
                                                                                                multiple="multiple"
                                                                                                style="width: 100%">
                                                                                                @foreach ($itemstatuses as $key => $status)
                                                                                                    <option
                                                                                                        value="{{ $key }}"
                                                                                                        selected="selected">
                                                                                                        {{ $status }}
                                                                                                    </option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="col-lg-2 ">
                                                                                            <button type="button"
                                                                                                class="btn btn-primary btn-sm btn-block d-flex align-items-center mt-2"
                                                                                                id="status-filter-select-all">
                                                                                                <i
                                                                                                    class="fa fa-check"></i>&nbsp;
                                                                                                Select All
                                                                                            </button>
                                                                                            <button type="button"
                                                                                                class="btn btn-warning btn-sm btn-block d-flex align-items-center mt-2"
                                                                                                id="status-filter-deselect-all">
                                                                                                <i
                                                                                                    class="fa fa-sync"></i>&nbsp;
                                                                                                Deselect All
                                                                                            </button>
                                                                                            <button
                                                                                                class="btn btn-success btn-sm btn-block d-flex align-items-center mt-2"
                                                                                                id="status-filter-apply">
                                                                                                <i
                                                                                                    class="fa fa-search"></i>&nbsp;
                                                                                                Apply
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <!-- Table -->
                                        <section class="table-section">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="card p-3 border">
                                                        <h4 class="card-title text-center mb-5">
                                                            Product/Services
                                                        </h4>
                                                        <div class="table-responisve">
                                                            <table id="dt-product-service"
                                                                class="table table-bordered dt-responsive nowrap w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">NO</th>
                                                                        <th class="text-center">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    name="cb-select-all-pservices"
                                                                                    class="custom-control-input"
                                                                                    id="cb-select-all-pservices">
                                                                                <label class="custom-control-label"
                                                                                    for="cb-select-all-pservices">&nbsp;</label>
                                                                            </div>
                                                                        </th>
                                                                        <th class="text-center">ID</th>
                                                                        <th class="text-center">Product/Service</th>
                                                                        <th class="text-center">Amount</th>
                                                                        <th class="text-center">Billing Cycle</th>
                                                                        <th class="text-center">Signup Date</th>
                                                                        <th class="text-center">Next Due Date</th>
                                                                        <th class="text-center">Status</th>
                                                                        <th class="text-center">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="card p-3 border ">
                                                        <h4 class="card-title text-center mb-5">
                                                            Addons
                                                        </h4>
                                                        <div class="table-responisve">
                                                            <table id="dt-addons"
                                                                class="table table-bordered dt-responsive nowrap w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">NO</th>
                                                                        <th class="text-center">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    name="cb-select-all-addons"
                                                                                    class="custom-control-input"
                                                                                    id="cb-select-all-addons">
                                                                                <label class="custom-control-label"
                                                                                    for="cb-select-all-addons">&nbsp;</label>
                                                                            </div>
                                                                        </th>
                                                                        <th class="text-center">ID</th>
                                                                        <th class="text-center">Name</th>
                                                                        <th class="text-center">Amount</th>
                                                                        <th class="text-center">Billing Cycle</th>
                                                                        <th class="text-center">Signup Date</th>
                                                                        <th class="text-center">Next Due Date</th>
                                                                        <th class="text-center">Status</th>
                                                                        <th class="text-center">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="card p-3 border ">
                                                        <h4 class="card-title text-center mb-5">
                                                            Domains
                                                        </h4>
                                                        <div class="table-responsive">
                                                            <table id="dt-domains"
                                                                class="table table-bordered dt-responsive nowrap w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">NO</th>
                                                                        <th class="text-center">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    name="cb-select-all-domains"
                                                                                    class="custom-control-input"
                                                                                    id="cb-select-all-domains">
                                                                                <label class="custom-control-label"
                                                                                    for="cb-select-all-domains">&nbsp;</label>
                                                                            </div>
                                                                        </th>
                                                                        <th class="text-center">ID</th>
                                                                        <th class="text-center">Domain</th>
                                                                        <th class="text-center">Registrar</th>
                                                                        <th class="text-center">Registration Date</th>
                                                                        <th class="text-center">Next Due Date</th>
                                                                        <th class="text-center">Expiry Date</th>
                                                                        <th class="text-center">Status</th>
                                                                        <th class="text-center">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="card p-3 border ">
                                                        <h4 class="card-title text-center mb-5">
                                                            Current Quotes
                                                        </h4>
                                                        <div class="table-responsive">
                                                            <table id="dt-quotes"
                                                                class="table table-bordered dt-responsive nowrap w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">NO</th>
                                                                        <th class="text-center">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    name="cb-select-all-quotes"
                                                                                    class="custom-control-input"
                                                                                    id="cb-select-all-quotes">
                                                                                <label class="custom-control-label"
                                                                                    for="cb-select-all-quotes">&nbsp;</label>
                                                                            </div>
                                                                        </th>
                                                                        <th class="text-center">ID</th>
                                                                        <th class="text-center">Subject</th>
                                                                        <th class="text-center">Date</th>
                                                                        <th class="text-center">Total</th>
                                                                        <th class="text-center">Valid Until Date</th>
                                                                        <th class="text-center">Stage</th>
                                                                        <th class="text-center">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                        <section class="bulk-action p-2 mt-5 bg-secondary text-white rounded">
                                            <form
                                                action="{{ route('admin.pages.clients.viewclients.clientsummary.massAction') }}"
                                                method="POST" id="form-mass-action" enctype="multipart/form-data"
                                                autocomplete="off">
                                                @csrf
                                                <input type="number" name="userid"
                                                    value="{{ $clientsdetails['userid'] }}" hidden>
                                                <input type="text" name="selproducts" id="selproducts" hidden>
                                                <input type="text" name="seladdons" id="seladdons" hidden>
                                                <input type="text" name="seldomains" id="seldomains" hidden>
                                                <input type="number" name="massupdate" id="massupdate" hidden>
                                                <input type="number" name="masscreate" id="masscreate" hidden>
                                                <input type="number" name="masssuspend" id="masssuspend" hidden>
                                                <input type="number" name="massunsuspend" id="massunsuspend" hidden>
                                                <input type="number" name="massterminate" id="massterminate" hidden>
                                                <input type="number" name="masschangepackage" id="masschangepackage"
                                                    hidden>
                                                <input type="number" name="masschangepw" id="masschangepw" hidden>
                                                <input type="number" name="inv" id="inv" hidden>
                                                <input type="number" name="del" id="del" hidden>

                                                <div class="row">
                                                    <div class="col-lg-2 col-md-12 my-2">
                                                        <label class="mt-2" for="bulk">With Selected: </label>
                                                    </div>
                                                    <div class="col-lg-3 col-md-12 my-2">
                                                        <button type="button" class="btn btn-light btn-block px-2"
                                                            onclick="massAction('massinvoice');">
                                                            <i class="fas fa-sync"></i>
                                                            Invoice Selected Items
                                                        </button>
                                                    </div>
                                                    <div class="col-lg-3 col-md-12 my-2">
                                                        <button type="button" class="btn btn-danger btn-block px-2"
                                                            onclick="massAction('massdelete');">
                                                            <i class="fas fa-trash-alt"></i>
                                                            Delete Selected Items
                                                        </button>
                                                    </div>
                                                    <div class="col-lg-4 col-md-12 my-2">
                                                        <button type="button" id="btn-massupdate"
                                                            class="btn btn-success float-lg-right"
                                                            onclick="massAction('massupdate');">Apply</button>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-2 col-md-12 my-2">
                                                        <label class="mt-2" for="bulk">Bulk Actions: </label>
                                                    </div>
                                                    <div class="col-lg-3 col-md-12 my-2">
                                                        <select name="set_status" id="set_status" class="form-control">
                                                            <option value="">- {{ __('admin.supportsetStatus') }} -
                                                            </option>
                                                            <option value="Pending">{{ __('admin.statuspending') }}
                                                            </option>
                                                            <option value="Active">{{ __('admin.statusactive') }}
                                                            </option>
                                                            <option value="Completed">{{ __('admin.statuscompleted') }}
                                                            </option>
                                                            <option value="Suspended">{{ __('admin.statussuspended') }}
                                                            </option>
                                                            <option value="Terminated">{{ __('admin.statusterminated') }}
                                                            </option>
                                                            <option value="Cancelled">{{ __('admin.statuscancelled') }}
                                                            </option>
                                                            <option value="Fraud">{{ __('admin.statusfraud') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-3 col-md-12 my-2">
                                                        <select name="paymentmethod" id="paymentmethod"
                                                            class="form-control">
                                                            {!! $paymentmethoddropdown !!}
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-4 col-md-12 my-2">
                                                        <a href="#collapseTwo"
                                                            class="btn btn-outline-light text-light float-lg-right"
                                                            data-toggle="collapse" aria-expanded="true"
                                                            aria-controls="collapseTwo">
                                                            Show Advanced Options
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-2 col-md-12 my-2">
                                                        <label class="mt-2" for="bulk"></label>
                                                    </div>
                                                    <div class="col-lg-3 col-md-12 my-2">
                                                        <input type="checkbox" name="overideautosuspend"
                                                            id="overridesuspend" value="1" />
                                                        <label class="mt-2" for="overridesuspend">Do not suspend
                                                            until</label>
                                                    </div>
                                                    <div class="col-lg-3 col-md-12 my-2">
                                                        <div class="input-daterange input-group "
                                                            id="overidesuspenduntil">
                                                            <input type="text" name="overidesuspenduntil"
                                                                class="input-inline form-control date-picker-single future"
                                                                placeholder="dd/mm/yyyy" autocomplete="off" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-12 col-md-12 my-2">
                                                        <div id="accordion2" class="custom-accordion mt-1 pb-1">
                                                            <div class="card mb-1 shadow-none">
                                                                {{-- <a href="#collapseOne" class="text-dark" data-toggle="collapse"
                                                                    aria-expanded="true" aria-controls="collapseOne">
                                                                    <div class="card-header" id="headingOne">
                                                                        <h6 class="m-0">Show Advanced Options
                                                                            <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                        </h6>
                                                                    </div>
                                                                </a> --}}
                                                                <div id="collapseTwo" class="collapse hide bg-secondary"
                                                                    aria-labelledby="headingTwo"
                                                                    data-parent="#accordion2">
                                                                    <div class="card-body p-0 mt-3 ">
                                                                        <div class="row">
                                                                            <div class="col-lg-6">
                                                                                <div class="form-group row">
                                                                                    <label for="firstpaymentamount"
                                                                                        class="col-sm-4 col-form-label">First
                                                                                        Payment Amount</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="number"
                                                                                            min="0" step="1"
                                                                                            name="firstpaymentamount"
                                                                                            class="form-control input-200"
                                                                                            placeholder="First Payment Value"
                                                                                            autocomplete="off" />
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group row">
                                                                                    <label for="visitor-reff"
                                                                                        class="col-sm-4 col-form-label">Next
                                                                                        Due Date</label>
                                                                                    <div class="col-sm-8">
                                                                                        <div class="input-daterange input-group "
                                                                                            id="nextduedate">
                                                                                            <input type="text"
                                                                                                name="nextduedate"
                                                                                                class="input-inline form-control date-picker-single future"
                                                                                                placeholder="dd/mm/yyyy"
                                                                                                autocomplete="off" />
                                                                                        </div>
                                                                                        <input type="checkbox"
                                                                                            name="proratabill"
                                                                                            id="proratabill"
                                                                                            value="1"
                                                                                            autocomplete="off" />
                                                                                        <label class="mt-2"
                                                                                            for="proratabill">Create
                                                                                            Prorata Invoice</label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-lg-6">
                                                                                <div class="form-group row">
                                                                                    <label for="recurringamount"
                                                                                        class="col-sm-4 col-form-label">Recurring
                                                                                        Amount</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="number"
                                                                                            min="0" step="1"
                                                                                            name="recurringamount"
                                                                                            class="form-control input-200"
                                                                                            placeholder="Recurring Amount Value"
                                                                                            autocomplete="off" />
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group row">
                                                                                    <label for="billingcycle"
                                                                                        class="col-sm-4 col-form-label">Billing
                                                                                        Cycle</label>
                                                                                    <div class="col-sm-8">
                                                                                        <select name="billingcycle"
                                                                                            class="form-control input-200">
                                                                                            <option value="">-
                                                                                                {{ __('admin.nochange') }}
                                                                                                -</option>
                                                                                            <option value="Free Account">
                                                                                                {{ __('admin.billingcyclesfree') }}
                                                                                            </option>
                                                                                            <option value="One Time">
                                                                                                {{ __('admin.billingcyclesonetime') }}
                                                                                            </option>
                                                                                            <option value="Monthly">
                                                                                                {{ __('admin.billingcyclesmonthly') }}
                                                                                            </option>
                                                                                            <option value="Quarterly">
                                                                                                {{ __('admin.billingcyclesquarterly') }}
                                                                                            </option>
                                                                                            <option value="Semi-Annually">
                                                                                                {{ __('admin.billingcyclessemiannually') }}
                                                                                            </option>
                                                                                            <option value="Annually">
                                                                                                {{ __('admin.billingcyclesannually') }}
                                                                                            </option>
                                                                                            <option value="Biennially">
                                                                                                {{ __('admin.billingcyclesbiennially') }}
                                                                                            </option>
                                                                                            <option value="Triennially">
                                                                                                {{ __('admin.billingcyclestriennially') }}
                                                                                            </option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-lg-12">
                                                                                <div class="form-group row">
                                                                                    <label for="modulecommand"
                                                                                        class="col-sm-2 col-form-label">Module
                                                                                        Commands</label>
                                                                                    <div class="col-sm-10">
                                                                                        <button type="button"
                                                                                            class="btn btn-light mt-2"
                                                                                            onclick="massAction('masscreate');">{{ __('admin.modulebuttonscreate') }}</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-light mt-2"
                                                                                            onclick="massAction('masssuspend');">{{ __('admin.modulebuttonssuspend') }}</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-light mt-2"
                                                                                            onclick="massAction('massunsuspend');">{{ __('admin.modulebuttonsunsuspend') }}</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-light mt-2"
                                                                                            onclick="massAction('massterminate');">{{ __('admin.modulebuttonsterminate') }}</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-light mt-2"
                                                                                            onclick="massAction('masschangepackage');">{{ __('admin.modulebuttonschangepackage') }}</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-light mt-2"
                                                                                            onclick="massAction('masschangepw');">{{ __('admin.modulebuttonschangepassword') }}</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                </div>
                                            </form>
                                        </section>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--  Modal Merge Client -->
    <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="merge-client-modal"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.pages.clients.viewclients.clientsummary.mergeClient') }}" method="POST"
                enctype="multipart/form-data" id="form-merge-client">
                @csrf
                <input type="number" name="clientid" value="{{ $clientsdetails['userid'] }}" hidden>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0" id="myLargeModalLabel">Merge Client<br>
                            <p>This process allows you to merge two client accounts into one.</p>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card p-3">
                            <div class="form-group row">
                                <label class="col-sm-12 col-lg-4 col-form-label">First Client</label>
                                <div class="col-sm-12 col-lg-8 pt-2">
                                    <p>{{ $firstclientmerge_name }}</p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-lg-4 col-form-label">Second Client ID</label>
                                <div class="col-sm-12 col-lg-8">
                                    <input type="number" name="newuserid" id="newuserid" min="0" step="1"
                                        class="form-control" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-lg-4 col-form-label">Merge Method</label>
                                <div class="col-sm-12 col-lg-8">
                                    <div class="d-flex align-items-center py-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mergemethod"
                                                id="mergemethod1" value="to1" required>
                                            <label class="form-check-label" for="mergemethod1">Merge to First
                                                Client</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mergemethod"
                                                id="mergemethod2" value="to2" required>
                                            <label class="form-check-label" for="mergemethod2">Merge to Second
                                                Client</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card p-3">
                            <div class="form-group row">
                                <label class="col-sm-12 col-lg-4 col-form-label">Enter Name, Company or Email to
                                    Search:</label>
                                <div class="col-sm-12 col-lg-8 pt-2">
                                    <select name="search_client" id="search_client" class="form-control select2-limiting"
                                        style="width: 100%">

                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light waves-effect" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Funds Modal --}}
    <div class="modal fade bd-example-modal-md" id="funds-modal" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <form action="{{ route('admin.pages.clients.viewclients.clientsummary.addfunds') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="number" name="clientid" value="{{ $clientsdetails['userid'] }}" hidden>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Create Add Funds Invoice</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>You can create invoices in this way to allow a client to deposit funds to their account, or to
                            charge a specific amount from a clients credit card.</p>
                        <div>
                            <div class="form-group row justify-content-center">
                                <div class="col-sm-12 col-lg-2 col-form-label text-right"> Amount: </div>
                                <div class="col-sm-12 col-lg-6">
                                    <input type="number" name="addfundsamt" min="0" step="0.01"
                                        class="form-control" value="{{ $addFundsMinimum }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Due Invoices Modal --}}
    <div class="modal fade bd-example-modal-md" id="dueinvoices-modal" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <form action="{{ route('admin.pages.clients.viewclients.clientsummary.generateDueInvoices') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                <input type="number" name="clientid" value="{{ $clientsdetails['userid'] }}" hidden>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Generate Due Invoices</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Do you want to send the invoice notification emails immediately after generation?</p>
                        <div class="form-group row">
                            <label class="col-sm-12 col-lg-4 col-form-label">Send Mail</label>
                            <div class="col-sm-12 col-lg-8">
                                <div class="d-flex align-items-center py-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="noemails" id="noemails1"
                                            value="false" required>
                                        <label class="form-check-label" for="noemails1">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="noemails" id="noemails2"
                                            value="true" required>
                                        <label class="form-check-label" for="noemails2">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Required datatable js -->
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}
    <script src="{{ Theme::asset('assets/js/accordion-radio.js') }}"></script>

    <!-- Moment JS -->
    <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>

    <!-- JQuery Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Sweetalert2 -->
    <script src="{{ Theme::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <!-- Select2 -->
    {{-- <script src="{{ Theme::asset('assets/libs/select2/js/select2.min.js') }}"></script> --}}

    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    @stack('clientsearch')

    <script>
        // Datatable
        let dtTableProductServices;
        let dtTableAddons;
        let dtTableDomains;
        let dtTableQuotes;

        // Checkbox selected
        let selectedProductServicesId = [];
        let selectedAddonsId = [];
        let selectedDomainsId = [];
        let selectedQuotesId = [];

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            timerProgressBar: true,
            timer: 5000,
        });

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

        $(() => {
            // Datepicker input
            $('#overidesuspenduntil').datepicker(dateRangeOption);
            $('#nextduedate').datepicker(dateRangeOption);

            // Action delete file
            $('body').on('click', '.act-delete-file', function() {
                let id = $(this).attr('data-id');
                $('#file-id').val(id);

                console.log(id);

                Swal.fire({
                    title: "Are you sure?",
                    html: `The <b>Data</b> will be deleted from database.`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, Delete!",
                }).then((response) => {
                    if (response.isConfirmed) {
                        $('#form-delete-file').submit();
                    }
                }).catch(swal.noop);
            });

            // Action delete file
            $('body').on('click', '#btn-resetpw', function() {
                Swal.fire({
                    title: "Are you sure?",
                    html: `You will sent password reset link to this client`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, Send Password Reset Link!",
                }).then((response) => {
                    if (response.isConfirmed) {
                        $('#form-resetpw').submit();
                    }
                }).catch(swal.noop);
            });

            dtClientSummaryProductServices();
            dtClientSummaryAddons();
            dtClientSummaryDomains();
            dtClientSummaryQuotes();

            // Select all checkbox (Product Services)
            $('body').on('change', '#cb-select-all-pservices', function() {
                let checked = $(this).is(':checked');

                $('.select-checkbox-pservices').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedProductServicesId.includes(id)) selectedProductServicesId.push(
                            id);
                    } else {
                        $(this).prop('checked', false);

                        selectedProductServicesId = [];
                    }
                });

                // console.log(selectedProductServicesId);
            });

            // Select all checkbox (Addons)
            $('body').on('change', '#cb-select-all-addons', function() {
                let checked = $(this).is(':checked');

                $('.select-checkbox-addons').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedAddonsId.includes(id)) selectedAddonsId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedAddonsId = [];
                    }
                });

                // console.log(selectedAddonsId);
            });

            // Select all checkbox (Domains)
            $('body').on('change', '#cb-select-all-domains', function() {
                let checked = $(this).is(':checked');

                $('.select-checkbox-domains').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedDomainsId.includes(id)) selectedDomainsId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedDomainsId = [];
                    }
                });

                // console.log(selectedDomainsId);
            });

            // Select all checkbox (Quotes)
            $('body').on('change', '#cb-select-all-quotes', function() {
                let checked = $(this).is(':checked');

                $('.select-checkbox-quotes').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedQuotesId.includes(id)) selectedQuotesId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedQuotesId = [];
                    }
                });

                // console.log(selectedQuotesId);
            });

            // Select individual checkbox (Product Services)
            $('body').on('change', '.select-checkbox-pservices', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());

                if (checked) {
                    if (!selectedProductServicesId.includes(id)) selectedProductServicesId.push(id);
                } else {
                    let idx = selectedProductServicesId.indexOf(id);

                    if (idx > -1) selectedProductServicesId.splice(idx, 1);
                }

                // console.log(selectedProductServicesId);
            });

            // Select individual checkbox (Addons)
            $('body').on('change', '.select-checkbox-addons', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());

                if (checked) {
                    if (!selectedAddonsId.includes(id)) selectedAddonsId.push(id);
                } else {
                    let idx = selectedAddonsId.indexOf(id);

                    if (idx > -1) selectedAddonsId.splice(idx, 1);
                }

                // console.log(selectedAddonsId);
            });

            // Select individual checkbox (Domains)
            $('body').on('change', '.select-checkbox-domains', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());

                if (checked) {
                    if (!selectedDomainsId.includes(id)) selectedDomainsId.push(id);
                } else {
                    let idx = selectedDomainsId.indexOf(id);

                    if (idx > -1) selectedDomainsId.splice(idx, 1);
                }

                // console.log(selectedDomainsId);
            });

            // Select individual checkbox (Quotes)
            $('body').on('change', '.select-checkbox-quotes', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());

                if (checked) {
                    if (!selectedQuotesId.includes(id)) selectedQuotesId.push(id);
                } else {
                    let idx = selectedQuotesId.indexOf(id);

                    if (idx > -1) selectedQuotesId.splice(idx, 1);
                }

                // console.log(selectedQuotesId);
            });

            // Status Filters
            let itemstatuses = JSON.parse('{!! json_encode($itemstatuses) !!}');
            let statusesFilter = [];
            $.each(itemstatuses, function(index, value) {
                statusesFilter.push(index);
            });

            $("#status_filters").select2({
                // theme: "classic"
                placeholder: 'Select Status',
                allowClear: true,
                width: 'resolve',
            });

            // Select all status filter
            $('body').on('click', '#status-filter-select-all', function() {
                $('#status_filters').val(statusesFilter).trigger("change");
            });

            // Deselect status filter
            $('body').on('click', '#status-filter-deselect-all', function() {
                $('#status_filters').val(null).trigger('change');
            });

            // Search Client on merge client modal
            $("#search_client").select2({
                // theme: "classic"
                placeholder: 'Search Client',
                allowClear: true,
                width: 'resolve',
                closeOnSelect: false,
                templateResult: formatState, // TODO: Fix the display text format
                ajax: {
                    url: '{{ route('admin.pages.clients.viewclients.clientsummary.searchClient') }}',
                    type: 'GET',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    delay: 1000, // Wait 1 seconds before triggering the request
                    data: function(params) {
                        // You can add more params here
                        let query = {
                            search: params.term,
                            clientid: '{{ $clientsdetails['userid'] }}',
                        }

                        return query;
                    }
                },
                cache: true,
                minimumInputLength: 3,
            });

            $('#search_client').on('select2:select', function(e) {
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

        const deleteClient = () => {
            $("#form-other-action").attr("action",
                "{{ route('admin.pages.clients.viewclients.clientsummary.deleteClient') }}");

            Swal.fire({
                title: "Are you sure?",
                html: `The <b>client and any other data related to this client</b> will be deleted from database.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Delete!",
            }).then((response) => {
                if (response.isConfirmed) {
                    $('#form-other-action').submit();
                }
            }).catch(swal.noop);
        }

        const closeClient = () => {
            $("#form-other-action").attr("action",
                "{{ route('admin.pages.clients.viewclients.clientsummary.closeClient') }}");

            Swal.fire({
                title: "Are you sure?",
                html: `The <b>client account</b> will be change to closed.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Close!",
            }).then((response) => {
                if (response.isConfirmed) {
                    $('#form-other-action').submit();
                }
            }).catch(swal.noop);
        }

        const affiliateActivate = () => {
            $("#form-other-action").attr("action",
                "{{ route('admin.pages.clients.viewclients.clientsummary.affiliateActivate') }}");

            Swal.fire({
                title: "Are you sure?",
                html: `This <b>action</b> will be activate this account as affiliate.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Activate!",
            }).then((response) => {
                if (response.isConfirmed) {
                    $('#form-other-action').submit();
                }
            }).catch(swal.noop);
        }

        const mergeClient = () => {
            $('#merge-client-modal').modal({
                show: true,
                backdrop: 'static'
            });
        }

        const dtClientSummaryProductServices = () => {
            dtTableProductServices = $('#dt-product-service').DataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                serverSide: true,
                autoWidth: false,
                searching: false,
                destroy: true,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                },
                drawCallback: () => {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
                ajax: {
                    url: "{!! route('admin.pages.clients.viewclients.clientsummary.dtClientSummaryProductServices') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        width: '2%',
                        className: 'text-center',
                        visible: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'id',
                        name: 'id',
                        width: '2%',
                        className: 'text-center',
                        orderable: false,
                        render: (data, type, row) => {
                            let checked = selectedProductServicesId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-checkbox-pservices[]" id="select-checkbox-pservices-${data}" ${checked} class="custom-control-input select-checkbox-pservices" value="${data}">
                                        <label class="custom-control-label" for="select-checkbox-pservices-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    {
                        data: 'raw_id',
                        name: 'raw_id',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'product_services',
                        name: 'product_services',
                        width: '10%',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        width: '10%',
                        orderable: false,
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'billingcycle',
                        name: 'billingcycle',
                        width: '10%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'regdate',
                        name: 'regdate',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'nextduedate',
                        name: 'nextduedate',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'domainstatus',
                        name: 'domainstatus',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        width: '5%',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                ],
            });
        }

        const dtClientSummaryAddons = () => {
            dtTableAddons = $('#dt-addons').DataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                serverSide: true,
                autoWidth: false,
                searching: false,
                destroy: true,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                },
                drawCallback: () => {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
                ajax: {
                    url: "{!! route('admin.pages.clients.viewclients.clientsummary.dtClientSummaryAddons') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        width: '2%',
                        className: 'text-center',
                        visible: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'id',
                        name: 'id',
                        width: '2%',
                        className: 'text-center',
                        orderable: false,
                        render: (data, type, row) => {
                            let checked = selectedAddonsId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-checkbox-addons[]" id="select-checkbox-addons-${data}" ${checked} class="custom-control-input select-checkbox-addons" value="${data}">
                                        <label class="custom-control-label" for="select-checkbox-addons-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    {
                        data: 'raw_id',
                        name: 'raw_id',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'addonname',
                        name: 'addonname',
                        width: '10%',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        width: '10%',
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'billingcycle',
                        name: 'billingcycle',
                        width: '10%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'regdate',
                        name: 'regdate',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'nextduedate',
                        name: 'nextduedate',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'translated_status',
                        name: 'translated_status',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        width: '5%',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                ],
            });
        }

        const dtClientSummaryDomains = () => {
            dtTableDomains = $('#dt-domains').DataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                serverSide: true,
                autoWidth: false,
                searching: false,
                destroy: true,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                },
                drawCallback: () => {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
                ajax: {
                    url: "{!! route('admin.pages.clients.viewclients.clientsummary.dtClientSummaryDomain') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        width: '2%',
                        className: 'text-center',
                        visible: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'id',
                        name: 'id',
                        width: '2%',
                        className: 'text-center',
                        orderable: false,
                        render: (data, type, row) => {
                            let checked = selectedDomainsId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-checkbox-domains[]" id="select-checkbox-domains-${data}" ${checked} class="custom-control-input select-checkbox-domains" value="${data}">
                                        <label class="custom-control-label" for="select-checkbox-domains-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    {
                        data: 'raw_id',
                        name: 'raw_id',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'domain',
                        name: 'domain',
                        width: '10%',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'registrar',
                        name: 'registrar',
                        width: '10%',
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'registrationdate',
                        name: 'registrationdate',
                        width: '10%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'nextduedate',
                        name: 'nextduedate',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'expirydate',
                        name: 'expirydate',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'translated_status',
                        name: 'translated_status',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        width: '5%',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                ],
            });
        }

        const dtClientSummaryQuotes = () => {
            dtTableQuotes = $('#dt-quotes').DataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                serverSide: true,
                autoWidth: false,
                searching: false,
                destroy: true,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                },
                drawCallback: () => {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
                ajax: {
                    url: "{!! route('admin.pages.clients.viewclients.clientsummary.dtClientSummaryQuotes') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        width: '2%',
                        className: 'text-center',
                        visible: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'id',
                        name: 'id',
                        width: '2%',
                        className: 'text-center',
                        orderable: false,
                        visible: false,
                        render: (data, type, row) => {
                            let checked = selectedQuotesId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-checkbox-quotes[]" id="select-checkbox-quotes-${data}" ${checked} class="custom-control-input select-checkbox-quotes" value="${data}">
                                        <label class="custom-control-label" for="select-checkbox-quotes-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    {
                        data: 'raw_id',
                        name: 'raw_id',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'subject',
                        name: 'subject',
                        width: '15%',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'datecreated',
                        name: 'datecreated',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'total',
                        name: 'total',
                        width: '10%',
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'validuntil',
                        name: 'validuntil',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'stage',
                        name: 'stage',
                        width: '10%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        width: '5%',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                ],
            });
        }

        const massAction = (action) => {
            if (selectedProductServicesId.length || selectedAddonsId.length || selectedDomainsId.length) {
                Swal.fire({
                    title: "Are you sure?",
                    html: `This action will modify all selected items.`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "OK!",
                }).then((response) => {
                    if (response.isConfirmed) {
                        $("#selproducts").val(JSON.stringify(selectedProductServicesId));
                        $("#seladdons").val(JSON.stringify(selectedAddonsId));
                        $("#seldomains").val(JSON.stringify(selectedDomainsId));

                        switch (action) {
                            case "massupdate":
                                $("#massupdate").val(1);
                                break;
                            case "massinvoice":
                                $("#inv").val(1);
                                break;
                            case "massdelete":
                                $("#del").val(1);
                                break;
                            case "masscreate":
                                $("#masscreate").val(1);
                                break;
                            case "masssuspend":
                                $("#masssuspend").val(1);
                                break;
                            case "massunsuspend":
                                $("#massunsuspend").val(1);
                                break;
                            case "massterminate":
                                $("#massterminate").val(1);
                                break;
                            case "masschangepackage":
                                $("#masschangepackage").val(1);
                                break;
                            case "masschangepw":
                                $("#masschangepw").val(1);
                                break;
                            default:
                                break;
                        }

                        $("#form-mass-action").submit();
                    }
                }).catch(swal.noop);
            } else {
                showEmptyIDToast();
            }
        }

        const filterTable = (form) => {

            selectedProductServicesId = [];
            selectedAddonsId = [];
            selectedDomainsId = [];
            selectedQuotesId = [];

            dtTableProductServices.ajax.reload();
            dtTableAddons.ajax.reload();
            dtTableDomains.ajax.reload();
            dtTableQuotes.ajax.reload();

            return false;
        }

        const showEmptyIDToast = (message = null) => {
            Toast.fire({
                icon: 'warning',
                title: message ?? 'You must select at least one or more item in the list.',
            });
        }

        const csajaxtoggle = async (action) => {
            let payloads = {
                userid: "{{ $userid }}",
                csajaxtoggle: action,
            };

            const url = "{!! route('admin.pages.clients.viewclients.clientsummary.csajaxtoggle') !!}";
            const options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(payloads),
            };

            $(`#${action}`).attr({
                "disabled": true
            });
            $("#moduleSettingsLoader").removeAttr("hidden");
            let response = await fetch(url, options)
                .then(response => {
                    if (!response.ok) throw new Error(response.statusText);

                    return response.json();
                })
                .catch(error => {
                    console.log(`Request failed: ${error}`);
                    return false;
                });

            $(`#${action}`).attr({
                "disabled": false
            });
            $("#moduleSettingsLoader").attr({
                "hidden": true
            });
            if (response) {
                const {
                    result,
                    message,
                    data = null
                } = response;

                if (result == "error" || !data) {
                    Toast.fire({
                        icon: result,
                        title: message,
                    });
                    return false;
                }

                $(`#${data.element}`).html(data.body);

                return true;
            }

            console.log("Failed to fetch data. Response: " + response);
        }

        const resendVerificationEmail = async (action) => {
            let payloads = {
                userid: "{{ $userid }}",
            };

            const url = "{!! route('admin.pages.clients.viewclients.clientsummary.resendVerificationEmail') !!}";
            const options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(payloads),
            };

            $("#btnResendVerificationEmail").attr({
                "disabled": true
            });
            $("#moduleSettingsLoader").removeAttr("hidden");
            let response = await fetch(url, options)
                .then(response => {
                    if (!response.ok) throw new Error(response.statusText);

                    return response.json();
                })
                .catch(error => {
                    console.log(`Request failed: ${error}`);
                    return false;
                });

            // $("#btnResendVerificationEmail").attr({ "disabled": false });
            $("#moduleSettingsLoader").attr({
                "hidden": true
            });
            if (response) {
                const {
                    result,
                    message,
                    data = null
                } = response;

                if (result == "error" || !data) {
                    Toast.fire({
                        icon: result,
                        title: message,
                    });
                    return false;
                }

                $("#btnResendVerificationEmail").text(data.body);

                return true;
            }

            console.log("Failed to fetch data. Response: " + response);
        }

        const changeAction = (el) => {
            let action = "{!! route('admin.pages.clients.massmail.sendmessage') !!}";

            if ($(el).val() != 0) {
                action = "{!! route('admin.pages.clients.viewclients.clientemails.sendMessage', [
                    'userid' => $userid,
                    'action' => 'send',
                    'type' => 'general',
                ]) !!}";
            }

            $("#form-sendmessage").attr("action", action);
        }
    </script>
@endsection
