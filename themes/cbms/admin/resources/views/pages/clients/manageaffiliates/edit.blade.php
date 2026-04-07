@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Affiliates</title>
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
                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Affiliates</h4>
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
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <form action="{{ route('admin.pages.clients.manageaffiliates.update') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="number" name="id" value="{{ $affiliates->id }}" hidden>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Affiliate ID
                                                        </label>
                                                        <div class="col-sm-12 col-lg-2 pt-2">
                                                            <p>{{ $affiliates->id }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Client Name
                                                        </label>
                                                        <div class="col-sm-12 col-lg-6 pt-6">
                                                            <p>{!! $affiliates->client_name !!}</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Comission Type
                                                        </label>
                                                        <div class="col-sm-12 col-lg-9 pt-2">
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" name="paytype" id="paytype1" class="form-check-input" value="" {{ !$affiliates->paytype ? "checked" : "" }}>
                                                                <label class="form-check-label" for="paytype1">Use Default</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" name="paytype" id="paytype2" class="form-check-input" value="percentage" {{ $affiliates->paytype == "percentage" ? "checked" : "" }}>
                                                                <label class="form-check-label" for="paytype2">Percentage</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" name="paytype" id="paytype3" class="form-check-input" value="fixed" {{ $affiliates->paytype == "fixed" ? "checked" : "" }}>
                                                                <label class="form-check-label" for="paytype3">Fixed Amount</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Commision Amount
                                                        </label>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <input type="number" name="payamount" min="0" step="0.01" class="form-control" value="{{ $affiliates->payamount }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" name="onetime" id="onetime" min="0" step="0.01" class="custom-control-input" value="1" {{ $affiliates->onetime ? "checked" : "" }}>
                                                                <label class="custom-control-label" for="onetime">Pay One Time Only</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Visitor Referred
                                                        </label>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <input type="number" name="visitors" min="0" step="1" class="form-control" value="{{ $affiliates->visitors }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Signup Date
                                                        </label>
                                                        <div class="col-sm-12 col-lg-9 pt-2">
                                                            <p>{{ $affiliates->date }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Pending Commissions
                                                        </label>
                                                        <div class="col-sm-12 col-lg-9 pt-2">
                                                            <p>{{ $affiliates->pendingcommissionsamount }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Available to Withdraw Balance
                                                        </label>
                                                        <div class="col-sm-12 col-lg-9">
                                                            <input type="number" name="balance" min="0" step="0.01" value="{{ $affiliates->balance }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Withdrawn Amount
                                                        </label>
                                                        <div class="col-sm-12 col-lg-9">
                                                            <input type="number" name="withdrawn" min="0" step="0.01" value="{{ $affiliates->withdrawn }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-3 col-form-label">
                                                            Conversion Rate
                                                        </label>
                                                        <div class="col-sm-12 col-lg-9 pt-2">
                                                            <p>{{ $affiliates->conversionrate }}%</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 text-center">
                                                    <button type="submit" class="btn btn-success px-3">Save Changes</button>
                                                    <button type="reset" class="btn btn-light px-3">Cancel Changes</button>
                                                </div>
                                            </div>
                                        </form>
                                        <hr>

                                        <div class="row my-3" style="display: none">
                                            <div class="col-lg-12">
                                                <form action="#" id="form-filters" enctype="multipart/form-data" autocomplete="off" onsubmit="return false;">
                                                    @csrf
                                                    <input type="number" name="id" value="{{ $id }}">
                                                    <input type="number" name="days" id="days" value="{{ $days }}">
                                                </form>
                                            </div>
                                        </div>

                                        <div class="row my-3">
                                            <div class="col-lg-12">
                                                <nav>
                                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                        <a class="nav-link nav-item active" id="nav-referals-tab" data-toggle="tab" href="#nav-referals" role="tab" aria-controls="nav-referals" aria-selected="true" data-loaded="0" onclick="dtReferrals(this);">
                                                            Referrals
                                                        </a>
                                                        <a class="nav-link nav-item" id="nav-refsignup-tab" data-toggle="tab" href="#nav-refsignup" role="tab" aria-controls="nav-refsignup" aria-selected="true" data-loaded="0" onclick="dtReferredSignups(this);">
                                                            Referred Signups
                                                        </a>
                                                        <a class="nav-link nav-item" id="nav-commpending-tab" data-toggle="tab" href="#nav-commpending" role="tab" aria-controls="nav-commpending" aria-selected="true" data-loaded="0" onclick="dtPendingCommissions(this);">
                                                            Pending Commissions
                                                        </a>
                                                        <a class="nav-link nav-item" id="nav-commhistory-tab" data-toggle="tab" href="#nav-commhistory" role="tab" aria-controls="nav-commhistory" aria-selected="true" data-loaded="0" onclick="dtCommissionsHistory(this);">
                                                            Commissions History
                                                        </a>
                                                        <a class="nav-link nav-item" id="nav-withdrawals-tab" data-toggle="tab" href="#nav-withdrawals" role="tab" aria-controls="nav-withdrawals" aria-selected="true" data-loaded="0" onclick="dtWithdrawalsHistory(this);">
                                                            Withdrawals History
                                                        </a>
                                                    </div>
                                                </nav>
                                                <div class="tab-content" id="nav-tabContent">
                                                    <div class="tab-pane fade active show" id="nav-referals" role="tabpanel" aria-labelledby="nav-referals-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12 text-right">
                                                                <div class="text-right"><strong>Time Period:&nbsp;&nbsp;</strong> 
                                                                    <div class="btn-group btn-group-toggle mt-2 mt-xl-0" data-toggle="buttons">
                                                                        @foreach ($referralTimePeriods as $referralDays => $referralLabel) 
                                                                        <label class="btn btn-light {{ $days == $referralDays ? "active" : "" }}">
                                                                            <input type="radio" name="period" id="period{{ $referralDays }}" onchange="filterStats(this);" value="{{ $referralDays }}" {{ $days == $referralDays ? "checked=\"\"" : "" }}> {{ $referralLabel }}
                                                                        </label>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="table-responsive">
                                                                    <canvas id="line-chart" width="600" height="200"></canvas>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="table-responsive">
                                                                    <table id="dt-referals" class="table table-bordered dt-responsive nowrap w-100">
                                                                        <thead>		
                                                                            <tr class="text-center">
                                                                                <th class="text-center" hidden>NO</th>
                                                                                <th class="text-center">Referrer URL</th>
                                                                                <th class="text-center">Number of Hits</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="nav-refsignup" role="tabpanel" aria-labelledby="nav-refsignup-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="table-responsive">
                                                                    <table id="dt-signups" class="table table-bordered dt-responsive nowrap w-100">
                                                                        <thead>
                                                                            <tr class="text-center">
                                                                                <th class="text-center" hidden>NO</th>
                                                                                <th class="text-center">ID</th>
                                                                                <th class="text-center">Signup Date</th>
                                                                                <th class="text-center">Client Name</th>
                                                                                <th class="text-center">Product/Service</th>
                                                                                <th class="text-center">Commission</th>
                                                                                <th class="text-center">Last Paid</th>
                                                                                <th class="text-center">Product Status</th>
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
                                                    <div class="tab-pane fade" id="nav-commpending" role="tabpanel" aria-labelledby="nav-commpending-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="table-responsive">
                                                                    <table id="dt-commpending" class="table table-bordered dt-responsive nowrap w-100">
                                                                        <thead>
                                                                            <tr class="text-center">
                                                                                <th class="text-center" hidden>NO</th>
                                                                                <th class="text-center">Referral ID</th>
                                                                                <th class="text-center">Client Name</th>
                                                                                <th class="text-center">Product/Service</th>
                                                                                <th class="text-center">Product Status</th>
                                                                                <th class="text-center">Amount</th>
                                                                                <th class="text-center">Clearing Date</th>
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
                                                    <div class="tab-pane fade" id="nav-commhistory" role="tabpanel" aria-labelledby="nav-commhistory-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="table-responsive">
                                                                    <table id="dt-commhistory" class="table table-bordered dt-responsive nowrap w-100">
                                                                        <thead>		
                                                                            <tr class="text-center">
                                                                                <th class="text-center" hidden>NO</th>
                                                                                <th class="text-center">Date</th>
                                                                                <th class="text-center">Referral ID</th>
                                                                                <th class="text-center">Client Name</th>
                                                                                <th class="text-center">Product/Service</th>
                                                                                <th class="text-center">Product Status</th>
                                                                                <th class="text-center">Description</th>
                                                                                <th class="text-center">Amount</th>
                                                                                <th class="text-center">Actions</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <form action="{{ route('admin.pages.clients.manageaffiliates.actionCommand') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                                                                    @csrf
                                                                    <input type="number" name="id" value="{{ $id }}" hidden>
                                                                    <input type="text" name="action" value="addcomm" hidden>
                                                                    <div class="container bg-light py-3 ">
                                                                        <h5>Add Manual Commission Entry</h5>
                                                                        <div class="row mt-4">
                                                                            <div class="col-lg-12 ">
                                                                                <div class="form-group row">
                                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Date:</label>
                                                                                    <div class="col-sm-12 col-lg-5">
                                                                                        <input type="text" name="date" id="refdate" value="{{ $todayDate }}" placeholder="Date" class="form-control">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Related Referral:</label>
                                                                                    <div class="col-sm-12 col-lg-3">
                                                                                        <select name="refid" class="form-control" required>
                                                                                            <option value="">None</option>
                                                                                            @foreach ($relatedReferrals as $data)
                                                                                                @php
                                                                                                    $affaccid = $data->id;
                                                                                                    $lastpaid = $data->lastpaid;
                                                                                                    $relid = $data->relid;
                                                                                                    $referraldata = $data->referraldata;

                                                                                                    $referraldata = explode("|||", $referraldata);
                                                                                                    $firstname = $lastname = $userid = $product = $status = $domain = $amount = $date = $billingcycle = "";
                                                                                                    list($firstname, $lastname, $userid, $product, $status, $domain, $amount, $date, $billingcycle) = $referraldata;
                                                                                                    if (!$domain) { $domain = ""; }

                                                                                                    if ($lastpaid == "0000-00-00") {
                                                                                                        $lastpaid = __("admin.affiiatesnever");
                                                                                                    } else {
                                                                                                        $lastpaid = (new \App\Helpers\Client())->fromMySQLDate($lastpaid);
                                                                                                    }
                                                                                                @endphp 

                                                                                                <option value="{{ $affaccid }}">{{ "ID $affaccid - $firstname $lastname - $product" }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Description</label>
                                                                                    <div class="col-sm-12 col-lg-5">
                                                                                        <input name="description" type="text" placeholder="Description" class="form-control">
                                                                                    </div>
                                                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                                                        <p>(Optional)</p>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Amount:</label>
                                                                                    <div class="col-sm-12 col-lg-5">
                                                                                        <input type="number" min="0" step="0.01" name="amount" value="0.00" type="text" placeholder="Amount" class="form-control">
                                                                                    </div>
                                                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                                                        <p></p>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-lg-12 text-center">
                                                                                <button type="submit" class="btn btn-success px-3">Submit</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane fade" id="nav-withdrawals" role="tabpanel" aria-labelledby="nav-withdrawals-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="table-responsive">
                                                                    <table id="dt-withdrawals" class="table table-bordered dt-responsive nowrap w-100">
                                                                        <thead>		
                                                                            <tr class="text-center">
                                                                                <th class="text-center" hidden>NO</th>
                                                                                <th class="text-center">Date</th>
                                                                                <th class="text-center">Amount</th>
                                                                                <th class="text-center">Actions</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <form action="{{ route('admin.pages.clients.manageaffiliates.actionCommand') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                                                                    @csrf
                                                                    <input type="number" name="id" value="{{ $id }}" hidden>
                                                                    <input type="text" name="action" value="withdraw" hidden>
                                                                    <div class="container bg-light py-3 ">
                                                                        <h5>Make Withdrawal Payout</h5>
                                                                        <div class="row mt-4">
                                                                            <div class="col-lg-12 ">
                                                                                <div class="form-group row">
                                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Amount:</label>
                                                                                    <div class="col-sm-12 col-lg-5">
                                                                                        <input type="number" min="0" step="0.01" name="amount" value="0.00" type="text" placeholder="Amount" class="form-control">
                                                                                    </div>
                                                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                                                        <p></p>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Payout Type:</label>
                                                                                    <div class="col-sm-12 col-lg-3">
                                                                                        <select name="payouttype" class="form-control">
                                                                                            <option value="1">{{ __("admin.affiiatestransactiontoclient") }}</option>
                                                                                            <option value="2">{{ __("admin.affiiatesaddtocredit") }}</option>
                                                                                            <option>{{ __("admin.affiiateswithdrawalsonly") }}</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Transaction ID:</label>
                                                                                    <div class="col-sm-12 col-lg-5">
                                                                                        <input type="text" name="transid" placeholder="Transaction ID" class="form-control">
                                                                                    </div>
                                                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                                                        <p> (Only applies to Transaction Payout Type)</p>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <label class="col-sm-12 col-lg-2 col-form-label">Payment Method:</label>
                                                                                    <div class="col-sm-12 col-lg-3">
                                                                                        <select name="paymentmethod" class="form-control select-inline">
                                                                                            {!! $paymentMethods !!}
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-lg-12 text-center">
                                                                                <button type="submit" class="btn btn-success px-3">Submit</button>
                                                                            </div>
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
                        </div>
                    </div>
                    <!-- End MAIN CARD -->
                </div>
            </div>
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
    
    <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/chart.js/Chart.min.js') }}"></script>
    {{-- <script src="{{ Theme::asset('assets/js/pages/chart-referrals.js') }}"></script> --}}
    {{-- <script src="{{ Theme::asset('assets/js/pages/class-datatables.js') }}"></script> --}}

    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- JQuery Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    <script>

        let dtreferals;
        let dtsignups;
        let dtcommpending;
        let dtcommhistory;
        let dtwithdrawals;

        $(() => {
            $('#refdate').datepicker(dateRangeOption);
            dtReferrals($("#nav-referals-tab"));
            getReferalChart();
        });

        const dtReferrals = (el) => {
            if (hasHandled(el)) return;

            // Table dtReferrals
            dtreferals = $("#dt-referals").DataTable({
                ...baseDtTableConfig,
                ajax: {
                    url: "{!! route('admin.pages.clients.manageaffiliates.dtReferrals') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'referrer', name: 'referrer', width: '10%', className:'text-center', defaultContent: 'N/A', orderable: false, searchable: false, },
                    { data: 'hits', name: 'hits', width: '10%', className:'text-center', defaultContent: 'N/A', orderable: false, searchable: false, },
                ],
            });
        }

        const dtReferredSignups = (el) => {
            if (hasHandled(el)) return;
            
            // Table dtReferredSignups
            dtsignups = $("#dt-signups").DataTable({
                ...baseDtTableConfig,
                ajax: {
                    url: "{!! route('admin.pages.clients.manageaffiliates.dtReferredSignups') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'affaccid', name: 'affaccid', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'date', name: 'date', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'client', name: 'client', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'productservice', name: 'productservice', width: '15%', orderable: false, searchable: false, defaultContent: 'N/A', },
                    { data: 'commission', name: 'commission', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'lastpaid', name: 'lastpaid', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'status', name: 'status', width: '10%', defaultContent: 'N/A'},
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const dtPendingCommissions = (el) => {
            if (hasHandled(el)) return;

            // Table dtPendingCommissions
            dtcommpending = $("#dt-commpending").DataTable({
                ...baseDtTableConfig,
                ajax: {
                    url: "{!! route('admin.pages.clients.manageaffiliates.dtPendingCommissions') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'affaccid', name: 'affaccid', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'client', name: 'client', width: '15%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'productservice', name: 'productservice', width: '15%', orderable: false, searchable: false, defaultContent: 'N/A', },
                    { data: 'status', name: 'status', width: '10%', defaultContent: 'N/A'},
                    { data: 'amount', name: 'amount', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'clearingdate', name: 'clearingdate', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const dtCommissionsHistory = (el) => {
            if (hasHandled(el)) return;

            // Table dtCommissionsHistory
            dtcommhistory = $("#dt-commhistory").DataTable({
                ...baseDtTableConfig,
                ajax: {
                    url: "{!! route('admin.pages.clients.manageaffiliates.dtCommissionsHistory') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'date', name: 'date', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'affaccid', name: 'affaccid', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'client', name: 'client', width: '15%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'productservice', name: 'productservice', width: '15%', orderable: false, searchable: false, defaultContent: 'N/A', },
                    { data: 'status', name: 'status', width: '10%', defaultContent: 'N/A'},
                    { data: 'description', name: 'description', width: '15%', defaultContent: 'N/A'},
                    { data: 'amount', name: 'amount', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });

        }

        const dtWithdrawalsHistory = (el) => {
            if (hasHandled(el)) return;

            // Table dtWithdrawalsHistory
            dtwithdrawals = $("#dt-withdrawals").DataTable({
                ...baseDtTableConfig,
                ajax: {
                    url: "{!! route('admin.pages.clients.manageaffiliates.dtWithdrawalsHistory') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'date', name: 'date', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'amount', name: 'amount', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const hasHandled = (el) => {
            let hasLoaded = $(el).attr("data-loaded"); 

            if (hasLoaded == 0) {
                $(el).attr("data-loaded", 1);
                return false;
            } 

            return true;
        }

        // TODO:
        const actionCommand = async (action, el = null, params = {}) => {
            const url = "{!! route('admin.pages.clients.manageaffiliates.actionCommand') !!}";
            const payloads = {
                ...params,
                action,
            };

            let title = "Are you sure?"; 
            let message = `The <b>Data</b> will be deleted from database.`; 

            switch (action) {
                case "deletecommission":
                    message = '{{ __("admin.affiiatescomdeletesure") }}';
                    payloads.cid = $(el).attr("data-id");
                    break;
                case "deletehistory":
                    message = '{{ __("admin.affiiatespytdeletesure") }}';
                    payloads.hid = $(el).attr("data-id");
                    break;
                case "deletereferral":
                    message = '{{ __("admin.affiiatesrefdeletesure") }}';
                    payloads.affaccid = $(el).attr("data-id");
                    break;
                case "deletewithdrawal":
                    message = '{{ __("admin.affiiateswitdeletesure") }}';
                    payloads.wid = $(el).attr("data-id");
                    break;
                default:
                    return;
            }

            Swal.fire({
                title: title,
                html: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Delete!",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async (data) => {
                    options.method = 'POST';
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
                    const { result, message } = response.value;

                    Toast.fire({ icon: result, title: message, });
                    filterTable(action);
                }
            }).catch(swal.noop);
        }

        const filterTable = (action) => {
            if(action == "dtreferals") { dtreferals.ajax.reload() };
            if(action == "deletereferral") { dtsignups.ajax.reload() };
            if(action == "deletecommission") { dtcommpending.ajax.reload() };
            if(action == "deletehistory") { dtcommhistory.ajax.reload() };
            if(action == "deletewithdrawal") { dtwithdrawals.ajax.reload() };

            return false;
        }

        const filterStats = async (el) => {
            $("#days").val( $(el).val() );
            
            filterTable("dtreferals");
            getReferalChart();
        }

        const getReferalChart = async () => {
            const url = "{!! route('admin.pages.clients.manageaffiliates.getrefchart') !!}";
            const formData = $('#form-filters').serializeJSON();
            const payloads = {
                ...formData,
            }

            options.body = JSON.stringify(payloads);
            const response = await cbmsPost(url, options);

            if (response) {    
                const { result, message, data = null } = response;

                if (result == 'error') {
                    Toast.fire({ icon: result, title: message, });
                    return false;
                }

                if (data) {
                    plotChart(data);
                }

                return true;
            }

            console.log(`getReferalChart: Failed to fetch data. Response: ${response}`);
        }

        const plotChart = (data) => {
            new Chart(document.getElementById("line-chart"), {
                type: "line",
                data: {
                    labels: data.label, // [1500, 1600, 1700, 1750, 1800, 1850, 1900, 1950, 1999, 2050],
                    datasets: [
                        {
                            data: data.value, // [86, 114, 106, 106, 107, 111, 133, 221, 783, 2478],
                            label: data.title,
                            borderColor: "#3e95cd",
                            fill: false,
                        },
                    ],
                },
                options: {
                    title: {
                        display: true,
                    },
                },
            });
        } 
    </script>
@endsection
