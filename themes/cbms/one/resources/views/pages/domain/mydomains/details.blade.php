@extends('layouts.clientbase')

@section('title')
    Domain Contact Info
@endsection

@section('page-title')
    {{ Lang::get('client.domaincontactinfo') }}
@endsection

@section('content')
    <style>
        .card-header {
            background-color: rgb(255 255 255);
        }

        .text-prime {
            color: #ffb444;
            /* Example color */
        }

        .list-group-item {
            padding: 0.274rem 0rem;
            border: 0px solid rgba(0, 0, 0, 0.125);
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-xl-8 col-lg-8">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <a
                                href="{{ route('pages.domain.mydomains.index') }}"> / My Domains</a> <span class="text-muted">
                                / Atur Domain</span></h6>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="container my-5">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <span class="icon"><i class="fas fa-globe text-prime fa-2x"></i></span>
                            </div>
                            @php
                                use Carbon\Carbon;
                            @endphp
                            <div class="card-body">
                                <h3 class="text-prime"><strong>{{ $domain_data['domain'] }}</strong></h3>
                                <p>Tanggal Pendaftaran : </p>
                                <h4><strong>{{ Carbon::parse($domain_data['registrationdate'])->format('d/m/Y') }}</strong>
                                </h4>
                                <p>Jatuh Tempo berikutnya :</p>
                                <h4><strong>{{ Carbon::parse($domain_data['nextduedate'])->format('d/m/Y') }}</strong></h4>
                                <p>Masa Kontrak :</p>
                                <h4><strong>{{ $domain_data['registrationperiod'] }} Tahun</strong></h4>
                                <p>Total Perpanjangan :</p>
                                <h4><strong>{{ 'Rp.' . number_format($domain_data['recurringamount'], 2, ',', '.') }}</strong>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <span class="icon"><i class="fas fa-cog text-prime fa-2x"></i></span>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="#" class="action-button list-group-item" style="color: inherit;"><img
                                            class="inline-text" style="width: 17px; height: 17px;"
                                            src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/edit.png">
                                        Ubah Nameserver</a>
                                    <a href="#" class="action-button list-group-item" style="color: inherit;"><img
                                            class="inline-text" style="width: 17px; height: 17px;"
                                            src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/file-plus.png">
                                        Tambah Child Nameserver</a>
                                    <a href="#" class="action-button list-group-item" style="color: inherit;"><img
                                            class="inline-text" style="width: 17px; height: 17px;"
                                            src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/edit-2.png">
                                        Ubah Informasi Data pemilik/WHOIS domain</a>
                                    <a href="#" class="action-button list-group-item" style="color: inherit;"><img
                                            class="inline-text" style="width: 17px; height: 17px;"
                                            src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/unlock.png">
                                        Ubah Status Lock Domain</a>
                                    <a href="#" class="action-button list-group-item" style="color: inherit;"><img
                                            class="inline-text" style="width: 17px; height: 17px;"
                                            src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/trending-up.png">
                                        Perpanjang Domain</a>
                                    <a href="#" class="action-button list-group-item" style="color: inherit;"><img
                                            class="inline-text" style="width: 17px; height: 17px;"
                                            src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/key.png">
                                        Get EPP Code</a>
                                    <a href="{{ route('pages.domain.mydomains.details.document', array_merge(request()->query(), ['module' => 'PrivateNsRegistrar', 'page' => 'upload'])) }}"
                                        class="action-button list-group-item" style="color: inherit;"><img
                                            class="inline-text" style="width: 17px; height: 17px;"
                                            src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/upload-cloud.png">
                                        Upload Syarat Domain
                                    </a>
                                   <a 
                                    href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domain_data['id'], 'module' => 'ForwardDomain', 'page' => 'dns']) }}" 
                                    class="action-button list-group-item" style="color: inherit;"><img class="inline-text" style="width: 17px; height: 17px;" 
                                    src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/globe.png"> Domain DNS Manager
                                    </a>
                                    <a
                                        href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domain_data['id'], 'module' => 'ForwardDomain', 'page' => 'domain_fwd']) }}" 
                                        class="action-button list-group-item" style="color: inherit;"><img class="inline-text" style="width: 17px; height: 17px;" 
                                        src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/link-2.png"> Domain Forwarder / URL Masking
                                    </a>
                                    <a href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domain_data['id'], 'module' => 'ForwardDomain', 'page' => 'email_fwd']) }}" 
                                        class="action-button list-group-item" style="color: inherit;"><img class="inline-text" style="width: 17px; height: 17px;" 
                                        src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/send.png"> Domain Email Forwarder
                                    </a>
                                    <a href="#" class="action-button list-group-item" style="color: inherit;"><img
                                            class="inline-text" style="width: 17px; height: 17px;"
                                            src="https://portal.qwords.com/templates/qwordsv2/img/list manage domain/shield.png">
                                        DNSSEC</a>
                                </div>
                                <div class="text-center">
                                    <button class="btn btn-outline-warning" data-toggle="modal"
                                        data-target="#moveServiceModal">Move Service</button>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="moveServiceModal" tabindex="-1" role="dialog" aria-labelledby="moveServiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="moveServiceModalLabel">Move Service</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Your modal content here -->
                    Dengan melakukan Move Service anda akan melepas hak anda atas layanan ini (serta Domain/Hosting dengan
                    nama domain utama yang sama di akun ini) dan memberikannya ke akun baru yang dituju,
                    Anda telah memahami dan menyetujui dengan mengisi akun baru dan mengklik Submit
                    <hr>
                    <p><strong>Masukan Email dari Akun yang akan menerima layanan ini: </strong></p>
                    <input type="email" class="form-control" name="domainidUpdate" placeholder="name@example.com">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>
@endsection
