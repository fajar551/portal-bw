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
                                        <p class="text-muted mb-4">Please enter your email address below to receive instructions for resetting password.</p>

                                        @if (session('message'))
                                            <div class="alert alert-{{ session('type') }}">
                                                <button type="button" class="close" data-dismiss="alert">×</button>
                                                <strong>{!! session('message') !!}</strong>
                                            </div>
                                        @endif

                                        <form class="user" method="POST" action="{{ route('password.email') }}">
                                            @csrf
                                            <div class="form-group">
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" id="email" placeholder="Email Address" required autocomplete="email" autofocus>
                                                @error('email')
                                                    <div class="text-danger" >{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <button type="submit" class="btn btn-success btn-block">
                                                {{ __('Send Password Reset Link') }}
                                            </button>
                                        </form>
                                        <div class="text-center mt-3">
                                            <h5 class="text-muted font-size-16">or</h5>
                                            {{-- <div class="list-inline mt-3 mb-0">
                                                <a href="" class="btn btn-outline-secondary btn-block"><i
                                                        class="fab fa-google"></i> Log In With Google </a>
                                            </div> --}}
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col-12 text-center">
                                                <a href="{{ route('login') }}" class="text-info font-weight-normat ml-1">
                                                    <b>{{ __("Back to Login") }}</b>
                                                </a>
                                            </div> <!-- end col -->
                                        </div>
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
