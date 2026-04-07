@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Report</title>
@endsection

@section('styles')
    {{-- Date Picker --}}
    <link href="{{ Theme::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <!-- End Sidebar -->
                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-4">Reports</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-10">
                                    <!-- START HERE -->
                                    <h4 class="font-weight-bold">Client Account Balance</h4>
                                </div>
                                <div class="col-lg-2">
                                    {{-- <div class="dropdown float-lg-right">
                                        <a class="btn btn-light dropdown-toggle ml" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-cogs" aria-hidden="true"></i>
                                            Tools
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#">Export to CSV</a>
                                            <a class="dropdown-item" href="#">View Printable Version</a>
                                        </div>
                                    </div> --}}
                                </div>
                                <div class="col-lg-12">
                                    <p>This report provides a statement of account for individual client accounts.</p>
                                </div>
                            </div>
                            <div class="card p-3">
                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                    <div class="card mb-1 shadow-none">
                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne">
                                            <div class="card-header" id="headingOne">
                                                <h6 class="m-0">
                                                    Search & Filter
                                                    <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                </h6>
                                            </div>
                                        </a>
                                        <div id="collapseOne" class="" aria-labelledby="headingOne" data-parent="#accordion">
                                            <div class="card-body">
                                                <form action="{{ route('admin.pages.clients.viewclients.reportstatement.index') }}">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="client">Enter Name, Company or Email to Search</label>
                                                                <select name="userid" id="userid" class="form-control select2-limiting" style="width: 100%" required>
                                                                    @if (isset($client) && $client)
                                                                    <option value="{{ $client->id }}" selected="selected">{{ "{$client->firstname} {$client->lastname} - {$client->companyname} #{$client->id} {$client->email}" }}</option>
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="form-group mb-0">
                                                                <label>Date Range</label>
                                                                <div>
                                                                    <div class="input-daterange input-group" id="date-range">
                                                                        <input type="text" class="form-control" name="start" placeholder="Start" value="{{ $start }}" autocomplete="off" />
                                                                        <input type="text" class="form-control" name="end" placeholder="End" value="{{ $end }}" autocomplete="off"/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group mt-4">
                                                                <button type="submit" class="mt-1 btn btn-primary btn-block waves-effect waves-light font-weight-bold">
                                                                    <span class="align-middle"><i class="fas fa-search"></i></span> Apply
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        <table id="dt-statement" class="table table-bordered dt-responsive w-100">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Type</th>
                                                    <th class="text-center">Date</th>
                                                    <th class="text-center">Description</th>
                                                    <th class="text-center">Credits</th>
                                                    <th class="text-center">Debits</th>
                                                    <th class="text-center">Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($reportdata["tablevalues"] as $num => $values)
                                                    @php
                                                        $rowbgcolor = "#ffffff";
                                                        if (isset($values[0]) && strlen($values[0]) == 7 && substr($values[0], 0, 1) == "#") {
                                                            $rowbgcolor = $values[0];
                                                            unset($values[0]);
                                                        }
                                                    @endphp
                                                    <tr bgcolor="{{$rowbgcolor}}" class="text-center">
                                                    @foreach ($values as $value) 
                                                        <td>{!! $value !!} </td>
                                                    @endforeach
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">No data available in table</td>
                                                    </tr>
                                                @endforelse
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <p class="text-right">{{ $reportTime }}</p>
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
    {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}
    
    
    <!-- Date Picker -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <script>

        const reportTitle = `client_statement_export_{{ date("Ymd")}}`;

        $(() => {
            // Search Client on merge client modal
            $("#userid").select2({
                // theme: "classic"
                placeholder: 'Search Client',
                allowClear: true,
                width: 'resolve',
                closeOnSelect: true,
                templateResult: formatState, // TODO: Fix the display text format
                ajax: {
                    url: '{{ route("admin.pages.clients.viewclients.clientsummary.searchClient") }}',
                    type: 'GET',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    delay: 1000, // Wait 1 seconds before triggering the request
                    data: function (params) {
                        // You can add more params here
                        let query = {
                            search: params.term
                        }

                        return query;
                    }
                },
                cache: true,
                minimumInputLength: 3,
            });

            $('#date-range').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'bottom',
                todayBtn: 'linked',
                todayHighlight: true,
                clearBtn: true,
                disableTouchKeyboard: true,
            });

            dtReportStatement();
        });

        const formatState = (state) => {
            if (!state.id) return state.text;
            
            // console.log(state);

            let result = $(
                `<strong>${state.data.firstname} ${state.data.lastname} ${state.data.companyname}</strong> #${state.data.id}<br /><span>${state.data.email}</span>`
            );

            return result;
        };

        const dtReportStatement = () => {
            let dt = $('#dt-statement').DataTable({
                stateSave: false,
                processing: true,
                responsive: true,
                serverSide: false,
                autoWidth: false,
                searching: false,
                destroy: true,
                paging:false,
                info: false,
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    // 'print',
                    {
                        extend: 'pdfHtml5',
                        title: reportTitle
                    },
                    {
                        extend: 'excelHtml5',
                        title: reportTitle
                    },
                    {
                        extend: 'csvHtml5',
                        title: reportTitle
                    },
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                },
                order: [[ 1, "desc" ]],
                drawCallback: () => {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
            });
        }
    </script>
@endsection
