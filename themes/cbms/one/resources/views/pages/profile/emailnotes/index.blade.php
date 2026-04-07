@extends('layouts.clientbase')

@section('title')
    Email Notes
@endsection

@section('page-title')
    Email Notes
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row pb-3">
                <div class="col-xl-12 col-lg-12">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="{{ url('home') }}">Dashboard</a> <span
                                class="text-muted"> / Email
                                Notes</span></h6>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-3">Email Notes</h4>
                            <table id="basic-datatable" class="table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Date Sent</th>
                                        <th>Message Subject</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>17/06/2021 (09:51)</td>
                                        <td>[Ticket ID: 200180] Konfirmasi pembuatan faktur pajak</td>
                                        <td>
                                            <div class="row">
                                                <a href="" class="btn btn-primary btn-sm">Detail</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>16/06/2021 (16:58)</td>
                                        <td>Invoice 26579 Information</td>
                                        <td>
                                            <div class="row">
                                                <a href="" class="btn btn-primary btn-sm">Detail</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>16/06/2021 (16:53)</td>
                                        <td>Tagihan Baru 26579</td>
                                        <td>
                                            <div class="row">
                                                <a href="" class="btn btn-primary btn-sm">Detail</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>16/06/2021 (16:53)</td>
                                        <td>**Order Received** :: Goldenfast Networks</td>
                                        <td>
                                            <div class="row">
                                                <a href="" class="btn btn-primary btn-sm">Detail</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>15/02/2021 (09:05)</td>
                                        <td>[SUKSES] Pendaftaran Akun client.goldenfast.net berhasil</td>
                                        <td>
                                            <div class="row">
                                                <a href="" class="btn btn-primary btn-sm">Detail</a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row-->
        </div> <!-- container-fluid -->
    </div>
@endsection
