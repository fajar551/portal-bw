@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Email Templates</title>
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
                              <h4 class="mb-3">Email Templates</h4>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           @if ($message = Session::get('success'))
                              <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Success!</h5>
                                 <small>{{ $message }}</small>
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                 </button>
                              </div>
                           @endif
                           <div class="card p-3">
                              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam omnis
                                 expedita iste, rerum earum nemo sed vitae repudiandae, neque eaque, distinctio
                                 at nostrum ullam molestiae culpa qui laborum officiis ipsa?</p>
                              <div class="row">
                                 <div class="col-lg-12">
                                    <button type="button" class="btn btn-outline-success px-2" data-toggle="modal"
                                       data-target="#modelId"><i class="fa fa-plus mr-2" aria-hidden="true"></i> Create New
                                       Email
                                       Template</button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="modelId" tabindex="-1" role="dialog"
                                       aria-labelledby="modelTitleId" aria-hidden="true">
                                       <div class="modal-dialog modal-lg" role="document">
                                          <div class="modal-content">
                                             <div class="modal-header " style="background-color: #252B3B">
                                                <h5 class="modal-title text-white">Create New Email Template
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                   aria-label="Close">
                                                   <span aria-hidden="true">&times;</span>
                                                </button>
                                             </div>
                                             <div class="modal-body">
                                                <form action="{{ route('admin.pages.setup.emailtemplates.create') }}"
                                                   method="POST">
                                                   @csrf
                                                   <div class="form-group">
                                                      <label for="type">Email Type</label>
                                                      <select name="type" class="form-control" id="">
                                                         <option value="general">General</option>
                                                         <option value="product">Product/Service</option>
                                                         <option value="domain">Domain</option>
                                                         <option value="invoice">Invoice</option>
                                                         <option value="notification">Notification
                                                         </option>
                                                      </select>
                                                   </div>
                                                   <div class="form-group">
                                                      <label for="name">Unique Name</label>
                                                      <input type="text" name="name" class="form-control">
                                                   </div>
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="checkbox" class="custom-control-input" id="customCheck1"
                                                         name="custom" value="1" checked>
                                                      <label class="custom-control-label" for="customCheck1">Custom
                                                         Email?</label>
                                                   </div>
                                             </div>
                                             <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                   data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-success px-3">Create</button>
                                             </div>
                                             </form>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-lg-6 col-sm-12">
                                    <div class="row">
                                       {{-- GENERAL MESSAGE TABLE --}}
                                       <div class="col-lg-12">
                                          <h4 class="card-title my-3">General Messages</h4>
                                          <div class="table responsive">
                                             <table class="table table-bordered">
                                                <thead>
                                                   <tr>
                                                      <th class="w-25">Status</th>
                                                      <th class="w-100">Template Name</th>
                                                      <th class="w-100"></th>
                                                      <th class="w-100"></th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   @foreach ($generalTemplates as $template)
                                                      <tr>
                                                         <td class="text-success"><i class="fa fa-check-circle"
                                                               aria-hidden="true"></i></td>
                                                         <td>
                                                            @if ($template['custom'] == 1)
                                                               {{ $template['name'] }}<span
                                                                  class="ml-2 p-1 badge badge-danger">CUSTOM</span>
                                                            @else
                                                               {{ $template['name'] }}
                                                            @endif
                                                         </td>
                                                         <td class="text-dark text-center">
                                                            <a
                                                               href="{{ route('admin.pages.setup.emailtemplates.edit', $template['id']) }}">
                                                               <i class="fas fa-edit"></i>
                                                            </a>
                                                         </td>
                                                         <td class="text-center">
                                                             @if ($template['custom'])
                                                                <a onclick="ConfirmDelete('{{ route('admin.pages.setup.emailtemplates.delete', $template['id']) }}')"
                                                                    href="#"
                                                                    class="deleteSwal text-danger text-decoration-none">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                             @endif
                                                         </td>
                                                      </tr>
                                                   @endforeach
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                       {{-- INVOICES MESSAGES --}}
                                       <div class="col-lg-12">
                                          <h4 class="card-title my-3">Invoices Messages</h4>
                                          <div class="table responsive">
                                             <table class="table table-bordered">
                                                <thead>
                                                   <tr>
                                                      <th class="w-25">Status</th>
                                                      <th class="w-100">Template Name</th>
                                                      <th class="w-100"></th>
                                                      <th class="w-100"></th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   @foreach ($invoiceTemplates as $template)

                                                      <tr>
                                                         <td class="text-success"><i class="fa fa-check-circle"
                                                               aria-hidden="true"></i></td>
                                                         <td>
                                                            @if ($template['custom'] == 1)
                                                               {{ $template['name'] }}<span
                                                                  class="ml-2 p-1 badge badge-danger">CUSTOM</span>
                                                            @else
                                                               {{ $template['name'] }}
                                                            @endif
                                                         </td>
                                                         <td class="text-dark text-center">
                                                            <a
                                                               href="{{ route('admin.pages.setup.emailtemplates.edit', $template['id']) }}">
                                                               <i class="fas fa-edit"></i>
                                                            </a>
                                                         </td>
                                                         <td class="text-center">
                                                             @if ($template['custom'])
                                                                <a onclick="ConfirmDelete('{{ route('admin.pages.setup.emailtemplates.delete', $template['id']) }}')"
                                                                    href="#"
                                                                    class="deleteSwal text-danger text-decoration-none">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                             @endif
                                                         </td>
                                                      </tr>
                                                   @endforeach
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                       {{-- SUPPORT MESSAGES --}}
                                       <div class="col-lg-12">
                                          <h4 class="card-title my-3">Support Messages</h4>
                                          <div class="table responsive">
                                             <table class="table table-bordered">
                                                <thead>
                                                   <tr>
                                                      <th class="w-25">Status</th>
                                                      <th class="w-100">Template Name</th>
                                                      <th class="w-100"></th>
                                                      <th class="w-100"></th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   @foreach ($supportTemplates as $template)
                                                      <tr>
                                                         <td class="text-success"><i class="fa fa-check-circle"
                                                               aria-hidden="true"></i></td>
                                                         <td>
                                                            @if ($template['custom'] == 1)
                                                               {{ $template['name'] }}<span
                                                                  class="ml-2 p-1 badge badge-danger">CUSTOM</span>
                                                            @else
                                                               {{ $template['name'] }}
                                                            @endif
                                                         </td>
                                                         <td class="text-dark text-center">
                                                            <a
                                                               href="{{ route('admin.pages.setup.emailtemplates.edit', $template['id']) }}">
                                                               <i class="fas fa-edit"></i>
                                                            </a>
                                                         </td>
                                                         <td class="text-center">
                                                            @if ($template['custom'])
                                                                <a onclick="ConfirmDelete('{{ route('admin.pages.setup.emailtemplates.delete', $template['id']) }}')"
                                                                    href="#"
                                                                    class="deleteSwal text-danger text-decoration-none">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                            @endif
                                                         </td>
                                                      </tr>
                                                   @endforeach
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                       {{-- NOTIFICATION MESSAGES --}}
                                       <div class="col-lg-12">
                                          <h4 class="card-title my-3">Notification Messages</h4>
                                          <div class="table responsive">
                                             <table class="table table-bordered">
                                                <thead>
                                                   <tr>
                                                      <th class="w-25">Status</th>
                                                      <th class="w-100">Template Name</th>
                                                      <th class="w-100"></th>
                                                      <th class="w-100"></th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   @foreach ($notificationTemplates as $template)
                                                      <tr>
                                                         <td class="text-success"><i class="fa fa-check-circle"
                                                               aria-hidden="true"></i></td>
                                                         <td>
                                                            @if ($template['custom'] == 1)
                                                               {{ $template['name'] }}<span
                                                                  class="ml-2 p-1 badge badge-danger">CUSTOM</span>
                                                            @else
                                                               {{ $template['name'] }}
                                                            @endif
                                                         </td>
                                                         <td class="text-dark text-center">
                                                            <a
                                                               href="{{ route('admin.pages.setup.emailtemplates.edit', $template['id']) }}">
                                                               <i class="fas fa-edit"></i>
                                                            </a>
                                                         </td>
                                                         <td class="text-center">
                                                             @if ($template['custom'])    
                                                                <a onclick="ConfirmDelete('{{ route('admin.pages.setup.emailtemplates.delete', $template['id']) }}')"
                                                                    href="#"
                                                                    class="deleteSwal text-danger text-decoration-none">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                             @endif
                                                         </td>
                                                      </tr>
                                                   @endforeach
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-lg-6 col-sm-12">
                                    <div class="row">
                                       {{-- PRODUCT/SERVICE MESSAGES --}}
                                       <div class="col-lg-12">
                                          <h4 class="card-title my-3">Product/Service Messages</h4>
                                          <div class="table responsive">
                                             <table class="table table-bordered">
                                                <thead>
                                                   <tr>
                                                      <th class="w-25">Status</th>
                                                      <th class="w-100">Template Name</th>
                                                      <th class="w-100"></th>
                                                      <th class="w-100"></th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   @foreach ($productTemplates as $template)
                                                      <tr>
                                                         <td class="text-success"><i class="fa fa-check-circle"
                                                               aria-hidden="true"></i></td>
                                                         <td>
                                                            @if ($template['custom'] == 1)
                                                               {{ $template['name'] }}<span
                                                                  class="ml-2 p-1 badge badge-danger">CUSTOM</span>
                                                            @else
                                                               {{ $template['name'] }}
                                                            @endif
                                                         </td>
                                                         <td class="text-dark text-center">
                                                            <a
                                                               href="{{ route('admin.pages.setup.emailtemplates.edit', $template['id']) }}">
                                                               <i class="fas fa-edit"></i>
                                                            </a>
                                                         </td>
                                                         <td class="text-center">
                                                             @if ($template['custom'])
                                                                <a onclick="ConfirmDelete('{{ route('admin.pages.setup.emailtemplates.delete', $template['id']) }}')"
                                                                    href="#"
                                                                    class="deleteSwal text-danger text-decoration-none">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                             @endif
                                                         </td>
                                                      </tr>
                                                   @endforeach
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                       {{-- DOMAIN MESSAGES --}}
                                       <div class="col-lg-12">
                                          <h4 class="card-title my-3">Domain Messages</h4>
                                          <div class="table responsive">
                                             <table class="table table-bordered">
                                                <thead>
                                                   <tr>
                                                      <th class="w-25">Status</th>
                                                      <th class="w-100">Template Name</th>
                                                      <th class="w-100"></th>
                                                      <th class="w-100"></th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   @foreach ($domainTemplates as $template)
                                                      <tr>
                                                         <td class="text-success"><i class="fa fa-check-circle"
                                                               aria-hidden="true"></i></td>
                                                         <td>
                                                            @if ($template['custom'] == 1)
                                                               {{ $template['name'] }}<span
                                                                  class="ml-2 p-1 badge badge-danger">CUSTOM</span>
                                                            @else
                                                               {{ $template['name'] }}
                                                            @endif
                                                         </td>
                                                         <td class="text-dark text-center">
                                                            <a
                                                               href="{{ route('admin.pages.setup.emailtemplates.edit', $template['id']) }}">
                                                               <i class="fas fa-edit"></i>
                                                            </a>
                                                         </td>
                                                         <td class="text-center">
                                                             @if ($template['custom'])
                                                                <a onclick="ConfirmDelete('{{ route('admin.pages.setup.emailtemplates.delete', $template['id']) }}')"
                                                                    href="#"
                                                                    class="deleteSwal text-danger text-decoration-none">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                             @endif
                                                         </td>
                                                      </tr>
                                                   @endforeach
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                       {{-- ADMIN MESSAGES --}}
                                       <div class="col-lg-12">
                                          <h4 class="card-title my-3">Admin Messages</h4>
                                          <div class="table responsive">
                                             <table class="table table-bordered">
                                                <thead>
                                                   <tr>
                                                      <th class="w-25">Status</th>
                                                      <th class="w-100">Template Name</th>
                                                      <th class="w-100"></th>
                                                      <th class="w-100"></th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   @foreach ($adminTemplates as $template)
                                                      <tr>
                                                         <td class="text-success"><i class="fa fa-check-circle"
                                                               aria-hidden="true"></i></td>
                                                         <td>
                                                            @if ($template['custom'] == 1)
                                                               {{ $template['name'] }}<span
                                                                  class="ml-2 p-1 badge badge-danger">CUSTOM</span>
                                                            @else
                                                               {{ $template['name'] }}
                                                            @endif
                                                         </td>
                                                         <td class="text-dark text-center">
                                                            <a
                                                               href="{{ route('admin.pages.setup.emailtemplates.edit', $template['id']) }}">
                                                               <i class="fas fa-edit"></i>
                                                            </a>
                                                         </td>
                                                         <td class="text-center">
                                                             @if ($template['custom'])
                                                                <a onclick="ConfirmDelete('{{ route('admin.pages.setup.emailtemplates.delete', $template['id']) }}')"
                                                                    href="#"
                                                                    class="deleteSwal text-danger text-decoration-none">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                             @endif
                                                         </td>
                                                      </tr>
                                                   @endforeach
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                       {{-- AFFILIATES MESSAGES --}}
                                       <div class="col-lg-12">
                                          <h4 class="card-title my-3">Affiliates Messages</h4>
                                          <div class="table responsive">
                                             <table class="table table-bordered">
                                                <thead>
                                                   <tr>
                                                      <th class="w-25">Status</th>
                                                      <th class="w-100">Template Name</th>
                                                      <th class="w-100"></th>
                                                      <th class="w-100"></th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   @foreach ($affiliatesTemplates as $template)
                                                      <tr>
                                                         <td class="text-success"><i class="fa fa-check-circle"
                                                               aria-hidden="true"></i></td>
                                                         <td>
                                                            @if ($template['custom'] == 1)
                                                               {{ $template['name'] }}<span
                                                                  class="ml-2 p-1 badge badge-danger">CUSTOM</span>
                                                            @else
                                                               {{ $template['name'] }}
                                                            @endif
                                                         </td>
                                                         <td class="text-dark text-center">
                                                            <a
                                                               href="{{ route('admin.pages.setup.emailtemplates.edit', $template['id']) }}">
                                                               <i class="fas fa-edit"></i>
                                                            </a>
                                                         </td>
                                                         <td class="text-center">
                                                             @if ($template['custom'])
                                                                <a onclick="ConfirmDelete('{{ route('admin.pages.setup.emailtemplates.delete', $template['id']) }}')"
                                                                    href="#"
                                                                    class="deleteSwal text-danger text-decoration-none">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                             @endif
                                                         </td>
                                                      </tr>
                                                   @endforeach
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
                  </div>
               </div>
               <!-- End MAIN CARD -->
            </div>
         </div>
      </div>
   </div>
@endsection

@section('scripts')
   <script>
      function ConfirmDelete(url) {
         const csrf = $('meta[name="csrf-token"]').attr("content");
         console.log(url);
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
                  method: "GET",
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
   </script>
@endsection
