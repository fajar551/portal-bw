@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Servers</title>
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
                              <h4 class="mb-3">Servers</h4>
                           </div>
                           @if ($message = Session::get('success'))
                              <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Success!</h5>
                                 <small>{{ $message }}</small>
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                 </button>
                              </div>
                           @endif
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <div class="card p-3">
                              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Earum unde temporibus
                                 eligendi sed deserunt dolores reiciendis maiores optio officia. Laboriosam
                                 doloribus aperiam atque. Esse accusamus tempora ut fuga, similique blanditiis.
                              </p>
                              <div class="row">
                                 <div class="col-lg-12 d-flex align-items-center">
                                    <a href="{{ url('admin/setup/productservices/serverconfig/add') }}">
                                       <button class=" btn btn-outline-success px-3 mx-1"><i
                                             class="fas fa-solid fa-plus mr-2"></i>Add
                                          New Server</button>
                                    </a>
                                    <a href="{{ url('admin/setup/productservices/serverconfig/add-group') }}">
                                       <button class="btn btn-outline-success px-3 mx-1">
                                          <i class="far fa-plus-square mr-2"></i> Create New Group
                                       </button>
                                    </a>
                                 </div>
                              </div>
                              <div class="row my-3">
                                 <div class="col-lg-12">
                                    <div class="table-responsive">
                                       <table id="datatable" class="table table-bordered dt-responsive w-100">
                                          <thead>
                                             <tr>
                                                <th>ID</th>
                                                <th class="text-center">Server Name</th>
                                                <th>IP Address</th>
                                                <th>CBMS Usage Stats</th>
                                                <th>Remote Usage Stats</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                             </tr>
                                          </thead>
                                          <tbody>
                                             {{-- @foreach ($testGet as $server)
                                                <tr>
                                                   <td>{{$server->name}}</td>
                                                   <td>{{$server->ipaddress}}</td>
                                                </tr>
                                             @endforeach --}}
                                          </tbody>
                                       </table>
                                    </div>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-lg-12">
                                    <h5 class="my-3">Groups</h5>
                                    <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Laborum dolor
                                       libero culpa ducimus error adipisci possimus? Veritatis, similique
                                       blanditiis deserunt unde placeat totam, rerum illum quasi pariatur
                                       exercitationem nam ipsum?</p>
                                    <div class="table-responsive">
                                       <table id="dtServerGroup" class="table table-bordered">
                                          <thead>
                                             <tr>
                                                <th>ID</th>
                                                <th>Group Name</th>
                                                <th>Fill Type</th>
                                                <th class="text-center">Servers</th>
                                                <th>Actions</th>
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
               <!-- End MAIN CARD -->
            </div>
         </div>
      </div>
   </div>
@endsection

@section('scripts')
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
   <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>
   <script type="text/javascript">
      let dtTable;

      function ConfirmDelete(url) {
         console.log(url);
         const csrf = $('meta[name="csrf-token"]').attr("content");
         Swal.fire({
            title: "Are you sure?",
            html: `The <b>Data</b> will be deleted from database.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Delete!",
            showLoaderOnConfirm: true,
            preConfirm: () => {
               const options = {
                  method: "DELETE",
                  headers: {
                     "Content-Type": "application/json",
                     "X-CSRF-TOKEN": csrf,
                  },
               };
               return fetch(
                     url,
                     options
                  )
                  .then((response) => {
                     if (response) {
                        Swal.fire(
                           "Deleted!",
                           "Your file has been deleted.",
                           "success"
                        );
                     }
                  })
                  .then(setTimeout(function() {
                     location.reload()
                  }, 2000))
                  // .then(location.reload())
                  .catch((error) => {
                     Swal.showValidationMessage(`Request failed: ${error}`);
                  });
            },
            allowOutsideClick: () => !Swal.isLoading(),
         });
      }

      function updateActiveServer(url, active) {
         console.log(url, active);
         const csrf = $('meta[name="csrf-token"]').attr("content");
         let _data = {
            title: "Disable Server",
            options: active,
         }
         const options = {
            method: "POST",
            headers: {
               "Content-Type": "application/json",
               "X-CSRF-TOKEN": csrf,
            },
            body: JSON.stringify(_data),
         };
         fetch(url, options)
            .then((data) => {
               console.log('Success:', data);
               // location.reload();
            })
            .catch(error => {
               console.error('Error:', error);
            });
      }

      $(() => {

         dtIndex();
         dtIndex2();
         hideFormAdd();

         const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            timerProgressBar: true,
            timer: 3000,
         });


      });

      const dtIndex = () => {
         dtTable = $('#datatable').DataTable({
            stateSave: true,
            processing: true,
            responsive: true,
            serverSide: true,
            autoWidth: false,
            searching: false,
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
               url: "{!! route('admin.pages.setup.prodsservice.serverconfig.dtServers') !!}",
               type: "GET",
            },
            columns: [{
                  data: 'DT_RowIndex',
                  name: 'DT_RowIndex',
                  width: '2%',
                  className: 'text-center align-middle',
                  visible: false,
                  orderable: false,
                  searchable: false,
               },
               {
                  data: 'name',
                  name: 'name',
                  width: '7%',
                  className: 'text-left align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'ipaddress',
                  name: 'ipaddress',
                  width: '2%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'cbmsusagestats',
                  name: 'cbmsusagestats',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'remoteusagestats',
                  name: 'remoteusagestats',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'status',
                  name: 'status',
                  width: '1%',
                  className: 'text-center align-middle',
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

      const dtIndex2 = () => {
         dtTable = $('#dtServerGroup').DataTable({
            stateSave: true,
            processing: true,
            responsive: true,
            serverSide: true,
            autoWidth: false,
            searching: false,
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
               url: "{!! route('admin.pages.setup.prodsservice.serverconfig.dtServerGroup') !!}",
               type: "GET",
            },
            columns: [{
                  data: 'DT_RowIndex',
                  name: 'DT_RowIndex',
                  width: '2%',
                  className: 'text-center align-middle',
                  visible: false,
                  orderable: false,
                  searchable: false,
               },
               {
                  data: 'name',
                  name: 'name',
                  width: '2%',
                  className: 'text-left align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'filltype',
                  name: 'filltype',
                  width: '6%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'servers',
                  name: 'servers',
                  width: '10%',
                  className: 'text-left align-middle',
                  defaultContent: 'N/A',
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

      const hideFormAdd = () => {
         localStorage.removeItem('hiddenForm');
      }
   </script>
@endsection
