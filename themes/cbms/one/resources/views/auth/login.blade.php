@extends('layouts.registerloginbase')

@section('register-title')
    Login
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-12">
                <div class="text-center d-block d-lg-none login-icon-container pt-3">
                    <!--<img src="{{ Cfg::getValue('LogoURL') }}" alt="app-logo" width="70" />-->
                </div>
                <div class="row">
                    <div class="col-lg-5 d-none d-lg-block bg-login rounded-left">
                        @if (!empty(Cfg::getValue('LogoURL')))
                            <div class="p-3 login-icon-container">
                                <!--<img src="{{ Cfg::getValue('LogoURL') }}" alt="app-logo" height="60" />-->
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-7 rounded my-3 my-lg-0 bg-white">
                        <div class="p-5">
                            <div class="text-lg-left text-center login-icon-container">
                                @if (empty(Cfg::getValue('LogoURL')))
                                    <a href="{{ url('home') }}" class="d-block mb-5">
                                        <img src="{{ Theme::asset('assets/images/WHMCEPS-dark.png') }}" alt="app-logo" width="100" />
                                    </a>
                                @endif
                            </div>
                            <h3 class="mb-1">Welcome,</h3>
                            <p class="text-muted mb-4">Enter your email and password to access client area.</p>
                            <form method="POST" action="{{ route('login') }}" id="login-form">
                                @csrf

                                <div class="form-group row">
                                    <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Email Address') }}</label>

                                    <div class="col-md-8">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email') }}" required autocomplete="email" autofocus>

                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                                    <div class="col-md-8">
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"
                                            required autocomplete="current-password">

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6 offset-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                                {{ old('remember') ? 'checked' : '' }}>

                                            <label class="form-check-label" for="remember">
                                                {{ __('Remember Me') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row mb-0">
                                    <div class="col-md-8 offset-md-4">
                                        <button type="submit" class="btn btn-block btn-success" id="login-btn">
                                            <div id="login-text"><i class="fas fa-sign-in-alt mr-2"></i>{{ __('Login') }}</div>
                                        </button>
                                    </div>
                                    <div class="col-md-8 text-center offset-md-4">
                                        <p class="text-muted my-3">
                                            @if (Route::has('password.request'))
                                                <a href="{{ route('password.request') }}" class="text-muted font-weight-medium ml-1">Forgot
                                                    Password?</a>
                                            @endif
                                        </p>
                                        <a href="{{ route('register') }}" class="text-info font-weight-normat ml-1"><b>Create New
                                                Account</b>
                                        </a>
                                    </div> <!-- end col -->
                                </div>
                            </form>

                            {{-- <div class="row d-flex flex-row-reverse mt-4">
                                            <div class="col-12 text-center">
                                                <p class="text-muted mb-2">
                                                    @if (Route::has('password.request'))
                                                        <a href="{{ route('password.request') }}"
                                                            class="text-muted font-weight-medium ml-1">Forgot Password?</a>
                                                    @endif
                                                </p>
                                                <a href="{{ route('register') }}"
                                                    class="text-info font-weight-normat ml-1"><b>Create New
                                                        Account</b></a>
                                            </div> <!-- end col -->
                                        </div> --}}
                            <!-- end row -->
                        </div> <!-- end .padding-5 -->
                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- end col-->
        </div> <!-- end row -->
    </div>
    <!-- end container -->
@endsection
