@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Order Statuses</title>
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
                                        <h4 class="mb-3">Order Statuses</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p>Here you can define the order statuses you wish to use. The 4 default statuses
                                            Pending, Active, Fraud and Cancelled cannot be deleted or renamed.
                                        </p>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button class="btn btn-outline-success px-3"><i
                                                        class="fa fa-plus-square mr-2" aria-hidden="true"></i> Add
                                                    New</button>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Title</th>
                                                                <th>Include in Pending</th>
                                                                <th>Include in Active</th>
                                                                <th>Include in Cancelled</th>
                                                                <th>Sort Order</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Pending</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Active</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Cancelled</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Fraud</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-lg-12">
                                                <h4 class="card-title mb-3">Add Ticket Status</h4>
                                                <div class="form-group row">
                                                    <div class="col-sm-12 col-lg-2 col-form-label">
                                                        Title
                                                    </div>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-sm-12 col-lg-2 col-form-label">
                                                        Status Color
                                                    </div>
                                                    <div class="col-sm-12 col-lg-2">
                                                        <div class="input-group colorpicker-default"
                                                            title="Using format option">
                                                            <input type="text" class="form-control input-lg"
                                                                value="#4667cc" />
                                                            <span class="input-group-append">
                                                                <span
                                                                    class="input-group-text colorpicker-input-addon"><i></i></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Include in
                                                        Pending</label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="includeActive">
                                                            <label class="custom-control-label" for="includeActive"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Include in
                                                        Active</label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="includeAwaitingReply">
                                                            <label class="custom-control-label"
                                                                for="includeAwaitingReply"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Include In
                                                        Cancelled</label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="autoClose">
                                                            <label class="custom-control-label" for="autoClose"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Sort
                                                        Order</label>
                                                    <div class="col-sm-12 col-lg-3">
                                                        <input type="text" class="form-control" value="10">
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-lg-12 text-center">
                                                <button class="btn btn-success px-3">Save Changes</button>
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
    <script src="{{ Theme::asset('assets/js/colorpicker.js') }}"></script>
@endsection
