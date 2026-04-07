@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Activity Log</title>
@endsection

@section('styles')
    <!-- Date Picker -->
    <link href="{{ Theme::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">
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
                              <h4 class="mb-3"> Activity Log</h4>
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
                                                         <div class="col-md-2">
                                                               <div class="form-group">
                                                                  <div class="input-daterange input-group" id="input-date">
                                                                     <input type="text" name="date" id="datefilter"  class="form-control" placeholder="Date">
                                                                  </div>
                                                               </div>
                                                         </div>
                                                         <div class="col-md-2">
                                                               <div class="form-group">
                                                                  <input type="text" name="username" id="username" class="form-control" placeholder="Username">
                                                                  {{-- <select name="username" id="username" class="form-control">
                                                                     <option value="">Select Username</option>
                                                                  </select> --}}
                                                               </div>
                                                         </div>
                                                         <div class="col-md-4">
                                                               <div class="form-group">
                                                                  <input type="text" name="description" id="description" class="form-control" placeholder="Description">
                                                               </div>
                                                         </div>
                                                         <div class="col-md-2">
                                                               <div class="form-group">
                                                                  <input type="text" name="ipaddress" id="ipaddress" class="form-control" placeholder="IP Address">
                                                               </div>
                                                         </div>
                                                         <div class="col-md-2">
                                                               <div class="form-group">
                                                                  <button type="submit" class="btn btn-primary btn-block waves-effect waves-light font-weight-bold">
                                                                     <i class="ri-search-line mr-2"></i> Search
                                                                  </button>
                                                               </div>
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
                                          <table id="activitylog" class="table dt-responsive">
                                             <thead>
                                                   <tr>
                                                      <th>Date</th>
                                                      <th>Description</th>
                                                      <th>Username</th>
                                                      <th>Ip Adrress</th>
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
       <!-- Date Picker -->
   <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
      $(document).ready(function () {
         $('#input-date').datepicker();
         var activitylog=function(filter=[]){
            if (! $.fn.dataTable.isDataTable('#transactionlist') ) {
               var date=$('#datefilter').val();
               var username=$('#username').val();
               var description=$('#description').val();
               var ipaddress=$('#ipaddress').val();
                //var status=$('#status').val();

                var tbl =$('#activitylog').DataTable({
                            paging: true,
                            processing: true,
                            serverSide: true,
                          
                            ajax:{
                                url : route('admin.activitylog.ajax'),
                                type: 'POST',
                                headers : {
                                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                                },
                                data : {
                                    date : date,
                                    username : username,
                                    description : description,
                                    resipaddressult : ipaddress,
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
                            
                              { data: 'date', name: 'date', width: '5%', defaultContent: 'N/A', className:'text-center', },
                              { data: 'description', name: 'description', width: '15%', defaultContent: 'N/A', },
                              { data: 'username', name: 'username', width: '10%', defaultContent: 'N/A', },
                              { data: 'ipaddr', name: 'ipaddr', width: '10%', className:'text-center', defaultContent: 'N/A', }
                            
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
                $('#activitylog').dataTable().fnDestroy();
                activitylog();
                return false;
            });

            activitylog();


      });
   </script> 

@endsection