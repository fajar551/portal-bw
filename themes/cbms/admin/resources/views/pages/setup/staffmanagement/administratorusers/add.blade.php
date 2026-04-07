@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Add New Admin</title>
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
                     @if (session('message'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                           <h5>Something Went Wrong!</h5>
                           <small>{!! session('message') !!}</small>
                           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                           </button>
                        </div>
                     @endif
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <form action="{{ route('admin.pages.setup.staffmanagement.administratorusers.insert') }}"
                              method="POST" id="AdminForm">
                              @csrf
                              <div class="card p-3">
                                 <h6>Add New Admin</h6>
                                 <hr>
                                 <div class="row">
                                    <div class="col-lg-12">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Administrator
                                             Role</label>
                                          <div class="col-sm-12 col-lg-4">
                                             <select class="form-control" name="roleid">
                                                @foreach ($roleList as $key => $value)
                                                   <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">First Name</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" name="firstname"
                                                placeholder="e.g: John">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Last Name</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" name="lastname"
                                                placeholder="e.g: Doe">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Email Address</label>
                                          <div class="col-sm-12 col-lg-6">
                                             <input type="email" class="form-control" name="email"
                                                placeholder="example@mail.co.id">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Username</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" name="username">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Password</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="password" id="password" name="password" class="form-control">
                                             <small>Minimum 6 Characters</small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Confirm
                                             Password</label>
                                          <div class="col-sm-12 col-lg-3 d-flex">
                                             <input type="password" id="password2" name="password2" class="form-control"
                                                required>
                                          </div>
                                          <div class="col-7">
                                             <div class="mt-2" id="message"></div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Assigned
                                             Departments</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="row">
                                                @foreach ($suppDept as $id => $name)
                                                   <div class="col-lg-4">
                                                      <div class="custom-control custom-checkbox">
                                                         <input name="supportdepts[]" type="checkbox"
                                                            class="custom-control-input"
                                                            id="customCheck{{ $id }}"
                                                            value="{{ $id }}">
                                                         <label class="custom-control-label"
                                                            for="customCheck{{ $id }}">{{ $name }}</label>
                                                      </div>
                                                   </div>
                                                @endforeach
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Support Ticket
                                             Signature</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="signature" id="signature" cols="30" rows="5"
                                                class="form-control"></textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Private Notes</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="notes" id="notes" cols="30" rows="4"
                                                class="form-control"></textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Template</label>
                                          <div class="col-sm-12 col-lg-4">
                                             <select name="template" id="template" class="form-control">
                                                <option value="0">
                                                   Blend</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Language</label>
                                          <div class="col-sm-12 col-lg-4">
                                             <select name="language" id="language" class="form-control">
                                                <option value="0">
                                                   English</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Disable</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="disabled" class="custom-control-input"
                                                   id="disabledCheckHidden" value="0">
                                                <input type="checkbox" name="disabled" class="custom-control-input"
                                                   id="disabledCheck" value="1">
                                                <label class="custom-control-label" for="disabledCheck">Tick this
                                                   box to deactivate this account and prevent login (you cannot
                                                   disable your own account or the only admin</label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-lg-12 d-flex justify-content-center">
                                       <button type="submit" id="btnCreateAdmin" class="btn btn-success px-3 mx-1">
                                          Create New Administrator
                                       </button>
                                       <a
                                          href="{{ route('admin.pages.setup.staffmanagement.administratorusers.index') }}">
                                          <button type="button" class="btn btn-light px-3 mx-1">
                                             Cancel
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

@section('scripts')
   <script src="{{ Theme::asset('assets/js/submit-btn.js') }}"></script>
   <script type="text/javascript">
      $('#password, #password2').on('keyup', function() {
         if ($('#password').val() == "") {
            $('#message').html('<i class="fas fa-times-circle mr-2"></i>Password Is Required').css('color', 'red');
         } else if ($('#password').val() == $('#password2').val()) {
            $('#message').html('<i class="fas fa-check-circle mr-2"></i>Password Match').css('color', 'green');
         } else if (!$('#password2').val()) {
            $('#message').html('<i class="fas fa-times-circle mr-2"></i>Confirm Your Password').css('color', 'red');
         } else {
            $('#message').html('<i class="fas fa-times-circle mr-2"></i>Password Not Match').css('color', 'red');
         }
      });
   </script>
@endsection
