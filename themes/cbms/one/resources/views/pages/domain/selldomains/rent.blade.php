@extends('layouts.clientbase')

@section('title')
    <title>Public Rent Domain Page</title>
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('modules/addons/sell_domain/assets/custom.css') }}">

@section('content')
    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 col-md-8">
                    <h3 class="mb-0">Public Rent Domain</h3>
                    <small class="text-muted">By CBMS</small>
                </div>
                {{-- Message alert --}}
                <div class="col-md-12">
                    @if (Session::get('alert-message'))
                        <div class="alert alert-{{ Session::get('alert-type') }}" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {!! nl2br(Session::get('alert-message')) !!}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <b>Error:</b>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                {{-- Message alert --}}

                <div class="col-md-12 mt-3">
                    <div class="card table-responsive">
                        <div class="card-body">
                            <h5 class="card-header">Public Rent Domain</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Domain</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Harga</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($domain_rents_all as $data)
                                        <tr>
                                            <td>{{ $data->domain }}</td>
                                            <td>
                                                @if ($data->status == 'NEED_VERIFY')
                                                    <span class="badge badge-warning">Unverified</span>
                                                @elseif($data->status == 'VERIFIED')
                                                    <span class="badge badge-success">Verified</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($data->price, 0, ',', '.') }}</td>
                                            <td>
                                                <form method="POST"
                                                    action="{{ route('pages.domain.selldomains.action', ['domain' => $data->domain, 'action' => 'rent']) }}"
                                                    style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Pesan</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('pages.domain.selldomains.index') }}" class="btn btn-secondary">Back to sell domain page</a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                columnDefs: [{
                        orderable: false,
                        targets: -1
                    } // Make the last column (Aksi) non-orderable
                ]
            });
        });
    </script>
@endsection
