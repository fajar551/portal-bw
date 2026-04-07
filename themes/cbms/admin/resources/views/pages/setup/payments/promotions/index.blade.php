@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Promotions</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <!-- Sidebar Shortcut -->
                     
                    <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Promotions/Coupons</h4>
                                    </div>
                                </div>
                            </div>
                            @if(Session::has('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                                @php
                                    Session::forget('success');
                                @endphp
                            </div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
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
                    <form action="" method="POST" id="form-filters" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Promotion Code</label>
                                    <input type="text" name="code" class="form-control" placeholder="Promotion Code">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Type</label>
                                    <select name="type" class="custom-select">
                                        <option value="">Any</option>
                                        <option value="Percentage">Percentage</option>
                                        <option value="Fixed Amount">Fixed Amount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select name="status" class="custom-select">
                                        <option value="">Any</option>
                                        <option value="active">Active</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block waves-effect waves-light font-weight-bold">
                                        <span class="align-middle"><i class="ri-search-line mr-2"></i></span>
                                        Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-12 col-lg-6">
                                                <a href="{{ url('admin/setup/payments/promotions/add') }}">
                                                    <button class="btn btn-outline-success px-3">
                                                        <i class="fa fa-plus mr-2" aria-hidden="true"></i> Create New
                                                        Promotions
                                                    </button>
                                                </a>
                                            </div>
                                            <div class="col-sm-12 col-lg-6 ">
                                                <div class="form-group row justify-content-end">
                                                    <div class="col-sm-12 col-lg-4 col-form-label text-right">
                                                        Sort by:
                                                    </div>
                                                    <div class="col-sm-12 col-lg-8">
                                                        <select name="filter" id="selectfilter" class="form-control">
                                                            <option value="0">Active Promotions</option>
                                                            <option value="expired">Expired Promotions</option>
                                                            <option value="all">All Promotions</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="config" class="table table-bordere dt-responsive w-100">
                                                        <thead>
                                                            <tr>
                                                                <th>Promotion Code</th>
                                                                <th>Type</th>
                                                                <th>Value</th>
                                                                <th>Recurring</th>
                                                                <th>Uses</th>
                                                                <th>Start Date</th>
                                                                <th>Expiry Date</th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                           
                                                        </tbody>
                                                    </table>
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
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script type="text/javascript">
            $(document).ready(function () {
    let dtTable;

    const initDataTable = function() {
        if (!$.fn.DataTable.isDataTable('#config')) {
            dtTable = $('#config').DataTable({
                paging: true,
                processing: true,
                serverSide: true,
                ajax: {
    url: '{{ route("admin.pages.setup.payments.promotions.dtPromotions") }}', // Gunakan route name yang benar
    type: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    data: function(data) {
        data.filter = $('#selectfilter').val();
        data.dataFiltered = $('#form-filters').serialize();
    },
    error: function (xhr, error, thrown) {
        console.error('DataTables error:', error);
        alert('Error loading data. Please check the console for details.');
    }
},
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                    searching: false,
                },
                columns: [
                    {data: 'code', name: 'code'},
                    {data: 'type', name: 'type'},
                    {data: 'value', name: 'value'},
                    {data: 'recurring', name: 'recurring'},
                    {data: 'uses', name: 'uses'},
                    {data: 'startdate', name: 'startdate'},
                    {data: 'expirationdate', name: 'expirationdate'},
                    {
                        data: 'id', 
                        name: 'id',
                        orderable: false, 
                        searchable: false,
                        width: '80px',
                        render: (data, type, row) => {
                            return '<a href="{{ url(Request::segment(1).'/setup/payments/promotions/duplicate')}}/'+data+'" " ><i class="fas fa-plus-square"></i>Duplicate</a>';
                        }
                    },
                    {
                        data: 'id', 
                        name: 'expire',
                        orderable: false, 
                        searchable: false,
                        width: '130px',
                        render: (data, type, row) => {
                            return `
                                <form action="{{ url(Request::segment(1).'/setup/payments/promotions/expired') }}" method="POST">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="id" value="${data}">
                                    <button type="submit" class="expired btn btn-success btn-xs"><i class="fas fa-stopwatch"></i> Expire Now</button>
                                </form>`;
                        }
                    },
                    {
                        data: 'id', 
                        name: 'id',
                        orderable: false, 
                        searchable: false,
                        width: '80px',
                        render: (data, type, row) => {
                            return `
                                <form id="fd${data}" action="{{ url(Request::segment(1).'/setup/payments/promotions/destroy') }}" method="POST">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="id" value="${data}">
                                    <a title="Edit ${row.type}" href="{{ url(Request::segment(1).'/setup/payments/promotions/edit') }}/${data}" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                                    <button type="button" data-id="${data}" data-title="${row.name}" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                                </form>`;
                        }
                    },
                ],
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
                order: [[9, "desc"]]
            });
        }
    };

    // Initialize DataTable
    initDataTable();

    // Handle filter form submission
    // Handle filter form submission
$('#form-filters').on('submit', function(e) {
    e.preventDefault();
    if (dtTable) {
        dtTable.destroy();
    }
    initDataTable();
    return false;
});

    // Handle select filter change
    $('#selectfilter').change(function() {
        if (dtTable) {
            dtTable.destroy();
        }
        initDataTable();
        return false;
    });

    // Handle delete button click
    $('#config').on('click', '.delete', function() {
        Swal.fire({
            title: "Warning..!",
            text: "Do you want to delete Promotions/Coupons "+$(this).data('id')+" ?",
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: '#d33',
            buttons: true,
            dangerMode: true,
        })
        .then((value) => {
            if(value.isConfirmed){
                $('#fd'+$(this).data('id')).submit();
            }else{
                return false;
            }
        });
        return false;
    });
});
    </script>   
@endsection
