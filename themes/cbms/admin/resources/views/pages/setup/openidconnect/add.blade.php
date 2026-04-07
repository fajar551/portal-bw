@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Create New Client API Credentials</title>
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
                                        <h4 class="mb-3">OpenID Connect</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <h4 class="card-title mb-3">Create New Client API Credentials</h4>
                                        <div class="form-group row">
                                            <label for="" class="col-sm-12 col-lg-2 col-form-label">Name</label>
                                            <div class="col-sm-12 col-lg-10">
                                                <input type="text" class="form-control" placeholder="Application Name">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Description</label>
                                            <div class="col-sm-12 col-lg-10">
                                                <input type="text" class="form-control"
                                                    placeholder="A description to help you identify this credential set">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Client API Credentials</label>
                                            <div class="col-sm-12 col-lg-10">
                                                <div class="alert alert-warning" role="alert">
                                                    <i class="fa fa-exclamation-triangle mr-2" aria-hidden="true"></i>
                                                    Client API Credentials will be generated upon first save.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Logo URL</label>
                                            <div class="col-sm-12 col-lg-10">
                                                <input type=" text" class="form-control"
                                                    placeholder="eg. /path/to/logo.png">
                                                <p>URL or Path Relative to the Base WHMCS Client Area Directory to a logo
                                                    image file for this application.</p>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-12 col-lg-2 col-form-label">Authorized Redirect
                                                URIs</label>
                                            <div class="col-sm-12 col-lg-10 border rounded p-2">
                                                <div class="form-auth-redirect" id="input-redirect">
                                                    <input type="text" class="mb-2 w-75 form-control d-inline"
                                                        placeholder="http://www.example.com/oauth">
                                                </div>
                                                <p>Must have a protocol. Cannot contain URL fragments or relative paths.
                                                    Cannot be a public IP address.</p>
                                                <button class="btn btn-light btn-sm" id="btnAddAnother">
                                                    <i class="fa fa-plus mr-2" aria-hidden="true"></i>
                                                    Add Another</button>
                                                <button class="btn btn-sm btn-danger px-1" id="btnRemoveInput"><i
                                                        class="fa fa-times mr-2" aria-hidden="true"></i>Remove</button>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-success px-2">Generate Credentials</button>
                                            <button type="button" class="btn btn-light px-2">Cancel changes</button>
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
    <script src="{{ Theme::asset('assets/js/pages/create-new-openid-connect.js') }}"></script>
@endsection
