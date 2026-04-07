@extends('layouts.clientbase')

@section('title')
   Update Password
@endsection

@section('page-title')
   Update Password
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         <div class="row pb-3">
            <div class="col-xl-8 col-lg-8">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mt-2"><a
                    href="{{ url('/home') }}">{{ __('client.clientareanavdashboard') }}</a> <span
                        class="text-muted"> / Update Password</span></h6>
               </div>
            </div>
         </div>

         <div class="row">
            <div class="col-xl-12 col-lg-12">
               <div class="card">
                  <div class="card-body">
                     @if (session('success_pass'))
                        <div class="alert alert-success">
                           <button type="button" class="close" data-dismiss="alert">×</button>
                           <h5>Password Updated!</h5>
                           <p class="m-0">{!! session('success_pass') !!}</p>
                        </div>
                     @endif
                     @if (session('error_pass'))
                        <div class="alert alert-danger">
                           <button type="button" class="close" data-dismiss="alert">×</button>
                           <h5>Something Wrong!</h5>
                           @php
                              $errorPass = session('error_pass');
                           @endphp
                           <ul class="mb-0 list-unstyled">
                              @foreach ($errorPass as $error)
                                 <li>{{ $error }}</li>
                              @endforeach
                           </ul>
                        </div>
                     @endif
                     {{-- <h4 class="card-title">Ubah Password</h4> --}}
                     <form action="{{ route('pages.profile.editaccountdetails.updatepw') }}" method="POST">
                        @csrf
                        <div class="form-group">
                           <label for="simpleinput">Previous Password</label>
                           <input type="password" name="prevPassword" id="prev_password" class="form-control">
                        </div>
                        <div class="form-group mt-4">
                           <label for="simpleinput">New Password</label>
                           <input type="password" name="newPassword" id="new_password" class="form-control">
                        </div>
                        <div class="form-group mt-4">
                           <label for="simpleinput">Confirm New Password</label>
                           <input type="password" name="confirmPassword" id="conf_new_password" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                           <div class="alert alert-primary" role="alert">
                              <h6>Tips for a good password</h6>
                              <p>
                              <ul>
                                 <li>Use both upper and lowercase characters</li>
                                 <li>Include at least one symbol (# $ ! % & etc...)</li>
                                 <li>Don't use dictionary words</li>
                              </ul>
                              </p>
                           </div>
                        </div>
                        <button class="btn btn-light">Cancel</button>
                        <button type="submit" class="btn btn-success-qw">Save Changes</button>
                     </form>

                  </div>
               </div>
            </div>
         </div>
         <!-- end row-->
      </div> <!-- container-fluid -->
   </div>
@endsection
