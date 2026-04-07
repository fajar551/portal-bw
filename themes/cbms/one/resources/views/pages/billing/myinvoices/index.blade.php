@extends('layouts.clientbase')

@section('title')
   My Invoices
@endsection

@section('page-title')
   My Invoices
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         <div class="row pb-3">
            <div class="col-xl-12 col-lg-12">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mt-2"><a
                    href="{{ url('/home') }}">{{ __('client.clientareanavdashboard') }}</a> <span
                        class="text-muted"> / My Invoices</span></h6>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-xl-3 col-lg-6">
               <div class="card">
                  <div class="card-body">
                     <div class="row align-items-center">
                        <div class="col">
                           <h6 class="text-uppercase font-size-12 text-muted mb-3">Paid</h6>
                           <span class="h3 mb-0 text-success"> {{ $getInvoice->where('status', 'Paid')->count() }}
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
                           <h6 class="text-uppercase font-size-12 text-muted mb-3">Unpaid</h6>
                           <span class="h3 mb-0 text-danger"> {{ $getInvoice->where('status', 'Unpaid')->count() }}
                           </span>
                        </div>
                        <div class="col-auto ic-card">
                           <i class="feather-alert-circle text-danger opacity-1"></i>
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
                           <h6 class="text-uppercase font-size-12 text-muted mb-3">Canceled</h6>
                           <span class="h3 mb-0 text-info"> {{ $getInvoice->where('status', 'Cancelled')->count() }}
                           </span>
                        </div>
                        <div class="col-auto ic-card">
                           <i class="feather-x-circle text-info opacity-1"></i>
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
                           <h6 class="text-uppercase font-size-12 text-muted mb-3">Refunded</h6>
                           <span class="h3 mb-0 text-warning"> {{ $getInvoice->where('status', 'Refunded')->count() }}
                           </span>
                        </div>
                        <div class="col-auto ic-card">
                           <i class="feather-credit-card text-warning opacity-1"></i>
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
                     <h6 class="mb-3">My Invoice List</h6>
                     <div class="table-responsive">
                        <table id="myInvoice" class="table">
                           <thead>
                              <tr>
                                 <th>No</th>
                                 <th>No. Invoice</th>
                                 <th>Invoice Date</th>
                                 <th>Due Date</th>
                                 <th>Total</th>
                                 <th>Status</th>
                                 <th>Action</th>
                              </tr>
                           </thead>
                           <tbody>
                             
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
         dtTable = $('#myInvoice').DataTable({
            columnDefs: [{
               "width": "1%",
               "targets": 0
            }],
            stateSave: true,
            processing: true,
            responsive: true,
            serverSide: true,
            autoWidth: false,
            searching: true,
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
               url: "{!! route('dt_myInvoices') !!}",
               type: "GET",
            },
            columns: [{
                  data: 'DT_RowIndex',
                  name: 'DT_RowIndex',
                  className: 'text-left align-middle',
                  visible: true,
                  orderable: false,
                  searchable: false,
               },
               {
                  data: 'invoicenum',
                  name: 'invoicenum',
                  className: 'text-left align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'date',
                  name: 'date',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'duedate',
                  name: 'duedate',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'total',
                  name: 'total',
                  className: 'text-left align-middle',
                  searchable: false,
                  defaultContent: 'Off',
               },
               {
                  data: 'status',
                  name: 'status',
                  className: 'text-left align-middle',
                  searchable: false,
                  defaultContent: 'Off',
               },
               {
                  data: 'actions',
                  name: 'actions',
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
