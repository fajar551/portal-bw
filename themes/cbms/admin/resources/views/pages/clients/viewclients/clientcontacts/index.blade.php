@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Contacts</title>
@endsection

@section('styles')
    <!-- Sweetalert2 -->
    <link href="{{ Theme::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                     
                    <div class="col-xl-12">
                        <div class="client-summary-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Client Profile</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    @if (session('message'))
                                        <div class="alert alert-{{ session('type') }}">
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                            <strong>{!! session('message') !!}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Row client select --}}
                            @include('includes.clientsearch')

                            {{-- Tab Nav --}}
                            @include('includes.tabnavclient')
                            <div class="row">
                                <div class="col-lg-12">
                                    @if ($addnew)
                                    <div class="card p-3 min-vh-100 bg-white">
                                        <form>
                                            <div class="form-group row">
                                                <label for="currency" class="col-sm-2 col-form-label">Add New Contact: </label>
                                                <div class="col-sm-2">
                                                    {{-- <select class="form-control" name="currency" id="currency">
                                                        <option value="1">Add New</option>
                                                    </select> --}}
                                                </div>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ route("admin.pages.clients.viewclients.clientcontacts.create") }}" enctype="multipart/form-data" id="form-contact" class="needs-validation" novalidate>
                                            @csrf
                                            <input type="number" name="userid" value="{{ $clientsdetails["userid"] }}" hidden>
                                            <div class="rounded border p-3 mb-3">
                                                <div class="row flex-wrap">
                                                    <div class="col-lg-6">
                                                        <div class="form-group row">
                                                            <label for="firstName" class="col-sm-3 col-form-label">First Name</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="firstname" class="form-control @error('firstname') is-invalid @enderror" id="firstname" value="{{ old("firstname") }}" placeholder="First Name" required autocomplete="off">
                                                                @error('firstname')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="lastName" class="col-sm-3 col-form-label">Last Name</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="lastname" class="form-control @error('lastname') is-invalid @enderror" id="lastName" value="{{ old("lastname") }}" placeholder="Last Name" required autocomplete="off">
                                                                @error('lastname')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="companyName" class="col-sm-3 col-form-label">Company Name</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="companyname" class="form-control @error('companyname') is-invalid @enderror" id="companyName" value="{{ old("companyname") }}" placeholder="Company Name" required autocomplete="off">
                                                                @error('companyname')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="emailAddress" class="col-sm-3 col-form-label">Email Address</label>
                                                            <div class="col-sm-9">
                                                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="emailAddress" value="{{ old("email") }}" placeholder="Email Address" required autocomplete="off">
                                                                @error('email')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-3">Activate Sub-Account</div>
                                                            <div class="col-sm-9">
                                                                <div class="form-check">
                                                                    <input type="checkbox" name="subaccount" class="form-check-input @error('subaccount') is-invalid @enderror" id="subaccount" value="1"
                                                                    @if (old('subaccount')) checked @endif>
                                                                    <label class="form-check-label" for="subaccount"> Tick to Enable</label>
                                                                </div>
                                                                @error('subaccount')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="inputPassword" class="col-sm-3 col-form-label">Password</label>
                                                            <div class="col-sm-9">
                                                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="inputPassword" placeholder="Enter Password"
                                                                @if (old('password')) required @endif>
                                                                @error('password')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group row">
                                                            <label for="address1" class="col-sm-3 col-form-label">Address 1</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="address1" class="form-control @error('address1') is-invalid @enderror" id="address1" value="{{ old("address1") }}" placeholder="Enter Address 1" required autocomplete="off">
                                                                @error('address1')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="address2" class="col-sm-3 col-form-label">Address 2</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="address2" class="form-control @error('address2') is-invalid @enderror" id="address2" value="{{ old("address2") }}" placeholder="Enter Address 2 (if any)" autocomplete="off">
                                                                @error('address2')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="city" class="col-sm-3 col-form-label">City</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" id="city" value="{{ old("city") }}" placeholder="City" autocomplete="off">
                                                                @error('city')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="state" class="col-sm-3 col-form-label">State/Region</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" id="state" value="{{ old("state") }}" placeholder="State/Region">
                                                                @error('state')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="postCode" class="col-sm-3 col-form-label">Postcode</label>
                                                            <div class="col-sm-9">
                                                                <input type="number" name="postcode" class="form-control @error('postcode') is-invalid @enderror" id="postcode" min="0" max="999999999" step="1" value="{{  old("postcode") }}" placeholder="Postal Code">
                                                                @error('postcode')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="country" class="col-sm-3 col-form-label">Country</label>
                                                            <div class="col-sm-9">
                                                                <select name="country" class="form-control @error('country') is-invalid @enderror" id="country" required>
                                                                    <option value="">Select Country</option>
                                                                    @foreach ($countries as $country)
                                                                    <option value="{{ $country["id"] }}" @if ($country["id"] == old("country")) selected @endif>{{ $country["name"] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('country')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="phoneNumber" class="col-sm-3 col-form-label">Phone Number</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="phonenumber" class="form-control @error('phonenumber') is-invalid @enderror" id="phonenumber" value="{{ old("phonenumber") }}" placeholder="Phone Number" autocomplete="off">
                                                                @error('phonenumber')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row">
                                                                    <div class="col-sm-2 align-self-center">Email Notification
                                                                    </div>
                                                                    <div class="col-sm-9">
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[general]" class="form-check-input @error('email_preferences.general') is-invalid @enderror" id="email_preferences_general" value="1" 
                                                                            @if (old('email_preferences.general')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_general"> General Emails - All account related emails</label>
                                                                            @error('email_preferences.general')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[invoice]" class="form-check-input @error('email_preferences.invoice') is-invalid @enderror" id="email_preferences_invoice" value="1" 
                                                                            @if (old('email_preferences.invoice')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_invoice"> Invoice Emails - New Invoices, Reminders, & Overdue Notices</label>
                                                                            @error('email_preferences.invoice')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[support]" class="form-check-input @error('email_preferences.support') is-invalid @enderror" id="email_preferences_support" value="1" 
                                                                            @if (old('email_preferences.support')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_support"> Support Emails - Receive a copy of all Support Ticket Communications</label>
                                                                            @error('email_preferences.support')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[product]" class="form-check-input @error('email_preferences.product') is-invalid @enderror" id="email_preferences_product" value="1" 
                                                                            @if (old('email_preferences.product')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_product"> Product Emails - Welcome Emails, Suspensions & Other Lifecycle Notifications</label>
                                                                            @error('email_preferences.product')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <!--<div class="form-check">-->
                                                                        <!--    <input type="checkbox" name="email_preferences[domain]" class="form-check-input @error('email_preferences.domain') is-invalid @enderror" id="email_preferences_domain" value="1" -->
                                                                        <!--    @if (old('email_preferences.domain')) checked @endif>-->
                                                                        <!--    <label class="form-check-label" for="email_preferences_domain"> Domain Emails - Registration/Transfer Confirmation & Renewal Notices</label>-->
                                                                        <!--    @error('email_preferences.domain')-->
                                                                        <!--        <div class="text-danger" >{{ $message }}</div>-->
                                                                        <!--    @enderror-->
                                                                        <!--</div>-->
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[affiliate]" class="form-check-input @error('email_preferences.affiliate') is-invalid @enderror" id="email_preferences_affiliate" value="1" 
                                                                            @if (old('email_preferences.affiliate')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_affiliate"> Affiliate Emails - Receive Affiliate Notifications</label>
                                                                            @error('email_preferences.affiliate')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        {{-- @error('email_preferences.*')
                                                                            @foreach ($errors->get('email_preferences.*') as $message)
                                                                                <div class="text-danger" >{{ implode(",", $message) }}</div>
                                                                            @endforeach
                                                                        @enderror --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group row">
                                                            <label for="tax_id" class="col-sm-3 col-form-label">Tax ID</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id" value="{{ old("tax_id") }}" placeholder="Tax ID">
                                                                @error('tax_id')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="form-group row">
                                                            <label for="linkedSignIn" class="col-sm-2 col-form-label align-self-center">Linked Sign-In Accounts</label>
                                                            <div class="col-sm-10">
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                        <tr class="text-white table-head-primary-color">
                                                                            <th scope="col">Provider</th>
                                                                            <th scope="col">Name</th>
                                                                            <th scope="col">Emaill Address</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="3">
                                                                                <p>No record found</p>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row">
                                                                    <div class="col-sm-2 align-self-center">
                                                                        Pemissions
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        @foreach ($allPermissions as $key => $perm)
                                                                            <div class="form-check">
                                                                                <input type="checkbox" class="form-check-input @error('permissions.'.$perm) is-invalid @enderror" name="permissions[{{$perm}}]" id="permissions{{ $key }}" value="{{ $perm }}"
                                                                                @if (old('permissions.'.$perm)) checked @endif>
                                                                                <label class="form-check-label" for="permissions{{ $key }}">
                                                                                    {{ __("admin.contactpermissionsperm$perm") }}
                                                                                </label>
                                                                                @error('permissions.'.$perm)
                                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                                @enderror
                                                                            </div>
                                                                        @endforeach
                                                                        {{-- @error('permissions.*')
                                                                            @foreach ($errors->get('permissions.*') as $message)
                                                                                <div class="text-danger" >{{ implode(",", $message) }}</div>
                                                                            @endforeach
                                                                        @enderror --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12 d-flex justify-content-center">
                                                    <button type="submit" class="btn btn-success px-3 mr-2">Add Contact</button>
                                                    <button type="reset" class="btn btn-light">Reset Changes</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    @else
                                    <div class="card p-3 min-vh-100 bg-white">
                                        <form>
                                            <div class="form-group row">
                                                <label for="currency" class="col-sm-2 col-form-label">Update Contact: </label>
                                                <div class="col-sm-2">
                                                    {{-- <select class="form-control" name="currency" id="currency">
                                                        <option value="1">Add New</option>
                                                    </select> --}}
                                                </div>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ route("admin.pages.clients.viewclients.clientcontacts.update") }}" enctype="multipart/form-data" id="form-contact" class="needs-validation" novalidate>
                                            @csrf
                                            <input type="number" name="userid" value="{{ $clientsdetails["userid"] }}" hidden>
                                            <input type="number" name="contactid" value="{{ $contact->id }}" hidden>
                                            <div class="rounded border p-3 mb-3">
                                                <div class="row flex-wrap">
                                                    <div class="col-lg-6">
                                                        <div class="form-group row">
                                                            <label for="firstName" class="col-sm-3 col-form-label">First Name</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="firstname" class="form-control @error('firstname') is-invalid @enderror" id="firstname" value="{{ old("firstname") ?? $contact->firstname }}" placeholder="First Name" required autocomplete="off">
                                                                @error('firstname')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="lastName" class="col-sm-3 col-form-label">Last Name</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="lastname" class="form-control @error('lastname') is-invalid @enderror" id="lastName" value="{{ old("lastname") ?? $contact->lastname }}" placeholder="Last Name" required autocomplete="off">
                                                                @error('lastname')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="companyName" class="col-sm-3 col-form-label">Company Name</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="companyname" class="form-control @error('companyname') is-invalid @enderror" id="companyName" value="{{ old("companyname") ?? $contact->companyname }}" placeholder="Company Name" required autocomplete="off">
                                                                @error('companyname')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="emailAddress" class="col-sm-3 col-form-label">Email Address</label>
                                                            <div class="col-sm-9">
                                                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="emailAddress" value="{{ old("email") ?? $contact->email }}" placeholder="Email Address" required autocomplete="off">
                                                                @error('email')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-3">Activate Sub-Account</div>
                                                            <div class="col-sm-9">
                                                                <div class="form-check">
                                                                    <input name="subaccount" class="form-check-input @error('subaccount') is-invalid @enderror" id="subaccount" type="checkbox" value="1" 
                                                                    @if ($contact->subaccount || old('subaccount')) checked @endif>
                                                                    <label class="form-check-label" for="subaccount"> Tick to Enable</label>
                                                                </div>
                                                                @error('subaccount')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="inputPassword" class="col-sm-3 col-form-label">Password</label>
                                                            <div class="col-sm-9">
                                                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="inputPassword" placeholder="Enter Password">
                                                                @error('password')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group row">
                                                            <label for="address1" class="col-sm-3 col-form-label">Address 1</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="address1" class="form-control @error('address1') is-invalid @enderror" id="address1" value="{{ old('address1') ?? $contact->address1 }}" placeholder="Enter Address 1" required autocomplete="off">
                                                                @error('address1')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="address2" class="col-sm-3 col-form-label">Address 2</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="address2" class="form-control @error('address2') is-invalid @enderror" id="address2" value="{{ old('address2') ?? $contact->address2 }}" placeholder="Enter Address 2 (if any)" autocomplete="off">
                                                                @error('address2')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="city" class="col-sm-3 col-form-label">City</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" id="city" value="{{ old('city') ?? $contact->city }}" placeholder="City" autocomplete="off">
                                                                @error('city')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="state" class="col-sm-3 col-form-label">State/Region</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" id="state" value="{{ old('state') ?? $contact->state }}" placeholder="State/Region">
                                                                @error('state')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="postCode" class="col-sm-3 col-form-label">Postcode</label>
                                                            <div class="col-sm-9">
                                                                <input type="number" name="postcode" class="form-control @error('postcode') is-invalid @enderror" id="postcode" min="0" max="999999999" step="1" value="{{ old('postcode') ?? $contact->postcode }}" placeholder="Postal Code">
                                                                @error('postcode')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="country" class="col-sm-3 col-form-label">Country</label>
                                                            <div class="col-sm-9">
                                                                <select name="country" class="form-control @error('country') is-invalid @enderror" id="country" required>
                                                                    <option value="">Select Country</option>
                                                                    @foreach ($countries as $country)
                                                                        <option value="{{ $country["id"] }}"
                                                                            @if (old('country'))
                                                                                @if($country["id"] == old('country')) selected @endif
                                                                            @else 
                                                                                @if($country["id"] == $contact->country) selected @endif
                                                                            @endif>
                                                                            {{ $country["name"] }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('country')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="phoneNumber" class="col-sm-3 col-form-label">Phone Number</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="phonenumber" class="form-control @error('phonenumber') is-invalid @enderror" id="phonenumber" value="{{ old('phonenumber') ?? $contact->phonenumber }}" placeholder="Phone Number" autocomplete="off">
                                                                @error('phonenumber')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row">
                                                                    <div class="col-sm-2 align-self-center">Email Notification
                                                                    </div>
                                                                    <div class="col-sm-9">
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[general]" class="form-check-input @error('email_preferences.general') is-invalid @enderror" id="email_preferences_general" value="1" 
                                                                            @if ($contact->generalemails || old('email_preferences.general')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_general"> General Emails - All account related emails</label>
                                                                            @error('email_preferences.general')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[invoice]" class="form-check-input @error('email_preferences.invoice') is-invalid @enderror" id="email_preferences_invoice" value="1" 
                                                                            @if ($contact->invoiceemails || old('email_preferences.invoice')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_invoice"> Invoice Emails - New Invoices, Reminders, & Overdue Notices</label>
                                                                            @error('email_preferences.invoice')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[support]" class="form-check-input @error('email_preferences.support') is-invalid @enderror" id="email_preferences_support" value="1" 
                                                                            @if ($contact->supportemails || old('email_preferences.support')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_support"> Support Emails - Receive a copy of all Support Ticket Communications</label>
                                                                            @error('email_preferences.support')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[product]" class="form-check-input @error('email_preferences.product') is-invalid @enderror" id="email_preferences_product" value="1" 
                                                                            @if ($contact->productemails || old('email_preferences.product')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_product"> Product Emails - Welcome Emails, Suspensions & Other Lifecycle Notifications</label>
                                                                            @error('email_preferences.product')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <!--<div class="form-check">-->
                                                                        <!--    <input type="checkbox" name="email_preferences[domain]" class="form-check-input @error('email_preferences.domain') is-invalid @enderror" id="email_preferences_domain" value="1" -->
                                                                        <!--    @if ($contact->domainemails || old('email_preferences.domain')) checked @endif>-->
                                                                        <!--    <label class="form-check-label" for="email_preferences_domain"> Domain Emails - Registration/Transfer Confirmation & Renewal Notices</label>-->
                                                                        <!--    @error('email_preferences.domain')-->
                                                                        <!--        <div class="text-danger" >{{ $message }}</div>-->
                                                                        <!--    @enderror-->
                                                                        <!--</div>-->
                                                                        <div class="form-check">
                                                                            <input type="checkbox" name="email_preferences[affiliate]" class="form-check-input @error('email_preferences.affiliate') is-invalid @enderror" id="email_preferences_affiliate" value="1" 
                                                                            @if ($contact->affiliateemails || old('email_preferences.affiliate')) checked @endif>
                                                                            <label class="form-check-label" for="email_preferences_affiliate"> Affiliate Emails - Receive Affiliate Notifications</label>
                                                                            @error('email_preferences.affiliate')
                                                                                <div class="text-danger" >{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        {{-- @error('email_preferences.*')
                                                                            @foreach ($errors->get('email_preferences.*') as $message)
                                                                                <div class="text-danger" >{{ implode(",", $message) }}</div>
                                                                            @endforeach
                                                                        @enderror --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group row">
                                                            <label for="tax_id" class="col-sm-3 col-form-label">Tax ID</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id" value="{{ $contact->tax_id }}" placeholder="Tax ID">
                                                                @error('tax_id')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="form-group row">
                                                            <label for="linkedSignIn" class="col-sm-2 col-form-label align-self-center">Linked Sign-In Accounts</label>
                                                            <div class="col-sm-10">
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                        <tr class="text-white table-head-primary-color">
                                                                            <th scope="col">Provider</th>
                                                                            <th scope="col">Name</th>
                                                                            <th scope="col">Emaill Address</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="3"><p>No record found</p></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row">
                                                                    <div class="col-sm-2 align-self-center">
                                                                        Pemissions
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        @foreach ($allPermissions as $key => $perm)
                                                                            <div class="form-check">
                                                                                <input type="checkbox" name="permissions[{{$perm}}]" class="form-check-input @error('permissions.'.$perm) is-invalid @enderror" id="permissions{{ $key }}" value="{{ $perm }}"
                                                                                @if (in_array($perm, $contact->permissions) || old('permissions.'.$perm)) checked @endif>
                                                                                <label class="form-check-label" for="permissions{{ $key }}">
                                                                                    {{ __("admin.contactpermissionsperm$perm") }}
                                                                                </label>
                                                                                @error('permissions.'.$perm)
                                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                                @enderror
                                                                            </div>
                                                                        @endforeach
                                                                        {{-- @error('permissions.*')
                                                                            @foreach ($errors->get('permissions.*') as $message)
                                                                                <div class="text-danger" >{{ implode(",", $message) }}</div>
                                                                            @endforeach
                                                                        @enderror --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12 d-flex justify-content-center">
                                                    <button type="submit" class="btn btn-success px-3 mr-2">Save Changes</button>
                                                    <button type="reset" class="btn btn-light">Reset Changes</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-lg-12">
                                    <div class="card p-3 border">
                                        <h4 class="card-title text-center mb-5">
                                            List of Available Contacts
                                        </h4>
                                        <form action="" method="POST" id="form-filters" enctype="multipart/form-data" onsubmit="return false" style="display: none">
                                            @csrf
                                            <input type="number" name="userid" value="{{ $clientsdetails["userid"] }}" hidden>
                                        </form>
                                        <div class="table-responisve">
                                            <table id="dt-client-contact" class="table table-bordered dt-responsive nowrap w-100">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">NO</th>
                                                        <th class="text-center">ID</th>
                                                        <th class="text-center">First Name</th>
                                                        <th class="text-center">Last Name</th>
                                                        <th class="text-center">Company Name</th>
                                                        <th class="text-center">Email Address</th>
                                                        <th class="text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
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
@endsection

@section('scripts')
     <!-- Required datatable js -->
     <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
     <!-- Buttons examples -->
     <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
     <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
     
     <!-- Responsive examples -->
     <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
     {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}
     <script src="{{ Theme::asset('assets/js/accordion-radio.js') }}"></script>
 
      <!-- Moment JS -->
      <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>
 
      <!-- JQuery Serialize Json -->
      <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>
 
     <!-- Sweetalert2 -->
     <script src="{{ Theme::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

     <!-- Bootstrap default validation -->
     <script src="{{ Theme::asset('assets/js/pages/form-validation.init.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    @stack('clientsearch')

     <script>

        // Datatable
        let dtTableContact;

        $(() => {
            
            dtClientContact();

            $('body').on('click', '.act-delete', function() {
                actionDeleteContact($(this).attr('data-id'));
            });

        });

        const dtClientContact = () => {
            dtTableContact = $('#dt-client-contact').DataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                serverSide: true,
                autoWidth: false,
                searching: false,
                destroy: true,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                },
                drawCallback: () => {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
                ajax: {
                    url: "{!! route('admin.pages.clients.viewclients.clientcontacts.dtClientContact') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'id', name: 'id', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'firstname', name: 'firstname', width: '10%', defaultContent: 'N/A', },
                    { data: 'lastname', name: 'lastname', width: '10%', defaultContent: 'N/A', },
                    { data: 'companyname', name: 'companyname', width: '10%', searchable: false, defaultContent: 'N/A', },
                    { data: 'email', name: 'email', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const actionDeleteContact = (id) => {
            const url = "{!! route('admin.pages.clients.clientcontacts.delete') !!}";
            const payloads = {
                id,
            };

            Swal.fire({
                title: "Are you sure?",
                html: `The <b>Data</b> will be deleted from database.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Delete!",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async (data) => {
                    options.method = 'DELETE';
                    options.body = JSON.stringify(payloads);

                    const response = await cbmsPost(url, options);
                    if (!response) {
                        const error = "An error occured.";
                        return Swal.showValidationMessage(`Request failed: ${error}`);
                    }

                    return response;
                },
            }).then((response) => {
                if (response.value) {
                    const { result, message } = response.value;

                    Toast.fire({ icon: result, title: message });
                    filterTable(null);
                }
            }).catch(swal.noop);
        }

        const filterTable = (form) => {
            
            dtTableContact.ajax.reload();

            return false;
        }

     </script>
@endsection
