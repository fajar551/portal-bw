@extends('layouts.registerloginbase')

@section('register-title')
   Reset Password
@endsection

@section('content')
   <div class="cbms-primary-color vh-100">
      <div class="vh-100 d-flex justify-content-center align-items-center align-content-center pt-auto flex-column">
         <div class="mb-3">
            <img src="{{ Theme::asset('assets/images/WHMCEPS.png') }}" alt="logo-png" width="250">
         </div>
         <div class="card login-card">
            <div class="card-title mb-3">
               <h1>Reset Password</h1>
               @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            </div>
            
            <form method="POST" action="{{ route('admin.password.email') }}">
               @csrf
               <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email') }}" required autocomplete="off" autofocus>
                  @error('email')
                     <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                     </span>
                  @enderror
               </div>
               <div class="d-flex">
                  <button type="submit" class="mx-auto btn btn-success btn-admin-login">
                    {{ __('Send Password Reset Link') }}
                  </button>
               </div>
            </form>
         </div>
      </div>
   </div>
@endsection
