@extends('layouts.clientbase')

@section('title')
    <title>Detail Lelang Domain</title>
@endsection

@section('content')
    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
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

                <div class="col-md-12 mt-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">

                                <h1 class="mt-3 text-primary font-weight-bold mb-4">
                                    {{ $detail->domain }}
                                </h1>

                                <div>Waktu tersisa:</div>
                                <div class="my-4 text-center">
                                    <table class="table table-borderless w-auto mx-auto" style="font-size: 2em;">
                                        <tbody>
                                            <tr>
                                                <td id="days" class="text-warning font-weight-bold">0</td>
                                                <td id="hours" class="text-warning font-weight-bold">0</td>
                                                <td id="minutes" class="text-warning font-weight-bold">0</td>
                                                <td id="seconds" class="text-warning font-weight-bold">0</td>
                                            </tr>
                                            <tr class="text-warning small">
                                                <td class="font-weight-bold">Hari</td>
                                                <td class="font-weight-bold">Jam</td>
                                                <td class="font-weight-bold">Menit</td>
                                                <td class="font-weight-bold">Detik</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="font-weight-bold">Perpanjangan Waktu ke: {{ $detail->maxtry }}</div>

                                <div class="row justify-content-center mt-4">
                                    <div class="col-md-4">
                                        <table class="table table-striped table-bordered text-left">
                                            <tbody>
                                                <tr>
                                                    <th class="text-warning">Waktu Mulai</th>
                                                    <td>:
                                                        {{ \Carbon\Carbon::parse($detail->open_date)->translatedFormat('d F Y') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-warning">Waktu Berakhir</th>
                                                    <td>:
                                                        {{ \Carbon\Carbon::parse($detail->close_date)->translatedFormat('d F Y') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-warning">Total Bid Terakhir</th>
                                                    <td>: <span
                                                            id="last_price">Rp&nbsp;{{ number_format($detail->last_price + $total_hargadomain, 0, ',', '.') }}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row justify-content-center mt-4">
                                    @if ($detail->close_date && \Carbon\Carbon::now()->isBefore($detail->close_date))
                                        <form action="{{ route('pages.domain.lelangdomains.action') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="action" value="bid">
                                            <input type="hidden" name="domain" value="{{ $detail->domain }}" />
                                            <input type="hidden" name="bid_token" value="{{ $bid_token }}" />
                                            <input type="hidden" name="last_price_form"
                                                value="{{ $detail->last_price }}" />
                                            <input type="hidden" min="{{ $min_bid_value }}" name="mbv" />
                                            <input type="hidden" id="anon_email" name="anon_email" value="0" />

                                            <div class="text-center">
                                                <div class="form-group row justify-content-center mb-3">
                                                    <label for="bid_value" class="col-form-label mr-1">Rp.</label>
                                                    <div class="col-auto">
                                                        <input type="number" min="50" name="bid_value" id="bid_value"
                                                            class="form-control" />
                                                    </div>
                                                    <div class="col-auto">
                                                        <span class="form-control" style="width: 70px;" disabled>000</span>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <button type="submit" class="btn btn-primary mx-1"
                                                        id="bid_button">Bid</button>
                                                    <button type="submit" class="btn btn-secondary mx-1"
                                                        id="anon_bid_button">Anon Bid</button>
                                                </div>

                                                <p class="mb-1">Anda menambah: Rp <span id="bid_value_display">0</span>
                                                </p>
                                                <p class="mb-1">Harga terakhir setelah ditambah bid: Rp <span
                                                        id="total_bid_display">0</span></p>
                                                <input type="hidden" id="total_bid" name="total_bid" value="0" />
                                                <p><small>*Harga yang sudah di bid tidak bisa di ubah</small></p>
                                            </div>
                                        </form>
                                    @else
                                        <div class="alert alert-warning text-center" role="alert">
                                            Waktu lelang sudah berakhir. Tidak ada lagi bid yang bisa dilakukan.
                                        </div>
                                    @endif
                                </div>

                                @if ($detail->domain)
                                    <button class="btn btn-primary"
                                        onclick="window.location.href='{{ route('pages.domain.lelangdomains.index', ['page' => 'history', 'domain' => $detail->domain]) }}'">
                                        History Bid
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-link"
                            onclick="window.location.href='{{ route('pages.domain.lelangdomains.index') }}'">
                            <i class="fas fa-arrow-left"></i> Back To Auction
                        </button>

                        <h5 class="mb-0">
                            <button class="btn btn-info"
                                onclick="window.location.href='{{ route('pages.domain.lelangdomains.index', ['page' => 'list', 'domain' => $detail->domain]) }}'">
                                Go To List {{ $detail->domain }} Page
                            </button>
                        </h5>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Modal for Auction Ended -->
    <div class="modal fade" id="auctionEndedModal" tabindex="-1" role="dialog" aria-labelledby="auctionEndedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="auctionEndedModalLabel">Auction Ended</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Waktu lelang sudah berakhir!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Function to start the countdown
            @if ($detail->close_date)
                let countdownEnd = new Date("{{ $detail->close_date }}").getTime();

                function startCountDown() {
                    let interval = setInterval(function() {
                        let now = new Date().getTime();
                        let distance = countdownEnd - now;

                        // Calculate time components
                        let days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        // Display the countdown
                        $('#days').text(days);
                        $('#hours').text(hours);
                        $('#minutes').text(minutes);
                        $('#seconds').text(seconds);

                        // Stop the countdown when time is up
                        if (distance < 0) {
                            clearInterval(interval);
                            $('#auctionEndedModal').modal('show'); // Show the modal
                            $('#days').text("0");
                            $('#hours').text("0");
                            $('#minutes').text("0");
                            $('#seconds').text("0");
                        }
                    }, 1000);
                }

                startCountDown();
            @else
                $('#auctionEndedModal').modal('show'); // Show the modal
            @endif

            // Function to update the bid value display
            $('#bid_value').on('input', function() {
                let bidValue = parseInt($(this).val()) || 0; // If the value is empty or NaN, set to 0
                let lastPrice = parseInt($('#last_price').text().replace(/[^0-9]/g, ''));
                let totalBid = (bidValue * 1000) + lastPrice;

                // Update the displayed bid values
                $('#bid_value_display').text(bidValue * 1000);
                $('#total_bid_display').text(totalBid.toLocaleString('id'));
                $('#total_bid').val(totalBid);
            });

            // Function for bid confirmation
            $('#bid_button').on('click', function(e) {
                e.preventDefault();
                let totalBid = $('#bid_value').val();
                let minBidValue = parseInt($('input[name="mbv"]').attr('min'));

                if (confirm("Apakah anda yakin?")) {
                    $('#anon_email').val(0); // Set to normal bid
                    if (parseInt(totalBid) < minBidValue) {
                        alert('Bid minimal ' + minBidValue + '.000');
                    } else {
                        $(this).closest('form').submit();
                    }
                }
            });

            // Function for anonymous bid confirmation
            $('#anon_bid_button').on('click', function(e) {
                e.preventDefault();
                let totalBid = $('#bid_value').val();
                let minBidValue = parseInt($('input[name="mbv"]').attr('min'));

                if (confirm("Apakah anda yakin?")) {
                    $('#anon_email').val(1); // Set to anonymous bid
                    if (parseInt(totalBid) < minBidValue) {
                        alert('Bid minimal ' + minBidValue + '.000');
                    } else {
                        $(this).closest('form').submit();
                    }
                }
            });
        });
    </script>
@endsection
