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
                           {!!$infobox!!}
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           @include('includes.tabnavgeneralsettings')
                           <form id="settingsForm"
                              name="configfrm"
                              action="{{ route('admin.pages.setup.generalsettings.general.index', ['action' => 'save']) }}" method="POST">
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
                                                value="{{App\Helpers\Sanitize::makeSafeForOutput(Cfg::get("CompanyName"))}}" name="companyname">
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
                                                value="{{Cfg::get("Email")}}" name="email">
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
                                                placeholder="http://www.yourdomain.com/" value="{{Cfg::get("Domain")}}"
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
                                             <input type="url" class="form-control" value="{{ Cfg::get("LogoURL") }}"
                                                name="logourl" placeholder="http://www.yourdomain.com/">
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
                                             <textarea name="invoicepayto" id="InvoicePayTo" cols="20" rows="5"
                                                class="form-control">{{Cfg::get("InvoicePayTo")}}</textarea>
                                             <small class="m-0 pt-0 text-muted">
                                                This text is displayed on the invoice as the Pay To details
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">CBMS System
                                             URL</label>
                                          <div class="col-sm-12 col-lg-8">
                                             <input type="text" class="form-control" value="{{Cfg::get("SystemURL")}}"
                                                name="systemurl">
                                             <small class="m-0 p-0 text-muted">
                                                The URL to your CBMS installation (SSL Recommended) eg.
                                                https://www.example.com/members/
                                             </small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Template</label>
                                          <div class="col-sm-12 col-lg-2">
                                             <select name="template" id="template" class="form-control">
                                                @foreach ($clientTemplates ?? [] as $key => $theme)
                                                   <option value="{{$theme['name']}}"
                                                      {{ Cfg::get("Template") == $theme['name'] ? 'selected' : '' }}>
                                                      {{ Str::ucfirst($theme['name']) }} ({{$theme['status']}})</option>
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
                                                value="{{Cfg::get("ActivityLimit")}}" name="activitylimit">
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
                                                name="numrecords">
                                                <option value="50" {{ Cfg::get("NumRecordsToDisplay") == 50 ? 'selected' : '' }}>50
                                                </option>
                                                <option value="100" {{ Cfg::get("NumRecordsToDisplay") == 100 ? 'selected' : '' }}>100
                                                </option>
                                                <option value="200" {{ Cfg::get("NumRecordsToDisplay") == 200 ? 'selected' : '' }}>200
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
                                                {{-- <input class="custom-control-input" id="MaintenanceModeHidden"
                                                   type="hidden" value="0" name="MaintenanceMode"> --}}
                                                <input class="custom-control-input" type="checkbox" id="MaintenanceMode"
                                                   value="1" {{ Cfg::get("MaintenanceMode") ? 'checked' : '' }}
                                                   name="maintenancemode">
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
                                                name="maintenancemodemessage">{{ Cfg::get("MaintenanceModeMessage")  }}</textarea>
                                          </div>

                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Maintenance Mode
                                             Redirect URL</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" value="{{ Cfg::get("MaintenanceModeURL") }}"
                                                name="maintenancemodeurl">
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
                                             <input type="text" class="form-control" value="{{ Cfg::get("Charset") }}"
                                                name="charset">
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
                                             <select class="form-control" name="dateformat">
                                                <option value="DD/MM/YYYY"
                                                   {{ Cfg::get("DateFormat") == 'DD/MM/YYYY' ? 'selected' : '' }}>
                                                   DD/MM/YYYY</option>
                                                <option value="DD.MM.YYYY"
                                                   {{ Cfg::get("DateFormat") == 'DD.MM.YYYY' ? 'selected' : '' }}>
                                                   DD.MM.YYYY</option>
                                                <option value="DD-MM-YYYY"
                                                   {{ Cfg::get("DateFormat") == 'DD-MM-YYYY' ? 'selected' : '' }}>
                                                   DD-MM-YYYY</option>
                                                <option value="MM/DD/YYYY"
                                                   {{ Cfg::get("DateFormat") == 'MM/DD/YYYY' ? 'selected' : '' }}>
                                                   MM/DD/YYYY</option>
                                                <option value="YYYY/MM/DD"
                                                   {{ Cfg::get("DateFormat") == 'YYYY/MM/DD' ? 'selected' : '' }}>
                                                   YYYY/MM/DD</option>
                                                <option value="YYYY-MM-DD"
                                                   {{ Cfg::get("DateFormat") == 'YYYY-MM-DD' ? 'selected' : '' }}>
                                                   YYYY-MM-DD</option>
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
                                             <select name="clientdateformat" class="form-control">
                                                <option value="default"
                                                   {{ Cfg::get("ClientDateFormat") == '' ? 'selected' : '' }}>Use Global Date
                                                   Format</option>
                                                <option value="full"
                                                   {{ Cfg::get("ClientDateFormat") == 'full' ? 'selected' : '' }}>
                                                   1st January 2000</option>
                                                <option value="shortmonth"
                                                   {{ Cfg::get("ClientDateFormat") == 'shortmonth' ? 'selected' : '' }}>1st Jan
                                                   2000
                                                </option>
                                                <option value="fullday"
                                                   {{ Cfg::get("ClientDateFormat") == 'fullday' ? 'selected' : '' }}>Monday,
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
                                             {{-- <select class="form-control" name="defaultCountry">
                                                <option value="US" {{ Cfg::get("OrderFormTemplate") == 'US' ? 'selected' : '' }}>
                                                   United
                                                   States</option>
                                                <option value="ID" {{ Cfg::get("OrderFormTemplate") == 'ID' ? 'selected' : '' }}>
                                                   Indonesia</option>
                                             </select> --}}
                                             {!!App\Helpers\ClientHelper::getCountriesDropDown(Cfg::get("DefaultCountry"), "defaultcountry")!!}
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Default Language
                                          </label>
                                          <div class="col-sm-12 col-lg-2">
                                             <select class="form-control" name="language">
                                                <option value="english" {{ Cfg::get("Language") == 'english' ? 'selected' : '' }}>
                                                   English
                                                </option>
                                                <option value="indonesia"
                                                   {{ Cfg::get("Language") == 'indonesia' ? 'selected' : '' }}>
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
                                                {{-- <input class="custom-control-input" id="allowLangChangeHidden"
                                                   type="hidden" value="off" name="AllowLanguageChange"> --}}
                                                <input class="custom-control-input" type="checkbox" id="allowLangChange"
                                                    name="allowuserlanguage"
                                                   {{ Cfg::get("AllowLanguageChange") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden"
                                                   id="dynamicFieldCustomHidden" value="0" name="EnableTranslations"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="dynamicFieldCustom" value="1" name="enable_translations"
                                                   {{ Cfg::get("EnableTranslations") ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" id="CutUtf8Mb4Hidden" type="hidden"
                                                   value="off" name="CutUtf8Mb4"> --}}
                                                <input class="custom-control-input" type="checkbox" id="CutUtf8Mb4"
                                                   name="cututf8mb4"
                                                   {{ Cfg::get("CutUtf8Mb4") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input name="PhoneNumberDropdown" class="custom-control-input"
                                                   type="hidden" id="phoneNumberCustomHidden" value="0"> --}}
                                                <input name="tel-cc-input" class="custom-control-input"
                                                   type="checkbox" id="phoneNumberCustom" value="1"
                                                   {{ Cfg::get("PhoneNumberDropdown") ? 'checked' : '' }}>
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
                                             <input type="number" class="form-control" name="orderdaysgrace"
                                                value="{{ Cfg::get("OrderDaysGrace") }}">
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
                                                @foreach ($orderFormTemplates ?? [] as $key => $orderformTheme)
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
                                                            name="orderformtemplate" id="{{ $orderformTheme['name'] }}"
                                                            value="{{ $orderformTheme['name'] }}"
                                                            {{ Cfg::get("OrderFormTemplate") == $orderformTheme['name'] ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" id="OrderFormSidebarToggleHidden"
                                                   type="hidden" value="off" name="OrderFormSidebarToggle"> --}}
                                                <input class="custom-control-input" type="checkbox" value="1"
                                                   id="OrderFormSidebarToggle" name="orderfrmsidebartoggle"
                                                   {{ Cfg::get("OrderFormSidebarToggle") ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" id="enableTOSAcceptHidden"
                                                   type="hidden" value="off" name="EnableTOSAccept"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="enableTOSAccept" name="enabletos"
                                                   {{ Cfg::get("EnableTOSAccept") == 'on' ? 'checked' : '' }}>
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
                                             <input type="text" class="form-control" name="tos"
                                                value="{{ Cfg::get("TermsOfService") }}">
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
                                                   name="autoredirecttoinvoice" id="autoDirectOnCheckout1"
                                                   value="no_redirect"
                                                   {{ Cfg::get("AutoRedirectoInvoice") == '' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="autoDirectOnCheckout1">
                                                   Just show the order completed page (no payment redirect)
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="autoredirecttoinvoice" id="autoDirectOnCheckout2"
                                                   value="to_invoice"
                                                   {{ Cfg::get("AutoRedirectoInvoice") == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="autoDirectOnCheckout2">
                                                   Automatically take the user to the invoice
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="autoredirecttoinvoice" id="autoDirectOnCheckout3"
                                                   value="payment_gateway"
                                                   {{ Cfg::get("AutoRedirectoInvoice") == 'gateway' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" id="ShowNotesFieldonCheckoutHidden"
                                                   type="hidden" value="off" name="ShowNotesFieldonCheckout"> --}}
                                                <input class="custom-control-input" name="shownotesfieldoncheckout"
                                                   type="checkbox" id="ShowNotesFieldonCheckout"
                                                   {{ Cfg::get("ShowNotesFieldonCheckout") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="ProductMonthlyPricingBreakdownHidden"
                                                   name="ProductMonthlyPricingBreakdown"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="ProductMonthlyPricingBreakdown"
                                                   name="productmonthlypricingbreakdown"
                                                   {{ Cfg::get("ProductMonthlyPricingBreakdown") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="allowDomainTwiceHidden" name="AllowDomainsTwice"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="allowDomainTwice" name="allowdomainstwice"
                                                   {{ Cfg::get("AllowDomainsTwice") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="NoInvoiceEmailOnOrderHidden" name="NoInvoiceEmailOnOrder"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="NoInvoiceEmailOnOrder" name="noinvoicemeailonorder"
                                                   {{ Cfg::get("NoInvoiceEmailOnOrder") ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="SkipFraudForExistingHidden" name="SkipFraudForExisting"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="SkipFraudForExisting" name="skipfraudforexisting"
                                                   {{ Cfg::get("SkipFraudForExisting") ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="AutoProvisionExistingOnlyHidden" name="AutoProvisionExistingOnly"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="AutoProvisionExistingOnly" name="autoprovisionexistingonly"
                                                   {{ Cfg::get("AutoProvisionExistingOnly") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="GenerateRandomUsernameHidden" name="GenerateRandomUsername"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="GenerateRandomUsername" name="generaterandomusername"
                                                   {{ Cfg::get("GenerateRandomUsername") ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="ProrataClientsAnniversaryDateHidden"
                                                   name="ProrataClientsAnniversaryDate"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="prorataclientsanniversarydate" name="prorataclientsanniversarydate"
                                                   {{ Cfg::get("ProrataClientsAnniversaryDate") ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="prorataclientsanniversarydate">
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="AllowRegisterHidden" name="AllowRegister"> --}}
                                                <input class="custom-control-input" type="checkbox" 
                                                   id="AllowRegister" name="allowregister"
                                                   {{ Cfg::get("AllowRegister") == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowRegister">
                                                   Allow clients to register domains with you
                                                </label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="AlloTransferHidden" name="AllowTransfer"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="AlloTransfer" name="allowtransfer"
                                                   {{ Cfg::get("AllowTransfer") == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AlloTransfer">
                                                   Allow clients to transfer a domain to you
                                                </label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="AllowOwnDomainHidden" name="AllowOwnDomain"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="AllowOwnDomain" name="allowowndomain"
                                                   {{ Cfg::get("AllowOwnDomain") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="EnableDomainRenewalOrdersHidden" name="EnableDomainRenewalOrders"> --}}
                                                <input class="custom-control-input" type="checkbox" 
                                                   id="EnableDomainRenewalOrders" name="enabledomainrenewalorders"
                                                   {{ Cfg::get("EnableDomainRenewalOrders") ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="AutoRenewDomainsonPaymentHidden" name="AutoRenewDomainsonPayment"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="AutoRenewDomainsonPayment" name="autorenewdomainsonpayment"
                                                   {{ Cfg::get("AutoRenewDomainsonPayment") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="FreeDomainAutoRenewRequiresProductHidden"
                                                   name="FreeDomainAutoRenewRequiresProduct"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="FreeDomainAutoRenewRequiresProduct"
                                                   name="freedomainautorenewrequiresproduct"
                                                   {{ Cfg::get("FreeDomainAutoRenewRequiresProduct") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="DomainAutoRenewDefaultHidden" name="DomainAutoRenewDefault"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="DomainAutoRenewDefault" name="domainautorenewdefault"
                                                   {{ Cfg::get("DomainAutoRenewDefault") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="DomainToDoListEntriesHidden" name="DomainToDoListEntries"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="DomainToDoListEntries" name="domaintodolistentries"
                                                   {{ Cfg::get("DomainToDoListEntries") ? 'checked' : '' }}>
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
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="AllowIDNDomainsHidden" name="AllowIDNDomains"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="AllowIDNDomains" name="allowidndomains"
                                                   {{ Cfg::get("AllowIDNDomains") ? 'checked' : '' }} {{$hasMbstring === false ? 'disabled':''}}>
                                                <label class="custom-control-label" for="AllowIDNDomains">
                                                   Tick this box to enable Internationalized Domain
                                                   Names
                                                   (IDN)
                                                   support.
                                                </label>
                                             </div>
                                             @if ($hasMbstring === false)
                                                <div id="warnIDN" style="background: #FCFCFC; border: 1px solid red; padding: 2px; max-width: 50em">
                                                   {!!Lang::get("admin.generalidnmbstringwarning")!!}
                                                </div>
                                             @endif
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
                                                   name="disabledomaingrace" value="0"
                                                   id="DisableDomainGraceAndRedemptionFeesOn"
                                                   {{ !Cfg::get("DisableDomainGraceAndRedemptionFees") ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="DisableDomainGraceAndRedemptionFeesOn">
                                                   Enable
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio d-inline">
                                                <input class="custom-control-input" type="radio"
                                                   name="disabledomaingrace"
                                                   id="DisableDomainGraceAndRedemptionFeesOff" value="1"
                                                   {{ Cfg::get("DisableDomainGraceAndRedemptionFees") ? 'checked' : '' }}>
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
                                                   name="domainExpiryFeeHandling" id="exampleRadios3"
                                                   value="existing" {{Cfg::get("DomainExpirationFeeHandling") == "existing" ? 'checked':''}}>
                                                <label class="custom-control-label" for="exampleRadios3">
                                                   Add Grace and Redemption Fees to existing
                                                   invoice
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="domainExpiryFeeHandling" id="exampleRadios4"
                                                   value="option4" {{Cfg::get("DomainExpirationFeeHandling") == "new" ? 'checked':''}}>
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
                                                name="ns1" value="{{ Cfg::get("DefaultNameserver1") }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default
                                             Nameserver
                                             2</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <input type="text" class="form-control" placeholder="ns2.yourdomain.com"
                                                name="ns2" value="{{ Cfg::get("DefaultNameserver2") }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default
                                             Nameserver
                                             3</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <input type="text" class="form-control" name="ns3"
                                                value="{{ Cfg::get("DefaultNameserver3") }}" placeholder="ns3.yourdomain.com">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default
                                             Nameserver
                                             4</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <input type="text" class="form-control" name="ns4"
                                                value="{{ Cfg::get("DefaultNameserver4") }}" placeholder="ns4.yourdomain.com">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Default
                                             Nameserver
                                             5</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <input type="text" class="form-control" name="ns5"
                                                value="{{ Cfg::get("DefaultNameserver5") }}" placeholder="ns5.yourdomain.com">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Use Clients
                                             Details</label>
                                          <div class="col-sm-12 col-lg-8 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                {{-- <input class="custom-control-input" type="hidden" value="off"
                                                   id="RegistrarAdminUseClientDetailsHidden"
                                                   name="RegistrarAdminUseClientDetails"> --}}
                                                <input class="custom-control-input" type="checkbox"
                                                   id="RegistrarAdminUseClientDetails"
                                                   name="domuseclientsdetails"
                                                   {{ Cfg::get("RegistrarAdminUseClientDetails") == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="RegistrarAdminUseClientDetails">
                                                   Tick this box to use clients details for the
                                                   Billing/Admin/Tech contacts
                                                </label>
                                             </div>
                                          </div>
                                       </div>

                                       <div class=""
                                          id="clientField">
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">First
                                                Name</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="domfirstname"
                                                   value="{{Cfg::get("RegistrarAdminFirstName")}}">
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
                                                <input type="text" class="form-control" name="domlastname"
                                                   value="{{Cfg::get("RegistrarAdminLastName")}}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Company
                                                Name</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="domcompanyname"
                                                   value="{{Cfg::get("RegistrarAdminCompanyName")}}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Email
                                                Address</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control"
                                                   name="domemail" value="{{Cfg::get("RegistrarAdminEmailAddress")}}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Address
                                                1</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="domaddress1" value="{{Cfg::get("RegistrarAdminAddress1")}}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Address
                                                2</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="domaddress2" value="{{Cfg::get("RegistrarAdminAddress2")}}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">City</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="domcity" value="{{Cfg::get("RegistrarAdminCity")}}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">State/Region</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control"
                                                   name="domstate" value="{{Cfg::get("RegistrarAdminStateProvince")}}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Postcode</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="text" class="form-control" name="dompostcode" value="{{Cfg::get("RegistrarAdminPostalCode")}}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Country</label>
                                             <div class="col-sm-12 col-lg-3">
                                                {{-- <select name="RegistrarAdminCountry" id="country-name"
                                                   class="form-control">
                                                   <option value="ID">Indonesia</option>
                                                </select> --}}
                                                {!!App\Helpers\ClientHelper::getCountriesDropDown(Cfg::get("RegistrarAdminCountry"), "domcountry")!!}
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Phone
                                                Number</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="number" class="form-control" name="domphone" value="{{Cfg::get("RegistrarAdminPhone")}}">
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
                                                   {{ Cfg::get("MailType") == 'mail' ? 'selected' : '' }}>
                                                   PHP
                                                   Mail</option>
                                                <option value="mail" {{ Cfg::get("MailType") == 'smtp' ? 'selected' : '' }}>SMTP
                                                </option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Mail
                                             Encoding</label>
                                          <div class="col-sm-12 col-lg-10">
                                             {!!$frm1->dropdown("mailencoding", $validMailEncodings, Cfg::get("MailEncoding"))!!}
                                             {{-- <select name="MailEncoding" id="MailEncoding" class="form-control">
                                                <option value="8bit" {{ Cfg::get("OrderFormTemplate") == '' ? 'selected' : '' }}>8bit
                                                </option>
                                                <option value="7bit" {{ Cfg::get("OrderFormTemplate") == '' ? 'selected' : '' }}>7bit
                                                </option>
                                                <option value="binary" {{ Cfg::get("OrderFormTemplate") == '' ? 'selected' : '' }}>
                                                   binary
                                                </option>
                                                <option value="base64">
                                                   {{ Cfg::get("OrderFormTemplate") == '' ? 'selected' : '' }}base64
                                                </option>
                                                <option value="quoted-printable"
                                                   {{ Cfg::get("OrderFormTemplate") == '' ? 'selected' : '' }}>
                                                   quoted-printable
                                                </option>
                                             </select> --}}
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">SMTP
                                             Port</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" placeholder="25" name="smtpport"
                                                value="{{ Cfg::get("SMTPPort") }}">
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
                                             <input type="text" class="form-control" name="smtphost"
                                                value="{{ Cfg::get("SMTPHost") }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">SMTP
                                             Username</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="text" class="form-control" name="smtpusername"
                                                value="{{ Cfg::get("SMTPUsername") }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">SMTP
                                             Password</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="password" class="form-control" name="smtppassword"
                                                value="{{ \App\Helpers\AdminFunctions::replacePasswordWithMasks((new \App\Helpers\Pwd)->decrypt(Cfg::get("SMTPPassword"))) }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">SMTP SSL
                                             Type</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="d-flex align-items-center py-2">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                   <input class="custom-control-input" type="radio" name="smtpssl"
                                                      id="mail-smtp-nossl" value=""
                                                      {{ Cfg::get("SMTPSSL") == '' ? 'checked' : '' }}>
                                                   <label class="custom-control-label" for="mail-smtp-nossl">None</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                   <input class="custom-control-input" type="radio" name="smtpssl"
                                                      id="mail-smtp-ssl" value="ssl"
                                                      {{ Cfg::get("SMTPSSL") == 'ssl' ? 'checked' : '' }}>
                                                   <label class="custom-control-label" for="mail-smtp-ssl">SSL</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                   <input class="custom-control-input" type="radio" name="smtpssl"
                                                      id="mail-smtp-tls" value="tls"
                                                      {{ Cfg::get("SMTPSSL") == 'tls' ? 'checked' : '' }}>
                                                   <label class="custom-control-label" for="mail-smtp-tls">TLS</label>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Global
                                             Email
                                             Signature</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="signature" id="Signature" class="form-control" cols="30"
                                                rows="5">{{ Cfg::get("Signature") }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Global
                                             Email
                                             CSS
                                             Styling</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="emailcss" id="EmailCSS" class="form-control" cols="30"
                                                rows="5" placeholder="">{{ Cfg::get("EmailCSS") }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Client
                                             Email
                                             Header
                                             Content</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <textarea name="emailglobalheader" id="EmailGlobalHeader"
                                                class="form-control" cols="30" rows="5"
                                                placeholder="">{!! App\Helpers\Sanitize::makeSafeForOutput(Cfg::get("EmailGlobalHeader")) !!}</textarea>
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
                                             <textarea name="emailglobalfooter" id="EmailGlobalFooter"
                                                class="form-control" cols="30" rows="5"
                                                placeholder="">{!! App\Helpers\Sanitize::makeSafeForOutput(Cfg::get("EmailGlobalFooter")) !!}</textarea>
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
                                             <input type="text" class="form-control" name="systememailsfromname"
                                                value="{{ App\Helpers\Sanitize::makeSafeForOutput(Cfg::get("SystemEmailsFromName")) }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">System
                                             Email
                                             From
                                             Email</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" name="systememailsfromemail"
                                                value="{{ Cfg::get("SystemEmailsFromEmail") }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">BCC
                                             Messages</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="text" class="form-control" name="bccmessages"
                                                value="{{ Cfg::get("BCCMessages") }}">
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
                                             <select name="contactformdept" id="ContactFormDept" class="form-control">
                                                <option value="">{{Lang::get("admin.generalpresalesdept")}}</option>
                                                @php
                                                   $dept_query = \App\Models\Ticketdepartment::all();
                                                @endphp
                                                @foreach ($dept_query->toArray() as $dept_result)
                                                    <option value="{{$dept_result['id']}}" {{$dept_result['id']==Cfg::get("ContactFormDept")?'selected':''}}>{{$dept_result['name']}}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Presales
                                             Contact
                                             Form
                                             Email</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" class="form-control" name="contactformto"
                                                id="ContactFormTo" value="{{ Cfg::get("ContactFormTo") }}">
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
                                             <select name="supportmodule" id="support-module" class="form-control">
                                                <option value="">
                                                   CBMS Built-in-System
                                                </option>
                                                {{-- TODO: this --}}
                                             </select>
                                          </div>
                                       </div>
                                       @php
                                          $ticketEmailLimit = (int) Cfg::get("TicketEmailLimit");
                                          if (!$ticketEmailLimit) {
                                             $ticketEmailLimit = 10;
                                          }
                                       @endphp
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Support
                                             Ticket
                                             Mask
                                             Format</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <input type="text" name="ticketmask" class="form-control"
                                                value="{{ Cfg::get("TicketMask") }}">
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
                                             <select name="supportticketorder" id="ticket-reply-list-order"
                                                class="form-control">
                                                <option value="ASC" {{ Cfg::get("SupportTicketOrder") == 'ASC' ? 'selected' : '' }}>
                                                   Ascending (Oldest to Newest)
                                                </option>
                                                <option value="DESC" {{ Cfg::get("SupportTicketOrder") == 'DESC' ? 'selected' : '' }}>
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
                                             <input type="text" name="ticketEmailLimit" class="form-control"
                                                value="{{ $ticketEmailLimit }}">
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="ShowClientOnlyDeptsHidden" name="ShowClientOnlyDepts" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="ShowClientOnlyDepts" name="showclientonlydepts"
                                                   {{ Cfg::get("ShowClientOnlyDepts") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="RequireLoginforClientTicketsHidden"
                                                   name="RequireLoginforClientTickets" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="RequireLoginforClientTickets" name="requireloginforclienttickets"
                                                   {{ Cfg::get("RequireLoginforClientTickets") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="SupportTicketKBSuggestionsHidden" name="SupportTicketKBSuggestions"
                                                   value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="SupportTicketKBSuggestions" name="supportticketkbsuggestions"
                                                   {{ Cfg::get("SupportTicketKBSuggestions") == 'on' ? ' checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="AttachmentThumbnailsHidden" name="AttachmentThumbnails" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AttachmentThumbnails" name="attachmentthumbnails"
                                                   {{ Cfg::get("AttachmentThumbnails") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="TicketRatingEnabledHidden" name="TicketRatingEnabled" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="TicketRatingEnabled" name="ticketratingenabled"
                                                   {{ Cfg::get("TicketRatingEnabled") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="TicketAddCarbonCopyRecipientsHidden"
                                                   name="TicketAddCarbonCopyRecipients" value="off"> --}}
                                                <input type="hidden" name="ticket_add_cc" value="0">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="TicketAddCarbonCopyRecipients" name="ticket_add_cc" value="1"
                                                   {{ (bool) Cfg::get("TicketAddCarbonCopyRecipients") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="PreventEmailReopeningHidden" name="PreventEmailReopening"
                                                   value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="PreventEmailReopening" name="preventEmailReopening"
                                                   {{ (bool) Cfg::get("PreventEmailReopening") ? 'checked' : '' }}>
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
                                                   name="lastreplyupdate" id="UpdateLastReplyTimestamp1"
                                                   value="always"
                                                   {{!Cfg::get("UpdateLastReplyTimestamp") || Cfg::get("UpdateLastReplyTimestamp") == 'always' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="UpdateLastReplyTimestamp1">Every
                                                   time a
                                                   reply is made (Default) </label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio"
                                                   name="lastreplyupdate" id="UpdateLastReplyTimestamp2"
                                                   value="statusonly"
                                                   {{ Cfg::get("UpdateLastReplyTimestamp") == 'statusonly' ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="DisableSupportTicketReplyEmailsLoggingHidden"
                                                   name="DisableSupportTicketReplyEmailsLogging" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="DisableSupportTicketReplyEmailsLogging"
                                                   name="disablesupportticketreplyemailslogging"
                                                   {{ Cfg::get("DisableSupportTicketReplyEmailsLogging") ? 'checked' : '' }}>
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
                                                placeholder=".jpg,.gif,.jpeg,.png,.txt,.pdf" name="allowedfiletypes"
                                                value="{{ Cfg::get("TicketAllowedFileTypes") }}">
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="NetworkIssuesRequireLoginHidden" name="NetworkIssuesRequireLogin"
                                                   value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="NetworkIssuesRequireLogin" name="networkissuesrequirelogin"
                                                   {{ Cfg::get("NetworkIssuesRequireLogin") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="DownloadsIncludeProductLinkedHidden"
                                                   name="DownloadsIncludeProductLinked" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="DownloadsIncludeProductLinked" name="dlinclproductdl"
                                                   {{ !empty(Cfg::get("DownloadsIncludeProductLinked")) ? 'checked' : '' }}>
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
                                          <label class="col-sm-12 col-lg-2 col-form-label">Continuous Invoice Generation </label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="ContinuousInvoiceGenerationHidden"
                                                   name="ContinuousInvoiceGeneration" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="ContinuousInvoiceGeneration" name="continuousinvoicegeneration"
                                                   {{ Cfg::get("ContinuousInvoiceGeneration") == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ContinuousInvoiceGeneration">If
                                                   enabled, invoices will be generated for each
                                                   cycle
                                                   even
                                                   if
                                                   the previous invoice remains unpaid</label>
                                             </div>
                                          </div>
                                       </div>
                                       {{-- <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable
                                             Metric
                                             Usage
                                             Invoicing</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                <input type="hidden" class="custom-control-input"
                                                   id="MetricUsageInvoicingHidden" name="MetricUsageInvoicing" value="off">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="MetricUsageInvoicing" name="MetricUsageInvoicing"
                                                   {{ Cfg::get("OrderFormTemplate") == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="MetricUsageInvoicing">Tick
                                                   to
                                                   enable invoicing of metric usage for all priced
                                                   product
                                                   metrics</label>
                                             </div>
                                          </div>
                                       </div> --}}
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Enable PDF
                                             Invoices</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="EnablePDFInvoicesHidden" name="EnablePDFInvoices" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input" id="EnablePDFInvoices"
                                                   name="enablepdfinvoices"
                                                   {{ Cfg::get("EnablePDFInvoices") == 'on' ? 'checked' : '' }}>
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
                                             <select name="pdfpapersize" id="pdf-paper-size" class="form-control">
                                                <option value="A0" {{ Cfg::get("PDFPaperSize") == 'A0' ? 'selected' : '' }}>A0
                                                </option>
                                                <option value="A1" {{ Cfg::get("PDFPaperSize") == 'A1' ? 'selected' : '' }}>A1
                                                </option>
                                                <option value="A2" {{ Cfg::get("PDFPaperSize") == 'A2' ? 'selected' : '' }}>A2
                                                </option>
                                                <option value="A3" {{ Cfg::get("PDFPaperSize") == 'A3' ? 'selected' : '' }}>A3
                                                </option>
                                                <option value="A4" {{ Cfg::get("PDFPaperSize") == 'A4' ? 'selected' : '' }}>A4
                                                </option>
                                                <option value="A5" {{ Cfg::get("PDFPaperSize") == 'A5' ? 'selected' : '' }}>A5
                                                </option>
                                                <option value="Letter" {{ Cfg::get("PDFPaperSize") == 'Letter' ? 'selected' : '' }}>Letter
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
                                             @php
                                                $defaultFont = false;
                                             @endphp
                                             @foreach ($tcpdfDefaultFonts as $font)
                                                <div class="custom-control custom-radio custom-control-inline">
                                                   <input class="custom-control-input" type="radio" id="TCPDFFont{{$loop->index}}"
                                                      value="{{$font}}" name="tcpdffont"
                                                      @if ($font == $activeFontName)
                                                         checked
                                                         @php
                                                            $defaultFont = true;
                                                            $activeFontName = "";
                                                         @endphp
                                                      @endif
                                                   >
                                                   <label class="custom-control-label" for="TCPDFFont{{$loop->index}}">{{ucfirst($font)}}<label>
                                                </div>
                                             @endforeach
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" id="TCPDFFontCustom"
                                                   value="custom" name="tcpdffont" {{!$defaultFont?'checked':''}}>
                                                <label class="custom-control-label" for="TCPDFFontCustom">Custom<label>
                                             </div>
                                             <div class="form-group">
                                                <div class="collapse" id="TCPDFFontTextInput">
                                                   <input type="text" id="TCPDFFontTextField"
                                                      class="form-control d-inline mt-2 w-50" name="tcpdffontcustom"
                                                      placeholder="Enter your font name here"
                                                      value="{{ $activeFontName }}">
                                                </div>
                                             </div>
                                          </div>
                                       </div>

                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Billing Notification Receiver</label>
                                          <div class="col-sm-12 col-lg-8">
                                             <input type="text" class="form-control" name="billingnotificationreceiver" 
                                                   value="{{ Cfg::get('BillingNotificationReceiver') }}" 
                                                   placeholder="email1, email2, email3, email4">
                                          </div>
                                          <div class="col-sm-12 col-lg-2">
                                             <p class="m-0 pt-2 font-size-13">
                                                   Separate multiple emails with commas
                                             </p>
                                          </div>
                                       </div>

                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Store
                                             Client
                                             Data
                                             Snapshot</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="StoreClientDataSnapshotOnInvoiceCreationHidden"
                                                   name="StoreClientDataSnapshotOnInvoiceCreation" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="StoreClientDataSnapshotOnInvoiceCreation"
                                                   name="invoiceclientdatasnapshot"
                                                   {{ !empty(Cfg::get("StoreClientDataSnapshotOnInvoiceCreation")) ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input" id="EnableMassPayHidden"
                                                   name="EnableMassPay" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input" id="EnableMassPay"
                                                   name="enablemasspay"
                                                   {{ Cfg::get("EnableMassPay") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="AllowCustomerChangeInvoiceGatewayHidden"
                                                   name="AllowCustomerChangeInvoiceGateway" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AllowCustomerChangeInvoiceGateway"
                                                   name="allowcustomerchangeinvoicegateway"
                                                   {{ Cfg::get("AllowCustomerChangeInvoiceGateway") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="GroupSimilarLineItemsHidden" name="GroupSimilarLineItems"
                                                   value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="GroupSimilarLineItems" name="groupsimilarlineitems"
                                                   {{ Cfg::get("GroupSimilarLineItems") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="AutoCancellationRequestsHidden" name="AutoCancellationRequests"
                                                   value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AutoCancellationRequests" name="cancelinvoiceoncancel"
                                                   {{ Cfg::get("CancelInvoiceOnCancellation") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="AutoCancelSubscriptionsHidden" name="AutoCancelSubscriptions"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AutoCancelSubscriptions" name="autoCancelSubscriptions"
                                                   {{ !empty(Cfg::get("AutoCancelSubscriptions")) ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="EnableProformaInvoicingHidden" name="EnableProformaInvoicing"
                                                   value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="enableProformaInvoicing" name="enableProformaInvoicing"
                                                   {{ Cfg::get("EnableProformaInvoicing") ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="enableProformaInvoicing">Tick
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
                                                {{-- <input type="checkbox" class="custom-control-input"
                                                   id="SequentialInvoiceNumberingHidden" name="SequentialInvoiceNumbering"
                                                   value="off"> --}}
                                                <input type="hidden" name="sequentialinvoicenumbering" value="0">
                                                <input type="checkbox" class="custom-control-input"
                                                   id="sequentialpaidnumbering" name="sequentialinvoicenumbering" value="1"
                                                   {{ Cfg::get("SequentialInvoiceNumbering") ? 'checked' : '' }} {{Cfg::get("EnableProformaInvoicing")?'disabled':''}}>
                                                <label class="custom-control-label" for="sequentialpaidnumbering">Tick
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
                                                name="sequentialinvoicenumberformat" placeholder="{NUMBER}"
                                                value="{{ Cfg::get("SequentialInvoiceNumberFormat") }}">
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
                                             <input type="text" class="form-control" placeholder="{{Cfg::get("SequentialInvoiceNumberValue")}}"
                                                name="sequentialinvoicenumbervalue"
                                                {{-- value="{{ Cfg::get("SequentialInvoiceNumberValue") }}" --}}
                                             >
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
                                                <input class="custom-control-input" type="radio" name="latefeetype"
                                                   id="lateFeeType1" value="Percentage"
                                                   {{ Cfg::get("LateFeeType") == 'Percentage' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="lateFeeType1">Percentage</label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input class="custom-control-input" type="radio" name="latefeetype"
                                                   id="lateFeeType2" value="Fixed Amount"
                                                   {{ Cfg::get("LateFeeType") == 'Fixed Amount' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="lateFeeType2">Fixed
                                                   Amount</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Late Fee
                                             Amount</label>
                                          <div class="col-sm-12 col-lg-3">
                                             <input type="text" class="form-control" name="invoicelatefeeamount"
                                                value="{{ Cfg::get("InvoiceLateFeeAmount") }}" placeholder="10.00">
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
                                             <input type="text" class="form-control" name="latefeeminimum"
                                                value="{{ Cfg::get("LateFeeMinimum") }}" placeholder="0.00">
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
                                       {{-- <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Accepted
                                             Credit
                                             Card
                                             Types</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <select name="AcceptedCardTypes[]" id="credit-card-list"
                                                class="form-control" multiple>
                                                @foreach ($acceptedCard ?? [] as $card)
                                                   <option value="{{ Cfg::get("PhoneNumberDropdown") }}" @foreach ($activeCard as $active) {{ Cfg::get("OrderFormTemplate") == $active ? 'selected' : '' }} @endforeach>
                                                      {{ Cfg::get("PhoneNumberDropdown") }}</option>
                                                @endforeach
                                             </select>
                                             <small class="m-0 p-0">
                                                Hold/Use Ctrl+Click to select Multiple Card Types
                                             </small>
                                          </div>
                                       </div> --}}
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Issue
                                             Number/Start
                                             Date</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-checkbox pt-2">
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="ShowCCIssueStartHidden" name="ShowCCIssueStart" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input" id="ShowCCIssueStart"
                                                   name="showccissuestart"
                                                   {{ Cfg::get("ShowCCIssueStart") == 'on' ? 'checked' : '' }}>
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
                                             <input type="text" class="form-control" name="invoiceincrement"
                                                value="{{ Cfg::get("InvoiceIncrement") }}" placeholder="1">
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
                                             <input type="text" class="form-control" name="invoicestartnumber" value="">
                                          </div>
                                          <div class="col-sm-12 col-lg-7">
                                             <p class="m-0 pt-2 my-1 font-size-13">
                                                Enter to set the next invoice number, must be
                                                greater
                                                than
                                                last
                                                @php
                                                   $maxinvnum = \App\Models\Invoiceitem::orderBy("invoiceid", "DESC")->value("invoiceid") ?? 0;
                                                @endphp
                                                #{{ $maxinvnum ? $maxinvnum : "0" }} (Blank for no change)
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="AddFundsEnabledHidden" name="AddFundsEnabled" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input" id="AddFundsEnabled"
                                                   name="addfundsenabled"
                                                   {{ Cfg::get("AddFundsEnabled") ? 'checked' : '' }}>
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
                                             <input type="text" class="form-control" name="addfundsminimum"
                                                placeholder="100000.00" value="{{ Cfg::get("AddFundsMinimum") }}">
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
                                                name="addfundsmaximum" value="{{ Cfg::get("AddFundsMaximum") }}">
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
                                                name="addfundsmaximumbalance" value="{{ Cfg::get("AddFundsMaximumBalance") }}">
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="AddFundsRequireOrderHidden" name="AddFundsRequireOrder" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input"
                                                   id="AddFundsRequireOrder" name="addfundsrequireorder"
                                                   {{ Cfg::get("AddFundsRequireOrder") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="NoAutoApplyCreditHidden" name="NoAutoApplyCredit"> --}}
                                                <input type="checkbox" class="custom-control-input" id="NoAutoApplyCredit"
                                                   name="noautoapplycredit" value="on"
                                                   {{ !Cfg::get("NoAutoApplyCredit") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" class="custom-control-input"
                                                   id="CreditOnDowngradeHidden" name="CreditOnDowngrade" value="off"> --}}
                                                <input type="checkbox" class="custom-control-input" id="CreditOnDowngrade"
                                                   name="creditondowngrade"
                                                   {{ Cfg::get("CreditOnDowngrade") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="AffiliateEnabled" class="custom-control-input"
                                                   id="AffiliateEnabledHidden" value="off"> --}}
                                                <input type="checkbox" name="affiliateenabled"
                                                   class="custom-control-input" id="AffiliateEnabled"
                                                   {{ Cfg::get("AffiliateEnabled") == 'on' ? 'checked' : '' }}>
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
                                                name="affiliateearningpercent" value="{{ Cfg::get("AffiliateEarningPercent") }}"
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
                                                name="affiliatebonusdeposit" id="AffiliateBonusDeposit"
                                                value="{{ Cfg::get("AffiliateBonusDeposit") }}">
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
                                                name="affiliatepayout" id="AffiliatePayout"
                                                value="{{ Cfg::get("AffiliatePayout") }}">
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
                                                name="affiliatesdelaycommission" id="AffiliatesDelayCommission"
                                                value="{{ Cfg::get("AffiliatesDelayCommission") }}">
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
                                             <select name="affiliatedepartment" id="payout-request-department"
                                                class="form-control">
                                                @foreach ($dept_query->toArray() as $dept_result)
                                                    <option value="{{$dept_result['id']}}" {{$dept_result['id']==Cfg::get("AffiliateDepartment")?'selected':''}}>{{$dept_result['name']}}</option>
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
                                             <textarea name="affiliatelinks" id="AffiliateLinks" cols="30" rows="5"
                                                class="form-control">{{ Cfg::get("AffiliateLinks") }}</textarea>
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
                                    {{-- TODO: Security Tab Content --}}
                                    <div class="tab-pane fade" id="nav-security" role="tabpanel"
                                       aria-labelledby="nav-security-tab">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Email
                                             Verification</label>
                                          <div class="col-sm-12 col-lg-5 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                {{-- <input name="EnableEmailVerification" type="hidden"
                                                   class="custom-control-input" id="EnableEmailVerificationHidden"
                                                   value="off"> --}}
                                                <input name="enable_email_verification" type="checkbox" value="1"
                                                   class="custom-control-input" id="EnableEmailVerification"
                                                   {{ Cfg::get("EnableEmailVerification") ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EnableEmailVerification">
                                                   Request users to confirm their email address on
                                                   signup
                                                   or
                                                   change of email address
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       {{-- <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Captcha
                                             Form
                                             Protection</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaSetting"
                                                   id="alwaysOn" value="always_on"
                                                   {{ Cfg::get("OrderFormTemplate") == 'always_on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="alwaysOn">
                                                   Always On (code shown to ensure human
                                                   submission)
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaSetting"
                                                   id="offWhenLogin" value="off_when_logged_in"
                                                   {{ Cfg::get("OrderFormTemplate") == 'off_when_logged_in' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="offWhenLogin">
                                                   Off when logged in
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaSetting"
                                                   id="alwaysOff" value="always_off"
                                                   {{ Cfg::get("OrderFormTemplate") == 'always_off' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="alwaysOff">
                                                   Always Off
                                                </label>
                                             </div>
                                          </div>
                                       </div> --}}
                                       {{-- <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Captcha
                                             Type</label>
                                          <div class="col-sm-12 col-lg-3 pt-2">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="CaptchaType"
                                                   id="DefaultCaptcha" value="default"
                                                   {{ Cfg::get("OrderFormTemplate") == 'default' ? 'checked' : '' }}>
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
                                                   {{ Cfg::get("OrderFormTemplate") == 'v2' ? 'checked' : '' }}>
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
                                                   {{ Cfg::get("OrderFormTemplate") == 'invisble' ? 'checked' : '' }}>
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
                                       </div> --}}
                                       {{-- <div class="form-group row">

                                          <label class="col-sm-12 col-lg-2 col-form-label">Captcha for
                                             Select
                                             Forms</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="checkoutCompletion" value="false"
                                                   class="custom-control-input" id="checkoutCompletionHidden">
                                                <input type="checkbox" name="checkoutCompletion" value="true"
                                                   class="custom-control-input" id="checkoutCompletion"
                                                   {{ Cfg::get("OrderFormTemplate") === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="checkoutCompletion">Shopping
                                                   Cart Checkout</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="domainChecker" value="false"
                                                   class="custom-control-input" id="domainCheckerHidden">
                                                <input type="checkbox" name="domainChecker" value="true"
                                                   class="custom-control-input" id="domainChecker"
                                                   {{ Cfg::get("OrderFormTemplate") === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="domainChecker">Domain
                                                   Checker</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="registration" value="false"
                                                   class="custom-control-input" id="registrationHidden">
                                                <input type="checkbox" name="registration" value="true"
                                                   class="custom-control-input" id="registration"
                                                   {{ Cfg::get("OrderFormTemplate") === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="registration">Client
                                                   Registration</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="contactUs" value="false"
                                                   class="custom-control-input" id="contactUsHidden">
                                                <input type="checkbox" name="contactUs" value="true"
                                                   class="custom-control-input" id="contactUs"
                                                   {{ Cfg::get("OrderFormTemplate") === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="contactUs">Contact
                                                   Form</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="submitTicket" value="false"
                                                   class="custom-control-input" id="submitTicketHidden">
                                                <input type="checkbox" name="submitTicket" value="true"
                                                   class="custom-control-input" id="submitTicket"
                                                   {{ Cfg::get("OrderFormTemplate") === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="submitTicket">Ticket
                                                   Submission</label>
                                             </div>
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="login" value="false"
                                                   class="custom-control-input" id="loginHidden">
                                                <input type="checkbox" name="login" value="true"
                                                   class="custom-control-input" id="login"
                                                   {{ Cfg::get("OrderFormTemplate") === 'true' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="login">Login
                                                   Forms</label>
                                             </div>
                                          </div>
                                       </div> --}}
                                       {{-- <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">reCAPTCHA
                                             Site
                                             Key</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" name="ReCAPTCHAPublicKey" class="form-control"
                                                placeholder="Site Key" value="{{ Cfg::get("PhoneNumberDropdown") }}">
                                             <small class="m-0 p-0">
                                                https://www.google.com/recaptcha/admin
                                             </small>
                                          </div>
                                          <div class="col-sm-12 col-lg-5 pt-2">
                                             <p class="m-0 p-0">
                                                You need to register for reCAPTCHA @
                                             </p>
                                          </div>
                                       </div> --}}
                                       {{-- <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">reCAPTCHA
                                             Secret
                                             Key</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input type="text" name="ReCAPTCHAPrivateKey" class="form-control"
                                                placeholder="Secret Key" value="{{ Cfg::get("PhoneNumberDropdown") }}">
                                          </div>
                                       </div> --}}
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Auto
                                             Generated
                                             Password
                                             Format</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="autogeneratedpwformat" id="AutoGeneratedPasswordFormat1"
                                                   value=""
                                                   {{ Cfg::get("AutoGeneratedPasswordFormat") != "legacy" ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AutoGeneratedPasswordFormat1">
                                                   Generate passwords containing a combination of
                                                   letters,
                                                   numbers and special characters (Default)
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="autogeneratedpwformat" id="AutoGeneratedPasswordFormat2"
                                                   value="legacy"
                                                   {{ Cfg::get("AutoGeneratedPasswordFormat") == "legacy" ? 'checked' : '' }}>
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
                                             <input type="text" name="requiredpwstrength" class="form-control"
                                                placeholder="1 - 100" value="{{ Cfg::get("RequiredPWStrength") }}">
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
                                             <input type="text" name="invalidloginsbanlength" class="form-control"
                                                value="{{ Cfg::get("InvalidLoginBanLength") }}">
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
                                             <select name="whitelistedips[]" id="whitelistedips" class="form-control"
                                                multiple>
                                                @php
                                                   $whitelistedips = Cfg::get("WhitelistedIPs") ? (new \App\Helpers\Client)->safe_unserialize(Cfg::get("WhitelistedIPs")) : array();
                                                   $whitelistedips = is_array($whitelistedips) ? $whitelistedips : array();
                                                @endphp
                                                @foreach ($whitelistedips as $whitelist)
                                                    <option value="{{$whitelist["ip"]}}">{{$whitelist["ip"]}} - {{$whitelist["note"]}}</option>
                                                @endforeach
                                             </select>
                                             <div class="d-flex justify-content-start mt-2">
                                                <button type="button" class="btn btn-outline-success btn-sm px-3 mr-2"
                                                   data-toggle="modal" data-target="#modalAddWhiteListIp">Add
                                                   IP</button>
                                                <button type="button"
                                                   id="removewhitelistedip"
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
                                                {{-- <input type="hidden" name="sendFailedLoginWhitelist"
                                                   class="custom-control-input" id="sendFailedLoginWhitelistHidden"
                                                   value="0"> --}}
                                                <input type="checkbox" name="sendFailedLoginWhitelist"
                                                   class="custom-control-input" id="sendFailedLoginWhitelist"
                                                   {{ Cfg::get("sendFailedLoginWhitelist") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="DisableAdminPWReset" value="off"
                                                   class="custom-control-input" id="DisableAdminPWResetHidden"> --}}
                                                <input type="checkbox" name="disableadminpwreset"
                                                   class="custom-control-input" id="DisableAdminPWReset"
                                                   {{ Cfg::get("DisableAdminPWReset") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="CCAllowCustomerDelete" value="off"
                                                   class="custom-control-input" id="CCAllowCustomerDeleteHidden"> --}}
                                                <input type="checkbox" name="ccallowcustomerdelete"
                                                   class="custom-control-input" id="CCAllowCustomerDelete"
                                                   {{ Cfg::get("CCAllowCustomerDelete") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="DisableSessionIPCheck" value="off"
                                                   class="custom-control-input" id="DisableSessionIPCheckHidden"> --}}
                                                <input type="checkbox" name="disablesessionipcheck"
                                                   class="custom-control-input" id="DisableSessionIPCheck"
                                                   {{ Cfg::get("DisableSessionIPCheck") ? 'checked' : '' }}>
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
                                                <input class="custom-control-input" type="radio" name="allowsmartyphptags"
                                                   id="AllowSmartyPhpTags1" value="1"
                                                   {{ !empty(Cfg::get("AllowSmartyPhpTags")) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowSmartyPhpTags1">
                                                   Enabled
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="allowsmartyphptags"
                                                   id="AllowSmartyPhpTags2" value="0"
                                                   {{ empty(Cfg::get("AllowSmartyPhpTags")) ? 'checked' : '' }}>
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
                                             <input type="text" class="form-control" name="proxyheader" id="proxyHeader"
                                                value="{{ Cfg::get("proxyHeader") }}">
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
                                             <select name="apiallowedips[]" id="apiallowedips" class="form-control"
                                                multiple>
                                                @php
                                                   $whitelistedips = (new \App\Helpers\Client)->safe_unserialize(Cfg::get("APIAllowedIPs"));
                                                   foreach ($whitelistedips as $whitelist) {
                                                      echo "<option value=" . $whitelist["ip"] . ">" . $whitelist["ip"] . " - " . $whitelist["note"] . "</option>";
                                                   }
                                                @endphp
                                             </select>
                                             <div class="d-flex justify-content-start mt-2">
                                                <button type="button" class="btn btn-outline-success btn-sm px-3 mr-2"
                                                   data-toggle="modal" data-target="#modalAddApiIp">Add
                                                   IP</button>
                                                <button type="button"
                                                   id="removeapiip"
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
                                                {{-- <input type="hidden" name="LogAPIAuthentication" value="off"
                                                   class="custom-control-input" id="LogAPIAuthenticationHidden"> --}}
                                                <input type="checkbox" name="logapiauthentication" value="1"
                                                   class="custom-control-input" id="LogAPIAuthentication"
                                                   {{ Cfg::get("LogAPIAuthentication") ? 'checked' : '' }}>
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
                                                   {{ Cfg::get("OrderFormTemplate") == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowAutoAuth1">
                                                   Enabled
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="AllowAutoAuth"
                                                   id="AllowAutoAuth2" value="0"
                                                   {{ Cfg::get("OrderFormTemplate") == '0' ? 'checked' : '' }}>
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
                                             <input type="text" class="form-control" name="twitterusername"
                                                value="{{ Cfg::get("TwitterUsername") }}" />
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
                                                {{-- <input type="hidden" name="AnnouncementsTweet" value="off"
                                                   class="custom-control-input" id="AnnouncementsTweetHidden"> --}}
                                                <input type="checkbox" name="announcementstweet"
                                                   class="custom-control-input" id="AnnouncementsTweet"
                                                   {{ Cfg::get("AnnouncementsTweet") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="AnnouncementsFBRecommend" value="off"
                                                   class="custom-control-input" id="AnnouncementsFBRecommendHidden"> --}}
                                                <input type="checkbox" name="announcementsfbrecommend"
                                                   class="custom-control-input" id="AnnouncementsFBRecommend"
                                                   {{ Cfg::get("AnnouncementsFBRecommend") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="AnnouncementsFBComments" value="off"
                                                   class="custom-control-input" id="AnnouncementsFBCommentsHidden"> --}}
                                                <input type="checkbox" name="announcementsfbcomments"
                                                   class="custom-control-input" id="AnnouncementsFBComments"
                                                   {{ Cfg::get("AnnouncementsFBComments") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="EmailMarketingRequireOptIn" value="off"
                                                   class="custom-control-input" id="EmailMarketingRequireOptInHidden"> --}}
                                                <input type="checkbox" name="allowclientsemailoptout" value="1"
                                                   class="custom-control-input" id="EmailMarketingRequireOptIn"
                                                   {{ Cfg::get("AllowClientsEmailOptOut") ? 'checked' : '' }}>
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
                                                   name="marketingreqoptin" id="AllowClientsEmailOptOut1" value="1"
                                                   {{ Cfg::get("EmailMarketingRequireOptIn") ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowClientsEmailOptOut1">
                                                   Enabled - Require users to opt-in to marketing
                                                   emails
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="marketingreqoptin" id="AllowClientsEmailOptOut2" value="0"
                                                   {{ !Cfg::get("EmailMarketingRequireOptIn") ? 'checked' : '' }}>
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
                                             <textarea name="marketingoptinmessage" id="EmailMarketingOptInMessage"
                                                cols="30" rows="5"
                                                class="form-control">{{ Cfg::get("EmailMarketingOptInMessage") }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Admin
                                             Client
                                             Display Format</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="clientdisplayformat" id="ClientDisplayFormat1" value="1"
                                                   {{ Cfg::get("ClientDisplayFormat") == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ClientDisplayFormat1">
                                                   Show first name/last name only
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="clientdisplayformat" id="ClientDisplayFormat2" value="2"
                                                   {{ Cfg::get("ClientDisplayFormat") == '2' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ClientDisplayFormat2">
                                                   Show company name if set, otherwise first
                                                   name/last
                                                   name
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio"
                                                   name="clientdisplayformat" id="ClientDisplayFormat3" value="3"
                                                   {{ Cfg::get("ClientDisplayFormat") == '3' ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="DefaultToClientArea" value="off"
                                                   class="custom-control-input" id="DefaultToClientAreaHidden"> --}}
                                                <input type="checkbox" name="defaulttoclientarea"
                                                   class="custom-control-input" id="DefaultToClientArea"
                                                   {{ Cfg::get("DefaultToClientArea") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="AllowClientRegister" value="off"
                                                   class="custom-control-input" id="AllowClientRegisterHidden"> --}}
                                                <input type="checkbox" name="allowclientregister"
                                                   class="custom-control-input" id="AllowClientRegister"
                                                   {{ Cfg::get("AllowClientRegister") == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="AllowClientRegister">Tick
                                                   this box to allow registration without ordering
                                                   any
                                                   products/services</label>
                                             </div>
                                          </div>
                                       </div>
                                       {{-- <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Client
                                             Email
                                             Preferences</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="DisableClientEmailPreferences" value="off"
                                                   class="custom-control-input" id="DisableClientEmailPreferencesHidden">
                                                <input type="checkbox" name="DisableClientEmailPreferences"
                                                   class="custom-control-input" id="DisableClientEmailPreferences"
                                                   {{ Cfg::get("OrderFormTemplate") == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                   for="DisableClientEmailPreferences">Tick
                                                   this box to allow clients to customise the email
                                                   notification types they receive</label>
                                             </div>
                                          </div>
                                       </div> --}}
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
                                                @php
                                                   $ClientsProfileOptionalFields = explode(",", Cfg::get("ClientsProfileOptionalFields"));
                                                   $updatefieldsarray = array("firstname" => $aInt->lang("fields", "firstname"), "lastname" => $aInt->lang("fields", "lastname"), "address1" => $aInt->lang("fields", "address1"), "city" => $aInt->lang("fields", "city"), "state" => $aInt->lang("fields", "state"), "postcode" => $aInt->lang("fields", "postcode"), "phonenumber" => $aInt->lang("fields", "phonenumber"));
                                                   $fieldcount = 0;
                                                @endphp
                                                @foreach ($updatefieldsarray as $field => $displayname)
                                                   <div class="col-sm-12 col-lg-3">
                                                      <div class="custom-control custom-checkbox">
                                                         {{-- <input name="optFirstName" value="false" type="hidden"
                                                            class="custom-control-input" id="optFirstNameHidden"> --}}
                                                         <input name="clientsprofoptional[]" value="{{$field}}" type="checkbox"
                                                            class="custom-control-input" id="opt{{$loop->index}}"
                                                            {{ in_array($field, $ClientsProfileOptionalFields) ? 'checked' : '' }}>
                                                         <label class="custom-control-label" for="opt{{$loop->index}}">{{$displayname}}</label>
                                                      </div>
                                                   </div>
                                                @endforeach
                                                {{-- <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="optFirstName" value="false" type="hidden"
                                                         class="custom-control-input" id="optFirstNameHidden">
                                                      <input name="optFirstName" value="true" type="checkbox"
                                                         class="custom-control-input" id="optFirstName"
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="optCity">City</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="optStateRegion" value="false" type="hidden"
                                                         class="custom-control-input" id="optStateRegionHidden">
                                                      <input name="optStateRegion" value="true" type="checkbox"
                                                         class="custom-control-input" id="optStateRegion"
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="optPhoneNumber">Phone
                                                         Number</label>
                                                   </div>
                                                </div> --}}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Locked
                                             Client
                                             Profile Fields</label>
                                          @php
                                             $ClientsProfileUneditableFields = explode(",", Cfg::get("ClientsProfileUneditableFields"));
                                             $updatefieldsarray = array("firstname" => Lang::get("admin.fieldsfirstname"), "lastname" => Lang::get("admin.fieldslastname"), "companyname" => Lang::get("admin.fieldscompanyname"), "email" => Lang::get("admin.fieldsemail"), "address1" => Lang::get("admin.fieldsaddress1"), "address2" => Lang::get("admin.fieldsaddress2"), "city" => Lang::get("admin.fieldscity"), "state" => Lang::get("admin.fieldsstate"), "postcode" => Lang::get("admin.fieldspostcode"), "country" => Lang::get("admin.fieldscountry"), "phonenumber" => Lang::get("admin.fieldsphonenumber"), "tax_id" => Lang::get(App\Helpers\Vat::getLabel()));
                                             $fieldcount = 0;
                                          @endphp
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
                                                @foreach ($updatefieldsarray as $field => $displayname)
                                                   <div class="col-sm-12 col-lg-3">
                                                      <div class="custom-control custom-checkbox">
                                                         {{-- <input type="hidden" name="lockFirstName" value="false"
                                                            class="custom-control-input" id="lockFirstNameHidden"> --}}
                                                         <input type="checkbox" name="clientsprofuneditable[]" value="{{$field}}"
                                                            class="custom-control-input" id="lock{{$loop->index}}"
                                                            {{ in_array($field, $ClientsProfileUneditableFields) ? 'checked' : '' }}>
                                                         <label class="custom-control-label" for="lock{{$loop->index}}">{{$displayname}}</label>
                                                      </div>
                                                   </div>
                                                @endforeach
                                                {{-- <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockFirstName" value="false"
                                                         class="custom-control-input" id="lockFirstNameHidden">
                                                      <input type="checkbox" name="lockFirstName" value="true"
                                                         class="custom-control-input" id="lockFirstName"
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockCity">City</label>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input type="hidden" name="lockStateRegion" value="false"
                                                         class="custom-control-input" id="lockStateRegionHidden">
                                                      <input type="checkbox" name="lockStateRegion" value="true"
                                                         class="custom-control-input" id="lockStateRegion"
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
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
                                                         {{ Cfg::get("OrderFormTemplate") == 'true' ? 'checked' : '' }}>
                                                      <label class="custom-control-label" for="lockTaxID">Tax
                                                         ID</label>
                                                   </div>
                                                </div> --}}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Client
                                             Details
                                             Change Notify</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                {{-- <input type="hidden" name="SendEmailNotificationonUserDetailsChange"
                                                   value="off" class="custom-control-input"
                                                   id="SendEmailNotificationonUserDetailsChangeHidden"> --}}
                                                <input type="checkbox" name="sendemailnotificationonuserdetailschange"
                                                   class="custom-control-input"
                                                   id="SendEmailNotificationonUserDetailsChange"
                                                   {{ Cfg::get("SendEmailNotificationonUserDetailsChange") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="ShowCancellationButton" value="off"
                                                   class="custom-control-input" id="ShowCancellationButtonHidden"> --}}
                                                <input type="checkbox" name="showcancel"
                                                   class="custom-control-input" id="ShowCancellationButton"
                                                   {{ Cfg::get("ShowCancellationButton") == 'on' ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="SendAffiliateReportMonthly" value="off"
                                                   class="custom-control-input" id="SendAffiliateReportMonthlyHidden"> --}}
                                                <input type="checkbox" name="affreport"
                                                   class="custom-control-input" id="SendAffiliateReportMonthly"
                                                   {{ Cfg::get("SendAffiliateReportMonthly") == 'on' ? 'checked' : '' }}>
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
                                             <textarea name="bannedsubdomainprefixes" id="BannedSubdomainPrefixes"
                                                cols="30" rows="2"
                                                class="form-control">{{ Cfg::get("BannedSubdomainPrefixes") }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Encoded
                                             File
                                             Loading</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="enablesafeinclude"
                                                   id="EnableSafeInclude1" value="1"
                                                   {{ Cfg::get("EnableSafeInclude") ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="EnableSafeInclude1">
                                                   Do not load files encoded with ionCube for
                                                   unknown
                                                   PHP
                                                   targets
                                                </label>
                                             </div>
                                             <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" name="enablesafeinclude"
                                                   id="EnableSafeInclude2" value="0"
                                                   {{ !Cfg::get("EnableSafeInclude") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="DisplayErrors" value="off"
                                                   class="custom-control-input" id="DisplayErrorsHidden"> --}}
                                                <input type="checkbox" name="displayerrors"
                                                   class="custom-control-input" id="DisplayErrors"
                                                   {{ Cfg::get("DisplayErrors") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="LogErrors" value="off"
                                                   class="custom-control-input" id="LogErrorsHidden"> --}}
                                                <input type="checkbox" name="logerrors"
                                                   class="custom-control-input" id="LogErrors"
                                                   {{ Cfg::get("LogErrors") ? 'checked' : '' }}>
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
                                                {{-- <input type="hidden" name="SQLErrorReporting" value="off"
                                                   class="custom-control-input" id="SQLErrorReportingHidden"> --}}
                                                <input type="checkbox" name="sqlerrorreporting"
                                                   class="custom-control-input" id="SQLErrorReporting"
                                                   {{ Cfg::get("SQLErrorReporting") ? 'checked' : 'on' }}>
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
                                                {{-- <input type="hidden" name="HooksDebugMode" value="off"
                                                   class="custom-control-input" id="HooksDebugModeHidden"> --}}
                                                <input type="checkbox" name="hooksdebugmode"
                                                   class="custom-control-input" id="HooksDebugMode"
                                                   {{ Cfg::get("HooksDebugMode") ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="HooksDebugMode">Tick
                                                   to
                                                   enable logging of Hook Calls (Use only for
                                                   testing
                                                   purposes)</label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="d-flex justify-content-center mt-5">
                                 <input type="hidden" name="tab" value="{{Request::get('tab')}}">
                                 <button type="submit" id="saveChanges"
                                    class="btn btn-success mx-1 waves-effect">Save Changes
                                 </button>
                                 <button type="reset" class="btn btn-light mx-1">Cancel
                                    Changes</button>
                              </div>
                           </form>
                        </div>

                        <!-- Modal -->
                        @php
                           echo $aInt->modal("AddTrustedProxyIp", $aInt->lang("general", "addtrustedproxy"), "<table id=\"add-trusted-proxy-ip-table\"><tr><td>" . $aInt->lang("fields", "ipaddressorrange") . ":</td><td><input type=\"text\" id=\"ipaddress3\" class=\"form-control\" /></td></tr>" . "<tr><td></td><td>" . $aInt->lang("fields", "ipaddressorrangeinfo") . " <a href=\"https://docs.whmcs.com/Security_Tab#Trusted_Proxies\" target=\"_blank\">" . $aInt->lang("help", "contextlink") . "?</a></td></tr><tr><td>" . $aInt->lang("fields", "adminnotes") . ":</td><td><input type=\"text\" id=\"notes3\" class=\"form-control\" /></td></tr></table>", array(array("title" => $aInt->lang("general", "addip"), "onclick" => "addTrustedProxyIp(jQuery(\"#ipaddress3\").val(),jQuery(\"#notes3\").val());"), array("title" => $aInt->lang("", "cancel"))));
                           echo $aInt->modal("AddWhiteListIp", $aInt->lang("general", "addwhitelistedip"), "<table id=\"add-white-listed-ip-table\"><tr><td>" . $aInt->lang("fields", "ipaddress") . ":</td><td><input type=\"text\" id=\"ipaddress\" class=\"form-control\" /></td></tr>" . "<tr><td>" . $aInt->lang("fields", "reason") . ":</td><td><input type=\"text\" id=\"notes\" class=\"form-control\" />" . "</td></tr></table>", array(array("title" => $aInt->lang("general", "addip"), "onclick" => "addWhiteListedIp(jQuery(\"#ipaddress\").val(), jQuery(\"#notes\").val());"), array("title" => $aInt->lang("", "cancel"))), "small");
                           echo $aInt->modal("AddApiIp", $aInt->lang("general", "addwhitelistedip"), "<table><tr><td>" . $aInt->lang("fields", "ipaddress") . ":</td><td><input type=\"text\" id=\"ipaddress2\" class=\"form-control\" /></td></tr>" . "<tr><td>" . $aInt->lang("fields", "notes") . ":</td><td><input type=\"text\" id=\"notes2\" class=\"form-control\" />" . "</td></tr></table>", array(array("title" => $aInt->lang("general", "addip"), "onclick" => "addApiIp(jQuery(\"#ipaddress2\").val(), jQuery(\"#notes2\").val());"), array("title" => $aInt->lang("", "cancel"))), "small");
                        @endphp

                        {{-- Modal For Add Whitelisted IP --}}
                        {{-- <div class="modal fade" id="whitelistIPModal" tabindex="-1"
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
                        </div> --}}

                        {{-- Modal For Add API Allowed IP --}}
                        {{-- <div class="modal fade" id="ApiAllowedIPs" tabindex="-1" aria-labelledby="ApiAllowedIPsLabel"
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
                        </div> --}}

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
   <script src="{{ Theme::asset('assets/js/active-general-tab.js') }}"></script>
   <script>
      $("#enableProformaInvoicing").click(function() {
         if ($("#enableProformaInvoicing").is(":checked")) {
            $("#sequentialpaidnumbering").prop("checked", true);
            $("#sequentialpaidnumbering").prop("disabled", true);
         } else {
            $("#sequentialpaidnumbering").prop("disabled", false);
         }
      });
      $("#saveChanges").click(function() {
         $("#sequentialpaidnumbering").prop("disabled", false);
      });

      $("#removewhitelistedip").click(function () {
         var removeip = $('#whitelistedips option:selected').text();
         $('#whitelistedips option:selected').remove();
         $.ajax({
            url: route('admin.pages.setup.generalsettings.general.index'),
            data: { action: "deletewhitelistip", removeip: removeip, "_token": "{{csrf_token()}}"},
         });
         return false;
      });
      function checkToDisplayAccessDeniedMessage(box, responseText)
      {
         var errorResponse;
         var errorResponseHtml;
         
            // Check if access was denied.  If so, load the error page.
         if (responseText.toLowerCase().indexOf("error-page") !== -1) {
                  // Create a jQuery object from the page's response,
            // so it can be traversed.
            errorResponse = jQuery("<div>", { html: responseText });

            // Remove the "Access Denied" <h1> tag.
            errorResponse.find("h1").remove();
            // Remove the "Go Back" button.
            errorResponse.find(".error-footer").remove();

            // Find the markup for the error page.
            errorResponseHtml = errorResponse.find("#contentarea")
                  .html();

            // Load the error page's markup.
            box.html(errorResponseHtml);
         }
      }

      $("#removetrustedproxyip").click(function () {
         var removeip = $('#trustedproxyips option:selected').text();
         $('#trustedproxyips option:selected').remove();
         $.ajax({
            url: route('admin.pages.setup.generalsettings.general.index'),
            data: { action: "deletetrustedproxyip", removeip: removeip, "_token": "{{csrf_token()}}"},
         });
         return false;
      });
      $("#removeapiip").click(function () {
         var removeip = $('#apiallowedips option:selected').text();
         $('#apiallowedips option:selected').remove();
         $.ajax({
            url: route('admin.pages.setup.generalsettings.general.index'),
            data: { action: "deleteapiip", removeip: removeip, "_token": "{{csrf_token()}}"},
         });
         return false;
      });
   </script>
   <script>
      function addTrustedProxyIp(ipaddress, note) {
         $.ajax({
            url: route('admin.pages.setup.generalsettings.general.index'),
            data: {
                  action: "addTrustedProxyIp",
                  ipaddress: ipaddress,
                  notes: note,
                  "_token": '{{csrf_token()}}',
            },
            success: function (data) {
                  if (data) {
                     alert(data);
                  } else {
                     $('#trustedproxyips').append('<option>' + ipaddress + ' - ' + note + '</option>');
                     $('#modalAddTrustedProxyIp').modal('hide');
                  }
            },
         });
         return false;
      }

      function addWhiteListedIp(ipaddress, note) {
         $('#whitelistedips').append('<option>' + ipaddress + ' - ' + note + '</option>');
         $.ajax({
            url: route('admin.pages.setup.generalsettings.general.index'),
            data: {
                  action: "addWhiteListIp",
                  ipaddress: ipaddress,
                  notes: note,
                  "_token": '{{csrf_token()}}',
            },
         });
         $('#modalAddWhiteListIp').modal('hide');
         return false;
      }

      function addApiIp(ipaddress, note) {
         $('#apiallowedips').append('<option>' + ipaddress + ' - ' + note + '</option>');
         $.ajax({
            url: route('admin.pages.setup.generalsettings.general.index'),
            data: {
                  action: "addApiIp",
                  ipaddress: ipaddress,
                  notes: note,
                  "_token": '{{csrf_token()}}',
            },
         });
         $('#modalAddApiIp').modal('hide');
         return false;
      }
   </script>
@endsection
