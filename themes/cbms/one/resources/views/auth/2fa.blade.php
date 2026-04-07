@extends('layouts.registerloginbase')

@section('register-title')
   Login
@endsection

@section('content')
   <div class="container" style="padding-top: 10em;">
      <div class="row justify-content-center">
         <div class="col-6">
            <div class="text-center d-sm-block d-md-none login-icon-container">
               <img src="{{ Cfg::getValue('LogoURL') }}" alt="app-logo" width="70" />
            </div>
         </div>
         <div class="col-12">
            <div class="d-flex justify-content-center">
               <div class="d-block bg-white shadow-lg rounded my-5" style="width: 90%">
                  <div class="row">
                     <div class="col-lg-5 d-none d-lg-block bg-login rounded-left">
                        {{-- {{ dd() }} --}}
                        @if (!empty(Cfg::getValue('LogoURL')))
                           <div class="p-3 login-icon-container">
                              <img src="{{ Cfg::getValue('LogoURL') }}" alt="app-logo" height="60" />
                           </div>
                        @endif
                     </div>
                     <div class="col-lg-7">
                        <div class="p-5">
                           <div class="text-lg-left text-center login-icon-container">
                              @if (empty(Cfg::getValue('LogoURL')))
                                 <a href="{{ url('home') }}" class="d-block mb-5">
                                    <img src="{{ Theme::asset('assets/images/WHMCEPS-dark.png') }}" alt="app-logo"
                                       width="100" />
                                 </a>
                              @endif
                           </div>
                           <h3 class="mb-1">{{ __('client.twofactorauth') }}</h3>
                           <div class="alert alert-warning" role="alert">
                              {{ __('client.twofa2ndfactorreq') }}
                           </div>
                           <form method="POST" action="{{ route('login') }}" id="login-form">
                              @csrf

                              <div class="form-group row">
                                 <div class="col-md-12">
                                    <input id="email" type="email"
                                       class="form-control @error('email') is-invalid @enderror" name="email"
                                       value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                       <span class="invalid-feedback" role="alert">
                                          <strong>{{ $message }}</strong>
                                       </span>
                                    @enderror
                                 </div>
                              </div>


                              <div class="form-group row mb-0">
                                 <div class="col-md-12">
                                    <button type="submit" class="btn btn-block btn-success" id="login-btn">
                                       <div id="login-text"><i class="fas fa-sign-in-alt mr-2"></i>{{ __('Login') }}
                                       </div>
                                    </button>
                                 </div>
                              </div>

                              <div class="text-center mt-3">
                                 <a class="text-info" href="#">{{ __('client.twofacantaccess2ndfactor') }}</a>
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
               </div> <!-- end .w-100 -->
            </div> <!-- end .d-flex -->
         </div> <!-- end col-->
      </div> <!-- end row -->
   </div>
   <!-- end container -->
@endsection
