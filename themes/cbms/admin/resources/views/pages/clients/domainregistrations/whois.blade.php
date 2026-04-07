@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Whois</title>
@endsection

@section('styles')

@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h4>Domain WHOIS Lookup</h4>
                                    <div class="card p-3">
                                        <div class="row">
                                            @if ($result)
                                            <div class="col-lg-12">
                                                <div class="alert alert-{{ $result["type"] }}" role="alert" style="font-size:18px;">
                                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                                    <strong>{!! $result["message"] !!}</strong>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-lg-12">
                                                <form action="{{ route("admin.pages.clients.domainregistrations.whois") }}" method="POST">
                                                    @csrf
                                                    <div class="form-group row">
                                                        <label for="category-name" class="col-sm-2 col-form-label">Domain</label>
                                                        <div class="col-sm-10 col-lg-8">
                                                            <input type="text" name="domain" class="form-control" value="{{ old("domain", $domain ?? "") }}" placeholder="domaintolookup.com" required>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <button type="submit" class="btn btn-success ">Lookup Domain</button>
                                                        </div>
                                                    </div>
                                                </form>
                                                <hr>
                                            </div>
                                            @if (isset($details))
                                            <div class="col-lg-12">
                                                <h4>{{ __("admin.whoiswhois") }}</h4>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Required datatable js -->
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}

    <!-- Moment JS -->
    <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>

    <!-- JQuery Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            timerProgressBar: true,
            timer: 3000,
        });

        $(() => {

        });

    </script>
    
@endsection
