@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Manage Affiliates</title>
@endsection

@section('styles')
    
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                     

                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h4>Affiliates</h4>
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                    <div class="card mb-1 shadow-none">
                                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse"
                                                            aria-expanded="true" aria-controls="collapseOne">
                                                            <div class="card-header" id="headingOne">
                                                                <h6 class="m-0">Search & Filter
                                                                    <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                </h6>
                                                            </div>
                                                        </a>
                                                        <div id="collapseOne" class="collapse hide"
                                                            aria-labelledby="headingOne" data-parent="#accordion">
                                                            <div class="card-body p-0 mt-3">
                                                                <form action="" method="POST" id="form-filters" enctype="multipart/form-data" onsubmit="return filterTable(this)">
                                                                    @csrf
                                                                    <div class="row">
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="client" class="col-sm-2 col-form-label">Client Name</label>
                                                                                <div class="col-sm-8">
                                                                                    <input type="text" name="client" class="form-control" placeholder="Client Name" autocomplete="off"/>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="visitor-reff"
                                                                                    class="col-sm-2 col-form-label">Visitors Referred</label>
                                                                                <div class="col-sm-4">
                                                                                    <select class="form-control" name="visitorsType">
                                                                                        <option value=">">Greater Than</option>
                                                                                        <option value="<">Less Than</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-sm-4">
                                                                                    <input type="number" name="visitors" min="0" step="1" class="form-control" placeholder="Visitors Value" autocomplete="off">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="balance-input" class="col-sm-2 col-form-label">Balance</label>
                                                                                <div class="col-sm-4">
                                                                                    <select class="form-control" name="balanceType" id="balanceType">
                                                                                        <option value=">">Greater Than</option>
                                                                                        <option value="<">Less Than</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-sm-4">
                                                                                    <input type="number" name="balance" min="0" step="1" class="form-control" placeholder="Balance Value" autocomplete="off">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="withdrawn" class="col-sm-2 col-form-label">Withdrawn</label>
                                                                                <div class="col-sm-4">
                                                                                    <select class="form-control" name="withdrawnType" id="withdrawnType">
                                                                                        <option value=">">Greater Than</option>
                                                                                        <option value="<">Less Than</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-sm-4">
                                                                                    <input type="number" name="withdrawn" min="0" step="1" class="form-control" placeholder="Withdrawn Value" autocomplete="off">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <button class="btn btn-primary px-5 d-flex align-items-center ml-auto"><i class="ri-search-line mr-2"></i>
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
                                                    <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th class="text-center">NO</th>
                                                                <th class="text-center">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="cb-select-all" class="custom-control-input" id="cb-select-all">
                                                                        <label class="custom-control-label" for="cb-select-all">&nbsp;</label>
                                                                    </div>
                                                                </th>
                                                                <th class="text-center">ID</th>
                                                                <th class="text-center">Signup Date</th>
                                                                <th class="text-center">Client Name</th>
                                                                <th class="text-center">Visitors Referred</th>
                                                                <th class="text-center">Signups</th>
                                                                <th class="text-center">Balance</th>
                                                                <th class="text-center">Withdrawn	</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <form action="{{ route('admin.pages.clients.massmail.sendmessage') }}" method="POST" id="form-sendmultiple" enctype="multipart/form-data" autocomplete="off" hidden>
                                                    @csrf
                                                    <input type="text" name="type" value="affiliate" required hidden>
                                                    <input type="text" name="multiple" value="true" required hidden>
                                                </form>
                                                <div class="form-group row mt-3">
                                                    <label class="col-sm-2 col-form-label">With Selected: </label>
                                                    <div class="col-sm-10">
                                                        <button type="button" class="btn btn-light px-5" onclick="sendMultiple(selectedId, $('#form-sendmultiple'))">Send Message</button>
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

    <!-- Moment JS -->
    <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>

    <!-- JQuery Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    <!-- Custom Js -->
    <script type="text/javascript">

        let dtTable;
        let selectedId = [];

        $(() => {
            
            dtIndex();

            // Select all checkbox
            $('body').on('change', '#cb-select-all', function() {
                let checked = $(this).is(':checked');

                $('.select-checkbox').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedId.includes(id)) selectedId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedId = [];
                    }
                });

                // console.log(selectedId);
            });

            // Select individual checkbox
            $('body').on('change', '.select-checkbox', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());
                
                if (checked) {
                    if (!selectedId.includes(id)) selectedId.push(id);
                } else {
                    let idx = selectedId.indexOf(id);

                    if (idx > -1) selectedId.splice(idx, 1);
                }

                // console.log(selectedId);
            });

            $('body').on('click', '.act-delete', function() {
                const url = "{!! route('admin.pages.clients.manageaffiliates.delete') !!}";
                const payloads = {
                    id: $(this).attr('data-id'),
                };

                Swal.fire({
                    title: "Are you sure?",
                    html: `The <b>Data</b> will be deleted from database.`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, Delete!",
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

                        Toast.fire({ icon: result, title: message, });
                        filterTable(null);
                    }
                }).catch(swal.noop);
            });

        });

        const dtIndex = () => {
            dtTable = $('#datatable').DataTable({
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
                    url: "{!! route('admin.pages.clients.manageaffiliates.dtAffiliates') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'id', name: 'id', width: '2%', className:'text-center', orderable: false, 
                        render: (data, type, row) => {
                            let checked = selectedId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-checkbox[]" id="select-checkbox-${data}" ${checked} class="custom-control-input select-checkbox" value="${data}">
                                        <label class="custom-control-label" for="select-checkbox-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    { data: 'raw_id', name: 'raw_id', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'date', name: 'date', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'full_name', name: 'full_name', width: '10%', orderable: false, searchable: false, defaultContent: 'N/A', },
                    { data: 'visitors', name: 'visitors', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'signups', name: 'signups', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'balance', name: 'balance', width: '10%', defaultContent: 'N/A'},
                    { data: 'withdrawn', name: 'withdrawn', width: '10%', defaultContent: 'N/A'},
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const filterTable = (form) => {
            
            selectedId = [];
            dtTable.ajax.reload();

            return false;
        }

    </script>

@endsection
