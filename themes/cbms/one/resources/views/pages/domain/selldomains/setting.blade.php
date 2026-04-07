@extends('layouts.clientbase')

@section('title')
    <title>Setting Sell & Rent Domain Page</title>
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('modules/addons/sell_domain/assets/custom.css') }}">

@section('content')
    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 col-md-8">
                    <h3 class="mb-0">Setting</h3>
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
                    <div class="card">
                        <div class="card-body" style="width: 70%; margin: 0 auto;">
                            <form method="POST"
                                action="{{ route('pages.domain.selldomains.action', ['action' => 'set_bank']) }}">
                                @csrf
                                <div class="form-group">
                                    <label for="selector_bank" class="form-label">Nama Bank:</label>
                                    <select class="form-control" id="selector_bank" name="field[bank]" required>
                                        <option value="mandiri" {{ $bank == 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                                        <option value="bri" {{ $bank == 'bri' ? 'selected' : '' }}>BRI</option>
                                        <option value="bca" {{ $bank == 'bca' ? 'selected' : '' }}>BCA</option>
                                        <option value=""
                                            {{ !in_array($bank, ['bca', 'bri', 'mandiri']) && !empty($bank) ? 'selected' : '' }}>
                                            Bank Lainnya</option>
                                    </select>
                                </div>

                                <div class="form-group" id="input_bank_container"
                                    style="{{ !in_array($bank, ['bca', 'bri', 'mandiri']) && !empty($bank) ? 'display:block;' : 'display:none;' }}">
                                    <input class="form-control" type="text" id="input_bank" name="field[bank_other]"
                                        placeholder="Masukkan nama bank lain..." value="{{ $bank }}">
                                </div>

                                <div class="form-group">
                                    <label for="atas_nama" class="form-label">Atas Nama:</label>
                                    <input class="form-control" type="text" id="atas_nama" name="field[an]"
                                        value="{{ $atasnama }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="nomor_rekening" class="form-label">No Rekening:</label>
                                    <input class="form-control" type="text" id="nomor_rekening" name="field[rekening]"
                                        value="{{ $rekening }}" placeholder="098989878382" required>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <br />
                    <div class="text-center">
                        <a href="{{ route('pages.domain.selldomains.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        $(document).ready(function() {
            var $selectorBank = $('#selector_bank');
            var $inputBankContainer = $('#input_bank_container');
            var $inputBank = $('#input_bank');

            $selectorBank.on('change', function() {
                if ($selectorBank.val() === '') {
                    $inputBankContainer.show();
                    $inputBank.prop('required', true);
                } else {
                    $inputBankContainer.hide();
                    $inputBank.prop('required', false);
                }
            });

            if ($selectorBank.val() === '') {
                $inputBankContainer.show();
                $inputBank.prop('required', true);
            } else {
                $inputBankContainer.hide();
                $inputBank.prop('required', false);
            }
        });
    </script>
@endsection
