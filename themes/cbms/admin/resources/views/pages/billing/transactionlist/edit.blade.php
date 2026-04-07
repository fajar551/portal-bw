@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Transaction Edit</title>
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
                                    <h4>Edit Transactions</h4>
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
                                                <div class="card p-3">
                                                   @if(Session::has('tabAddClient'))
                                                      @php
                                                         Session::forget('tabAddClient');
                                                      @endphp
                                                   @endif
                                                   <form action="{{ url($baseURL.'transactionlist/update')}}" method="POST" id="addtras" autocomplete="off">
                                                       {{ csrf_field() }}
                                                       @method('PUT')
                                                      <div class="row">
                                                            <div class="col-lg-6">
                                                               <div class="form-group row">
                                                                  <label for="date-transaction"
                                                                        class="col-sm-2 col-form-label">Date</label>
                                                                  <div class="col-sm-8">
                                                                        <!--<input type="date" class="form-control" name="date" id="date-transaction" value="{{$data->date}}"  />-->
                                                                        <div class="input-daterange input-group " id="inputRegDate">
                                                                            <input type="text" class="form-control" name="date" placeholder="dd/mm/yyyy" value="{{$data->date}}" autocomplete="off">
                                                                        </div>
                                                                  </div>
                                                               </div>
                                                               <div class="form-group row">
                                                                  <label for="related-client"
                                                                        class="col-sm-2 col-form-label">Related Client</label>
                                                                  <div class="col-sm-8">
                                                                        <select class="form-control" name="client" id="related-client">
                                                                         
                                                                        </select>
                                                                  </div>
                                                               </div>
                                                               <div class="form-group row">
                                                                  <label for="description-transaction" class="col-sm-2 col-form-label">Description</label>
                                                                  <div class="col-sm-8">
                                                                        <input type="text" class="form-control" value="{{ $data->description }}"  name="description" id="description-transaction" />
                                                                  </div>
                                                               </div>
                                                               <div class="form-group row">
                                                                  <label for="transactionID"
                                                                        class="col-sm-2 col-form-label">Transaction ID</label>
                                                                  <div class="col-sm-8">
                                                                        <input type="text" class="form-control" name="transid" id="transactionID"  value="{{ $data->transid }}" />
                                                                  </div>
                                                               </div>
                                                               <div class="form-group row">
                                                                  <label for="related-client"
                                                                        class="col-sm-2 col-form-label">Invoice ID(s)</label>
                                                                  <div class="col-sm-5">
                                                                       
                                                                        <input type="text" class="form-control" name="invoiceids"  value="{{ $data->invoiceid }}" id="related-client" />
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
                                                                              <option value="{{$k}}" {{ ($data->gateway == $k )?'selected':'' }} >{{$v}}</option>
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
                                                                        ---
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
                                                                        
                                                                        <input type="text" class="form-control"
                                                                           name="amountin"
                                                                           id="amountIn-transaction" value="{{ $data->amountin }}" />
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
                                                                           name="fees" id="fees-transaction" value="{{ $data->fees }}" />
                                                                  </div>
                                                               </div>
                                                               <div class="form-group row">
                                                                  <label for="amountOut-transaction"
                                                                        class="col-sm-2 col-form-label">Amount
                                                                        Out</label>
                                                                  <div class="col-sm-8">
                                                                        <input type="text" class="form-control"
                                                                           name="amountout"
                                                                           id="amountOut-transaction" value="{{ $data->amountout }}" />
                                                                  </div>
                                                               </div>
                                                               
                                                            </div>
                                                            <div class="col-lg-12 text-center">
                                                               <input type="hidden" name="id" value="{{ $data->id }}">
                                                               <button class="btn btn-success px-3 float-lg-right"type="submit" >
                                                               Save Changes
                                                               </button>
                                                            </div>
                                                      </div>
                                                   <form>
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
        $('.select2').select2();

        $('#related-client').select2({
            minimumInputLength: 2,
            /* placeholder: 'Client', */
            ajax: {
                type: "post",
                url: '{{ url('admin/support/getClientselect2') }}',
                dataType: 'json',
                allowClear: true,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processResults: processData,
                cache: true
            },
            data: processData([@php echo json_encode($select); @endphp]).results,
            templateResult: function (d) { return $(d.text); },
            templateSelection: function (d) { return $(d.text); },
        
          });   

          function processData(data) {
            var mapdata = $.map(data, function (obj) {      
               obj.id = obj.id;
               obj.text = obj.name;
               return obj;
            });
            return { 
                        results: $.map(data, function(item) {
                            return {
                                text: `<div class="selectclient">   
                                            <span class="d-block" > `+item.firstname +` `+item.lastname+` (`+item.companyname+`) </span>
                                            <span class="d-block" > <small>`+item.email+`</small></span>
                                        </div>`,
                                id: item.id,
                             
                            }
                        })
                  
                  }; 
         }

   });   
</script> 


@endsection
