@extends('layouts.clientbase')

@section('title')
   My Tickets
@endsection

@section('page-title')
   My Tickets
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         <div class="row pb-3">
            <div class="col-xl-12 col-lg-12">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mt-2"><a
                    href="{{ url('/home') }}">{{ __('client.clientareanavdashboard') }}</a> <span
                        class="text-muted"> / My Tickets</span></h6>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-xl-3 col-lg-6">
               <div class="card">
                  <div class="card-body">
                     <div class="row align-items-center">
                        <div class="col">
                           <h6 class="text-uppercase font-size-12 text-muted mb-3">Open</h6>
                           <span class="h3 mb-0 text-info"> {{ $getTicket->where('status', 'Open')->count() }} </span>
                        </div>
                        <div class="col-auto ic-card">
                           <i class="feather-book-open text-info opacity-1"></i>
                        </div>
                     </div> <!-- end row -->

                     <div id="sparkline1" class="mt-3"></div>
                  </div> <!-- end card-body-->
               </div> <!-- end card-->
            </div> <!-- end col-->

            <div class="col-xl-3 col-lg-6">
               <div class="card">
                  <div class="card-body">
                     <div class="row align-items-center">
                        <div class="col">
                           <h6 class="text-uppercase font-size-12 text-muted mb-3">Answered</h6>
                           <span class="h3 mb-0 text-success"> {{ $getTicket->where('status', 'Answered')->count() }}
                           </span>
                        </div>
                        <div class="col-auto ic-card">
                           <i class="feather-check text-success opacity-1"></i>
                        </div>
                     </div> <!-- end row -->

                     <div id="sparkline1" class="mt-3"></div>
                  </div> <!-- end card-body-->
               </div> <!-- end card-->
            </div> <!-- end col-->

            <div class="col-xl-3 col-lg-6">
               <div class="card">
                  <div class="card-body">
                     <div class="row align-items-center">
                        <div class="col">
                           <h6 class="text-uppercase font-size-12 text-muted mb-3">Replied</h6>
                           <span class="h3 mb-0 text-secondary">
                              {{ $getTicket->where('status', 'Customer-Reply')->count() }} </span>
                        </div>
                        <div class="col-auto ic-card">
                           <i class="feather-message-circle text-secondary opacity-1"></i>
                        </div>
                     </div> <!-- end row -->

                     <div id="sparkline1" class="mt-3"></div>
                  </div> <!-- end card-body-->
               </div> <!-- end card-->
            </div> <!-- end col-->

            <div class="col-xl-3 col-lg-6">
               <div class="card">
                  <div class="card-body">
                     <div class="row align-items-center">
                        <div class="col">
                           <h6 class="text-uppercase font-size-12 text-muted mb-3">Closed</h6>
                           <span class="h3 mb-0 text-danger"> {{ $getTicket->where('status', 'Closed')->count() }}
                           </span>
                        </div>
                        <div class="col-auto ic-card">
                           <i class="feather-x text-danger opacity-1"></i>
                        </div>
                     </div> <!-- end row -->

                     <div id="sparkline1" class="mt-3"></div>
                  </div> <!-- end card-body-->
               </div> <!-- end card-->
            </div> <!-- end col-->

         </div>
         <!-- end row-->

         <div class="row">

            <div class="col-xl-12 col-lg-12">
               <div class="card">
                  <div class="card-body">
                     <h4 class="card-title">My Ticket List</h4>
                     <div class="table-responsive">
                        <table id="mytickets" class="table dt-responsive w-100">
                           <thead>
                              <tr>
                                 <th>No</th>
                                 <th>Department</th>
                                 <th>Title</th>
                                 <th>Last Update</th>
                                 <th>Status</th>
                                 <th>Action</th>
                              </tr>
                           </thead>
                           <tbody>
                              {{-- <tr>
                                            <td>1</td>
                                            <td>Request tax invoice</td>
                                            <td><span class="font-weight-bold text-info">#45322</span> Confirmation of
                                                making
                                                tax invoice</td>
                                            <td><span class="badge badge-info">Open</span></td>
                                            <td>12 June 2021, 09:10</td>
                                            <td>
                                                <div class="row">
                                                    <a href="" class="btn btn-primary btn-sm">Detail</a>
                                                </div>
                                            </td>
                                        </tr> --}}
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!-- end row-->
      </div> <!-- container-fluid -->
   </div>
@endsection

@section('scripts')
   <script type="text/javascript">
      let dtTable;

      $(() => {
         dtIndex();
      })

      const dtIndex = () => {
         dtTable = $('#mytickets').DataTable({
            stateSave: true,
            processing: true,
            responsive: true,
            serverSide: true,
            autoWidth: false,
            searching: false,
            //    bInfo: false, //used to hide the property 
            destroy: true,
            language: {
               paginate: {
                  previous: "<i class='mdi mdi-chevron-left'>",
                  next: "<i class='mdi mdi-chevron-right'>",
               },
            },
            drawCallback: () => {
               $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
            },
            ajax: {
               url: "{!! route('dt_myTickets') !!}",
               type: "GET",
            },
            columns: [{
                  data: 'DT_RowIndex',
                  name: 'DT_RowIndex',
                  width: '1%',
                  className: 'text-left align-middle',
                  visible: true,
                  orderable: false,
                  searchable: false,
               },
               {
                  data: 'department',
                  name: 'department',
                  width: '2%',
                  className: 'text-left align-middle',
                  orderable: false,
                  defaultContent: 'N/A',
               },
               {
                  data: 'title',
                  name: 'title',
                  width: '5%',
                  className: 'text-left align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'date',
                  name: 'date',
                  width: '7%',
                  className: 'text-left align-middle',
                  searchable: false,
                  defaultContent: 'Off',
               },
               {
                  data: 'status',
                  name: 'status',
                  width: '1%',
                  className: 'text-left align-middle',
                  searchable: false,
                  defaultContent: 'Off',
               },
               {
                  data: 'actions',
                  name: 'actions',
                  width: '2%',
                  className: 'text-center align-middle',
                  orderable: false,
                  searchable: false,
                  defaultContent: 'N/A',
               },
            ]
         })
      }
   </script>
@endsection
