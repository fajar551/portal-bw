@extends('layouts.clientbase')

@section('title')
    <title>Detail Lelang Domain</title>
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

                <div class="col-md-12">
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

                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <h4 class="mt-3 text-primary font-weight-bold mb-4 text-center">
                                            History Bid
                                        </h4>
                                        <div class="table-responsive">
                                            <table class="table mt-3">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="text-center">Harga Terakhir</th>
                                                        <th scope="col" class="text-center">Bid Value</th>
                                                        <th scope="col" class="text-center">User</th>
                                                        <th scope="col" class="text-center">Tanggal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($history as $data)
                                                        <tr>
                                                            <td class="text-center">Rp
                                                                {{ number_format($data->last_price + $total_order, 0, '.', '.') }}
                                                            </td>
                                                            <td class="text-center">Rp
                                                                {{ number_format($data->bid_price, 0, '.', '.') }}</td>
                                                            <td class="text-center">{{ $data->user }}</td>
                                                            <td class="text-center">{{ $data->date }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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
