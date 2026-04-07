@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Fraud Protection</title>
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
                                        <h4 class="mb-3">Fraud Protection</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p>This is where you configure the fraud protection module you wish to use. You may
                                            only have one fraud module enabled at a time.</p>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                                        Choose Fraud Module:
                                                    </label>
                                                    <div class="col-sm-12 col-lg-3 d-inline-flex">
                                                        <select name="" id="fraudOpt" class="form-control d-inline mr-2">
                                                            <option value="0">Choose Module</option>
                                                            <option value="1">FraudLabs</option>
                                                            <option value="2">MaxMind</option>
                                                        </select>
                                                        <button class="btn btn-success px-3 d-inline">Go</button>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="collapse" id="fraudLabs">
                                                            <div class="form-group row">
                                                                <label for=""
                                                                    class="col-sm-12 col-lg-2 col-form-label">Enable
                                                                    FraudLabs Pro</label>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input"
                                                                            id="enableFraudLabs">
                                                                        <label class="custom-control-label"
                                                                            for="enableFraudLabs">Tick to enable FraudLabs
                                                                            Pro
                                                                            Fraud Checking for Orders</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for=""
                                                                    class="col-sm-12 col-lg-2 col-form-label">FraudLabs Pro
                                                                    License Key</label>
                                                                <div class="col-sm-12 col-lg-5">
                                                                    <input type="text" class="form-control"
                                                                        id="fraudLicenseKey">
                                                                </div>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <p>Don't have an account? Click here to <a href="#">sign
                                                                            up »</a></p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                                                    FraudLabs Pro Fraud Risk Score
                                                                </label>
                                                                <div class="col-sm-12 col-lg-1">
                                                                    <input type="text" class="form-control">
                                                                </div>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <p>Higher than this value and the order will be blocked
                                                                        (1 -> 100)</p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for=""
                                                                    class="col-sm-12 col-lg-2 col-form-label">Reject Free
                                                                    Email Service</label>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input"
                                                                            id="rejectFreeMail">
                                                                        <label class="custom-control-label"
                                                                            for="rejectFreeMail">Block orders from free
                                                                            email addresses such as Hotmail & Yahoo!</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for=""
                                                                    class="col-sm-12 col-lg-2 col-form-label">Reject Country
                                                                    Mismatch</label>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input"
                                                                            id="rejectCountry">
                                                                        <label class="custom-control-label"
                                                                            for="rejectCountry">Block orders where order
                                                                            address is different from IP Location</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for=""
                                                                    class="col-sm-12 col-lg-2 col-form-label">Reject
                                                                    Anonymous Networks</label>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input"
                                                                            id="rejectAnon">
                                                                        <label class="custom-control-label"
                                                                            for="rejectAnon">Block orders where the user is
                                                                            ordering through an anonymous network</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for=""
                                                                    class="col-sm-12 col-lg-2 col-form-label">Reject High
                                                                    Risk Country</label>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input"
                                                                            id="rejectHighRisk">
                                                                        <label class="custom-control-label"
                                                                            for="rejectHighRisk">Block orders from high risk
                                                                            countries</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="text-center">
                                                                <button class="btn btn-success px-2">Save Changes</button>
                                                                <button class="btn btn-light px-2">Cancel Changes</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="collapse" id="maxMind">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">Enable
                                                                            MaxMind</label>
                                                                        <div class="col-sm-12 col-lg-10">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="custom-control-input"
                                                                                    id="enableMaxMind">
                                                                                <label class="custom-control-label"
                                                                                    for="enableMaxMind">Tick to enable
                                                                                    MaxMind Fraud Checking for
                                                                                    Orders</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">MaxMind
                                                                            User ID</label>
                                                                        <div class="col-sm-12 col-lg-5">
                                                                            <input type="text" class="form-control">
                                                                        </div>
                                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                                            <p>Don't have an account? Click here to sign up
                                                                                »</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">MaxMind
                                                                            License Key</label>
                                                                        <div class="col-sm-12 col-lg-10">
                                                                            <input type="text" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">Service
                                                                            Type</label>
                                                                        <div class="col-sm-12 col-lg-3">
                                                                            <select name="" id="" class="form-control">
                                                                                <option value="0">Insights</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                                            <p>Determines the level of checks that are
                                                                                performed. Default is Score. Learn more
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">MaxMind
                                                                            Fraud Risk Score</label>
                                                                        <div class="col-sm-12 col-lg-5">
                                                                            <input type="text" class="form-control"
                                                                                value="20">
                                                                        </div>
                                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                                            <p> Higher than this value and the order will be
                                                                                blocked (0.01 -> 99)</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label class="col-sm-12 col-lg-2 col-form-label">Do
                                                                            Not Validate Address Information</label>
                                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="custom-control-input"
                                                                                    id="noValidateAddress">
                                                                                <label class="custom-control-label"
                                                                                    for="noValidateAddress">Tick to ignore
                                                                                    warnings related to address information
                                                                                    validation failing.</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">Reject
                                                                            Free Email Service</label>
                                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="custom-control-input"
                                                                                    id="rejectEmailMaxmind">
                                                                                <label class="custom-control-label"
                                                                                    for="rejectEmailMaxmind">Block orders
                                                                                    from
                                                                                    free email addresses such as Hotmail &
                                                                                    Yahoo!*</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">Reject
                                                                            Country Mismatch</label>
                                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="custom-control-input"
                                                                                    id="rejectCountryMaxmind">
                                                                                <label class="custom-control-label"
                                                                                    for="rejectCountryMaxmind">Block orders
                                                                                    where order address is different from IP
                                                                                    Location*</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">Reject
                                                                            Anonymous Networks</label>
                                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="custom-control-input"
                                                                                    id="rejectAnonMaxmind">
                                                                                <label class="custom-control-label"
                                                                                    for="rejectAnonMaxmind">Block orders
                                                                                    where the user is ordering through an
                                                                                    anonymous network*</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">Reject
                                                                            High Risk Country</label>
                                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="custom-control-input"
                                                                                    id="RejectHighRiskCountryMaxmind">
                                                                                <label class="custom-control-label"
                                                                                    for="RejectHighRiskCountryMaxmind">Block
                                                                                    orders from high risk countries*</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group row">
                                                                        <label
                                                                            class="col-sm-12 col-lg-2 col-form-label">Custom
                                                                            Rules</label>
                                                                        <div class="col-sm-12 col-lg-10">
                                                                            <p class="text-info">Additional rules can be
                                                                                created within your
                                                                                MaxMind account to apply automated fraud
                                                                                check filtering based on rules and criteria
                                                                                you define.
                                                                                For more information about custom rules,
                                                                                visit the <a href="#">MaxMind website</a>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="text-center">
                                                                <button class="btn btn-success px-2">Save Changes</button>
                                                                <button class="btn btn-light px-2">Cancel Changes</button>
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
                    </div>
                    <!-- End MAIN CARD -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ Theme::asset('assets/js/pages/fraud-protection.js') }}"></script>
@endsection
