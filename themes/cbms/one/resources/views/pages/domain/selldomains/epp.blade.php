@extends('layouts.clientbase')

@section('title')
    <title>Epp Sell Domain Page</title>
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('modules/addons/sell_domain/assets/custom.css') }}">

    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
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
                    <form method="POST" action="{{ route('pages.domain.selldomains.action', ['action' => 'set_epp']) }}">
                        @csrf
                        <input type="hidden" name="domain" value="{{ $domain }}">
                        <div class="mb-3">
                            <label for="epp" class="form-label">EPP Code:</label>
                            <input class="form-control" type="text" name="epp" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Submit</button>
                    </form>

                </div>
            </div>
            <div class="text-center">
                <a class="btn btn-secondary" href="{{ route('pages.domain.selldomains.index') }}">Back</a>
            </div>
        </div>
    </div>
@endsection
