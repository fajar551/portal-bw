@extends('layouts.clientbase')

@section('title')
    <title>Lelang Domain</title>
@endsection

@section('content')
    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 col-md-8">
                    <h2 class="mb-0">Lelang Domain</h2>
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

                <div class="col-md-12 d-flex justify-content-between align-items-center mt-2 mb-3">
                    <!-- My Auction Button -->
                    <a class="btn btn-primary mb-2 filter-container mb-md-0"
                        href="{{ route('pages.domain.lelangdomains.index', ['page' => 'my_auction']) }}">
                        My Auction
                    </a>
                    <div class="d-flex flex-wrap filter-container align-items-center">
                        <label class="mb-0 me-2 mr-2">Filter:</label>
                        <!-- Waktu Filter -->
                        <select class="form-control  me-3 mr-2 mb-2 mb-md-0" id="change_waktu" onchange="handlerChangeWaktu(event)"
                            style="width: 180px;">
                            <option value="waktu-terdekat" {{ $waktu == 'waktu-terdekat' ? 'selected' : '' }}>
                                Waktu Terdekat
                            </option>
                            <option value="waktu-terlama" {{ $waktu == 'waktu-terlama' ? 'selected' : '' }}>
                                Waktu Terlama
                            </option>
                        </select>
                        <!-- Tipe Filter -->
                        <select class="form-control me-3 mr-2 mb-2 mb-md-0" id="change_tipe" onchange="handlerChangeTipe(event)"
                            style="width: 180px;">
                            <option value="all" {{ $tipe == 'all' ? 'selected' : '' }}>
                                All
                            </option>
                            <option value="client" {{ $tipe == 'client' ? 'selected' : '' }}>
                                Client
                            </option>
                            <option value="backorder" {{ $tipe == 'backorder' ? 'selected' : '' }}>
                                Backorder
                            </option>
                        </select>
                        <!-- Settings Icon -->
                        <a href="{{ route('pages.domain.lelangdomains.index', ['page' => 'setting']) }}"
                            class="text-decoration-none">
                            <i class="fas fa-cog fa-2x"></i>
                        </a>
                    </div>
                </div>

                <script>
                    $(document).ready(function() {
                        $('#change_waktu').change(function() {
                            const waktu = $(this).val();
                            const tipe = $('#change_tipe').val();
                            window.location.href =
                                `{{ route('pages.domain.lelangdomains.index') }}?waktu=${waktu}&tipe=${tipe}`;
                        });

                        $('#change_tipe').change(function() {
                            const tipe = $(this).val();
                            const waktu = $('#change_waktu').val();
                            window.location.href =
                                `{{ route('pages.domain.lelangdomains.index') }}?waktu=${waktu}&tipe=${tipe}`;
                        });
                    });
                </script>

                <div class="col-md-12 mt-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Penjualan Hari Ini</h5>
                            <form id="cf-form" action="" method="post">
                                @csrf
                                <table class="table table-hover table-striped wrap" id="history">
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
                                        @foreach ($public_domain as $domain)
                                            <tr>
                                                <td>{{ $domain->domain }}</td>
                                                <td>
                                                    @if ($domain->last_price)
                                                        Rp {{ number_format($domain->last_price, 0, '.', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($domain->status == 'OPEN_LELANG')
                                                        {{ $domain->close_date }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $domain->status }}</td>
                                                <td>
                                                    @if ($domain->status == 'OPEN_LELANG')
                                                        {{-- @if ($domain->allowed) --}}
                                                        @if ($domain->domain === null)
                                                            <div class="text-center">
                                                                <a href="" class="btn btn-primary"
                                                                    style="background:#e43331fa;border:#e43331fa;">Lihat
                                                                    Detail</a>
                                                            </div>
                                                        @else
                                                            <div class="text-center">
                                                                @if ($domain->owner !== null)
                                                                    <a href="{{ route('pages.domain.lelangdomains.index', ['page' => 'buy_domain_lelang', 'domain' => $domain->domain]) }}"
                                                                        onclick="showModal(event,this)"
                                                                        class="btn btn-success">Ikut Lelang</a>
                                                                    {{-- <a data-order="" onclick="openModal(this,'Domain Lelang {{ $domain->domain }}', {{ $domain->last_price }})" class="btn btn-success" style="display:none">Ikut Lelang</a> --}}
                                                                @else
                                                                    <a href="{{ route('cart', ['a' => 'add', 'pid' => 382]) }}&cf_domain={{ $domain->domain }}"
                                                                        class="btn btn-info">Ikut Lelang</a>
                                                                    {{-- <a data-order="" onclick="openModal(this,'Register Domain {{ $domain->domain }}', {{ $domain->price_domain }}, 'Biaya backorder domain', 150000)" class="btn btn-success" style="display:none">Ikut Lelang</a> --}}
                                                                @endif
                                                                <a href="{{ route('pages.domain.lelangdomains.index', ['page' => 'detail', 'domain' => $domain->domain]) }}"
                                                                    class="btn btn-primary"
                                                                    style="background:#e43331fa; border:#e43331fa;">Lihat
                                                                    Detail</a>
                                                        @endif
                                                    @endif

                                                    <div class="text-center">
                                                        @if ($domain->status == 'SELL_DOMAIN')
                                                            <button type="button" class="btn btn-primary"
                                                                data-toggle="modal" data-target="#modalBuy"
                                                                onclick="setDomain('{{ $domain->domain }}', {{ $domain->last_price ?? 0 }})">Buy
                                                                Now</button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                    {{-- <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-right">
                                                <button class="btn btn-success">Save Change</button>
                                            </th>
                                        </tr>
                                    </tfoot> --}}
                                </table>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

{{-- modal buyDomain --}}
<div class="modal fade" id="modalBuy" tabindex="-1" aria-labelledby="modalBuyLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header d-flex" style="justify-content: space-between">
                <div style="width:50%">
                    <h3 class="modal-title" id="modalBuyLabel">Pembelian Domain</h3>
                </div>
                <div style="width:50%">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body" id="textbuy">
                Apakah Anda ingin membeli domain ini? <span id="domainName"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="buyDomain()">Yes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
{{-- modal buyDomain --}}

{{-- modal winner lelang --}}
<div id="modal_confirm" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Konfirmasi</h4>
            </div>
            <div class="modal-body" id="_item">
                <div class="row">
                    <div class="col-6">juara</div>
                    <div class="col-6">juara</div>
                </div>
                <div class="row">
                    <div class="col-6">juara</div>
                    <div class="col-6">juara</div>
                </div>
                <div class="row" style="margin:1em">
                    <h4>Pilih metode pembayaran</h4>
                    <select class="form-control">
                        @foreach ($payment_methods as $data)
                            <option value="{{ $data['module'] }}">{{ $data['displayname'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="goCheckout()">Confirm</button>
            </div>
        </div>
    </div>
</div>
{{-- modal winner lelang --}}

@section('scripts')
    <!-- Load jQuery from CDN (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Load DataTables from CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#history').DataTable({
                "columnDefs": [{
                        "orderable": true,
                        "targets": [0, 1, 2, 3]
                    }, // Set kolom yang bisa diurutkan
                    {
                        "orderable": false,
                        "targets": [4]
                    } // Kolom aksi tidak bisa diurutkan
                ],
                "order": [] // Tidak mengatur kolom yang diurutkan secara success
            });
        });

        function showModal(e, el) {
            e.preventDefault();
            if (confirm("Apakah anda yakin akan ikut lelang?") == true) {
                location.href = el.href
                return true
            } else {
                return false
            }
        }

        function openModal(el, desc1, price1, desc2, price2) {
            window.current_link = $(el).data('order')

            $('#_item').empty()
            if (Boolean(desc1)) {
                $('#_item').append(`<div class="row">
                    <div class="col-md-2">1</div>
                    <div class="col-md-5">${desc1}</div>
                    <div class="col-md-5">Rp ${price1}</div>
                </div>`)
            }

            if (Boolean(desc2)) {
                $('#_item').append(`<div class="row">
                    <div class="col-md-2">2</div>
                    <div class="col-md-5">${desc2}</div>
                    <div class="col-md-5">Rp ${price2}</div>
                </div>`)
            }

            $('#modal_confirm').modal('show')
        }

        function goCheckout() {
            if (Boolean(window.current_link)) {
                location.href = window.current_link
            }
        }

        function showModal(e, el) {
            e.preventDefault();
            if (confirm("Apakah anda yakin akan ikut lelang?")) {
                window.location.href = $(el).attr('href');
                return true;
            } else {
                return false;
            }
        }

        let currentDomain = '';
        let currentDomainLastPrice = 0;

        function setDomain(domain, lastPrice) {
            currentDomain = domain;
            currentDomainLastPrice = lastPrice;
            document.getElementById('domainName').innerText = domain;
        }

        function buyDomain() {
            console.log(currentDomain, currentDomainLastPrice);
            if (!currentDomain) {
                alert('Domain not found');
                return;
            }
            let buyUrl =
                `{{ route('pages.domain.lelangdomains.index', ['page' => 'buy_domain', 'domain' => '']) }}${currentDomain}&last_price=${currentDomainLastPrice}`;
            buyUrl = buyUrl.replace(/&amp;/g, '&');
            window.location.href = buyUrl;
        }
        // function buyDomain() {
        //     if (!currentDomain) {
        //         alert('Domain not found');
        //         return;
        //     }
        //     const buyUrl = `{{ route('pages.domain.lelangdomains.index', ['page' => 'buy_domain', 'domain' => '']) }}${encodeURIComponent(currentDomain)}`;
        //     window.location.href = buyUrl; 
        // }
    </script>
@endsection
