@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Tickets</title>
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
                                    @if (isset($invalidClientId))
                                        <div class="card d-flex align-items-center justify-content-center p-3"
                                            style="min-height: 70vh;">
                                            <div class="col-lg-6">
                                                <div class="alert alert-warning p-3" role="alert">
                                                    <h4 class="alert-heading">Invalid Client ID</h4>
                                                    <hr>
                                                    <p class="mb-0">
                                                        Please <a
                                                            href="{{ route('admin.pages.clients.viewclients.index') }}">Click
                                                            here</a>
                                                        to find correct Client ID
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @if (isset($clientsdetails))
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card p-3">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-12 d-flex flex-row-reverse mb-2">
                                                            <a href="#">
                                                                <a href="{{ route('admin.pages.support.opennewtickets.index', ['action' => 'open', 'userid' => $userid]) }}"
                                                                    class="btn btn-outline-success align-items-center d-flex">
                                                                    <i class="ri-add-fill mr-2"></i> Open New Ticket
                                                                </a>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3 col-sm-12">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h1>{{ $ticketCounts['thisMonth'] }}</h1>
                                                            <p>Opened This Month</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-12">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h1>{{ $ticketCounts['lastMonth'] }}</h1>
                                                            <p>Opened Last Month</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-12">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h1>{{ $ticketCounts['thisYear'] }}</h1>
                                                            <p>Opened This Year</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-12">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h1>{{ $ticketCounts['lastYear'] }}</h1>
                                                            <p>Opened Last Year</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <form action="" method="POST" id="form-filters"
                                                    enctype="multipart/form-data" hidden>
                                                    @csrf
                                                    <input type="number" name="userid" value="{{ $userid }}" hidden>
                                                </form>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="table-responsive"
                                                        style="overflow-x: auto; min-width: 100%;">
                                                        <table id="dt-tickets"
                                                            class="table table-bordered dt-responsive nowrap w-100"
                                                            style="min-width: 800px;">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-center">NO</th>
                                                                    <th class="text-center">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" name="cb-select-all"
                                                                                class="custom-control-input"
                                                                                id="cb-select-all">
                                                                            <label class="custom-control-label"
                                                                                for="cb-select-all">&nbsp;</label>
                                                                        </div>
                                                                    </th>
                                                                    <th class="text-center">Flag</th>
                                                                    <th class="text-center">Date Opened</th>
                                                                    <th class="text-center">Departmen</th>
                                                                    <th class="text-center">Subject</th>
                                                                    <th class="text-center">Status</th>
                                                                    <th class="text-center">Last Reply</th>
                                                                </tr>
                                                            </thead>
                                                        </table>
                                                    </div>
                                                    <hr>
                                                    @if (0 < count($tickets))
                                                        <div class="row">
                                                            <div class="col-lg-3">
                                                                <button type="button" class="btn btn-sm btn-light"
                                                                    id="ticketsMerge"
                                                                    onclick="clientTicketCommand('merge');"> Merge </button>
                                                                <button type="button" class="btn btn-sm btn-light"
                                                                    id="ticketsClose"
                                                                    onclick="clientTicketCommand('close');"> Close </button>
                                                                <button type="button" class="btn btn-sm btn-danger"
                                                                    id="ticketsDelete"
                                                                    onclick="clientTicketCommand('delete');"> Delete
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <hr>
                                            </div>
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
    <style>
        /* Menghilangkan icon plus dari DataTables responsive */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
            display: none !important;
        }

        /* Memastikan semua kolom tetap terlihat saat zoom */
        #dt-tickets {
            min-width: 100% !important;
            width: 100% !important;
            table-layout: fixed !important;
        }

        #dt-tickets th,
        #dt-tickets td {
            white-space: nowrap;
            min-width: auto !important;
        }

        /* Menghilangkan padding kiri yang digunakan untuk icon plus */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control {
            padding-left: 8px !important;
        }

        /* Memastikan container tabel tidak ada scroll horizontal */
        .table-responsive {
            overflow-x: hidden !important;
        }

        /* Memastikan kolom memiliki lebar yang proporsional */
        #dt-tickets th:nth-child(1),
        #dt-tickets td:nth-child(1) {
            width: 5% !important;
        }

        /* Checkbox */
        #dt-tickets th:nth-child(2),
        #dt-tickets td:nth-child(2) {
            width: 8% !important;
        }

        /* Flag */
        #dt-tickets th:nth-child(3),
        #dt-tickets td:nth-child(3) {
            width: 15% !important;
        }

        /* Date */
        #dt-tickets th:nth-child(4),
        #dt-tickets td:nth-child(4) {
            width: 18% !important;
        }

        /* Department */
        #dt-tickets th:nth-child(5),
        #dt-tickets td:nth-child(5) {
            width: 25% !important;
        }

        /* Subject */
        #dt-tickets th:nth-child(6),
        #dt-tickets td:nth-child(6) {
            width: 12% !important;
        }

        /* Status */
        #dt-tickets th:nth-child(7),
        #dt-tickets td:nth-child(7) {
            width: 17% !important;
        }

        /* Last Reply */
    </style>

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
        let dtTicketsTable;

        // Selected id
        let selectedTicketsId = [];

        @if (isset($clientsdetails))

            $(() => {
                dtTickets();

                // Select all checkbox
                $('body').on('change', '#cb-select-all', function() {
                    let checked = $(this).is(':checked');

                    $('.select-tickets').each(function() {
                        if (checked) {
                            let id = parseInt($(this).val());

                            $(this).prop('checked', true);

                            if (!selectedTicketsId.includes(id)) selectedTicketsId.push(id);
                        } else {
                            $(this).prop('checked', false);

                            selectedTicketsId = [];
                        }
                    });

                    // console.log(selectedTicketsId);
                });

                // Select individual checkbox
                $('body').on('change', '.select-tickets', function() {
                    let checked = $(this).is(':checked');
                    let id = parseInt($(this).val());

                    if (checked) {
                        if (!selectedTicketsId.includes(id)) selectedTicketsId.push(id);
                    } else {
                        let idx = selectedTicketsId.indexOf(id);

                        if (idx > -1) selectedTicketsId.splice(idx, 1);
                    }

                    // console.log(selectedTicketsId);
                });
            });

            const dtTickets = () => {
                dtTicketsTable = $('#dt-tickets').DataTable({
                    stateSave: true,
                    processing: true,
                    responsive: false,
                    serverSide: true,
                    autoWidth: false,
                    scrollX: false,
                    scrollCollapse: false,
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
                        url: "{!! route('admin.pages.clients.viewclients.clienttickets.dtClientTicket') !!}",
                        type: "GET",
                        data: (data) => {
                            data.dataFiltered = $('#form-filters').serializeJSON();
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            width: '5%',
                            className: 'text-center',
                            visible: false,
                            orderable: false,
                            searchable: false,
                        },
                        {
                            data: 'id',
                            name: 'id',
                            width: '5%',
                            className: 'text-center',
                            orderable: false,
                            render: (data, type, row) => {
                                let checked = selectedTicketsId.includes(row.id) ? "checked" : "";

                                return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-tickets[]" id="select-tickets-${data}" ${checked} class="custom-control-input select-tickets" value="${data}">
                                        <label class="custom-control-label" for="select-tickets-${data}">&nbsp;</label>
                                    </div>`;
                            }
                        },
                        {
                            data: 'flag',
                            name: 'flag',
                            width: '8%',
                            className: 'text-center'
                        },
                        {
                            data: 'date',
                            name: 'date',
                            width: '15%',
                            className: 'text-center',
                            defaultContent: 'N/A',
                        },
                        {
                            data: 'department',
                            name: 'department',
                            width: '25%',
                            defaultContent: 'N/A',
                        },
                        {
                            data: 'subject',
                            name: 'subject',
                            width: '25%',
                            defaultContent: 'N/A',
                        },
                        {
                            data: 'status',
                            name: 'status',
                            width: '15%',
                            defaultContent: 'N/A',
                        },
                        {
                            data: 'last_reply',
                            name: 'last_reply',
                            width: '25%',
                            defaultContent: 'N/A',
                        },
                    ],
                    // Menonaktifkan responsive behavior sepenuhnya
                    responsive: {
                        details: false
                    },
                });
            }

            const clientTicketCommand = (action) => {
                let title = "{{ __('admin.areYouSure') }}";
                let message = "";
                let url = "{!! route('admin.pages.clients.viewclients.clienttickets.clientTicketCommand') !!}";
                let payloads = {
                    action,
                    userid: "{{ $userid }}",
                    ticketIds: selectedTicketsId,
                };

                if (!selectedTicketsId.length) {
                    showEmptyIDToast();
                    return;
                }

                switch (action) {
                    case "merge":
                        if (selectedTicketsId.length < 2) {
                            showEmptyIDToast("{{ __('admin.supportmergeticketsfailed') }}");
                            return;
                        }
                        message = "{{ __('admin.supportmassmergeconfirm') }}";
                        break;
                    case "close":
                        message = "{{ __('admin.supportmasscloseconfirm') }}";
                        break;
                    case "delete":
                        message = "{{ __('admin.supportmassdeleteconfirm') }}";
                        break;
                    default:
                        Toast.fire({
                            icon: 'warning',
                            title: 'N/A!',
                        });
                        return;
                }

                Swal.fire({
                    title: title,
                    html: message,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "OK",
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
                        const {
                            result,
                            message
                        } = response.value;

                        Toast.fire({
                            icon: result,
                            title: message
                        });
                        filterTable(null);
                    }
                }).catch(swal.noop);
            }

            const filterTable = (form) => {
                selectedTicketsId = [];
                dtTicketsTable.ajax.reload();

                return false;
            }
        @endif
    </script>
@endsection
