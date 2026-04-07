@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  My Account</title>
@endsection

@section('content')
   <div class="main-content">
      <div class="page-content">
         <div class="container-fluid">

            <div class="row">
               <!-- Sidebar Shortcut -->

               <!-- End Sidebar -->

               <!-- MAIN CARD -->

               @if ($message = Session::get('error'))
                  <div class="col-lg-12">
                     <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h4 class="alert-heading">Something went wrong!</h4>
                        <small>{{ $message }}</small>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div>
               @endif

               <div class="col-xl-12">
                  <div class="view-client-wrapper">
                     <div class="row">
                        <div class="col-12">
                           <div class="card-title mb-3">
                              <h4 class="mb-3">My Account</h4>
                           </div>
                        </div>
                     </div>

                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <form class="w-100" action="{{ route('admin.pages.myaccount.update') }}"
                              method="POST" id="edit-profile-form">
                              <div class="card p-3">
                                 <div class="row">
                                    @csrf
                                    <input type="hidden" name="userId" value="{{ $user->id }}">
                                    <div class="col-lg-12">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Username</label>
                                          <div class="col-sm-12 col-lg-5 pt-2">
                                             <strong>{{ $user->username }}</strong>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Administrator
                                             Role</label>
                                          <div class="col-sm-12 col-lg-5 pt-2">
                                             <strong>{{ $roleName }}</strong>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">First
                                             Name</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" name="firstname" class="form-control"
                                                value="{{ $user->firstname }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Last
                                             Name</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" name="lastname" class="form-control"
                                                value="{{ $user->lastname }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Email
                                             Address</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" name="email" class="form-control"
                                                value="{{ $user->email }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label
                                             class="col-sm-12 col-lg-2 col-form-label d-flex align-items-center">Support
                                             Tickets
                                             Notifications</label>
                                          <div class="col-sm-12 col-lg-3">
                                             @foreach ($getSuppDept as $key => $ticket)
                                                <div class="custom-control custom-checkbox">
                                                   <input type="checkbox" class="custom-control-input"
                                                      id="techincalCheckHidden{{ $key }}" value="0">
                                                   <input type="checkbox" class="custom-control-input"
                                                      id="techincalCheck{{ $key }}" value="{{ $key }}">
                                                   <label class="custom-control-label"
                                                      for="techincalCheck{{ $key }}">{{ $ticket }}</label>
                                                </div>
                                             @endforeach
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="ticketSignature" class="col-sm-12 col-lg-2 col-form-label">Support
                                             Ticket
                                             Signature</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea class="form-control" name="signature" id="signature" cols="30"
                                                rows="5">{{ $user->signature }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="notes" class="col-sm-12 col-lg-2 col-form-label">My
                                             Notes</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea class="form-control" name="notes" id="notes" cols="30"
                                                rows="5">{{ $user->notes }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Template</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <select name="template" class="form-control" id="template">
                                                <option value="0" selected>None</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Language</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <select name="language" class="form-control" id="lang">
                                                <option value="EN">English</option>
                                                <option value="ID">Bahasa Indonesia</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Two-Factor Authentication
                                          </label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input class="d-inline mr-2" type="checkbox" data-toggle="toggle"
                                                data-onstyle="success" data-width="80">
                                             {{-- <p class="d-inline">Click here to Enable</p> --}}
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <hr>
                                 <div class="row">
                                    <div class="col-lg-12">
                                       <h6 class="mb-4">Enter only if you want to change the password</h6>
                                       <div class="form-group row">
                                          <label for="passwordConf"
                                             class="col-sm-12 col-lg-2 col-form-label">Password</label>
                                          <div class="col-sm-12 col-lg-4">
                                             <input type="password" name="password" id="password" class="form-control">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="againPasswordConf" class="col-sm-12 col-lg-2 col-form-label">Confirm
                                             Password</label>
                                          <div class="col-sm-12 col-lg-4">
                                             <input type="password" name="password2" id="password2" class="form-control">
                                             <div id="message" class="mt-2"></div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 {{-- <div class="row">
                              <div class="col-lg-12">
                                 <h6 class="mb-4">Please confirm your admin password to add or make changes
                                    to administrator account details</h6>
                                 <div class="form-group row">
                                    <label class="col-sm-12 col-lg-2 col-form-label" for="currentPassword">Confirm
                                       Password</label>
                                    <div class="col-sm-12 col-lg-4">
                                       <input type="text" name="currentpassword" id="currentPassword"
                                          class="form-control">
                                    </div>
                                 </div>
                              </div>
                           </div> --}}
                                 {{-- <hr> --}}
                                 <div class="text-center">
                                    <button type="submit" class="btn btn-success px-2" id="btn-save">Save
                                       Changes</button>
                                    <a href="{{ route('admin.pages.dashboard.index') }}">
                                       <button class="btn btn-light px-2">Cancel Changes</button>
                                    </a>
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
   <script src="{{ Theme::asset('assets/libs/bootstrap-switch-custom/bootstrap4-toggle.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

   <script type="text/javascript">
      $('#password, #password2').on('keyup', function() {
         passChecker();
      });

      async function passChecker() {
         let pass;
         if (!$('#password').val()) {
            $('#message').html('<i class="fas fa-times-circle mr-2"></i>Password Not Match').css('color', 'red');
            pass = false;
         } else if ($('#password').val() == "" && !$('#password2').val() || $('#password').val().length <= 1) {
            $('#message').html('<i class="fas fa-times-circle mr-2"></i>Confirm Your Password').css('color', 'red');
            pass = false;
         } else if ($('#password').val() == $('#password2').val() && $('#password, #password2').val().length >= 6) {
            $('#message').html('<i class="fas fa-check-circle mr-2"></i>Password Match').css('color', 'green');
            pass = true;
         } else if ($('#password, #password2').val().length < 6 ) {
            $('#message').html('<i class="fas fa-times-circle mr-2"></i>Password must be 6 character minimum.').css('color', 'red');
            pass = false;
         } else {
            $('#message').html('<i class="fas fa-times-circle mr-2"></i>Password Not Match').css('color', 'red');
            pass = false;
         }
         if (!pass) {
            $('#btn-save').attr('disabled', true);
         } else {
            $('#btn-save').removeAttr('disabled', true);
         }
         return pass;
      }
   </script>
@endsection
