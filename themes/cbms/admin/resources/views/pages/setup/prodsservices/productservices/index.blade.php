@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} - Products/Services</title>
@endsection

@section('content')
   <div class="main-content">
      <div class="page-content">
         <div class="container-fluid">

            <div class="row">

               <!-- MAIN CARD -->
               <div class="col-xl-12">
                  <div class="view-client-wrapper">
                     <div class="row">
                        <div class="col-12">
                           <div class="card-title mb-3">
                              <h4 class="mb-3">Products/Services</h4>
                           </div>
                        </div>
                        <div class="col-12">
                           @if ($message = Session::get('success'))
                              <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Success!</h5>
                                 <small>{{ $message }}</small>
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                 </button>
                              </div>
                           @endif
                           @if ($message = Session::get('error'))
                              <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Access Resricted!</h5>
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
                              <div class="row">
                                 <div class="col-lg-12">
                                    <p>This is where you configure all your products and services. Each product must be
                                       assigned to a group which can either be visible or hidden from the order page
                                       (products may also be hidden individually). A product which is in a hidden group can
                                       still be ordered using the Direct Order Link shown when editing the package.
                                    </p>
                                 </div>
                                 <div class="col-lg-12">
                                    <a href="{{ url('admin/setup/productservices/creategroup') }}">
                                       <button class="btn btn-outline-success px"><i class="fa fa-plus mr-2"
                                             aria-hidden="true"></i>Create a New Group</button>
                                    </a>
                                    <a href="{{ url('admin/setup/productservices/createproduct') }}">
                                       <button class="btn btn-outline-success px"><i class="fa fa-plus-circle mr-2"
                                             aria-hidden="true"></i>Create a
                                          New Product</button>
                                    </a>
                                    <a href="{{ url('admin/setup/productservices/duplicateproduct') }}">
                                       <button class="btn btn-outline-success px"><i class="fa fa-plus-square mr-2"
                                             aria-hidden="true"></i>Duplicate a Product</button>
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
                                                <th class="text-center">Product Name</th>
                                                <th>Type</th>
                                                <th>GID</th>
                                                <th>Pay Type</th>
                                                <th>Stock</th>
                                                <th>Auto Setup</th>
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
   <!-- Required datatable js -->
   <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-rowGroup-1.1.3/js/dataTables.rowGroup.min.js') }}"></script>
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
   <script src="{{ Theme::asset('assets/libs/datatables.net-rowGroup-1.1.3/js/rowGroup.bootstrap4.min.js') }}"></script>
   <!-- Responsive examples -->
   <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>
   {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}
   <script type="text/javascript">
      let dtTable;

      const editProduct = (id) => {
         const url = route('admin.pages.setup.prodsservices.productservices.createproduct.edit.prods', id);
         window.location.replace(url);
      }

      const ConfirmDelete = (id) => {
         const url = route('admin.pages.setup.prodsservices.productservices.createproduct.delete.prods', id)
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

      const deleteGroup = (id) => {
         const url = route('admin.pages.setup.prodsservices.productservices.deletegroup', id)
        //  window.location.replace(url);
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
                  method: "POST",
                  headers: {
                     "Content-Type": "application/json",
                     "X-CSRF-TOKEN": "{{csrf_token()}}",
                  },
               };
               return fetch(
                     url,
                     options
                  )
                  .then(response => response.json())
                  .then(async result => {
                    if (result.message == 'OK') {
                        Swal.fire(
                           "Deleted!",
                           result.text,
                           "success"
                        );
                     }
                     await dtIndex()
                  })
                  .catch((error) => {
                     Swal.showValidationMessage(`Request failed: ${error}`);
                  });
            },
            allowOutsideClick: () => !Swal.isLoading(),
         });
      }

      $(() => {
         dtIndex();
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
            orderFixed: [3, 'asc'],
            rowGroup: {
               dataSrc: 'group_name'
            },
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
               url: "{!! route('admin.pages.setup.prodsservices.productservices.dtProducts') !!}",
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
                  width: '10%',
                  className: 'text-left align-middle',
                  defaultContent: 'Product Not Found On This Group',
                  orderable: false,
               },
               {
                  data: 'type',
                  name: 'type',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: '-',
                  orderable: false,
               },
               {
                  data: 'id_group',
                  name: 'id_group',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
                  visible: false,
                  orderable: true,
               },
               {
                  data: 'paytype',
                  name: 'paytype',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
                  orderable: false,
               },
               {
                  data: 'stockcontrol',
                  name: 'stockcontrol',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: '-',
                  orderable: false,
               },
               {
                  data: 'autosetup',
                  name: 'autosetup',
                  width: '5%',
                  className: 'text-center align-middle',
                  defaultContent: '-',
                  orderable: false,
               },
               {
                  data: 'actions',
                  name: 'actions',
                  width: '2%',
                  className: 'text-center align-middle',
                  orderable: false,
                  searchable: false,
                  defaultContent: '-',
               },
            ]
         })
      }
   </script>
@endsection
