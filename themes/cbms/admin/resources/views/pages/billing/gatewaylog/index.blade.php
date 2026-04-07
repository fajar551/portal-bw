@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Gateway Log</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                     


                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Gateway Transaction Log</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                    <div class="card mb-1 shadow-none">
                                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse"
                                                            aria-expanded="true" aria-controls="collapseOne">
                                                            <div class="card-header" id="headingOne">
                                                                <h6 class="m-0">
                                                                    Search & filter
                                                                    <i
                                                                        class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                </h6>
                                                            </div>
                                                        </a>
                                                        <div id="collapseOne" class="collapse hide"
                                                            aria-labelledby="headingOne" data-parent="#accordion">
                                                            <div class="card-body p-0 mt-3">
                                                                <form action="" method="POST" id="filter" autocomplete="off">
                                                                    <div class="row">
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="order-id" class="col-sm-4 col-form-label">Date Range</label>
                                                                                <div class="col-sm-8">
                                                                                    <input type="text" name="dateRage" id="date-rang" class="form-control daterange">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="order-number" class="col-sm-4 col-form-label">Debug Data</label>
                                                                                <div class="col-sm-8">
                                                                                    <input type="text" name="dbeugData"  id="debug-data" class="form-control">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="payement-gateway"
                                                                                    class="col-sm-4 col-form-label">Gateway</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control"  name="paymentGateway"  id="payement-gateway">
                                                                                        <option value="" >Any</option>
                                                                                        @foreach($payment as $k=>$v)
                                                                                            <option value="{{ $v }}" >{{ $v }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="result-status"
                                                                                    class="col-sm-4 col-form-label">Result</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="resultStatus" id="result-status">
                                                                                        <option value="" >Any</option>
                                                                                        @foreach($result as $r)
                                                                                            <option value="{{ $r->result }}" >{{ $r->result }}</option>

                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12 d-flex align-items-center">
                                                                            <button class="btn btn-primary px-5  ml-auto"><i class="ri-search-line mr-2"></i> Search</button>
                                                                        </div>
                                                                    </div>

                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="table-responsive">
                                                        <table id="transactionLog" class="table dt-responsive">
                                                            <thead>
                                                                <tr>
                                                                    <th>Date</th>
                                                                    <th>Gateway</th>
                                                                    <th>Debug Data Date</th>
                                                                    <th>Result</th>
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

    <script src="{{ Theme::asset('assets/js/moment.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/daterangepicker.js') }}"></script>
    <script type="text/javascript">
    $(document).ready(function () {
        $('.daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });
        $('.daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' | ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        $('select').select2();

        var GatewayTransactionLog=function(filter=[]){
            if (! $.fn.dataTable.isDataTable('#transactionlist') ) {
                var date=$('#date-rang').val();
                var debugdata=$('#debug-data').val();
                var gateway=$('#payement-gateway').val();
                var result=$('#result-status').val();
                //var status=$('#status').val();

                var tbl =$('#transactionLog').DataTable({
                            paging: true,
                            processing: true,
                            serverSide: true,
                          
                            ajax:{
                                url : '{{ url(Request::segment(1).'/billing/gatewaylog') }}',
                                type: 'POST',
                                headers : {
                                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                                },
                                data : {
                                    date : date,
                                    debugdata : debugdata,
                                    gateway : gateway,
                                    result : result,
                                }, 
                                dataType: 'json',
                            },
                            language: {
                                paginate: {
                                    previous: "<i class='mdi mdi-chevron-left'>",
                                    next: "<i class='mdi mdi-chevron-right'>",
                                },
                                searching: false,
                            },
                            columns : [
                                {data: 'date', name: 'id'},
                                {data: 'gateway', name: 'gateway'},
                                {data: 'data', name: 'data',render:(data, type, row) => {
                                            return '<textarea class="form-control" id="exampleFormControlTextarea1" rows="3">'+data+'</textarea>'
                                     }
                                },
                                {data: 'result', name: 'result'},
                            
                            ],  
                           /*  columnDefs: [
                                    {
                                        'targets': 0,
                                        'checkboxes': {
                                        'selectRow': true
                                        }
                                    }
                                ],
                            select : {
                                style : 'multi'
                            }, */
                            drawCallback: function () {
                                $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                            },
                           order : [[ 0, "desc" ]]
                        });
                }
            };

            $('#filter').on('submit', function(e){
                e.preventDefault();
                $('#transactionLog').dataTable().fnDestroy();
                GatewayTransactionLog();
                return false;
            });

            GatewayTransactionLog();


    });
</script> 
@endsection

