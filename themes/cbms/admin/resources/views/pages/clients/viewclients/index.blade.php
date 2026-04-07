@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  {{ __('admin.clientsviewsearch') }}</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <!-- SHORCUTS CARD (Sidebar) -->
                     
                    <!-- END SHORTCUTS -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">{{ __('admin.clientsviewsearch') }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card p-3">
                                        <div id="accordion" class="custom-accordion mt-1 pb-1">
                                            <div class="card mb-1 shadow-none">
                                                <a href="#collapseOne" class="text-dark" data-toggle="collapse"
                                                    aria-expanded="true" aria-controls="collapseOne">
                                                    <div class="card-header" id="headingOne">
                                                        <h6 class="m-0">
                                                            Search & Filter
                                                            <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                        </h6>
                                                    </div>
                                                </a>
                                                <div id="collapseOne" class="collapse hide" aria-labelledby="headingOne" data-parent="#accordion">
                                                    <div class="card-body">
                                                        <form action="" method="POST" id="form-filters" enctype="multipart/form-data" onsubmit="return filterTable(this)">
                                                            <div class="row">
                                                                <div class="col-md-2">
                                                                    <div class="form-group">
                                                                        <label for="">Client/Company Name</label>
                                                                        <input type="text" name="client" class="form-control" placeholder="Client/Company Name">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="form-group">
                                                                        <label for="">Email Address</label>
                                                                        <input type="text" name="email" class="form-control" placeholder="Email Address">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="form-group">
                                                                        <label for="">Phone Number</label>
                                                                        <input type="text" name="phonenumber" class="form-control" placeholder="Phone Number">
                                                                        {{-- <select class="custom-select"
                                                                            aria-placeholder="Phone Number">
                                                                            <option>
                                                                                {{ __('admin.maxmindresultssubscoresphone_number') }}
                                                                            </option>
                                                                        </select> --}}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="form-group">
                                                                        <label for="">Client Group</label>
                                                                        <select name="groupid" class="custom-select" aria-placeholder="Choose Client Group">
                                                                            <option value="Any">Any</option>
                                                                            @foreach ($clientGroup as $value)
                                                                                <option value="{{ $value->id }}">{{ $value->groupname }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="form-group">
                                                                        <label for="">Status</label>
                                                                        <select name="status" class="custom-select" aria-placeholder="Choose Status">
                                                                            <option value="Any">Any</option>
                                                                            <option value="Active">Active</option>
                                                                            <option value="Inactive">Inactive</option>
                                                                            <option value="Closed">Closed</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="form-group">
                                                                        <label for="">&nbsp;</label>
                                                                        <button type="submit" class="btn btn-primary btn-block waves-effect waves-light font-weight-bold" >
                                                                            <span class="align-middle"><i class="ri-search-line mr-2"></i></span>
                                                                            {{ __('admin.appsnavsearch') }}
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="table-responsive">
                                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
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
                                                                <th class="text-center">First Name</th>
                                                                <th class="text-center">Last Name</th>
                                                                <th class="text-center">Company Name</th>
                                                                <th class="text-center">Email Address</th>
                                                                <th class="text-center">Services</th>
                                                                <th class="text-center">Created</th>
                                                                <th class="text-center">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <form action="{{ route('admin.pages.clients.massmail.sendmessage') }}" method="POST" id="form-sendmultiple" enctype="multipart/form-data" autocomplete="off" hidden>
                                                    @csrf
                                                    <input type="text" name="type" value="general" required hidden>
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
    <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>
    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}
    
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

                $('.select-clients').each(function() {
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
            $('body').on('change', '.select-clients', function() {
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
                    url: "{!! route('admin.pages.clients.viewclients.dtClient') !!}",
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
                                        <input type="checkbox" name="select-clients[]" id="select-clients-${data}" ${checked} class="custom-control-input select-clients" value="${data}">
                                        <label class="custom-control-label" for="select-clients-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    { data: 'raw_id', name: 'raw_id', width: '2%', className:'text-center' },
                    { data: 'firstname', name: 'firstname', width: '10%', defaultContent: 'N/A'},
                    { data: 'lastname', name: 'lastname', width: '10%', defaultContent: 'N/A'},
                    { data: 'companyname', name: 'companyname', width: '15%', defaultContent: 'N/A'},
                    { data: 'email', name: 'email', width: '10%', defaultContent: 'N/A'},
                    { data: 'services', name: 'services', width: '5%', defaultContent: 'N/A', className:'text-center',  orderable: false, },
                    { data: 'datecreated', name: 'datecreated', width: '10%', defaultContent: 'N/A', className:'text-center',
                        render: (data, type, row) => {
                            return data ? moment(data).format('DD/MM/YYYY') : 'N/A';
                        }
                    },
                    { data: 'status', name: 'status', width: '5%', defaultContent: 'N/A', className:'text-center',
                        render: (data, type, row) => {
                            return `<span class="badge badge-${data == "Active" ? "success" : (data == "Inactive" ? "secondary" : "danger")}">${data}</span>`;
                        }
                    },
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
