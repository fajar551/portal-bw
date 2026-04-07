@extends('layouts.clientbase')

@section('title')
    Domain Contact Info
@endsection

@section('page-title')
    {{ Lang::get('client.domaincontactinfo') }}
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-xl-8 col-lg-8">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <a
                                href="{{ route('pages.domain.mydomains.index') }}"> / My Domains</a> <span class="text-muted">
                                / Tambah Childnameservers</span></h6>
                    </div>
                </div>
            </div>
            <div class="alert alert-primary">
                Dari sini Anda dapat membuat dan mengelola nameserver kustom untuk domain Anda (misalnya ns1.yourdomain.com,
                ns1.yourdomain.com ....)
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

            <!-- Daftar Nameserver Section -->
            <h4 class="mb-4 text-left">Daftar NameServer Name</h4>
            <form method="post" action="{{ route('pages.domain.mydomains.childnameservers.create') }}">
                @csrf
                <input type="text" class="form-control" id="domainidCreate" name="domainidCreate"
                    value="{{ $domain_data['id'] }}" hidden>
                <input type="text" class="form-control" id="useridCreate" name="useridCreate"
                    value="{{ $domain_data['userid'] }}" hidden>
                <input type="text" class="form-control" id="domainCreate" name="domainCreate"
                    value="{{ $domain_data['domain'] }}" hidden>
                <input type="text" class="form-control" id="registrarCreate" name="registrarCreate"
                    value="{{ $domain_data['registrar'] }}" hidden>

                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label for="nameserver" class="text-left">Nameserver</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('nameserver') is-invalid @enderror"
                                id="nameserver" name="nameserver" value="{{ old('nameserver') }}">
                            <div class="input-group-append">
                                <span class="input-group-text">.{{ $domain_data['domain'] }}</span>
                            </div>
                        </div>
                        @error('nameserver')
                            <div class="invalid-feedback d-block"> <!-- Ensure the feedback is displayed -->
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ipAddress" class="text-left">IP Address</label>
                        <input type="text" class="form-control @error('ipAddress') is-invalid @enderror" id="ipAddress"
                            name="ipAddress" value="{{ old('ipAddress') }}">
                        @error('ipAddress')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>

            <hr class="my-5">

            <!-- Modifikasi IP Nameserver Section -->
            <h4 class="mb-4 text-left">Modifikasi IP NameServer</h4>
            <form method="post" action="{{ route('pages.domain.mydomains.childnameservers.update') }}">
                @csrf
                <input type="text" class="form-control" id="domainidUpdate" name="domainidUpdate"
                    value="{{ $domain_data['id'] }}" hidden>
                <input type="text" class="form-control" id="useridUpdate" name="useridUpdate"
                    value="{{ $domain_data['userid'] }}" hidden>
                <input type="text" class="form-control" id="domainUpdate" name="domainUpdate"
                    value="{{ $domain_data['domain'] }}" hidden>
                <input type="text" class="form-control" id="registrarUpdate" name="registrarUpdate"
                    value="{{ $domain_data['registrar'] }}" hidden>
                <div class="form-row">
                    <div class="col-md-4">
                        <label for="currentNameserver" class="text-left">Nameserver</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('updateNameserver') is-invalid @enderror"
                                id="updateNameserver" name="updateNameserver" value="{{ old('updateNameserver') }}">
                            <div class="input-group-append">
                                <span class="input-group-text">.{{ $domain_data['domain'] }}</span>
                            </div>
                        </div>
                        @error('updateNameserver')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="currentIP" class="text-left">IP Address Saat ini</label>
                        <input type="text" class="form-control @error('currentIP') is-invalid @enderror"
                            id="currentIP" name="currentIP" value="{{ old('currentIP') }}">
                        @error('currentIP')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="newIP" class="text-left">IP Address Baru</label>
                        <input type="text" class="form-control @error('newIP') is-invalid @enderror" id="newIP"
                            name="newIP" value="{{ old('newIP') }}">
                        @error('newIP')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>

            <hr class="my-5">

            <!-- Hapus Nameserver Section -->
            <h4 class="mb-4 text-left">Hapus NameServer</h4>
            <form method="post" action="{{ route('pages.domain.mydomains.childnameservers.delete') }}">
                <input type="text" class="form-control" id="domainidDelete" name="domainidDelete"
                    value="{{ $domain_data['id'] }}" hidden>
                <input type="text" class="form-control" id="useridDelete" name="useridDelete"
                    value="{{ $domain_data['userid'] }}" hidden>
                <input type="text" class="form-control" id="domainDelete" name="domainDelete"
                    value="{{ $domain_data['domain'] }}" hidden>
                <input type="text" class="form-control" id="registrarDelete" name="registrarDelete"
                    value="{{ $domain_data['registrar'] }}" hidden>
                @csrf
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label for="nameserver" class="text-left">Nameserver</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('deleteNameserver') is-invalid @enderror"
                                id="deleteNameserver" name="deleteNameserver" value="{{ old('deleteNameserver') }}">
                            <div class="input-group-append">
                                <span class="input-group-text">.{{ $domain_data['domain'] }}</span>
                            </div>
                        </div>
                        @error('deleteNameserver')
                            <div class="invalid-feedback d-block"> <!-- Ensure the feedback is displayed -->
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Spacer using my-3 class -->
                <div class="form-row">
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
