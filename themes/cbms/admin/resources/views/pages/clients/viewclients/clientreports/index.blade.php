@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Report</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <!-- End Sidebar -->
                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-4">Reports</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-8 col-lg-10">
                                    <!-- START HERE -->
                                    <h4 class="font-weight-bold">Client Account Balance</h4>
                                </div>
                                <div class="col-4 col-lg-2 d-flex">
                                    <div class="dropdown ml-auto">
                                        <a class="btn btn-light dropdown-toggle btn-sm" href="#" role="button"
                                            id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false"> <i class="fa fa-cogs" aria-hidden="true"></i>
                                            Tools
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#">Export to CSV</a>
                                            <a class="dropdown-item" href="#">View Printable Version</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <p>This report provides a statement of account for individual client accounts.</p>
                                </div>
                            </div>
                            <div class="card p-3">
                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                    <div class="card mb-1 shadow-none">
                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse" aria-expanded="true"
                                            aria-controls="collapseOne">
                                            <div class="card-header" id="headingOne">
                                                <h6 class="m-0">
                                                    Search & Filter
                                                    <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                </h6>
                                            </div>
                                        </a>
                                        <div id="collapseOne" class="" aria-labelledby="headingOne"
                                            data-parent="#accordion">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="client">Client</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="Client/Company Name" required="">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="daterange">Date Range</label>
                                                            <input type="date" class="form-control" placeholder="Optional">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group mt-4">
                                                            <button type="button"
                                                                class="mt-1 btn btn-primary btn-block waves-effect waves-light font-weight-bold">
                                                                <span class="align-middle"><i
                                                                        class="fas fa-save"></i></span>
                                                                Apply
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        <table id="datatable" class="table table-bordered dt-responsive w-100">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                    <th>Description</th>
                                                    <th>Credits</th>
                                                    <th>Debits</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-lg-12 text-lg-right">
                                    <h6 class="text-muted my-2">Report generated on 04/08/2021 14:05</h6>
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
@endsection
