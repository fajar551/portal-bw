@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Edit Role Group</title>
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
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <form
                              action="{{ route('admin.pages.setup.staffmanagement.administratorroles.update', $selectedRole->id) }}"
                              method="POST" id="AdminRolesForm">
                              @csrf
                              <div class="card p-3">
                                 <div class="form-group row">
                                    <label class="col-sm-12 col-lg-2 col-form-label">Name</label>
                                    <div class="col-sm-12 col-lg-6">
                                       <input type="text" name="name" id="rolename" class="form-control" placeholder=""
                                          aria-describedby="helpId" value="{{ $selectedRole->name }}">
                                    </div>
                                 </div>
                                 <div class="row justify-content-end mt-2">
                                    <div class="col-lg-4">
                                       <div class="custom-control custom-checkbox float-lg-right">
                                          <input type="checkbox" name="checkAll" class="custom-control-input checkAll"
                                             id="checkAll" onclick="check_uncheck_checkbox(this.checked);">
                                          <label for="checkAll" class="custom-control-label text-primary">Check All
                                             Permission </label>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label
                                       class="col-sm-12 col-lg-2 col-form-label d-flex align-items-center">Permissions</label>
                                    <div class="col-sm-12 col-lg-10">
                                       <div class="row">
                                          @foreach ($permissions as $id => $name)
                                             <div class="col-lg-4">
                                                <div class="custom-control custom-checkbox">
                                                   <input type="hidden" name="adminpermsdisable[]"
                                                      id="adminpermsHidden{{ $id + 1 }}" value="{{ $name }}">
                                                   <input type="checkbox" name="adminperms[]"
                                                      class="custom-control-input permsCheck"
                                                      id="adminperms{{ $id + 1 }}" value="{{ $name }}"
                                                      @foreach ($activePermissionList as $permsid => $permsname)
                                                   {{ $permsname == $name ? 'checked' : '' }}
                                          @endforeach
                                          >
                                          <label class="custom-control-label"
                                             for="adminperms{{ $id + 1 }}">{{ $name }}</label>
                                       </div>
                                    </div>
                                    @endforeach
                                 </div>
                              </div>
                        </div>
                        <div class="form-group row">
                           <label class="col-sm-12 col-lg-2 col-form-label">Reports Access Control</label>
                           <div class="col-sm-12 col-lg-10 pt-2">
                              <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1"
                                    value="option1">
                                 <label class="form-check-label" for="inlineRadio1">Unrestricted</label>
                              </div>
                              <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2"
                                    value="option2">
                                 <label class="form-check-label" for="inlineRadio2">Restrict
                                    Access</label>
                              </div>
                           </div>
                        </div>
                        <div class="form-group row">
                           <label class="col-sm-12 col-lg-2 col-form-label">Email Messages</label>
                           <div class="col-sm-12 col-lg-10">
                              <div class="custom-control custom-checkbox">
                                 <input type="hidden" name="systememails" class="custom-control-input"
                                    id="customCheck53Hidden" value="0">
                                 <input type="checkbox" name="systememails" class="custom-control-input" id="customCheck53"
                                    value="1" {{ $selectedRole->systememails == 1 ? 'checked' : '' }}>
                                 <label class="custom-control-label" for="customCheck53">System Emails
                                    (eg.
                                    Cron Notifications, Invalid Login Attempts, etc...)</label>
                              </div>
                              <div class="custom-control custom-checkbox">
                                 <input type="hidden" name="accountemails" class="custom-control-input"
                                    id="customCheck54Hidden" value="0">
                                 <input type="checkbox" name="accountemails" class="custom-control-input"
                                    id="customCheck54" value="1"
                                    {{ $selectedRole->accountemails == 1 ? 'checked' : '' }}>
                                 <label class="custom-control-label" for="customCheck54">Account Emails
                                    (eg.
                                    Order Confirmations, Details Changes, Automatic Setup Notifications,
                                    etc...)</label>
                              </div>
                              <div class="custom-control custom-checkbox">
                                 <input type="hidden" name="supportemails" class="custom-control-input"
                                    id="customCheck55Hidden" value="0">
                                 <input type="checkbox" name="supportemails" class="custom-control-input"
                                    id="customCheck55" value="1"
                                    {{ $selectedRole->supportemails == 1 ? 'checked' : '' }}>
                                 <label class="custom-control-label" for="customCheck55">Support Emails
                                    (eg.
                                    New Ticket & Ticket Reply Notifications)</label>
                              </div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-lg-12 d-flex justify-content-center">
                              <button type="submit" class="btn btn-success px-2 mx-1" id="btnUpdateAdminRoles">
                                 Save Changes
                              </button>
                              <a href="{{ route('admin.pages.setup.staffmanagement.administratorroles.index') }}">
                                 <button type="button" class="btn btn-light px-2 mx-1">
                                    Cancel Changes
                                 </button>
                              </a>
                           </div>
                        </div>
                        </form>
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
   <script src="{{ Theme::asset('assets/js/submit-btn.js') }}"></script>
   <script type="text/javascript">
      function check_uncheck_checkbox(isChecked) {
         if (isChecked) {
            $('.permsCheck').each(function() {
               this.checked = true;
            });
         } else {
            $('.permsCheck').each(function() {
               this.checked = false;
            });
         }
      }
   </script>
@endsection
