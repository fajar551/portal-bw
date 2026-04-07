@extends('layouts.clientbase')

@section('title')
    <title>Detail Lelang Domain</title>
@endsection

@section('content')
    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 col-md-8">
                    <h2 class="mb-0">My Lelang Domain</h2>
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
                <div class="col-md-12 d-flex justify-content-end">
                    {{-- <a class="btn btn-sm btn-primary" href="{{ route('pages.domain.lelangdomains.index', ['page' => 'my_auction']) }}"> My Auction </a> --}}
                    {{-- <a class="btn btn-sm btn-primary"
                        href="{{ route('pages.domain.lelangdomains.index', ['page' => 'setting']) }}">Setting 
                    </a> --}}
                </div>

                <div class="col-md-12 mt-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Daftar Lelang Saya</h5>
                            <table class="table table-hover table-striped wrap" id="my_auction_table">
                                <thead>
                                    <tr>
                                        <th>Domain</th>
                                        <th>Harga Terakhir</th>
                                        <th>Tanggal Penutupan</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($list as $data)
                                        <tr>
                                            <td>{{ $data->domain }}</td>
                                            <td>Rp {{ number_format($data->price_last, 0, '.', '.') }}</td>
                                            <td>{{ $data->close_date }}</td>
                                            <td>
                                                @if ($data->status == 'CLOSE_LELANG' || $data->status == 'INVOICE_CREATED')
                                                    CLOSED
                                                @else
                                                    {{ $data->status }}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($data->status == 'OPEN_LELANG')
                                                    <a href="{{ route('pages.domain.lelangdomains.index', ['page' => 'detail', 'domain' => $data->domain]) }}"
                                                        target="_blank" class="btn btn-primary">Detail</a>
                                                @endif
                                                <a href="{{ route('pages.domain.lelangdomains.index', ['page' => 'history', 'domain' => $data->domain]) }}"
                                                    target="_blank" class="btn btn-secondary">History</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                {{-- <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">
                                            <button class="btn btn-success">Save Changes</button>
                                        </th>
                                    </tr>
                                </tfoot> --}}
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-link"
                        onclick="window.location.href='{{ route('pages.domain.lelangdomains.index') }}'">
                        <i class="fas fa-arrow-left"></i> Back To Auction
                    </button>                
                    {{-- <h5 class="mb-0">
                        <button class="btn btn-info"
                            onclick="window.location.href='{{ route('pages.domain.lelangdomains.index', ['page' => 'list', 'domain' => $detail->domain]) }}'">
                            Go To List {{ $detail->domain }} Page
                        </button>
                    </h5> --}}
                </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#my_auction_table').DataTable({
                "columnDefs": [{
                        "orderable": true,
                        "targets": [0, 1, 2, 3]
                    }, // Kolom yang bisa diurutkan
                    {
                        "orderable": false,
                        "targets": [4]
                    } // Kolom aksi tidak bisa diurutkan
                ],
                "order": [] // Tidak ada kolom yang otomatis diurutkan
            });
        });
    </script>
@endsection
