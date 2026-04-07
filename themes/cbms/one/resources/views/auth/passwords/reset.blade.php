@extends('layouts.registerloginbase')

@section('register-title')
    Reset Password
@endsection

@section('content')
    <div style="background-color: #252B3B">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-center vh-100">
                        <div class="d-block bg-white shadow-lg rounded my-5" style="width: 90%">
                            <div class="row">
                                <div class="col-lg-5 d-none d-lg-block bg-login rounded-left"></div>
                                <div class="col-lg-7">
                                    <div class="p-5">
                                        <div class="text-lg-left text-center">
                                            <a href="{{ url('home') }}" class="d-block mb-5">
                                                <img src="{{ Theme::asset('assets/images/WHMCEPS-dark.png') }}"
                                                    alt="app-logo" height="40" />
                                            </a>
                                        </div>
                                        <h3 class="mb-1">Recover my password</h3>
                                        <p class="text-muted mb-4">Please enter your new login information.</p>

                                        @if (session('message'))
                                            <div class="alert alert-{{ session('type') }}">
                                                <button type="button" class="close" data-dismiss="alert">×</button>
                                                <strong>{!! session('message') !!}</strong>
                                            </div>
                                        @endif

                                        <form class="user" method="POST" action="{{ route('password.update') }}">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $token }}">
                                            <div class="form-group">
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" id="email" placeholder="Email Address" required autocomplete="email" autofocus>
                                                @error('email')
                                                    <div class="text-danger" >{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="New Password" name="password" required autocomplete="current-password">
                                                @error('password')
                                                    <div class="text-danger" >{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="confirm-password" placeholder="Confirm Password" name="password_confirmation" required autocomplete="current-password">
                                                @error('password_confirmation')
                                                    <div class="text-danger" >{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <button type="submit" class="btn btn-success btn-block" >
                                                Reset Password
                                            </button>
                                        </form>
                                        <!-- end row -->
                                    </div> <!-- end .padding-5 -->
                                </div> <!-- end col -->
                            </div> <!-- end row -->
                        </div> <!-- end .w-100 -->
                    </div> <!-- end .d-flex -->
                </div> <!-- end col-->
            </div> <!-- end row -->
        </div>
        <!-- end container -->
    </div>
@endsection
