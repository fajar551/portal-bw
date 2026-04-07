@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Administrator Users</title>
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
                              <h4 class="mb-3">Administrators</h4>
                           </div>
                        </div>
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
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <div class="card p-3">
                              <div class="row">
                                 <div class="col-lg-8 d-flex align-items-center">
                                    <h6>This is where you configure the users which you want to allow to access
                                       the admin
                                       area.</h6>
                                 </div>
                                 <div class="col-lg-4">
                                    <a href="{{ url('admin/setup/staffmanagement/administratorusers/add') }}">
                                       <button class="btn btn-outline-success px-2 float-lg-right">
                                          <i class="fas fa-user-plus mr-2"></i>Add New Administrator
                                       </button>
                                    </a>
                                 </div>
                              </div>
                              <div class="row mt-3">
                                 <div class="col-lg-12">
                                    <h5>Active Administrator</h5>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-lg-12">
                                    
                                 </div>
                                 <div class="col-lg-12">
                                 </div>
                                 <div class="col-lg-12">
                                    <div class="table-responsive">
                                       <table id="datatable" class="table table-bordered dt-responsive w-100">
                                          <thead>
                                             <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email Address</th>
                                                <th>Username</th>
                                                <th>Admin Role</th>
                                                <th>Assigned Departments</th>
                                                <th>Actions</th>
                                             </tr>
                                          </thead>
                                          <tbody>
                                             <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </div>
                                 </div>
                              </div>
                              <div class="row mt-5">
                                 <div class="col-lg-12">
                                    <h5>Inactive Administrator</h5>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-lg-12">
                                    <div class="table-responsive">
                                       <table id="datatable2" class="table table-bordered dt-responsive w-100">
                                          <thead>
                                             <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email Address</th>
                                                <th>Username</th>
                                                <th>Admin Role</th>
                                                <th>Assigned Departments</th>
                                                <th>Actions</th>
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
   {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}
   <script type="text/javascript">
      let dtTable;
      let dtTable2;

      function ConfirmDelete(url) {

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
                  // .then(setTimeout(function () {
                  //     location.reload()
                  // }, 2000))
                  .then(location.reload())
                  .catch((error) => {
                     Swal.showValidationMessage(`Request failed: ${error}`);
                  });
            },
            allowOutsideClick: () => !Swal.isLoading(),
         });
      }

      $(() => {

         dtIndex();
         dtIndex2();

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
               url: "{{ route('admin.pages.setup.staffmanagement.administratorusers.dtActiveAdmin') }}",
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
                  width: '5%',
                  orderable: false,
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'email',
                  name: 'email',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'username',
                  name: 'username',
                  width: '2%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'roleName',
                  name: 'roleName',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'assignedDepts',
                  name: 'assignedDepts',
                  width: '15%',
                  className: 'text align-middle-left',
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
         dtTable2 = $('#datatable2').DataTable({
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
               url: "{{ route('admin.pages.setup.staffmanagement.administratorusers.dtDisabledAdmin') }}",
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
                  width: '5%',
                  orderable: false,
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'email',
                  name: 'email',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'username',
                  name: 'username',
                  width: '2%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'roleName',
                  name: 'roleName',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'assignedDepts',
                  name: 'assignedDepts',
                  width: '15%',
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
