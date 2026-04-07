@extends('layouts.registerloginbase')
@php
    $companyLogo = Cfg::getValue('LogoURL');
    $defaultLogo = Theme::asset('assets/images/WHMCEPS.png')
@endphp
@section('register-title')
   Login
@endsection

@section('content')
   <div class="cbms-primary-color vh-100">
      <div class="vh-100 d-flex justify-content-center align-items-center align-content-center pt-auto flex-column">
         <div class="mb-3">
             @if (empty($companyLogo))
             <img src="{{ $defaultLogo}}" alt="logo-png" width="100">
             @else
             <img src="{{ $companyLogo }}" alt="logo-png" width="100">
             @endif
         </div>
         <div class="card login-card">
            <div class="card-title mb-3">
               <h1>Log In</h1>
               <small>Login first to access your Admin Area.</small>
            </div>
            <div class="row">
               @if ($message = Session::get('error_login'))
                  <div class="col-lg-12">
                     <div class="alert alert-danger fade show" role="alert" id="success-alert">
                        <h5>Warning!</h5>
                        <small>{{ $message }}</small>
                     </div>
                  </div>
               @endif
               @if (session('message'))
                  <div class="col-lg-12">
                     <div class="alert alert-warning fade show" role="alert" id="success-alert">
                        <h5>Warning!</h5>
                        <small>Please login first, try again.</small>
                     </div>
                  </div>
               @endif
            </div>
            <form method="POST" action="{{ route('admin.login') }}" id="login-admin-form">
               @csrf
               <div class="form-group">
                  <label for="email">Username or Email</label>
                  <input type="text" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email') }}" required autocomplete="off" autofocus>
                  @error('email')
                     <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                     </span>
                  @enderror
               </div>
               <div class="form-group">
                  <label for="password">Password</label>
                  <input class="form-control @error('password') is-invalid @enderror" type="password" name="password"
                     id="password" autocomplete="off" autofocus>
                  @error('password')
                     <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                     </span>
                  @enderror
               </div>
               <div class="form-group">
                  <div class="custom-control custom-checkbox">
                     <input type="checkbox" class="custom-control-input" id="remember" name="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                     <label class="custom-control-label" for="remember">Remember me</label>
                  </div>
               </div>
               <div class="d-flex">
                  <button id="login-btn" type="submit" class="mx-auto btn btn-success btn-admin-login">
                     <div id="login-text">Login</div>
                  </button>
               </div>
               <div class="text-center mt-2">
                  <a href="{{route('admin.password.request')}}" class="text-success">Forgot Password?</a>
               </div>
            </form>
         </div>
      </div>
   </div>
@endsection
