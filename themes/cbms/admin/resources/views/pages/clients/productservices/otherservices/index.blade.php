@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Other Services</title>
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
                                    <h4>Other Services</h4>
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                    <div class="card mb-1 shadow-none">
                                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse"
                                                            aria-expanded="true" aria-controls="collapseOne">
                                                            <div class="card-header" id="headingOne">
                                                                <h6 class="m-0">
                                                                    Search & Filter
                                                                    <i
                                                                        class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                </h6>
                                                            </div>
                                                        </a>
                                                        <div id="collapseOne" class="collapse hide"
                                                            aria-labelledby="headingOne" data-parent="#accordion">
                                                            <div class="card-body p-0 mt-3">
                                                                <div class="row">
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group row">
                                                                            <label for="product-type"
                                                                                class="col-sm-4 col-form-label">Product
                                                                                Type</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control"
                                                                                    name="product-type" id="product-type">
                                                                                    <option></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="product-service"
                                                                                class="col-sm-4 col-form-label">Product/Service</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control"
                                                                                    name="product-service"
                                                                                    id="product-service">
                                                                                    <option></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="billing-cycle"
                                                                                class="col-sm-4 col-form-label">Billing
                                                                                Cycle</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control"
                                                                                    name="billing-cycle" id="billing-cycle">
                                                                                    <option></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="domain-name"
                                                                                class="col-sm-4 col-form-label">Domain</label>
                                                                            <div class="col-sm-8">
                                                                                <input class="form-control"
                                                                                    name="domain-name" id="domain-name" />
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="client-name"
                                                                                class="col-sm-4 col-form-label">Client
                                                                                Name</label>
                                                                            <div class="col-sm-8">
                                                                                <input class="form-control"
                                                                                    name="client-name" id="client-name" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group row">
                                                                            <label for="server-name"
                                                                                class="col-sm-4 col-form-label">Server</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control"
                                                                                    name="server-name" id="server-name">
                                                                                    <option></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="payment-method"
                                                                                class="col-sm-4 col-form-label">Payment
                                                                                Methods</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control"
                                                                                    name="payment-method"
                                                                                    id="payment-method">
                                                                                    <option></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="stats-domain"
                                                                                class="col-sm-4 col-form-label">Status</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control"
                                                                                    name="stats-domain" id="stats-domain">
                                                                                    <option></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="custom-field"
                                                                                class="col-sm-4 col-form-label">Custom
                                                                                Field</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control"
                                                                                    name="custom-field" id="custom-field">
                                                                                    <option></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="custom-field-value"
                                                                                class="col-sm-4 col-form-label">Custom
                                                                                Field Value</label>
                                                                            <div class="col-sm-8">
                                                                                <input class="form-control"
                                                                                    name="custom-field-value"
                                                                                    id="custom-field-value" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-12">
                                                                        <button
                                                                            class="btn btn-primary px-5 d-flex align-items-center ml-auto"><i
                                                                                class="ri-search-line mr-2"></i>
                                                                            Search</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="datatable" class="table dt-responsive w-100">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th>
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input"
                                                                            id="ordercheck1">
                                                                        <label class="custom-control-label"
                                                                            for="ordercheck1">&nbsp;</label>
                                                                    </div>
                                                                </th>
                                                                <th>ID</th>
                                                                <th>Addons</th>
                                                                <th>Product Service</th>
                                                                <th>Client Name</th>
                                                                <th>Billing Cycle</th>
                                                                <th>Price</th>
                                                                <th>Next Due Date</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                                <div class="form-group row mt-3">
                                                    <label for="custom-field" class="col-sm-2 col-form-label">With
                                                        Selected: </label>
                                                    <div class="col-sm-10">
                                                        <button class="btn btn-light px-5">Send Message</button>
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
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
@endsection
