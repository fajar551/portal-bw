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

                <div class="col-md-12 mt-3">
                    <div class="card">
                        <div class="card-body">

                            <div class="panel panel-default panel-accent-teal">
                                <form action="{{ route('pages.domain.lelangdomains.action') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="action" value="save_setting">
                                    <!-- Include action input -->
                                    <div class="form-check">
                                        <input name="notif" type="checkbox" class="form-check-input" id="notif"
                                            {{ isset($setting_notif) && $setting_notif ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notif">Notif domain lelang, jual dan
                                            sewa domain</label>
                                    </div>
                                    <br>
                                    <button class="btn btn-primary bg-color-teal" type="submit">Simpan</button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-link"
                    onclick="window.location.href='{{ route('pages.domain.lelangdomains.index') }}'">
                    &lt;&lt; Back To Lelang Domain
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
    <script>
        $(document).ready(function() {
            // Your JS code here
        });
    </script>
@endsection
