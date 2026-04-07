@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Transaction</title>
@endsection

@section('styles')
    <style type="text/css"> 
        .truncate {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
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
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="row">
                                                    <div class="col-lg-12 d-flex flex-row-reverse mb-2">
                                                        <a href="{{ route("admin.pages.clients.viewclients.clienttransactions.create", ["userid" => $clientsdetails["userid"] ]) }}">
                                                            <button class="btn btn-outline-success align-items-center d-flex">
                                                                <i class="ri-add-fill mr-2"></i> Add New Transaction
                                                            </button>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-3 col-sm-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="">
                                                            <h2 class="truncate">{{ $amountIn }}</h2>
                                                            <p>Total In</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-sm-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h2 class="truncate">{{ $fees }}</h2>
                                                        <p>Total Fees</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-sm-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h2 class="truncate">{{ $amountOut  }}</h2>
                                                        <p>Total Out</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-sm-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h2 class="truncate">{{ $balance  }}</h2>
                                                        <p>Balance</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <form action="" method="POST" id="form-filters" enctype="multipart/form-data" hidden>
                                                @csrf
                                                <input type="number" name="userid" value="{{ $clientsdetails["userid"] }}" hidden>
                                            </form>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="dt-client-transaction" class="table table-bordered dt-responsive nowrap w-100">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">NO</th>
                                                                <th class="text-center">Date</th>
                                                                <th class="text-center">Payment Method</th>
                                                                <th class="text-center">Description</th>
                                                                <th class="text-center">Amount In</th>
                                                                <th class="text-center">Fees</th>
                                                                <th class="text-center">Amount Out</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
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

    <!-- Moment JS -->
    <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>

    <!-- JQuery Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    @stack('clientsearch')
    
    <script>
        // Datatable
        let dtTableTransaction;

        $(() => {
            dtClientTransaction();

            $('body').on('click', '.act-delete', function() {
                deleteTransaction($(this).attr('data-id'));
            });
        });

        const dtClientTransaction = () => {
            dtTableTransaction = $('#dt-client-transaction').DataTable({
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
                    url: "{!! route('admin.pages.clients.domainregistrations.dtClientTransaction') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'date', name: 'date', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'gateway', name: 'gateway', width: '10%', defaultContent: 'N/A', },
                    { data: 'description', name: 'description', width: '15%', defaultContent: 'N/A', },
                    { data: 'amountin', name: 'amountin', width: '10%', defaultContent: 'N/A', },
                    { data: 'fees', name: 'fees', width: '10%', defaultContent: 'N/A', },
                    { data: 'amountout', name: 'amountout', width: '10%', defaultContent: 'N/A'},
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const deleteTransaction = (id) => {
            const url = "{!! route('admin.pages.clients.viewclients.clienttransactions.delete') !!}";
            const payloads = { 
                id, 
                userid: '{{ $clientsdetails["userid"] }}' 
            };

            Swal.fire({
                title: "Are you sure?",
                html: `The <b>Data</b> will be deleted from database.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Delete",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async (data) => {
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

        const filterTable = (form) => {

            dtTableTransaction.ajax.reload();

            return false;
        }
    </script>
@endsection
