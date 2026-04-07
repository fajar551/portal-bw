@extends('layouts.clientbase')

@section('title')
    Offer For Me
@endsection

@section('page-title')
    Offer For Me
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row pb-3">
                <div class="col-xl-12 col-lg-12">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <span
                                class="text-muted"> / Offer for me</span></h6>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-6 col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase font-size-12 text-muted mb-3">Received</h6>
                                    <span class="h3 mb-0 text-success"> 1 </span>
                                </div>
                                <div class="col-auto ic-card">
                                    <i class="feather-check text-success opacity-1"></i>
                                </div>
                            </div> <!-- end row -->

                            <div id="sparkline1" class="mt-3"></div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->

                <div class="col-xl-6 col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase font-size-12 text-muted mb-3">Sent</h6>
                                    <span class="h3 mb-0 text-info"> 0 </span>
                                </div>
                                <div class="col-auto ic-card">
                                    <i class="feather-send text-info opacity-1"></i>
                                </div>
                            </div> <!-- end row -->

                            <div id="sparkline1" class="mt-3"></div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->

            </div>
            <!-- end row-->

            <div class="row">

                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-3">List of offers for me</h4>
                            <div class="table-responsive">
                                <table id="basic-datatable" class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Quotes</th>
                                            <th>Title</th>
                                            <th>Date Created</th>
                                            <th>Valid Until</th>
                                            <th>Step</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Penawaran untuk upgrade akun</td>
                                            <td>Upgrade akun Goldenfast</td>
                                            <td>12 June 2021</td>
                                            <td>20 June 2022</td>
                                            <td>-</td>
                                            <td>
                                                <a href="#" class="btn btn-primary btn-sm">Detail</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row-->

        </div> <!-- container-fluid -->
    </div>
@endsection
