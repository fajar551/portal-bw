@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Spam Control</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <!-- Sidebar Shortcut -->

                    <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Support Ticket Spam Control</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="border rounded p-2">
                                            <div class="form-group mb-0 row justify-content-center">
                                                <label class="col-sm-12 col-lg-2 col-form-label text-lg-right">Add:
                                                    Type/Value</label>
                                                <div class="col-sm-12 col-lg-2 my-1">
                                                    <select name="" id="" class="form-control">
                                                        <option value="0">Sender</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-12 col-lg-4 my-1">
                                                    <input type="text" class="form-control">
                                                </div>
                                                <div class="col-sm-12 col-lg-3 my-1">
                                                    <button class="btn btn-success px-1">Add New Spam Control</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row my-3">
                                            <div class="col-lg-12">
                                                <nav>
                                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                        <a class="nav-link nav-item active" id="nav-senders-tab"
                                                            data-toggle="tab" href="#nav-senders" role="tab"
                                                            aria-controls="nav-senders" aria-selected="true">Blocked
                                                            Senders</a>
                                                        <a class="nav-link nav-item" id="nav-subjects-tab" data-toggle="tab"
                                                            href="#nav-subjects" role="tab" aria-controls="nav-subjects"
                                                            aria-selected="false">Blocked Subjects</a>
                                                        <a class="nav-link nav-item" id="nav-phrases-tab" data-toggle="tab"
                                                            href="#nav-phrases" role="tab" aria-controls="nav-phrases"
                                                            aria-selected="false">Blocked Phrases</a>
                                                    </div>
                                                </nav>
                                                <div class="tab-content" id="nav-tabContent">
                                                    <div class="tab-pane fade show active" id="nav-senders" role="tabpanel"
                                                        aria-labelledby="nav-senders-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="table-responsive">
                                                                    <table id="datatable" class="table table-bordered">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Content</th>
                                                                            </tr>
                                                                        </thead>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="nav-subjects" role="tabpanel"
                                                        aria-labelledby="nav-subjects-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="table-responsive">
                                                                    <table id="alternative-page-datatable"
                                                                        class="table table-bordered">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Content</th>
                                                                            </tr>
                                                                        </thead>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="nav-phrases" role="tabpanel"
                                                        aria-labelledby="nav-phrases-tab">
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="table-responsive">
                                                                    <table id="selection-datatable"
                                                                        class="table table-bordered">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Content</th>
                                                                            </tr>
                                                                        </thead>
                                                                    </table>
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
                        </div>
                    </div>
                    <!-- End MAIN CARD -->
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
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
@endsection
