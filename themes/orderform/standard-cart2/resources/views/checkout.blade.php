<script>
    // Define state tab index value
    var statesTab = 10;
    // Do not enforce state input client side
    var stateNotRequired = true;
</script>
@include('common')
<script type="text/javascript" src="{{ Theme::asset('js/StatesDropdown.js') }}"></script>
<script type="text/javascript" src="{{ Theme::asset('js/PasswordStrength.js') }}"></script>
<script>
    window.langPasswordStrength = "{{ Lang::get('client.pwstrength') }}";
    window.langPasswordWeak = "{{ Lang::get('client.pwstrengthweak') }}";
    window.langPasswordModerate = "{{ Lang::get('client.pwstrengthmoderate') }}";
    window.langPasswordStrong = "{{ Lang::get('client.pwstrengthstrong') }}";
</script>

<div class="page-content">
    <div class="container-fluid">
        <div id="order-standard_cart">
            <div class="row">

                <div class="pull-md-right col-md-9">

                    <div class="header-lined">
                        <h1>{{ Lang::get('orderForm.checkout') }}</h1>
                    </div>

                </div>

                <div class="col-md-3 pull-md-left sidebar hidden-xs hidden-sm">

                    @include('sidebar-categories')

                </div>

                <div class="col-md-9 pull-md-right">

                    @include('sidebar-categories-collapsed')

                    <div class="already-registered clearfix">
                        <div class="pull-right">
                            <button type="button" class="btn btn-info {{ $loggedin || (!$loggedin && $custtype == 'existing') ? 'hidden' : '' }}"
                                id="btnAlreadyRegistered">
                                {{ Lang::get('orderForm.alreadyRegistered') }}
                            </button>
                            <button type="button" class="btn btn-warning {{ $loggedin || $custtype != 'existing' ? 'hidden' : '' }}"
                                id="btnNewUserSignup">
                                {{ Lang::get('orderForm.createAccount') }}
                            </button>
                        </div>
                        <p>{{ Lang::get('orderForm.enterPersonalDetails') }}</p>
                    </div>

                    @if (isset($errormessage) && $errormessage)
                    <div class="alert alert-danger checkout-error-feedback" role="alert">
                        <p>{{ Lang::get('orderForm.correctErrors') }}:</p>
                        <ul>
                            {!! $errormessage !!}
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                    @endif

                    <form method="post" action="{{ route('cart') }}?a=checkout" name="orderfrm" id="frmCheckout" enctype="multipart/form-data">
                        <input type="hidden" name="submit" value="true" />
                        <input type="hidden" name="custtype" id="inputCustType" value="{{ $custtype }}" />

                        <div id="containerExistingUserSignin" @if ($loggedin || $custtype !='existing' ) class="hidden" @endif>

                            <div class="sub-heading">
                                <span>{{ Lang::get('orderForm.existingCustomerLogin') }}</span>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group prepend-icon">
                                        <label for="inputLoginEmail" class="field-icon">
                                            <i class="fas fa-envelope"></i>
                                        </label>
                                        <input type="text" name="loginemail" value="{{ old('loginemail') }}" id="inputLoginEmail" class="field"
                                            placeholder="{{ Lang::get('orderForm.emailAddress') }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group prepend-icon">
                                        <label for="inputLoginPassword" class="field-icon">
                                            <i class="fas fa-lock"></i>
                                        </label>
                                        <input type="password" name="loginpassword" id="inputLoginPassword" class="field"
                                            placeholder="{{ Lang::get('client.clientareapassword') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- {include file="orderforms/standard_cart/linkedaccounts.tpl" linkContext="checkout-existing"} --}}
                            @include('linkedaccounts', ['linkContext' => 'checkout-existing'])
                        </div>

                        <div id="containerNewUserSignup" @if ($custtype=='existing' && !$loggedin) class="hidden" @endif>

                            <div @if ($loggedin) class="hidden" @endif>
                                {{-- {include file="orderforms/standard_cart/linkedaccounts.tpl" linkContext="checkout-new"} --}}
                                @include('linkedaccounts', ['linkContext' => 'checkout-new'])
                            </div>

                            <div class="sub-heading">
                                <span>{{ Lang::get('orderForm.personalInformation') }}</span>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group prepend-icon">
                                        <label for="inputFirstName" class="field-icon">
                                            <i class="fas fa-user"></i>
                                        </label>
                                        <input type="text" name="firstname" id="inputFirstName" class="field"
                                            placeholder="{{ Lang::get('orderForm.firstName') }}" value="{{ $clientsdetails['firstname'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif autofocus>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group prepend-icon">
                                        <label for="inputLastName" class="field-icon">
                                            <i class="fas fa-user"></i>
                                        </label>
                                        <input type="text" name="lastname" id="inputLastName" class="field"
                                            placeholder="{{ Lang::get('orderForm.lastName') }}" value="{{ $clientsdetails['lastname'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group prepend-icon">
                                        <label for="inputEmail" class="field-icon">
                                            <i class="fas fa-envelope"></i>
                                        </label>
                                        <input type="email" name="email" id="inputEmail" class="field"
                                            placeholder="{{ Lang::get('orderForm.emailAddress') }}" value="{{ $clientsdetails['email'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group prepend-icon">
                                        <label for="inputPhone" class="field-icon">
                                            <i class="fas fa-phone"></i>
                                        </label>
                                        <input type="tel" name="phonenumber" id="inputPhone" class="field"
                                            placeholder="{{ Lang::get('orderForm.phoneNumber') }}"
                                            value="{{ $clientsdetails['phonenumber'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                            </div>

                            <div class="sub-heading">
                                <span>{{ Lang::get('orderForm.billingAddress') }}</span>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group prepend-icon">
                                        <label for="inputCompanyName" class="field-icon">
                                            <i class="fas fa-building"></i>
                                        </label>
                                        <input type="text" name="companyname" id="inputCompanyName" class="field"
                                            placeholder="{{ Lang::get('orderForm.companyName') }} ({{ Lang::get('orderForm.optional') }})"
                                            value="{{ $clientsdetails['companyname'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group prepend-icon">
                                        <label for="inputAddress1" class="field-icon">
                                            <i class="far fa-building"></i>
                                        </label>
                                        <input type="text" name="address1" id="inputAddress1" class="field"
                                            placeholder="{{ Lang::get('orderForm.streetAddress') }}"
                                            value="{{ $clientsdetails['address1'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group prepend-icon">
                                        <label for="inputAddress2" class="field-icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </label>
                                        <input type="text" name="address2" id="inputAddress2" class="field"
                                            placeholder="{{ Lang::get('orderForm.streetAddress2') }}"
                                            value="{{ $clientsdetails['address2'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group prepend-icon">
                                        <label for="inputCity" class="field-icon">
                                            <i class="far fa-building"></i>
                                        </label>
                                        <input type="text" name="city" id="inputCity" class="field"
                                            placeholder="{{ Lang::get('orderForm.city') }}" value="{{ $clientsdetails['city'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group prepend-icon">
                                        <label for="state" class="field-icon" id="inputStateIcon">
                                            <i class="fas fa-map-signs"></i>
                                        </label>
                                        <label for="stateinput" class="field-icon" id="inputStateIcon">
                                            <i class="fas fa-map-signs"></i>
                                        </label>
                                        <input type="text" name="state" id="inputState" class="field"
                                            placeholder="{{ Lang::get('orderForm.state') }}" value="{{ $clientsdetails['state'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group prepend-icon">
                                        <label for="inputPostcode" class="field-icon">
                                            <i class="fas fa-certificate"></i>
                                        </label>
                                        <input type="text" name="postcode" id="inputPostcode" class="field"
                                            placeholder="{{ Lang::get('orderForm.postcode') }}" value="{{ $clientsdetails['postcode'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group prepend-icon">
                                        <label for="inputCountry" class="field-icon" id="inputCountryIcon">
                                            <i class="fas fa-globe"></i>
                                        </label>
                                        <select name="country" id="inputCountry" class="field"
                                            @if ($loggedin) disabled="disabled" @endif>
                                            @foreach ($countries as $countrycode => $countrylabel)
                                            <option value="{{ $countrycode }}" @if ((!$country && $countrycode==$defaultcountry) || $countrycode==$country) selected @endif>
                                                {{ $countrylabel }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if ($showTaxIdField)
                                <div class="col-sm-12">
                                    <div class="form-group prepend-icon">
                                        <label for="inputTaxId" class="field-icon">
                                            <i class="fas fa-building"></i>
                                        </label>

                                        <input type="text" name="tax_id" id="inputTaxId" class="field"
                                            placeholder="{{ Lang::get(\App\Helpers\Vat::getLabel()) }} ({{ Lang::get('orderForm.optional') }})"
                                            value="{{ $clientsdetails['tax_id'] ?? '' }}"
                                            @if ($loggedin) readonly="readonly" @endif>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if ($customfields ?? '')
                            <div class="sub-heading">
                                <span>{{ Lang::get('client.orderadditionalrequiredinfo') }}</span>
                            </div>
                            <div class="field-container">
                                <div class="row">
                                    @foreach ($customfields as $customfield)
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="customfield{{ $customfield['id'] }}">{{ $customfield['name'] }}</label>
                                            {!! $customfield['input'] !!}
                                            @if ($customfield['description'])
                                            <span class="field-help-text">
                                                {{ $customfield['description'] }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                        </div>

                        @if ($domainsinorder)
                        <div class="sub-heading">
                            <span>{{ Lang::get('client.domainregistrantinfo') }}</span>
                        </div>

                        <p class="small text-muted">{{ Lang::get('orderForm.domainAlternativeContact') }}</p>

                        <div class="row margin-bottom">
                            <div class="col-sm-6 col-sm-offset-3">
                                <select name="contact" id="inputDomainContact" class="field">
                                    <option value="">{{ Lang::get('client.usedefaultcontact') }}</option>
                                    @foreach ($domaincontacts as $domcontact)
                                    <option value="{{ $domcontact['id'] }}" @if (($contact ?? '' )==$domcontact['id']) selected @endif>
                                        {{ $domcontact['name'] }}
                                    </option>
                                    @endforeach
                                    <option value="addingnew" @if (($contact ?? '' )=='addingnew' ) selected @endif>
                                        {{ Lang::get('client.clientareanavaddcontact') }}...
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row {{ ($contact ?? '') != 'addingnew' ? 'hidden' : '' }}" id="domainRegistrantInputFields">
                            <div class="col-sm-6">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCFirstName" class="field-icon">
                                        <i class="fas fa-user"></i>
                                    </label>
                                    <input type="text" name="domaincontactfirstname" id="inputDCFirstName" class="field"
                                        placeholder="{{ Lang::get('orderForm.firstName') }}"
                                        value="{{ $domaincontact['firstname'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCLastName" class="field-icon">
                                        <i class="fas fa-user"></i>
                                    </label>
                                    <input type="text" name="domaincontactlastname" id="inputDCLastName" class="field"
                                        placeholder="{{ Lang::get('orderForm.lastName') }}"
                                        value="{{ $domaincontact['lastname'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCEmail" class="field-icon">
                                        <i class="fas fa-envelope"></i>
                                    </label>
                                    <input type="email" name="domaincontactemail" id="inputDCEmail" class="field"
                                        placeholder="{{ Lang::get('orderForm.emailAddress') }}"
                                        value="{{ $domaincontact['email'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCPhone" class="field-icon">
                                        <i class="fas fa-phone"></i>
                                    </label>
                                    <input type="tel" name="domaincontactphonenumber" id="inputDCPhone" class="field"
                                        placeholder="{{ Lang::get('orderForm.phoneNumber') }}"
                                        value="{{ $domaincontact['phonenumber'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCCompanyName" class="field-icon">
                                        <i class="fas fa-building"></i>
                                    </label>
                                    <input type="text" name="domaincontactcompanyname" id="inputDCCompanyName" class="field"
                                        placeholder="{{ Lang::get('orderForm.companyName') }} ({{ Lang::get('orderForm.optional') }})"
                                        value="{{ $domaincontact['companyname'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCAddress1" class="field-icon">
                                        <i class="far fa-building"></i>
                                    </label>
                                    <input type="text" name="domaincontactaddress1" id="inputDCAddress1" class="field"
                                        placeholder="{{ Lang::get('orderForm.streetAddress') }}"
                                        value="{{ $domaincontact['address1'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCAddress2" class="field-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </label>
                                    <input type="text" name="domaincontactaddress2" id="inputDCAddress2" class="field"
                                        placeholder="{{ Lang::get('orderForm.streetAddress2') }}"
                                        value="{{ $domaincontact['address2'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCCity" class="field-icon">
                                        <i class="far fa-building"></i>
                                    </label>
                                    <input type="text" name="domaincontactcity" id="inputDCCity" class="field"
                                        placeholder="{{ Lang::get('orderForm.city') }}"
                                        value="{{ $domaincontact['city'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCState" class="field-icon">
                                        <i class="fas fa-map-signs"></i>
                                    </label>
                                    <input type="text" name="domaincontactstate" id="inputDCState" class="field"
                                        placeholder="{{ Lang::get('orderForm.state') }}"
                                        value="{{ $domaincontact['state'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCPostcode" class="field-icon">
                                        <i class="fas fa-certificate"></i>
                                    </label>
                                    <input type="text" name="domaincontactpostcode" id="inputDCPostcode" class="field"
                                        placeholder="{{ Lang::get('orderForm.postcode') }}"
                                        value="{{ $domaincontact['postcode'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCCountry" class="field-icon" id="inputCountryIcon">
                                        <i class="fas fa-globe"></i>
                                    </label>
                                    <select name="domaincontactcountry" id="inputDCCountry" class="field">
                                        @foreach ($countries as $countrycode => $countrylabel)
                                        <option value="{{ $countrycode }}" @if ((!isset($domaincontact['country']) && $countrycode==$defaultcountry) || $countrycode==($domaincontact['country'] ?? '' )) selected @endif>
                                            {{ $countrylabel }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group prepend-icon">
                                    <label for="inputDCTaxId" class="field-icon">
                                        <i class="fas fa-building"></i>
                                    </label>
                                    <input type="text" name="domaincontacttax_id" id="inputDCTaxId" class="field"
                                        placeholder="{{ Lang::get(\App\Helpers\Vat::getLabel()) }} ({{ Lang::get('orderForm.optional') }})"
                                        value="{{ $domaincontact['tax_id'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        @endif

                        @if (!$loggedin)
                        <div id="containerNewUserSecurity" @if ((!$loggedin && $custtype=='existing' ) || (isset($remote_auth_prelinked) && $remote_auth_prelinked && !$securityquestions)) class="hidden" @endif>

                            <div class="sub-heading">
                                <span>{{ Lang::get('orderForm.accountSecurity') }}</span>
                            </div>

                            <div id="containerPassword"
                                class="row {{ isset($remote_auth_prelinked) && $remote_auth_prelinked && $securityquestions ? 'hidden' : '' }}">
                                <div id="passwdFeedback" style="display: none;" class="alert alert-info text-center col-sm-12"></div>
                                <div class="col-sm-6">
                                    <div class="form-group prepend-icon">
                                        <label for="inputNewPassword1" class="field-icon">
                                            <i class="fas fa-lock"></i>
                                        </label>
                                        <input type="password" name="password" id="inputNewPassword1"
                                            data-error-threshold="{{ $pwStrengthErrorThreshold ?? 0 }}"
                                            data-warning-threshold="{{ $pwStrengthWarningThreshold ?? 0 }}" class="field"
                                            placeholder="{{ Lang::get('client.clientareapassword') }}"
                                            @if (isset($remote_auth_prelinked) && $remote_auth_prelinked) value="{{ $password }}" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group prepend-icon">
                                        <label for="inputNewPassword2" class="field-icon">
                                            <i class="fas fa-lock"></i>
                                        </label>
                                        <input type="password" name="password2" id="inputNewPassword2" class="field"
                                            placeholder="{{ Lang::get('client.clientareaconfirmpassword') }}"
                                            @if (isset($remote_auth_prelinked) && $remote_auth_prelinked) value="{{ $password }}" @endif>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <button type="button" class="btn btn-success btn-sm generate-password"
                                        data-targetfields="inputNewPassword1,inputNewPassword2">
                                        {{ Lang::get('client.generatePasswordbtnLabel') }}
                                    </button>
                                </div>
                                <div class="col-sm-6">
                                    <div class="password-strength-meter">
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar"
                                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="passwordStrengthMeterBar">
                                            </div>
                                        </div>
                                        <p class="text-center small text-muted" id="passwordStrengthTextLabel">
                                            {{ Lang::get('client.pwstrength') }}: {{ Lang::get('client.pwstrengthenter') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @if ($securityquestions)
                            <div class="row">
                                <div class="col-sm-6">
                                    <select name="securityqid" id="inputSecurityQId" class="field">
                                        <option value="">{{ Lang::get('client.clientareasecurityquestion') }}</option>
                                        @foreach ($securityquestions as $question)
                                        <option value="{{ $question['id'] }}" @if ($question['id']==$securityqid) selected @endif>
                                            {{ $question['question'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group prepend-icon">
                                        <label for="inputSecurityQAns" class="field-icon">
                                            <i class="fas fa-lock"></i>
                                        </label>
                                        <input type="password" name="securityqans" id="inputSecurityQAns" class="field"
                                            placeholder="{{ Lang::get('client.clientareasecurityanswer') }}">
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                        @endif

                        @foreach ($hookOutput as $output)
                        <div>
                            {!! $output !!}
                        </div>
                        @endforeach

                        <div class="sub-heading">
                            <span>{{ Lang::get('orderForm.paymentDetails') }}</span>
                        </div>

                        <div class="alert alert-success text-center large-text" role="alert">
                            {{ Lang::get('client.ordertotalduetoday') }}: &nbsp; <strong>{{ $total }}</strong>
                        </div>

                        @if ($canUseCreditOnCheckout)
                        <div id="applyCreditContainer" class="apply-credit-container" data-apply-credit="{{ $applyCredit }}">
                            <p>{!! Lang::get('client.cart.availableCreditBalance', ['amount' => $creditBalance]) !!}</p>

                            @if ($creditBalance->toNumeric() >= $total->toNumeric())
                            <label class="radio">
                                <input id="useFullCreditOnCheckout" type="radio" name="applycredit" value="1"
                                    {{ $applyCredit ? 'checked' : '' }}>
                                {!! Lang::get('client.cart.applyCreditAmountNoFurtherPayment', ['amount' => $total]) !!}
                            </label>
                            @else
                            <label class="radio">
                                <input id="useCreditOnCheckout" type="radio" name="applycredit" value="1"
                                    {{ $applyCredit ? 'checked' : '' }}>
                                {!! Lang::get('client.cart.applyCreditAmount', ['amount' => $creditBalance]) !!}
                            </label>
                            @endif

                            <label class="radio">
                                <input id="skipCreditOnCheckout" type="radio" name="applycredit" value="0"
                                    {{ !$applyCredit ? 'checked' : '' }}>
                                {!! Lang::get('client.cart.applyCreditSkip', ['amount' => $creditBalance]) !!}
                            </label>
                        </div>
                        @endif
                        <div id="paymentGatewaysContainer" class="form-group">
                            <p class="small text-muted">{{ Lang::get('orderForm.preferredPaymentMethod') }}</p>

                            <div class="text-center">
                                @foreach ($gateways as $gateway)
                                <label class="radio-inline">
                                    <input type="radio" name="paymentmethod" value="{{ $gateway['sysname'] }}"
                                        data-payment-type="{{ $gateway['payment_type'] }}"
                                        data-show-local="{{ $gateway['show_local_cards'] }}"
                                        class="payment-methods {{ $gateway['type'] == 'CC' ? 'is-credit-card' : '' }}"
                                        @if ($selectedgateway==$gateway['sysname']) checked @endif />
                                    {{ $gateway['name'] }}
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="alert alert-danger text-center gateway-errors hidden"></div>

                        <div class="clearfix"></div>

                        <div class="cc-input-container {{ $selectedgatewaytype != 'CC' ? 'hidden' : '' }}" id="creditCardInputFields">
                            @if ($client)
                            <div id="existingCardsContainer" class="existing-cc-grid">
                                @foreach ($client->payMethods->validateGateways()->sortByExpiryDate() as $payMethod)
                                @php
                                $payMethodExpired = 0;
                                $expiryDate = '';
                                @endphp
                                @if ($payMethod->isCreditCard())
                                @if ($payMethod->payment->isExpired())
                                @php
                                $payMethodExpired = 1;
                                @endphp
                                @endif
                                @if ($payMethod->payment->getExpiryDate())
                                @php
                                $expiryDate = $payMethod->payment->getExpiryDate()->format('m/Y');
                                @endphp
                                @endif
                                @endif
                                <div class="paymethod-info radio-inline" data-paymethod-id="{{ $payMethod->id }}">
                                    <input type="radio" name="ccinfo" class="existing-card"
                                        @if ($payMethodExpired) disabled @endif data-payment-type="{{ $payMethod->getType() }}"
                                        data-payment-gateway="{{ $payMethod->gateway_name }}"
                                        data-order-preference="{{ $payMethod->order_preference }}" value="{{ $payMethod->id }}">
                                </div>

                                <div class="paymethod-info" data-paymethod-id="{{ $payMethod->id }}">
                                    <i class="{{ $payMethod->getFontAwesomeIcon() }}"></i>
                                </div>
                                <div class="paymethod-info" data-paymethod-id="{{ $payMethod->id }}">
                                    @if ($payMethod->isCreditCard() || $payMethod->isRemoteBankAccount())
                                    {{ $payMethod->payment->getDisplayName() }}
                                    @else
                                    <span class="type">
                                        {{ $payMethod->payment->getAccountType() }}
                                    </span>
                                    {{ substr($payMethod->payment->getAccountNumber(), -4) }}
                                    @endif
                                </div>
                                <div class="paymethod-info" data-paymethod-id="{{ $payMethod->id }}">
                                    {{ $payMethod->getDescription() }}
                                </div>
                                <div class="paymethod-info" data-paymethod-id="{{ $payMethod->id }}">
                                    {{ $expiryDate }}
                                    @if ($payMethodExpired)
                                    <br><small>{{ Lang::get('client.clientareaexpired') }}</small>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif
                            <div class="row cvv-input" id="existingCardInfo">
                                <div class="col-lg-3 col-sm-4">
                                    <div class="form-group prepend-icon">
                                        <label for="inputCardCVV2" class="field-icon">
                                            <i class="fas fa-barcode"></i>
                                        </label>
                                        <div class="input-group">
                                            <input type="tel" name="cccvv" id="inputCardCVV2" class="field"
                                                placeholder="{{ Lang::get('client.creditcardcvvnumbershort') }}" autocomplete="cc-cvc">
                                            <span class="input-group-btn position-absolute" style="right: 0">
                                                <button type="button" class="btn btn-default" data-toggle="popover" data-placement="right" id="ppover">
                                                    ?
                                                </button>

                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <ul>
                                <li>
                                    <label class="radio-inline">
                                        <input type="radio" name="ccinfo" value="new" id="new"
                                            @if (!$client || $client->payMethods->count() === 0) checked="checked" @endif />
                                        &nbsp;
                                        {{ Lang::get('client.creditcardenternewcard') }}
                                    </label>
                                </li>
                            </ul>

                            <div class="row" id="newCardInfo">
                                <div id="cardNumberContainer" class="col-sm-6 new-card-container">
                                    <div class="form-group prepend-icon">
                                        <label for="inputCardNumber" class="field-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </label>
                                        <input type="tel" name="ccnumber" id="inputCardNumber" class="field cc-number-field"
                                            placeholder="{{ Lang::get('orderForm.cardNumber') }}" autocomplete="cc-number">
                                    </div>
                                </div>
                                <div class="col-sm-3 new-card-container">
                                    <div class="form-group prepend-icon">
                                        <label for="inputCardExpiry" class="field-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </label>
                                        <input type="tel" name="ccexpirydate" id="inputCardExpiry" class="field"
                                            placeholder="MM / YY {{ $showccissuestart ? Lang::get('client.creditcardcardexpires') : '' }}"
                                            autocomplete="cc-exp">
                                    </div>
                                </div>
                                <div class="col-sm-3" id="cvv-field-container">
                                    <div class="form-group prepend-icon">
                                        <label for="inputCardCVV" class="field-icon">
                                            <i class="fas fa-barcode"></i>
                                        </label>
                                        <div class="input-group">
                                            <input type="tel" name="cccvv" id="inputCardCVV" class="field"
                                                placeholder="{{ Lang::get('client.creditcardcvvnumbershort') }}" autocomplete="cc-cvc">
                                            <span class="input-group-btn position-absolute" style="right: 0">
                                                <button type="button" class="btn btn-success" data-toggle="popover" data-placement="bottom" id="new-ccv">
                                                    ?
                                                </button>
                                                {{-- </span> --}}
                                        </div>
                                    </div>
                                </div>
                                @if ($showccissuestart)
                                <div class="col-sm-3 col-sm-offset-6 new-card-container">
                                    <div class="form-group prepend-icon">
                                        <label for="inputCardStart" class="field-icon">
                                            <i class="far fa-calendar-check"></i>
                                        </label>
                                        <input type="tel" name="ccstartdate" id="inputCardStart" class="field"
                                            placeholder="MM / YY ({{ Lang::get('client.creditcardcardstart') }})" autocomplete="cc-exp">
                                    </div>
                                </div>
                                <div class="col-sm-3 new-card-container">
                                    <div class="form-group prepend-icon">
                                        <label for="inputCardIssue" class="field-icon">
                                            <i class="fas fa-asterisk"></i>
                                        </label>
                                        <input type="tel" name="ccissuenum" id="inputCardIssue" class="field"
                                            placeholder="{{ Lang::get('client.creditcardcardissuenum') }}">
                                    </div>
                                </div>
                                @endif
                                <div class="form-group new-card-container w-100">
                                    <div id="inputDescriptionContainer" class="col-md-12">
                                        <div class="prepend-icon">
                                            <label for="inputDescription" class="field-icon">
                                                <i class="fas fa-edit"></i>
                                            </label>
                                            <input type="text" class="field" id="inputDescription" name="ccdescription" autocomplete="off"
                                                value=""
                                                placeholder="{{ Lang::get('client.paymentMethods.descriptionInput') }} {{ Lang::get('client.paymentMethodsManage.optional') }}" />
                                        </div>
                                    </div>
                                    @if ($allowClientsToRemoveCards)
                                    <div class="col-md-6" style="line-height: 32px;">
                                        <input type="hidden" name="nostore" value="1">
                                        <input type="checkbox" class="toggle-switch-success no-icheck" data-size="mini" checked="checked"
                                            name="nostore" id="inputNoStore" value="0" data-on-text="{{ Lang::get('client.yes') }}"
                                            data-off-text="{{ Lang::get('client.no') }}">
                                        <label for="inputNoStore" class="checkbox-inline no-padding">
                                            &nbsp;&nbsp;
                                            {{ Lang::get('client.creditCardStore') }}
                                        </label>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($shownotesfield)
                        <div class="sub-heading">
                            <span>{{ Lang::get('orderForm.additionalNotes') }}</span>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <textarea name="notes" class="field" rows="4" placeholder="{{ Lang::get('client.ordernotesdescription') }}">{!! $orderNotes ?? '' !!}</textarea>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if ($showMarketingEmailOptIn)
                        <div class="marketing-email-optin">
                            <h4>{{ Lang::get('client.emailMarketing.joinOurMailingList') }}</h4>
                            <p>{!! $marketingEmailOptInMessage !!}</p>
                            <input type="checkbox" name="marketingoptin" value="1" @if ($marketingEmailOptIn) checked @endif
                                class="no-icheck toggle-switch-success" data-size="small" data-on-text="{{ Lang::get('client.yes') }}"
                                data-off-text="{{ Lang::get('client.no') }}">
                        </div>
                        @endif

                        <div class="text-center">
                            @if ($accepttos)
                            <p>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="accepttos" id="accepttos" />
                                    &nbsp;
                                    {{ Lang::get('client.ordertosagreement') }}
                                    <a href="{{ $tosurl }}" target="_blank">{{ Lang::get('client.ordertos') }}</a>
                                </label>
                            </p>
                            @endif
                            @if (isset($captcha) && $captcha)
                            <div class="text-center margin-bottom">
                                @include('captcha')
                            </div>
                            @endif


                            <button type="submit" id="btnCompleteOrder"
                                class="btn btn-primary btn-lg disable-on-click spinner-on-click{{ ($captcha ?? null) ? $captcha->getButtonClass($captchaForm) : '' }}"
                                @if ($cartitems==0) disabled="disabled" @endif
                                onclick="this.value='{{ Lang::get('client.pleasewait') }}'">
                                {{ Lang::get('client.completeorder') }}
                                &nbsp;<i class="fas fa-arrow-circle-right"></i>
                            </button>

                        </div>
                    </form>

                    @if (isset($servedOverSsl))
                    <div class="alert alert-warning checkout-security-msg">
                        <i class="fas fa-lock"></i>
                        {{ Lang::get('client.ordersecure') }} (<strong>{{ $ipaddress }}</strong>) {{ Lang::get('client.ordersecure2') }}
                        <div class="clearfix"></div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ Theme::asset('js/jquery.payment.js') }}"></script>

@section('scripts')
<script>
    let img = '{{ Theme::asset("img/ccv.gif") }}'
    $('#new-ccv, #ppover').popover({
        html: true,
        trigger: 'hover',
        content: () => {
            return `<img src=${img} width='210' />`
        }
    })
</script>
@endsection