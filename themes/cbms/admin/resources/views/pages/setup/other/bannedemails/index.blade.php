@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Banned Email Domains</title>
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
                                        <h4 class="mb-3">Banned Email Domains</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <nav>
                                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                        <a class="nav-link nav-item active" id="nav-home-tab"
                                                            data-toggle="tab" href="#nav-home" role="tab"
                                                            aria-controls="nav-home" aria-selected="true">Add Email</a>

                                                    </div>
                                                </nav>
                                                <div class="tab-content" id="nav-tabContent">
                                                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                                                        aria-labelledby="nav-home-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row">
                                                                    <label for=""
                                                                        class="col-sm-12 col-lg-2 col-form-label">Email
                                                                        Address</label>
                                                                    <div class="col-sm-12 col-lg-5">
                                                                        <input type="text" class="form-control">
                                                                    </div>
                                                                    <div class="col-sm-12 col-lg-4 pt-2">
                                                                        <p>(Only enter the domain - eg. hotmail.com)</p>
                                                                    </div>
                                                                </div>
                                                                <div class="text-center">
                                                                    <button class="btn btn-success px-3">Add Banned
                                                                        Email</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Email Domain</th>
                                                                <th>Usage Count</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="3">No Data</td>
                                                            </tr>
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
