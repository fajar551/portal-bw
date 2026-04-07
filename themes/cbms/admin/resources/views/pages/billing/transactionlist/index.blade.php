@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Transaction List</title>
@endsection
<style>
    span.selection .selectclient {
        line-height: 17px;
    }
</style>
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                     
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h4>Transactions</h4>
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
                                    

                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button class="btn btn-outline-success px-3 float-lg-left mb-2 d-flex align-items-center  " type="button" data-toggle="collapse" data-target="#transactionForm" aria-expanded="@if(Session::has('tabAddClient')) true @else false @endif" aria-controls="transactionForm">
                                                    <i class="ri-add-line mr-2"></i> Add Transaction
                                                </button>
                                            </div>
                                            <div class="col-lg-12">
                                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                    <div class="card mb-1 shadow-none">
                                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse"
                                                            aria-expanded="true" aria-controls="collapseOne">
                                                            <div class="card-header" id="headingOne">
                                                                <h6 class="m-0">
                                                                    Search & filter
                                                                    <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                </h6>
                                                            </div>
                                                        </a>
                                                        <div id="collapseOne" class="collapse hide" aria-labelledby="headingOne" data-parent="#accordion">
                                                            <div class="card-body p-0 mt-3">
                                                            <form action="" method="POST" id="filter" autocomplete="off">
                                                                <div class="row">
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group row">
                                                                            <label for="filter-activity"
                                                                                class="col-sm-4 col-form-label">Show</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control" name="show" id="filter-show">
                                                                                    <option value="" >All Activity</option>
                                                                                    <option value="received" >Payments Received</option>
                                                                                    <option value="sent" >Payments Sent</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="description"
                                                                                class="col-sm-4 col-form-label">Description</label>
                                                                            <div class="col-sm-8">
                                                                                <input class="form-control" name="filterdescription" id="filterdescription" />
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="stats-domain"
                                                                                class="col-sm-4 col-form-label">Transaction ID</label>
                                                                            <div class="col-sm-8">
                                                                                <input class="form-control" name="filtertransid" id="filtertransid" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group row">
                                                                            <label for="date-range"
                                                                                class="col-sm-4 col-form-label">Date
                                                                                Range</label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" id="daterange" name="daterange" class="form-control daterange" autocomplete="off">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="amount-pay"
                                                                                class="col-sm-4 col-form-label">Amount</label>
                                                                            <div class="col-sm-8">
                                                                                <input class="form-control"
                                                                                    name="amount" id="amount-pay">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="payment-method"
                                                                                class="col-sm-4 col-form-label">Payment
                                                                                Method</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control select2" name="payment-method" id="payment-method">
                                                                                <option value="">Any</option>
                                                                                    @foreach($gateway as $k => $v )    
                                                                                        <option value="{{$k}}">{{$v}}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-12">
                                                                        <button
                                                                            class="btn btn-primary px-5 d-flex align-items-center ml-auto"><i
                                                                                class="ri-search-line mr-2"></i>
                                                                            Search</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                           
                                                <div class="collapse @if(Session::has('tabAddClient')) show @endif" id="transactionForm">
                                                    <div class="card p-3">
                                                    @if(Session::has('tabAddClient'))
                                                        @php
                                                            Session::forget('tabAddClient');
                                                        @endphp
                                                    @endif
                                                        <form action="{{ url($baseURL.'transactionlist/store')}}" method="POST" id="addtras" autocomplete="off">
                                                        {{ csrf_field() }}
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label for="date-transaction"
                                                                            class="col-sm-2 col-form-label">Date</label>
                                                                        <div class="col-sm-8">
                                                                            
                                                                            <div class="input-daterange input-group " id="inputRegDate">
                                                                                 <input type="text" class="form-control" name="date" placeholder="dd/mm/yyyy" value="{{old('date')}}" autocomplete="off">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label for="related-client"
                                                                            class="col-sm-2 col-form-label">Related Client</label>
                                                                        <div class="col-sm-8">
                                                                            <select class="form-control" name="client" id="related-client">
                                                                                <option></option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label for="description-transaction" class="col-sm-2 col-form-label">Description</label>
                                                                        <div class="col-sm-8">
                                                                            <input type="text" class="form-control" value="{{old('description')}}"  name="description" id="description-transaction" />
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label for="transactionID"
                                                                            class="col-sm-2 col-form-label">Transaction ID</label>
                                                                        <div class="col-sm-8">
                                                                            <input type="text" class="form-control" name="transid" id="transactionID"  value="{{old('transid')}}" />
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label for="related-client"
                                                                            class="col-sm-2 col-form-label">Invoice ID(s)</label>
                                                                        <div class="col-sm-5">
                                                                             @if ($errors->has('invoiceids'))
                                                                                   <span class="text-danger">{{ $errors->first('invoiceids') }}</span>
                                                                             @endif
                                                                            <input type="text" class="form-control" name="invoiceids"  value="{{old('invoiceids')}}" id="related-client" />
                                                                        </div>
                                                                        <div class="col-sm-4">
                                                                            <label class="mt-2">Comma Separated</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label for="payment-method-transaction"
                                                                            class="col-sm-2 col-form-label">Payment Method</label>
                                                                        <div class="col-sm-8">
                                                                             @if ($errors->has('paymentmethod'))
                                                                                   <span class="text-danger">{{ $errors->first('paymentmethod') }}</span>
                                                                             @endif
                                                                            <select class="form-control select2" name="paymentmethod" id="payment-method-transaction">
                                                                                <option value="">None</option>
                                                                                @foreach($gateway as $k => $v )    
                                                                                    <option value="{{$k}}" {{ (old('amountout') == $k )?'selected':'' }}  >{{$v}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label for="payment-method-transaction"
                                                                            class="col-sm-2 col-form-label">Currency</label>
                                                                        <div class="col-sm-4">
                                                                            <select class="form-control"
                                                                                name="currency"
                                                                                id="payment-method-transaction">
                                                                                @foreach($currency as $k)
                                                                                    <option value="{{ $k->id }}" >{{ $k->code}}</option>
                                                                                @endforeach
                                                                                
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-sm-4">
                                                                            <label class="mt-2">
                                                                                (Non Client Only)
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label for="amountIn-transaction"
                                                                            class="col-sm-2 col-form-label">Amount
                                                                            In</label>
                                                                        <div class="col-sm-8">
                                                                             @if ($errors->has('amountin'))
                                                                                   <span class="text-danger">{{ $errors->first('amountin') }}</span>
                                                                             @endif
                                                                            <input type="text" class="form-control"
                                                                                name="amountin"
                                                                                id="amountIn-transaction" value="{{ old('amountin') }}" >
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label for="fees-transaction"
                                                                            class="col-sm-2 col-form-label">Fees</label>
                                                                        <div class="col-sm-8">
                                                                             @if ($errors->has('fees'))
                                                                                   <span class="text-danger">{{ $errors->first('fees') }}</span>
                                                                             @endif
                                                                            <input type="text" class="form-control"
                                                                                name="fees" id="fees-transaction" value="{{ old('fees') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label for="amountOut-transaction"
                                                                            class="col-sm-2 col-form-label">Amount
                                                                            Out</label>
                                                                        <div class="col-sm-8">
                                                                            <input type="text" class="form-control"
                                                                                name="amountout"
                                                                                id="amountOut-transaction"  value="{{ old('amountout') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label for="amountIn-transaction"
                                                                            class="col-sm-2 col-form-label">Credit</label>
                                                                        <div class="col-sm-8">
                                                                            <div class="form-check mt-2">
                                                                                <input class="form-check-input" name="addcredit" type="checkbox"
                                                                                    value="" id="defaultCheck1">
                                                                                <label class="form-check-label"
                                                                                    for="defaultCheck1">
                                                                                    Add to Client's Credit Balance
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-12">
                                                                    <button class="btn btn-success px-3 float-lg-right"type="submit" >
                                                                        Add Transaction
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        <form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="transactionlist" class="table dt-responsive w-100">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th>Client Name</th>
                                                                <th>Date</th>
                                                                <th>Payment Method</th>
                                                                <th>Description</th>
                                                                <th>Amount In</th>
                                                                <th>Fees</th>
                                                                <th>Amount Out</th>
                                                                <th></th>
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
    <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
   <!-- <script src="{{ Theme::asset('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script> -->
   <!-- <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>-->
    <script type="text/javascript">
    let dateRangeOption = {
            format: 'dd/mm/yyyy',
            autoclose: true,
            orientation: 'bottom',
            todayBtn: 'linked',
            todayHighlight: true,
            clearBtn: true,
            disableTouchKeyboard: true,
        };


    $(document).ready(function () {
        $('#inputRegDate').datepicker(dateRangeOption);
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
        $('.select2').select2();
        var transactionlist=function(filter=[]){
            if (! $.fn.dataTable.isDataTable('#transactionlist') ) {
                var show=$('#filter-show').val();
                var filterdescription=$('#filterdescription').val();
                var filtertransid=$('#filtertransid').val();
                var daterange=$('#daterange').val();
                var amount=$('#amount-pay').val();
                var paymentmethod=$('#payment-method').val();


                var tbl =$('#transactionlist').DataTable({
                            paging: true,
                            processing: true,
                            serverSide: true,
                            ordering:false,
                            ajax:{
                                url : '{{ url($baseURL.'transactionlist') }}',
                                type: 'POST',
                                headers : {
                                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                                },
                                data : {
                                    show : show,
                                    filterdescription : filterdescription,
                                    filtertransid : filtertransid,
                                    daterange : daterange,
                                    amount : amount,
                                    paymentmethod : paymentmethod
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
                                {data: 'client', name: 'client'},
                                {data: 'date', name: 'date'},
                                {data: 'gateway', name: 'gateway'},
                                {data: 'description', name: 'description'},
                                {data: 'amountin', name: 'amountin'},
                                {data: 'fees', name: 'fees'},
                                {data: 'amountout', name: 'amountout'}, 
                                {data: 'action', name: 'action', orderable: false, searchable: false,width:'100px'},
                            /*  {data: 'delete', name: 'delete', orderable: false, searchable: false}, */
                            ],
                            drawCallback: function () {
                                $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                            },
                            order : [[ 0, "desc" ]]
                        });
                }
            };


       /*  $('#transactionlist').DataTable({
            paging: true,
            processing: true,
            serverSide: true,
            ajax:{
                url : '{{ url($baseURL.'transactionlist') }}',
                type: 'POST',
                headers : {
                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                }
            },
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>",
                },
                searching: false,
            },
            columns : [
                {data: 'client', name: 'client'},
                {data: 'date', name: 'date'},
                {data: 'gateway', name: 'gateway'},
                {data: 'description', name: 'description'},
                {data: 'amountin', name: 'amountin'},
                {data: 'fees', name: 'fees'},
                {data: 'amountout', name: 'amountout'}, 
                {data: 'action', name: 'action', orderable: false, searchable: false,width:'100px'},
               
            ],
            drawCallback: function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
            },
        }) */

        transactionlist();
        //delete announcements
        $('#filter').on('submit', function(e){
            e.preventDefault();
            $('#transactionlist').dataTable().fnDestroy();
            transactionlist();
            return false;
        });
        $('#transactionlist').on('click', '.delete', function (){
           // e.preventDefault();
           Swal.fire({
				title: "Warning..!",
				text: "Do you want to delete  "+$(this).data('title')+" ?",
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

        $('#related-client').select2({
            minimumInputLength: 2,
            placeholder: 'Client',
            ajax: {
                type: "post",
                url: '{{ url('admin/support/getClientselect2') }}',
                dataType: 'json',
                /*   delay: 250, */
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: `<div class="selectclient" >   
                                            <span class="d-block" > `+item.firstname +` `+item.lastname+` (`+item.companyname+`) </span>
                                            <span class="d-block" > <small>`+item.email+`</small></span>
                                        </div>`,
                                id: item.id,
                              /*   element : '<div class="coba" ></div>' */
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
