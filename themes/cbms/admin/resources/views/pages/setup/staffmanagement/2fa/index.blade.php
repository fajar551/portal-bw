@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Two-Factor Authentication</title>
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
                                        <h4 class="mb-3">Two-Factor Authentication</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p>The following services are supported for Two-Factor Authentication. You may
                                            activate one or more of these.</p>
                                        <div class="row">
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card p-3">
                                                    <div class="card-body mx-auto">
                                                        <div class="img-container d-flex justify-content-center my-3">
                                                            <img src="https://proto.qwords.com/modules/security/duosecurity/logo.png"
                                                                alt="logo.png" class="" width="250">
                                                        </div>
                                                        <h4 class="card-title text-center">Duo Security</h4>
                                                        <p class="card-text text-center mb-3">Get codes via Duo Push, SMS or
                                                            Phone Callback.
                                                        </p>
                                                    </div>
                                                    <div class="p-3 mx-auto">
                                                        <button type="button" data-toggle="modal" data-target="#duoModal"
                                                            class="btn btn-success px-5">Activate</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="duoModal" tabindex="-1"
                                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="background-color: #252B3B">
                                                            <h5 class="modal-title text-white" id="exampleModalLabel">
                                                                Configure Duo
                                                                Security</h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>
                                                                Duo Security enables your users to secure their logins using
                                                                their smartphones. Authentication options include push
                                                                notifications, passcodes, text messages and/or phone calls.
                                                            </p>
                                                            <div class="alert alert-success" role="alert">
                                                                New to Duo Security? <strong>Click here to create an
                                                                    account</strong>
                                                            </div>
                                                            <h5 class="mb-3">Status</h5>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck3">
                                                                <label class="custom-control-label"
                                                                    for="customCheck3">Enable for use by Clients</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck4">
                                                                <label class="custom-control-label"
                                                                    for="customCheck4">Enable for use by Administrative
                                                                    Users</label>
                                                            </div>
                                                            <h5 class="my-3">Configuration Settings</h5>
                                                            <div class="form-group">
                                                                <label>Integration Key</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Secret Key</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>API Hostname</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light"
                                                                data-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary">Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card p-3">
                                                    <div class="card-body mx-auto">
                                                        <div class="img-container d-flex justify-content-center my-3">
                                                            <img src="https://proto.qwords.com/modules/security/totp/logo.png"
                                                                alt="logo.png" class="" width="auto">
                                                        </div>
                                                        <h4 class="card-title text-center">Time Based Tokens</h4>
                                                        <p class="card-text text-center mb-3">Get codes from an app like
                                                            Google Authenticator or Duo.
                                                        </p>
                                                    </div>
                                                    <div class="p-3 mx-auto">
                                                        <button type="button" data-toggle="modal" data-target="#tokenModal"
                                                            class="btn btn-light px-5">Configure</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="tokenModal" tabindex="-1"
                                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="background-color: #252B3B">
                                                            <h5 class="modal-title text-white" id="exampleModalLabel">
                                                                Configure Time Based Tokens</h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>
                                                                TOTP requires that a user enter a 6 digit code that changes
                                                                every 30 seconds to complete login. This works with mobile
                                                                apps such as OATH Token and Google Authenticator.
                                                            </p>

                                                            <h5 class="mb-3">Status</h5>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck5">
                                                                <label class="custom-control-label"
                                                                    for="customCheck5">Enable for use by Clients</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck6">
                                                                <label class="custom-control-label"
                                                                    for="customCheck6">Enable for use by Administrative
                                                                    Users</label>
                                                            </div>
                                                            <h5 class="my-3">Configuration Settings</h5>
                                                            <small>No configuration requiered.</small>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light"
                                                                data-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary">Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card p-3">
                                                    <div class="card-body mx-auto">
                                                        <div class="img-container d-flex justify-content-center my-3">
                                                            <img src="https://proto.qwords.com/modules/security/yubico/logo.png"
                                                                alt="logo.png" class="" width="300px">
                                                        </div>
                                                        <h4 class="card-title text-center">Yubico</h4>
                                                        <p class="card-text text-center mb-3">Generate codes using a YubiKey
                                                            hardware device.
                                                        </p>
                                                    </div>
                                                    <div class="p-3 mx-auto">
                                                        <button type="button" data-toggle="modal" data-target="#yubicoModal"
                                                            class="btn btn-success px-5">Activate</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="yubicoModal" tabindex="-1"
                                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="background-color: #252B3B">
                                                            <h5 class="modal-title text-white" id="exampleModalLabel">
                                                                Configure Yubico</h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>
                                                                Yubico is a hardware based solution which requires each of
                                                                your users to use a YubiKey to authenticate and complete the
                                                                login process.
                                                            </p>

                                                            <h5 class="mb-3">Status</h5>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck7">
                                                                <label class="custom-control-label"
                                                                    for="customCheck7">Enable for use by Clients</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck8">
                                                                <label class="custom-control-label"
                                                                    for="customCheck8">Enable for use by Administrative
                                                                    Users</label>
                                                            </div>
                                                            <h5 class="my-3">Configuration Settings</h5>
                                                            <div class="form-group">
                                                                <label>Client ID</label>
                                                                <div>
                                                                    <input type="text" class="form-control d-inline w-25">
                                                                    <p class="d-inline">Setup Your YubiKey if you haven't
                                                                        already @</p>
                                                                </div>
                                                                <small>https://upgrade.yubico.com/getapikey/</small>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Secret Key</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light"
                                                                data-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary">Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h6>What is Two-Factor Authentication?</h6>
                                                <p>Two-factor authentication adds an additional layer of security by adding
                                                    a second step to the login process. It takes something you know (ie.
                                                    your password) and adds a second factor, typically something you have
                                                    (such as your phone). Since both are required to log in, the threat of a
                                                    leaked password is lessened.</p>
                                                <p>
                                                    One of the most common and simplest forms of Two-Factor Authentication
                                                    is Time Based Tokens. With Time Based Tokens, in addition to your
                                                    regular username & password, you also have to enter a 6 digit code that
                                                    re-generates every 30 seconds. Only your token device (typically a
                                                    mobile smartphone app) will know your secret key and be able to generate
                                                    valid one time passwords for your account. <strong><em>We recommend
                                                            enabling Time Based Tokens (also enabled by
                                                            default).</em></strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="border border-light bg-light rounded p-3">
                                                    <h6 class="mb-3">Global Two-Factor Authentication Settings</h6>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="customCheck1">
                                                        <label class="custom-control-label" for="customCheck1">Force Client
                                                            Users to enable Two Factor Authentication on Next Login</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="customCheck2">
                                                        <label class="custom-control-label" for="customCheck2">Force
                                                            Administrative Users to enable Two Factor Authentication on Next
                                                            Login</label>
                                                    </div>
                                                    <button class="btn btn-outline-dark px-3 mt-3">Save Changes</button>
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
