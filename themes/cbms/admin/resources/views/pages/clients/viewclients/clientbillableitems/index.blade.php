@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Billable Items</title>
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
                                <div class="col-lg-6">
                                    <form>
                                        <div class="form-group">
                                            <select name="profile_name" id="select-prof-name" class="form-control">
                                                <option>Tiger Nixon - #1</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @include('includes.tabnavclient')
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="row">
                                                    <div class="col-lg-12 d-flex flex-row-reverse mb-2">
                                                        <a href="add-billable-item.html">
                                                            <button class="btn btn-success align-items-center d-flex"><i
                                                                    class="ri-add-fill mr-2"></i> Add
                                                                Billable Item</button>
                                                        </a>
                                                        <a href="#">
                                                            <button class="btn btn-outline-success mr-2">
                                                                Add Time Billing
                                                                Entries
                                                            </button>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h5>Uninvoiced Items - <span class="text-danger">Rp. 0.00</span> (0)
                                                </h5>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="selection-datatable" class="table dt-responsive w-100">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Description</th>
                                                                <th>Hours</th>
                                                                <th>Amount</th>
                                                                <th>Invoice Action</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                    <hr>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <form action="">
                                                    <div class="form-group row">
                                                        <label for="deleteSelected"
                                                            class="col-sm-2 col-form-label my-1">With
                                                            Selected:</label>
                                                        <div class="col-sm-3">
                                                            <input type="text" class="form-control my-1">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <button class="btn btn-danger btn-block my-1">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-lg-12">
                                                <h5>Invoiced Items</h5>
                                                <div class="table-responsive">
                                                    <table id="datatable" class="table dt-responsive w-100">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Description</th>
                                                                <th>Hours</th>
                                                                <th>Amount</th>
                                                                <th>Invoice Action</th>
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
                    <!-- <div class="view-client-wrapper d-flex align-items-center justify-content-center"
                                                            style="min-height: 80vh;">
                                                            
                                                        </div> -->
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
