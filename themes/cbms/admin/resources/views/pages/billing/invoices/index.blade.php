@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Invoices</title>
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
                                        <h4 class="mb-3">Invoices</h4>
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
                                                        <div id="collapseOne" class="collapse hide"
                                                            aria-labelledby="headingOne" data-parent="#accordion">
                                                            <div class="card-body p-0 mt-3">
                                                                <form action="" method="post" id="filter"  >
                                                                    <div class="row">
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="clientName"
                                                                                    class="col-sm-3 col-form-label my-1">Client Name</label>
                                                                                <div class="col-sm-9">
                                                                                    <select class=" form-control" name="clientname" id="related-client"  style="width: 100%;" >
                                                                                        
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="invoiceID"
                                                                                    class="col-sm-3 col-form-label my-1">Invoice  #</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="text" name="invoicenum" id="invoicenum" class="form-control my-1">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="lineItem" class="col-sm-3 col-form-label my-1">Line Item Description</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="text" name="lineitem" id="lineitem" class="form-control my-1">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="paymentMethod" class="col-sm-3 col-form-label my-1">Payment Method</label>
                                                                                <div class="col-sm-4">
                                                                                    <select class="select2 form-control" name="paymentmethod" id="paymentmethod" style="width: 100%;">
                                                                                        <option value="">Any</option>
                                                                                        @foreach($gateway as $k=>$v)
                                                                                        <option value="{{ $k }}">{{ $v }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="stats" class="col-sm-3 col-form-label my-1">Status</label>
                                                                                <div class="col-sm-4">
                                                                                    <select  class="select2 form-control" name="status" id="status"style="width: 100%;">
                                                                                        <option value="">Any</option>
                                                                                        <option value="Draft">Draft</option>
                                                                                        <option value="Unpaid">Unpaid</option>
                                                                                        <option value="Overdue">Overdue</option>
                                                                                        <option value="Paid">Paid</option>
                                                                                        <option value="Cancelled">Cancelled</option>
                                                                                        <option value="Refunded">Refunded</option>
                                                                                        <option value="Collections">Collections</option>
                                                                                        <option value="Payment Pending">Payment Pending</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="totalDue"
                                                                                    class="col-sm-3 col-form-label my-1">Total Due</label>
                                                                                <div class="col-sm-9">
                                                                                    <div class="row">
                                                                                        <div class="col-sm-6 d-flex">
                                                                                            <label for="from" class="col-form-label my-1">From</label>
                                                                                            <div>
                                                                                                <input type="number" name="totalfrom" id="totalfrom"  class="form-control ml-2">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-6 d-flex">
                                                                                            <label for="from" class="col-form-label my-1">To</label>
                                                                                            <div>
                                                                                                <input type="number" name="totalto" id="totalto" class="form-control ml-2">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="invoiceDate"
                                                                                    class="col-sm-3 col-form-label my-1">Invoice Date</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="date" class="form-control my-1" name="invoicedate" id="invoicedate"  >
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="dueDate" class="col-sm-3 col-form-label my-1">Due Date</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="date" class="form-control my-1" name="duedate" >
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="datePaid" class="col-sm-3 col-form-label my-1">Date Paid</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="date" class="form-control my-1" name="datepaid" id="datepaid" >
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="lastCapture"  class="col-sm-3 col-form-label my-1">Last Capture Attempt</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="date" class="form-control my-1" name="last_capture_attempt" id="last_capture_attempt" >
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="dateRefunded"
                                                                                    class="col-sm-3 col-form-label my-1">Date Refunded</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="date" class="form-control my-1" name="date_refunded" id="date_refunded" >
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="dateCancelled" class="col-sm-3 col-form-label my-1">Date Cancelled</label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="date" class="form-control my-1" name="date_cancelled" id="date_cancelled" >
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <button type="submit" class="btn btn-primary px-5 float-lg-right d-flex align-items-center"><i class="ri-search-line mr-2" aria-hidden="true"></i>Search</button>
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
                                        @foreach($count as $counts )
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="alert alert-secondary d-lg-flex justify-content-lg-around font-size-16"
                                                    role="alert">
                                                    <strong>{{ $counts['currencycode'] }}</strong>
                                                    <div>
                                                        Paid: <span class="text-success font-weight-bold">
                                                        {{ $counts['paid'] }}</span>
                                                    </div>
                                                    <div>
                                                        Unpaid: <span class="text-danger font-weight-bold">
                                                        {{ $counts['unpaid'] }}</span>
                                                    </div>
                                                    <div>
                                                        Overdue: <span class="text-dark font-weight-bold">
                                                        {{ $counts['overdue'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <form action="{{ url($baseURL.'invoices/action') }}" method="POST" id="formdatatable">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="table-responsive">
                                                        <table id="dataIvoice" class="table dt-responsive w-100">
                                                            <thead>
                                                                <tr>
                                                                    <th>
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" class="custom-control-input" name="invid" id="allchack">
                                                                            <label class="custom-control-label"
                                                                                for="allchack">&nbsp;</label>
                                                                        </div>
                                                                    </th>
                                                                    <th>Invoice #</th>
                                                                    <th>Client Name</th>
                                                                    <th>Invoice Date</th>
                                                                    <th>Due Date</th>
                                                                    <th>Last Capture Attempt</th>
                                                                    <th>Total</th>
                                                                    <th>Payment Method</th>
                                                                    <th>Status</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                        </table>
                                                    
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <hr>
                                                    
                                                        <div class="form-group row">
                                                            <label for="selectedData" class="col-sm-1 col-form-label my-1">With
                                                                Selected:</label>
                                                            <div class="col-lg-10 col-sm-12 d-lg-flex">
                                                                <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                                                <button type="submit" value="Mark Paid" class="btn btn-success my-1 mx-1" name="markpaid" >Mark Paid</button>
                                                                <button type="submit" value="Mark Unpaid" class="btn btn-light my-1 mx-1" name="markunpaid" >Mark Unpaid</button>
                                                                <button type="submit" value="Mark Cancelled" class="btn btn-light my-1 mx-1" name="markcancelled" >Mark Cancelled</button>
                                                                <button type="submit" value="Duplicate Invoice" name="duplicateinvoice" class="btn btn-light my-1 mx-1">Duplicate Invoice</button>
                                                                <button type="submit" value="Send Reminder" name="paymentreminder" class="btn btn-light my-1 mx-1">Send paymentReminderInvoice</button>
                                                                <button type="submit" value="Delete"  name="massdelete" class="btn btn-danger my-1 mx-1"> Delete</button>
                                                                
                                                            </div>
                                                        </div>
                                                    
                                                </div>
                                            </div>
                                        </form>
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
    <script type="text/javascript">
    $(document).ready(function () {
        let selectedId = [];
        var transactionlist=function(filter=[]){
            if (! $.fn.dataTable.isDataTable('#transactionlist') ) {
                var client=$('#related-client').val();
                var invoicenum=$('#invoicenum').val();
                var lineitem=$('#lineitem').val();
                var paymentmethod=$('#paymentmethod').val();
                var status=$('#status').val();
               // var paymentmethod=$('#payment-method').val();
                var totalfrom=$('#totalfrom').val();
                var invoicedate=$('#invoicedate').val();
                var duedate=$('#duedate').val();
                var datepaid=$('#datepaid').val();
                var last_capture_attempt=$('#last_capture_attempt').val();
                var date_refunded=$('#date_refunded').val();
                var date_cancelled=$('#date_cancelled').val();
                var totalto=$('#totalto').val();


                var tbl =$('#dataIvoice').DataTable({
                            paging: true,
                            processing: true,
                            serverSide: true,
                          
                            ajax:{
                                url : '{{ url($baseURL.'invoices') }}',
                                type: 'POST',
                                headers : {
                                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                                },
                                data : {
                                    client : client,
                                    invoicenum : invoicenum,
                                    lineitem : lineitem,
                                    paymentmethod : paymentmethod,
                                    status : status,
                                    totalfrom : totalfrom,
                                    invoicedate : invoicedate,
                                    duedate : duedate,
                                    last_capture_attempt : last_capture_attempt,
                                    date_refunded : date_refunded,
                                    date_cancelled : date_cancelled,
                                    totalto:totalto
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
                                {data: 'checkbox', name: 'checkbox',orderable: false, searchable: false,
                                    render: (data, type, row) => {
                                            let checked = selectedId.includes(row.id) ? "checked" : "";

                                            return `<div class="custom-control custom-checkbox">
                                                        <input type="checkbox" name="invoice[]" id="select${data}" ${checked} class="custom-control-input select-invoice" value="${data}">
                                                        <label class="custom-control-label" for="select${data}">&nbsp;</label>
                                                    </div>`;
                                        }
                                },
                                {data: 'id', name: 'id'},
                                {data: 'client', name: 'client'},
                                {data: 'date', name: 'date'},
                                {data: 'duedate', name: 'duedate'},
                                {data: 'last_capture_attempt', name: 'last_capture_attempt'},
                                {data: 'total', name: 'total'},
                                {data: 'paymentmethod', name: 'paymentmethod'}, 
                                {data: 'status', name: 'status'}, 
                               
                                {data: 'action', name: 'action', orderable: false, searchable: false,width:'100px'},
                           
                            ],  
                            columnDefs: [
                                    {
                                        'targets': 0,
                                        'checkboxes': {
                                        'selectRow': true
                                        }
                                    }
                                ],
                           //  select : {
                           //      style : 'multi'
                           //  },
                            drawCallback: function () {
                                $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                                $('[data-toggle="popover"]').popover({
                                   html: true
                                }); 

                            },
                            order : [[1, "desc" ]]
                        });
                }
            };

            $('#filter').on('submit', function(e){
                e.preventDefault();
                $('#dataIvoice').dataTable().fnDestroy();
                transactionlist();
                return false;
            });


            transactionlist();
            $('body').on('change', '#allchack', function() {
                let checked = $(this).is(':checked');
                // /alert('aaa');
                $('.select-invoice').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedId.includes(id)) selectedId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedId = [];
                    }
                });

                // console.log(selectedId);
            });


            $('body').on('change', '.select-invoice', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());
               
                if (checked) {
                    if (!selectedId.includes(id)) selectedId.push(id);
                } else {
                    let idx = selectedId.indexOf(id);

                    if (idx > -1) selectedId.splice(idx, 1);
                }

                // console.log(selectedId);
            });

            $('#dataIvoice').on('click', '.delete', function (){
            // e.preventDefault();
                Swal.fire({
                        title: "Warning..!",
                        text: "Do you want to delete Invoices "+$(this).data('id')+" ?",
                        icon: "warning",
                        showCancelButton:true,
                        cancelButtonColor: '#d33',
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((value) => {
                        if(value.isConfirmed){
                            $('#fd'+$(this).data('id')).submit();
                        }else{
                            return false;
                        }
                });
                return false;
            });
            
            $('.select2').select2();


            $('#related-client').select2({
                minimumInputLength: 2,
                placeholder: 'Client',
                ajax: {
                    type: "post",
                    url: '{{ url('admin/getclientjson') }}',
                    dataType: 'json',
                    delay: 250, 
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                console.log(item,'datanya');
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
