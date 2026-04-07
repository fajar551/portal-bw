@extends('layouts.registerloginbase')

@section('register-title')
    Register
@endsection

@section('content')

<style>
    /* Wrapper untuk captcha */
    .g-recaptcha {
        display: flex !important;
        justify-content: center;
        align-items: center;
        margin: 20px auto;
        width: 100%;
    }

    /* Container captcha */
    .g-recaptcha > div {
        margin: 0 auto;
    }

    /* Responsive captcha */
    @media screen and (max-width: 480px) {
        .g-recaptcha {
            transform: scale(0.85);
            transform-origin: center;
            margin: 10px auto;
        }
    }

    /* Spacing untuk error message */
    .alert-danger {
        margin-bottom: 15px;
    }

    /* Container untuk captcha dan button */
    .form-group.text-center {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
</style>

    <div style="background-color: #252B3B">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="text-center d-block d-lg-none login-icon-container pt-3">
                        <img src="{{ Cfg::getValue('LogoURL') }}" alt="app-logo" width="70" />
                    </div>
                    <div class="d-flex align-items-center min-vh-100">
                        <div class="w-100 d-block bg-white shadow-lg rounded my-3">
                            <div class="row">
                                <div class="col-lg-5 d-none d-lg-block bg-register rounded-left">
                                    @if (!empty(Cfg::getValue('LogoURL')))
                                        <div class="p-3">
                                            <!--<img src="{{ Cfg::getValue('LogoURL') }}" alt="app-logo" height="60" />-->
                                            <img src="" alt="" height="60" />
                                        </div>
                                    @endif
                                </div>
                                <div class="col-lg-7">
                                    <div class="p-5">
                                        <div class="text-lg-left text-center">
                                            @if (empty(Cfg::getValue('LogoURL')))
                                                <a href="{{ url('home') }}" class="d-block mb-5">
                                                    <img src="{{ Theme::asset('assets/images/WHMCEPS-dark.png') }}" alt="app-logo" height="40" />
                                                </a>
                                            @endif
                                        </div>
                                        @if (!$registrationDisabled)
                                            {{--<form action="{{ route('register') }}" method="post" enctype="multipart/form-data">--}}
                                            <form action="{{ route('register') }}" method="post" enctype="multipart/form-data" id="registration-form">
                                                @csrf
                                                <h1 class="h5 mb-1">Create your account</h1>
                                                <p class="text-muted mb-4">Don't have an account yet? Create your CBMS account.</p>
                                                @if ($errors->any())
                                                    <div class="alert alert-danger">
                                                        <ul class="mb-0">
                                                            @foreach ($errors->all() as $error)
                                                                @if (Str::contains($error, ['<li>', '</li>']))
                                                                    {!! $error !!}
                                                                @else
                                                                    <li>{{ $error }}</li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                                <div class="sub-heading">
                                                    <h5>{{ Lang::get('orderForm.personalInformation') }}</h5>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
                                                            </div>
                                                            <input type="text" name="firstname" id="inputFirstName" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.firstName') }}" value="{{ old('firstname') }}"
                                                                {{ !in_array('firstname', $optionalFields) ? 'required' : '' }} autofocus>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
                                                            </div>
                                                            <input type="text" name="lastname" id="inputLastName" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.lastName') }}" value="{{ old('lastname') }}"
                                                                {{ !in_array('lastname', $optionalFields) ? 'required' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-envelope"></i></span>
                                                            </div>
                                                            <input type="email" name="email" id="inputEmail" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.emailAddress') }}" value="{{ old('email') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-phone"></i></span>
                                                            </div>
                                                            <input type="tel" name="phonenumber" id="inputPhone" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.phoneNumber') }}"
                                                                value="{{ old('phonenumber') }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="sub-heading">
                                                    <h5>{{ Lang::get('orderForm.billingAddress') }}</h5>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-building"></i></span>
                                                            </div>
                                                            <input type="text" name="companyname" id="inputCompanyName" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.companyName') }} ({{ Lang::get('orderForm.optional') }})"
                                                                value="{{ old('companyname') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="far fa-building"></i></span>
                                                            </div>
                                                            <input type="text" name="address1" id="inputAddress1" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.streetAddress') }}"
                                                                value="{{ old('address1') }}"
                                                                {{ !in_array('address1', $optionalFields) ? 'required' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i
                                                                        class="fas fa-map-marker-alt"></i></span>
                                                            </div>
                                                            <input type="text" name="address2" id="inputAddress2" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.streetAddress2') }}"
                                                                value="{{ old('address2') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="far fa-building"></i></span>
                                                            </div>
                                                            <input type="text" name="city" id="inputCity" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.city') }}" value="{{ old('city') }}"
                                                                {{ !in_array('city', $optionalFields) ? 'required' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-5">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-map-signs"></i></span>
                                                            </div>
                                                            <input type="text" name="state" id="state" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.state') }}" value="{{ old('state') }}"
                                                                {{ !in_array('state', $optionalFields) ? 'required' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-certificate"></i></span>
                                                            </div>
                                                            <input type="text" name="postcode" id="inputPostcode" class="field form-control"
                                                                placeholder="{{ Lang::get('orderForm.postcode') }}" value="{{ old('postcode') }}"
                                                                {{ !in_array('postcode', $optionalFields) ? 'required' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <label class="input-group-text" for="inputGroupSelect01"><i
                                                                        class="fas fa-globe"></i></label>
                                                            </div>
                                                            <select name="country" id="inputCountry" class="field form-control">
                                                                @foreach ($clientcountries as $countryCode => $countryName)
                                                                    <option value="{{ $countryCode }}"
                                                                        {{ (!$clientcountry && $countryCode == $defaultCountry) || $countryCode == $clientcountry ? 'selected="selected"' : '' }}>
                                                                        {{ $countryName }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    @if ($showTaxIdField)
                                                        <div class="col-sm-12">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i
                                                                            class="fas fa-building"></i></span>
                                                                </div>
                                                                <input type="text" name="tax_id" id="inputTaxId" class="field form-control"
                                                                    placeholder="{{ Lang::get(App\Helpers\Vat::getLabel()) }} ({{ Lang::get('orderForm.optional') }})"
                                                                    value="{{ old('tax_id') }}">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($customfields || $currencies)
                                                    <div class="sub-heading">
                                                        <h5>{{ Lang::get('orderForm.orderadditionalrequiredinfo') }}</h5>
                                                    </div>
                                                    <div class="row">
                                                        @if ($customfields)
                                                            @foreach ($customfields as $customfield)
                                                                <div class="col-sm-6">
                                                                    <div class="form-group">
                                                                        <label
                                                                            for="customfield{{ $customfield['id'] }}">{{ $customfield['name'] }}</label>
                                                                        <div class="control">
                                                                            {!! $customfield['input'] !!}
                                                                            @if ($customfield['description'])
                                                                                <small class="text-muted">{{ $customfield['description'] }}</small>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                        @if ($customfields && count($customfields) % 2 > 0)
                                                            <div class="clearfix"></div>
                                                        @endif
                                                        @if ($currencies)
                                                            <div class="col-sm-6">
                                                                <div class="form-group prepend-icon">
                                                                    <label for="inputCurrency" class="field-icon">
                                                                        <i class="far fa-money-bill-alt"></i>
                                                                    </label>
                                                                    <select id="inputCurrency" name="currency" class="field form-control">
                                                                        @foreach ($currencies as $curr)
                                                                            <option value="{{ $curr['id'] }}"
                                                                                @if ((!old('currency') && $curr['default']) || old('currency') == $curr['id']) selected @endif>{{ $curr['code'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div id="containerNewUserSecurity" {{ false && !$securityquestions ? 'class="d-none"' : '' }}>
                                                    <div class="sub-heading">
                                                        <h5>{{ Lang::get('orderForm.accountSecurity') }}</h5>
                                                    </div>
                                                    <div id="containerPassword" class="row{{ false && $securityquestions ? ' d-none' : '' }}">
                                                        <div id="passwdFeedback" style="display: none;" class="alert alert-info text-center col-sm-12">
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i
                                                                            class="fas fa-lock"></i></span>
                                                                </div>
                                                                <input type="password" name="password" id="inputNewPassword1"
                                                                    data-error-threshold="{{ $pwStrengthErrorThreshold ?? '' }}"
                                                                    data-warning-threshold="{{ $pwStrengthWarningThreshold ?? '' }}"
                                                                    class="field form-control"
                                                                    placeholder="{{ Lang::get('client.clientareapassword') }}" autocomplete="off">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i
                                                                            class="fas fa-lock"></i></span>
                                                                </div>
                                                                <input type="password" name="password2" id="inputNewPassword2" class="field form-control"
                                                                    placeholder="{{ Lang::get('client.clientareaconfirmpassword') }}"
                                                                    autocomplete="off">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            {{-- <button type="button" class="btn btn-secondary btn-sm generate-password" data-targetfields="inputNewPassword1,inputNewPassword2">
                                                                {{Lang::get('generatePassword.btnLabel')}}
                                                            </button> --}}
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="password-strength-meter">
                                                                <div class="progress">
                                                                    <div class="progress-bar progress-bar-success progress-bar-striped"
                                                                        role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                                                                        id="passwordStrengthMeterBar">
                                                                    </div>
                                                                </div>
                                                                <p class="text-center small text-muted mt-2" id="passwordStrengthTextLabel">
                                                                    {{ Lang::get('client.pwstrength') }}: {{ Lang::get('client.pwstrengthenter') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if ($securityquestions)
                                                        <div class="row">
                                                            <div class="form-group col-sm-12">
                                                                <select name="securityqid" id="inputSecurityQId" class="field form-control">
                                                                    <option value="">{{ Lang::get('client.clientareasecurityquestion') }}</option>
                                                                    @foreach ($securityquestions as $question)
                                                                        <option value="{{ $question['id'] }}"
                                                                            @if ($question['id'] == old('securityqid')) selected @endif>
                                                                            {{ $question['question'] }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1"><i
                                                                                class="fas fa-lock"></i></span>
                                                                    </div>
                                                                    <input type="password" name="securityqans" id="inputSecurityQAns"
                                                                        class="field form-control"
                                                                        placeholder="{{ Lang::get('client.clientareasecurityanswer') }}"
                                                                        autocomplete="off">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                @if ($showMarketingEmailOptIn)
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="marketing-email-optin">
                                                                <h5>{{ Lang::get('client.emailMarketing.joinOurMailingList') }}</h5>
                                                                <div class="row mt-2">
                                                                    <div class="col-1 d-flex justify-content-end align-items-center">
                                                                        <input type="checkbox" name="marketingoptin" value="1"
                                                                            {{ $marketingEmailOptIn ? 'checked' : '' }}
                                                                            class="no-icheck toggle-switch-success" data-size="small"
                                                                            data-on-text="{{ Lang::get('client.yes') }}"
                                                                            data-off-text="{{ Lang::get('client.no') }}">
                                                                    </div>
                                                                    <div class="col-10">
                                                                        <p class="mb-0">{{ $marketingEmailOptInMessage }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($accepttos)
                                                    <div class="row mt-3">
                                                        <div class="col-md-12">
                                                            <div class="card">
                                                                <div class="card-header bg-danger">
                                                                    <h3 class="card-title mb-0 text-white"><span
                                                                            class="fas fa-exclamation-triangle tosicon"></span> &nbsp;
                                                                        {{ Lang::get('client.ordertos') }}</h3>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="col-md-12">
                                                                        <label class="checkbox">
                                                                            <input type="checkbox" name="accepttos" class="accepttos mr-2">
                                                                            {{ Lang::get('client.ordertosagreement') }} <a href="{{ $tosurl }}"
                                                                                target="_blank">{{ Lang::get('client.ordertos') }}</a>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                               <div class="row mb-3">
    <div class="col-md-12">
        <div class="form-group text-center">
            @error('captcha')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            
            {{-- Captcha container --}}
            <div class="captcha-container">
                <div class="g-recaptcha" 
                    data-sitekey="{{ $recaptchaSiteKey }}">
                </div>
            </div>

            {{-- Submit button --}}
            <button type="submit" class="btn btn-block btn-lg btn-success">
                {{ Lang::get('client.clientregistertitle') }}
            </button>
        </div>
    </div>
</div>

                                                
                                            </form>
                                        @endif

                                        <div class="row mt-4">
                                            <div class="col-12 text-center">
                                                <p class="text-muted mb-0">Already have an account?<a href="{{ route('login') }}"
                                                        class="text-info font-weight-medium ml-1"><b>Login</b></a></p>
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

@push('scripts')
    <script src="{{ Theme::asset('js/StatesDropdown.js') }}"></script>
    <script src="{{ Theme::asset('js/PasswordStrength.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        window.langPasswordStrength = "{{ Lang::get('client.pwstrength') }}";
        window.langPasswordWeak = "{{ Lang::get('client.pwstrengthweak') }}";
        window.langPasswordModerate = "{{ Lang::get('client.pwstrengthmoderate') }}";
        window.langPasswordStrong = "{{ Lang::get('client.pwstrengthstrong') }}";
        
        jQuery(document).ready(function() {
            jQuery("#inputNewPassword1").keyup(registerFormPasswordStrengthFeedback);
            
            // Validasi captcha dengan SweetAlert2
            jQuery("#registration-form").on('submit', function(e) {
                if(grecaptcha.getResponse() == "") {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Please complete the captcha verification',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            });
        });
    </script>
@endpush