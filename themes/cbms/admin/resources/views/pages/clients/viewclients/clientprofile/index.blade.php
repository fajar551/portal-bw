@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Profile</title>
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

                            @include('includes.tabnavclient')
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3 min-vh-100 bg-white">
                                        <form action="{{ route("admin.pages.clients.viewclients.clientprofile.update") }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                            @csrf
                                            <input type="number" name="userid" value="{{ $clientsdetails["userid"] }}" hidden>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <label for="firstName" class="col-sm-3 col-form-label">First Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="firstname" class="form-control @error('firstname') is-invalid @enderror" id="firstname" value="{{ old('firstname', $clientsdetails["firstname"]) }}" placeholder="First Name" required autocomplete="off">
                                                            @error('firstname')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="lastName" class="col-sm-3 col-form-label">Last Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="lastname" class="form-control @error('lastname') is-invalid @enderror" id="lastName" value="{{ old('lastname', $clientsdetails["lastname"]) }}" placeholder="Last Name" required autocomplete="off">
                                                            @error('lastname')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="companyName" class="col-sm-3 col-form-label">Company Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="companyname" class="form-control @error('companyname') is-invalid @enderror" id="companyName" value="{{ old('companyname', $clientsdetails["companyname"]) }}" placeholder="Company Name" autocomplete="off">
                                                            @error('companyname')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="emailAddress" class="col-sm-3 col-form-label">Email Address</label>
                                                        <div class="col-sm-9">
                                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="emailAddress" value="{{ old('email', $clientsdetails["email"]) }}" placeholder="Email Address" required autocomplete="off">
                                                            @error('email')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="inputPassword" class="col-sm-3 col-form-label">Password</label>
                                                        <div class="col-sm-9">
                                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="inputPassword" placeholder="{{ $password }}">
                                                            @error('password')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="securityqid" class="col-sm-3 col-form-label">Security Question</label>
                                                        <div class="col-sm-9">
                                                            <select name="securityqid" class="form-control @error('securityqid') is-invalid @enderror" id="securityqid"> 
                                                                <option value="0">None</option>
                                                                @foreach ($questions as $question)
                                                                    <option value="{{ $question["id"] }}" @if (old('securityqid', $clientsdetails["securityqid"]) == $question["id"]) selected @endif>
                                                                        {{ $question["question"] }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('securityqid')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="securityqans" class="col-sm-3 col-form-label">Security Answer</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="securityqans" class="form-control @error('securityqans') is-invalid @enderror" id="securityqans" value="{{ old('securityqans', $clientsdetails["securityqans"]) }}" placeholder="Security Answer">
                                                            @error('securityqans')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="tax_id" class="col-sm-3 col-form-label">Tax ID</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id" value="{{ old('tax_id', $clientsdetails["tax_id"]) }}" placeholder="Tax ID">
                                                            @error('tax_id')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="address1" class="col-sm-3 col-form-label">Address 1</label>
                                                        <div class="col-sm-9">
                                                            <textarea type="textarea" name="address1" class="form-control @error('address1') is-invalid @enderror" id="address1" placeholder="Enter Address 1" rows="4" required autocomplete="off">{{ old('address1', $clientsdetails["address1"]) }}</textarea>
                                                            @error('address1')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="address2" class="col-sm-3 col-form-label">Address 2</label>
                                                        <div class="col-sm-9">
                                                            <textarea type="textarea" name="address2" class="form-control @error('address2') is-invalid @enderror" id="address2" placeholder="Enter Address 2 (if any)" rows="4" autocomplete="off">{{ old('address2', $clientsdetails["address2"]) }}</textarea>
                                                            @error('address2')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <label for="city" class="col-sm-3 col-form-label">City</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" id="city" value="{{ old('city', $clientsdetails["city"]) }}" placeholder="City" required autocomplete="off">
                                                            @error('city')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="state" class="col-sm-3 col-form-label">State/Region</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" id="state" value="{{ old('state', $clientsdetails["state"]) }}" required placeholder="State/Region">
                                                            @error('state')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="postCode" class="col-sm-3 col-form-label">Postcode</label>
                                                        <div class="col-sm-9">
                                                            <input type="number" name="postcode" class="form-control" id="postcode" min="0" max="999999999" step="1" value="{{ old('postcode', $clientsdetails["postcode"]) }}" placeholder="Postal Code">
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
                                                                    <option value="{{ $country["id"] }}" @if (old('country', $clientsdetails["country"]) == $country["id"]) selected @endif>
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
                                                            <input type="text" name="phonenumber" class="form-control @error('phonenumber') is-invalid @enderror" id="phonenumber" value="{{ old('phonenumber', $clientsdetails["phonenumber"]) }}" placeholder="Phone Number" required autocomplete="off">
                                                            {{-- <select class="form-control" name="phonenumber" id="phonenumber">
                                                                <option value="1">0896444141441</option>
                                                            </select> --}}
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paymentmethod" class="col-sm-3 col-form-label">Payment Method</label>
                                                        <div class="col-sm-9">
                                                            <select name="paymentmethod" class="form-control @error('paymentmethod') is-invalid @enderror" id="paymentmethod">
                                                                <option value="">{{ __("admin.clientschangedefault") }}</option>
                                                                @foreach ($paymentmethodlist as $paymentmethod)
                                                                    <option value="{{ $paymentmethod["gateway"] }}" @if (old('paymentmethod', $clientsdetails["defaultgateway"]) == $paymentmethod["gateway"]) selected @endif>
                                                                        {{ $paymentmethod["value"] }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('paymentmethod')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="billingcid" class="col-sm-3 col-form-label">Billing Contact</label>
                                                        <div class="col-sm-9">
                                                            <select name="billingcid" class="form-control @error('billingcid') is-invalid @enderror" id="billingcid">
                                                                <option value="0">{{ __("admin.default") }}</option>
                                                                @foreach ($billingcontacts as $contact)
                                                                    <option value="{{ $contact->id }}" @if (old('billingcid', $clientsdetails["billingcid"]) == $contact->id) selected @endif>
                                                                        {{ "{$contact->firstname} {$contact->lastname}" }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('billingcid')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="language" class="col-sm-3 col-form-label">Language</label>
                                                        <div class="col-sm-9">
                                                            <select name="language" class="form-control @error('language') is-invalid @enderror" id="language" required>
                                                                <option value="0">{{ __("admin.default") }}</option>
                                                                @foreach ($languages as $key => $language)
                                                                    <option value="{{ $key }}" @if($key == old('language', $clientsdetails["language"])) selected @endif>
                                                                        {{ $language["name"] }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('language')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="clientstatus" class="col-sm-3 col-form-label">Status</label>
                                                        <div class="col-sm-9">
                                                            <select name="clientstatus" class="form-control @error('clientstatus') is-invalid @enderror" id="clientstatus">
                                                                <option value="Active" @if(old('clientstatus', $clientsdetails["status"]) == "Active") selected @endif>Active</option>
                                                                <option value="Inactive" @if(old('clientstatus', $clientsdetails["status"]) == "Inactive") selected @endif>Inactive</option>
                                                                <option value="Closed" @if(old('clientstatus', $clientsdetails["status"]) == "Closed") selected @endif>Closed</option>
                                                            </select>
                                                            @error('clientstatus')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="currency" class="col-sm-3 col-form-label">Currecy</label>
                                                        <div class="col-sm-9">
                                                            <select name="currency" class="form-control @error('currency') is-invalid @enderror" id="currency">
                                                                @foreach ($currencies as $currency)
                                                                    <option value="{{ $currency->id }}" @if (old('currency', $clientsdetails["currency"]) == $currency->id) selected @endif>
                                                                        {{ $currency->code }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('currency')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="clientGroup" class="col-sm-3 col-form-label">Client Group</label>
                                                        <div class="col-sm-9">
                                                            <select name="groupid" class="form-control @error('groupid') is-invalid @enderror" id="groupid">
                                                                <option value="0">{{ __("admin.none") }}</option>
                                                                @foreach ($clientgroups as $group)
                                                                    <option value="{{ $group["id"] }}" @if (old('groupid', $clientsdetails["groupid"]) == $group["id"]) selected @endif style="background-color:{{ $group["colour"] }};">
                                                                        {{ $group["name"] }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('groupid')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">Late Fees</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="latefeeoveride" class="form-check-input @error('latefeeoveride') is-invalid @enderror" id="latefeeoveride" value="1"
                                                                @if ($clientsdetails["latefeeoveride"] === "on" || $clientsdetails["latefeeoveride"] == 1 || old('latefeeoveride')) checked @endif>
                                                                <label class="form-check-label" for="latefeeoveride"> Don't Apply Late Fees</label>
                                                            </div>
                                                            @error('latefeeoveride')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">Overdue Notices</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="overideduenotices" class="form-check-input @error('overideduenotices') is-invalid @enderror" id="overideduenotices" value="1"
                                                                @if ($clientsdetails["overideduenotices"] === "on" || $clientsdetails["overideduenotices"] == 1 || old('overideduenotices')) checked @endif>
                                                                <label class="form-check-label" for="overideduenotices"> Don't Send Overdue Emails</label>
                                                            </div>
                                                            @error('overideduenotices')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">Tax Exempt</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="taxexempt" class="form-check-input @error('taxexempt') is-invalid @enderror" id="taxexempt" value="1"
                                                                @if ($clientsdetails["taxexempt"] === "on" || $clientsdetails["taxexempt"] == 1 || old('taxexempt')) checked @endif>
                                                                <label class="form-check-label" for="taxexempt"> Don't Apply Tax to Invoices</label>
                                                            </div>
                                                            @error('taxexempt')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">Separate Invoice</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="separateinvoices" class="form-check-input @error('separateinvoices') is-invalid @enderror" id="separateinvoices" value="1"
                                                                @if ($clientsdetails["separateinvoices"] === "on" || $clientsdetails["separateinvoices"] == 1 || old('separateinvoices')) checked @endif>
                                                                <label class="form-check-label" for="separateinvoices"> Separate Invoices for Services</label>
                                                            </div>
                                                            @error('separateinvoices')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">Disable CC Processing</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="disableautocc" class="form-check-input @error('disableautocc') is-invalid @enderror" id="disableautocc" value="1"
                                                                @if ($clientsdetails["disableautocc"] === "on" || $clientsdetails["disableautocc"] == 1 || old('disableautocc')) checked @endif>
                                                                <label class="form-check-label" for="disableautocc"> Disable Automatic CC Processing</label>
                                                            </div>
                                                            @error('disableautocc')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">Marketing Emails Opt-in</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="marketing_emails_opt_in" class="form-check-input @error('marketing_emails_opt_in') is-invalid @enderror" id="marketing_emails_opt_in" value="1" {{ old('marketing_emails_opt_in', $marketingEmailsOptIn) ? "checked" : "" }}>
                                                                <label class="form-check-label" for="marketing_emails_opt_in"> Send Client Marketing Emails</label>
                                                            </div>
                                                            @error('marketing_emails_opt_in')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">Status Update</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="overrideautoclose" class="form-check-input @error('overrideautoclose') is-invalid @enderror" id="overrideautoclose" value="1"
                                                                @if (old('overrideautoclose', $clientsdetails["overrideautoclose"] == 1)) checked @endif>
                                                                <label class="form-check-label" for="overrideautoclose"> Disable Automatic Status Update</label>
                                                            </div>
                                                            @error('overrideautoclose')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">Allow Single Sign-On</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="allow_sso" class="form-check-input @error('allow_sso') is-invalid @enderror" id="allow_sso" value="1"
                                                                @if (old('allow_sso', $clientsdetails["allowSingleSignOn"] == 1)) checked @endif>
                                                                <label class="form-check-label" for="allow_sso"> Tick to allow Single Sign-On</label>
                                                            </div>
                                                            @error('allow_sso')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    {{-- TODO --}}
                                                    @if (!\App\Helpers\Cfg::get("DisableClientEmailPreferences"))
                                                    <div class="form-group row">
                                                        <div class="col-sm-3 align-self-center">Email Notification</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="email_preferences[general]" class="form-check-input @error('email_preferences.general') is-invalid @enderror" id="email_preferences_general" value="1" 
                                                                @if ((isset($emailPref->general) && $emailPref->general == 1) || old('email_preferences.general')) checked @endif>
                                                                <label class="form-check-label" for="email_preferences_general"> General Emails - All account related emails</label>
                                                                @error('email_preferences.general')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="email_preferences[invoice]" class="form-check-input @error('email_preferences.invoice') is-invalid @enderror" id="email_preferences_invoice" value="1" 
                                                                @if ((isset($emailPref->invoice) && $emailPref->invoice == 1) || old('email_preferences.invoice')) checked @endif>
                                                                <label class="form-check-label" for="email_preferences_invoice"> Invoice Emails - New Invoices, Reminders, & Overdue Notices</label>
                                                                @error('email_preferences.invoice')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="email_preferences[support]" class="form-check-input @error('email_preferences.support') is-invalid @enderror" id="email_preferences_support" value="1" 
                                                                @if ((isset($emailPref->support) && $emailPref->support == 1) || old('email_preferences.support')) checked @endif>
                                                                <label class="form-check-label" for="email_preferences_support"> Support Emails - Receive a copy of all Support Ticket Communications</label>
                                                                @error('email_preferences.support')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="email_preferences[product]" class="form-check-input @error('email_preferences.product') is-invalid @enderror" id="email_preferences_product" value="1" 
                                                                @if ((isset($emailPref->product) && $emailPref->product == 1) || old('email_preferences.product')) checked @endif>
                                                                <label class="form-check-label" for="email_preferences_product"> Product Emails - Welcome Emails, Suspensions & Other Lifecycle Notifications</label>
                                                                @error('email_preferences.product')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="email_preferences[domain]" class="form-check-input @error('email_preferences.domain') is-invalid @enderror" id="email_preferences_domain" value="1" 
                                                                @if ((isset($emailPref->domain) && $emailPref->domain == 1) || old('email_preferences.domain')) checked @endif>
                                                                <label class="form-check-label" for="email_preferences_domain"> Domain Emails - Registration/Transfer Confirmation & Renewal Notices</label>
                                                                @error('email_preferences.domain')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="email_preferences[affiliate]" class="form-check-input @error('email_preferences.affiliate') is-invalid @enderror" id="email_preferences_affiliate" value="1" 
                                                                @if ((isset($emailPref->affiliate) && $emailPref->affiliate == 1) || old('email_preferences.affiliate')) checked @endif>
                                                                <label class="form-check-label" for="email_preferences_affiliate"> Affiliate Emails - Receive Affiliate Notifications</label>
                                                                @error('email_preferences.affiliate')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @foreach ($customfields as $customfield)
                                                    <div class="form-group row">
                                                        <label for="#" class="col-sm-3 col-form-label">{!! $customfield["name"] !!}</label>
                                                        <div class="col-sm-9">
                                                            {!! $customfield["input"] !!}
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                    <div class="form-group row">
                                                        <label for="whatsAppNumber" class="col-sm-3 col-form-label">Admin Notes</label>
                                                        <div class="col-sm-9">
                                                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" id="adminNotes" cols="30" rows="10">{{ old('notes', $clientsdetails["notes"]) }}</textarea>
                                                            @error('notes')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">Two-Factor Authentication</div>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="twofaenabled" class="form-check-input @error('twofaenabled') is-invalid @enderror" id="twofaenabled" value="1"
                                                                @if (old('twofaenabled', $clientsdetails["twofaenabled"])) checked @endif>
                                                                <label class="form-check-label" for="twofaenabled"> Enabled - Uncheck to disable</label>
                                                                @error('twofaenabled')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-lg-12 d-flex justify-content-center">
                                                    <button type="submit" class="btn btn-success px-3 mr-2">Save Changes</button>
                                                    <button type="reset" class="btn btn-light">Cancel Changes</button>
                                                </div>
                                            </div>
                                        </form>
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
    <!-- Bootstrap default validation -->
    <script src="{{ Theme::asset('assets/js/pages/form-validation.init.js') }}"></script>
    @stack('clientsearch')
@endsection
