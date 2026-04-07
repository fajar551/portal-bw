@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Manage Orders</title>
@endsection

@section('styles')
    <!-- Date Picker -->
    <link href="{{ Theme::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <!-- <div class="row">
                        <div class="col-12 p-3">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">Dashboard</h4>
                            </div>
                        </div>
                    </div> -->
                <!-- end page title -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h4>Manage Orders</h4>
                                    <div class="card p-3">
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
                                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                    <div class="card mb-1 shadow-none">
                                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne">
                                                            <div class="card-header" id="headingOne">
                                                                <h6 class="m-0">
                                                                    Search & filter
                                                                    <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                </h6>
                                                            </div>
                                                        </a>
                                                        <div id="collapseOne" class="collapse hide" aria-labelledby="headingOne" data-parent="#accordion">
                                                            <div class="card-body p-0 mt-3">
                                                                <form action="" method="POST" id="form-filters" enctype="multipart/form-data" onsubmit="return filterTable(this)" autocomplete="off">
                                                                    @csrf
                                                                    <div class="row">
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="orderid" class="col-sm-3 col-form-label">Order ID</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="number" step="1" min="0" class="form-control" name="orderid" id="orderid" placeholder="Order ID"/>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="ordernum" class="col-sm-3 col-form-label">Order #</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="number" step="1" min="0" class="form-control" name="ordernum" id="ordernum" placeholder="Order Number"/>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="orderdate" class="col-sm-3 col-form-label">Date Range</label>
                                                                                <div class="col-sm-9">
                                                                                    <div class="input-daterange input-group" id="orderdate_range">
                                                                                        <input type="text" class="form-control" name="orderdate_from" id="orderdate_from" placeholder="From (dd/mm/yyyy)" />
                                                                                        <input type="text" class="form-control" name="orderdate_to" id="orderdate_to" placeholder="To (dd/mm/yyyy)"/>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="amount" class="col-sm-3 col-form-label">Amount</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="number" step="1" min="0" class="form-control" name="amount" id="amount" placeholder="Amount" />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="clientid" class="col-sm-3 col-form-label">Client</label>
                                                                                <div class="col-sm-9">
                                                                                    <select name="clientid" id="search_client" class="form-control select2-limiting" style="width: 100%">
                                                                                        @if (isset($client) && $client)
                                                                                            <option value="{{ $client->id }}" selected="selected"><strong>{{ "$client->firstname $client->lastname $client->companyname" }}</strong> #{{ $client->id }}<br /> <span>{{ $client->email }}</span></option>
                                                                                        @endif
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="paymentstatus" class="col-sm-3 col-form-label">Payment Status</label>
                                                                                <div class="col-sm-9">
                                                                                    <select class="form-control" name="paymentstatus" id="paymentstatus">
                                                                                        <option value="Any">Any</option>
                                                                                        <option value="Paid">{{ __("admin.statuspaid") }}</option>
                                                                                        <option value="Unpaid">{{ __("admin.statusunpaid") }}</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="orderstatus" class="col-sm-3 col-form-label">Status</label>
                                                                                <div class="col-sm-9">
                                                                                    <select class="form-control" name="orderstatus" id="orderstatus">
                                                                                        <option value="Any">Any</option>
                                                                                        @foreach ($orderStatus as $data)
                                                                                            <option value="{{ $data->title }}" style="color:{{ $data->color }}">{{ ( __("admin.status" .strtolower($data->title)) ? __("admin.status" .strtolower($data->title)) : $data->title) }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="orderip" class="col-sm-3 col-form-label">IP Address</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="text" class="form-control" name="orderip" id="orderip" placeholder="IP Address" value="{{ $orderip }}" />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <button class="btn btn-primary px-5 d-flex align-items-center ml-auto">
                                                                                <i class="ri-search-line mr-2"></i>
                                                                                Search
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="dt-orders" class="table table-bordered dt-responsive nowrap w-100">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">NO</th>
                                                                <th class="text-center">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="cb-select-all" class="custom-control-input" id="cb-select-all">
                                                                        <label class="custom-control-label" for="cb-select-all">&nbsp;</label>
                                                                    </div>
                                                                </th>
                                                                <th class="text-center">ID</th>
                                                                <th class="text-center">Order #</th>
                                                                <th class="text-center">Date</th>
                                                                <th class="text-center">Client Name</th>
                                                                <th class="text-center">Payment Method</th>
                                                                <th class="text-center">Total</th>
                                                                <th class="text-center">Payment Status</th>
                                                                <th class="text-center">Status</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                                <form action="{{ route('admin.pages.clients.massmail.sendmessage') }}" method="POST" id="form-sendmultiple" enctype="multipart/form-data" autocomplete="off" hidden>
                                                    @csrf
                                                    <input type="text" name="type" value="order" required hidden>
                                                    <input type="text" name="multiple" value="true" required hidden>
                                                </form>
                                                <div class="form-group row mt-3">
                                                    <label class="col-sm-2 col-form-label mt-2">With Selected: </label>
                                                    <div class="col-sm-10">
                                                        <button class="btn btn-success px-3 mt-2" onclick="massAction('massAccept');">Accept Order</button>
                                                        <button class="btn btn-primary px-3 mt-2" onclick="massAction('massCancel');">Cancel Order</button>
                                                        <button class="btn btn-danger px-3 mt-2" onclick="massAction('massDelete');">Delete Order</button>
                                                        <button class="btn btn-light px-3 mt-2" onclick="sendMultiple(selectedOrdersId, $('#form-sendmultiple'));">Send Message</button>
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
    <script src="{{ Theme::asset('assets/js/pages/helpers/select2-utils.js') }}"></script>

    <script>
        // Table
        let dtOrderTable;

        // Selected id
        let selectedOrdersId = [];

        const searchEl = $("#search_client");
        const searchURL = "{!! route('admin.pages.clients.viewclients.clientsummary.searchClient') !!}";
        const actionURL = "{!! route('admin.pages.orders.listallorders.actionCommand') !!}";

        $(() => {
            
            dtOrders();
            searchClient(searchEl, searchURL);

            $('#orderdate_range').datepicker(dateRangeOption);

            // Select all checkbox
            $('body').on('change', '#cb-select-all', function() {
                let checked = $(this).is(':checked');

                $('.select-orders').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedOrdersId.includes(id)) selectedOrdersId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedOrdersId = [];
                    }
                });

                // console.log(selectedOrdersId);
            });

            // Select individual checkbox
            $('body').on('change', '.select-orders', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());
                
                if (checked) {
                    if (!selectedOrdersId.includes(id)) selectedOrdersId.push(id);
                } else {
                    let idx = selectedOrdersId.indexOf(id);

                    if (idx > -1) selectedOrdersId.splice(idx, 1);
                }

                // console.log(selectedOrdersId);
            });

            $('body').on('click', '.act-delete', function() {
                massAction("delete", { 
                    func: $(this).attr('data-function'), 
                    lang: $(this).attr('data-lang'), 
                    id: $(this).attr('data-id')
                });
            });
        });

        const dtOrders = () => {
            dtOrderTable = $('#dt-orders').DataTable({
                // stateSave: true,
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
                    url: "{!! route('admin.pages.orders.listallorders.dtOrder') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                order: [[ 2, "DESC" ]],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'id', name: 'id', width: '2%', className:'text-center', orderable: false, 
                        render: (data, type, row) => {
                            let checked = selectedOrdersId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-orders[]" id="select-orders-${data}" ${checked} class="custom-control-input select-orders" value="${data}">
                                        <label class="custom-control-label" for="select-orders-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    { data: 'raw_id', name: 'raw_id', width: '5%', className:'text-center' },
                    { data: 'ordernum', name: 'ordernum', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'date', name: 'date', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'clientname', name: 'clientname', width: '10%', className:'text-center', searchable: false, defaultContent: 'N/A', },
                    { data: 'paymentmethod', name: 'paymentmethod', width: '10%', className:'text-center', searchable: false, defaultContent: 'N/A', },
                    { data: 'amount', name: 'amount', width: '10%', searchable: false, defaultContent: 'N/A', },
                    { data: 'paymentstatusformatted', name: 'paymentstatusformatted', width: '10%', defaultContent: 'N/A', },
                    { data: 'statusformatted', name: 'statusformatted', width: '10%', defaultContent: 'N/A', },
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const massAction = (action, params = {}) => {
            let url = actionURL;
            let message = "";
            let id = 0;

            if (!selectedOrdersId.length && action != "delete") {
                showEmptyIDToast();
                return;
            }

            switch (action) {
                case "delete":
                    message = params.lang;
                    id = params.id;

                    if (params.func == "doDelete") {
                        action = "delete";
                    } else if (params.func == "doCancelDelete") {
                        action = "cancelDelete";
                    }

                    break;
                case "massAccept":
                    message = "Are you sure you want to approve the selected orders?";
                    break;
                case "massCancel":
                    message = "Are you sure you want to cancel the selected orders?";
                    break;
                case "massDelete":
                    message = "Are you sure you want to delete the selected orders?";
                    break;
                case "sendMessage":
                    message = "Are you sure you wish to send a message for these orders?";
                    break;
                default:
                    return;
            }

            const payloads = { 
                action,
                id,
                selectedOrdersId,
            };

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
                    filterTable(null);
                }
            }).catch(swal.noop);
        }

        const filterTable = (form) => {

            selectedOrdersId = [];
            dtOrderTable.ajax.reload();

            return false;
        }
    </script>
@endsection
