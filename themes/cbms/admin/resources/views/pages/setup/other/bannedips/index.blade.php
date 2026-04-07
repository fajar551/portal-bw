@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Banned IPs</title>
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
                                        <h4 class="mb-3">Banned IPs</h4>
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
                                                        <a class="nav-link nav-item active" id="nav-add-ip-tab"
                                                            data-toggle="tab" href="#nav-add-ip" role="tab"
                                                            aria-controls="nav-add-ip" aria-selected="true">Add IP</a>
                                                        <a class="nav-link nav-item" id="nav-search-tab" data-toggle="tab"
                                                            href="#nav-search" role="tab" aria-controls="nav-search"
                                                            aria-selected="false">Search/Filter</a>
                                                    </div>
                                                </nav>
                                                <div class="tab-content" id="nav-tabContent">
                                                    <div class="tab-pane fade show active" id="nav-add-ip" role="tabpanel"
                                                        aria-labelledby="nav-add-ip-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row">
                                                                    <label for=""
                                                                        class="col-sm-12 col-lg-2 col-form-label">IP
                                                                        Address</label>
                                                                    <div class="col-sm-12 col-lg-2">
                                                                        <input type="number" class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for=""
                                                                        class="col-sm-12 col-lg-2 col-form-label">Ban
                                                                        Reason</label>
                                                                    <div class="col-sm-12 col-lg-10">
                                                                        <input type="text" class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for=""
                                                                        class="col-sm-12 col-lg-2 col-form-label">Ban
                                                                        Expires</label>
                                                                    <div class="col-sm-12 col-lg-3">
                                                                        <div
                                                                            class="d-inline-flex align-items-center font-size-22">
                                                                            <input type="text"
                                                                                class="form-control d-inline mr-2 mb-2">/
                                                                            <input type="text"
                                                                                class="form-control d-inline mx-2 mb-2">/
                                                                            <input type="text"
                                                                                class="form-control d-inline mx-2 mb-2">
                                                                        </div>
                                                                    </div>
                                                                    <div
                                                                        class="col-sm-12 col-lg-2 align-items-center font-size-22">
                                                                        <div class="d-inline-flex">
                                                                            <input type="text"
                                                                                class="form-control d-inline mr-2 mb-2">:
                                                                            <input type="text"
                                                                                class="form-control d-inline mx-2 mb-2">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                                        <p>(Format: DD/MM/YYYY HH:MM)</p>
                                                                    </div>
                                                                </div>
                                                                <div class="text-center">
                                                                    <button class="btn btn-success px-3">Add Banned
                                                                        IP</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="nav-search" role="tabpanel"
                                                        aria-labelledby="nav-search-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-5 col-sm-12">
                                                                <div class="form-group row">
                                                                    <label for=""
                                                                        class="col-sm-12 col-lg-3 col-form-label">Filter
                                                                        For</label>
                                                                    <div class="col-sm-12 col-lg-9">
                                                                        <select name="" id="" class="form-control">
                                                                            <option value="0">IP Address</option>
                                                                            <option value="1">Ban Reason</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-5 col-sm-12">
                                                                <div class="form-group row">
                                                                    <label for=""
                                                                        class="col-sm-12 col-lg-3 col-form-label text-center">matching</label>
                                                                    <div class="col-sm-12 col-lg-9">
                                                                        <input type="text" class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-2 col-sm-12">
                                                                <button class="btn btn-success px-3">Search</button>
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
                                                                <th>IP Address</th>
                                                                <th>Ban Reason</th>
                                                                <th>Ban Expires</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <td colspan="3">No Data</td>
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
