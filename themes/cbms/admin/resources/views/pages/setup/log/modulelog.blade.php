@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Module Debug Log</title>
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
                              <h4 class="mb-3"> Activity Module Debug Log </h4>
                        </div>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-lg-12">
                        <div class="card p-3">
                           <div class="row">
                              <div class="col-lg-12">
                                    <div class="table-responsive">
                                       <table id="activitylog" class="table dt-responsive">
                                          <thead>
                                                <tr>
                                                   <th>Date</th>
                                                   <th>Subject</th>
                                                   <th>Client</th>
                                                   <th>Message</th>
                                                   
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

                var tbl = $('#activitylog').DataTable({
                  paging: true,
                  processing: true,
                  serverSide: true,
                  ajax: {
                      url: route('admin.modulelog.ajax'),
                      type: 'POST',
                      headers: {
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      data: {
                          date: date,
                          username: username,
                          description: description,
                          resipaddressult: ipaddress,
                      },
                      dataType: 'json'
                  },
                  language: {
                      paginate: {
                          previous: "<i class='mdi mdi-chevron-left'>",
                          next: "<i class='mdi mdi-chevron-right'>"
                      },
                      searching: false
                  },
                  columns: [
                      { data: 'date', name: 'date', defaultContent: 'N/A' },
                      { data: 'subject', name: 'subject', defaultContent: 'N/A' },
                      { data: 'client', name: 'client', defaultContent: 'N/A' },
                      { data: 'message', name: 'message', defaultContent: 'N/A' }
                  ],
                  drawCallback: function() {
                      $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                  }
              });
            };
          }

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