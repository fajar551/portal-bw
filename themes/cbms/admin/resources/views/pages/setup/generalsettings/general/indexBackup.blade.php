@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  General Settings</title>
@endsection

@section('content')
   <div class="main-content">
      <div class="page-content">
         <div class="container-fluid">
            <div class="row">
               <!-- Sidebar Shortcut -->

               <!-- End Sidebar -->
               <!-- MAIN CARD -->
               <div class="col-xl-12">
                  <div class="view-client-wrapper">
                     <div class="row">
                        <div class="col-12">
                           <div class="card-title mb-3">
                              <h4 class="mb-3">General Settings</h4>
                           </div>
                           @if ($message = Session::get('success'))
                              <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Changes Successful!</h5>
                                 <small>{{ $message }}</small>
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                 </button>
                              </div>
                           @endif
                           @if ($message = Session::get('error'))
                              <div class="alert alert-warning alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Something Wrong!</h5>
                                 <small>{{ $message }}</small>
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                 </button>
                              </div>
                           @endif
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           @include('includes.tabnavgeneralsettings')
                           <form id="settingsForm"
                              action="{{ route('admin.pages.setup.generalsettings.general.update') }}" method="POST">
                              @method('PUT')
                              @csrf
                              <div class="card p-3">
                                 <div class="tab-content" id="nav-tabContent">
                                    {{-- General Tab Content --}}
                                    <div class="tab-pane fade show active" id="nav-general" role="tabpanel"
                                       aria-labelledby="nav-general-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Company
                                             Name</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" placeholder="e.g Qwords.com"
                                                value="{{ $companyName }}" name="CompanyName">
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                Your Company Name as you want it to appear throughout the
                                                system
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Email
                                             Address</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="email" class="form-control" placeholder="email@yourcompany.com"
                                                value="{{ $email }}" name="Email">
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                The default sender address used for emails sent by CBMS
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Domain</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="url" class="form-control"
                                                placeholder="http://www.yourdomain.com/" value="{{ $domain }}"
                                                name="Domain">
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                The URL to your website homepage
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Logo URL</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="url" class="form-control" value="{{ $logoURL }}"
                                                name="LogoURL" placeholder="http://www.yourdomain.com/">
                                             <small class="m-0 pt-0 text-muted">
                                                Enter your logo URL to display in email messages or leave
                                                blank
                                                for none
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Pay To Text</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <textarea name="InvoicePayTo" id="InvoicePayTo" cols="20" rows="5"
                                                class="form-control">{{ $payTo }}</textarea>
                                             <small class="m-0 pt-0 text-muted">
                                                This text is displayed on the invoice as the Pay To details
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">CBMS System
                                             URL</label>
                                          <div class="col-sm-12 col-lg-8">
                                             <input type="text" class="form-control" value="{{ $systemURL }}"
                                                name="SystemURL">
                                             <small class="m-0 p-0 text-muted">
                                                The URL to your CBMS installation (SSL Recommended) eg.
                                                https://www.example.com/members/
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Template</label>
                                          <div class="col-sm-12 col-lg-2">
                                             <select name="Template" id="template" class="form-control" name="Template">
                                                @foreach ($themes as $key => $theme)
                                                   <option value="{{$theme['name']}}"
                                                      {{ $templateGeneral == $theme['name'] ? 'selected' : '' }}>
                                                      {{ Str::ucfirst($theme['name']) }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                The template you want CBMS to use
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Limit Activity
                                             Log</label>
                                          <div class="col-sm-12 col-lg-2">
                                             <input type="text" class="form-control" placeholder="10000"
                                                value="{{ $activityLimit }}" name="ActivityLimit">
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                The maximum number of System Level Activity Log entries you
                                                wish
                                                to
                                                retain
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Record to Display
                                             per
                                             Page</label>
                                          <div class="col-sm-12 col-lg-2">
                                             <select id="records-per-page" class="form-control"
                                                name="NumRecordsToDisplay">
                                                <option value="50" {{ $recordPerPage == 50 ? 'selected' : '' }}>50
                                                </option>
                                                <option value="100" {{ $recordPerPage == 100 ? 'selected' : '' }}>100
                                                </option>
                                                <option value="200" {{ $recordPerPage == 200 ? 'selected' : '' }}>200
                                                </option>
                                             </select>
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                The maximum number of System Level Activity Log entries you
                                                wish
                                                to
                                                retain
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Maintenance
                                             Mode</label>
                                          <div class="col-sm-12 col-lg-5 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" id="MaintenanceModeHidden"
                                                   type="hidden" value="0" name="MaintenanceMode">
                                                <input class="custom-control-input" type="checkbox" id="MaintenanceMode"
                                                   value="1" {{ $maintenanceMode == 1 ? 'checked' : '' }}
                                                   name="MaintenanceMode">
                                                <label class="custom-control-label" for="MaintenanceMode">Tick
                                                   to
                                                   enable - prevents client area access when
                                                   enabled</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Maintenance Mode
                                             Message</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <textarea cols="30" rows="5" class="form-control"
                                                name="MaintenanceModeMessage">{{ $maintenanceMessage }}</textarea>
                                          </div>

                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Maintenance Mode
                                             Redirect URL</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" value="{{ $maintenanceURL }}"
                                                name="MaintenanceModeURL">
                                             <small class="m-0 p-0 text-muted">If specified, redirects client
                                                area visitors
                                                to
                                                this URL when Maintenance Mode is enabled</small>
                                          </div>
                                       </div>
                                       {{-- <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Friendly
                                             URLs</label>
                                          <div class="col-sm-12 col-lg-2">
                                             <select class="form-control" name="SEOfriendlyurls">
                                                <option value="0">Full Friendly Rewrite</option>
                                             </select>
                                          </div>
                                          <div class="col-lg-3 col-sm-12 ">
                                             <div class="badge badge-success p-1 mt-2">SYSTEM-DETECTED</div>
                                          </div>
                                       </div> --}}
                                    </div>
                                    {{-- Localisation Tab Content --}}
                                    <div class="tab-pane fade" id="nav-localisation" role="tabpanel"
                                       aria-labelledby="nav-localisation-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             System Charset
                                          </label>
                                          <div class="col-sm-12 col-lg-4">
                                             <input type="text" class="form-control" value="{{ $systemCharset }}"
                                                name="Charset">
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                Default: utf-8
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Global Date Format
                                          </label>
                                          <div class="col-sm-12 col-lg-2">
                                             <select class="form-control" name="dateFormat">
                                                <option value="DD/MM/YYYY"
                                                   {{ $dateFormat == 'DD/MM/YYYY' ? 'selected' : '' }}>
                                                   DD/MM/YYYY</option>
                                                <option value="YYYY/MM/DD"
                                                   {{ $dateFormat == 'YYYY/MM/DD' ? 'selected' : '' }}>
                                                   YYYY/MM/DD</option>
                                                <option value="MM/DD/YYYY"
                                                   {{ $dateFormat == 'MM/DD/YYYY' ? 'selected' : '' }}>
                                                   MM/DD/YYYY</option>
                                             </select>
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                Choose numeric display format
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Client Date Format
                                          </label>
                                          <div class="col-sm-12 col-lg-3">
                                             <select name="ClientDateFormat" class="form-control">
                                                <option value="default"
                                                   {{ $clientDateFormat == 'full' ? 'selected' : '' }}>Use Global Date
                                                   Format</option>
                                                <option value="full"
                                                   {{ $clientDateFormat == 'full' ? 'selected' : '' }}>
                                                   1st January 2000</option>
                                                <option value="shortmonth"
                                                   {{ $clientDateFormat == 'shortmonth' ? 'selected' : '' }}>1st Jan
                                                   2000
                                                </option>
                                                <option value="fullday"
                                                   {{ $clientDateFormat == 'fullday' ? 'selected' : '' }}>Monday,
                                                   January
                                                   1st, 2000</option>
                                             </select>
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                Choose display style for clients (can be text based)
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Default Country
                                          </label>
                                          <div class="col-sm-12 col-lg-4">
                                             <select class="form-control" name="defaultCountry">
                                                <option value="US" {{ $defaultCountry == 'US' ? 'selected' : '' }}>
                                                   United
                                                   States</option>
                                                <option value="ID" {{ $defaultCountry == 'ID' ? 'selected' : '' }}>
                                                   Indonesia</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Default Language
                                          </label>
                                          <div class="col-sm-12 col-lg-2">
                                             <select class="form-control" name="language">
                                                <option value="english" {{ $language == 'english' ? 'selected' : '' }}>
                                                   English
                                                </option>
                                                <option value="indonesia"
                                                   {{ $language == 'indonesia' ? 'selected' : '' }}>
                                                   Bahasa Indonesia</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Enable Language Menu
                                          </label>
                                          <div class="col-sm-12 col-lg-10 p-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" id="allowLangChangeHidden"
                                                   type="hidden" value="off" name="AllowLanguageChange">
                                                <input class="custom-control-input" type="checkbox" id="allowLangChange"
                                                   value="on" name="AllowLanguageChange"
                                                   {{ $languageChange == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="allowLangChange">Allow
                                                   users to change the language of the system</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Dynamic Field Translations
                                          </label>
                                          <div class="col-sm-12 col-lg-10 p-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden"
                                                   id="dynamicFieldCustomHidden" value="0" name="EnableTranslations">
                                                <input class="custom-control-input" type="checkbox"
                                                   id="dynamicFieldCustom" value="1" name="EnableTranslations"
                                                   {{ $enableTranslation == 1 ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="dynamicFieldCustom">Enable
                                                   localisation of
                                                   supported database field values to multiple
                                                   languages</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Remove Extended UTF-8 Characters
                                          </label>
                                          <div class="col-sm-12 col-lg-10 p-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" id="CutUtf8Mb4Hidden" type="hidden"
                                                   value="off" name="CutUtf8Mb4">
                                                <input class="custom-control-input" type="checkbox" id="CutUtf8Mb4"
                                                   value="on" name="CutUtf8Mb4"
                                                   {{ $utfCutOption == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="CutUtf8Mb4">Automatically
                                                   remove 4 byte UTF-8
                                                   characters such as emoticons from customer ticket posts
                                                   and
                                                   emails</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Phone Numbers
                                          </label>
                                          <div class="col-sm-12 col-lg-10 p-2">
                                             <div class="custom-control custom-checkbox">
                                                <input name="PhoneNumberDropdown" class="custom-control-input"
                                                   type="hidden" id="phoneNumberCustomHidden" value="0">
                                                <input name="PhoneNumberDropdown" class="custom-control-input"
                                                   type="checkbox" id="phoneNumberCustom" value="1"
                                                   {{ $phoneNumberDropdown == 1 ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="phoneNumberCustom">Tick
                                                   to
                                                   enable international phone number input interface and
                                                   automatic formatting
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Ordering Tab Content --}}
                                    <div class="tab-pane fade" id="nav-ordering" role="tabpanel"
                                       aria-labelledby="nav-ordering-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Order Days Grace
                                          </label>
                                          <div class="col-sm-12 col-lg-2">
                                             <input type="number" class="form-control" name="OrderDaysGrace"
                                                value="{{ $orderDaysGrace }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-6">
                                             <p class="m-0 pt-2">
                                                The number of days to allow for payment of an order before
                                                being
                                                overdue
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default Order Form
                                             Template</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="row">
                                                @foreach ($orderformThemes as $key => $orderformTheme)
                                                   <div class="col-sm-6 col-lg-3 mb-3">
                                                      <div class="label-thumb">
                                                         <label for="{{ $orderformTheme['name'] }}">
                                                            <img
                                                               src="{{$orderformTheme['thumbnail_url']}}"
                                                               alt="thumbs.jpg">
                                                         </label>
                                                      </div>
                                                      <div class="custom-control custom-radio">
                                                         <input class="custom-control-input" type="radio"
                                                            name="OrderFormTemplate" id="{{ $orderformTheme['name'] }}"
                                                            value="{{ $orderformTheme['name'] }}"
                                                            {{ $orderFormTemplate == $orderformTheme['name'] ? 'checked' : '' }}>
                                                         <label class="custom-control-label" for="{{ $orderformTheme['name'] }}">
                                                            {{ $orderformTheme['description'] }}
                                                            {{ $orderformTheme['name'] == \App\Helpers\ThemeManager::orderformThemeDefault() ? '(Default)' : '' }}
                                                         </label>
                                                      </div>
                                                   </div>
                                                @endforeach
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Sidebar Toggle
                                             Option</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" id="OrderFormSidebarToggleHidden"
                                                   type="hidden" value="off" name="OrderFormSidebarToggle">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="OrderFormSidebarToggle" name="OrderFormSidebarToggle"
                                                   {{ $orderFormSidebarToggle == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="OrderFormSidebarToggle">
                                                   Tick to enable the display of a sidebar toggle button on
                                                   Order Form Product Selection Pages
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable TOS
                                             Acceptance</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" id="enableTOSAcceptHidden"
                                                   type="hidden" value="off" name="EnableTOSAccept">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="enableTOSAccept" name="EnableTOSAccept"
                                                   {{ $enableTOSAccept == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="enableTOSAccept">
                                                   If ticked, clients must agree to your Terms of Service
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Terms of Service
                                             URL</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <input type="text" class="form-control" name="TermsOfService"
                                                value="{{ $termsOfService }}">
                                             <small class="text-muted m-0 p-0">
                                                The URL to your Terms of Service page on your site (eg.
                                                http://www.example.com/tos.html)
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Auto Redirect on
                                             Checkout</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="AutoRedirectoInvoice" id="autoDirectOnCheckout1"
                                                   value="no_redirect"
                                                   {{ $autoDirectOnCheckout == 'no_redirect' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="autoDirectOnCheckout1">
                                                   Just show the order completed page (no payment redirect)
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="AutoRedirectoInvoice" id="autoDirectOnCheckout2"
                                                   value="to_invoice"
                                                   {{ $autoDirectOnCheckout == 'to_invoice' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="autoDirectOnCheckout2">
                                                   Automatically take the user to the invoice
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="AutoRedirectoInvoice" id="autoDirectOnCheckout3"
                                                   value="payment_gateway"
                                                   {{ $autoDirectOnCheckout == 'payment_gateway' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="autoDirectOnCheckout3">
                                                   Automatically forward the user to the payment gateway
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Allow Notes on
                                             Checkout</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" id="ShowNotesFieldonCheckoutHidden"
                                                   type="hidden" value="off" name="ShowNotesFieldonCheckout">
                                                <input class="custom-control-input" name="ShowNotesFieldonCheckout"
                                                   type="checkbox" value="on" id="ShowNotesFieldonCheckout"
                                                   {{ $noteOnCheckout == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ShowNotesFieldonCheckout">
                                                   Tick this box to show a field on the order form where
                                                   the
                                                   customer can enter additional info for staff
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Monthly Pricing</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="ProductMonthlyPricingBreakdownHidden"
                                                   name="ProductMonthlyPricingBreakdown">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="ProductMonthlyPricingBreakdown"
                                                   name="ProductMonthlyPricingBreakdown"
                                                   {{ $pricingBreakdown == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ProductMonthlyPricingBreakdown">
                                                   Tick this box to enable monthly pricing breakdown for
                                                   recurring terms on the order form
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Block Existing
                                             Domains</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="allowDomainTwiceHidden" name="AllowDomainsTwice">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="allowDomainTwice" name="AllowDomainsTwice"
                                                   {{ $allowDomainTwice == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="allowDomainTwice">
                                                   Tick this box to prevent orders being placed for domains
                                                   already in your system
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">No Invoice Email on
                                             Order</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="NoInvoiceEmailOnOrderHidden" name="NoInvoiceEmailOnOrder">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="NoInvoiceEmailOnOrder" name="NoInvoiceEmailOnOrder"
                                                   {{ $noInvoiceEmail == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="NoInvoiceEmailOnOrder">
                                                   Tick this box to not send an invoice created notice when
                                                   new
                                                   orders are placed
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Skip Fraud Check
                                             for
                                             Existing</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="SkipFraudForExistingHidden" name="SkipFraudForExisting">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="SkipFraudForExisting" name="SkipFraudForExisting"
                                                   {{ $skipFraud == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="SkipFraudForExisting">
                                                   Tick this box to skip the fraud check for existing
                                                   clients
                                                   who already have an active order
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Only Auto Provision
                                             for
                                             Existing</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="AutoProvisionExistingOnlyHidden" name="AutoProvisionExistingOnly">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="AutoProvisionExistingOnly" name="AutoProvisionExistingOnly"
                                                   {{ $autoProvision == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AutoProvisionExistingOnly">
                                                   Tick this box to always leave orders by new clients
                                                   pending
                                                   for manual review (no auto setup/registration)
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable Random
                                             Usernames</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="GenerateRandomUsernameHidden" name="GenerateRandomUsername">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="GenerateRandomUsername" name="GenerateRandomUsername"
                                                   {{ $generateRandomUsername == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="GenerateRandomUsername">
                                                   Tick this box to generate random usernames for services
                                                   rather than use the first 8 letters of the domain
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Signup Anniversary
                                             Prorata</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="ProrataClientsAnniversaryDateHidden"
                                                   name="ProrataClientsAnniversaryDate">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="ProrataClientsAnniversaryDate" name="ProrataClientsAnniversaryDate"
                                                   {{ $prorataClientData == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ProrataClientsAnniversaryDate">
                                                   Prorata products to the clients signup anniversary date
                                                   if
                                                   prorata is enabled (ie. all items due on the same date
                                                   per
                                                   client)
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Domains Tab Content --}}
                                    <div class="tab-pane fade" id="nav-domains" role="tabpanel"
                                       aria-labelledby="nav-domains-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Domain Registration
                                             Options</label>
                                          <div class="col-sm-12 col-lg-8">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="AllowRegisterHidden" name="AllowRegister">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="AllowRegister" name="AllowRegister"
                                                   {{ $allowRegister == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowRegister">
                                                   Allow clients to register domains with you
                                                </label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="AlloTransferHidden" name="AllowTransfer">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="AlloTransfer" name="AllowTransfer"
                                                   {{ $allowTransfer == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AlloTransfer">
                                                   Allow clients to transfer a domain to you
                                                </label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="AllowOwnDomainHidden" name="AllowOwnDomain">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="AllowOwnDomain" name="AllowOwnDomain"
                                                   {{ $allowOwnDomain == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowOwnDomain">
                                                   Allow clients to use their own domain
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable Renewal
                                             Orders</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control  custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="EnableDomainRenewalOrdersHidden" name="EnableDomainRenewalOrders">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="EnableDomainRenewalOrders" name="EnableDomainRenewalOrders"
                                                   {{ $enableRenewDomain == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EnableDomainRenewalOrders">
                                                   Tick this box to show the Domain Renewals cart category
                                                   allowing clients to place renewal orders early if they
                                                   wish
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Auto Renew on
                                             Payment</label>
                                          <div class="col-sm-12 col-lg-8 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="AutoRenewDomainsonPaymentHidden" name="AutoRenewDomainsonPayment">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="AutoRenewDomainsonPayment" name="AutoRenewDomainsonPayment"
                                                   {{ $autoRenewOnPayment == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AutoRenewDomainsonPayment">
                                                   Automatically renew domains which are set to a supported
                                                   registrar when they are paid for
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Auto Renew Requires
                                             Product</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control  custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="FreeDomainAutoRenewRequiresProductHidden"
                                                   name="FreeDomainAutoRenewRequiresProduct">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="FreeDomainAutoRenewRequiresProduct"
                                                   name="FreeDomainAutoRenewRequiresProduct"
                                                   {{ $renewRequireProd == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="FreeDomainAutoRenewRequiresProduct">
                                                   Only auto renew free domains that have a corresponding
                                                   active product/service for the same domain
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default Auto
                                             Renewal
                                             Setting</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="DomainAutoRenewDefaultHidden" name="DomainAutoRenewDefault">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="DomainAutoRenewDefault" name="DomainAutoRenewDefault"
                                                   {{ $autoRenewDefault == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="DomainAutoRenewDefault">
                                                   This can be changed per domain, but sets the default
                                                   of
                                                   whether invoices should auto generate for expiring
                                                   domains
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Create
                                             To-Do
                                             List
                                             Entries</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control  custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="DomainToDoListEntriesHidden" name="DomainToDoListEntries">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="DomainToDoListEntries" name="DomainToDoListEntries"
                                                   {{ $domainTodoList == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="DomainToDoListEntries">
                                                   Tick this box to create To-Do list entries for
                                                   new
                                                   or
                                                   failed
                                                   domain actions that require manual intervention
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Allow IDN
                                             Domains</label>
                                          <div class="col-sm-12 col-lg-8 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="AllowIDNDomainsHidden" name="AllowIDNDomains">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="AllowIDNDomains" name="AllowIDNDomains"
                                                   {{ $allowIDNDomains == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowIDNDomains">
                                                   Tick this box to enable Internationalized Domain
                                                   Names
                                                   (IDN)
                                                   support.
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Domain
                                             Grace
                                             and
                                             Redemption Fees</label>
                                          <div class="col-sm-12 col-lg-8 pt-2">
                                             <div class="custom-control custom-radio d-inline mr-2">
                                                <input class="custom-control-input" type="radio"
                                                   name="DisableDomainGraceAndRedemptionFees"
                                                   id="DisableDomainGraceAndRedemptionFeesOn" value="on"
                                                   {{ $graceRedemptionFee == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="DisableDomainGraceAndRedemptionFeesOn">
                                                   Enable
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio d-inline">
                                                <input class="custom-control-input" type="radio"
                                                   name="DisableDomainGraceAndRedemptionFees"
                                                   id="DisableDomainGraceAndRedemptionFeesOff" value="off"
                                                   {{ $graceRedemptionFee == 'off' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="DisableDomainGraceAndRedemptionFeesOff">
                                                   Disable
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Domain
                                             Grace
                                             and
                                             Redemption Fee Billing</label>
                                          <div class="col-sm-12 col-lg-8 pt-2">
                                             <div class="custom-control custom-radio mr-2">
                                                <input class="custom-control-input" type="radio"
                                                   name="domainGraceAndRedemptionBilling" id="exampleRadios3"
                                                   value="option3" checked>
                                                <label class="custom-control-label" for="exampleRadios3">
                                                   Add Grace and Redemption Fees to existing
                                                   invoice
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="domainGraceAndRedemptionBilling" id="exampleRadios4"
                                                   value="option4">
                                                <label class="custom-control-label" for="exampleRadios4">
                                                   Generate a new invoice for the domain renewal
                                                   including
                                                   any
                                                   Grace and Redemption Fees
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default
                                             Nameserver
                                             1</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <input type="text" class="form-control" placeholder="ns1.yourdomain.com"
                                                name="DefaultNameserver1" value="{{ $nameserver1 }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default
                                             Nameserver
                                             2</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <input type="text" class="form-control" placeholder="ns2.yourdomain.com"
                                                name="DefaultNameserver2" value="{{ $nameserver2 }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default
                                             Nameserver
                                             3</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <input type="text" class="form-control" name="DefaultNameserver3"
                                                value="{{ $nameserver3 }}" placeholder="ns3.yourdomain.com">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default
                                             Nameserver
                                             4</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <input type="text" class="form-control" name="DefaultNameserver4"
                                                value="{{ $nameserver4 }}" placeholder="ns4.yourdomain.com">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default
                                             Nameserver
                                             5</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <input type="text" class="form-control" name="DefaultNameserver5"
                                                value="{{ $nameserver5 }}" placeholder="ns5.yourdomain.com">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Use Clients
                                             Details</label>
                                          <div class="col-sm-12 col-lg-8 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="hidden" value="off"
                                                   id="RegistrarAdminUseClientDetailsHidden"
                                                   name="RegistrarAdminUseClientDetails">
                                                <input class="custom-control-input" type="checkbox" value="on"
                                                   id="RegistrarAdminUseClientDetails"
                                                   name="RegistrarAdminUseClientDetails"
                                                   {{ $useClientDetails == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="RegistrarAdminUseClientDetails">
                                                   Tick this box to use clients details for the
                                                   Billing/Admin/Tech contacts
                                                </label>
                                             </div>
                                          </div>
                                       </div>

                                       <div class="collapse {{ $useClientDetails == 'on' ? 'show' : 'hide' }}"
                                          id="clientField">
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">First
                                                Name</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="RegistrarAdminFirstName"
                                                   value="">
                                             </div>
                                             <div class="col-sm-12 col-lg-4 pt-2">
                                                <p class="m-0 p-0">
                                                   Default Billing/Admin/Tech Contact Details
                                                </p>
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Last
                                                Name</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="RegistrarAdminLastName"
                                                   value="">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Company
                                                Name</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="RegistrarAdminCompanyName"
                                                   value="">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Email
                                                Address</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control"
                                                   name="RegistrarAdminEmailAddress">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Address
                                                1</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="RegistrarAdminAddress1">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Address
                                                2</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="RegistrarAdminAddress2">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">City</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="RegistrarAdminCity">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">State/Region</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control"
                                                   name="RegistrarAdminStateProvince">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Postcode</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="RegistrarAdminPostalCode">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Country</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <select name="RegistrarAdminCountry" id="country-name"
                                                   class="form-control">
                                                   <option value="ID">Indonesia</option>
                                                </select>
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Phone
                                                Number</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="number" class="form-control" name="RegistrarAdminPhone">
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Mail Tab Content --}}
                                    <div class="tab-pane fade" id="nav-mail" role="tabpanel"
                                       aria-labelledby="nav-mail-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Mail
                                             Type</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <select name="MailType" id="MailType" class="form-control">
                                                <option value="php_mail"
                                                   {{ $mailType == 'php_mail' ? 'selected' : '' }}>
                                                   PHP
                                                   Mail</option>
                                                <option value="mail" {{ $mailType == 'mail' ? 'selected' : '' }}>e-Mail
                                                </option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Mail
                                             Encoding</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <select name="MailEncoding" id="MailEncoding" class="form-control">
                                                <option value="8bit" {{ $mailEncoding == '' ? 'selected' : '' }}>8bit
                                                </option>
                                                <option value="7bit" {{ $mailEncoding == '' ? 'selected' : '' }}>7bit
                                                </option>
                                                <option value="binary" {{ $mailEncoding == '' ? 'selected' : '' }}>
                                                   binary
                                                </option>
                                                <option value="base64">
                                                   {{ $mailEncoding == '' ? 'selected' : '' }}base64
                                                </option>
                                                <option value="quoted-printable"
                                                   {{ $mailEncoding == '' ? 'selected' : '' }}>
                                                   quoted-printable
                                                </option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">SMTP
                                             Port</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" placeholder="25" name="SMTPPort"
                                                value="{{ $smtpPort }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                The port your mail server uses
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">SMTP
                                             Host</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="text" class="form-control" name="SMTPHost"
                                                value="{{ $smtpHost }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">SMTP
                                             Username</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="text" class="form-control" name="SMTPUsername"
                                                value="{{ $smtpUsername }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">SMTP
                                             Password</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="password" class="form-control" name="SMTPPassword"
                                                value="{{ $smtpPassword }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">SMTP SSL
                                             Type</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="d-flex align-items-center py-2">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                   <input class="custom-control-input" type="radio" name="SMTPSSL"
                                                      id="SMTPSSL_none" value="none"
                                                      {{ $smtpSSL == 'none' ? 'checked' : '' }}>
                                                   <label class="custom-control-label" for="SMTPSSL_none">None</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                   <input class="custom-control-input" type="radio" name="SMTPSSL"
                                                      id="SMTPSSL_ssl" value="ssl"
                                                      {{ $smtpSSL == 'ssl' ? 'checked' : '' }}>
                                                   <label class="custom-control-label" for="SMTPSSL_ssl">SSL</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                   <input class="custom-control-input" type="radio" name="SMTPSSL"
                                                      id="SMTPSSL_tls" value="tls"
                                                      {{ $smtpSSL == 'tls' ? 'checked' : '' }}>
                                                   <label class="custom-control-label" for="SMTPSSL_tls">TLS</label>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Global
                                             Email
                                             Signature</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="Signature" id="Signature" class="form-control" cols="30"
                                                rows="5">{{ $mailSignature }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Global
                                             Email
                                             CSS
                                             Styling</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="EmailCSS" id="EmailCSS" class="form-control" cols="30"
                                                rows="5" placeholder="">{{ $mailCSS }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Client
                                             Email
                                             Header
                                             Content</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="EmailGlobalHeader" id="EmailGlobalHeader"
                                                class="form-control" cols="30" rows="5"
                                                placeholder="">{{ htmlspecialchars_decode($globalHeader) }}</textarea>
                                             <small class="p-0 m-0 text-muted">
                                                Any text you enter here will be prefixed to the top
                                                of
                                                all
                                                client email templates sent out by the system. HTML
                                                is
                                                accepted.
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Client
                                             Email
                                             Footer
                                             Content</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="EmailGlobalFooter" id="EmailGlobalFooter"
                                                class="form-control" cols="30" rows="5"
                                                placeholder="">{{ htmlspecialchars_decode($globalFooter) }}</textarea>
                                             <small class="p-0 m-0 text-muted">
                                                Any text you enter here will be added to the bottom
                                                of
                                                all
                                                client email templates sent out by the system. HTML
                                                is
                                                accepted.
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">System
                                             Email
                                             From
                                             Name</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" name="SystemEmailsFromName"
                                                value="{{ $emailFromName }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">System
                                             Email
                                             From
                                             Email</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" name="SystemEmailsFromEmail"
                                                value="{{ $emailFromEmail }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">BCC
                                             Messages</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="text" class="form-control" name="BCCMessages"
                                                value="{{ $bccMessage }}">
                                             <small class="text-muted m-0 p-0">
                                                If you want copies of all emails sent by the system
                                                sent
                                                to
                                                an
                                                address of yours enter the address here. You may
                                                enter
                                                multiple
                                                addresses seperated by a comma (,)
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Presales
                                             Form
                                             Destination</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <select name="ContactFormDept" id="ContactFormDept" class="form-control">
                                                <option value="none" {{ $formDept == 'none' ? 'selected' : '' }}>Choose
                                                   a
                                                   Department - OR - Send to
                                                   email
                                                   address below</option>
                                                <option value="technical"
                                                   {{ $formDept == 'technical' ? 'selected' : '' }}>
                                                   Technical
                                                   Support</option>
                                                <option value="billing" {{ $formDept == 'billing' ? 'selected' : '' }}>
                                                   Billing
                                                   Support</option>
                                                <option value="sales" {{ $formDept == 'sales' ? 'selected' : '' }}>
                                                   Sales
                                                   Support
                                                </option>
                                                <option value="developer"
                                                   {{ $formDept == 'developer' ? 'selected' : '' }}>
                                                   Developer
                                                   Team</option>
                                                <option value="remote" {{ $formDept == 'remote' ? 'selected' : '' }}>
                                                   Remote
                                                   Support</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Presales
                                             Contact
                                             Form
                                             Email</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" name="ContactFormTo"
                                                id="ContactFormTo" value="{{ $formContact }}">
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Support Tab Content --}}
                                    <div class="tab-pane fade" id="nav-support" role="tabpanel"
                                       aria-labelledby="nav-support-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Support
                                             Module</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <select name="SupportModule" id="support-module" class="form-control">
                                                <option value="built_in_system"
                                                   {{ $supportModule == 'built_in_system' ? 'selected' : '' }}>
                                                   CBMS Built-in-System
                                                </option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Support
                                             Ticket
                                             Mask
                                             Format</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="text" name="TicketMask" class="form-control"
                                                value="{{ $ticketMask }}">
                                             <small class="p-0 m-0">Key: %A - Uppercase letter | %a -
                                                Lowercase
                                                letter | %n -
                                                Number | %y - Year | %m - Month | %d - Day | %i -
                                                Ticket
                                                ID</small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Ticket
                                             Reply
                                             List
                                             Order</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <select name="SupportTicketOrder" id="ticket-reply-list-order"
                                                class="form-control">
                                                <option value="ASC" {{ $ticketOrder == 'ASC' ? 'selected' : '' }}>
                                                   Ascending (Oldest to Newest)
                                                </option>
                                                <option value="DESC" {{ $ticketOrder == 'DESC' ? 'selected' : '' }}>
                                                   Descending (Newest to Oldest)
                                                </option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Ticket
                                             Reply
                                             Email
                                             Limit</label>
                                          <div class="col-sm-12 col-lg-2">
                                             <input type="text" name="TicketEmailLimit" class="form-control"
                                                value="{{ $ticketLimit }}">
                                          </div>
                                          <div class="col-lg-4 col-sm-12">
                                             <p class="m-0 pt-2">
                                                Email sending limit per 15 minutes
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Show Client
                                             Only
                                             Departments</label>
                                          <div class="col-sm-12 col-lg-10 ">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="ShowClientOnlyDeptsHidden" name="ShowClientOnlyDepts" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="ShowClientOnlyDepts" name="ShowClientOnlyDepts" value="on"
                                                   {{ $clientOnlyDept == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ShowClientOnlyDepts">Tick
                                                   to
                                                   show client only departments to guests (not
                                                   logged
                                                   in
                                                   visitors)</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Client
                                             Tickets
                                             Require
                                             Login</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="RequireLoginforClientTicketsHidden"
                                                   name="RequireLoginforClientTickets" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="RequireLoginforClientTickets" name="RequireLoginforClientTickets"
                                                   value="on" {{ $requireLoginClient == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="RequireLoginforClientTickets">Require
                                                   login by the owning client for viewing tickets
                                                   assigned
                                                   to a
                                                   client</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Knowledgebase
                                             Suggestions</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="SupportTicketKBSuggestionsHidden" name="SupportTicketKBSuggestions"
                                                   value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="SupportTicketKBSuggestions" name="SupportTicketKBSuggestions"
                                                   value="on" {{ $knowledgebaseSuggestion == 'on' ? ' checked' : '' }}>
                                                <label class="custom-control-label" for="SupportTicketKBSuggestions">Show
                                                   suggested KB articles to a user as they enter a
                                                   support
                                                   ticket message</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Attachment
                                             Thumbnail
                                             Previews</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="AttachmentThumbnailsHidden" name="AttachmentThumbnails" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AttachmentThumbnails" name="AttachmentThumbnails" value="on"
                                                   {{ $attachmentThumbnails == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AttachmentThumbnails">Tick
                                                   to
                                                   enable thumbnail previews of image attachments
                                                   (requires
                                                   GD)</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Support
                                             Ticket
                                             Rating</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="TicketRatingEnabledHidden" name="TicketRatingEnabled" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="TicketRatingEnabled" name="TicketRatingEnabled" value="on"
                                                   {{ $ticketRatingEnabled == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="TicketRatingEnabled">Allow
                                                   users to rate support ticket replies from
                                                   staff</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Auto Add
                                             Carbon
                                             Copy
                                             Recipients</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="TicketAddCarbonCopyRecipientsHidden"
                                                   name="TicketAddCarbonCopyRecipients" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="TicketAddCarbonCopyRecipients" name="TicketAddCarbonCopyRecipients"
                                                   value="on" {{ $ticketCarbon == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="TicketAddCarbonCopyRecipients">Read
                                                   and
                                                   add carbon copy recipients from incoming emails
                                                   for
                                                   tickets
                                                   opened via email</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Prevent
                                             Email
                                             Reopening</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="PreventEmailReopeningHidden" name="PreventEmailReopening"
                                                   value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="PreventEmailReopening" name="PreventEmailReopening" value="on"
                                                   {{ $preventEmailReopening == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="PreventEmailReopening">Tick
                                                   to
                                                   prevent email replies from re-opening closed
                                                   tickets
                                                   and
                                                   to
                                                   send an email advising to open a new ticket or
                                                   update
                                                   the
                                                   existing ticket for clients.</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Update Last
                                             Reply
                                             Timestamp</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio"
                                                   name="updateLastReplyTimestamp" id="UpdateLastReplyTimestamp1"
                                                   value="always"
                                                   {{ $lastReplyTimestamp == 'always' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="UpdateLastReplyTimestamp1">Every
                                                   time a
                                                   reply is made (Default) </label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio"
                                                   name="updateLastReplyTimestamp" id="UpdateLastReplyTimestamp2"
                                                   value="specific"
                                                   {{ $lastReplyTimestamp == 'specific' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="UpdateLastReplyTimestamp2">Every
                                                   time
                                                   for staff replies, only on a change of status
                                                   for
                                                   clients</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Disable
                                             Reply
                                             Email
                                             Logging</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="DisableSupportTicketReplyEmailsLoggingHidden"
                                                   name="DisableSupportTicketReplyEmailsLogging" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="DisableSupportTicketReplyEmailsLogging"
                                                   name="DisableSupportTicketReplyEmailsLogging" value="on"
                                                   {{ $emailsLogging == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="DisableSupportTicketReplyEmailsLogging">Do
                                                   not
                                                   create email log entry for ticket replies (text
                                                   is
                                                   already
                                                   logged in ticket so saves disk space)</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Allowed
                                             File
                                             Attachment
                                             Types</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control"
                                                placeholder=".jpg,.gif,.jpeg,.png,.txt,.pdf" name="TicketAllowedFileTypes"
                                                value="{{ $allowedFileTypes }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                Separate multiple extensions with a comma
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Service
                                             Status
                                             Require
                                             Login</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="NetworkIssuesRequireLoginHidden" name="NetworkIssuesRequireLogin"
                                                   value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="NetworkIssuesRequireLogin" name="NetworkIssuesRequireLogin"
                                                   value="on" {{ $networkIssueLogin == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="NetworkIssuesRequireLogin">Require
                                                   a
                                                   login to view the server status & network issues
                                                   pages</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Include
                                             Product
                                             Downloads</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="DownloadsIncludeProductLinkedHidden"
                                                   name="DownloadsIncludeProductLinked" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="DownloadsIncludeProductLinked" name="DownloadsIncludeProductLinked"
                                                   value="on" {{ $downloadProductLinked == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="DownloadsIncludeProductLinked">
                                                   Tick to
                                                   include Product Associated Downloads in the
                                                   Downloads
                                                   Directory</label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Invoices Tab Content --}}
                                    <div class="tab-pane fade" id="nav-invoices" role="tabpanel"
                                       aria-labelledby="nav-invoices-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Continuous
                                             Invoice
                                             Generation</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="ContinuousInvoiceGenerationHidden"
                                                   name="ContinuousInvoiceGeneration" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="ContinuousInvoiceGeneration" name="ContinuousInvoiceGeneration"
                                                   value="on" {{ $invoiceGeneration == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ContinuousInvoiceGeneration">If
                                                   enabled, invoices will be generated for each
                                                   cycle
                                                   even
                                                   if
                                                   the previous invoice remains unpaid</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable
                                             Metric
                                             Usage
                                             Invoicing</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="MetricUsageInvoicingHidden" name="MetricUsageInvoicing" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="MetricUsageInvoicing" name="MetricUsageInvoicing" value="on"
                                                   {{ $metricInvoice == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="MetricUsageInvoicing">Tick
                                                   to
                                                   enable invoicing of metric usage for all priced
                                                   product
                                                   metrics</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable PDF
                                             Invoices</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="EnablePDFInvoicesHidden" name="EnablePDFInvoices" value="off">
                                                <input type="checkbox" class="custom-control-input" id="EnablePDFInvoices"
                                                   name="EnablePDFInvoices" value="on"
                                                   {{ $pdfInvoiceEnable == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EnablePDFInvoices">Tick
                                                   to
                                                   send PDF versions of invoices along with invoice
                                                   emails</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">PDF Paper
                                             Size</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <select name="PDFPaperSize" id="pdf-paper-size" class="form-control">
                                                <option value="A0" {{ $pdfPaperSize == 'A0' ? 'selected' : '' }}>A0
                                                </option>
                                                <option value="A1" {{ $pdfPaperSize == 'A1' ? 'selected' : '' }}>A1
                                                </option>
                                                <option value="A2" {{ $pdfPaperSize == 'A2' ? 'selected' : '' }}>A2
                                                </option>
                                                <option value="A3" {{ $pdfPaperSize == 'A3' ? 'selected' : '' }}>A3
                                                </option>
                                                <option value="A4" {{ $pdfPaperSize == 'A4' ? 'selected' : '' }}>A4
                                                </option>
                                                <option value="A5" {{ $pdfPaperSize == 'A5' ? 'selected' : '' }}>A5
                                                </option>
                                             </select>
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                Choose the paper format to use when generating PDF
                                                files
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label mt-2">PDF
                                             Font
                                             Family</label>
                                          <div class="col-sm-12 col-lg-10 pt-3">
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" name="TCPDFFont"
                                                   id="TCPDFFont" value="courier"
                                                   {{ $pdfFontFamily == 'courier' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="TCPDFFont">Courier</label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" name="TCPDFFont"
                                                   id="TCPDFFont2" value="freesans"
                                                   {{ $pdfFontFamily == 'freesans' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="TCPDFFont2">Freesans</label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" name="TCPDFFont"
                                                   id="TCPDFFont3" value="helvetica"
                                                   {{ $pdfFontFamily == 'helvetica' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="TCPDFFont3">Helvetica</label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" name="TCPDFFont"
                                                   id="TCPDFFont4" value="times"
                                                   {{ $pdfFontFamily == 'times' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="TCPDFFont4">Times<label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" name="TCPDFFont"
                                                   id="TCPDFFont5" value="dejavusans"
                                                   {{ $pdfFontFamily == 'dejavusans' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="TCPDFFont5">Dejavusans<label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" id="TCPDFFont6"
                                                   value="custom" name="TCPDFFont" @if ($pdfFontFamily !== 'courier' && $pdfFontFamily !== 'freesans' && $pdfFontFamily !== 'helvetica' && $pdfFontFamily !== 'times' && $pdfFontFamily !== 'dejavusans') checked @endif>
                                                <label class="custom-control-label" for="TCPDFFont6">Custom<label>
                                             </div>
                                             <div class="form-group">
                                                <div class="collapse" id="TCPDFFontTextInput">
                                                   <input type="text" id="TCPDFFontTextField"
                                                      class="form-control d-inline mt-2 w-50" name="TCPDFFont"
                                                      placeholder="Enter your font name here"
                                                      value="{{ ucWords($pdfFontFamily) }}" disabled>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Store
                                             Client
                                             Data
                                             Snapshot</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="StoreClientDataSnapshotOnInvoiceCreationHidden"
                                                   name="StoreClientDataSnapshotOnInvoiceCreation" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="StoreClientDataSnapshotOnInvoiceCreation"
                                                   name="StoreClientDataSnapshotOnInvoiceCreation" value="on"
                                                   {{ $clientSnapshot == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="StoreClientDataSnapshotOnInvoiceCreation">Preserve
                                                   client details upon invoice generation to
                                                   prevent
                                                   profile
                                                   changes for existing invoices</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable mass
                                             Payment</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input" id="EnableMassPayHidden"
                                                   name="EnableMassPay" value="off">
                                                <input type="checkbox" class="custom-control-input" id="EnableMassPay"
                                                   name="EnableMassPay" value="on"
                                                   {{ $massPayment == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EnableMassPay">Tick
                                                   to
                                                   enable the multiple invoice payment options on
                                                   the
                                                   client
                                                   area homepage</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Clients
                                             Choose
                                             Gateway</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="AllowCustomerChangeInvoiceGatewayHidden"
                                                   name="AllowCustomerChangeInvoiceGateway" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AllowCustomerChangeInvoiceGateway"
                                                   name="AllowCustomerChangeInvoiceGateway" value="on"
                                                   {{ $clientChangeGateway == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="AllowCustomerChangeInvoiceGateway">Tick
                                                   to
                                                   allow clients to choose the gateway they pay
                                                   with
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Group
                                             Similar
                                             Line
                                             Items</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="GroupSimilarLineItemsHidden" name="GroupSimilarLineItems"
                                                   value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="GroupSimilarLineItems" name="GroupSimilarLineItems" value="on"
                                                   {{ $groupSimiliarItems == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="GroupSimilarLineItems">Tick
                                                   to enable automatically grouping identical line
                                                   items
                                                   into a quantity x description format</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Cancellation
                                             Request
                                             Handling</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="AutoCancellationRequestsHidden" name="AutoCancellationRequests"
                                                   value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AutoCancellationRequests" name="AutoCancellationRequests" value="on"
                                                   {{ $autoCancel == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AutoCancellationRequests">Tick
                                                   to
                                                   automatically cancel outstanding unpaid invoices
                                                   when a
                                                   cancellation request is submitted</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Automatic
                                             Subscription
                                             Management</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="AutoCancelSubscriptionsHidden" name="AutoCancelSubscriptions">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AutoCancelSubscriptions" name="AutoCancelSubscriptions"
                                                   {{ $autoSubs == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AutoCancelSubscriptions">Tick
                                                   to
                                                   auto-cancel existing subscription agreements
                                                   (eg.
                                                   PayPal
                                                   Subscriptions) on Upgrade or
                                                   Cancellation.</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable
                                             Proforma
                                             Invoicing</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="EnableProformaInvoicingHidden" name="EnableProformaInvoicing"
                                                   value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="EnableProformaInvoicing" name="EnableProformaInvoicing" value="on"
                                                   {{ $proformaInvoicing == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EnableProformaInvoicing">Tick
                                                   to
                                                   enable proforma invoicing for unpaid
                                                   invoices</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Sequential
                                             Paid
                                             Invoice
                                             Numbering</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="SequentialInvoiceNumberingHidden" name="SequentialInvoiceNumbering"
                                                   value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="SequentialInvoiceNumbering" name="SequentialInvoiceNumbering"
                                                   value="on" {{ $seqInvoiceNumbering == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="SequentialInvoiceNumbering">Tick
                                                   this box to enable automatic sequential
                                                   numbering of
                                                   paid
                                                   invoices</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Sequential
                                             Invoice
                                             Number Format</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control"
                                                name="SequentialInvoiceNumberFormat" placeholder="{NUMBER}"
                                                value="{{ $seqInvoiceNumberFormat }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2 font-size-13">
                                                Available auto-insert tags are: {YEAR} {MONTH} {DAY}
                                                {NUMBER}
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Next Paid
                                             Invoice
                                             Number</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" placeholder="1"
                                                name="SequentialInvoiceNumberValue"
                                                value="{{ $seqInvoiceNumberValue }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-7">
                                             <p class="m-0 pt-2">
                                                The next invoice number that will be assigned
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Late Fee
                                             Type</label>
                                          <div class="col-sm-12 col-lg-5 pt-2">
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" name="lateFeeType"
                                                   id="lateFeeType1" value="percentage"
                                                   {{ $lateFeeType == 'percentage' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="lateFeeType1">Percentage</label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" name="lateFeeType"
                                                   id="lateFeeType2" value="fixed_amount"
                                                   {{ $lateFeeType == 'fixed_amount' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="lateFeeType2">Fixed
                                                   Amount</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Late Fee
                                             Amount</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" name="InvoiceLateFeeAmount"
                                                value="{{ $lateFeeAmount }}" placeholder="10.00">
                                          </div>
                                          <div class="col-sm-12 col-lg-7">
                                             <p class="m-0 pt-2 my-1 font-size-13">
                                                Enter the amount (percentage or monetary value) to
                                                apply
                                                to
                                                late
                                                invoices (set to 0 to disable)
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Late Fee
                                             Minimum</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" name="LateFeeMinimum"
                                                value="{{ $lateFeeMinimum }}" placeholder="0.00">
                                          </div>
                                          <div class="col-sm-12 col-lg-7">
                                             <p class="m-0 pt-2 my-1 font-size-13">
                                                Enter the minimum amount to charge in cases where
                                                the
                                                calculated
                                                late fee falls below this figure
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Accepted
                                             Credit
                                             Card
                                             Types</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <select name="AcceptedCardTypes[]" id="credit-card-list"
                                                class="form-control" multiple>
                                                @foreach ($acceptedCard as $card)
                                                   <option value="{{ $card }}" @foreach ($activeCard as $active) {{ $card == $active ? 'selected' : '' }} @endforeach>
                                                      {{ $card }}</option>
                                                @endforeach
                                             </select>
                                             <small class="m-0 p-0">
                                                Hold/Use Ctrl+Click to select Multiple Card Types
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Issue
                                             Number/Start
                                             Date</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="ShowCCIssueStartHidden" name="ShowCCIssueStart" value="off">
                                                <input type="checkbox" class="custom-control-input" id="ShowCCIssueStart"
                                                   name="ShowCCIssueStart" value="on"
                                                   {{ $ccIssueStart == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ShowCCIssueStart">Tick
                                                   to
                                                   show these fields for credit card
                                                   payments</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Invoice #
                                             Incrementation</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" name="InvoiceIncrement"
                                                value="{{ $invoiceIncrement }}" placeholder="1">
                                          </div>
                                          <div class="col-sm-12 col-lg-7">
                                             <p class="m-0 pt-2 my-1 font-size-13">
                                                Enter the difference you want between invoice
                                                numbers
                                                generated
                                                by the system (Default: 1)
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Invoice
                                             Starting
                                             #</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" name="InvoiceStartNumber">
                                          </div>
                                          <div class="col-sm-12 col-lg-7">
                                             <p class="m-0 pt-2 my-1 font-size-13">
                                                Enter to set the next invoice number, must be
                                                greater
                                                than
                                                last
                                                #{{ $lastInvoiceId }} (Blank for no change)
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Credit Tab Content --}}
                                    <div class="tab-pane fade" id="nav-credit" role="tabpanel"
                                       aria-labelledby="nav-credit-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable/Disable</label>
                                          <div class="col-sm-12 col-lg-8">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="AddFundsEnabledHidden" name="AddFundsEnabled" value="off">
                                                <input type="checkbox" class="custom-control-input" id="AddFundsEnabled"
                                                   name="AddFundsEnabled" value="on"
                                                   {{ $addFundsEnabled == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AddFundsEnabled">Tick
                                                   this
                                                   box to enable adding of funds by clients from
                                                   the
                                                   client
                                                   area</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Minimum
                                             Deposit</label>
                                          <div class="col-sm-12 col-lg-4">
                                             <input type="text" class="form-control" name="AddFundsMinimum"
                                                placeholder="100000.00" value="{{ $addFundsMinimum }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-6">
                                             <p class="m-0 pt-2">
                                                Enter the minimum amount a client can add in a
                                                single
                                                transaction
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Maximum
                                             Deposit</label>
                                          <div class="col-sm-12 col-lg-4">
                                             <input type="text" class="form-control" placeholder="10000000.00"
                                                name="AddFundsMaximum" value="{{ $addFundsMaximum }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-6">
                                             <p class="m-0 pt-2">
                                                Enter the maximum amount a client can add in a
                                                single
                                                transaction
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Maximum
                                             Balance</label>
                                          <div class="col-sm-12 col-lg-4">
                                             <input type="text" class="form-control" placeholder="30000000.00"
                                                name="AddFundsMaximumBalance" value="{{ $addFundsMaximumBalance }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-6">
                                             <p class="m-0 pt-2">
                                                Enter the maximum balance that a client can add in
                                                credit
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Require
                                             Active
                                             Order</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" class="custom-control-input"
                                                   id="AddFundsRequireOrderHidden" name="AddFundsRequireOrder" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AddFundsRequireOrder" name="AddFundsRequireOrder" value="on"
                                                   {{ $addFundsRequireOrder == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AddFundsRequireOrder">
                                                   Require an active order before allowing Add
                                                   Funds
                                                   use
                                                   (used
                                                   to protect against fraud, means an admin must
                                                   have
                                                   manually
                                                   reviewed the client & approved an order before
                                                   allowing
                                                   credit to be added)
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Automatic
                                             Credit
                                             Use</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" class="custom-control-input"
                                                   id="NoAutoApplyCreditHidden" name="NoAutoApplyCredit" value="on">
                                                <input type="checkbox" class="custom-control-input" id="NoAutoApplyCredit"
                                                   name="NoAutoApplyCredit" value="off"
                                                   {{ $noAutoApplyCredit == 'off' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="NoAutoApplyCredit">
                                                   Tick to automatically apply available credit
                                                   from a
                                                   users
                                                   credit balance to recurring invoices upon
                                                   creation
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Credit
                                             On
                                             Downgrade</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" class="custom-control-input"
                                                   id="CreditOnDowngradeHidden" name="CreditOnDowngrade" value="off">
                                                <input type="checkbox" class="custom-control-input" id="CreditOnDowngrade"
                                                   name="CreditOnDowngrade" value="on"
                                                   {{ $creditDowngrade == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="CreditOnDowngrade">
                                                   Tick this box to provide a prorata refund to
                                                   clients
                                                   when
                                                   downgrading for unused time
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Affiliates Tab Content --}}
                                    <div class="tab-pane fade" id="nav-affiliates" role="tabpanel"
                                       aria-labelledby="nav-affiliates-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable/Disable</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="AffiliateEnabled" class="custom-control-input"
                                                   id="AffiliateEnabledHidden" value="off">
                                                <input type="checkbox" name="AffiliateEnabled"
                                                   class="custom-control-input" id="AffiliateEnabled" value="on"
                                                   {{ $affiliateEnabled == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AffiliateEnabled">
                                                   Tick this box to enable the affiliate system
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Affiliate
                                             Earning
                                             Percentage</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" placeholder="0"
                                                name="AffiliateEarningPercent" value="{{ $affiliateEarningPercent }}"
                                                id="AffiliateEarningPercent">
                                          </div>
                                          <div class="col-sm-12 col-lg-7">
                                             <p class="m-0 pt-2">
                                                Enter the percentage of each payment you want
                                                affiliates
                                                to
                                                receive
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Affiliate
                                             Bonus
                                             Deposit</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" placeholder="0.00"
                                                name="AffiliateBonusDeposit" id="AffiliateBonusDeposit"
                                                value="{{ $affiliateBonus }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-7">
                                             <p class="m-0 pt-2">
                                                Enter the amount you want affiliates to receive in
                                                their
                                                account
                                                after signing up
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Affiliate
                                             Payout
                                             Amount</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" placeholder="25.00"
                                                name="AffiliatePayout" id="AffiliatePayout"
                                                value="{{ $affiliatePayout }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-7">
                                             <p class="m-0 pt-2">
                                                Enter the minimum amount affiliates have to reach
                                                before
                                                making
                                                a withdrawal
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Affiliate
                                             Commission
                                             Delay</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" placeholder="0"
                                                name="AffiliatesDelayCommission" id="AffiliatesDelayCommission"
                                                value="{{ $affiliateDelay }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-6">
                                             <p class="m-0 pt-2">
                                                Enter the number of days to delay commission
                                                payments -
                                                then
                                                only pays if account is still active
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Payout
                                             Request
                                             Department</label>
                                          <div class="col-sm-12 col-lg-4">
                                             <select name="AffiliateDepartment" id="payout-request-department"
                                                class="form-control">
                                                @foreach ($affiliatePayoutDepartment as $key => $department)
                                                   <option value="{{ $key }}"
                                                      {{ $activeAffDept == $key ? 'selected' : '' }}>
                                                      {{ $department }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                          <div class="col-sm-12 col-lg-6">
                                             <p class="m-0 pt-2">
                                                Select the support department to use for affiliate
                                                withdrawal
                                                requests
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Affiliate
                                             Links</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="AffiliateLinks" id="AffiliateLinks" cols="30" rows="5"
                                                class="form-control">{{ $affiliateLinks }}</textarea>
                                             <small class="m-0 p-0">
                                                Enter [AffiliateLinkCode] where the affiliate's
                                                customised
                                                link
                                                code should be inserted
                                                Use <( for open brackets and )> for close brackets
                                                   in
                                                   HTML
                                                   or
                                                   else the HTML will be executed on the page
                                             </small>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Security Tab Content --}}
                                    <div class="tab-pane fade" id="nav-security" role="tabpanel"
                                       aria-labelledby="nav-security-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Email
                                             Verification</label>
                                          <div class="col-sm-12 col-lg-5 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input name="EnableEmailVerification" type="hidden"
                                                   class="custom-control-input" id="EnableEmailVerificationHidden"
                                                   value="off">
                                                <input name="EnableEmailVerification" type="checkbox"
                                                   class="custom-control-input" id="EnableEmailVerification" value="on"
                                                   {{ $emailVerification == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EnableEmailVerification">
                                                   Request users to confirm their email address on
                                                   signup
                                                   or
                                                   change of email address
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Captcha
                                             Form
                                             Protection</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaSetting"
                                                   id="alwaysOn" value="always_on"
                                                   {{ $captchaSettings == 'always_on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="alwaysOn">
                                                   Always On (code shown to ensure human
                                                   submission)
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaSetting"
                                                   id="offWhenLogin" value="off_when_logged_in"
                                                   {{ $captchaSettings == 'off_when_logged_in' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="offWhenLogin">
                                                   Off when logged in
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaSetting"
                                                   id="alwaysOff" value="always_off"
                                                   {{ $captchaSettings == 'always_off' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="alwaysOff">
                                                   Always Off
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Captcha
                                             Type</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaType"
                                                   id="DefaultCaptcha" value="default"
                                                   {{ $captchaType == 'default' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="DefaultCaptcha">
                                                   Default (5 Character Verification Code)
                                                </label>
                                                <div>
                                                   <img src="{{ Theme::asset('assets/images/thumbnail/captcha.jpg') }}"
                                                      alt="">
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaType"
                                                   id="reCaptchav2" value="v2"
                                                   {{ $captchaType == 'v2' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="reCaptchav2">
                                                   Google's reCAPTCHA system
                                                </label>
                                                <div>
                                                   <img src="{{ Theme::asset('assets/images/recaptcha.gif') }}"
                                                      width="250">
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaType"
                                                   id="invisibleReCaptcha" value="invisble"
                                                   {{ $captchaType == 'invisble' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="invisibleReCaptcha">
                                                   Invisible reCAPTCHA
                                                </label>
                                                <div>
                                                   <img
                                                      src="{{ Theme::asset('assets/images/recaptcha-invisible.png') }}"
                                                      width="250">
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">

                                          <label class="col-sm-12 col-lg-2 col-form-label">Captcha for
                                             Select
                                             Forms</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="checkoutCompletion" value="false"
                                                   class="custom-control-input" id="checkoutCompletionHidden">
                                                <input type="checkbox" name="checkoutCompletion" value="true"
                                                   class="custom-control-input" id="checkoutCompletion"
                                                   {{ $captchaForms['checkoutCompletion'] === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="checkoutCompletion">Shopping
                                                   Cart Checkout</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="domainChecker" value="false"
                                                   class="custom-control-input" id="domainCheckerHidden">
                                                <input type="checkbox" name="domainChecker" value="true"
                                                   class="custom-control-input" id="domainChecker"
                                                   {{ $captchaForms['domainChecker'] === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="domainChecker">Domain
                                                   Checker</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="registration" value="false"
                                                   class="custom-control-input" id="registrationHidden">
                                                <input type="checkbox" name="registration" value="true"
                                                   class="custom-control-input" id="registration"
                                                   {{ $captchaForms['registration'] === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="registration">Client
                                                   Registration</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="contactUs" value="false"
                                                   class="custom-control-input" id="contactUsHidden">
                                                <input type="checkbox" name="contactUs" value="true"
                                                   class="custom-control-input" id="contactUs"
                                                   {{ $captchaForms['contactUs'] === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="contactUs">Contact
                                                   Form</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="submitTicket" value="false"
                                                   class="custom-control-input" id="submitTicketHidden">
                                                <input type="checkbox" name="submitTicket" value="true"
                                                   class="custom-control-input" id="submitTicket"
                                                   {{ $captchaForms['submitTicket'] === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="submitTicket">Ticket
                                                   Submission</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="login" value="false"
                                                   class="custom-control-input" id="loginHidden">
                                                <input type="checkbox" name="login" value="true"
                                                   class="custom-control-input" id="login"
                                                   {{ $captchaForms['login'] === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="login">Login
                                                   Forms</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">reCAPTCHA
                                             Site
                                             Key</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" name="ReCAPTCHAPublicKey" class="form-control"
                                                placeholder="Site Key" value="{{ $captchaPublicKey }}">
                                             <small class="m-0 p-0">
                                                https://www.google.com/recaptcha/admin
                                             </small>
                                          </div>
                                          <div class="col-sm-12 col-lg-5 pt-2">
                                             <p class="m-0 p-0">
                                                You need to register for reCAPTCHA @
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">reCAPTCHA
                                             Secret
                                             Key</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" name="ReCAPTCHAPrivateKey" class="form-control"
                                                placeholder="Secret Key" value="{{ $captchaPrivateKey }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Auto
                                             Generated
                                             Password
                                             Format</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="AutoGeneratedPasswordFormat" id="AutoGeneratedPasswordFormat1"
                                                   value="default"
                                                   {{ $generatedPasswordFormat == 'default' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AutoGeneratedPasswordFormat1">
                                                   Generate passwords containing a combination of
                                                   letters,
                                                   numbers and special characters (Default)
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="AutoGeneratedPasswordFormat" id="AutoGeneratedPasswordFormat2"
                                                   value="simple"
                                                   {{ $generatedPasswordFormat == 'simple' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AutoGeneratedPasswordFormat2">
                                                   Generate passwords containing a combination of
                                                   letters
                                                   and
                                                   numbers only
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Minimum
                                             User
                                             Password Strength</label>
                                          <div class="col-sm-12 col-lg-2">
                                             <input type="text" name="RequiredPWStrength" class="form-control"
                                                placeholder="1 - 100" value="{{ $passStrength }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-6">
                                             <p class="m-0 pt-2">
                                                Enter a value between 1 and 100, or 0 to disable
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Failed
                                             Admin
                                             Login
                                             Ban
                                             Time</label>
                                          <div class="col-sm-12 col-lg-2">
                                             <input type="text" name="InvalidLoginBanLength" class="form-control"
                                                value="{{ $loginBanLength }}">
                                          </div>
                                          <div class="col-sm-12 col-lg-6">
                                             <p class="m-0 pt-2">
                                                Enter the time to ban an IP in minutes after 3
                                                failed
                                                login
                                                attempts - Enter 0 to Disable
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Whitelisted
                                             IPs</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <select name="WhitelistedIPs[]" id="whitelist-ip" class="form-control"
                                                multiple>
                                                @foreach ($arrayWhitelistIP as $key => $value)
                                                   <option value="{{ $value['ip'] }}">{{ $value['ip'] }} -
                                                      {{ $value['note'] }}</option>
                                                @endforeach
                                             </select>
                                             <div class="d-flex justify-content-start mt-2">
                                                <button type="button" class="btn btn-outline-success btn-sm px-3 mr-2"
                                                   data-toggle="modal" data-target="#whitelistIPModal">Add
                                                   IP</button>
                                                <button type="button"
                                                   onclick="removeIP(' {{ route('admin.pages.setup.generalsettings.general.whitelist.delete') }} ')"
                                                   class="btn btn-outline-danger btn-sm px-3">Remove
                                                   Selected</button>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 py-auto">IP Addresses exempt from being
                                                banned
                                                for
                                                invalid login attempts</p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2">Whitelisted IP Login
                                             Failure
                                             Notices</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="sendFailedLoginWhitelist"
                                                   class="custom-control-input" id="sendFailedLoginWhitelistHidden"
                                                   value="0">
                                                <input type="checkbox" name="sendFailedLoginWhitelist"
                                                   class="custom-control-input" id="sendFailedLoginWhitelist" value="1"
                                                   {{ $sendFailedLoginWhitelist == 1 ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="sendFailedLoginWhitelist">Tick
                                                   to
                                                   send login failure notices for Whitelisted IP
                                                   addresses</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2">Disable Admin Password
                                             Reset</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="DisableAdminPWReset" value="off"
                                                   class="custom-control-input" id="DisableAdminPWResetHidden">
                                                <input type="checkbox" name="DisableAdminPWReset" value="on"
                                                   class="custom-control-input" id="DisableAdminPWReset"
                                                   {{ $disableAdminPWReset == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="DisableAdminPWReset">Tick
                                                   this box to disable the forgotten password
                                                   feature
                                                   on
                                                   the
                                                   admin login page</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2">Allow Client Pay Method
                                             Removal</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="CCAllowCustomerDelete" value="off"
                                                   class="custom-control-input" id="CCAllowCustomerDeleteHidden">
                                                <input type="checkbox" name="CCAllowCustomerDelete" value="on"
                                                   class="custom-control-input" id="CCAllowCustomerDelete"
                                                   {{ $allowClientPayMethodRemoval == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="CCAllowCustomerDelete">Tick
                                                   this box to allow customers to delete the
                                                   payment
                                                   methods
                                                   associated with their account</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2">Disable Session IP
                                             Check</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="DisableSessionIPCheck" value="off"
                                                   class="custom-control-input" id="DisableSessionIPCheckHidden">
                                                <input type="checkbox" name="DisableSessionIPCheck" value="on"
                                                   class="custom-control-input" id="DisableSessionIPCheck"
                                                   {{ $disableSessionIP == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="DisableSessionIPCheck">This
                                                   is
                                                   used to protect against cookie/session hijacking
                                                   but
                                                   can
                                                   cause problems for users with dynamic
                                                   IPs</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Allow
                                             Smarty
                                             PHP
                                             Tags</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <p class="m-0 p-0">Tick to allow use of the Smarty {php}
                                                tag
                                                in
                                                templates. This is considered a security risk.</p>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="AllowSmartyPhpTags"
                                                   id="AllowSmartyPhpTags1" value="1"
                                                   {{ $smartyPHPtags == 1 ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowSmartyPhpTags1">
                                                   Enabled
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="AllowSmartyPhpTags"
                                                   id="AllowSmartyPhpTags2" value="0"
                                                   {{ $smartyPHPtags == 0 ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowSmartyPhpTags2">
                                                   Disabled (Recommended)
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Proxy IP
                                             Header</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" name="proxyHeader" id="proxyHeader"
                                                value="{{ $proxyHeader }}">
                                             <small class="m-0 p-0">
                                                "X_FORWARDED_FOR"; that is the default if no value
                                                is
                                                specified
                                             </small>
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">
                                                Header used by your trusted proxies to relay IP
                                                information.
                                                Most proxies use
                                             </p>
                                          </div>
                                       </div>
                                       {{-- <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Trusted
                                             Proxies</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <select name="/" id="trusted-proxies" class="form-control"
                                                multiple></select>
                                             <div class="d-flex justify-content-start mt-2">
                                                <button class="btn btn-outline-success btn-sm px-3 mr-2">Add
                                                   IP</button>
                                                <button class="btn btn-outline-danger btn-sm px-3">Remove
                                                   Selected</button>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             IP addresses of trusted proxies that forward traffic to
                                             CBMS.
                                             Only
                                             add addresses that directly proxy requests!
                                          </div>
                                       </div> --}}
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">API IP
                                             Access
                                             Restriction</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <select name="APIAllowedIPs[]" id="allowed-ip-api" class="form-control"
                                                multiple>
                                                @foreach ($arrayDataAPIallowedIP as $key => $value)
                                                   <option value="{{ $value['ip'] }}">{{ $value['ip'] }} -
                                                      {{ $value['note'] }}</option>
                                                @endforeach
                                             </select>
                                             <div class="d-flex justify-content-start mt-2">
                                                <button type="button" class="btn btn-outline-success btn-sm px-3 mr-2"
                                                   data-toggle="modal" data-target="#ApiAllowedIPs">Add
                                                   IP</button>
                                                <button type="button"
                                                   onclick="removeAllowedIP('{{ route('admin.pages.setup.generalsettings.general.APIAllowedIPs.delete') }}')"
                                                   class="btn btn-outline-danger btn-sm px-3">Remove
                                                   Selected</button>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 p-0">
                                                IP Addresses allowed to connect to the CBMS API
                                             </p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Log API
                                             Authentication</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="LogAPIAuthentication" value="off"
                                                   class="custom-control-input" id="LogAPIAuthenticationHidden">
                                                <input type="checkbox" name="LogAPIAuthentication" value="on"
                                                   class="custom-control-input" id="LogAPIAuthentication"
                                                   {{ $apiLogAuthentication == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="LogAPIAuthentication">Tick
                                                   to
                                                   record successful API authentications in Admin
                                                   Log</label>
                                             </div>
                                          </div>
                                       </div>
                                       {{-- <div class=" form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">CSRF
                                             Tokens:
                                             General</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <p class="m-0 p-0">
                                                Tick to enable general use of CSRF tokens for all
                                                public
                                                and
                                                clientarea forms (Highly Recommended)
                                             </p>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="csrfGeneral"
                                                   id="csrfGeneral1" value="option1" checked>
                                                <label class="custom-control-label" for="csrfGeneral1">
                                                   Enabled (Default)
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="csrfGeneral"
                                                   id="csrfGeneral2" value="option2">
                                                <label class="custom-control-label" for="csrfGeneral2">
                                                   Disabled
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">CSRF
                                             Tokens:
                                             Domain
                                             Checker</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <p class="m-0 p-0">
                                                Tick to enable use of CSRF tokens for the Domain
                                                Checker
                                                form
                                             </p>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="csrfDomainChecker"
                                                   id="csrfDomainChecker1" value="option1">
                                                <label class="custom-control-label" for="csrfDomainChecker1">
                                                   Enabled
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="csrfDomainChecker"
                                                   id="csrfDomainChecker2" value="option2" checked>
                                                <label class="custom-control-label" for="csrfDomainChecker2">
                                                   Disabled (Default)
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Allow
                                             AutoAuth</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <p class="m-0 p-0">
                                                Tick to enable use of CSRF tokens for the Domain
                                                Checker
                                                form
                                             </p>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="AllowAutoAuth"
                                                   id="AllowAutoAuth1" value="1"
                                                   {{ $allowAutoAuth == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowAutoAuth1">
                                                   Enabled
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="AllowAutoAuth"
                                                   id="AllowAutoAuth2" value="0"
                                                   {{ $allowAutoAuth == '0' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowAutoAuth2">
                                                   Disabled (Default)
                                                </label>
                                             </div>
                                          </div>
                                       </div> --}}
                                    </div>
                                    {{-- Social Tab Content --}}
                                    <div class="tab-pane fade" id="nav-social" role="tabpanel"
                                       aria-labelledby="nav-social-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Twitter
                                             Username</label>
                                          <div class="col-sm-12 col-lg-5 d-flex">
                                             <input type="text" class="form-control" name="TwitterUsername"
                                                value="{{ $twitterUsername }}" />
                                          </div>
                                          <div class="col-sm-12 col-lg-5">
                                             <p class="m-0 pt-2">Enter your Twitter Username here to
                                                Enable Integration</p>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Announcements
                                             Tweet</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="AnnouncementsTweet" value="off"
                                                   class="custom-control-input" id="AnnouncementsTweetHidden">
                                                <input type="checkbox" name="AnnouncementsTweet" value="on"
                                                   class="custom-control-input" id="AnnouncementsTweet"
                                                   {{ $tweetAnnounce == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AnnouncementsTweet">Enable
                                                   Tweet Button on Announcements</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Facebook
                                             Recommend</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="AnnouncementsFBRecommend" value="off"
                                                   class="custom-control-input" id="AnnouncementsFBRecommendHidden">
                                                <input type="checkbox" name="AnnouncementsFBRecommend" value="on"
                                                   class="custom-control-input" id="AnnouncementsFBRecommend"
                                                   {{ $fbRecommend == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AnnouncementsFBRecommend">Enable
                                                   Facebook Recommend/Send on Announcements</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Facebook
                                             Comments</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="AnnouncementsFBComments" value="off"
                                                   class="custom-control-input" id="AnnouncementsFBCommentsHidden">
                                                <input type="checkbox" name="AnnouncementsFBComments" value="on"
                                                   class="custom-control-input" id="AnnouncementsFBComments"
                                                   {{ $fbComment == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AnnouncementsFBComments">Enable
                                                   Facebook Comments on Announcements</label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Other Tab Content --}}
                                    <div class="tab-pane fade" id="nav-other" role="tabpanel"
                                       aria-labelledby="nav-other-tab">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Marketing
                                             Emails</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="EmailMarketingRequireOptIn" value="off"
                                                   class="custom-control-input" id="EmailMarketingRequireOptInHidden">
                                                <input type="checkbox" name="EmailMarketingRequireOptIn" value="on"
                                                   class="custom-control-input" id="EmailMarketingRequireOptIn"
                                                   {{ $emailMarketing == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EmailMarketingRequireOptIn">Tick
                                                   to enable marketing email opt-in/opt-out functionality</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Require
                                             User
                                             Opt-In</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="AllowClientsEmailOptOut" id="AllowClientsEmailOptOut1" value="on"
                                                   {{ $requireUserOpt == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowClientsEmailOptOut1">
                                                   Enabled - Require users to opt-in to marketing
                                                   emails
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="AllowClientsEmailOptOut" id="AllowClientsEmailOptOut2" value="off"
                                                   {{ $requireUserOpt == 'off' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowClientsEmailOptOut2">
                                                   Disabled - Default to opt-in and allow users to
                                                   uncheck
                                                   the
                                                   box to opt-out
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Marketing
                                             Email
                                             Opt-In Messaging</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="EmailMarketingOptInMessage" id="EmailMarketingOptInMessage"
                                                cols="30" rows="5"
                                                class="form-control">{{ $emailMarketingMessage }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Admin
                                             Client
                                             Display Format</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="ClientDisplayFormat" id="ClientDisplayFormat1" value="1"
                                                   {{ $clientDisplayFormat == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ClientDisplayFormat1">
                                                   Show first name/last name only
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="ClientDisplayFormat" id="ClientDisplayFormat2" value="2"
                                                   {{ $clientDisplayFormat == '2' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ClientDisplayFormat2">
                                                   Show company name if set, otherwise first
                                                   name/last
                                                   name
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="ClientDisplayFormat" id="ClientDisplayFormat3" value="3"
                                                   {{ $clientDisplayFormat == '3' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ClientDisplayFormat3">
                                                   Show full name & company if set
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Default
                                             to
                                             Client Area</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="DefaultToClientArea" value="off"
                                                   class="custom-control-input" id="DefaultToClientAreaHidden">
                                                <input type="checkbox" name="DefaultToClientArea" value="on"
                                                   class="custom-control-input" id="DefaultToClientArea"
                                                   {{ $defaultToClientArea == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="DefaultToClientArea">Tick
                                                   this
                                                   box to skip the homepage and forward users
                                                   directly
                                                   to
                                                   the
                                                   client area/login form upon first visiting
                                                   CBMS</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Allow
                                             Client
                                             Registration</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="AllowClientRegister" value="off"
                                                   class="custom-control-input" id="AllowClientRegisterHidden">
                                                <input type="checkbox" name="AllowClientRegister" value="on"
                                                   class="custom-control-input" id="AllowClientRegister"
                                                   {{ $allowClientRegister == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowClientRegister">Tick
                                                   this box to allow registration without ordering
                                                   any
                                                   products/services</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Client
                                             Email
                                             Preferences</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="DisableClientEmailPreferences" value="off"
                                                   class="custom-control-input" id="DisableClientEmailPreferencesHidden">
                                                <input type="checkbox" name="DisableClientEmailPreferences" value="on"
                                                   class="custom-control-input" id="DisableClientEmailPreferences"
                                                   {{ $emailClientPreferences == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="DisableClientEmailPreferences">Tick
                                                   this box to allow clients to customise the email
                                                   notification types they receive</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Optional
                                             Client
                                             Profile Fields</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="row">
                                                <div class="col-lg-12">
                                                   <p class="m-0 pt-2">
                                                      Tick any of the fields below to make them
                                                      optional
                                                      at
                                                      signup time:
                                                   </p>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="optFirstName" value="false" type="hidden"
                                                         class="custom-control-input" id="optFirstNameHidden">
                                                      <input name="optFirstName" value="true" type="checkbox"
                                                         class="custom-control-input" id="optFirstName"
                                                         {{ $optionProfileFields['optFirstName'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="optFirstName">First
                                                         Name</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="optLastName" value="false" type="hidden"
                                                         class="custom-control-input" id="optLastNameHidden">
                                                      <input name="optLastName" value="true" type="checkbox"
                                                         class="custom-control-input" id="optLastName"
                                                         {{ $optionProfileFields['optLastName'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="optLastName">Last
                                                         Name</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="optAddress1" value="false" type="hidden"
                                                         class="custom-control-input" id="optAddress1Hidden">
                                                      <input name="optAddress1" value="true" type="checkbox"
                                                         class="custom-control-input" id="optAddress1"
                                                         {{ $optionProfileFields['optAddress1'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="optAddress1">Address
                                                         1</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="optCity" value="false" type="hidden"
                                                         class="custom-control-input" id="optCityHidden">
                                                      <input name="optCity" value="true" type="checkbox"
                                                         class="custom-control-input" id="optCity"
                                                         {{ $optionProfileFields['optCity'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="optCity">City</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="optStateRegion" value="false" type="hidden"
                                                         class="custom-control-input" id="optStateRegionHidden">
                                                      <input name="optStateRegion" value="true" type="checkbox"
                                                         class="custom-control-input" id="optStateRegion"
                                                         {{ $optionProfileFields['optStateRegion'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label"
                                                         for="optStateRegion">State/Region</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="optPostcode" value="false" type="hidden"
                                                         class="custom-control-input" id="optPostcodeHidden">
                                                      <input name="optPostcode" value="true" type="checkbox"
                                                         class="custom-control-input" id="optPostcode"
                                                         {{ $optionProfileFields['optPostcode'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label"
                                                         for="optPostcode">Postcode</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="optPhoneNumber" value="false" type="hidden"
                                                         class="custom-control-input" id="optPhoneNumberHidden">
                                                      <input name="optPhoneNumber" value="true" type="checkbox"
                                                         class="custom-control-input" id="optPhoneNumber"
                                                         {{ $optionProfileFields['optPhoneNumber'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="optPhoneNumber">Phone
                                                         Number</label>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Locked
                                             Client
                                             Profile Fields</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="row">
                                                <div class="col-lg-12">
                                                   <p class="m-0 pt-2">
                                                      Select any fields below that you want to
                                                      prevent
                                                      clients
                                                      being able to edit from the client area:
                                                   </p>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockFirstName" value="false"
                                                         class="custom-control-input" id="lockFirstNameHidden">
                                                      <input type="checkbox" name="lockFirstName" value="true"
                                                         class="custom-control-input" id="lockFirstName"
                                                         {{ $lockedProfileFields['lockFirstName'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockFirstName">First
                                                         Name</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockLastName" value="false"
                                                         class="custom-control-input" id="lockLastNameHidden">
                                                      <input type="checkbox" name="lockLastName" value="true"
                                                         class="custom-control-input" id="lockLastName"
                                                         {{ $lockedProfileFields['lockLastName'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockLastName">Last
                                                         Name</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockCompanyName" value="false"
                                                         class="custom-control-input" id="lockCompanyNameHidden">
                                                      <input type="checkbox" name="lockCompanyName" value="true"
                                                         class="custom-control-input" id="lockCompanyName"
                                                         {{ $lockedProfileFields['lockCompanyName'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockCompanyName">Company
                                                         Name</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockEmailAddress" value="false"
                                                         class="custom-control-input" id="lockEmailAddressHidden">
                                                      <input type="checkbox" name="lockEmailAddress" value="true"
                                                         class="custom-control-input" id="lockEmailAddress"
                                                         {{ $lockedProfileFields['lockEmailAddress'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockEmailAddress">Email
                                                         Address</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockAddress1" value="false"
                                                         class="custom-control-input" id="lockAddress1Hidden">
                                                      <input type="checkbox" name="lockAddress1" value="true"
                                                         class="custom-control-input" id="lockAddress1"
                                                         {{ $lockedProfileFields['lockAddress1'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockAddress1">Address
                                                         1</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockAddress2" value="false"
                                                         class="custom-control-input" id="lockAddress2Hidden">
                                                      <input type="checkbox" name="lockAddress2" value="true"
                                                         class="custom-control-input" id="lockAddress2"
                                                         {{ $lockedProfileFields['lockAddress2'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockAddress2">Address
                                                         2</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockCity" value="false"
                                                         class="custom-control-input" id="lockCityHidden">
                                                      <input type="checkbox" name="lockCity" value="true"
                                                         class="custom-control-input" id="lockCity"
                                                         {{ $lockedProfileFields['lockCity'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockCity">City</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockStateRegion" value="false"
                                                         class="custom-control-input" id="lockStateRegionHidden">
                                                      <input type="checkbox" name="lockStateRegion" value="true"
                                                         class="custom-control-input" id="lockStateRegion"
                                                         {{ $lockedProfileFields['lockStateRegion'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label"
                                                         for="lockStateRegion">State/Region</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockPostcode" value="false"
                                                         class="custom-control-input" id="lockPostcodeHidden">
                                                      <input type="checkbox" name="lockPostcode" value="true"
                                                         class="custom-control-input" id="lockPostcode"
                                                         {{ $lockedProfileFields['lockPostcode'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label"
                                                         for="lockPostcode">Postcode</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockCountry" value="false"
                                                         class="custom-control-input" id="lockCountryHidden">
                                                      <input type="checkbox" name="lockCountry" value="true"
                                                         class="custom-control-input" id="lockCountry"
                                                         {{ $lockedProfileFields['lockCountry'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label"
                                                         for="lockCountry">Country</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockPhoneNumber" value="false"
                                                         class="custom-control-input" id="lockPhoneNumberHidden">
                                                      <input type="checkbox" name="lockPhoneNumber" value="true"
                                                         class="custom-control-input" id="lockPhoneNumber"
                                                         {{ $lockedProfileFields['lockPhoneNumber'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockPhoneNumber">Phone
                                                         Number</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockTaxID" value="false"
                                                         class="custom-control-input" id="lockTaxIDHidden">
                                                      <input type="checkbox" name="lockTaxID" value="true"
                                                         class="custom-control-input" id="lockTaxID"
                                                         {{ $lockedProfileFields['lockTaxID'] == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockTaxID">Tax
                                                         ID</label>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Client
                                             Details
                                             Change Notify</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="SendEmailNotificationonUserDetailsChange"
                                                   value="off" class="custom-control-input"
                                                   id="SendEmailNotificationonUserDetailsChangeHidden">
                                                <input type="checkbox" name="SendEmailNotificationonUserDetailsChange"
                                                   value="on" class="custom-control-input"
                                                   id="SendEmailNotificationonUserDetailsChange"
                                                   {{ $clientDetailsNotify == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="SendEmailNotificationonUserDetailsChange">Tick
                                                   this box to send an email notification to admins
                                                   on
                                                   user
                                                   details change</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Show
                                             Cancellation Link</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="ShowCancellationButton" value="off"
                                                   class="custom-control-input" id="ShowCancellationButtonHidden">
                                                <input type="checkbox" name="ShowCancellationButton" value="on"
                                                   class="custom-control-input" id="ShowCancellationButton"
                                                   {{ $showCancellation == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ShowCancellationButton">Tick
                                                   this box to show the cancellation request option
                                                   in
                                                   the
                                                   client area for products</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Monthly
                                             Affiliate Reports</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="SendAffiliateReportMonthly" value="off"
                                                   class="custom-control-input" id="SendAffiliateReportMonthlyHidden">
                                                <input type="checkbox" name="SendAffiliateReportMonthly" value="on"
                                                   class="custom-control-input" id="SendAffiliateReportMonthly"
                                                   {{ $sendAffiliateReport == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="SendAffiliateReportMonthly">Tick
                                                   this box to send Monthly Referrals Reports to
                                                   Affiliates
                                                   on
                                                   the 1st of each month</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Banned
                                             Subdomain
                                             Prefixes</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="BannedSubdomainPrefixes" id="BannedSubdomainPrefixes"
                                                cols="30" rows="2"
                                                class="form-control">{{ $bannedSubdomain }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Encoded
                                             File
                                             Loading</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="EnableSafeInclude"
                                                   id="EnableSafeInclude1" value="0"
                                                   {{ $enableSafeInclude == '0' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EnableSafeInclude1">
                                                   Do not load files encoded with ionCube for
                                                   unknown
                                                   PHP
                                                   targets
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="EnableSafeInclude"
                                                   id="EnableSafeInclude2" value="1"
                                                   {{ $enableSafeInclude == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EnableSafeInclude2">
                                                   Attempt to load all files
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Display
                                             Errors
                                          </label>
                                          <div class="col-sm-12 col-lg-10 mt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="DisplayErrors" value="off"
                                                   class="custom-control-input" id="DisplayErrorsHidden">
                                                <input type="checkbox" name="DisplayErrors" value="on"
                                                   class="custom-control-input" id="DisplayErrors"
                                                   {{ $displayErrors == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="DisplayErrors">Tick
                                                   to
                                                   enable Displaying PHP Errors (Not recommended
                                                   for
                                                   production
                                                   use)</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Log
                                             Errors</label>
                                          <div class="col-sm-12 col-lg-10 mt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="LogErrors" value="off"
                                                   class="custom-control-input" id="LogErrorsHidden">
                                                <input type="checkbox" name="LogErrors" value="on"
                                                   class="custom-control-input" id="LogErrors"
                                                   {{ $logErrors == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="LogErrors">Tick
                                                   to
                                                   enable logging of PHP Errors when possible (Not
                                                   recommended
                                                   for daily production use)</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">SQL
                                             Debug
                                             Mode</label>
                                          <div class="col-sm-12 col-lg-10 mt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="SQLErrorReporting" value="off"
                                                   class="custom-control-input" id="SQLErrorReportingHidden">
                                                <input type="checkbox" name="SQLErrorReporting" value="on"
                                                   class="custom-control-input" id="SQLErrorReporting"
                                                   {{ $sqlReporting == 'on' ? 'checked' : 'on' }}>
                                                <label class="custom-control-label" for="SQLErrorReporting">Tick
                                                   to
                                                   enable logging of SQL Errors (Use only for
                                                   testing
                                                   purposes)</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Hooks
                                             Debug
                                             Mode</label>
                                          <div class="col-sm-12 col-lg-10 mt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="HooksDebugMode" value="off"
                                                   class="custom-control-input" id="HooksDebugModeHidden">
                                                <input type="checkbox" name="HooksDebugMode" value="on"
                                                   class="custom-control-input" id="HooksDebugMode"
                                                   {{ $hooksDebugMode == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="HooksDebugMode">Tick
                                                   to
                                                   enable logging of Hook Calls (Use only for
                                                   testing
                                                   purposes)</label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- Inst Invoice Content --}}
                                    <div class="tab-pane fade" id="nav-inst-invoice" role="tabpanel"
                                       aria-labelledby="nav-inst-invoice-tab">
                                       <div class="row">
                                          <div class="col-lg-4">
                                             <div class="accordion" id="accordionSettings">

                                                <div class="card">
                                                   <div class="card-header" id="headingOne">
                                                      <h2 class="mb-0">
                                                         <button class="btn btn-block text-left" type="button"
                                                            data-toggle="collapse" data-target="#invoiceSettings"
                                                            aria-expanded="true" aria-controls="invoiceSettings">
                                                            Invoice Settings
                                                         </button>
                                                      </h2>
                                                   </div>
                                                   <div id="invoiceSettings" class="collapse show"
                                                      aria-labelledby="headingOne" data-parent="#accordionSettings">
                                                      <div class="card-body">
                                                         <h5 class="font-weight-bolder">Customer Settings</h5>
                                                         <hr>
                                                         <div class="form-group">
                                                            <label for="lang">Default Language</label>
                                                            <select name="lang" id="lang" class="form-control">
                                                               <option value="ID">Indonesia</option>
                                                               <option value="EN">English</option>
                                                            </select>
                                                         </div>
                                                         <div class="form-group">
                                                            <label for="currency">Display Currency</label>
                                                            <select name="currency" id="currency" class="form-control">
                                                               <option value="IDR">IDR</option>
                                                            </select>
                                                         </div>
                                                         <div class="form-group">
                                                            <label for="inv-duration">Default Invoice Duration</label>
                                                            <div class="row">
                                                               <div class="col-lg-6">
                                                                  <input type="number" class="form-control"
                                                                     id="inv-duration" value="1">
                                                               </div>
                                                               <div class="col-lg-6">
                                                                  <select name="duration" id="duration"
                                                                     class="form-control">
                                                                     <option value="hours">Hours</option>
                                                                     <option value="days">Days</option>
                                                                     <option value="week">Weeks</option>
                                                                     <option value="month">Month</option>
                                                                     <option value="years">Years</option>
                                                                  </select>
                                                               </div>
                                                            </div>
                                                         </div>
                                                         <div class="form-group">
                                                            <label for="due-duration">Send Due Date Reminder</label>
                                                            <div class="row">
                                                               <div class="col-lg-6">
                                                                  <input type="number" class="form-control"
                                                                     id="due-duration" value="1">
                                                               </div>
                                                               <div class="col-lg-6">
                                                                  <select name="due-period" id="due-period"
                                                                     class="form-control">
                                                                     <option value="hours">Hours</option>
                                                                     <option value="days">Days</option>
                                                                     <option value="week">Weeks</option>
                                                                     <option value="month">Month</option>
                                                                     <option value="years">Years</option>
                                                                  </select>
                                                               </div>
                                                            </div>
                                                         </div>
                                                         <hr>
                                                         <h5 class="font-weight-bolder mt-2">Customer Notification</h5>
                                                         <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                                                            Velit, animi.</p>
                                                         <hr>
                                                         <div class="form-group">
                                                            <div for="send-invoice"><span class="font-weight-bolder">Send
                                                                  invoice via</span> select at least 1 channel to notify
                                                               customer</div>
                                                            <div class="custom-control custom-checkbox">
                                                               <input type="checkbox" class="custom-control-input"
                                                                  id="inv-email">
                                                               <label class="custom-control-label"
                                                                  for="inv-email">Email</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                               <input type="checkbox" class="custom-control-input"
                                                                  id="inv-wa">
                                                               <label class="custom-control-label"
                                                                  for="inv-wa">WhatsApp</label>
                                                            </div>
                                                         </div>
                                                         <div class="form-group">
                                                            <div for="send-invoice"><span class="font-weight-bolder">Send
                                                                  reminder via</span> select at least 1 channel to notify
                                                               customer</div>
                                                            <div class="custom-control custom-checkbox">
                                                               <input type="checkbox" class="custom-control-input"
                                                                  id="remind-email">
                                                               <label class="custom-control-label"
                                                                  for="remind-email">Email</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                               <input type="checkbox" class="custom-control-input"
                                                                  id="remind-wa">
                                                               <label class="custom-control-label"
                                                                  for="remind-wa">WhatsApp</label>
                                                            </div>
                                                         </div>
                                                         <div class="form-group">
                                                            <div for="send-invoice"><span class="font-weight-bolder">Send
                                                                  paid invoice notification via</span> select at least 1
                                                               channel to notify customer</div>
                                                            <div class="custom-control custom-checkbox">
                                                               <input type="checkbox" class="custom-control-input"
                                                                  id="paid-email">
                                                               <label class="custom-control-label"
                                                                  for="paid-email">Email</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                               <input type="checkbox" class="custom-control-input"
                                                                  id="paid-wa">
                                                               <label class="custom-control-label"
                                                                  for="paid-wa">WhatsApp</label>
                                                            </div>
                                                         </div>
                                                         <hr>
                                                         <h5 class="font-weight-bolder mt-2">Merchant Notification</h5>
                                                         <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                                                            Velit, animi.</p>
                                                         <hr>
                                                         <div class="form-group">
                                                            <label>Notify me if invoice status changed</label>
                                                            <div class="custom-control custom-checkbox">
                                                               <input type="checkbox" class="custom-control-input"
                                                                  id="inv-paid">
                                                               <label class="custom-control-label" for="inv-paid">Send
                                                                  email when invoice is paid</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                               <input type="checkbox" class="custom-control-input"
                                                                  id="inv-expired">
                                                               <label class="custom-control-label" for="inv-expired">Send
                                                                  email when invoice is expired</label>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>

                                                </div>
                                             </div>
                                             <div class="accordion" id="paymentSettings">
                                                <div class="card">
                                                   <div class="card-header" id="headingTwo">
                                                      <h2 class="mb-0">
                                                         <button class="btn btn-block text-left collapsed" type="button"
                                                            data-toggle="collapse" data-target="#paymentGateways"
                                                            aria-expanded="false" aria-controls="paymentGateways">
                                                            Payment Gateways
                                                         </button>
                                                      </h2>
                                                   </div>
                                                   <div id="paymentGateways" class="collapse"
                                                      aria-labelledby="headingTwo" data-parent="#paymentSettings">
                                                      <div class="card-body">
                                                         <h5>Available Payment Method</h5>
                                                         <hr>
                                                         <form action="{{ route('admin.checkPayment') }}" method="POST"
                                                            id="form-check-pg">
                                                            @csrf
                                                            <div class="row">
                                                               @foreach ($gateways as $key => $name)
                                                                  <div class="col-lg-6">
                                                                     <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="methods[]"
                                                                           class="custom-control-input"
                                                                           id="{{ $key }}"
                                                                           value="{{ $key }}">
                                                                        <label class="custom-control-label"
                                                                           for="{{ $key }}">{{ $name }}</label>
                                                                     </div>
                                                                  </div>
                                                               @endforeach
                                                            </div>
                                                            {{-- <div class="col-lg-12 mt-3">
                                                               <button type="sumbit"
                                                                  class="btn btn-sm btn-block btn-success">Apply</button>
                                                            </div> --}}
                                                         </form>
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>

                                          <div class="col-lg-8">
                                             <div class="card ">
                                                <div class="card-body">
                                                   <h3>Invoice Preview</h3>
                                                   <div class="invoice-container mt-3">
                                                      {{-- Header Invoice --}}
                                                      <div class="d-flex align-items-center admin-main-color p-3">
                                                         <div class="logo-inv-custom text-light">
                                                            <h1 class="text-white m-0">Logo</h1>
                                                         </div>
                                                         <div class="ml-auto">
                                                            <select class="form-control">
                                                               <option value="ID">Bahasa Indonesia</option>
                                                            </select>
                                                         </div>
                                                      </div>
                                                      {{-- End Of Header --}}
                                                      {{-- Child Header --}}
                                                      <div
                                                         class="d-flex align-items-center bg-light p-2 secondary-inv-header">
                                                         <a href=""><i class="fas fa-file-invoice-dollar"></i> Invoice #:
                                                            INV-20210202102002389</a>
                                                      </div>
                                                      {{-- End of Child Header --}}
                                                      {{-- Invoice Body --}}
                                                      <div class="invoice-body">
                                                         {{-- Total Pay --}}
                                                         <div class="total-pay-desc">
                                                            <h5>Jumlah yang harus di bayar</h5>
                                                            <div class="price-pay">{{ $totalPay }}</div>
                                                            <h6 class="text-success" id="duedate">Bayar sebelum 29
                                                               Oktober 2021
                                                               09:35AM</h6>
                                                         </div>
                                                         {{-- End Total Pay --}}
                                                         <div class="payment-gate-logo">
                                                            <div class="accordion" id="accordionExample1">
                                                               <div class="card">
                                                                  <div class="card-header" id="headingOne">
                                                                     <h2 class="mb-0">
                                                                        <button
                                                                           class="btn btn-accord btn-block text-left"
                                                                           type="button" data-toggle="collapse"
                                                                           data-target="#collapseOne" aria-expanded="false"
                                                                           aria-controls="collapseOne" id="trfBank">
                                                                           Transfer Bank
                                                                        </button>
                                                                     </h2>
                                                                  </div>

                                                                  <div id="collapseOne" class="collapse multi-collapse"
                                                                     aria-labelledby="headingOne"
                                                                     data-parent="#accordionExample1">
                                                                     <div class="card-body" id="trfbankContent">
                                                                        Not available yet
                                                                     </div>
                                                                  </div>
                                                               </div>
                                                            </div>
                                                            <div class="accordion" id="accordionExample2">
                                                               <div class="card">
                                                                  <div class="card-header" id="headingTwo">
                                                                     <h2 class="mb-0">
                                                                        <button
                                                                           class="btn btn-accord btn-block text-left collapsed"
                                                                           type="button" data-toggle="collapse"
                                                                           data-target="#collapseTwo" aria-expanded="false"
                                                                           aria-controls="collapseTwo"
                                                                           id="creditAccordBtn">
                                                                           Kartu Credit / Debit
                                                                        </button>
                                                                     </h2>
                                                                  </div>
                                                                  <div id="collapseTwo" class="collapse multi-collapse"
                                                                     aria-labelledby="headingTwo"
                                                                     data-parent="#accordionExample2">
                                                                     <div class="card-body" id="creditCardContent">
                                                                        Coming soon.
                                                                     </div>
                                                                  </div>
                                                               </div>
                                                            </div>
                                                            <div class="accordion" id="accordionExample3">
                                                               <div class="card">
                                                                  <div class="card-header" id="headingThree">
                                                                     <h2 class="mb-0">
                                                                        <button
                                                                           class="btn btn-accord btn-block text-left collapsed"
                                                                           type="button" data-toggle="collapse"
                                                                           data-target="#collapseThree"
                                                                           aria-expanded="false"
                                                                           aria-controls="collapseThree">
                                                                           E-Wallet / QRIS
                                                                        </button>
                                                                     </h2>
                                                                  </div>
                                                                  <div id="collapseThree" class="collapse multi-collapse"
                                                                     aria-labelledby="headingThree"
                                                                     data-parent="#accordionExample3">
                                                                     <div class="card-body">
                                                                        <div class="row align-items-center"
                                                                           id="logo-wallet-placeholder">
                                                                           @foreach ($gateways as $key => $name)
                                                                              {{-- <button type="button" class="btn btn-info" >Bayar Sekarang</button> --}}
                                                                              <div data-toggle="modal"
                                                                                 data-target="#guidePay"
                                                                                 data-payname="{{ $name }}"
                                                                                 data-paygateway="{{ $key }}"
                                                                                 id="{{ $key }}-modals"
                                                                                 class="{{ $key }} col-lg-2 disp-toggle">
                                                                                 <div class="logo-wallet-container"
                                                                                    id="{{ $key }}-thumb">
                                                                                    <img
                                                                                       src="{{ Theme::asset("assets/images/wallet-logo/$key.png") }}"
                                                                                       alt="{{ $key }}.png">
                                                                                 </div>
                                                                              </div>
                                                                           @endforeach
                                                                        </div>
                                                                     </div>
                                                                  </div>
                                                               </div>
                                                            </div>
                                                         </div>
                                                         {{-- <div class="text-center my-3" id="invoicePaymentButton">
                                                               {!! $paymentbutton !!}
                                                            </div> --}}
                                                      </div>
                                                      {{-- Invoice Footer --}}
                                                      <div class="text-center">
                                                         Powered by CBMS Auto
                                                      </div>
                                                      {{-- Modal How to Pay --}}
                                                      <!-- Modal -->
                                                      <div class="modal fade" id="guidePay" tabindex="-1"
                                                         aria-labelledby="exampleModalLabel" aria-hidden="true"
                                                         data-backdrop="static" data-keyboard="false">
                                                         <div class="modal-dialog">
                                                            <div class="modal-content">
                                                               <div class="modal-header">
                                                                  <h5 class="modal-title" id="exampleModalLabel">
                                                                     Cara Pembayaran</h5>
                                                                  <button type="button" class="close"
                                                                     data-dismiss="modal" aria-label="Close">
                                                                     <span aria-hidden="true">&times;</span>
                                                                  </button>
                                                               </div>
                                                               <div id="loader-modal-payment" class="text-center p-3">
                                                                  <div class="spinner-border text-primary mb-2"
                                                                     role="status">
                                                                     <span class="sr-only">Loading...</span>
                                                                  </div>
                                                                  <p>Please Wait</p>
                                                               </div>
                                                               <div id="error-handler">

                                                               </div>
                                                               <div class="modal-body" id="ModalGuide">
                                                                  <div>
                                                                     @include('pages.setup.generalsettings.instainvoice.dana')
                                                                     @include('pages.setup.generalsettings.instainvoice.jeniusQR')
                                                                     @include('pages.setup.generalsettings.instainvoice.ovo')
                                                                     @include('pages.setup.generalsettings.instainvoice.paypal')
                                                                  </div>
                                                               </div>
                                                               <div class="modal-footer flex-nowrap"
                                                                  id="pay-button-holder">


                                                               </div>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div id="pay-modal-alt">

                                                      </div>
                                                   </div>
                                                   {{-- End of Invoice Body --}}
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="d-flex justify-content-center mt-5">
                                 <button type="submit" id="btnUpdateSettings"
                                    class="btn btn-success mx-1 waves-effect">Save
                                    Changes</button>
                                 <button type="reset" class="btn btn-light mx-1">Cancel
                                    Changes</button>
                              </div>
                           </form>
                        </div>


                        {{-- Modal For Add Whitelisted IP --}}
                        <div class="modal fade" id="whitelistIPModal" tabindex="-1"
                           aria-labelledby="whitelistIPModalLabel" aria-hidden="true">
                           <div class="modal-dialog">
                              <div class="modal-content">
                                 <div class="modal-header">
                                    <h5 class="modal-title" id="whitelistIPModalLabel">Add IP</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                       <span aria-hidden="true">&times;</span>
                                    </button>
                                 </div>
                                 <div class="modal-body">
                                    <form action="#"
                                       onsubmit="addIP('{{ route('admin.pages.setup.generalsettings.general.whitelist') }}')"
                                       id="formAddIP" method="POST">
                                       @method('POST')
                                       @csrf
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label" for="newIP">IP</label>
                                          <div class="col-lg-10">
                                             <input type="text" id="newIP" class="form-control" name="ip" required>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label" for="reason">Reason</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="text" id="reason" class="form-control" name="note">
                                          </div>
                                       </div>
                                       <div class="modal-footer">
                                          <button type="submit" id="btn-add-whitelist-ip" class="btn btn-success">Add
                                             IP</button>
                                          <button type="button" id="close-modal" class="btn btn-light"
                                             data-dismiss="modal">Cancel</button>
                                       </div>
                                    </form>
                                 </div>
                              </div>
                           </div>
                        </div>

                        {{-- Modal For Add API Allowed IP --}}
                        <div class="modal fade" id="ApiAllowedIPs" tabindex="-1" aria-labelledby="ApiAllowedIPsLabel"
                           aria-hidden="true">
                           <div class="modal-dialog">
                              <div class="modal-content">
                                 <div class="modal-header">
                                    <h5 class="modal-title" id="ApiAllowedIPsLabel">Add IP</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                       <span aria-hidden="true">&times;</span>
                                    </button>
                                 </div>
                                 <div class="modal-body">
                                    <form action="#"
                                       onsubmit="addAllowedAPI('{{ route('admin.pages.setup.generalsettings.general.APIAllowedIPs') }}')"
                                       id="formAllowedAPI" method="POST">
                                       @method('POST')
                                       @csrf
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label" for="AllowedIP">IP</label>
                                          <div class="col-lg-10">
                                             <input type="text" id="AllowedIP" class="form-control" name="ip2"
                                                required>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label"
                                             for="noteAllowedIp">Reason</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="text" id="noteAllowedIp" class="form-control" name="note2">
                                          </div>
                                       </div>
                                       <div class="modal-footer">
                                          <button type="submit" id="btn-add-allowed-ip" class="btn btn-success">Add
                                             IP</button>
                                          <button type="button" id="close-modal-api" class="btn btn-light"
                                             data-dismiss="modal">Cancel</button>
                                       </div>
                                    </form>
                                 </div>
                              </div>
                           </div>
                        </div>

                        {{-- TOAST SUCCESS ADD --}}
                        <div class="position-fixed top-0 right-0 p-3" style="z-index: 5; right: 0; bottom: 0;">
                           <div id="liveToast" class="toast hide" role="alert" aria-live="assertive"
                              aria-atomic="true" data-delay="5000">
                              <div class="toast-header" id="headToast">
                                 <strong class="mr-auto"><i class="fas fa-bell mr-2"></i>Notification</strong>
                              </div>
                              <div class="toast-body">
                                 <div id="toast-success" class="text-success"></div>
                              </div>
                           </div>
                        </div>

                        {{-- TOAST SUCCES DELETED --}}
                        <div class="position-fixed top-0 right-0 p-3" style="z-index: 5; right: 0; bottom: 0;">
                           <div id="liveToastDeleted" class="toast hide" role="alert" aria-live="assertive"
                              aria-atomic="true" data-delay="5000">
                              <div class="toast-header" id="headToastDeleted">
                                 <strong class="mr-auto"><i class="fas fa-bell mr-2"></i>Notification</strong>
                              </div>
                              <div class="toast-body">
                                 <div id="toast-delete" class="text-danger"></div>
                              </div>
                           </div>
                        </div>


                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- End MAIN CARD -->
   </div>
@endsection

@section('scripts')
   <script src="{{ Theme::asset('assets/js/submit-btn.js') }}"></script>
   <script src="{{ Theme::asset('assets/js/active-general-tab.js') }}"></script>
   <script src="{{ Theme::asset('assets/js/add-whitelisted-ip.js') }}"></script>
   <script src="{{ Theme::asset('assets/js/add-allowed-api-ip.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/swiper-slider/swiper-bundle.js') }}"></script>
   <script type="module">
      import Swiper from 'https://unpkg.com/swiper@7/swiper-bundle.esm.browser.min.js'
      const swiper = new Swiper('.swiper', {
         // Optional parameters
         direction: 'horizontal',
         loop: false,
         allowTouchMove: false,
         spaceBetween: 17,


         // If we need pagination
         pagination: {
            el: '.swiper-pagination',
         },

         // Navigation arrows
         navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
         },
      });
   </script>
   <script type="text/javascript">
      $(document).ready(function() {

         var today = new Date();
         var month = ["January", "February", "March", "April", "May", "June", "July", "August", "September",
            "October", "November", "December"
         ];
         var date = +today.getDate() + 1 + ' ' + month[today.getMonth()] + ' ' + today.getFullYear();
         document.getElementById("duedate").innerHTML = `Bayar sebelum ${date}, ${today.getHours()}`;

         $('#RegistrarAdminUseClientDetails').click(function() {
            $('#clientField').toggleClass('show')
         })

         let initVal = $("input[name='TCPDFFont']:checked").val()
         if (initVal != 'custom') {
            $('#TCPDFFontTextInput').collapse('hide')
            $('#TCPDFFontTextField').prop('disabled, true')
            $('#TCPDFFontTextField').val('')
         } else {
            $('#TCPDFFontTextInput').collapse('show')
            $('#TCPDFFontTextField').prop('disabled', false)
         }

         $("input[name='TCPDFFont']").click(function() {
            let checkedValue = $("input[name='TCPDFFont']:checked").val()
            if (checkedValue == 'custom') {
               $('#TCPDFFontTextInput').collapse('show')
               $('#TCPDFFontTextField').prop('disabled', false)
            } else {
               $('#TCPDFFontTextInput').collapse('hide')
               $('#TCPDFFontTextField').prop('disabled', true)
            }
         })
      })
      let resetLoader = () => {
         $('#loader-modal-payment').removeAttr('hidden', true);
         $('.xendit, .alert, table').remove();
         $('.swiper').attr('hidden', true)
      }
   </script>

   <script type="text/javascript">
      $('input[name="methods[]"]').click(function() {
         var inputValue = $(this).attr("value");

         if (inputValue == 'Paypal') {
            const Paypal = document.getElementById("Paypal-modals");
            // console.log(Paypal);
            $("#creditCardContent").html(Paypal);
            $("#creditAccordBtn").click();
         }
         if (inputValue == 'BankTransfer') {
            const bankTrf = document.getElementById("BankTransfer-modals");
            $("#trfbankContent").html(bankTrf);
            // $(bankTrf).appendTo("#trfbankContent");
            $(".BankTransfer").toggle();
            $("#trfBank").click();
         } else {
            $('#collapseThree').addClass('show');
            $("." + inputValue).toggle();
         }
      });

      $('#guidePay').on('show.bs.modal', function(event) {
         $('#ModalGuide').attr('hidden', true);
         $('#pay-button-holder p').remove();
         $('#pay-button-holder button').remove();
         $('#pay-button-holder').append(`
            <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="resetLoader()" id="close-guide-modal">Close</button>
          `);
         $('#pay-modal-alt div').remove();

         var button = $(event.relatedTarget) // Button that triggered the modal
         var paymethodname = button.data('payname') // Extract info from data-* attributes
         var paygateway = button.data('paygateway') // Extract info from data-* attributes
         //  console.log(paygateway);
         var modal = $(this)
         modal.find('.modal-title').text('Payment Guide ' + paymethodname)

         var dataInvoice = {
            id: 806,
            paymentmethod: paygateway
         }
         $(`#swiper-${paygateway}`).removeAttr('hidden')

         $.ajax({
            url: "{!! route('admin.updatePaymentInvoice') !!}",
            headers: {
               'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            type: 'POST',
            data: dataInvoice, // Remember that you need to have your csrf token included
            dataType: 'json',
            success: function(_response) {
               if ($(`#swiper-${_response.gateway}`)) {
                  $(`#swiper-${_response.gateway}`).removeAttr('hidden', true)
               }
               $('#pay-button-holder').prepend(_response.button)
               $('#loader-modal-payment').attr('hidden', true);
               $('#ModalGuide').removeAttr('hidden', true);
               $('.btn-info').on('click', () => {
                  $('#close-guide-modal').click();
                  $('#getpay').appendTo('#pay-modal-alt');
                  $('#paywitOVO').appendTo('#pay-modal-alt');
               })
            },
            error: function(_response) {
               $('#loader-modal-payment').attr('hidden', true);
               $('#error-handler').append(`
                    <div class="alert alert-danger" role="alert">
                        ${_response.status} ${_response.statusText} 
                    </div>
               `)
            }
         })
      })
   </script>
@endsection
