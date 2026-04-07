@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Invoices</title>
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
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="row">
                                                    <div class="col-lg-12 d-flex flex-row-reverse mb-2">
                                                        <a href="{{ route("admin.pages.clients.viewclients.clientinvoices.create", ["userid" => $clientsdetails["userid"]]) }}" class="btn btn-outline-success align-items-center d-flex">
                                                            <i class="ri-add-fill mr-2"></i> Create Invoices
                                                        </a>
                                                        <a href="javascript:void(0)" type="button" id="btn-search" class="btn btn-primary mr-2 align-items-center d-flex">
                                                            <i class="ri-search-line mr-2"></i> Search
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card p-3 border">
                                                    <form action="" method="POST" id="form-filters" enctype="multipart/form-data" onsubmit="return filterTable(this)">
                                                        @csrf
                                                        <input type="number" name="userid" value="{{ $clientsdetails["userid"] }}" hidden>
                                                        <input type="number" name="serviceid" value="{{ $serviceid }}" hidden>
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="form-group row">
                                                                    <label for="clientName" class="col-sm-3 col-form-label my-1">Client Name</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" name="clientname" id="clientname" class="form-control my-1" placeholder="Client Name" autocomplete="off">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="invoicenum" class="col-sm-3 col-form-label my-1">Invoice #</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" name="invoicenum" class="form-control my-1" placeholder="Invoice Number" autocomplete="off">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="lineitem" class="col-sm-3 col-form-label my-1">Line Item Description</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" name="lineitem" id="lineitem" class="form-control my-1" placeholder="Line Item Description" autocomplete="off">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="paymentmethod" class="col-sm-3 col-form-label my-1">Payment Method</label>
                                                                    <div class="col-sm-9">
                                                                        <select class="select2-search-disable form-control" name="paymentmethod" id="paymentmethod" style="width: 100%;">
                                                                            <option value="Any">Any</option>
                                                                            @foreach ($paymentmethodlist as $paymentmethod)
                                                                                <option value="{{ $paymentmethod["gateway"] }}" @if($paymentmethod["gateway"] == old('paymentmethod')) selected @endif>
                                                                                    {{ $paymentmethod["value"] }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="invstatus" class="col-sm-3 col-form-label my-1 ">Status</label>
                                                                    <div class="col-sm-9">
                                                                        <select class="select2-search-disable form-control" name="invstatus" id="invstatus" style="width: 100%;">
                                                                            <option value="Any">Any</option>
                                                                            <option value="Draft">Draft</option>
                                                                            <option value="Unpaid">Unpaid</option>
                                                                            <option value="Overdue">Overdue</option>
                                                                            <option value="Paid">Paid</option>
                                                                            <option value="Cancelled">Cancelled</option>
                                                                            <option value="Refunded">Refunded</option>
                                                                            <option value="Collections">Collections</option>
                                                                            <option value="Payment Pending">Payment Pending</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="totalDue" class="col-sm-3 col-form-label my-1">Total Due</label>
                                                                    <div class="col-sm-9">
                                                                        <div class="row">
                                                                            <div class="col-sm-6 d-flex">
                                                                                {{-- <label for="totalfrom" class="col-form-label my-1">From</label> --}}
                                                                                <div>
                                                                                    <input type="number" name="totalfrom" id="totalfrom" min="0" step="1" placeholder="From" class="form-control ml-2" autocomplete="off">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 d-flex">
                                                                                {{-- <label for="totalto" class="col-form-label my-1">To</label> --}}
                                                                                <div>
                                                                                    <input type="text" name="totalto" id="totalto" min="0" step="1" class="form-control ml-2" placeholder="To" autocomplete="off">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group row">
                                                                    <label for="invoicedate_range" class="col-sm-3 col-form-label my-1">Invoice Date</label>
                                                                    <div class="col-sm-9">
                                                                        <div class="input-daterange input-group" id="invoicedate_range">
                                                                            <input type="text" class="form-control" name="invoicedate_from" placeholder="From (dd/mm/yyyy)" autocomplete="off" />
                                                                            <input type="text" class="form-control" name="invoicedate_to" placeholder="To (dd/mm/yyyy)" autocomplete="off"/>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="duedate_range" class="col-sm-3 col-form-label my-1">Due Date</label>
                                                                    <div class="col-sm-9">
                                                                        <div class="input-daterange input-group" id="duedate_range">
                                                                            <input type="text" class="form-control" name="duedate_from" placeholder="From (dd/mm/yyyy)" autocomplete="off" />
                                                                            <input type="text" class="form-control" name="duedate_to" placeholder="To (dd/mm/yyyy)" autocomplete="off"/>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="datepaid_range" class="col-sm-3 col-form-label my-1">Date Paid</label>
                                                                    <div class="col-sm-9">
                                                                        <div class="input-daterange input-group" id="datepaid_range">
                                                                            <input type="text" class="form-control" name="datepaid_from" placeholder="From (dd/mm/yyyy)" autocomplete="off" />
                                                                            <input type="text" class="form-control" name="datepaid_to" placeholder="To (dd/mm/yyyy)" autocomplete="off"/>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="last_capture_range" class="col-sm-3 col-form-label my-1">Last Capture Attempt</label>
                                                                    <div class="col-sm-9">
                                                                        <div class="input-daterange input-group" id="last_capture_range">
                                                                            <input type="text" class="form-control" name="last_capture_from" placeholder="From (dd/mm/yyyy)" autocomplete="off" />
                                                                            <input type="text" class="form-control" name="last_capture_to" placeholder="To (dd/mm/yyyy)" autocomplete="off"/>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="date_refunded_range" class="col-sm-3 col-form-label my-1">Date Refunded</label>
                                                                    <div class="col-sm-9">
                                                                        <div class="input-daterange input-group" id="date_refunded_range">
                                                                            <input type="text" class="form-control" name="date_refunded_from" placeholder="From (dd/mm/yyyy)" autocomplete="off" />
                                                                            <input type="text" class="form-control" name="date_refunded_to" placeholder="To (dd/mm/yyyy)" autocomplete="off"/>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="date_cancelled_range" class="col-sm-3 col-form-label my-1">Date Cancelled</label>
                                                                    <div class="col-sm-9">
                                                                        <div class="input-daterange input-group" id="date_cancelled_range">
                                                                            <input type="text" class="form-control" name="date_cancelled_from" placeholder="From (dd/mm/yyyy)" autocomplete="off" />
                                                                            <input type="text" class="form-control" name="date_cancelled_to" placeholder="To (dd/mm/yyyy)" autocomplete="off"/>
                                                                        </div>

                                                                        {{-- <input type="date" name="date_cancelled" id="date_cancelled" class="form-control my-1" placeholder="Date Cancelled" autocomplete="off"> --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="dt-invoices" class="table table-bordered dt-responsive nowrap w-100">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">NO</th>
                                                                <th class="text-center">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="cb-select-all" class="custom-control-input" id="cb-select-all">
                                                                        <label class="custom-control-label" for="cb-select-all">&nbsp;</label>
                                                                    </div>
                                                                </th>
                                                                <th>Invoice #</th>
                                                                <th>Invoice Date</th>
                                                                <th>Due Date</th>
                                                                <th>Date Paid</th>
                                                                <th>Total</th>
                                                                <th>Payment Method</th>
                                                                <th>Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <hr>
                                                <form action="#">
                                                    <div class="form-group row">
                                                        <label for="selectedData" class="col-sm-1 col-form-label my-1">With Selected:</label>
                                                        <div class="col-lg-10 d-flex table-responsive">
                                                            <button type="button" class="btn btn-success my-1 mx-1" id="act-markpaid-invoice" onclick="actionMarkInvoice('markpaid');"> Mark Paid </button>
                                                            <button type="button" class="btn btn-light my-1 mx-1" id="act-markunpaid-invoice" onclick="actionMarkInvoice('markunpaid');"> Mark Unpaid </button>
                                                            <button type="button" class="btn btn-light my-1 mx-1" id="act-markcancelled-invoice" onclick="actionMarkInvoice('markcancelled');"> Mark Cancelled </button>
                                                            <button type="button" class="btn btn-light my-1 mx-1" id="act-duplicate" onclick="actionMarkInvoice('duplicate');"> Duplicate Invoice </button>
                                                            <button type="button" class="btn btn-light my-1 mx-1" id="act-paymentreminder" onclick="actionMarkInvoice('paymentreminder');"> Send Reminder </button>
                                                            <button type="button" class="btn btn-light my-1 mx-1" id="act-merge" onclick="actionMarkInvoice('merge');"> Merge </button>
                                                            <button type="button" class="btn btn-light my-1 mx-1" id="act-masspay" onclick="actionMarkInvoice('masspay');"> Mass Pay </button>
                                                            <button type="button" class="btn btn-danger my-1 mx-1" id="act-mass-delete" onclick="actionMassDelete();"> Delete </button>
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

    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    @stack('clientsearch')
    
    <script>
        
        // Table
        let dtInvoicesTable;

        // Selected id
        let selectedInvoicesId = [];

        $(() => {

            dtInvoices();

            // Select all checkbox
            $('body').on('change', '#cb-select-all', function() {
                let checked = $(this).is(':checked');

                $('.select-invoices').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedInvoicesId.includes(id)) selectedInvoicesId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedInvoicesId = [];
                    }
                });

                // console.log(selectedInvoicesId);
            });

            // Select individual checkbox
            $('body').on('change', '.select-invoices', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());
                
                if (checked) {
                    if (!selectedInvoicesId.includes(id)) selectedInvoicesId.push(id);
                } else {
                    let idx = selectedInvoicesId.indexOf(id);

                    if (idx > -1) selectedInvoicesId.splice(idx, 1);
                }

                // console.log(selectedInvoicesId);
            });

            $('#invoicedate_range').datepicker(dateRangeOption);
            $('#duedate_range').datepicker(dateRangeOption);
            $('#datepaid_range').datepicker(dateRangeOption);
            $('#last_capture_range').datepicker(dateRangeOption);
            $('#date_refunded_range').datepicker(dateRangeOption);
            $('#date_cancelled_range').datepicker(dateRangeOption);

            $('body').on('click', '#btn-search', function() {
               $("#form-filters").submit(); 
            });

            $('body').on('click', '.act-delete', function() {
                const url = "{!! route('admin.pages.clients.viewclients.clientinvoices.delete') !!}";
                let id = $(this).attr('data-id');
                let flag = $(this).attr('data-flag');
                let confirmButtonMessage = "Yes, Delete!";
                let message = `Are you sure you want to delete this invoice<br>(recurring items in the invoice will not be reinvoiced for this date)?`;
                let returnCredit = false;
                let additionalOptions = {};

                if (flag == "ExistingCreditAndPayments") {
                    confirmButtonMessage = "Orphan Payments";
                    message = "This invoice has transactions applied to it. If you don't first refund these the transactions will be orphaned. Do you want to continue?";
                    returnCredit = true;

                    additionalOptions = {
                        input: 'radio',
                        inputOptions: {
                            '0': 'Discard Credit',
                            '1': 'Return Credit',
                        },
                        inputValidator: (value) => { if (!value) return 'You must to choose at least one option!'; },
                    }
                } else if (flag == "ExistingCredit") {
                    confirmButtonMessage = "Return Credit";
                    message = "This invoice has credit applied to it. Do you want to return the credit to the user, or discard it?";
                    returnCredit = true;
                } else if (flag == "ExistingPayments") {
                    confirmButtonMessage = "Orphan Payments";
                    message = "This invoice has transactions applied to it. If you don't first refund these the transactions will be orphaned. Do you want to continue?";
                    returnCredit = false;
                }

                Swal.fire({
                    ...additionalOptions,
                    title: "Are you sure?",
                    html: message,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: confirmButtonMessage,
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: async (data) => {
                        if (data !== true) returnCredit = data == 1 ? true : false;

                        const payloads = { 
                            id, 
                            returnCredit, 
                        };
                        
                        options.method = 'DELETE';
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

                        Toast.fire({ icon: result, title: message });
                        filterTable(null);
                    }
                }).catch(swal.noop);
            });
        });

        const dtInvoices = () => {
            dtInvoicesTable = $('#dt-invoices').DataTable({
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
                    url: "{!! route('admin.pages.clients.viewclients.clientinvoices.dtClientInvoice') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'id', name: 'id', width: '2%', className:'text-center', orderable: false, 
                        render: (data, type, row) => {
                            let checked = selectedInvoicesId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-invoices[]" id="select-invoices-${data}" ${checked} class="custom-control-input select-invoices" value="${data}">
                                        <label class="custom-control-label" for="select-invoices-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    { data: 'raw_id', name: 'raw_id', width: '5%', className:'text-center' },
                    { data: 'date', name: 'date', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'duedate', name: 'duedate', width: '10%', className:'text-center', searchable: false, defaultContent: 'N/A', },
                    { data: 'datepaid', name: 'datepaid', width: '10%', className:'text-center', searchable: false, defaultContent: 'N/A', },
                    { data: 'total', name: 'total', width: '10%', searchable: false, defaultContent: 'N/A', },
                    { data: 'paymentmethod', name: 'paymentmethod', width: '10%', defaultContent: 'N/A', },
                    { data: 'status', name: 'status', width: '10%', defaultContent: 'N/A', },
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const actionMassDelete = async () => {
            if (!selectedInvoicesId.length) {
                showEmptyIDToast();
                return;
            }

            const url = "{!! route('admin.pages.clients.viewclients.clientinvoices.delete') !!}";
            let massDelete = true;

            // console.log("Do send message action here with selected id: " +selectedInvoicesId.join(", "));
            Swal.fire({
                title: "Are you sure?",
                html: "You want to delete the selected invoices?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Delete!",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async (data) => { 
                    const payloads = { 
                        selectedInvoicesId, 
                        massDelete, 
                        userid: '{{ $clientsdetails["userid"] }}' 
                    };

                    options.method = 'DELETE';
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

                    Toast.fire({ icon: result, title: message });
                    filterTable(null);
                }
            }).catch(swal.noop);
        }

        const actionMarkInvoice = async (action) => {
            if (!selectedInvoicesId.length) {
                showEmptyIDToast();
                return;
            }

            if (selectedInvoicesId.length < 2 && action == "merge") {
                showEmptyIDToast("You must select at least 2 item in the list to be merged.");
                return;
            }

            if (selectedInvoicesId.length < 2 && action == "masspay") {
                showEmptyIDToast("You must select at least 2 item in the list to be mass payment.");
                return;
            }

            let message = "";
            let url = "";
            const payloads = { 
                selectedInvoicesId, 
                userid: '{{ $clientsdetails["userid"] }}' 
            }

            if (action == "markpaid") {
                message = "Are you sure you want to mark these invoices paid?";
                url = "{!! route('admin.pages.clients.viewclients.clientinvoices.markPaidInvoice') !!}";
            } else if (action == "markunpaid") {
                message = "Are you sure you want to mark these invoices Unpaid?";
                url = "{!! route('admin.pages.clients.viewclients.clientinvoices.markUnpaidInvoice') !!}";
            } else if (action == "markcancelled") {
                message = "Are you sure you want to cancel these invoices?";
                url = "{!! route('admin.pages.clients.viewclients.clientinvoices.markCancelledInvoice') !!}";
            } else if (action == "paymentreminder") {
                message = "Are you sure you want to send payment reminders for the selected invoices?";
                url = "{!! route('admin.pages.clients.viewclients.clientinvoices.paymentReminderInvoice') !!}";
            } else if (action == "duplicate") {
                message = "Are you sure you want to duplicate the selected invoices?";
                url = "{!! route('admin.pages.clients.viewclients.clientinvoices.duplicateInvoice') !!}";
            } else if (action == "merge") {
                message = "Are you sure you want to merge the selected invoices?";
                url = "{!! route('admin.pages.clients.viewclients.clientinvoices.mergeInvoice') !!}";
            } else if (action == "masspay") {
                message = "Are you sure you want to generate a mass pay invoice for the selected invoices?";
                url = "{!! route('admin.pages.clients.viewclients.clientinvoices.masspayInvoice') !!}";
            }

            Swal.fire({
                title: "Are you sure?",
                html: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText:  "OK",
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

                    Toast.fire({ icon: result, title: message });
                    filterTable(null);
                }
            }).catch(swal.noop);
        }

        const filterTable = (form) => {

            selectedInvoicesId = [];
            dtInvoicesTable.ajax.reload();

            return false;
        }

    </script>

@endsection
