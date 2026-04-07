@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Billable Items</title>
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
                                        <h4 class="mb-3">Billable Items</h4>
                                    </div>

                                    @if(Session::has('success'))
                                    <div class="alert alert-success">
                                        {{ Session::get('success') }}
                                        @php
                                            Session::forget('success');
                                        @endphp
                                    </div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <!-- SEARCH -->
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
                                                        <div id="collapseOne" class="collapse hide" aria-labelledby="headingOne" data-parent="#accordion">
                                                            <div class="card-body p-0 mt-3">
                                                                <form action="" method="POST" id="filter" autocomplete="off">
                                                                    <div class="row">
                                                                        <div class="col-sm-12 col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label
                                                                                    class="col-sm-12 col-lg-2 col-form-label">Client</label>
                                                                                <div class="col-sm-12 col-lg-6">
                                                                                    <select name="userid" id="optionclient" class="form-control select2-placeholder"></select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="" class="col-sm-12 col-lg-2 col-form label">Description</label>
                                                                                <div class="col-sm-12 col-lg-10">
                                                                                    <input type="text" id="description" name="description" class="form-control">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-sm-12 col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label class="col-sm-12 col-lg-2 col-form-label">Amount</label>
                                                                                <div class="col-sm-12 col-lg-2">
                                                                                    <input type="text" name="amount" id="amount" class="form-control">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label
                                                                                    class="col-sm-12 col-lg-2 col-form-label">
                                                                                    Status
                                                                                </label>
                                                                                <div class="col-sm-12 col-lg-6">
                                                                                    <select name="status" id="statusaction" class="form-control">
                                                                                        <option>Any</option>
                                                                                        <option>Uninvoiced</option>
                                                                                        <option>Invoiced</option>
                                                                                        <option>Recurring</option>
                                                                                        <option>Active Recurring</option>
                                                                                        <option>Completed Recurring</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <button type="submit" class="btn btn-primary px-5 d-flex align-items-center float-lg-right"><i class="ri-search-line mr-2"></i>Search</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <!-- end SEARCH -->
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="loadtablebillableitemes" class="table table-bordered dt-responsive w-100">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Client Name</th>
                                                                <th>Description</th>
                                                                <th>Hours</th>
                                                                <th>Amount</th>
                                                                <th>Invoice Action</th>
                                                                <th>Invoiced</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label mt-1">With
                                                        Selected: </label>
                                                    <div class="col-sm-12 col-lg-6 d-inline">
                                                        <button class="btn btn-light px-2">Invoice on Next Cron Run</button>
                                                        <button class="btn btn-danger px-2">Delete</button>
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
    <script type="text/javascript">
    $(document).ready(function () {
        var GatewayTransactionLog=function(filter=[]){
            if (! $.fn.dataTable.isDataTable('#transactionlist') ) {
                var client=$('#optionclient').val();
                var description=$('#description').val();
                var amount=$('#amount').val();
                var status=$('#statusaction').val();
        
                var tbl =$('#loadtablebillableitemes').DataTable({
                            paging: true,
                            processing: true,
                            serverSide: true,
                          
                            ajax:{
                                url : '{{ url(Request::segment(1).'/billing/billableitemlist') }}',
                                type: 'POST',
                                headers : {
                                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                                },
                                data : {
                                    client : client,
                                    description : description,
                                    amount : amount,
                                    status : status
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
                                {data: 'id', name: 'id',render:(data, type, row) => { 
                                        
                                        return `<a href="{{ url(Request::segment(1).'/billing/billableitemlist/edit/') }}/`+row.id+`">`+data+`</a>`
                                    }
                                },
                                {data: 'client', name: 'firstname'},
                                {data: 'description', name: 'description'},
                                {data: 'hours', name: 'hours'},
                                {data: 'amount', name: 'amount'},
                                {data: 'invoiceaction', name: 'invoiceaction'},
                                /* {data: 'data', name: 'data',render:(data, type, row) => {
                                            return '<textarea class="form-control" id="exampleFormControlTextarea1" rows="3">'+data+'</textarea>'
                                     }
                                }, */
                                {data: 'invoiced', name: 'invoiced'},
                                {data: 'action', name: 'action'},
                            
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

            GatewayTransactionLog();
        




            $('#optionclient').select2({
                minimumInputLength: 2,
                placeholder: 'Client',
                ajax: {
                    type: "post",
                    url: '{{ url(Request::segment(1).'/getclientjson') }}',
                    dataType: 'json',
                    delay: 250, 
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: `<div class="customselect" style="line-height: 1.4; font-size: 12px;" >   
                                                <span class="d-block" > `+item.firstname +` `+item.lastname+` (`+item.companyname+`) </span>
                                                <span class="d-block" > <small>`+item.email+`</small></span>
                                            </div>`,
                                    id: item.id,
                                
                                }
                            })
                        };
                    },
                    cache: true
                },
                templateResult: function (d) { return $(d.text); },
                templateSelection: function (d) { return $(d.text); }
        });

    });
</script> 





@endsection
