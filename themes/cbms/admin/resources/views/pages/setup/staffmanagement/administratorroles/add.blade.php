@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Add New Role Group</title>
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
                              <h4 class="mb-3">Administrator Roles</h4>
                           </div>
                           @if ($message = Session::get('error'))
                              <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Something went wrong!</h5>
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
                           <form action="{{route('admin.pages.setup.staffmanagement.administratorroles.create')}}"
                              method="POST">
                              @csrf
                              <div class="card p-3">
                                 <h6>Add New Role Group</h6>
                                 <hr>
                                 <div class="form-group row">
                                    <label class="col-sm-12 col-lg-2 col-form-label">Name</label>
                                    <div class="col-sm-12 col-lg-10">
                                       <input type="text" name="name" id="" class="form-control" placeholder=""
                                          aria-describedby="helpId">
                                    </div>
                                    <div class="col-sm-12 col-lg-12 d-flex justify-content-center pt-3">
                                       <a href="{{ url('admin/setup/staffmanagement/administratorroles/edit') }}">
                                          <button class="btn btn-success px-3">
                                             Continue
                                          </button>
                                       </a>
                                    </div>
                                 </div>
                              </div>
                           </form>
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
